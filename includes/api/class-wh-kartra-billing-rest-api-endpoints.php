<?php
/**
 * Add a rest api endpoints for our Template Pages.
 *
 * This class defines the template IDs and the Elementor Content for the templates.
 * Adds rest api endpoints for our Template Pages
 *
 * @link       https://waashero.com
 * @since      1.0.0
 *
 * @package    WU_REST_API
 * @subpackage WU_REST_API/includes
 * @since      1.0.0
 * @package    WU_REST_API
 * @subpackage WU_REST_API/includes
 * @author     J Hanlon | Waas Hero <info@waashero.com>
 */

namespace Wu_Kartra_Billing\WH_Kartra_Billing;

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;

// use WU_Settings;
// use WU_Subscription;
// use WU_Util;
// use WU_Gateway;
class WH_Kartra_Billing_Rest_Api_End_Points {

	protected $database_version = WH_KARTAR_BILLING_VERSION;
	protected $api_info;

	/**
	 * Store errors to display if the JWT Token is wrong
	 *
	 * @var WP_Error
	 */
	private $jwt_error = null;

	private $id    = 'kartra';
	private $title = 'kartra';

	public $network_id;

	// Multisite main network permalink (domain)
	public $network_domain;

	// Type of Multisite
	// TODO:set this programatically
	public $subdomain_install;

	function __constructor() {

		$this->network_domain    = get_permalink();
		$this->network_id        = get_current_network_id();
		$this->subdomain_install = true;

	}

	/**
	 * Registers the rest api routes for our custom elementor library and related data.
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function register_rest_routes() {
		register_rest_route(
			'waashero/v0',
			'/kartra-ipn',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_notifications' ),
				'permission_callback' => array( $this, 'verifyJWT' ),
			)
		);

		register_rest_route(
			'waashero/v0',
			'/kartra-create-user-and-site',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'createUserAndSite' ),
				'permission_callback' => array( $this, 'verifyJWT' ),
			)
		);

		register_rest_route(
			'waashero/v0',
			'/delete-site-kartra',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'deleteUserAndSiteKartra' ),
				'permission_callback' => array( $this, 'verifyJWT' ),
			)
		);

		register_rest_route(
			'waashero/v0',
			'/delete-site-after-kartra',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'deleteUserAndSiteKartraAfter' ),
				'permission_callback' => array( $this, 'verifyJWT' ),
			)
		);

		register_rest_route(
			'waashero/v0',
			'/halt-site-kartra',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'HaltSiteKartra' ),
				'permission_callback' => array( $this, 'verifyJWT' ),
			)
		);

		register_rest_route(
			'waashero/v0',
			'/revert-halt-site-kartra',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'RevertHaltSiteKartra' ),
				'permission_callback' => array( $this, 'verifyJWT' ),
			)
		);

		register_rest_route(
			'waashero/v0',
			'/refresh-api-token',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'refreshJWT' ),
			)
		);

	} // end register_rest_route_templates

	/**
	 * delete site
	 *
	 * @param \WP_REST_Request $req
	 * @return array (template_data)/ null
	 */
	public function deleteUserAndSiteKartra( \WP_REST_Request $request ) {

		$params        = file_get_contents( 'php://input' );
		$params        = json_decode( $params, true );
		$kartra_action = wh_kartra_api_settings( 'delete_user_and_site_action', '', 'cancel_subscription' );

		if ( empty( $params['action'] ) || $params['action'] !== trim( $kartra_action ) ) {

			return array(
				'status'     => 502,
				'   message' => esc_html__( 'Invalid request.', 'wh-kartra-billing' ),
				'request'    => $request->get_json_params(),
			);

		}

		 // Handle params
		$new_params = $this->handle_kartra_request_params( $params );
		$request->set_query_params( $new_params );

		$user_data['user_email'] = sanitize_email( $request->get_param( 'user_email' ) );

		if ( empty( $user_data['user_email'] ) ) {
			return array(
				'status'  => 500,
				'message' => esc_html__( 'Empty user email.', 'wh-kartra-billing' ),
			);
		}

		$wp_user = get_user_by( 'email', $user_data['user_email'] );

		if ( ! function_exists( 'wpmu_delete_blog' ) ) {
			require_once ABSPATH . '/wp-admin/includes/ms.php';
		}

		// Get the sites
		$site_list = get_blogs_of_user( $wp_user->ID );

		// $subscription->get_sites();
		$subscription = array();
		do_action( 'wh_kartra_before_delete_site', $wp_user, $subscription, $site_list );

		if ( empty( $site_list ) ) {

			return array(
				'status'  => 200,
				'message' => esc_html__( 'No site found to delete.', 'wh-kartra-billing' ),
			);
		}

		foreach ( $site_list as $site ) {

			switch_to_blog( $site->userblog_id );
			$wp_upload_info = wp_upload_dir();
			$dir            = str_replace( ' ', '\\ ', trailingslashit( $wp_upload_info['basedir'] ) );
			restore_current_blog();
			wpmu_delete_blog( $site->userblog_id, true );

			// wpmu_delete_blog leaves an empty site upload directory, that we want to remove :
			$this->rrmdir( $dir );

		} // end foreach;

		wpmu_delete_user( $wp_user->ID );
		$params = array(
			'user'  => $wp_user,
			'sites' => $site_list,
		);
		do_action( 'wh_kartra_after_delete_site', $params );

		return array(
			'status'  => 200,
			'message' => esc_html__( 'User site data deleted successfully.', 'wh-kartra-billing' ),
		);
	}

