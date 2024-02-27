<?php
/**
 * AnsPress admin features page.
 *
 * @link       https://anspress.net
 * @author     Rahul Aryan <rah12@live.com>
 * @package    AnsPress
 * @subpackage Admin Views
 * @since      4.2.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$form_name = ap_sanitize_unslash( 'ap_form_name', 'r' );

/**
 * Internal function for sorting features array.
 *
 * @param array $a Feature array.
 * @return int
 * @since 4.2.0
 */
function _ap_short_addons_list( $a ) { // phpcs:ignore
	return $a['active'] ? -1 : 1;
}
?>


<div class="ap-addons">
	<div class="ap-addons-list">
		<?php
		/**
		 * Action hook called before AnsPress addons list in wp-admin addons page.
		 *
		 * @since 4.1.0
		 */
		do_action( 'ap_before_addons_list' );

		$i              = 0;
		$addons         = ap_get_addons();
		$active_addons  = count( wp_list_filter( $addons, array( 'active' => true ) ) );
		$first_disabled = '';

		usort( $addons, '_ap_short_addons_list' );

		foreach ( (array) $addons as $file => $data ) {
			if ( $active_addons > 0 && empty( $first_disabled ) && ! $data['active'] ) {
				$first_disabled = $file;
			}
			?>

			<?php if ( $file === $first_disabled ) : ?>
				<div class="ap-addon-sep"></div>
			<?php endif; ?>

			<div class="ap-addon<?php echo $data['active'] ? ' active' : ''; ?> <?php echo esc_attr( $data['class'] ); ?>">
				<div class="ap-addon-image">
					<?php $image = ap_get_addon_image( $data['id'] ); ?>

					<?php if ( $image ) : ?>
						<img src="<?php echo esc_url( $image ); ?>" />
					<?php endif; ?>
				</div>
				<div class="ap-addon-detail">
					<h4>
						<?php echo esc_attr( $data['name'] ); ?>

						<div class="ap-addon-tags">
							<?php if ( $data['active'] ) : ?>
								<span class="ap-addon-status"><?php esc_attr_e( 'Active', 'anspress-question-answer' ); ?> </span>
							<?php endif; ?>
							<?php echo $data['pro'] ? '<span class="ap-addon-pro">PRO</span>' : ''; ?>
						</div>
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
						<button class="button button-small button-primary ap-addon-toggle" apajaxbtn apquery="<?php echo esc_js( $args ); ?>"><?php esc_attr_e( 'Disable', 'anspress-question-answer' ); ?></button>
					<?php else : ?>
						<button class="button button-small button-primary ap-addon-toggle" apajaxbtn apquery="<?php echo esc_js( $args ); ?>"><?php esc_attr_e( 'Enable', 'anspress-question-answer' ); ?></button>
					<?php endif; ?>
				</div>
			</div>

			<?php
			++$i;
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

<script type="text/javascript">
	(function($){
		AnsPress.on('toggleAddon', function(data){
			window.location.reload();
		})
	})(jQuery)
</script>
