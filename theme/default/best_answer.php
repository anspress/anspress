<?php
/**
 * Best answer content
 * 	
 * @author Rahul Aryan <rah12@live.com>
 * @link http://wp3.in/anspress
 * @since 0.1
 *
 * @package AnsPress
 */

/**
 * Show best answer
 */
if(ap_is_answer_selected(get_the_ID())){
	echo '<div id="ap-best-answer">';
		echo '<h3 class="ap-widget-title">' . __('Best answer', 'ap') .'</h3>';
		ap_get_best_answer();
	echo '</div>';
}