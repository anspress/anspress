<?php
/**
 * AnsPress admin add-ons page.
 *
 * @link       https://anspress.io
 * @since      4.0.0
 * @author     Rahul Aryan <support@anspress.io>
 * @package    AnsPress
 * @subpackage Admin Views
 * @since 4.1.5 Fixed form name.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Action hook triggered before loading addons page.
 *
 * @since 4.1.0
 */
do_action( 'ap_before_addons_page' );

$form_name = ap_sanitize_unslash( 'ap_form_name', 'r' );
$updated = false;

// Process submit form.
if ( ! empty( $form_name ) && anspress()->get_form( $form_name )->is_submitted() ) {
	$form = anspress()->get_form( $form_name );
	$values = $form->get_values();

	if ( ! $form->have_errors() ) {
		$options = get_option( 'anspress_opt', [] );

		foreach ( $values as $key => $opt ) {
			$options[ $key ] = $opt['value'];
		}

		update_option( 'anspress_opt', $options );
		wp_cache_delete( 'anspress_opt', 'ap' );
		wp_cache_delete( 'anspress_opt', 'ap' );

		$updated = true;
	}
}
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

	<?php if ( true === $updated ) :   ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Addon options updated successfully!', 'anspress-question-answer' ); ?></p>
		</div>
	<?php endif; ?>

	<div class="ap-addons" method="POST">
		<div class="ap-addons-list">
			<div class="ap-addons-listw">
				<?php
				/**
				 * Action hook called before AnsPress addons list in wp-admin addons page.
				 *
				 * @since 4.1.0
				 */
				do_action( 'ap_before_addons_list' );

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
		<div class="ap-addon-options">
			<?php
			$active_data = ap_get_addon( $active );

			/**
			 * Action hook called before AnsPress addon option header in wp-admin addons page.
			 *
			 * @param array $active_data Active addon data.
			 * @since 4.1.0
			 */
			do_action( 'ap_before_addon_header', $active_data );
			?>
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

			<?php
			/**
			 * Action hook called after AnsPress addon header in wp-admin addons page.
			 *
			 * @param array $active_data Active addon data.
			 * @since 4.1.0
			 */
			do_action( 'ap_after_addon_header', $active_data );
			?>

			<?php if ( ! $active_data['active'] ) : ?>
				<p class="ap-form-nofields"><?php esc_attr_e( 'Please enable addon to view options.', 'anspress-question-answer' ); ?></p>
			<?php else : ?>
				<?php
				$from_args = array(
					'form_action' => admin_url( 'admin.php?page=anspress_addons&active_addon=' . $active ),
					'ajax_submit' => false,
				);

				/**
				 * Filter AnsPress add-on options form.
				 *
				 * @param array $form_args Array for form arguments.
				 * @since 4.1.0
				 */
				$form_args = apply_filters( 'ap_addon_form_args', $from_args );

				$form_name = str_replace( '.php', '', $active_data['id'] );
				$form_name = str_replace( '/', '_', $form_name );

				if ( anspress()->form_exists( 'addon-' . $form_name ) ) {
					anspress()->get_form( 'addon-' . $form_name )->generate( $form_args );
				} else {
					echo '<p class="ap-form-nofields">' . esc_attr__( 'There is no option registered by this addon.', 'anspress-question-answer' ) . '</p>';
				}
				?>
			<?php endif; ?>

			<?php
			/**
			 * Action hook called after AnsPress addon options in wp-admin addons page.
			 *
			 * @param array $active_data Active addon data.
			 * @since 4.1.0
			 */
			do_action( 'ap_after_addon_options', $active_data );
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

