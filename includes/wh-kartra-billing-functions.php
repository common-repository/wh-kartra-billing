<?php

if ( ! function_exists( 'jsonp_decode' ) ) {
	/**
	 * Contains Supporting Functions for plugin
	 *
	 * @since  1.0.0
	 */
	function jsonp_decode( $jsonp, $assoc = false ) {
		// PHP 5.3 adds depth as third parameter to json_decode.
		if ( $jsonp[0] !== '[' && $jsonp[0] !== '{' ) { // we have JSONP.
			$jsonp = substr( $jsonp, strpos( $jsonp, '(' ) );
		}
		return json_decode( trim( $jsonp, '();' ), $assoc );
	}
}

if ( ! function_exists( 'wh_kartra_billing_delete_user_and_sites' ) ) {
	/**
	 * deletes user and his sites.
	 *
	 * @param int $user_id
	 * @return array
	 */
	function wh_kartra_billing_delete_user_and_sites( $user_id = '' ) {

		if ( $user_id == '' ) {
			$user_id = get_current_user_id();
		}
		$time         = get_user_meta( $user_id, 'wh_kartra_cancel_subscription_after', true );
		$subscription = '';
		if ( $time && time() > strtotime( $time ) ) {
			// Get the sites
			$site_list = get_blogs_of_user( $user_id );
			do_action( 'wh_kartra_before_delete_site', $user_id, $subscription, $site_list );

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
				rrmdir( $dir );

			} // end foreach;

			wpmu_delete_user( $user_id );
			$params = array(
				'user'  => $user_id,
				'sites' => $site_list,
			);
			do_action( 'wh_kartra_after_delete_site', $params );

			return array(
				'status'  => 200,
				'message' => esc_html__( 'User site data deleted successfully.', 'wh-kartra-billing' ),
			);
		}
	}
}


if ( ! function_exists( 'rrmdir' ) ) {
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
						rrmdir( $dir . '/' . $object );
					} else {
						unlink( $dir . '/' . $object );
					}
				}
			}
			reset( $objects );
			rmdir( $dir );
		}

	}
}

if ( ! function_exists( 'wu_kartra_billing_deslash' ) ) {
	/**
	 * Deslashed double slashes.
	 *
	 * @param string $content content to delash.
	 * @return string $content delashed content.
	 * @since  1.1.0
	 */
	function wu_kartra_billing_deslash( $content ) {
		// Note: \\\ inside a regex denotes a single backslash.

		/*
		* Replace one or more backslashes followed by a single quote with
		* a single quote.
		*/
		$content = preg_replace( "/\\\+'/", "'", $content );

		/*
		* Replace one or more backslashes followed by a double quote with
		* a double quote.
		*/
		$content = preg_replace( '/\\\+"/', '"', $content );

		// Replace one or more backslashes with one backslash.
		$content = preg_replace( '/\\\+/', '\\', $content );

		return $content;
	}
}

$this->loader->add_action(
	'wu_rest_api_after_creating_site',
	'',
	'wu_kartra_update_user_meta',
	10,
	1
);

if ( ! function_exists( 'wu_kartra_update_user_meta' ) ) {
	/**
	 * Update User Meta
	 *
	 * @param array $params params to update user meta.
	 * @return void
	 * @since  1.1.0
	 */
	function wu_kartra_update_user_meta( $params ) {
		if ( ! empty( $params['user']['user_id'] ) && 'kartra' == $params['user']['user_source'] ) {
			update_user_meta( $params['user']['user_id'], 'wu_kartra_user_id', $params['user']['source_id'] );
		}
	}
}

if ( ! function_exists( 'wu_kertar_get_user_by_metadata' ) ) {
	/**
	 * Get User By MetaData.
	 *
	 * @param string $meta_key meta key to search.
	 * @param string $meta_value meta value to search.
	 * @return array
	 */
	function wu_kertar_get_user_by_metadata( $meta_key, $meta_value ) {
		$wp_user_query = new WP_User_Query(
			array(

				'meta_query' => array(
					array(
						'key'   => $meta_key,
						'value' => $meta_value,
					),
				),
			)
		);
		return reset( $wp_user_query->get_results() );

	}
}

$this->loader->add_filter(
	'wu_gateway_integration_button_stripe',
	'',
	'change_wu_stripe_button',
	100,
	1
);

if ( function_exists( 'change_wu_stripe_button' ) ) {
	/**
	 * Redirect to Kartra IPN.
	 *
	 * @param string $html
	 * @return void
	 */
	function change_wu_stripe_button( $html ) {
		$html .= '<script> var old_element = document.getElementById("stripe-checkout-button"); var new_element = old_element.cloneNode(true); old_element.parentNode.replaceChild(new_element, old_element); var checkoutButton = document.getElementById("stripe-checkout-button"); checkoutButton.addEventListener("click", function () { window.location = "https://go.reigrow.com/ccupdate"; }); console.log( checkoutButton ); </script>';
		return $html;
	}
}

/**
 *  Debugger
 *
 *  @param data
 *  @return void
 */

if ( ! function_exists( '_debug' ) ) {

	function _debug( $data ) {
		echo '<pre/>';
		print_r( $data );
		exit;
	}
}


if ( ! function_exists( 'wh_kartra_api_settings' ) ) {

	/**
	 *  WH Kartra API settings
	 *
	 *  @param string $key
	 *  @param string $is_boolean
	 *  @param string $default_value
	 *  @return string|array|boolean
	 */

	function wh_kartra_api_settings( $key, $is_boolean = '', $default_value = '' ) {

		$wh_kartra_billing_admin_settings = get_site_option( 'wh_kartra_billing_admin_settings', array() );

		if ( $is_boolean === true ) {
			return ( ! empty( $wh_kartra_billing_admin_settings[ 'wh_kartra_' . $key ] ) ? true : false );
		} else {
			return ( ! empty( $wh_kartra_billing_admin_settings[ 'wh_kartra_' . $key ] ) ? $wh_kartra_billing_admin_settings[ 'wh_kartra_' . $key ] : $default_value );
		}
	}
}





