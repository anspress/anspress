<?php if(ap_user_can_answer(get_question_id())) : ?>
	<div id="answer-form-c">
		<h3 class="ap-widget-title"><?php _e('Your answer', 'ap') ?></h3>
		<?php ap_answer_form(get_question_id()); ?>
	</div>
<?php elseif( !is_user_logged_in() ): ?>
	<div class="ap-please-login">
		<?php printf(__('Please %s or %s to ask a question.', 'ap'), '<a href="'.wp_login_url(get_permalink()).'">'.__('Login', 'ap').'</a>', '<a href="'.wp_registration_url().'">'.__('Register', 'ap').'</a>') ?>
		<?php do_action( 'wordpress_social_login' ); ?>
	</div>
<?php endif; ?>

