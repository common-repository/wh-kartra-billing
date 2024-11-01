<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.waashero.com
 * @since             1.0.0
 * @package           WH_Kartra_Billing
 *
 * @wordpress-plugin
 * Plugin Name:       WH Kartra Billing
 * Plugin URI:        https://docs.waashero.com/docs/kartra/
 * Description:       Sync kartra billing actions with WordPress.
 * Version:           1.0.0
 * Author:            WaaSHero
 * Author URI:        www.waashero.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wh-kartra-billing
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WH_KARTAR_BILLING_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wh-kartra-billing-activator.php
 */
function activate_wh_kartra_billing() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wh-kartra-billing-activator.php';
	WH_Kartra_Billing_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wh-kartra-billing-deactivator.php
 */
function deactivate_wh_kartra_billing() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wh-kartra-billing-deactivator.php';
	WH_Kartra_Billing_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wh_kartra_billing' );
register_deactivation_hook( __FILE__, 'deactivate_wh_kartra_billing' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wh-kartra-billing.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wh_kartra_billing() {
	$plugin = Wu_Kartra_Billing\WH_Kartra_Billing::get_instance();
	$plugin->run();

}

add_action(
	'init',
	'run_wh_kartra_billing',
	9999,
	1
);
