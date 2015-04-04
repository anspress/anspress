<?php
/**
 * AnsPress reputation controller class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      http://wp3.com
 * @copyright 2014 Rahul Aryan
 */

class AnsPress_Reputation {
	
	public function __construct(){
		add_action('ap_after_new_question', array($this, 'new_question'));
		add_action('ap_untrash_question', array($this, 'new_question'));
		add_action('ap_trash_question', array($this, 'delete_question'));
		
		add_action('ap_after_new_answer', array($this, 'new_answer'));
		add_action('ap_untrash_answer', array($this, 'new_answer'));
		add_action('ap_trash_answer', array($this, 'delete_answer'));
		
		add_action('ap_select_answer', array($this, 'select_answer'), 10, 3);
		add_action('ap_unselect_answer', array($this, 'unselect_answer'), 10, 3);
		
		add_action('ap_vote_up', array($this, 'vote_up'), 10, 2);
		add_action('ap_vote_down', array($this, 'vote_down'), 10, 2);
		add_action('ap_undo_vote_up', array($this, 'undo_vote_up'), 10, 2);
		add_action('ap_undo_vote_down', array($this, 'undo_vote_down'), 10, 2);
		
		add_action('ap_publish_comment', array($this, 'new_comment'));
		add_action('ap_unpublish_comment', array($this, 'delete_comment'));
	}
	
	/**
	 * Update reputation of user created question 
	 * @param  integer $postid
	 * @return boolean|null
	 */
	public function new_question($postid) {
		$reputation = ap_reputation_by_event('new_question', true);
		return ap_reputation('question', get_current_user_id(), $reputation, $postid);
	}
	
	/**
	 * Update point of trashing question
	 * @param  integer $postid
	 * @return boolean
	 */
	public function delete_question($postid) {
		$reputation = ap_reputation_by_event('new_question', true);
		return ap_reputation_log_delete('question', get_current_user_id(), $reputation, $postid);
	}
	
	/**
	 * Update reputation of user created an answer
	 * @param  integer $postid
	 * @return boolean|null
	 */
	public function new_answer($postid) {
		$reputation = ap_reputation_by_event('new_answer', true);
		return ap_reputation('answer', get_current_user_id(), $reputation, $postid);
	}
	
	/**
	 * Update reputation on trasing answer
	 * @param  integer $postid
	 * @return boolean
	 */
	public function delete_answer($postid) {
		$reputation = ap_reputation_by_event('new_answer', true);
		return ap_reputation_log_delete('answer', get_current_user_id(), $reputation, $postid);
	}
	
	/**
	 * Update reputation of user selecting and author of answer on selecting an answer
	 * @param  integer $userid
	 * @param  integer $question_id
	 * @param  integer $answer_id
	 * @return void         
	 */
	public function select_answer($userid, $question_id, $answer_id){
		$reputation = ap_reputation_by_event('select_answer', true);
		$selector_reputation = ap_reputation_by_event('selecting_answer', true);
		$answer = get_post($answer_id);
		
		if($answer->post_author != $userid)
			ap_reputation('best_answer', $answer->post_author, $reputation, $answer_id);
			
		ap_reputation('selecting_answer', $userid, $selector_reputation, $answer_id);
		return;
	}	
	
	/**
	 * Update reputation of user selecting and author of answer on unselecting answer
	 * @param  integer $userid
	 * @param  integer $question_id
	 * @param  integer $answer_id
	 * @return void
	 */
	public function unselect_answer($userid, $question_id, $answer_id){
		$reputation = ap_reputation_by_event('select_answer', true);
		$selector_reputation = ap_reputation_by_event('selecting_answer', true);
		$answer = get_post($answer_id);
		
		if($answer->post_author != $userid){
			ap_reputation_log_delete('best_answer', $answer->post_author, $reputation, $answer_id);
		}
		ap_reputation_log_delete('selecting_answer', $userid, $selector_reputation, $answer_id);
		return;
	}
	
