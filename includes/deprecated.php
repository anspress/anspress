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

_deprecated_function('ap_is_best_answer', '2.1', 'ap_answer_is_best');

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
	if($post_id === false)
		$post_id = get_the_ID();
	
	$meta = get_post_meta($post_id, ANSPRESS_BEST_META, true);
	if($meta) return true;
	
	return false;
}