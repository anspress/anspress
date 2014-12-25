<?php

/**
 * Answers theme
 *
 * Control the output of answers
 *
 * @link http://wp3.in/anspress
 * @since 2.0
 *
 * @package AnsPress
 */

ap_answers_tab();

if(ap_user_can_see_answers()){

	echo '<div id="answers">';
		while ( $answers->have_posts() ) : $answers->the_post(); 
			include(ap_get_theme_location('answer.php'));
		endwhile ;
	echo '</div>';	
	ap_pagination('', 2, $paged, $answers);

}else{
	echo '<div class="ap-login-to-see-ans">'.sprintf(__('Please %s or %s to view answers and comments', 'ap'), '<a class="ap-open-modal ap-btn" title="Click here to login if you already have an account on this site." href="#ap_login_modal">Login</a>', '<a class="ap-open-modal ap-btn" title="Click here to signup if you do not have an account on this site." href="#ap_signup_modal">Sign Up</a>').'</div>';
	echo do_action('ap_after_answer_form');
}