	/**
	 * Update reputation of post author when received an up vote
	 * @param  integer $postid
	 * @param  array $counts
	 * @return null|false
	 */
	public function vote_up($postid, $counts) {
		$post = get_post($postid);
		
		// give reputation to post author
		if($post->post_type == 'question')
			$reputation = ap_reputation_by_event('question_upvote', true);
		elseif($post->post_type == 'answer')
			$reputation = ap_reputation_by_event('answer_upvote', true);
		
		$uid = $post->post_author;
		
		if(!empty($reputation))
			ap_reputation('vote_up', $uid, $reputation, $postid);
		
		if($post->post_type == 'question')
			$reputation = ap_reputation_by_event('question_upvoted', true);
		elseif($post->post_type == 'answer')
			$reputation = ap_reputation_by_event('answer_upvoted', true);
		
		$userid = get_current_user_id();
		
		if(!empty($reputation))
			return ap_reputation('vote_up', $userid, $reputation, $postid);

		return false;
	}
	
	/**
	 * Update reputation of post author when received a down vote
	 * @param  integer $postid
	 * @param  array $counts
	 * @return boolean
	 */
	public function vote_down($postid, $counts) {	
		$post = get_post($postid);
		
		// give reputation to post author
		if($post->post_type == 'question')
			$reputation = ap_reputation_by_event('question_downvote', true);
		elseif($post->post_type == 'answer')
			$reputation = ap_reputation_by_event('answer_downvote', true);
		
		$uid = $post->post_author;

		if(empty($reputation))
			return false;

		ap_reputation('vote_down', $uid, $reputation, $postid);
		
		// give reputation to user casting vote
		$userid = get_current_user_id();
		if($post->post_type == 'question')
			$reputation = ap_reputation_by_event('question_downvoted', true);
		elseif($post->post_type == 'answer')
			$reputation = ap_reputation_by_event('answer_downvoted', true);
		
		ap_reputation('voted_down', $userid, $reputation, $postid);

		return true;
	}
	
	/**
	 * Reverse reputation of post author when up vote is undone
	 * @param  integer $postid
	 * @param  array $counts
	 * @return boolean
	 */
	public function undo_vote_up($postid, $counts) {	
		$post = get_post($postid);
		
		// give reputation to post author
		if($post->post_type == 'question')
			$reputation = ap_reputation_by_event('question_upvote', true);
		elseif($post->post_type == 'answer')
			$reputation = ap_reputation_by_event('answer_upvote', true);
		
		$uid = $post->post_author;
		
		if(empty($reputation))
			return false;

		ap_reputation_log_delete('vote_up', $uid, $reputation, $postid);
		
		if($post->post_type == 'question')
			$reputation = ap_reputation_by_event('question_upvoted', true);
		elseif($post->post_type == 'answer')
			$reputation = ap_reputation_by_event('answer_upvoted', true);
		
		$userid = get_current_user_id();
		
		ap_reputation_log_delete('vote_up', $userid, $reputation, $postid);

		return true;
	}
	
	/**
	 * Reverse reputation of post author when down vote is undone
	 * @param  integer $postid
	 * @param  array $counts
	 * @return false|null
	 */
	public function undo_vote_down($postid, $counts) {	
		$post = get_post($postid);
		
		// give reputation to post author
		if($post->post_type == 'question')
			$reputation = ap_reputation_by_event('question_downvote', true);
		elseif($post->post_type == 'answer')
			$reputation = ap_reputation_by_event('answer_downvote', true);
		
		$uid = $post->post_author;

		if(empty($reputation))
			return false;	

		ap_reputation_log_delete('vote_up', $uid, $reputation, $postid);
		
		// give reputation to user casting vote
		$userid = get_current_user_id();
		if($post->post_type == 'question')
			$reputation = ap_reputation_by_event('question_downvoted', true);
		elseif($post->post_type == 'answer')
			$reputation = ap_reputation_by_event('answer_downvoted', true);

		ap_reputation_log_delete('voted_down', $userid, $reputation, $postid);
	}
	
	/**
	 * Award reputation on new comment
	 * @param  object $comment WordPress comment object
	 * @return void
	 */
	public function new_comment($comment){
		$reputation = ap_reputation_by_event('new_comment', true);
		ap_reputation('comment', $comment->user_id, $reputation, $comment->comment_ID);
	}
	
