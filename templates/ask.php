<?php
/**
 * Ask question page
 *
 * @link https://anspress.net
 * @since 0.1
 *
 * @package AnsPress
 */

?>
<div id="ap-ask-page" class="clearfix">
	<?php if ( ap_user_can_ask() ) : ?>
			<?php ap_ask_form(); ?>
	<?php elseif ( is_user_logged_in() ) : ?>
		<div class="ap-no-permission">
			<?php _e( 'You do not have permission to ask a question.', 'anspress-question-answer' ); ?>
		</div>
	<?php endif; ?>

	<?php ap_get_template_part( 'login-signup' ); ?>
</div>
