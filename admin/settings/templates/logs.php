<?php
/**
 * General Options
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
use Wu_Kartra_Billing\WH_Kartra_Billing\WH_Kartra_Billing_Logger as WH_Kartra_Billing_Logger;
global $wp_filesystem, $wpdb;
$pad_spaces                = 45;
$wh_kartra_billing_options = get_site_option( 'wh_kartra_billing_log_options', array() );

$logs_list = glob( WH_Kartra_Billing_Logger::get_logs_folder() . '*.log' );
$file      = ! empty( $wh_kartra_billing_options['wh_kartra_billing_log_select'] ) ? $wh_kartra_billing_options['wh_kartra_billing_log_select'] : '';
$contents  = $file && file_exists( $file ) ? file_get_contents( $file ) : '--';

?>

<div id="wh-kartra-billing-general-options" class="card">
	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
		<input type="hidden" name="action" value="wh_kartra_billing_admin_logs_settings">
		<?php wp_nonce_field( 'wh_kartra_billing_admin_settings_action', 'wh_kartra_billing_admin_settings_field' ); ?>
		<?php echo str_pad( esc_html__( 'Logs Directory', 'wh-kartra-billing' ) . ':', $pad_spaces ); ?><?php echo is_writable( WH_Kartra_Billing_Logger::get_logs_folder() ) ? 'Writable' : 'Not Writable' . "\n"; ?>
		<div class="alignright">
		 <?php do_action( 'wh_kartra_billing_logs_before_select' ); ?>
			<select name="wh_kartra_billing_log_select" style="">

				<option><?php esc_html_e( 'Select a Log File', 'wh-kartra-billing' ); ?></option>

				<?php foreach ( $logs_list as $file_path ) : ?>
				<option value="<?php echo esc_url_raw( $file_path ); ?>" <?php selected( $file == $file_path ); ?>><?php echo esc_url_raw( $file_path ); ?></option>
				<?php endforeach; ?>

			</select>

			<button class="button-primary" id="wh_kartra_billing_see_log" name="wh_kartra_billing_see_log" value="see" type="submit"><?php esc_html_e( 'See Log File', 'wh-kartra-billing' ); ?></button>
			<?php do_action( 'wh_kartra_billing_logs_after_button' ); ?>
		</div>
			<div class="clear"></div>

		<br>
	</form>

	<textarea  onclick="this.focus();this.select()" readonly="readonly" wrap="off" style="width: 100%; height: 600px; font-family: monospace;"><?php esc_html_e( $contents, 'wh-kartra-billing' ); ?></textarea>
	<?php do_action( 'wh_kartra_billing_logs_after_textarea' ); ?>
</div>
