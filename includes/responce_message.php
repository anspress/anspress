<?php
/**
 * Error messages
 *
 * @link http://wp3.in
 * @since 2.0.1
 * @license GPL 2+
 * @package AnsPress
 */

/**
 * Return response with type and message
 * @param  string $id error id
 * @return string
 * @since 2.0.1
 */
function ap_responce_message($id)
{
	$msg =array(
		'no_permission' => array('type' => 'warning', 'message' => __('You do not have permission to do this action.', 'ap')),
		'draft_comment_not_allowed' => array('type' => 'warning', 'message' => __('You are commenting on a draft post.', 'ap')),
		'comment_success' => array('type' => 'success', 'message' => __('Comment successfully posted.', 'ap')),
	);

	/**
	 * FILTER: ap_responce_message
	 * Can be used to alter response messages
	 * @var array
	 * @since 2.0.1 
	 */
	$msg = apply_filters( 'ap_responce_message', $msg );

	if(isset($msg[$id]))
		return $msg;

	return false;
}

function ap_ajax_responce($results)
{
	if(!is_array($results))
		return;

	$results['ap_responce'] = true;

	if( isset($results['message']) ){
		$error_message = ap_responce_message($results['message']);
		
		if($error_message !== false){
			$results['message'] = $error_message['message'];
			$results['message_type'] = $error_message['type'];
		}
	}

	/**
	 * FILTER: ap_ajax_responce
	 * Can be used to alter ap_ajax_responce
	 * @var array
	 * @since 2.0.1
	 */
	$results = apply_filters( 'ap_ajax_responce', $results );

	return $results;
}

?>