<?php
/**
 * Display AnsPress user notification
 *
 * @link http://anspress.io
 * @since 2.3
 *
 * @package AnsPress
 */
?>
<div class="ap-notification">
	<h3 class="ap-user-page-title clearfix">
		<?php echo ap_page_title() ?>
	</h3>
	<?php if(ap_has_notifications()): ?>
		<div class="ap-notification-list">
			<?php while ( ap_notifications() ) : ap_the_notification(); ?>
					<div class="ap-notification-<?php ap_notification_the_id(); ?> ap-notification-item clearfix<?php echo ap_notification_is_unread() ? ' unread' : ''; ?>">

						<a href="<?php ap_notification_the_permalink(); ?>" class="ap-notification-icon <?php ap_notification_the_type(); ?>"><?php ap_notification_the_icon(); ?></a>	
						
						<div class="ap-notification-btns ap-pull-right">
							<?php if(ap_notification_is_unread()): ?>
								<a class="ap-btn ap-btn-markread ap-tip <?php echo ap_icon('check'); ?>" href="#" data-action="ap_markread_notification" data-query="<?php echo 'id='.ap_notification_id().'&ap_ajax_action=markread_notification&__nonce='.wp_create_nonce('ap_markread_notification_'.ap_notification_id()); ?>" title="<?php _e('Mark as read', 'ap'); ?>"></a>
							<?php endif; ?>
							<a class="ap-btn ap-btn-delete ap-tip" href="#" data-action="ap_delete_notification" data-query="<?php echo 'id='.ap_notification_id().'&ap_ajax_action=delete_notification&__nonce='.wp_create_nonce('delete_notification'); ?>" title="<?php _e('Delete notificaton', 'ap'); ?>"><?php echo ap_icon('trashcan', true); ?></a>

						</div>
						<div class="no-overflow">
							<span class="ap-notification-content">						
								<a href="<?php ap_notification_the_permalink(); ?>"><?php ap_notification_the_content(); ?></a>
							</span>
							<span class="ap-notification-time"><?php ap_notification_the_date(); ?></span>
						</div>		
					</div>

			<?php endwhile; ?>
		</div>
		<?php ap_notification_pagination(); ?>
	<?php else: ?>
		<?php _e('No notification', 'ap'); ?>
	<?php endif; ?>
</div>
