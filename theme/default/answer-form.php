<?php if(ap_user_can_answer(get_question_id())) : ?>
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
					<?php if(ap_opt('answer_help_page') != '') : ?>
						<li><a href="#ap-form-help"><?php _e('How to answer', 'ap'); ?></a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="ap-tab-container">
				<div id="ap-form-main" class="active ap-tab-item">
					<?php ap_answer_form(get_question_id()); ?>
				</div>
				<div id="ap-form-help" class="ap-tab-item">
					<?php if(ap_opt('answer_help_page') != ''): ?>
						<?php ap_how_to_answer(); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php ap_get_template_part('login-signup'); ?>




