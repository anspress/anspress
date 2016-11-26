<?php
/**
 * Answers content
 * Control the output of answers.
 *
 * @link https://anspress.io/anspress
 * @since 2.0.1
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

?>
<ap-answers-w style="<?php echo ! ap_have_answers() ? 'display:none' : ''; ?>">
	<div id="ap-answers-c">
		<div class="ap-sorting-tab clearfix">
			<h3 class="ap-answers-label ap-pull-left" ap-answecount-text>
				<?php
					printf(
						_n( '%d Answer', '%d Answers', ap_total_answers_found(), 'anspress-question-answer' ),
						ap_total_answers_found()
					);
				?>
			</h3>
			<?php ap_answers_tab( get_the_permalink() ); ?>
		</div>

		<?php ap_answers_the_pagination(); ?>

		<div id="answers">
			<?php if ( ap_user_can_see_answers() ) : ?>
				<ap-answers>
					<?php while ( ap_have_answers() ) : ap_the_answer(); ?>
						<?php include( ap_get_theme_location( 'answer.php' ) ); ?>
					<?php endwhile ; ?>
				</ap-answers>

			<?php else: ?>
				<div class="ap-login-to-see-ans">
					<?php
						printf( __('Please %s or %s to view answers and comments', 'anspress-question-answer'), '<a class="ap-open-modal ap-btn" title="Click here to login if you already have an account on this site." href="#ap_login_modal">Login</a>', '<a class="ap-open-modal ap-btn" title="Click here to signup if you do not have an account on this site." href="#ap_signup_modal">Sign Up</a>' );
					?>
				</div>
				<?php do_action( 'ap_after_answer_form' ); ?>
			<?php endif; ?>
		</div>

		<?php ap_answers_the_pagination(); ?>

	</div>
</ap-answers-w>

