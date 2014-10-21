<?php
/**
 * AnsPress.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

/*  some code used here were inspired and taken from CubePoints plugin */
class AP_Points {

	/**
	 * Instance of this class.
	 */
	protected static $instance = null;
	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	public function __construct(){
		add_action('ap_event_new_question', array($this, 'new_question'), 10, 2);
		add_action('ap_event_untrash_answer', array($this, 'new_question'), 10, 2);
		add_action('ap_event_delete_question', array($this, 'delete_question'), 10, 2);
		
		add_action('ap_event_new_answer', array($this, 'new_answer'), 10, 2);
		add_action('ap_event_untrash_answer', array($this, 'new_answer'), 10, 2);
		add_action('ap_event_delete_answer', array($this, 'delete_answer'), 10, 2);
		
		add_action('ap_event_select_answer', array($this, 'select_answer'), 10, 3);
		add_action('ap_event_unselect_answer', array($this, 'unselect_answer'), 10, 3);
		
		add_action('ap_event_vote_up', array($this, 'vote_up'), 10, 2);
		add_action('ap_event_vote_down', array($this, 'vote_down'), 10, 2);
		add_action('ap_event_undo_vote_up', array($this, 'undo_vote_up'), 10, 2);
		add_action('ap_event_undo_vote_down', array($this, 'undo_vote_down'), 10, 2);
		
		add_action('ap_event_new_comment', array($this, 'new_comment'), 10, 2);
		add_action('ap_event_delete_comment', array($this, 'delete_comment'), 10, 2);
	}
	
	public function new_question($postid, $userid) {
		$point = ap_point_by_event('new_question', true);
		return ap_points('question', $userid, $point, $postid);
	}
	
	public function delete_question($postid, $userid) {
		$point = ap_point_by_event('new_question', true);
		return ap_point_log_delete('question', $userid, $point, $postid);
	}
	
	public function new_answer($postid, $userid) {
		$point = ap_point_by_event('new_answer', true);
		return ap_points('answer', $userid, $point, $postid);
	}
	
	public function delete_answer($postid, $userid) {
		$point = ap_point_by_event('new_answer', true);
		return ap_point_log_delete('answer', $userid, $point, $postid);
	}
	
	public function select_answer($userid, $question_id, $answer_id){
		$point = ap_point_by_event('select_answer', true);
		$selector_point = ap_point_by_event('selecting_answer', true);
		$answer = get_post($answer_id);
		
		if($answer->post_author != $userid)
			ap_points('best_answer', $answer->post_author, $point, $answer_id);
			
		ap_points('selecting_answer', $userid, $selector_point, $answer_id);
		return;
	}	
	
	public function unselect_answer($userid, $question_id, $answer_id){
		$point = ap_point_by_event('select_answer', true);
		$selector_point = ap_point_by_event('selecting_answer', true);
		$answer = get_post($answer_id);
		
		if($answer->post_author != $userid){
			ap_point_log_delete('best_answer', $answer->post_author, $point, $answer_id);
		}
		ap_point_log_delete('selecting_answer', $userid, $selector_point, $answer_id);
		return;
	}
	
	public function vote_up($postid, $counts) {
		$post = get_post($postid);
		
		// give points to post author
		if($post->post_type == 'question')
			$point = ap_point_by_event('question_upvote', true);
		elseif($post->post_type == 'answer')
			$point = ap_point_by_event('answer_upvote', true);
		
		$uid = $post->post_author;
		
		ap_points('vote_up', $uid, $point, $postid);
		
		if($post->post_type == 'question')
			$point = ap_point_by_event('question_upvoted', true);
		elseif($post->post_type == 'answer')
			$point = ap_point_by_event('answer_upvoted', true);
		
		$userid = get_current_user_id();
		
		ap_points('vote_up', $userid, $point, $postid);
	}
	
	public function vote_down($postid, $counts) {	
		$post = get_post($postid);
		
		// give points to post author
		if($post->post_type == 'question')
			$point = ap_point_by_event('question_downvote', true);
		elseif($post->post_type == 'answer')
			$point = ap_point_by_event('answer_downvote', true);
		
		$uid = $post->post_author;		
		ap_points('down_vote', $uid, $point, $postid);
		
		// give point to user casting vote
		$userid = get_current_user_id();
		if($post->post_type == 'question')
			$point = ap_point_by_event('question_downvoted', true);
		elseif($post->post_type == 'answer')
			$point = ap_point_by_event('answer_downvoted', true);
		
		$uid = $post->post_author;		
		ap_points('down_voted', $userid, $point, $postid);
	}
	
