<?php
/**
 * Best answer template *
 *
 * @author Rahul Aryan <support@anspress.io>
 * @link https://anspress.io/
 * @since 0.1
 *
 * @package AnsPress
 */

if ( ap_have_answers() ) {
	echo '<div id="ap-best-answer">';
		echo '<h3 class="ap-bestans-label"><span>' . __('Best answer', 'anspress-question-answer' ) .'</span></h3>';
	while ( ap_have_answers() ) : ap_the_answer();
		include(ap_get_theme_location('answer.php' ) );
	endwhile ;
	echo '</div>';
}
