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
		<?php ap_ask_form(); ?>
	<?php elseif (is_user_logged_in()): ?>
		<div class="ap-no-permission">
			<?php _e('You dont have permission to ask question.', 'ap'); ?>
		</div>
	<?php else: ?>
		<div class="ap-please-login">
			<?php printf(__('Please %s or %s to ask a question.', 'ap'), '<a href="'.wp_login_url(get_permalink()).'">'.__('Login', 'ap').'</a>', '<a href="'.wp_registration_url().'">'.__('Register', 'ap').'</a>') ?>
			<?php do_action( 'wordpress_social_login' ); ?>
		</div>
	<?php endif; ?>	
</div>
