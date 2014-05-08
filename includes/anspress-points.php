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
class anspress_points {

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
		add_action( 'save_post', array($this, 'new_question') );
		add_action( 'save_post', array($this, 'new_answer') );
		add_action('comment_post', array($this, 'new_comment'), 10 ,2);
		add_action('ap_voted_up', array($this, 'upvoted'));
		add_action('ap_voted_down', array($this, 'downvoted'));
		add_action('ap_undo_vote', array($this, 'undo_vote'), 10 ,2);
		
	}
	
	public function new_question($pid) {	
		$post = get_post($pid);
		if($post->post_type == 'question'){
			$uid = $post->post_author;
			global $wpdb;
			$count = (int) $wpdb->get_var("select count(id) from `".$wpdb->base_prefix."ap_points` where `type`='question' and `data`=". $pid);

			if($count==0){
				ap_points('question', $uid, apply_filters('ap_question_points',ap_opt('question_points')), $pid);
			}
		}
		return;
	}	
	public function new_answer($pid) {	
		$post = get_post($pid);
		if($post->post_type == 'answer'){
			$uid = $post->post_author;
			global $wpdb;
			$count = (int) $wpdb->get_var("select count(id) from `".$wpdb->base_prefix."ap_points` where `type`='answer' and `data`=". $pid);

			if($count==0){
				ap_points('answer', $uid, apply_filters('ap_answer_points',ap_opt('answer_points')), $pid);
			}
		}
		return;
	}
	public function new_comment($cid, $status) {
		$cdata = get_comment($cid);
		if($status == 1){
			do_action('ap_new_comment_point', $cid);
			ap_points('comment', ap_current_user_id(), apply_filters('ap_new_comment_point', ap_opt('comment_points')), $cid);
		}
	}	
	public function upvoted($pid) {	
		$post = get_post($pid);
		$uid = $post->post_author;
		
		ap_points('vote_up', $uid, apply_filters('ap_up_vote_points',ap_opt('up_vote_points')), $pid);
	}	
	public function downvoted($pid) {	
		$post = get_post($pid);
		$uid = $post->post_author;
		
		ap_points('vote_down', $uid, apply_filters('ap_down_vote_points',ap_opt('down_vote_points')), $pid);
	}	
	public function undo_vote($pid, $up_down) {	
		$post = get_post($pid);
		$uid = $post->post_author;
		
		if($up_down > 0)
			$point = (-1 * apply_filters('ap_up_vote_points',ap_opt('up_vote_points')));
		else
			$point = (-1 * apply_filters('ap_down_vote_points',ap_opt('down_vote_points')));
		
		ap_points('undo_vote', $uid, apply_filters('ap_undo_vote_points', $point), $pid);
	}
	
}

// get user points
function ap_get_points($uid) {
	$points = get_user_meta($uid, 'ap_points', 1);
	if ($points == '') {
		return 0;
	} else {
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

// add posits logs to DB
function ap_point_log($type, $uid, $points, $data){
	$userinfo = get_userdata($uid);
	
	if($userinfo->user_login=='')
		return false; 
		
	if($points==0 && $type!='reset')
		return false; 
		
	global $wpdb;
	$wpdb->insert(
		$wpdb->base_prefix.'ap_points',
		array(
			'uid' => $uid,
			'type' => $type,
			'data' => $data,
			'points' => $points,
			'timestamp' => time()
		),
		array('%d', '%s', '%s', '%d', '%d')
	);
	
	do_action('ap_point_log', $type, $uid, $points, $data);
	
	return true;
}

function ap_set_points($type, $uid, $points, $data){
	$points = apply_filters('ap_set_points',$points, $type, $uid, $data);
	$difference = $points - ap_get_points($uid);
	ap_update_points($uid, $points);
	ap_point_log($type, $uid, $difference, $data);
}