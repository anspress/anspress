<?php

/**
 * Answers content
 *
 * Control the output of answers
 *
 * @link http://wp3.in/anspress
 * @since 2.0.1
 *
 * @package AnsPress
 */

if(count($answers) > 0){
	$label = ap_is_answer_selected(get_the_ID()) ? 'Other ' : '';
	echo '<div class="ap-other-answers-tab clearfix">';
		echo '<h3 class="ap-widget-title ap-pull-left">'. sprintf(__('%sanswers (%s)', 'ap'), $label, '<span data-view="answer_count">'.ap_count_other_answer().'</span>') .'</h3>';
		ap_answers_tab();
	echo '</div>';

	if(ap_user_can_see_answers()){

		echo '<div id="answers">';
			while ( $answers->have_posts() ) : $answers->the_post(); 
				include(ap_get_theme_location('answer.php'));
			endwhile ;
		echo '</div>';	
		ap_pagination(false, $answers->max_num_pages);
	}else{
		echo '<div class="ap-login-to-see-ans">'.sprintf(__('Please %s or %s to view answers and comments', 'ap'), '<a class="ap-open-modal ap-btn" title="Click here to login if you already have an account on this site." href="#ap_login_modal">Login</a>', '<a class="ap-open-modal ap-btn" title="Click here to signup if you do not have an account on this site." href="#ap_signup_modal">Sign Up</a>').'</div>';
		echo do_action('ap_after_answer_form');
	}
}



