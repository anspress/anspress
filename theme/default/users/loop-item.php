<?php
/**
 * User item inside loop
 *
 * Template is used to display user inside a loop
 *
 * @package AnsPress
 */
	
?>
<?php
	/**
	 * ACTION:ap_users_before_item
	 * For hooking before users loop item
	 * @since 2.1.0
	 */
	do_action('ap_users_before_item');
?>
<div class="ap-users-item" data-id="<?php ap_user_the_ID(); ?>">
	<div class="ap-users-inner clearfix">
		<div class="ap-users-summary clearfix">
			<a class="ap-users-avatar" href="<?php ap_user_the_link(); ?>">
				<?php ap_user_the_avatar()  ?>
			</a>
			<div class="no-overflow">
				<a class="ap-users-name" href="<?php ap_user_the_link(); ?>"><?php ap_user_the_display_name(); ?></a>
			</div>
		</div>
		<div class="ap-users-metas clearfix">
			<?php if(!ap_opt('disable_reputation')): ?>
				<a class="ap-users-metas-rep" href="<?php echo ap_user_link(ap_user_get_the_ID(), 'points'); ?>" title="<?php _e('Reputation', 'ap') ?>"><span data-view="ap-points"><?php ap_user_the_reputation(); ?></span> <?php _e('Rep'); ?></a>
			<?php endif; ?>
			<?php 
	            /**
	             * ACTION: ap_users_loop_meta
	             * Used to hook into loop item meta
	             * @since 2.1.0
	             */
	            do_action('ap_users_loop_meta'); 
	        ?>
		</div>
	</div>
</div>