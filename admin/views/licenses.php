<?php
/**
 * View licenses page for AnsPress.
 *
 * @package AnsPress
 * @author Rahul Aryan <rah12@live.com>
 * @copyright 2014 - Rahul Aryan
 */

// Save license key if form is submitted.
AP_License::ap_product_license();


$fields   = ap_product_license_fields();
$licenses = get_option( 'anspress_license' );
?>

<div class="wrap">
	<h2>
		<?php esc_html_e( 'Licenses', 'anspress-question-answer' ); ?>
	</h2>
	<p class="lead"><?php _e( 'License keys for AnsPress products, i.e. extensions and themes.', 'anspress-question-answer' ); ?></p>

	<?php if ( ! empty( $fields ) ) : ?>
				<form method="post" action="<?php echo admin_url( 'admin.php?page=anspress_licenses' ); ?>">
						<table class="form-table">
								<tbody>
					<?php foreach ( $fields as $slug => $prod ) : ?>
												<tr valign="top">
							<th scope="row" valign="top"><?php echo $prod['name']; ?></th>
														<td>
								<input id="ap_license_<?php echo $slug; ?>" name="ap_license_<?php echo $slug; ?>" type="text" class="regular-text" value="<?php esc_attr_e( @$licenses[ $slug ]['key'] ); ?>" placeholder="<?php printf( __( 'Enter license key for %s' ), $prod['name'] ); ?>" />

								<?php if ( ! empty( $licenses[ $slug ]['key'] ) ) { ?>
									<?php if ( $licenses[ $slug ]['status'] !== false && $licenses[ $slug ]['status'] == 'valid' ) { ?>
										<span class="ap-license-check"><i class="apicon-check"></i><?php _e( 'active', 'anspress-question-answer' ); ?></span><br />
										<input type="submit" class="button-secondary" name="ap_license_deactivate_<?php echo $slug; ?>" value="<?php _e( 'Deactivate License' ); ?>"/>
									<?php } else { ?>
										<input type="submit" class="button-secondary" name="ap_license_activate_<?php echo $slug; ?>" value="<?php _e( 'Activate License' ); ?>"/>
									<?php } ?>
								<?php } ?>
														</td>
												</tr>
					<?php endforeach; ?>
								</tbody>
						</table>
						<input type="hidden" name="action" value="ap_product_license">
						<input type="submit" name="save_licenses" class="button button-primary" value="<?php _e( 'Save', 'anspress-question-answer' ); ?>" />
						<?php wp_nonce_field( 'ap_licenses_nonce', '__nonce' ); ?>
				</form>
	<?php else : ?>
		<?php _e( 'No license yet.' ); ?>
	<?php endif; ?>
</div>
