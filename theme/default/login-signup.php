<?php if(!is_user_logged_in()): ?>
	<div class="ap-please-login">
		<?php printf(__('Please %s or %s.', 'ap'), '<a data-action="ap_modal" data-toggle="#ap-login-modal" href="'.wp_login_url(get_permalink()).'">'.__('Login', 'ap').'</a>', '<a href="'.wp_registration_url().'">'.__('Sign up', 'ap').'</a>') ?>
		<?php do_action( 'wordpress_social_login' ); ?>
	</div>
	<div id="ap-login-modal" class="ap-modal">
		<div class="ap-modal-backdrop"></div>
		<div class="ap-modal-inner">
			<div class="ap-modal-header">
				<i class="ap-modal-close" data-action="ap_modal_close">&times;</i>
				<h3 class="ap-modal-title"><?php _e('Login', 'ap'); ?></h3>
			</div>
			<div class="ap-modal-body">
				<?php wp_login_form(); ?>
			</div>
		</div>
	</div>
<?php endif; ?>