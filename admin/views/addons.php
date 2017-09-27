<?php
/**
 * AnsPress admin add-ons page.
 *
 * @link       https://anspress.io
 * @since      4.0
 * @author     Rahul Aryan <support@anspress.io>
 * @package    AnsPress
 * @subpackage Admin Views
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $wpdb;

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
			$i = 0;
			$active = ap_isset_post_value( 'active_addon', '' );

			foreach ( (array) ap_get_addons() as $file => $data ) { ?>
				<?php

				if ( '' === $active && 0 === $i ) {
					$active = $data['id'];
				}

				?>
				<div class="ap-addon<?php echo $data['id'] === $active ? ' active' : ''; ?> ">
					<a class="no-overflow" href="<?php echo esc_url( admin_url( 'admin.php?page=anspress_addons&active_addon=' . $data['id'] ) ) ; ?>" data-id="<?php echo esc_attr( $data['id'] ); ?>">
						<?php echo esc_attr( $data['name'] ); ?>
						<?php if ( $data['active'] ) : ?>
							<span class="ap-addon-status"><?php esc_attr_e( 'Active', 'anspress-question-answer' ); ?> </span>
						<?php endif; ?>
						<?php echo $data['pro'] ? '<i>pro</i>' : ''; ?>
					</a>
				</div>
			<?php
			$i++;
			} ?>
		</div>
		<div class="ap-addon-options">
			<?php $active_data = ap_get_addon( $active ); ?>
			<?php
				$args = wp_json_encode( array(
					'action'   => 'ap_toggle_addon',
					'__nonce'  => wp_create_nonce( 'toggle_addon' ),
					'addon_id' => $active_data['id'],
				) );
			?>

			<h2 class="ap-addon-name">
				<a href="#" class="button ap-addon-toggle<?php echo $active_data['active'] ? ' button-primary' : ''; ?>" ap-ajax-btn ap-query="<?php echo esc_js( $args ); ?>"><?php echo $active_data['active'] ? __( 'Disable Addon', 'anspress-question-answer' ) : __( 'Enable Add-on', 'anspress-question-answer' ); ?></a>
				<?php echo esc_html( $active_data['name'] ); ?>
				<p><?php echo esc_html( $active_data['description'] ); ?></p>
			</h2>

			<?php if ( ! $active_data['active'] ) : ?>
				<p class="ap-form-nofields"><?php esc_attr_e( 'Please enable addon to view options.', 'anspress-question-answer' ); ?></p>
			<?php else : ?>

				<?php anspress()->get_form( 'addon-' . $active_data['id'] )->generate(); ?>
			<?php endif; ?>
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