	/**
	 * Reverse reputation on deleting comment
	 * @param  object $comment
	 * @return void
	 */
	public function delete_comment($comment){
		$reputation = ap_reputation_by_event('new_comment', true);
		ap_reputation_log_delete('comment', $comment->user_id, $reputation, $comment->comment_ID);
	}
	
}

function ap_reputation_option(){
	$data  	= wp_cache_get('ap_reputation', 'ap');
	if($data === false){
		$opt 	= get_option('ap_reputation');
		$data 	= (is_array($opt) ? $opt : array()) + ap_default_reputation();
		$data 	= apply_filters('ap_reputation_option', $data);
		wp_cache_set('ap_reputation', $data, 'ap');
	}
	return $data;
}

function ap_reputation_by_id($id){
	$opt = ap_reputation_option();
	foreach( $opt as $reputation)
		if($reputation['id'] == $id)
			return $reputation;
	
	return false;
}

/**
 * @param string $event
 */
function ap_reputation_by_event($event, $only_reputation = false){
	$opt = ap_reputation_option();
	foreach( $opt as $reputation)
		if($reputation['event'] == $event){
			if($only_reputation)
				return $reputation['reputation'];
			else
				return $reputation;
		}
	return false;
}


function ap_reputation_option_new($title, $desc, $reputation, $event){
	$opt 	= ap_reputation_option();
	$opt[] = array(
		'id' => count($opt),
		'title' => $title,
		'description' => $desc,
		'reputation' => $reputation,
		'event' => $event,
	);
	return update_option('ap_reputation', $opt);
}

function ap_reputation_option_update($id, $title, $desc, $reputation, $event){
	$opt 	= ap_reputation_option();
	foreach($opt as $k => $p){
		if($p['id'] == $id){
			$opt[$k]['title'] 		= $title;
			$opt[$k]['description'] = $desc;
			$opt[$k]['reputation'] 		= $reputation;
			$opt[$k]['event'] 		= $event;
		}
	}
	wp_cache_delete('ap_reputation', 'ap' );
	return update_option('ap_reputation', $opt);
}

function ap_reputation_option_delete($id){
	$opt 	= ap_reputation_option();
	foreach($opt as $k => $p){
		if($p['id'] == $id){
			unset($opt[$k]);
		}
	}
	return update_option('ap_reputation', $opt);
}

/**
 * Get the reputation of a user
 * @param  false|integere 	$uid    WordPress user id
 * @param  boolean 	$short  set it to true for formatted output like 1.2K
 * @return string
 */
function ap_get_reputation($uid = false, $short = false) {
	if(!$uid)
		$uid = get_current_user_id();
		
	$reputation = get_user_meta($uid, 'ap_reputation', true) + get_user_meta($uid, 'ap_points', true);
	
	if ($reputation == '') {
		return 0;
	} else {
		if(false !== $short)
			return ap_short_num( $reputation );
		
		return $reputation;
	}
}

function ap_get_all_reputation($user_id){
	global $wpdb;
	$query = "SELECT v.* FROM ".$wpdb->prefix."ap_meta v WHERE v.apmeta_type='reputation' AND v.apmeta_userid = $user_id";

	$key = md5($query);

	$result = wp_cache_get( $key, 'ap');

	if($result === false){
		$result = $wpdb->get_results($query);
		wp_cache_set( $key, $result, 'ap' );
	}

	return $result;
}

/**
 * @param string $type
 */
function ap_reputation($type, $uid, $reputation, $data){
	if($uid == 0)
		return;

	$reputation = apply_filters('ap_reputation',$reputation, $type, $uid, $data);
	ap_alter_reputation($uid, $reputation);
	ap_reputation_log($type, $uid, $reputation, $data);
}

//update reputation
function ap_update_reputation($uid, $reputation) {
	// no negative reputation 
	if ($reputation < 1) {
	  $reputation = 0;
	}

	update_user_meta($uid, 'ap_reputation', $reputation);
}