	/**
	 * delete site
	 *
	 * @param \WP_REST_Request $req
	 * @return array (template_data)/ null
	 */
	public function deleteUserAndSiteKartraAfter( \WP_REST_Request $request ) {

		$params        = file_get_contents( 'php://input' );
		$params        = json_decode( $params, true );
		$kartra_action = wh_kartra_api_settings( 'delete_site_after_action', '', 'cancel_subscription_after' );

		if ( empty( $params['action'] ) || $params['action'] !== trim( $kartra_action ) ) {

			return array(
				'status'  => 502,
				'message' => esc_html__( 'Invalid request.', 'wh-kartra-billing' ),
				'request' => $request->get_json_params(),
			);

		}

		// Handle params
		$new_params = $this->handle_kartra_request_params( $params );
		$request->set_query_params( $new_params );

		$user_data['user_email'] = sanitize_email( $request->get_param( 'user_email' ) );

		if ( empty( $user_data['user_email'] ) ) {
			return array(
				'status'  => 500,
				'message' => esc_html__( 'Empty user email.', 'wh-kartra-billing' ),
			);
		}

		$wp_user = get_user_by( 'email', $user_data['user_email'] );
		$time    = sanitize_text_field( $request->get_param( 'delete_after' ) );

		// $subscription->get_sites();
		$subscription = array();
		do_action( 'wh_kartra_before_delete_site_set_time', $wp_user, $subscription, $site_list );

		update_user_meta( $wp_user->ID, 'wh_kartra_cancel_subscription_after', $time );

		do_action( 'wh_kartra_after_delete_site_set_time', $params );

		return array(
			'status'  => 200,
			'message' => esc_html__( 'User site data deleted successfully.', 'wh-kartra-billing' ),
		);
	}

	/**
	 * Halt site
	 *
	 * @param \WP_REST_Request $req
	 * @return array (template_data)/ null
	 */
	public function HaltSiteKartra( \WP_REST_Request $request ) {

		$params        = file_get_contents( 'php://input' );
		$params        = json_decode( $params, true );
		$kartra_action = wh_kartra_api_settings( 'halt_site_action', '', 'halt_site' );

		\WU_Logger::add( 'kartra-haltsite', json_encode( $params ) );

		if ( empty( $params['action'] ) || $params['action'] !== trim( $kartra_action ) ) {

			return array(
				'status'  => 502,
				'message' => esc_html__( 'Invalid request.', 'wh-kartra-billing' ),
				'request' => $request->get_json_params(),
			);

		}

		 // Handle params
		$new_params = $this->handle_kartra_request_params( $params );
		$request->set_query_params( $new_params );

		$user_data['user_email'] = sanitize_email( $request->get_param( 'user_email' ) );

		if ( empty( $user_data['user_email'] ) ) {
			return array(
				'status'  => 500,
				'message' => esc_html__( 'Empty user email.', 'wh-kartra-billing' ),
			);
		}

		$wp_user = get_user_by( 'email', $user_data['user_email'] );

		if ( ! function_exists( 'wpmu_delete_blog' ) ) {
			require_once ABSPATH . '/wp-admin/includes/ms.php';
		}

		// Get the sites
		$site_list = get_blogs_of_user( $wp_user->ID );

		// $subscription->get_sites();
		$subscription = array();
		do_action( 'wh_kartra_before_halt_site', $wp_user, $subscription, $site_list );

		if ( empty( $site_list ) ) {

			return array(
				'status'  => 200,
				'message' => esc_html__( 'No site found to halt.', 'wh-kartra-billing' ),
			);
		}

		foreach ( $site_list as $site ) {

			update_user_meta( $wp_user->ID, 'kartra_payment_status_' . $site->userblog_id, 'failed' );
		} // end foreach;

		$params = array(
			'user'  => $wp_user,
			'sites' => $site_list,
		);
		do_action( 'wh_kartra_after_halt_site', $params );

		return array(
			'status'  => 200,
			'message' => esc_html__( 'User site halted successfully.', 'wh-kartra-billing' ),
		);
	}

