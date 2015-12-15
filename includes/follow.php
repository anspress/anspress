<?php
/**
 * Functions and handler for follow
 * @author Rahul Aryan <support@anspress.io>
 * @since 2.3
 */


/**
 * Output follow button HTML
 * @param  integer 		$user_to_follow
 * @param  array 		$text 					custom button text
 * @return string
 */
function ap_follow_button($user_to_follow , $text = false){
	echo ap_get_follow_button($user_to_follow, $text);
}

	/**
	 * Get follow button html
	 * @param  integer 		$user_to_follow 		user_id to follow or unfollow
	 * @param  array 		$text 					custom button text
	 * @return string
	 */
	function ap_get_follow_button($user_to_follow, $text = false){
		$current_user = get_current_user_id();

		if($current_user == $user_to_follow)
			return;

		$nonce = wp_create_nonce( 'follow_'.$user_to_follow.'_'.$current_user );

		$following = ap_is_user_following($user_to_follow, $current_user);

		if(false === $text)
			$title =  $following ? __('Unfollow', 'anspress-question-answer') : __('Follow', 'anspress-question-answer');
		else
			$title =  $following ? $text[0] : $text[1];

		$output = '<a href="#" id="follow_'.$user_to_follow.'" class="ap-btn ap-btn-follow'.($following ? ' active' : '').'" data-action="ap_follow" data-query="ap_ajax_action=follow&user_id='.$user_to_follow.'&__nonce='.$nonce.'">'.$title.'</a>';

		return $output;
	}

/**
 * Add a follower
 * @param  integer  		$current_user_id   	Current user_id
 * @param  integer  		$user_to_follow 	user to follow
 * @return integer
 */
function ap_add_follower($current_user_id, $user_to_follow){

	$row = ap_new_subscriber( $current_user_id, $user_to_follow, 'u_all' );

	if($row !== false)
		do_action('ap_added_follower', $user_to_follow, $current_user_id);

	return $row;
}

/**
 * Remove a follower
 * @param  integer 		$current_user_id 		Current user id
 * @param  integer 		$user_to_follow  		user id to unfollow
 * @return boolean
 */
function ap_remove_follower($current_user_id, $user_to_follow){
	$row = ap_remove_subscriber($user_to_follow, $current_user_id, 'u_all');

	if($row !== false)
		do_action('ap_removed_follower', $current_user_id, $user_to_follow);

	return $row;
}

/**
 * Checks if user is already following a user.
 * @param integer $user_to_follow 	User to follow.
 * @param integer $current_user_id 	Current user id.
 */
function ap_is_user_following($user_to_follow, $current_user_id = false){

	if($current_user_id === false){
		$user_id = get_current_user_id();
	}

	if($current_user_id > 0){
		return ap_is_user_subscribed($user_to_follow, 'u_all', $current_user_id);
	}

	return false;
}

/**
 * Count total numbers of followers
 * @param  integer $user_id
 * @return integer
 */
function ap_followers_count($user_id){
	return ap_subscribers_count($user_id, 'u_all');
}

/**
 * Count total numbers of following user
 * @param  integer 		$user_id
 * @return integer
 */
function ap_following_count($user_id){
	return ap_meta_total_count( 'follower', false, $user_id );
}