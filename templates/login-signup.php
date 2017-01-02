<?php
/**
 * Display login signup form
 *
 * @package WordPress/AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 */

?>

<?php if ( ! is_user_logged_in() ) : ?>
	<div class="ap-content ap-login">
		<div class="ap-cell">
			<div class="ap-cell-inner">
				<?php do_action( 'wordpress_social_login' ); ?>
				<div class="ap-login-buttons">
					<a href="<?php echo esc_url( wp_registration_url() ); ?>" class="ap-btn"><?php esc_attr_e( 'Register', 'anspress-question-answer' ); ?></a>
					<span class="ap-login-sep"><?php esc_attr_e( 'or', 'anspress-question-answer' ); ?></span>
					<a href="<?php echo esc_url( wp_login_url() ); ?>" class="ap-btn"><?php esc_attr_e( 'Login', 'anspress-question-answer' ); ?></a>
				</div>
			</div>
		</div>
	</div>

<?php endif; ?>
