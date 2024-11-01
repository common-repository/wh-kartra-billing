<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       www.waashero.com
 * @since      1.0.0
 *
 * @package    WH_Kartra_Billing
 * @subpackage WH_Kartra_Billing/includes
 */

namespace Wu_Kartra_Billing;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WH_Kartra_Billing
 * @subpackage WH_Kartra_Billing/includes
 * @author     J Hanlon <waashero@info.com>
 */
class WH_Kartra_Billing {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WH_Kartra_Billing_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Self instance.
	 *
	 * @var WH_Kartra_Billing
	 */
	private static $instance = null;
	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WH_KARTAR_BILLING_VERSION' ) ) {
			$this->version = WH_KARTAR_BILLING_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wh-kartra-billing';
		global $wh_kartra_actions;

		$wh_kartra_actions = array(
			get_rest_url( null, 'waashero/v0/kartra-create-user-and-site' ) => esc_html__( 'Create User and Site.', 'conditional-shortcode' ),
			get_rest_url( null, 'waashero/v0/delete-site-kartra' ) => esc_html__( 'Delete site as directed by the kartra action.', 'conditional-shortcode' ),
			get_rest_url( null, 'waashero/v0/delete-site-after-kartra' ) => esc_html__( 'Delete site after specific time. Site will be deleted if given time span has passed.', 'conditional-shortcode' ),
			get_rest_url( null, 'waashero/v0/halt-site-kartra' ) => esc_html__( 'Halt site ,block front end access and display error message in admin interfaces.', 'conditional-shortcode' ),
			get_rest_url( null, 'waashero/v0/revert-halt-site-kartra' ) => esc_html__( 'Revert site halt to normal state and allow user to access frontend.', 'conditional-shortcode' ),
			get_rest_url( null, 'waashero/v0/kartra-ipn' ) => esc_html__( 'Listens to kartra IPN and performs directed action fully availble in paid version.', 'conditional-shortcode' ),
		);

		$wh_kartra_actions = apply_filters( 'waashero_kartra_billing_actions', $wh_kartra_actions );
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_public_rest_api_hooks();

		$this->loader->add_action(
			'init',
			'',
			'wh_kartra_billing_delete_user_and_sites',
			99999
		);

	}
	// The object is created from within the class itself.
	// only if the class has no instance.
	/**
	 * Get instance
	 *
	 * @since 1.0.3
	 * @return WH_Kartra_Billing instance
	 */
	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new WH_Kartra_Billing();
		}

		return self::$instance;
	}
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WH_Kartra_Billing_Loader. Orchestrates the hooks of the plugin.
	 * - WH_Kartra_Billing_i18n. Defines internationalization functionality.
	 * - WH_Kartra_Billing_Admin. Defines all hooks for the admin area.
	 * - WH_Kartra_Billing_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Load dependecies managed by composer.
		 */
		if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php' ) && ( ! class_exists( '\Lcobucci\JWT\JWT' ) && ! class_exists( 'JWT' ) ) ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'vendor/autoload.php';
		}
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wh-kartra-billing-admin.php';
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wh-kartra-billing-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wh-kartra-billing-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings/class-wh-kartra-billing-options.php';
		/**
		 * The class responsible for defining all rest api actions that occur in the public-facing
		 * side of the site.
		 */
		if ( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api/class-wh-kartra-billing-rest-api-endpoints.php' ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api/class-wh-kartra-billing-rest-api-endpoints.php';
		}
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/log/class-wh-kartra-billing-logs.php';
		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wh-kartra-billing-public.php';

		$this->loader = new WH_Kartra_Billing\WH_Kartra_Billing_Loader();
		/**
		 * The functions responsible for loading the admin menu and definging the update actions.
		 */
		if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wh-kartra-billing-functions.php' ) ) {

			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wh-kartra-billing-functions.php';
		}

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WH_Kartra_Billing_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WH_Kartra_Billing\WH_Kartra_Billing_i18n();

		$this->loader->add_action(
			'plugins_loaded',
			$plugin_i18n,
			'load_plugin_textdomain'
		);

	}

	/**
	 * Register all of the hooks related to the public-facing rest api functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_rest_api_hooks() {

		$plugin_restapi            = new WH_Kartra_Billing\WH_Kartra_Billing_Rest_Api_End_Points();
		$wh_kartra_billing_options = get_site_option( 'wh_kartra_billing_advance_options', array() );
		$make_live                 = ! empty( $wh_kartra_billing_options['wh_kartra_billing_make_library'] ) ? $wh_kartra_billing_options['wh_kartra_billing_make_library'] : '';
		if ( $make_live == '' ) {
			$make_live = 'on';
		}

		if ( $make_live == 'on' ) {
			$this->loader->add_action(
				'rest_api_init',
				$plugin_restapi,
				'register_rest_routes'
			);
		}

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new WH_Kartra_Billing\WH_Kartra_Billing_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_logs  = new WH_Kartra_Billing\WH_Kartra_Billing_Logger();
		$settings     = new WH_Kartra_Billing\WH_Kartra_Billing_Options();
		$this->loader->add_action(
			'admin_enqueue_scripts',
			$plugin_admin,
			'enqueue_styles'
		);
		$this->loader->add_action(
			'admin_enqueue_scripts',
			$plugin_admin,
			'enqueue_scripts'
		);

		$this->loader->add_action(
			'admin_notices',
			$plugin_admin,
			'kartra_actions_admin_notice__error'
		);

		$this->loader->add_action(
			'network_admin_notices',
			$plugin_admin,
			'kartra_actions_admin_notice__error'
		);
		$this->loader->add_action(
			'wp_ajax_whkb_update_license_options',
			$plugin_admin,
			'whkb_update_license_options'
		);
		$this->loader->add_action(
			'admin_post_wh_kartra_billing_admin_logs_settings',
			$plugin_logs,
			'wh_kartra_billing_admin_logs_settings_save'
		);

		$this->loader->add_action(
			'admin_post_wh_kartra_billing_admin_settings',
			$settings,
			'wh_kartra_billing_admin_settings_save'
		);

		$this->loader->add_action(
			'network_admin_menu',
			$settings,
			'wh_kartra_billing_mu_menu',
			220
		);

		$this->loader->add_action(
			'network_admin_notices',
			$settings,
			'wh_kartra_billing_admin_notices'
		);

		$this->loader->add_filter(
			'admin_footer_text',
			$settings,
			'wh_kartra_billing_remove_footer_admin',
			100,
			1
		);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new WH_Kartra_Billing\WH_Kartra_Billing_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action(
			'wp_enqueue_scripts',
			$plugin_public,
			'enqueue_styles'
		);
		$this->loader->add_action(
			'wp_enqueue_scripts',
			$plugin_public,
			'enqueue_scripts'
		);

		$this->loader->add_action(
			'init',
			$plugin_public,
			'kartra_redirect_to_admin'
		);

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WH_Kartra_Billing_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
