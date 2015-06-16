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

<ul id="ap-notification-dropdown" class="ap-dropdown-menu ap-user-dropdown-menu ap-notification-items">
	<li class="ap-notification-head clearfix">
		<b><?php _e('Notifications', 'ap'); ?></b>
		<a href="#" data-action="ap_markread_notification" data-query="ap_ajax_action=markread_notification&__nonce='.wp_create_nonce( 'ap_markread_notification_'.get_current_user_id() ).'"><?php _e('Mark all as read', 'ap'); ?></a>
	</li>
	<?php if(ap_has_notifications()): ?>
		<li class="ap-notification-items clearfix">
			<div class="ap-notification-scroll scrollbar-dynamic">
				<?php while ( ap_notifications() ) : ap_the_notification(); ?>
					<div class="ap-notification-<?php ap_notification_the_id(); ?> ap-notification-item clearfix<?php echo ap_notification_is_unread() ? ' unread' : ''; ?>">
						<a href="<?php ap_notification_the_permalink() ?>" class="clearfix">
							<span class="ap-notification-icon <?php ap_notification_the_type(); ?>"><?php ap_notification_the_icon(); ?></span>
							<div class="no-overflow">
								<span class="ap-notification-content"><?php ap_notification_the_content(); ?></span>
								<span class="ap-notification-time"><?php ap_notification_the_date(); ?></span>
							</div>
						</a>
					</div>
				<?php endwhile; ?>
			</div>
		</li>
	<?php else: ?>
		<li class="ap-no-notification"><?php _e('No notification', 'ap'); ?></li>
	<?php endif; ?>
	<li class="ap-notification-footer clearfix"><a href="<?php echo ap_user_link(get_current_user_id(), 'notification'); ?>"><?php _e('View all notifications', 'ap'); ?></a></li>
</ul>