	/**
	 * Halt site
	 *
	 * @param \WP_REST_Request $req
	 * @return array (template_data)/ null
	 */
	public function RevertHaltSiteKartra( \WP_REST_Request $request ) {

		$params        = file_get_contents( 'php://input' );
		$params        = json_decode( $params, true );
		$kartra_action = wh_kartra_api_settings( 'revert_halt_site_action1', '', 'revert_halt_site' );

		if ( empty( $params['action'] ) || $params['action'] !== trim( $kartra_action ) ) {

			return array(
				'status'  => 502,
				'message' => esc_html__( 'Invalid request.', 'wh-kartra-billing' ),
				'request' => $request->get_json_params(),
			);

		}

		// Handle params
		$new_params = $this->handle_kartra_request_params( $params );
		$request->set_query_params( $new_params );

		$user_data['user_email'] = sanitize_email( $request->get_param( 'user_email' ) );

		if ( empty( $user_data['user_email'] ) ) {
			return array(
				'status'  => 500,
				'message' => esc_html__( 'Empty user email.', 'wh-kartra-billing' ),
			);
		}

		$wp_user = get_user_by( 'email', $user_data['user_email'] );

		if ( ! function_exists( 'wpmu_delete_blog' ) ) {
			require_once ABSPATH . '/wp-admin/includes/ms.php';
		}

		// Get the sites
		$site_list = get_blogs_of_user( $wp_user->ID );

		// $subscription->get_sites();
		$subscription = array();
		do_action( 'wh_kartra_before_revert_halt_site', $wp_user, $subscription, $site_list );

		if ( empty( $site_list ) ) {

			return array(
				'status'  => 200,
				'message' => esc_html__( 'No site found to halt.', 'wh-kartra-billing' ),
			);
		}

		foreach ( $site_list as $site ) {

			update_user_meta( $wp_user->ID, 'kartra_payment_status_' . $site->userblog_id, 'success' );
		} // end foreach;

		$params = array(
			'user'  => $wp_user,
			'sites' => $site_list,
		);
		do_action( 'wh_kartra_after_revert_halt_site', $params );

		return array(
			'status'  => 200,
			'message' => esc_html__( 'User site halt reverted successfully.', 'wh-kartra-billing' ),
		);
	}

