<?php
/**
 * Ask question page
 *
 * @link http://anspress.io
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
					<a href="#" class="apicon-screen-full pull-right ap-btn-fullscreen" data-action="ap_fullscreen_toggle"><?php _e('Toggle fullscreen', 'ap'); ?></a>
					<ul class="ap-form-head-tab ap-ul-inline clearfix ap-tab-nav">
						<li class="active"><a href="#ap-form-main"><?php _e('Write', 'ap'); ?></a></li>
						<?php if(ap_opt('question_help_page') != '') : ?>
							<li><a href="#ap-form-help"><?php _e('How to ask', 'ap'); ?></a></li>
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
		<div id="qsuggestion" class="ap-qsuggestion">

			<div class="ap-qsuggestion-header">
				<h3><?php _e('We found some similar questions', 'ap'); ?></h3>
				<p><?php _e('Check if your question is listed below, check them before posting new one.', 'ap'); ?></p>
			</div>
			<div class="ap-qsuggestion-inner">
				<div class="ap-qsuggestion-list clearfix">
					<span class="loading"><?php _e('Hold tight! loading similar questions', 'ap'); ?><b class="ap-loading-dot"></b></span>
				</div>
			</div>
			<div class="ap-qsuggestion-footer clearfix">
				<button class="ap-btn" data-action="confNewQuestion"><?php _e('I don\'t see my question, submit my question', 'ap'); ?></button>
			</div>

		</div>
	<?php elseif (is_user_logged_in()): ?>
		<div class="ap-no-permission">
			<?php _e('You don\'t have permission to ask question.', 'ap'); ?>
		</div>
	<?php endif; ?>
	<?php ap_get_template_part('login-signup'); ?>
</div>
