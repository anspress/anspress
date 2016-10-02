<?php
/**
 * AnsPress options page
 *
 * @link https://anspress.io/anspress
 * @since 2.0.1
 * @package AnsPress
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

new AnsPress_Options_Fields();

if ( isset( $_POST['__nonce'] ) && wp_verify_nonce( $_POST['__nonce'], 'nonce_option_form' ) && current_user_can( 'manage_options' ) ) {


	$settings = get_option( 'anspress_opt', array() );
	$groups = ap_get_option_groups();

	$active = ap_sanitize_unslash( 'fields_group', 'request' );

	// If active is set.
	if ( '' != $active ) {
		$fields = $groups[ $active ]['fields'];

		$default_opt = ap_default_options();

		// Check $_POST value against fields.
		foreach ( (array) $fields as $f ) {

			$value = ap_sanitize_unslash( $f['name'], 'request' );

			// If reset then get value from default option.
			if ( isset( $_POST['reset'] ) ) {
				$value = $default_opt[ $f['name'] ];
			}

			// Set checkbox field value as 0 when empty.
			if ( 'checkbox' == $f['type'] && empty( $value ) ) {
				$value = '0';
			}

			if ( isset( $value ) ) {
				$settings[ $f['name'] ] = $value;
			} else {
				unset( $settings[ $f['name'] ] );
			}
		}

		update_option( 'anspress_opt', $settings );
		wp_cache_delete( 'anspress_opt', 'ap' );
		$_POST['anspress_opt_updated'] = true;
	}
	flush_rewrite_rules();
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
	    </h2>

	    <div class="social-links clearfix">
	    	<a href="https://github.com/anspress/anspress" target="_blank">GitHub</a>
	        <a href="https://wordpress.org/plugins/anspress-question-answer/" target="_blank">WordPress.org</a>
	        <a href="https://twitter.com/anspress_io" target="_blank">@anspress_io</a>
	        <a href="https://www.facebook.com/wp.anspress" target="_blank">Facebook</a>
	    </div>

	    <div class="ap-wrap">
	        <div class="anspress-options ap-wrap-left clearfix">
	            <div class="option-nav-tab clearfix">
					<?php ap_options_nav(); ?>
			    </div>
	            <div class="ap-options-side">
		            <div class="ap-group-options get-ext">
		            	<a href="https://anspress.io/themes/">Browse Themes</a>
		            	<a href="https://anspress.io/extensions/">Browse Extensions</a>
		            	<h3>Check our AnsPress extensions and themes</h3>
		            </div>
		            <div class="ap-group-options">
						<?php ap_option_group_fields(); ?>
		            </div>
		        </div>
	        </div>
	    </div>
	</div>
</div>
