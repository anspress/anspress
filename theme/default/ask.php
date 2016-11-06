<?php
/**
 * Ask question page
 *
 * @link https://anspress.io
 * @since 0.1
 *
 * @package AnsPress
 */

?>
<div id="ap-ask-page" class="clearfix">
	<?php if (ap_user_can_ask()): ?>
		<div id="answer-form-c">
			<div class="ap-avatar ap-pull-left">
				<a href="<?php echo ap_user_link(get_current_user_id()); ?>"<?php ap_hover_card_attributes(get_current_user_id()); ?>>
					<?php echo get_avatar(get_current_user_id(), ap_opt('avatar_size_qquestion')); ?>
				</a>
			</div>
			<div class="ap-a-cells clearfix">
					<?php ap_ask_form(); ?>
			</div>
		</div>
	<?php elseif (is_user_logged_in()): ?>
		<div class="ap-no-permission">
			<?php _e('You do not have permission to ask question.', 'anspress-question-answer'); ?>
		</div>
	<?php endif; ?>
	<?php ap_get_template_part('login-signup'); ?>
</div>
