<?php
/**
 * WH_Kartra_Billing  Options
 *
 * Displays the WH_Kartra_Billing  Options.
 *
 * @author   J Hanlon
 * @category Admin
 * @package  WH_Kartra_Billing Options /Plugin Options
 * @version  1.0.0
 */

namespace Wu_Kartra_Billing\WH_Kartra_Billing;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WH_Kartra_Billing_opions
 *
 * @since 1.0.0
 */
class WH_Kartra_Billing_Options {
	public $page_tab;

	public static $network_wide;
	/**
	 * Hook in tabs.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( is_multisite() && is_network_admin() ) {
			$this->page_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'features';
		}

	}

	/**
	 * Adds admin notices
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wh_kartra_billing_admin_notices() {
		$screen = get_current_screen();
		if ( $screen->base != 'wh-kartra-billing-options' && $screen->base != 'settings_page_wh-kartra-billing-options' && $screen->base != 'settings_page_wh-kartra-billing-options-network' && $screen->base != 'toplevel_page_wh-kartra-billing-options-network' ) {

			return;
		}

		if ( isset( $_POST['wh_kartra_billing_settings_submit'] ) || ( isset( $_GET['settings-updated'] ) && sanitize_text_field( $_GET['settings-updated'] ) == 'true' ) ) {
			$class   = 'notice notice-success is-dismissible';
			$message = esc_html__( 'Settings Saved', 'wh-kartra-billing' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} elseif ( isset( $_POST['wh_kartra_billing_settings_submit'] ) || ( isset( $_GET['token-updated'] ) && sanitize_text_field( $_GET['token-updated'] ) == 'true' ) ) {
			$class   = 'notice notice-success is-dismissible';
			$message = esc_html__( 'Settings Saved , Token created And Library Activated', 'wh-kartra-billing' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} elseif ( isset( $_POST['wh_kartra_billing_see_log'] ) || ( isset( $_GET['logs-settings-updated'] ) && sanitize_text_field( $_GET['logs-settings-updated'] ) == 'true' ) ) {
			$class   = 'notice notice-success is-dismissible';
			$message = esc_html__( 'Logs Settings Saved', 'wh-kartra-billing' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} elseif ( isset( $_POST['wh_kartra_billing_see_log'] ) || ( isset( $_GET['logs-settings-updated'] ) && sanitize_text_field( $_GET['logs-settings-updated'] ) == 'false' ) ) {
			$class   = 'notice notice-error is-dismissible';
			$message = esc_html__( 'Logs Settings Not Saved', 'wh-kartra-billing' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} elseif ( isset( $_POST['wh_kartra_billing_advance_settings_submit'] ) || ( isset( $_GET['advance-settings-updated'] ) && sanitize_text_field( $_GET['advance-settings-updated'] ) == 'true' ) ) {
			$class   = 'notice notice-success is-dismissible';
			$message = esc_html__( 'Advance Settings Saved', 'wh-kartra-billing' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} elseif ( isset( $_POST['wh_kartra_billing_advance_settings_submit'] ) || ( isset( $_GET['advance-settings-updated'] ) && sanitize_text_field( $_GET['advance-settings-updated'] ) == 'false' ) ) {
			$class   = 'notice notice-error is-dismissible';
			$message = esc_html__( 'Advance Settings Not Saved', 'wh-kartra-billing' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		} elseif ( ( isset( $_GET['nonce-verified'] ) && sanitize_text_field( $_GET['nonce-verified'] ) == 'false' ) ) {
			$class   = 'notice notice-error is-dismissible';
			$message = esc_html__( 'Security Issues', 'wh-kartra-billing' );
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
		}
	}

	/**
	 * Advance settings save
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wh_kartra_billing_admin_settings_save() {

		$uploads = 'false';
		$nonce   = isset( $_POST['_wpnonce'] ) ? sanitize_text_field( $_POST['_wpnonce'] ) : '';
		$action  = 'wh_kartra_create_token_security';

		if ( isset( $_POST['wh_kartra_billing_admin_settings_submit'] ) && wp_verify_nonce( $nonce, $action ) ) {

			$wh_kartra_billing_admin_settings = get_site_option( 'wh_kartra_billing_admin_settings', array() );
			$wh_kartra_billing_admin_settings['wh_kartra_create_user_and_site_action'] = isset( $_POST['wh_kartra_create_user_and_site_action'] ) ? sanitize_text_field( $_POST['wh_kartra_create_user_and_site_action'] ) : 'buy_product';
			$wh_kartra_billing_admin_settings['wh_kartra_delete_user_and_site_action'] = isset( $_POST['wh_kartra_delete_user_and_site_action'] ) ? sanitize_text_field( $_POST['wh_kartra_delete_user_and_site_action'] ) : 'cancel_subscription';
			$wh_kartra_billing_admin_settings['wh_kartra_delete_site_after_action']    = isset( $_POST['wh_kartra_delete_site_after_action'] ) ? sanitize_text_field( $_POST['wh_kartra_delete_site_after_action'] ) : 'cancel_subscription_after';
			$wh_kartra_billing_admin_settings['wh_kartra_halt_site_action']            = isset( $_POST['wh_kartra_halt_site_action'] ) ? sanitize_text_field( $_POST['wh_kartra_halt_site_action'] ) : 'halt_site';
			$wh_kartra_billing_admin_settings['wh_kartra_revert_halt_site_action']     = isset( $_POST['wh_kartra_revert_halt_site_action'] ) ? sanitize_text_field( $_POST['wh_kartra_revert_halt_site_action'] ) : 'revert_halt_site';
			$wh_kartra_billing_admin_settings['wh_kartra_remove_authorization']        = isset( $_POST['wh_kartra_remove_authorization'] ) && sanitize_text_field( $_POST['wh_kartra_remove_authorization'] ) === 'on' ? true : false;

			// Clear the old token to generate new one
			if ( ! empty( $_POST['wh_kartra_refresh_token'] ) && sanitize_text_field( $_POST['wh_kartra_refresh_token'] ) === 'on' ) {
				$wh_kartra_billing_admin_settings['wh_kartra_api_token'] = '';
			}

			update_site_option( 'wh_kartra_billing_admin_settings', $wh_kartra_billing_admin_settings );

			$wh_kartra_billing_options = apply_filters( 'wh_kartra_billing_update_admin_settings', $wh_kartra_billing_options );
		} else {
			wp_safe_redirect( add_query_arg( 'nonce-verified', $uploads, sanitize_text_field( $_POST['_wp_http_referer'] ) ) );
			exit;
		}

		wp_safe_redirect( add_query_arg( 'settings-updated', true, sanitize_text_field( $_POST['_wp_http_referer'] ) ) );
		exit;
	}



	/**
	 * Add plugin's network menu
	 *
	 * @since 1.0.0
	 */
	public function wh_kartra_billing_mu_menu() {
		$admin_menu_title = get_site_option( 'wh_kartra_billing_network_menu_title', 'WH Kartra Billing' );
		$admin_menu_title = apply_filters( 'wh_kartra_billing_network_admin_menu_title', $admin_menu_title );
		$hook_suffix      = add_menu_page(
			esc_html__( $admin_menu_title, 'wh-kartra-billing' ),
			esc_html__( $admin_menu_title, 'wh-kartra-billing' ),
			'manage_network_options',
			'wh-kartra-billing-options',
			array( $this, 'wh_kartra_billing_mu_options' ),
			'dashicons-welcome-widgets-menus'
		);
	}