	/**
	 * Create wp ultimo site
	 *
	 * @param \WP_REST_Request $request
	 * @param user_login
	 * @param user_pass
	 * @param user_email
	 * @param plan_id
	 * @param plan_freq
	 * @param blog_title
	 * @param blogname
	 * @param role
	 * @param template_id
	 * @return array (template_data)/ null
	 */
	public function createUserAndSite( \WP_REST_Request $request ) {

		// Kartra's testing request params from postman
		$params        = file_get_contents( 'php://input' );
		$params        = json_decode( $params, true );
		$kartra_action = wh_kartra_api_settings( 'create_user_and_site_action', '', 'buy_product' );

		if ( empty( $params['action'] ) || $params['action'] !== trim( $kartra_action ) ) {

			return array(
				'status'  => 502,
				'message' => esc_html__( 'Invalid request.', 'wh-kartra-billing' ),
				'request' => $request->get_json_params(),
			);

		}

		// Handle params
		$new_params = $this->handle_kartra_request_params( $params );
		$request->set_query_params( $new_params );

		$network_domain    = $this->remove_http( ( ! empty( $this->network_domain ) ? $this->network_domain : network_site_url() ) );
		$network_id        = ( ! empty( $this->network_id ) ? $this->network_id : get_current_network_id() );
		$subdomain_install = true;

		// Create user array so we can filter later
		$user_data['user_login'] = sanitize_email( strtolower( $request->get_param( 'user_email' ) ) );
		$user_data['user_email'] = sanitize_email( $request->get_param( 'user_email' ) );
		$plan_data['plan_id']    = intval( $request->get_param( 'plan_id' ) );
		// $site_type               = sanitize_text_field( strtolower( $request->get_param( 'site_type' ) ) );
		$plan_data['plan_freq'] = intval( $request->get_param( 'plan_freq' ) );
		$user_data['user_pass'] = wp_generate_password( 10, true, false );
		$user_source            = $request->get_param( 'user_source' ) ? sanitize_text_field( strtolower( $request->get_param( 'user_source' ) ) ) : '';
		$source_id              = $request->get_param( 'source_id' ) ? sanitize_text_field( $request->get_param( 'source_id' ) ) : '';
		$subsite_url            = $request->get_param( 'subsite_url' ) ? sanitize_text_field( strtolower( $request->get_param( 'subsite_url' ) ) ) : '';
		$user_data['role']      = $request->get_param( 'user_role' ) ? sanitize_text_field( strtolower( $request->get_param( 'user_role' ) ) ) : 'subscriber';

		if ( empty( $user_data['user_login'] )
		   || empty( $user_data['user_pass'] )
		   || empty( $user_data['user_email'] )
			) {
			return array(
				'status'  => 500,
				'message' => esc_html__( 'Invalid user data or plan values. Please check your API post.', 'wh-kartra-billing' ),
				'request' => $request->get_json_params(),
			);
		}

		do_action( 'wh_kartra_before_creating_site_user', $user_data );

		$wp_user = get_user_by( 'email', $user_data['user_email'] );

		if ( ! wp_roles()->is_role( strtolower( trim( $user_data['role'] ) ) ) ) {
			return array(
				'status'  => 502,
				'message' => esc_html__( 'User role does not exist.', 'wh-kartra-billing' ),
			);
		}

		if ( empty( $wp_user->ID ) ) {
			$user_id = wpmu_create_user( $user_data['user_login'], $user_data['user_pass'], $user_data['user_email'] );
		} else {
			$user_id = $wp_user->ID;
		}

		if ( empty( $user_id ) || is_wp_error( $user_id ) ) {
			return array(
				'status'  => 501,
				'message' => esc_html__( 'A User could not be found or created.', 'wh-kartra-billing' ),
				'error'   => $user_id,
			);
		}

		 // Checks if user is valid
		if ( user_can( $user_id, 'manage_network' ) ) {
			// WP_Ultimo()->add_message( esc_html__( 'You cannot create a subscription for a network admin.', 'wh-kartra-billing' ), 'error', true );
			return array(
				'status'  => 501,
				'message' => esc_html__( 'You cannot create a subscription for a network admin.', 'wh-kartra-billing' ),
			);
		}

		if ( preg_match( '/[\'^Â£$%&*()}{@#~?><>,|=_+Â¬-]/', strtolower( $subsite_url ) ) ) {
			// one or more of the 'special characters' found in $string.
			return array(
				'status'  => 502,
				'message' => esc_html__( 'Missing or invalid site data. Subsite url can not contain special characters.', 'wh-kartra-billing' ),
				'request' => $request->get_json_params(),
			);
		}

		// Try setting directly
		// update_user_meta( $user_id, 'wp_capabilities', 'a:1:{s:13:"administrator";b:1;}' );

		if ( empty( $request->get_param( 'blog_title' ) ) ) {
			$blog_title = strtolower( str_replace( '.', '_', str_replace( '@', '_', $user_data['user_email'] ) ) );
		} else {
			$blog_title = $request->get_param( 'blog_title' );
		}

		$subsite_url = ( empty( $subsite_url ) ? strtolower( str_replace( '.', '_', str_replace( '@', '_', $blog_title ) ) ) : $subsite_url );

		// Build arrays & we can filter later with an action
		$user_data['user_id']     = $user_id;
		$user_data['user_source'] = $user_source;
		$user_data['source_id']   = $source_id;
		// $site_data['domain_option'] = uniqid( strtolower( $subsite_url ) );
		$site_data['blog_title'] = sanitize_text_field( $blog_title );
		$site_data['blogname']   = strtolower( $subsite_url );
		$template_id             = intval( $request->get_param( 'template_id' ) );

		// Check for missing data.
		if ( empty( $site_data['blog_title'] )
		   || empty( $site_data['blogname'] )
		   || empty( $user_data['role'] )
		   || empty( $template_id ) ) {
			   return array(
				   'status'  => 502,
				   'message' => esc_html__( 'Missing or invalid site data.', 'wh-kartra-billing' ),
				   'request' => $request->get_json_params(),
			   );
		}

		// Now we can filter the array if necessary
		$meta = array();
		do_action( 'wh_kartra_after_creating_site', $user_data, $site_data, $template_id, $meta );

		// Create site
		if ( $subdomain_install ) {
			$domain = "{$site_data['blogname']} . $network_domain";
			$path   = '/';
		} else {
			$domain = $network_domain;
			$path   = "/{$site_data['blogname']}/";
		}

		// Check if the domain has been used already. We should return an error message.
		if ( domain_exists( $domain, $path, $network_id ) ) {
			return array(
				'status'  => 503,
				'message' => esc_html__( 'Sorry, that site already exists!', 'wh-kartra-billing' ),
			);
		}

		// $site_id = wu_create_site( $user_id, $site_data, $template_id, $meta );
		$site_id = wpmu_create_blog( $domain, $path, $site_data['blog_title'], $user_id, array( 'public' => 1 ), $network_id );
		if ( empty( $site_id ) || is_wp_error( $site_id ) ) {
			return array(
				'status'   => 503,
				'message'  => esc_html__( 'There was an error. Your new site was not created.', 'wh-kartra-billing' ),
				'response' => $site_id,
			);
		}

		$params = array(
			'site_id'     => $site_id,
			'user'        => $user_data,
			'site'        => $site_data,
			'template_id' => $template_id,
			'meta'        => $meta,
		);

		// Filter paramters after site creation
		do_action( 'wh_kartra_after_creating_site', $params );

		/**
		 * We need to make this user the owner of the site
		*/

		// Email login credentials to a newly-registered user using WordPress built in function.
		wp_new_user_notification( $user_id, $user_data['user_pass'] );

		// Create a password reset link to send as a 'set password' url
		$site_url   = get_site_url( $site_id );
		$adt_rp_key = get_password_reset_key( get_userdata( $user_id ) );
		$rp_link    = $site_url . "/setup/?key=$adt_rp_key&login=" . rawurlencode( $user_data['user_login'] );

		// return successful
		return array(
			'status'    => 200,
			'message'   => esc_html__( 'Success! Your new site has been created. If you\'re a new user we also created a user account for your website!', 'wh-kartra-billing' ),
			'user_id'   => $user_id,
			'site_id'   => $site_id,
			'site_url'  => $site_url,
			'setup_url' => $rp_link,
		);
	}

