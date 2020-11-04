<?php
/**
 * AnsPress admin add-ons page.
 *
 * @link       https://anspress.net
 * @since      4.0.0
 * @author     Rahul Aryan <rah12@live.com>
 * @package    AnsPress
 * @subpackage Admin Views
 * @since 4.1.5 Fixed form name.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_thickbox();

/**
 * Action hook triggered before loading addons page.
 *
 * @since 4.1.0
 */
do_action( 'ap_before_addons_page' );

$form_name = ap_sanitize_unslash( 'ap_form_name', 'r' );
?>

<div id="anspress" class="wrap">
	<h2 class="admin-title">
		<?php esc_html_e( 'AnsPress Add-ons', 'anspress-question-answer' ); ?>
		<div class="social-links clearfix">
			<a href="https://github.com/anspress/anspress" target="_blank">GitHub</a>
			<a href="https://wordpress.org/plugins/anspress-question-answer/" target="_blank">WordPress.org</a>
			<a href="https://twitter.com/anspress_io" target="_blank">@anspress_io</a>
			<a href="https://www.facebook.com/wp.anspress" target="_blank">Facebook</a>
		</div>
	</h2>

	<div class="clear"></div>

	<div class="ap-addons" method="POST">
		<div class="ap-addons-list">
				<?php
				/**
				 * Action hook called before AnsPress addons list in wp-admin addons page.
				 *
				 * @since 4.1.0
				 */
				do_action( 'ap_before_addons_list' );

				$i = 0;

				foreach ( (array) ap_get_addons() as $file => $data ) {
				?>

					<div class="ap-addon<?php echo $data['active'] ? ' active' : ''; ?> <?php echo $data['class']; ?>">
						<div class="ap-addon-image">
							<div class="ap-addon-tags">
								<?php if ( $data['active'] ) : ?>
									<span class="ap-addon-status"><?php esc_attr_e( 'Active', 'anspress-question-answer' ); ?> </span>
								<?php endif; ?>
								<?php echo $data['pro'] ? '<span class="ap-addon-pro">PRO</span>' : ''; ?>
							</div>

							<?php if ( $image = ap_get_addon_image( $data['id'] ) ) : ?>
								<img src="<?php echo esc_url( $image ); ?>" />
							<?php endif; ?>
						</div>
						<div class="ap-addon-detail">
							<h4>
								<?php echo esc_attr( $data['name'] ); ?>
							</h4>
							<p><?php echo esc_html( $data['description'] ); ?></p>

							<?php
								$args = wp_json_encode(
									array(
										'action'   => 'ap_toggle_addon',
										'__nonce'  => wp_create_nonce( 'toggle_addon' ),
										'addon_id' => $data['id'],
									)
								);
							?>

							<?php if ( $data['active'] ) : ?>
								<button class="button button-small button-primary ap-addon-toggle" apajaxbtn apquery="<?php echo esc_js( $args ); ?>"><?php esc_attr_e( 'Disable Addon', 'anspress-question-answer' ); ?></button>

								<?php
									// Show options button if have options.
								if ( ap_addon_has_options( $data['id'] ) ) {
									$url = add_query_arg(
										array(
											'action'    => 'ap_addon_options',
											'addon'     => $data['id'],
											'TB_iframe' => 'true',
											'width'     => '800',
											'height'    => '500',
										), admin_url( 'admin.php' )
									);

									echo '<a name="' . sprintf( esc_attr__( '%s Options', 'anspress-question-answer' ), $data['name'] ) . '" href="' . $url . '" class="button button-small thickbox">' . __( 'Options', 'anspress-question-answer' ) . '</a>';
								}
								?>
							<?php else : ?>
								<button class="button button-small button-primary ap-addon-toggle" apajaxbtn apquery="<?php echo esc_js( $args ); ?>"><?php esc_attr_e( 'Enable Addon', 'anspress-question-answer' ); ?></button>
							<?php endif; ?>
						</div>
					</div>
				<?php
				$i++;
				}

				/**
				 * Action hook called after AnsPress addons list in wp-admin addons page.
				 *
				 * @since 4.1.0
				 */
				do_action( 'ap_after_addons_list' );

				?>
		</div>
	</div>

</div>

<script type="text/javascript">
	(function($){
		AnsPress.on('toggleAddon', function(data){
			window.location.reload();
		})
	})(jQuery)
</script>

