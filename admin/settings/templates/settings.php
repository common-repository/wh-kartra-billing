<?php
/**
 * General Options
 *
 * @package settings
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;

$wh_kartra_billing_admin_settings      = get_site_option( 'wh_kartra_billing_admin_settings', array() );
$wh_kartra_create_user_and_site_action = ! empty( $wh_kartra_billing_admin_settings['wh_kartra_create_user_and_site_action'] ) ? $wh_kartra_billing_admin_settings['wh_kartra_create_user_and_site_action'] : 'buy_product';
$wh_kartra_delete_user_and_site_action = ! empty( $wh_kartra_billing_admin_settings['wh_kartra_delete_user_and_site_action'] ) ? $wh_kartra_billing_admin_settings['wh_kartra_delete_user_and_site_action'] : 'cancel_subscription';
$wh_kartra_delete_site_after_action    = ! empty( $wh_kartra_billing_admin_settings['wh_kartra_delete_site_after_action'] ) ? $wh_kartra_billing_admin_settings['wh_kartra_delete_site_after_action'] : 'cancel_subscription_after';
$wh_kartra_halt_site_action            = ! empty( $wh_kartra_billing_admin_settings['wh_kartra_halt_site_action'] ) ? $wh_kartra_billing_admin_settings['wh_kartra_halt_site_action'] : 'halt_site';
$wh_kartra_revert_halt_site_action     = ! empty( $wh_kartra_billing_admin_settings['wh_kartra_revert_halt_site_action'] ) ? $wh_kartra_billing_admin_settings['wh_kartra_revert_halt_site_action'] : 'revert_halt_site';
$wh_kartra_remove_authorization        = ! empty( $wh_kartra_billing_admin_settings['wh_kartra_remove_authorization'] ) ? true : false;
$wh_kartra_encryption_key              = ! empty( $wh_kartra_billing_admin_settings['wh_kartra_encryption_key'] ) ? $wh_kartra_billing_admin_settings['wh_kartra_encryption_key'] : 'aBC5v1sOKVabdEitdSBrnu59nfNfbwkedkJVNrbosTw=';
$wh_kartra_app_id                      = ! empty( $wh_kartra_billing_admin_settings['wh_kartra_app_id'] ) ? $wh_kartra_billing_admin_settings['wh_kartra_app_id'] : '';
$wh_kartra_api_token                   = ! empty( $wh_kartra_billing_admin_settings['wh_kartra_api_token'] ) ? $wh_kartra_billing_admin_settings['wh_kartra_api_token'] : '';


if ( empty( $wh_kartra_app_id ) ) {

	$wh_kartra_app_id                                     = uniqid();
	$wh_kartra_billing_admin_settings['wh_kartra_app_id'] = $wh_kartra_app_id;
	update_site_option( 'wh_kartra_billing_admin_settings', $wh_kartra_billing_admin_settings );

}

// Check token validation
$is_token_valid = true;
if ( ! empty( $wh_kartra_api_token ) ) {

	$config = Configuration::forSymmetricSigner(
	 // You may use any HMAC variations (256, 384, and 512)
		new Sha256(),
		// replace the value below with a key of your own!
		InMemory::base64Encoded( $wh_kartra_encryption_key )
	);

	try {

		$token = $config->parser()->parse( $wh_kartra_api_token );
		assert( $token instanceof Token );
		$validate = $config->validator()->validate( $token, new IssuedBy( $wh_kartra_app_id ) );

		if ( ! $validate || $token->isExpired() ) {
			$is_token_valid = false;
		}
	} catch ( \Exception $e ) {

		 $is_token_valid = false;
	}
}


// If no API token exist or got expire create and save new one

if ( empty( $wh_kartra_api_token ) || ! $is_token_valid ) {

	$signer              = new Sha256();
	$wh_kartra_api_token = ( new Builder() )->setIssuedAt( time() )
				->setExpiration( time() + 604800 ) // maximum: 604,800 , 7 days
				->setIssuer( $wh_kartra_app_id ) // App ID
				->sign( $signer, $wh_kartra_encryption_key )
				->getToken();

	$wh_kartra_api_token = $wh_kartra_billing_admin_settings['wh_kartra_api_token'] = $wh_kartra_api_token->toString();
	update_site_option( 'wh_kartra_billing_admin_settings', $wh_kartra_billing_admin_settings );
}

?>

<div id="wh-kartra-billing-advance-options" class="card">

	<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST">
	
	   <input type="hidden" name="action" value="wh_kartra_billing_admin_settings">
		<?php wp_nonce_field( 'wh_kartra_create_token_security' ); ?>
		<table class="form-table wh-kartra-settings">
			<tbody>
				
			  <tr valign="top" style="display:none">
					<th scope="row" >
						<label for="wh_kartra_action_title">
							<?php esc_html_e( 'App ID', 'wh-kartra-billing' ); ?>
							<p class="make-api-description"><?php esc_html_e( 'App ID being used in API access token', 'wh-kartra-billing' ); ?></p>
						</label>
					</th>
					<td class="wh-kartra-app-id-box">
					<label  for="wh_kartra_app_id">
						<input type="text" required name="wh_kartra_app_id" value="<?php esc_html_e( $wh_kartra_app_id, 'wh-kartra-billing' ); ?>" placeholder ="<?php esc_html_e( $wh_kartra_app_id, 'wh-kartra-billing' ); ?>" class="" id="wh_kartra_app_id"/>
					</label>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" >
						<label for="wh_kartra_action_title">
							<?php esc_html_e( 'Create user & site action', 'wh-kartra-billing' ); ?>
							<p class="make-api-description"><?php esc_html_e( 'API endpoint to handle site and user creations', 'wh-kartra-billing' ); ?></p>
						</label>
					</th>
					<td class="wh-kartra-create-site-and-user-action-box">
					<label  for="wh_kartra_create_user_and_site_action">
						<input type="text" required name="wh_kartra_create_user_and_site_action" value="<?php esc_html_e( $wh_kartra_create_user_and_site_action, 'wh-kartra-billing' ); ?>" placeholder ="<?php esc_html_e( $wh_kartra_create_user_and_site_action, 'wh-kartra-billing' ); ?>" class="" id="wh_kartra_create_user_and_site_action"/>
						<p class="make-api-description"><?php echo esc_url_raw ( get_rest_url( null, '/waashero/v0/kartra-create-user-and-site' ) ); ?></p>
					</label>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" >
						<label for="wh_kartra_action_title">
							<?php esc_html_e( 'Delete user & site action', 'wh-kartra-billing' ); ?>
							<p class="make-api-description"><?php esc_html_e( 'API endpoint to delete user & site', 'wh-kartra-billing' ); ?></p>
						</label>
					</th>
					<td class="wh-kartra-delete-site-action-box">
					<label  for="wh_kartra_action_title">
						<input type="text" required name="wh_kartra_delete_user_and_site_action" value="<?php esc_html_e( $wh_kartra_delete_user_and_site_action, 'wh-kartra-billing' ); ?>" placeholder ="<?php esc_html_e( $wh_kartra_delete_user_and_site_action, 'wh-kartra-billing' ); ?>" class="" id="wh_kartra_delete_user_and_site_action"/>
						<p class="make-api-description"><?php echo esc_url_raw ( get_rest_url( null, '/waashero/v0/delete-site-kartra' ) ); ?></p>
					</label>
					</td>
				</tr>
		
				<tr valign="top">
					<th scope="row" >
						<label for="wh_kartra_action_title">
							<?php esc_html_e( 'Delete site after action', 'wh-kartra-billing' ); ?>
							<p class="make-api-description"><?php esc_html_e( 'API endpoint to set a specific time to delete a site', 'wh-kartra-billing' ); ?></p>
						</label>
					</th>
					<td class="wh-kartra-delete-site-after-action-box">
					<label  for="wh_kartra_action_title">
						<input type="text" required name="wh_kartra_delete_site_after_action" value="<?php esc_html_e( $wh_kartra_delete_site_after_action, 'wh-kartra-billing' ); ?>" placeholder ="<?php esc_html_e( $wh_kartra_delete_site_after_action, 'wh-kartra-billing' ); ?>" class="" id="wh_kartra_delete_site_after_action"/>
						<p class="make-api-description"><?php echo esc_url_raw ( get_rest_url( null, '/waashero/v0/delete-site-after-kartra' ) ); ?></p>
					</label>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" >
						<label for="wh_kartra_action_title">
							<?php esc_html_e( 'Halt site action', 'wh-kartra-billing' ); ?>
							<p class="make-api-description"><?php esc_html_e( 'API endpoint to halt a site on payment failure or on any other action hook', 'wh-kartra-billing' ); ?></p>
						</label>
					</th>
					<td class="wh-kartra-halt-site-action-box">
					<label  for="wh_kartra_action_title">
						<input type="text" required name="wh_kartra_halt_site_action" value="<?php esc_html_e( $wh_kartra_halt_site_action, 'wh-kartra-billing' ); ?>" placeholder ="<?php esc_html_e( $wh_kartra_halt_site_action, 'wh-kartra-billing' ); ?>" class="" id="wh_kartra_halt_site_action"/>
						<p class="make-api-description"><?php echo esc_url_raw ( get_rest_url( null, '/waashero/v0/halt-site-kartra' ) ); ?></p>
					</label>
					</td>
				</tr>
			
				<tr valign="top">
					<th scope="row" >
						<label for="wh_kartra_action_title">
							<?php esc_html_e( 'Revert halt site action', 'wh-kartra-billing' ); ?>
							<p class="make-api-description"><?php esc_html_e( 'API endpoint to revert halted site', 'wh-kartra-billing' ); ?></p>
						</label>
					</th>
					<td class="wh-kartra-revert-halt-site-action-box">
					<label  for="wh_kartra_action_title">
						<input type="text" required name="wh_kartra_revert_halt_site_action" value="<?php esc_html_e( $wh_kartra_revert_halt_site_action, 'wh-kartra-billing' ); ?>" placeholder ="<?php esc_html_e( $wh_kartra_revert_halt_site_action, 'wh-kartra-billing' ); ?>" class="" id="wh_kartra_revert_halt_site_action"/>
						<p class="make-api-description"><?php echo esc_url_raw ( get_rest_url( null, '/waashero/v0/revert-halt-site-kartra' ) ); ?></p>
					</label>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" >
						<label for="wh_kartra_action_title">
							<?php esc_html_e( 'Encryption key', 'wh-kartra-billing' ); ?>
							<p class="make-api-description"><?php esc_html_e( 'Encryption key used in authorization token', 'wh-kartra-billing' ); ?></p>
						</label>
					</th>
					<td class="wh-kartra-encryption-key-box">
					<label  for="wh_kartra_encryption_key">
						<input type="text" max="255" required name="wh_kartra_encryption_key" value="<?php esc_html_e( $wh_kartra_encryption_key, 'wh-kartra-billing' ); ?>" placeholder ="<?php esc_html_e( $wh_kartra_encryption_key, 'wh-kartra-billing' ); ?>" class="" id="wh_kartra_encryption_key"/>
					</label>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row" >
						<label for="th_elementor_client_allowed_extensions">
							<?php esc_html_e( 'Allow 3rd party integration without authorization', 'wh-kartra-billing' ); ?>
						</label>
						<p class="make-api-description"><?php esc_html_e( 'If checked it will allow 3rd parties to use API endpoints without authorizaton token.', 'wh-kartra-billing' ); ?></p>
					</th>
					<td class="wh-kartra-authorization-token-box">
					<label class="switch" for="wh_kartra_remove_authorization">
						<input type="checkbox"  name="wh_kartra_remove_authorization" class="" id="wh_kartra_remove_authorization"
						<?php
						if ( true === $wh_kartra_remove_authorization ) {
							?>
							 checked="checked" <?php } ?> 
						/>
						
					</label>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" >
						<label for="wh_kartra_action_title">
							<?php esc_html_e( 'API Authorizaton Token', 'wh-kartra-billing' ); ?>
							<p class="make-api-description"><?php esc_html_e( 'Use this token to make secure connection with APIs', 'wh-kartra-billing' ); ?></p>
						</label>
					</th>
					<td class="wh-kartra-api-token-box">
					<label  for="wh_kartra_api_token">
					  <?php esc_html_e( $wh_kartra_api_token, 'wh-kartra-billing' ); ?>
					</label>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" >
						<label for="wh_kartra_action_title">
							<?php esc_html_e( 'Refresh Token', 'wh-kartra-billing' ); ?>
							<p class="make-api-description"><?php esc_html_e( 'Check and submit the setting to refresh the API token', 'wh-kartra-billing' ); ?></p>
						</label>
					</th>
					<td class="wh-kartra-refresh-token-box">
					<label  for="wh_kartra_refresh_token">
					  <input type="checkbox"  name="wh_kartra_refresh_token" class="" id="wh_kartra_refresh_token" />
					</label>
					</td>
				</tr>

				<?php do_action( 'wh_kartra_billing_admin_settings', $wh_kartra_billing_admin_settings ); ?>
			</tbody>
		</table>
		<?php
			submit_button( esc_html__( 'Save Settings', 'wh-kartra-billing' ), 'primary', 'wh_kartra_billing_admin_settings_submit' );
		?>
	</form>
</div>


<?php
