<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.waashero.com
 * @since      1.0.0
 *
 * @package    WH_Kartra_Billing
 * @subpackage WH_Kartra_Billing/admin
 */
namespace Wu_Kartra_Billing\WH_Kartra_Billing;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WH_Kartra_Billing
 * @subpackage WH_Kartra_Billing/admin
 * @author     J Hanlon <waashero@info.com>
 */
class WH_Kartra_Billing_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name  The name of this plugin.
	 * @param    string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WH_Kartra_Billing_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WH_Kartra_Billing_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		if ( stripos( get_current_screen()->id, 'kartra-billing' ) ) {
			wp_enqueue_style(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'css/wh-kartra-billing-admin.css',
				array(),
				$this->version,
				'all'
			);
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in WH_Kartra_Billing_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The WH_Kartra_Billing_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		if ( stripos( get_current_screen()->id, 'kartra-billing' ) ) {
			wp_enqueue_script(
				$this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'js/wh-kartra-billing-admin.js',
				array( 'jquery' ),
				$this->version,
				false
			);
			wp_enqueue_script(
				'sweetalert2-wh-' . $this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'js/sweetalert2.min.js',
				array( 'jquery' ),
				$this->version,
				true
			);

			wp_enqueue_script(
				'tailwind-wh-' . $this->plugin_name,
				plugin_dir_url( __FILE__ ) . 'js/tailwindcss.min.js',
				array( 'jquery' ),
				$this->version,
				true
			);

			if ( isset( $_GET['tab'] ) ) {
				$tab = sanitize_text_field( $_GET['tab'] );
			} else {
				$tab = '';
			}
			$user_id = get_current_user_id();
			wp_localize_script(
				$this->plugin_name,
				'wh_kartra_billing_ajax_obj',
				array(
					'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
					'user_id'                  => $user_id,
					'blog_id'                  => get_current_blog_id(),
					'tab'                      => $tab,
					'th_create_token_security' => wp_create_nonce( 'create-token-' . $user_id ),
				)
			);

			wp_localize_script(
				$this->plugin_name . '-license',
				'wh_kartra_billing_ajax_obj',
				array(
					'ajaxurl'                  => admin_url( 'admin-ajax.php' ),
					'user_id'                  => $user_id,
					'blog_id'                  => get_current_blog_id(),
					'tab'                      => $tab,
					'th_create_token_security' => wp_create_nonce( 'create-token-' . $user_id ),
				)
			);
		}

	}

	/**
	 * Handles admin notifications.
	 *
	 * @return void
	 */
	public function kartra_actions_admin_notice__error() {
		$payment_failed = get_user_meta( get_current_user_id(), 'kartra_payment_status_' . get_current_blog_id(), true );
		if ( $payment_failed == 'failed' ) {
			$class   = 'notice notice-error is-dismissible';
			$message = esc_html__( 'Your payment for REI Grow Sites has failed.', 'wh-kartra-billing' );
			$link    = '<a href="www.Kartra.com">' . esc_html__( 'Click here to update your payment info', 'wh-kartra-billing' ) . '</a>';
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) . $link );
		}
	}

}