function ap_alter_reputation($uid, $reputation) {
	ap_update_reputation($uid, ap_get_reputation($uid) + $reputation);
}

// add reputation logs to DB
function ap_reputation_log($type, $uid, $reputation, $data){
	$userinfo = get_userdata($uid);
	
	if($userinfo->user_login=='')
		return false; 
		
	if($reputation==0 && $type!='reset')
		return false; 
	
	return ap_add_meta($uid, 'reputation', $data, $reputation, $type);
}

/**
 * @param string $type
 */
function ap_reputation_log_delete($type, $uid, $reputation =NULL, $data =NULL){
	$new_reputation = ap_get_reputation($uid) - $reputation;
	
	$row = ap_delete_meta(array('apmeta_type' => 'reputation', 'apmeta_userid' => $uid, 'apmeta_actionid' => $data, 'apmeta_value' => $reputation, 'apmeta_param' => $type ));
	update_user_meta($uid, 'ap_reputation', $new_reputation);

	return $row;
}


function ap_set_reputation($type, $uid, $reputation, $data){
	$reputation = apply_filters('ap_set_reputation',$reputation, $type, $uid, $data);
	$difference = $reputation - ap_get_reputation($uid);
	ap_update_reputation($uid, $reputation);
	ap_reputation_log($type, $uid, $difference, $data);
}

function ap_default_reputation(){
	$reputation = array(
		array(
			'id'       		=> 1,
			'title'       	=> __('New Registration', 'ap'),
			'description' 	=> __('Points given to newly registered user.', 'ap'),
			'reputation'      	=> '1',
			'event'    		=> 'registration'
		),
		array(
			'id'       		=> 2,
			'title'       	=> __('Uploading avatar', 'ap'),
			'description' 	=> __('Awarded for uploading an profile picture.', 'ap'),
			'reputation'      	=> '2',
			'event'    		=> 'uploaded_avatar'
		),
		array(
			'id'       		=> 3,
			'title'       	=> __('Completing profile', 'ap'),
			'description' 	=> __('Awarded for completing profile fields.', 'ap'),
			'reputation'      	=> '2',
			'event'    		=> 'uploaded_avatar'
		),
		array(
			'id'       		=> 4,
			'title'       	=> __('Question', 'ap'),
			'description' 	=> __('For asking a question.', 'ap'),
			'reputation'      	=> '2',
			'event'    		=> 'new_question'
		),
		array(
			'id'       		=> 5,
			'title'       	=> __('Answer', 'ap'),
			'description' 	=> __('For answering a question.', 'ap'),
			'reputation'      	=> '10',
			'event'    		=> 'new_answer'
		),
		array(
			'id'       		=> 6,
			'title'       	=> __('Comment', 'ap'),
			'description' 	=> __('For new comment.', 'ap'),
			'reputation'      	=> '1',
			'event'    		=> 'new_comment'
		),
		array(
			'id'       		=> 7,
			'title'       	=> __('Receive upvote on question', 'ap'),
			'description' 	=> __('When user receive an upvote on question', 'ap'),
			'reputation'      	=> '2',
			'event'    		=> 'question_upvote'
		),
		array(
			'id'       		=> 8,
			'title'       	=> __('Receive upvote on answer', 'ap'),
			'description' 	=> __('When user receive an upvote on answer', 'ap'),
			'reputation'      	=> '5',
			'event'    		=> 'answer_upvote'
		),
		array(
			'id'       		=> 9,
			'title'       	=> __('Receive down vote on question', 'ap'),
			'description' 	=> __('When user receive an down vote on question', 'ap'),
			'reputation'      	=> '-1',
			'event'    		=> 'question_downvote'
		),
		array(
			'id'       		=> 10,
			'title'       	=> __('Receive down vote on answer', 'ap'),
			'description' 	=> __('When user receive an down vote on answer', 'ap'),
			'reputation'      	=> '-3',
			'event'    		=> 'answer_downvote'
		),
		array(
			'id'       		=> 11,
			'title'       	=> __('Up voted questions', 'ap'),
			'description' 	=> __('When user upvote others question', 'ap'),
			'reputation'      	=> '0',
			'event'    		=> 'question_upvoted'
		),
		array(
			'id'       		=> 12,
			'title'       	=> __('Up voted answers', 'ap'),
			'description' 	=> __('When user upvote others answer', 'ap'),
			'reputation'      	=> '0',
			'event'    		=> 'answer_upvoted'
		),
		array(
			'id'       		=> 13,
			'title'       	=> __('Down voted questions', 'ap'),
			'description' 	=> __('When user down vote others question', 'ap'),
			'reputation'      	=> '-1',
			'event'    		=> 'question_downvoted'
		),
		array(
			'id'       		=> 14,
			'title'       	=> __('Down voted answers', 'ap'),
			'description' 	=> __('When user down vote others answers', 'ap'),
			'reputation'      	=> '-1',
			'event'    		=> 'answer_downvoted',
			'negative'    	=> true
		),
		array(
			'id'       		=> 15,
			'title'       	=> __('Best answer', 'ap'),
			'description' 	=> __('When user\'s answer get selected as best', 'ap'),
			'reputation'      	=> '10',
			'event'    		=> 'select_answer'
		),
		array(
			'id'       		=> 16,
			'title'       	=> __('Selecting answer', 'ap'),
			'description' 	=> __('When user user select an answer.', 'ap'),
			'reputation'      	=> '2',
			'event'    		=> 'selecting_answer'
		),
	);
	
	return $reputation;
}

