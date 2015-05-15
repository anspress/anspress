<?php

/**
 * Answers content
 *
 * Control the output of answers
 *
 * @link http://anspress.io/anspress
 * @since 2.0.1
 *
 * @package AnsPress
 */ 
?>
<?php if(ap_have_answers()): ?>
	<div id="ap-answers-c">
		<div class="ap-sorting-tab clearfix">
			<h3 class="ap-answers-label ap-pull-left">
				<?php echo '<span data-view="answer_count">'.ap_answer_get_the_count().'</span> ' .sprintf(_n('answer', 'answers', ap_answer_get_the_count(), 'ap')); ?>
			</h3>
			<?php ap_answers_tab(ap_question_get_the_permalink()); ?>
		</div>

		<?php if(ap_user_can_see_answers()): ?>
			<div id="answers">
				<?php
					$i = 1;
					while ( ap_have_answers() ) : ap_the_answer();
						include(ap_get_theme_location('answer.php'));
						$i++;
					endwhile ;
				?>
			</div>
			<?php if(!ap_opt('disable_answer_nav')): ?>
				<div id="ap-answers-nav-c">
					<div class="ap-answers-nav fix">
						<a href="#" title="<?php _e('Prev answer', 'ap'); ?>" data-acton="ap_answer_prev" class="ap-answerss-nav-prev apicon-chevron-up"></a>
						<span class="ap-answers-nav-count">
							<span class="ap-answers-nav-current" data-view="ap_answer_nav_cur">1</span> /
							<span class="ap-answers-nav-total" data-view="ap_answer_nav_total"><?php ap_answer_the_count(); ?></span>
						</span>
						<a href="#" title="<?php _e('Prev answer', 'ap'); ?>" data-acton="ap_answer_next" class="ap-answers-nav-next apicon-chevron-down"></a>
					</div>
				</div>
			<?php endif; ?>
			<?php ap_answers_the_pagination(); ?>
		<?php else: ?>
			<div class="ap-login-to-see-ans">
				<?php 
					printf(__('Please %s or %s to view answers and comments', 'ap'), '<a class="ap-open-modal ap-btn" title="Click here to login if you already have an account on this site." href="#ap_login_modal">Login</a>', '<a class="ap-open-modal ap-btn" title="Click here to signup if you do not have an account on this site." href="#ap_signup_modal">Sign Up</a>'); 
				?>
			</div>
			<?php do_action('ap_after_answer_form'); ?>
		<?php endif; ?>
	</div>
<?php endif; ?>




