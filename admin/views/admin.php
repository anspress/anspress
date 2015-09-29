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

$settings = ap_opt();


/**
 * Anspress option navigation
 * @var array
 */
?>

<div id="anspress" class="wrap">
    <h2 class="admin-title">
		<?php _e( 'AnsPress Options' ); ?>
        <a href="http://github.com/anspress/anspress" target="_blank">GitHub</a>
        <a href="https://wordpress.org/plugins/anspress-question-answer/" target="_blank">WordPress.org</a>
        <a href="https://twitter.com/anspress_io" target="_blank">@anspress_io</a>
        <a href="https://www.facebook.com/wp.anspress" target="_blank">Facebook</a>
    </h2>

	<?php if ( @$_POST['anspress_opt_updated'] === true ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'AnsPress options updated', 'ap' ); ?></strong></p></div>
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