	/**
	 * Handles the notifications sent by Stripe's API
	 */
	public function handle_notifications( \WP_REST_Request $request ) {

		global $wpdb;

		// Retrieve the request's body and parse it as JSON
		$event_ipn = $request->get_params();
		// Get user sent from metadata

		// For DEBUGING PURPUSES - Log entire request

		// if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {

			// \WU_Logger::add( 'gateway-' . $this->id . '-ipn', '------' );
			// \WU_Logger::add( 'gateway-' . $this->id . '-ipna', json_encode( $event_ipn ) );
			// \WU_Logger::add( 'gateway-' . $this->id . '-ipn', '------' );

		// } // end if;
		$event = (object) $event_ipn;

		if ( ! isset( $event->lead_id ) ) {

			return;

		} // end if;

		/**
		 * Get the user based on the custom information
		 */

		$customer_id = $event->lead_id;
		// for test only
		// update_user_meta( 123, 'wu_kartra_user_id', 1234 );
		$kartra_customer = wu_kertar_get_user_by_metadata( 'wu_kartra_user_id', $customer_id );
		$user_id         = ( ! empty( $kartra_customer->ID ) ? $kartra_customer->ID : '' );

		/**
		 * Now we switch each important message to take appropriate measures
		 */

		switch ( $event->transaction_type ) {

			/**
			 * Case Cancel
			 * Now we handle the cancelation of a subscription
			 */
			case 'cancellation':
				// case 'customer.deleted':

				// Log Event
				$log = sprintf( __( 'The payment subscription using %1$s, was canceled for user %2$s.', 'wh-kartra-billing' ), $this->title, $user_id );
				// \WU_Logger::add( 'gateway-' . $this->id, $log );

				update_user_meta( $user_id, 'kartra_payment_status', 'failed' );
				// End case
				break;

			/**
			 * Case Subscriptions created and updated
			 * Handles the payment received
			 */
			case 'sale':
				// \WU_Logger::add( 'gateway-' . $this->id, sprintf( __( 'User ID: %1$s - Kartra Webhook "%2$s" received.', 'wh-kartra-billing' ), $user_id, $event->type ) . $event->id );

				// \WU_Logger::add( 'gateway-' . $this->id, sprintf( __( 'User ID: %1$s - Kartra Webhook received: %2$s %3$s payment received, transaction ID %4$s', 'wh-kartra-billing' ), $user_id, $event->type, wu_format_currency( $this->format_from_stripe( $event->transaction_full_amount ) ), $event->id ) );
				update_user_meta( $user_id, 'kartra_payment_status', 'success' );
				/**
				 * @since  1.1.2 Hooks for payments and integrations
				 */
				do_action( 'wh_kartra_payment_completed', $user_id, $this->id, $event->transaction_full_amount );

				break;

			/**
			 * Case Payment Received - Successfully
			 * Handles the payment received
			 */
			case 'rebill':
				/**
				 * Resets the active until
				 *
				 * @var DateTime
				 */

				// \WU_Logger::add( 'gateway-' . $this->id, sprintf( __( 'User ID: %1$s - Kartra Webhook received: %2$s %3$s payment received, transaction ID %4$s', 'wh-kartra-billing' ), $user_id, $event->type, wu_format_currency( $this->format_from_stripe( $event->transaction_full_amount ) ), $event->id ) );
				update_user_meta( $user_id, 'kartra_payment_status', 'success' );
				/**
				 * @since  1.1.2 Hooks for payments and integrations
				 */
				do_action( 'wh_kartra_payment_completed', $user_id, $this->id, $event->transaction_full_amount, $setup_fee_value );

				// End case
				break;

			/**
			 * Case Payment Received - Failed
			 * Handles the payment received in case of failure
			 */
			case 'failed':
				// Log this
				// \WU_Logger::add( 'gateway-' . $this->id, sprintf( __( 'User ID: %s - Kartra Webhook received: The payment has failed.', 'wh-kartra-billing' ), $user_id, $event->id ) . $event_id );

				update_user_meta( $user_id, 'kartra_payment_status', 'failed' );
				/**
				 * @since  1.1.2 Hooks for payments and integrations
				 */
				do_action( 'wh_kartra_payment_refunded', $user_id, $this->id, $value );

				break;

		} // end switch;

		// Let Stripe know that everything went fine
		http_response_code( 200 ); // PHP 5.4 or greater

		// Return and Kill
		echo json_encode( array( 'message' => esc_html__( 'Thanks! Kartra', 'wh-kartra-billing' ) ) );
		die;

	} // end handle_notifications;