	public function undo_vote_up($postid, $counts) {	
		$post = get_post($postid);
		
		// give points to post author
		if($post->post_type == 'question')
			$point = ap_point_by_event('question_upvote', true);
		elseif($post->post_type == 'answer')
			$point = ap_point_by_event('answer_upvote', true);
		
		$uid = $post->post_author;
		
		ap_point_log_delete('vote_up', $uid, $point, $postid);
		
		if($post->post_type == 'question')
			$point = ap_point_by_event('question_upvoted', true);
		elseif($post->post_type == 'answer')
			$point = ap_point_by_event('answer_upvoted', true);
		
		$userid = get_current_user_id();
		
		ap_point_log_delete('vote_up', $userid, $point, $postid);
	}
	
	public function undo_vote_down($postid, $counts) {	
		$post = get_post($postid);
		
		// give points to post author
		if($post->post_type == 'question')
			$point = ap_point_by_event('question_downvote', true);
		elseif($post->post_type == 'answer')
			$point = ap_point_by_event('answer_downvote', true);
		
		$uid = $post->post_author;		
		ap_point_log_delete('down_vote', $uid, $point, $postid);
		
		// give point to user casting vote
		$userid = get_current_user_id();
		if($post->post_type == 'question')
			$point = ap_point_by_event('question_downvoted', true);
		elseif($post->post_type == 'answer')
			$point = ap_point_by_event('answer_downvoted', true);
		
		$uid = $post->post_author;		
		ap_point_log_delete('down_voted', $userid, $point, $postid);
	}
	
	public function new_comment($comment, $post_type){
		$point = ap_point_by_event('new_comment', true);
		ap_points('comment', $comment->user_id, $point, $comment->comment_ID);
	}
	
	public function delete_comment($comment, $post_type){
		$point = ap_point_by_event('new_comment', true);
		ap_point_log_delete('comment', $comment->user_id, $point, $comment->comment_ID);
	}
	
}

function ap_point_option(){
	$data  	= wp_cache_get('ap_points', 'ap');
	if($data === false){
		$opt 	= get_option('ap_points');
		$data 	= (is_array($opt) ? $opt : array()) + ap_default_points();
		$data 	= apply_filters('ap_point_option', $data);
		wp_cache_set('ap_points', $data, 'ap');
	}
	return $data;
}

function ap_point_by_id($id){
	$opt = ap_point_option();
	foreach( $opt as $point)
		if($point['id'] == $id)
			return $point;
	
	return false;
}

function ap_point_by_event($event, $only_point = false){
	$opt = ap_point_option();
	foreach( $opt as $point)
		if($point['event'] == $event){
			if($only_point)
				return $point['points'];
			else
				return $point;
		}
	return false;
}


function ap_point_option_new($title, $desc, $points, $event){
	$opt 	= ap_point_option();
	$opt[] = array(
		'id' => count($opt),
		'title' => $title,
		'description' => $desc,
		'points' => $points,
		'event' => $event,
	);
	return update_option('ap_points', $opt);
}

function ap_point_option_update($id, $title, $desc, $points, $event){
	$opt 	= ap_point_option();
	foreach($opt as $k => $p){
		if($p['id'] == $id){
			$opt[$k]['title'] 		= $title;
			$opt[$k]['description'] = $desc;
			$opt[$k]['points'] 		= $points;
			$opt[$k]['event'] 		= $event;
		}
	}
	return update_option('ap_points', $opt);
}

function ap_point_option_delete($id){
	$opt 	= ap_point_option();
	foreach($opt as $k => $p){
		if($p['id'] == $id){
			unset($opt[$k]);
		}
	}
	return update_option('ap_points', $opt);
}

// get user points
function ap_get_points($uid = false, $short = false) {
	if(!$uid)
		$uid = get_current_user_id();
		
	$points = get_user_meta($uid, 'ap_points', true);
	
	if ($points == '') {
		return 0;
	} else {
		if($short)
			return ap_short_num( $points );
		
		return $points;
	}
}

function ap_points($type, $uid, $points, $data){
	$points = apply_filters('ap_points',$points, $type, $uid, $data);
	ap_alter_points($uid, $points);
	ap_point_log($type, $uid, $points, $data);
}

//update points
function ap_update_points($uid, $points) {
	// no negative points 
	if ($points < 0) {
	  $points = 0;
	}
	update_user_meta($uid, 'ap_points', $points);
}

function ap_alter_points($uid, $points) {
	ap_update_points($uid, ap_get_points($uid) + $points);
}

// add points logs to DB
function ap_point_log($type, $uid, $points, $data){
	$userinfo = get_userdata($uid);
	
	if($userinfo->user_login=='')
		return false; 
		
	if($points==0 && $type!='reset')
		return false; 
	
	return ap_add_meta($uid, 'point', $data, $points, $type);
}

