<?php
	$current_user = get_userdata( get_current_user_id() );
?>
<?php if(ap_user_can_answer(get_the_ID())) : ?>
	<div id="answer-form-c">
		<h3 class="ap-widget-title"><?php _e('Your answer', 'ap') ?></h3>	
		<div class="no-overflow ap-editor">
			<?php ap_answer_form(get_the_ID()); ?>
		</div>
	</div>
<?php else: ?>
	<div class="ap-please-login">
		<?php printf(__('Please %s or %s to ask a question.', 'ap'), '<a href="'.wp_login_url(get_permalink()).'">'.__('Login', 'ap').'</a>', '<a href="'.wp_registration_url().'">'.__('Register', 'ap').'</a>') ?>
	</div>
<?php endif; ?>