	/**
	 * Verify the JWT token from api request
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function verifyJWT( \WP_REST_Request $request ) {

		$remove_authorization = wh_kartra_api_settings( 'remove_authorization', true );
		$encryption_key       = wh_kartra_api_settings( 'encryption_key', '', 'aBC5v1sOKVabdEitdSBrnu59nfNfbwkedkJVNrbosTw=' );
		$app_id               = wh_kartra_api_settings( 'app_id', '', '' );

		if ( empty( $remove_authorization ) ) {

			$token = $request->get_header( 'authorization' );
			$token = trim( str_replace( 'Bearer', '', $token ) );

			if ( empty( $token ) ) {
				$token = $request->get_param( 'token' );
			}

			$config = Configuration::forSymmetricSigner(
			// You may use any HMAC variations (256, 384, and 512)
				new Sha256(),
				// replace the value below with a key of your own!
				InMemory::base64Encoded( $encryption_key )
			);

			try {

				$token = $config->parser()->parse( $token );
				assert( $token instanceof Token );

				$validate = $config->validator()->validate( $token, new IssuedBy( $app_id ) );
				// new Constraint\PermittedFor('http://example.com')

				if ( ! $validate ) {

					return new \WP_Error(
						'jwt_auth_no_auth_data',
						esc_html__( 'Api Error: Invalid token. Please enter your token correctly.', 'wh-kartra-billing' ),
						array(
							'status'   => 400,
							'messsage' => esc_html__( 'Api Error: Invalid token. Please enter your token correctly.', 'wh-kartra-billing' ),
						)
					);

				} elseif ( $token->isExpired() ) {

					return new \WP_Error(
						'jwt_auth_no_auth_data',
						esc_html__( 'Api Error: Token Expired. Please get a valid token.', 'wh-kartra-billing' ),
						array(
							'status'   => 400,
							'messsage' => esc_html__( 'Api Error: Token Expired. Please get a valid token.', 'wh-kartra-billing' ),
						)
					);

				}
			} catch ( \Exception $e ) {

				return new \WP_Error(
					'jwt_auth_no_auth_data',
					esc_html__( 'Api Error: Invalid token. Please enter your token correctly.', 'wh-kartra-billing' ),
					array(
						'status'   => 400,
						'messsage' => esc_html__( 'Api Error: Invalid token. Please enter your token correctly.', 'wh-kartra-billing' ),
					)
				);
			}

			return array(
				'data' => array(
					'status'    => 200,
					'message'   => esc_html__( 'Your Api Key was successfully validated.', 'wh-kartra-billing' ),
					'validated' => $validate,
				),
			);

		} else {

			return array(
				'data' => array(
					'status'    => 200,
					'message'   => esc_html__( 'Your Api Key was successfully validated.', 'wh-kartra-billing' ),
					'validated' => array(),
				),
			);
		}

	}



	/**
	 * Refresh the JWT token
	 *
	 * @param \WP_REST_Request $request
	 * @return void
	 */
	public function refreshJWT( \WP_REST_Request $request ) {

		$remove_authorization = wh_kartra_api_settings( 'remove_authorization', true );
		$encryption_key       = wh_kartra_api_settings( 'encryption_key', '', 'aBC5v1sOKVabdEitdSBrnu59nfNfbwkedkJVNrbosTw=' );
		$app_id               = wh_kartra_api_settings( 'app_id', '' );

		if ( empty( $remove_authorization ) ) {

			$auth_token = $request->get_header( 'authorization' );
			$auth_token = trim( str_replace( 'Bearer', '', $auth_token ) );

			if ( empty( $auth_token ) ) {
				$auth_token = $request->get_param( 'token' );
			}

			$config = Configuration::forSymmetricSigner(
			// You may use any HMAC variations (256, 384, and 512)
				new Sha256(),
				// replace the value below with a key of your own!
				InMemory::base64Encoded( $encryption_key )
			);

			try {

				$token = $config->parser()->parse( $auth_token );
				assert( $token instanceof Token );

				$validate = $config->validator()->validate( $token, new IssuedBy( $app_id ) );

				if ( ! $validate ) {

					return new \WP_Error(
						'jwt_auth_no_auth_data',
						esc_html__( 'Api Error: Invalid token. Please enter your expired token correctly.', 'wh-kartra-billing' ),
						array(
							'status'   => 400,
							'messsage' => esc_html__( 'Api Error: Invalid token. Please enter your expired token correctly.', 'wh-kartra-billing' ),
						)
					);

				} elseif ( ! $token->isExpired() ) { // if got expire generate new one

					$token = ( new Builder() )->setIssuedAt( time() )
								->setExpiration( time() + 604800 ) // maximum: 604,800 , 7 days
								->setIssuer( $app_id ) // App ID
								->sign( new Sha256(), $encryption_key )
								->getToken();

					if ( ! empty( $token->toString() ) ) {

						// Update token for admin
						$wh_kartra_billing_admin_settings                        = get_site_option( 'wh_kartra_billing_admin_settings', array() );
						$wh_kartra_billing_admin_settings['wh_kartra_api_token'] = $token->toString();
						update_site_option( 'wh_kartra_billing_admin_settings', $wh_kartra_billing_admin_settings );

						return array(

							'data' => array(
								'status'  => 200,
								'message' => esc_html__( 'Your Api Key generated.', 'wh-kartra-billing' ),
								'token'   => $token->toString(),
							),
						);

					} else {

						return new \WP_Error(
							'jwt_auth_no_auth_data',
							esc_html__( 'Api Error: Invalid token. Please enter your expired token correctly.', 'wh-kartra-billing' ),
							array(
								'status'   => 400,
								'messsage' => esc_html__( 'Api Error: Invalid token. Please enter your expired token correctly.', 'wh-kartra-billing' ),
							)
						);

					}
				} else { // else return the existing token

					return array(

						'data' => array(
							'status'  => 200,
							'message' => esc_html__( 'Your Api Key generated.', 'wh-kartra-billing' ),
							'token'   => $auth_token,
						),
					);

				}
			} catch ( \Exception $e ) {

				return new \WP_Error(
					'jwt_auth_no_auth_data',
					esc_html__( 'Api Error: Invalid token. Please enter your expired token correctly.', 'wh-kartra-billing' ),
					array(
						'status'   => 400,
						'messsage' => esc_html__( 'Api Error: Invalid token. Please enter your expired token correctly.', 'wh-kartra-billing' ),
					)
				);
			}

			return array(
				'data' => array(
					'status'    => 200,
					'message'   => esc_html__( 'Your Api Key was successfully validated.', 'wh-kartra-billing' ),
					'validated' => $validate,
				),
			);

		} else {

			return array(
				'data' => array(
					'status'    => 200,
					'message'   => esc_html__( 'Service blocked by admin.', 'wh-kartra-billing' ),
					'validated' => array(),
				),
			);
		}

	}

