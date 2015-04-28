<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http:/anspress.io
 * @copyright 2014 Rahul Aryan
 */

if ( !function_exists( '_deprecated_function' ) )
	require_once ABSPATH . WPINC . '/functions.php';

/**
 * Check if given anser/post is selected as a best answer
 * You should use ap_answer_is_best instead
 * 
 * @param  false|integer $post_id 
 * @return boolean
 * @since unknown
 * @deprecated 2.1
 */
function ap_is_best_answer($post_id = false){
	_deprecated_function('ap_is_best_answer', '2.1', 'ap_answer_is_best');
	if($post_id === false)
		$post_id = get_the_ID();
	
	$meta = get_post_meta($post_id, ANSPRESS_BEST_META, true);
	if($meta) return true;
	
	return false;
}

/**
 * Check if answer is selected for given question
 * @param  false|integer $question_id
 * @return boolean
 */
function ap_is_answer_selected($question_id = false){
	_deprecated_function('ap_is_answer_selected', '2.1', 'ap_question_best_answer_selected');
	if($question_id === false)
		$question_id = get_the_ID();
	
	$meta = get_post_meta($question_id, ANSPRESS_SELECTED_META, true);

	if(!$meta)
		return false;
	
	return true;
}

function ap_have_ans($id){
	_deprecated_function('ap_have_ans', '2.1', 'ap_have_answers');
	if(ap_count_all_answers($id) > 0)
		return true;	
	
	return false;
}