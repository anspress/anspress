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

	$active = ap_isset_post_value( 'fields_group', '' );

	// If active is set.
	if ( '' != $active ) {
		$fields = $groups[ $active ]['fields'];

		//Get only field name.
		$field_names = array_column( $fields, 'name' );

		// Unset __sep name.
		if( ( $key = array_search('__sep', $field_names) ) !== false ) {
		    unset( $field_names[ $key ] );
		}

		foreach ( (array) $field_names as $name ) {
			$value = ap_sanitize_unslash( $name, 'request' );
			if ( !empty( $value ) ) {
				$settings[ $name ] = $value;
			} else {
				unset( $settings[ $name ] );
			}
		}

		update_option( 'anspress_opt', $settings );
		$_POST['anspress_opt_updated'] = true;
	}
}

//var_dump(wp_cache_get('anspress_opt', 'options' ));

/**
 * Anspress option navigation
 * @var array
 */
?>

<div id="anspress" class="wrap">
    <h2 class="admin-title">
		<?php _e( 'AnsPress Options', 'anspress-question-answer' ); ?>
        <a href="http://github.com/anspress/anspress" target="_blank">GitHub</a>
        <a href="https://wordpress.org/plugins/anspress-question-answer/" target="_blank">WordPress.org</a>
        <a href="https://twitter.com/anspress_io" target="_blank">@anspress_io</a>
        <a href="https://www.facebook.com/wp.anspress" target="_blank">Facebook</a>
    </h2>

	<?php if ( ap_isset_post_value('anspress_opt_updated' ) === true ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'AnsPress options updated', 'anspress-question-answer' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>

    <div class="ap-wrap">
        <div class="anspress-options ap-wrap-left clearfix">
            <div class="option-nav-tab clearfix">
				<?php ap_options_nav(); ?>
            </div>
            <div class="ap-group-options">
				<?php ap_option_group_fields(); ?>
            </div>
        </div>
        <?php include_once( 'sidebar.php' ); ?>
    </div>

</div>
