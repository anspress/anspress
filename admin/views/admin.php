<?php

/**
 * AnsPress options page
 *
 * 
 * @link http://wp3.in/anspress
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
/*$navigation = array(
	'general'		=> __('General', 'ap'),
	'questions'		=> __('Questions', 'ap'),
	'answers'		=> __('Answers', 'ap'),

	'user'			=> __('User', 'ap'),
	'permission'	=> __('Permission', 'ap'),
	'pages'			=> __('Pages', 'ap'),
	'spam'			=> __('Spam and Moderate', 'ap'),
);*/


/**
 * FILTER: ap_option_navigation
 * For filtering AnsPress option navigation
 */
//$navigation = apply_filters('ap_option_navigation', $navigation );

if ( ! isset( $_REQUEST['settings-updated'] ) )
	$_REQUEST['settings-updated'] = false; // This checks whether the form has just been submitted. ?>

<div class="wrap">
	<?php screen_icon(); echo '<h2>' . __( 'AnsPress Options' ) . '</h2>';
	// This shows the page's name and an icon if one has been provided ?>
			
	<?php if ( false !== $_REQUEST['settings-updated'] ) : ?>
	<div class="updated fade"><p><strong><?php _e( 'Options saved', 'ap' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>
	
	<div class="anspress-options">
		<div class="option-nav-tab">
			<?php ap_options_nav(); ?>
		</div>
		<div class="ap-group-options">
			<?php ap_option_group_fields(); ?>
		</div>
	</div>
	
</div>