	/**
	 * Setting page data
	 *
	 * @since 1.0.0
	 */
	public function wh_kartra_billing_mu_options() {

		?>
		<div class="wrap wh-kartra-billing-settings-wrapper">
			<div class="hidden sm:block">
				<div class="border-b border-gray-200">
				<nav class="-mb-px flex space-x-8" aria-label="Tabs">
					<?php
					$wh_kartra_billing_sections = $this->wh_kartra_billing_get_mu_setting_sections();
					foreach ( $wh_kartra_billing_sections as $key => $wh_kartra_billing_section ) {
						?>
					<!-- Current: "border-indigo-500 text-indigo-600", Default: "border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300" -->
					<a href="?page=wh-kartra-billing-options&tab=<?php echo $key; ?>" class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm <?php echo esc_attr( $this->page_tab == $key ? 'active text-blue-600 border-b-2 border-blue-600 active dark:text-blue-500 dark:border-blue-500 group' : '' ); ?>">
					<!--
						Heroicon name: mini/user

						Current: "text-indigo-500", Default: "text-gray-400 group-hover:text-gray-500"
					-->
					<svg class="<?php echo esc_attr( $this->page_tab == $key ? 'mr-2 w-5 h-5 text-blue-600 dark:text-blue-500' : 'text-gray-400 group-hover:text-gray-500 -ml-0.5 mr-2 h-5 w-5' ); ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
						<path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z" />
					</svg>
					<span><?php esc_html_e( $wh_kartra_billing_section['title'], 'wh-kartra-billing' ); ?></span>
					</a>
					<?php } ?>
				</nav>
				</div>
			</div>
			<div class="wh-kartra-billing-tab-innerbox">

				<?php
				foreach ( $wh_kartra_billing_sections as $key => $wh_kartra_billing_section ) {
					if ( $this->page_tab == $key ) {
						$key = apply_filters( 'wh_kartra_billing_tab_title', $key );
						include 'templates/' . $key . '.php';
					}
				}
				?>
			</div>
		</div>

		<?php
	}


	/**
	 * WH Kartra Billing Elementor Settings Sections
	 *
	 * @since 1.0.0
	 * @return mixed|void
	 */
	public function wh_kartra_billing_get_mu_setting_sections() {

		$features = 'Features';
		$features = apply_filters( 'wh_kartra_billing_api_keys_tab_title', $features );

		$logs = 'Logs & Error Files';
		$logs = apply_filters( 'wh_kartra_billing_logs_tab_title', $logs );

		$settings = 'settings';
		$settings = apply_filters( 'wh_kartra_billing_settings_tab_title', $settings );

		$wh_kartra_billing_settings_sections = array(

			'features' => array(
				'title' => esc_html__( $features, 'wh-kartra-billing' ),
				'icon'  => 'fa-hashtag',
			),

			'settings' => array(
				'title' => esc_html__( $settings, 'wh-kartra-billing' ),
				'icon'  => 'fa-settings',
			),
			'logs'     => array(
				'title' => esc_html__( $logs, 'wh-kartra-billing' ),
				'icon'  => 'fa-hashtag',
			),
		);

		return apply_filters( 'wh_kartra_billing_settings_sections', $wh_kartra_billing_settings_sections );
	}

	/**
	 * Add footer branding
	 *
	 * @since 1.0.0
	 * @param $footer_text
	 * @return mixed
	 */
	function wh_kartra_billing_remove_footer_admin( $footer_text ) {
		if ( isset( $_GET['page'] ) && ( sanitize_text_field( $_GET['page'] ) == 'wh-kartra-billing-options' ) ) {
			return _e(
				'<p>Powered by WordPress Built & Supported by <a href="https://waashero.com" target="_blank">WaaS Hero</a></p>',
				'wh-kartra-billing'
			);
		} else {
			return $footer_text;
		}
	}
}
