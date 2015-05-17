<?php

/**
 * AnsPress options page
 *
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
	<?php screen_icon(); echo '<h2>' . __( 'AnsPress Options' ) . '</h2>';
	// This shows the page's name and an icon if one has been provided ?>
			
	<?php if ( @$_POST['anspress_opt_updated'] === true ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'AnsPress options updated', 'ap' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>
	
	<div class="ap-wrap">
		<div class="anspress-options ap-wrap-left">
			<div class="option-nav-tab clearfix">
				<?php ap_options_nav(); ?>
			</div>
			<div class="ap-group-options">
				<?php ap_option_group_fields(); ?>
			</div>
		</div>
		<div class="ap-wrap-side">
			<div class="ap-dash-tile clearfix">
				<div class="anspress-links">
					<a href="http://anspress.io/" target="_blank"><i class="apicon-anspress-icon"></i><?php _e('About AnsPress', 'ap'); ?></a>
					<a href="http://anspress.io/questions/" target="_blank"><i class="apicon-question"></i><?php _e('Support Q&A', 'ap'); ?></a>
					<a href="http://anspress.io/themes/" target="_blank"><i class="apicon-info"></i><?php _e('AnsPress Themes', 'ap'); ?></a>
					<a href="http://github.com/anspress/anspress" target="_blank"><i class="apicon-mark-github"></i><?php _e('Github Repo', 'ap'); ?></a>
				</div>
				<div class="ap-ext-notice">
					<i class="apicon-puzzle"></i>
					<h3>AnsPress extensions</h3>
					<p>Extend AnsPress capabilities, get free extensions</p>
					<a href="http://anspress.io/extensions/" target="_blank">Browse</a>
				</div>
			</div>
		</div>
	</div>
	
</div>