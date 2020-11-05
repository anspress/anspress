<?php
/**
 * Display login signup form
 *
 * @package AnsPress
 * @author  Rahul Aryan <rah12@live.com>
 */

?>

<?php if ( ! is_user_logged_in() ) : ?>
	<div class="ap-login">
		<?php
			// Load WSL buttons if available.
			do_action( 'wordpress_social_login' );
		?>

		<div class="ap-login-buttons">
			<a href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_attr_e( 'Register', 'anspress-question-answer' ); ?></a>
			<span class="ap-login-sep"><?php esc_attr_e( 'or', 'anspress-question-answer' ); ?></span>
			<a href="<?php echo esc_url( wp_login_url( get_the_permalink() ) ); ?>"><?php esc_attr_e( 'Login', 'anspress-question-answer' ); ?></a>
		</div>
	</div>

<?php endif; ?>
