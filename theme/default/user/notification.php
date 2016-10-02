<?php
/**
 * Display AnsPress user notification
 *
 * @link https://anspress.io
 * @since 2.3
 *
 * @package AnsPress
 */
?>
<div class="ap-notification">
	<h3 class="ap-user-page-title clearfix">
		<?php echo ap_page_title() ?>
	</h3>
	<?php if(ap_has_activities()): ?>
		<div class="ap-notification-list">
			<?php while ( ap_activities() ) : ap_the_activity(); ?>
					<div id="ap-notification-<?php ap_activity_the_noti_id(); ?>" class="ap-notification-item clearfix<?php echo ap_notification_is_unread() ? ' unread' : ''; ?>">
						<div class="ap-avatar ap-pull-left">
							<a href="<?php echo ap_user_link( ap_activity_user_id() ); ?>"><?php echo get_avatar( ap_activity_user_id(), 40 ); ?></a>
						</div>
						
						<!-- <a href="<?php ap_activity_the_permalink(); ?>" class="ap-notification-icon <?php ap_activity_the_type(); ?>"><?php ap_activity_the_icon(); ?></a> -->

						<div class="ap-notification-btns ap-pull-right">
							<?php if(ap_notification_is_unread()): ?>
								<a class="ap-btn ap-btn-markread ap-tip <?php echo ap_icon('check'); ?>" href="#" data-action="ap_markread_notification" data-query="<?php echo 'id='.ap_activity_noti_id().'&ap_ajax_action=markread_notification&__nonce='.wp_create_nonce('ap_markread_notification_'.ap_activity_noti_id()); ?>" title="<?php _e('Mark as read', 'anspress-question-answer'); ?>"></a>
							<?php endif; ?>
							<a class="ap-btn ap-btn-delete ap-tip" href="#" data-action="ap_delete_notification" data-query="<?php echo 'id='.ap_activity_noti_id().'&ap_ajax_action=delete_notification&__nonce='.wp_create_nonce('delete_notification'); ?>" title="<?php _e('Delete notificaton', 'anspress-question-answer'); ?>"><?php echo ap_icon('trashcan', true); ?></a>

						</div>
						<div class="no-overflow">
							<span class="ap-notification-content">
								<?php ap_activity_the_content(); ?>
							</span>
							<div class="ap-notification-meta">
								<span class="ap-notification-time"><?php ap_activity_the_date(); ?></span>
							</div>
						</div>
					</div>

			<?php endwhile; ?>
		</div>
		<?php ap_activity_pagination(); ?>
	<?php else: ?>
		<?php _e('No notification', 'anspress-question-answer'); ?>
	<?php endif; ?>
</div>
