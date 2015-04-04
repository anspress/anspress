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
?>

<div class="wrap">
	<?php screen_icon(); echo '<h2>' . __( 'AnsPress Options' ) . '</h2>';
	// This shows the page's name and an icon if one has been provided ?>
			
	<?php if ( @$_POST['anspress_opt_updated'] === true ) : ?>
		<div class="updated fade"><p><strong><?php _e( 'AnsPress options updated', 'ap' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>
	
	<div class="anspress-options">
		<div class="option-nav-tab clearfix">
			<?php ap_options_nav(); ?>
		</div>
		<div class="ap-group-options">
			<?php ap_option_group_fields(); ?>
		</div>
	</div>
	
</div>