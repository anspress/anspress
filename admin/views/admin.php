<?php
/**
 * AnsPress options page
 *
 * @link http://anspress.io/anspress
 * @since 2.0.1
 * @package AnsPress
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

new AnsPress_Options_Fields();

if ( isset( $_POST['__nonce'] ) && wp_verify_nonce( $_POST['__nonce'], 'nonce_option_form' ) && current_user_can( 'manage_options' ) ) {
	flush_rewrite_rules();

	$settings = get_option( 'anspress_opt', array() );
	$groups = ap_get_option_groups();

	$active = ap_sanitize_unslash( 'fields_group', 'request' );

	// If active is set.
	if ( '' != $active ) {
		$fields = $groups[ $active ]['fields'];

		// Get only field name.
		$field_names = array_column( $fields, 'name' );

		// Check $_POST value against fields.
		foreach ( (array) $field_names as $name ) {
			$value = ap_sanitize_unslash( $name, 'request' );

			if ( ! empty( $value ) ) {
				$settings[ $name ] = $value;
			} else {
				unset( $settings[ $name ] );
			}
		}

		update_option( 'anspress_opt', $settings );
		wp_cache_delete( 'anspress_opt', 'ap' );
		$_POST['anspress_opt_updated'] = true;
	}
}

?>

<?php if ( isset( $_POST['anspress_opt_updated'] ) ) :   ?>
	<div class="notice notice-success is-dismissible">
	    <p><?php _e( 'AnsPress option updated!', 'anspress-question-answer' ); ?></p>
	</div>
<?php endif; ?>

<div id="anspress" class="wrap">	
    <div class="ap-optionpage-wrap">
	    <h2 class="admin-title">
			<?php _e( 'AnsPress Options', 'anspress-question-answer' ); ?>
	        <a href="http://github.com/anspress/anspress" target="_blank">GitHub</a>
	        <a href="https://wordpress.org/plugins/anspress-question-answer/" target="_blank">WordPress.org</a>
	        <a href="https://twitter.com/anspress_io" target="_blank">@anspress_io</a>
	        <a href="https://www.facebook.com/wp.anspress" target="_blank">Facebook</a>
	    </h2>

	    <div class="ap-wrap">
	        <div class="anspress-options ap-wrap-left clearfix">
	            <div class="option-nav-tab clearfix">
					<?php ap_options_nav(); ?>
			    </div>
	            <div class="ap-group-options">
					<?php ap_option_group_fields(); ?>
	            </div>
	        </div>
	    </div>
	</div>
</div>
