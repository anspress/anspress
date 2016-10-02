<?php
/**
 * Display user activity feed.
 *
 * This template is used to display site feed to user.
 *
 * @link https://anspress.io
 * @since 2.4
 *
 * @package AnsPress
 */
?>

<div id="ap-users" class="ap-users ap-user-activity">
	<h3 class="ap-user-page-title clearfix">
		<?php echo ap_page_title() ?>
	</h3>
	<?php if ( ap_has_activities() ) : ?>
		<div class="ap-activities">
			<?php
				/* Start the Loop */
				while ( ap_activities() ) : ap_the_activity();
					ap_get_template_part('activity/item');
				endwhile;
			?>
		</div>
	<?php ap_activity_pagination(ap_user_link(ap_get_displayed_user_id(), 'activity-feed')); ?>
	<?php
		else :
			ap_get_template_part('content-none');
		endif;
	?>
</div>

