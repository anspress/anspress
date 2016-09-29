<?php
/**
 * Activity item
 * Used to output each item for activities.
 *
 * @link   	https://anspress.io
 * @since  	2.4
 * @author 	Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */	
?>

<div id="activity-<?php ap_activity_the_id(); ?>" class="activity-<?php ap_activity_the_id(); ?> ap-activity clearfix">						
	<div class="ap-avatar">
		<a href="<?php echo ap_user_link( ap_activity_user_id() ); ?>"><?php echo get_avatar( ap_activity_user_id(), 35 ); ?></a>
	</div>
	<div class="no-overflow">
		<div class="ap-activity-content"><?php ap_activity_the_content(); ?></div>
		<div class="ap-activity-actions">
			<span class="ap-activity-time"><?php ap_activity_the_date(); ?></span>
			<?php ap_activity_the_delete_btn(); ?>
		</div>
	</div>
	
</div>