<?php
/**
 * Functions and handler for follow
 * @author Rahul Aryan <support@anspress.io>
 * @since 2.3
 */


/**
 * Output follow button HTML
 * @param  integer $user_to_follow
 * @return string
 */
function ap_follow_button($user_to_follow){
	echo ap_get_follow_button($user_to_follow);
}
	
	/**
	 * Get follow button html
	 * @param  integer $user_to_follow user_id to follow or unfollow
	 * @return string
	 */
	function ap_get_follow_button($user_to_follow){
		$current_user = get_current_user_id();
		
		if($current_user == $user_to_follow)
			return;

		$nonce = wp_create_nonce( 'follow_'.$user_to_follow.'_'.$current_user );

		$following = ap_is_user_following($user_to_follow, $current_user);

		$title =  $following ? __('Unfollow', 'ap') : __('Follow', 'ap');

		$output = '<a href="#" id="follow_'.$user_to_follow.'" class="ap-btn ap-btn-follow'.($following ? ' active' : '').'" data-action="ap_follow" data-query="ap_ajax_action=follow&user_id='.$user_to_follow.'&__nonce='.$nonce.'">'.$title.'</a>';

		return $output;
	}

/**
 * Add a follower
 * @param  integer  		$current_user_id   	Current user_id
 * @param  integer  		$user_to_follow 	user to follow
 * @return bollean|integer
 */
function ap_add_follower($current_user_id, $user_to_follow){
	
	$row = ap_add_meta($current_user_id, 'follower', $user_to_follow);

	if($row !== false)
		do_action('ap_added_follower', $user_to_follow, $current_user_id);

	return $row;
}

/**
 * Remove a follower
 * @param  integer 		$current_user_id 		Current user id
 * @param  integer 		$user_to_follow  		user id to unfollow
 * @return boolean|integer
 */
function ap_remove_follower($current_user_id, $user_to_follow){
	$row = ap_delete_meta(array('apmeta_type' => 'follower', 'apmeta_userid' => $current_user_id, 'apmeta_actionid' => $user_to_follow));

	if($row !== false)
		do_action('ap_removed_follower', $current_user_id, $user_to_follow);

	return $row;
}

function ap_is_user_following($user_to_follow, $current_user_id = false){
	
	if($current_user_id === false)
		$user_id = get_current_user_id();

	if($current_user_id > 0){
		$row = ap_meta_user_done('follower', $current_user_id, $user_to_follow);
		
		return $row > 0 ? true : false;
	}

	return false;
}

/**
 * Count total numbers of followers
 * @param  integer $user_id
 * @return integer
 */
function ap_followers_count($user_id){
	return ap_meta_total_count( 'follower', $user_id );
}

/**
 * Count total numbers of following user 
 * @param  integer 		$user_id
 * @return integer
 */
function ap_following_count($user_id){
	return ap_meta_total_count( 'follower', false, $user_id );
}