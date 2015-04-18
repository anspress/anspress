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
<?php if(ap_count_other_answer(get_question_id())): ?>
	<div id="ap-answers-c">
		<div class="ap-sorting-tab clearfix">
			<h3 class="ap-widget-title ap-pull-left">
				<?php printf(_n('%1$s answer', '%2$s Answers', ap_count_other_answer(get_question_id()), 'ap'), '<span data-view="answer_count">'.__('One', 'ap').'</span>', '<span data-view="answer_count">'.ap_count_other_answer(get_question_id()).'</span>'); ?>
			</h3>
			<?php ap_answers_tab(); ?>
		</div>

		<?php if(ap_user_can_see_answers()): ?>
			<div id="answers">
				<?php
					while ( anspress()->answers->have_posts() ) : anspress()->answers->the_post();
						include(ap_get_theme_location('answer.php'));
					endwhile ;
				?>
			</div>
			<?php //ap_pagination(false, $answers->max_num_pages); ?>
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



