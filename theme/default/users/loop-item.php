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
		</div>
		<div class="no-overflow clearfix">
			<a class="ap-users-name" href="<?php ap_user_the_link(); ?>"><?php ap_user_the_display_name(); ?></a>
			<div class="ap-uw-status">
				<span><?php printf(__('%s Rep.', 'anspress-question-answer'), ap_user_get_the_reputation()); ?></span>
				<span><?php printf(__('%d Best', 'anspress-question-answer'), ap_user_get_the_meta('__best_answers')); ?></span>
				<span><?php printf(__('%d Answers', 'anspress-question-answer'), ap_user_get_the_meta('__total_answers')); ?></span>
				<span><?php printf(__('%d Questions', 'anspress-question-answer'), ap_user_get_the_meta('__total_questions')); ?></span>
				<?php 
		            /**
		             * ACTION: ap_users_loop_meta
		             * Used to hook into loop item meta
		             * @since 2.1.0
		             */
		            do_action('ap_users_loop_meta'); 
		        ?>
	        </div>
	        <div class="ap-users-buttons clearfix">
				<?php ap_follow_button(ap_user_get_the_ID()); ?>
			</div>
		</div>
	</div>
</div>