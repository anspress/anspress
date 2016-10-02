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
				<div class="ap-form-head">
					<a href="#" class="apicon-screen-full pull-right ap-btn-fullscreen" data-action="ap_fullscreen_toggle"><?php _e('Toggle fullscreen', 'anspress-question-answer'); ?></a>
					<ul class="ap-form-head-tab ap-ul-inline clearfix ap-tab-nav">
						<li class="active"><a href="#ap-form-main"><?php _e('Write', 'anspress-question-answer'); ?></a></li>
						<?php if(ap_opt('question_help_page') != '') : ?>
							<li><a href="#ap-form-help"><?php _e('How to ask', 'anspress-question-answer'); ?></a></li>
						<?php endif; ?>
					</ul>
				</div>
				<div class="ap-tab-container">
					<div id="ap-form-main" class="active ap-tab-item">
						<?php ap_ask_form(); ?>
					</div>
					<div id="ap-form-help" class="ap-tab-item">
						<?php if(ap_opt('question_help_page') != ''): ?>
							<?php ap_how_to_ask(); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	<?php elseif (is_user_logged_in()): ?>
		<div class="ap-no-permission">
			<?php _e('You do not have permission to ask question.', 'anspress-question-answer'); ?>
		</div>
	<?php endif; ?>
	<?php ap_get_template_part('login-signup'); ?>
</div>
