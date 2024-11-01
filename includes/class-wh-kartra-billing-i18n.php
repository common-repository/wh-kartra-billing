<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       www.waashero.com
 * @since      1.0.0
 *
 * @package    WH_Kartra_Billing
 * @subpackage WH_Kartra_Billing/includes
 */
namespace Wu_Kartra_Billing\WH_Kartra_Billing;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WH_Kartra_Billing
 * @subpackage WH_Kartra_Billing/includes
 * @author     J Hanlon <waashero@info.com>
 */
class WH_Kartra_Billing_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wh-kartra-billing',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
