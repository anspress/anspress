<?php
/**
 * AnsPress options page
 *
 * @link https://anspress.io/anspress
 * @since 2.0.1
 * @author Rahul Aryan <support@anspress.io>
 * @package WordPress/AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$updated = false;

if ( ap_isset_post_value( '__nonce' ) && ap_verify_nonce( 'nonce_option_form' ) && current_user_can( 'manage_options' ) ) {

	$settings = get_option( 'anspress_opt', array() );
	$groups   = ap_get_option_groups();
	$active   = ap_sanitize_unslash( 'fields_group', 'request' );
	$ap_active_section = ap_isset_post_value( 'ap_active_section', '' );

	// If active is set.
	if ( '' !== $active && '' !== $ap_active_section ) {

		$default_opt = ap_default_options();

		$i = 0;
		// Check $_POST value against fields.
		foreach ( (array) $groups[ $active ]['sections'] as $section_slug => $section ) {
			if ( $section_slug === $ap_active_section ) {
				foreach ( (array) $section['fields'] as $k => $f ) {

					if ( ! isset( $f['name'] ) ) {
						continue;
					}

					if ( isset( $f['type'] ) && 'textarea' === $f['type'] ) {
						$value = esc_textarea( wp_unslash( ap_isset_post_value( $f['name'], '' ) ) );
					} else {
						$value = ap_sanitize_unslash( $f['name'], 'request' );
					}

					// If reset then get value from default option.
					if ( ap_sanitize_unslash( 'reset', 'p' ) ) {
						$value = $default_opt[ $f['name'] ];
					}

					// Set checkbox field value as 0 when empty.
					if ( isset( $f['type'] ) && 'checkbox' === $f['type'] && empty( $value ) ) {
						$value = '0';
					}

					if ( isset( $value ) ) {
						$settings[ $f['name'] ] = $value;
					} else {
						unset( $settings[ $f['name'] ] );
					}
				}
			}
			$i++;
		}

		update_option( 'anspress_opt', $settings );
		wp_cache_delete( 'anspress_opt', 'ap' );
		$updated = true;

	}

	flush_rewrite_rules();
	wp_safe_redirect( admin_url( 'admin.php?page=anspress_options' ) );
}
?>

<?php if ( true === $updated ) :   ?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'AnsPress option updated!', 'anspress-question-answer' ); ?></p>
	</div>
<?php endif; ?>

<div id="anspress" class="wrap">
	<h2 class="admin-title">
		<?php esc_html_e( 'AnsPress Options', 'anspress-question-answer' ); ?>
		<div class="social-links clearfix">
			<a href="https://github.com/anspress/anspress" target="_blank">GitHub</a>
			<a href="https://wordpress.org/plugins/anspress-question-answer/" target="_blank">WordPress.org</a>
			<a href="https://twitter.com/anspress_io" target="_blank">@anspress_io</a>
			<a href="https://www.facebook.com/wp.anspress" target="_blank">Facebook</a>
		</div>
	</h2>
	<div class="clear"></div>

	<div class="anspress-imglinks">
		<a href="https://anspress.io/extensions/" target="_blank">
			<img src="<?php echo ANSPRESS_URL; ?>assets/images/more_functions.svg" />
		</a>
	</div>

	<div class="ap-optionpage-wrap no-overflow">

		<div class="ap-wrap">
			<div class="anspress-options ap-wrap-left clearfix">
				<div class="option-nav-tab clearfix">
					<?php ap_options_nav(); ?>
				</div>
				<div class="metabox-holder">

					<div class="ap-group-options">
						<?php ap_option_group_fields(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($){
		$('.postbox > h3').click(function(){
			$(this).closest('.postbox').toggleClass('closed');
		});
		$('#question_page_slug').on('keyup', function(){
			$('.ap-base-slug').text($(this).val());
		})
	});
</script>