function ap_point_log_delete($type, $uid, $points =NULL, $data =NULL){
	$new_point = ap_get_points($uid) - $points;
	
	$row = ap_delete_meta(array('apmeta_type' => 'point', 'apmeta_userid' => $uid, 'apmeta_actionid' => $data, 'apmeta_value' => $points, 'apmeta_param' => $type ));
	update_user_meta($uid, 'ap_points', $new_point);
}


function ap_set_points($type, $uid, $points, $data){
	$points = apply_filters('ap_set_points',$points, $type, $uid, $data);
	$difference = $points - ap_get_points($uid);
	ap_update_points($uid, $points);
	ap_point_log($type, $uid, $difference, $data);
}

function ap_default_points(){
	$points = array(
		array(
			'id'       		=> 1,
			'title'       	=> __('New Registration', 'ap'),
			'description' 	=> __('Points given to newly registered user.', 'ap'),
			'points'      	=> '1',
			'event'    		=> 'registration'
		),
		array(
			'id'       		=> 2,
			'title'       	=> __('Uploading avatar', 'ap'),
			'description' 	=> __('Awarded for uploading an profile picture.', 'ap'),
			'points'      	=> '2',
			'event'    		=> 'uploaded_avatar'
		),
		array(
			'id'       		=> 3,
			'title'       	=> __('Completing profile', 'ap'),
			'description' 	=> __('Awarded for completing profile fields.', 'ap'),
			'points'      	=> '2',
			'event'    		=> 'uploaded_avatar'
		),
		array(
			'id'       		=> 4,
			'title'       	=> __('Question', 'ap'),
			'description' 	=> __('For asking a question.', 'ap'),
			'points'      	=> '2',
			'event'    		=> 'new_question'
		),
		array(
			'id'       		=> 5,
			'title'       	=> __('Answer', 'ap'),
			'description' 	=> __('For answering a question.', 'ap'),
			'points'      	=> '10',
			'event'    		=> 'new_answer'
		),
		array(
			'id'       		=> 6,
			'title'       	=> __('Comment', 'ap'),
			'description' 	=> __('For new comment.', 'ap'),
			'points'      	=> '1',
			'event'    		=> 'new_comment'
		),
		array(
			'id'       		=> 7,
			'title'       	=> __('Receive upvote on question', 'ap'),
			'description' 	=> __('When user receive an upvote on question', 'ap'),
			'points'      	=> '2',
			'event'    		=> 'question_upvote'
		),
		array(
			'id'       		=> 8,
			'title'       	=> __('Receive upvote on answer', 'ap'),
			'description' 	=> __('When user receive an upvote on answer', 'ap'),
			'points'      	=> '5',
			'event'    		=> 'answer_upvote'
		),
		array(
			'id'       		=> 9,
			'title'       	=> __('Receive down vote on question', 'ap'),
			'description' 	=> __('When user receive an down vote on question', 'ap'),
			'points'      	=> '-1',
			'event'    		=> 'question_downvote'
		),
		array(
			'id'       		=> 10,
			'title'       	=> __('Receive down vote on answer', 'ap'),
			'description' 	=> __('When user receive an down vote on answer', 'ap'),
			'points'      	=> '-3',
			'event'    		=> 'answer_downvote'
		),
		array(
			'id'       		=> 11,
			'title'       	=> __('Up voted questions', 'ap'),
			'description' 	=> __('When user upvote others question', 'ap'),
			'points'      	=> '0',
			'event'    		=> 'question_upvoted'
		),
		array(
			'id'       		=> 12,
			'title'       	=> __('Up voted answers', 'ap'),
			'description' 	=> __('When user upvote others answer', 'ap'),
			'points'      	=> '0',
			'event'    		=> 'answer_upvoted'
		),
		array(
			'id'       		=> 13,
			'title'       	=> __('Down voted questions', 'ap'),
			'description' 	=> __('When user down vote others question', 'ap'),
			'points'      	=> '-1',
			'event'    		=> 'question_downvoted'
		),
		array(
			'id'       		=> 14,
			'title'       	=> __('Down voted answers', 'ap'),
			'description' 	=> __('When user down vote others answers', 'ap'),
			'points'      	=> '-1',
			'event'    		=> 'answer_downvoted',
			'negative'    	=> true
		),
		array(
			'id'       		=> 15,
			'title'       	=> __('Best answer', 'ap'),
			'description' 	=> __('When user\'s answer get selected as best', 'ap'),
			'points'      	=> '10',
			'event'    		=> 'select_answer'
		),
		array(
			'id'       		=> 16,
			'title'       	=> __('Selecting answer', 'ap'),
			'description' 	=> __('When user user select an answer.', 'ap'),
			'points'      	=> '2',
			'event'    		=> 'selecting_answer'
		),
	);
	
	return $points;
}

function ap_received_point_post($post_id){
	$point 		= ap_point_by_event('question_upvote', true);
	$vote_count = ap_meta_total_count('vote_up', $post_id);
	return $vote_count*$point;
}