function ap_received_reputation_post($post_id){
	$reputation 		= ap_reputation_by_event('question_upvote', true);
	$vote_count = ap_meta_total_count('vote_up', $post_id);
	return $vote_count*$reputation;
}

/**
 * Get total reputation of all users
 * @return integer
 */
function ap_total_reputation(){
	global $wpdb;

	$count = wp_cache_get( 'site_total_reputation', 'ap' );

	if(false === $count){
		$count = $wpdb->get_var('SELECT sum(apmeta_value) FROM '.$wpdb->prefix.'ap_meta where apmeta_type = "reputation"');
		wp_cache_add( 'site_total_reputation', $count, 'ap' );
	}
	
	return (int)$count;
}

function ap_get_user_reputation_share($user_id){
	$user_points = ap_get_reputation($user_id);

	return ($user_points * ap_total_reputation()) / 100;
}

function ap_get_reputation_info($meta){
	$info = array(
		'answer' 		=> sprintf(__('Answered a question %s'), '<a href="'.get_permalink($meta->apmeta_actionid).'">'. get_the_title($meta->apmeta_actionid)) .'</a>',
		'question' 		=> sprintf(__('Asked %s'), '<a href="'.get_permalink($meta->apmeta_actionid).'">'.get_the_title($meta->apmeta_actionid)).'</a>',
		'comment' 		=> sprintf(__('Commented %s'), '<a href="'.get_comment_link($meta->apmeta_actionid).'">'. get_comment_text($meta->apmeta_actionid)).'</a>',
		'selecting_answer' => sprintf(__('Selected a best answer for %s'), '<a href="'.get_permalink($meta->apmeta_actionid).'">'. get_the_title($meta->apmeta_actionid)).'</a>',
		'vote_up' 		=> sprintf(__('Received a down vote on %s %s'), get_post_type($meta->apmeta_actionid), '<a href="'.get_permalink($meta->apmeta_actionid).'">'.get_the_title($meta->apmeta_actionid) ).'</a>',
		'vote_down' 	=> sprintf(__('Received a down vote on on %s %s'), get_post_type($meta->apmeta_actionid), '<a href="'.get_permalink($meta->apmeta_actionid).'">'.get_the_title($meta->apmeta_actionid).'</a>' ),
		'voted_down' 	=> sprintf(__('Voted down on %s'), get_post_type($meta->apmeta_actionid) ),
		'best_answer' 	=> sprintf(__('Answer on a question is selected as best, %s'), get_post_type($meta->apmeta_actionid) ),
	);

	if(isset($info[$meta->apmeta_param]))
		return $info[$meta->apmeta_param];
}