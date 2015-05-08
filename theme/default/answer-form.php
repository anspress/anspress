<?php if(ap_user_can_answer(get_question_id())) : ?>
	<div id="answer-form-c">
		<div class="ap-avatar ap-pull-left">
			<a href="<?php echo ap_user_link(get_current_user_id()); ?>">
				<?php echo get_avatar(get_current_user_id(), ap_opt('avatar_size_qquestion')); ?>
			</a>		
		</div>
		<div class="ap-a-cells">
			<?php ap_answer_form(get_question_id()); ?>
		</div>
	</div>
<?php elseif (is_user_logged_in()): ?>
	<div class="ap-no-permission">
		<?php _e('You dont have permission to answer this question.', 'ap'); ?>
	</div>
<?php else: ?>
	<div class="ap-please-login">
		<?php printf(__('Please %s or %s to answer this question.', 'ap'), '<a href="'.wp_login_url(get_permalink()).'">'.__('Login', 'ap').'</a>', '<a href="'.wp_registration_url().'">'.__('Register', 'ap').'</a>') ?>
		<?php do_action( 'wordpress_social_login' ); ?>
	</div>
<?php endif; ?>