	/**
	 * Format Stripe values received from stripe
	 *
	 * @param interger $value Value to be formated to amount
	 * @since  1.2.0 Support for zero decimal currencies
	 */
	public function format_from_stripe( $value ) {
		$value = \WU_Util::to_float( $value );

		// No Cents currencies
		$no_cents = array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF', 'USD' );

		return in_array( strtoupper( \WU_Settings::get_setting( 'currency_symbol' ) ), $no_cents ) ? $value : $value / 100;

	} // end format_from_stripe;


	/**
	 *  Handle Kartra's Request Params
	 *  This function will filter the Kartra's API request params and assign to WP_REST_Request object
	 *
	 *  @param Array @params
	 *  @return void
	 */

	private function handle_kartra_request_params( $params ) {

		$new_params = array();

		$param_keys = array(
			'user_email'   => 'lead_email',
			'plan_id'      => 'product_id',
			'plan_freq'    => 'trial_period',
			'user_source'  => 'user_source',
			'source_id'    => 'user_source_id',
			'subsite_url'  => 'user_site_name',
			'user_role'    => '',
			'blog_title'   => 'user_site_name',
			'template_id'  => 'user_site_template_id',
			'blogname'     => 'user_site_name',
			'user_login'   => '',
			'delete_after' => 'delete_after',

		);

		// Handle Kartra's request params
		if ( ! empty( $params['action_details']['transaction_details'] ) ) {

			$lead                     = ( ! empty( $params['lead'] ) ? $params['lead'] : array() );
			$params                   = array_merge( $params, $params['action_details']['transaction_details'] );
			$params['user_source']    = $lead['source'];
			$params['user_source_id'] = $lead['source_id'];

			if ( ! empty( $lead['custom_fields'] ) && count( $lead['custom_fields'] ) > 0 ) {

				$site_name_custom_field = array_filter(
					$lead['custom_fields'],
					function( $custom_field ) {

						return ( ! empty( $custom_field['field_identifier'] ) && $custom_field['field_identifier'] === 'site_name' );
					}
				);

				$site_template_custom_field = array_filter(
					$lead['custom_fields'],
					function( $custom_field ) {

						return ( ! empty( $custom_field['field_identifier'] ) && $custom_field['field_identifier'] === 'user_site_template_id' );
					}
				);

				$site_template_custom_field = ( ! empty( $site_template_custom_field ) ? reset( $site_template_custom_field ) : array() );
				$site_name_custom_field     = ( ! empty( $site_name_custom_field ) ? reset( $site_name_custom_field ) : array() );

				$params['user_site_name']        = ( ! empty( $site_name_custom_field['field_value'] ) ? $site_name_custom_field['field_value'] : '' );
				$params['user_site_template_id'] = ( ! empty( $site_template_custom_field['field_value'][0]['option_value'] ) ? $site_template_custom_field['field_value'][0]['option_value'] : 0 );

				if ( ! empty( $params['user_site_template_id'] ) ) {

					preg_match( '/\(([^\)]*)\)/', $params['user_site_template_id'], $TemplateNumberMatch );
					$params['user_site_template_id'] = str_replace( '#', '', $TemplateNumberMatch[1] );
				}
			}
		}

		foreach ( $param_keys  as $key => $param_value ) :

			if ( ! empty( $param_keys[ $key ] ) ) {
				if ( ! empty( $params[ $param_keys[ $key ] ] ) ) {
					$new_params[ $key ] = $params[ $param_keys[ $key ] ];
				} else {
					$new_params[ $key ] = $param_keys[ $key ];
				}
			} else {
				$new_params[ $key ] = $param_keys[ $key ];
			}

		endforeach;

		return $new_params;
	}

	/**
	 *  Remove HTTP protocol from url
	 *
	 *  @param url $url
	 *  @return url $url
	 */
	function remove_http( $url ) {

		$disallowed = array( 'http://', 'https://' );

		foreach ( $disallowed as $d ) {
			if ( strpos( $url, $d ) === 0 ) {
				return str_replace( $d, '', $url );
			}
		}

		return $url;

	}

	  /**
	   *  Remove Directory
	   *
	   *  @param url $url
	   *  @return void
	   */
	function rrmdir( $dir ) {

		if ( is_dir( $dir ) ) {
			$objects = scandir( $dir );
			foreach ( $objects as $object ) {
				if ( $object != '.' && $object != '..' ) {
					if ( filetype( $dir . '/' . $object ) == 'dir' ) {
						self::rrmdir( $dir . '/' . $object );
					} else {
						unlink( $dir . '/' . $object );
					}
				}
			}
			reset( $objects );
			rmdir( $dir );
		}

	}

} // end Class - WU_REST_API_Rest_Api
