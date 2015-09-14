<?php
/**
 * Best answer content
 * 	
 * @author Rahul Aryan <support@anspress.io>
 * @link http://anspress.io/anspress
 * @since 0.1
 *
 * @package AnsPress
 */

/**
 * Show best answer
 */
ap_get_best_answer();
if(ap_have_answers()){
	echo '<div id="ap-best-answer">';
		echo '<h3 class="ap-answers-label"><span>' . __('Best answer', 'ap') .'</span></h3>';		
		while ( ap_have_answers() ) : ap_the_answer();
	        include(ap_get_theme_location('answer.php'));
	    endwhile ;
	echo '</div>';
}