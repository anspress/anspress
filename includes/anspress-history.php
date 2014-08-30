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

class AP_History
{
    /**
     * Instance of this class.
     */
    protected static $instance = null;
    /**
     * Return an instance of this class.
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {
        
        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    public function __construct()
    {
		add_action('ap_event_new_answer', array($this, 'new_answer'), 10, 3);
		add_action('ap_event_new_comment', array($this, 'new_comment'), 10, 3);
		add_action('ap_event_edit_question', array($this, 'edit_question'), 10, 3);
	}
	public function new_answer($postid, $userid, $question_id) {
		ap_add_history($userid, $postid, $question_id, 'new_answer');
	}
	public function new_comment($comment, $post_type, $question_id){
		if($post_type == 'question')
			ap_add_history($comment->user_id, $comment->comment_ID, $comment->comment_post_ID, 'new_comment');
		else
			ap_add_history($comment->user_id, $comment->comment_ID, $question_id, 'new_comment_answer');
	}
	public function edit_question($post_id, $user_id) {
		ap_add_history($user_id, $post_id, NULL, 'edit_question');
	}
}

function ap_add_history($userid = false, $actionid, $value, $param=NULL){
	if(!$userid)
		$userid = get_current_user_id();
	
	$opts = array('userid' => $userid, 'actionid' => $actionid, 'value' => $value, 'param' =>$param);
	$opts = apply_filters('ap_add_history_parms', $opts );
	
	extract($opts);
	
	$last_history = ap_get_latest_history($actionid);

	if($last_history && $last_history['user_id'] == $userid && $last_history['action_id'] == $actionid && $last_history['question_id'] == $value)
		$row = ap_update_meta(
			array('apmeta_userid' => $userid, 'apmeta_actionid' => $actionid, 'apmeta_value' => $value, 'apmeta_param' =>$param),
			array('apmeta_userid' => $last_history['apmeta_userid'], 'apmeta_actionid' => $last_history['apmeta_actionid'], 'apmeta_value' => $last_history['apmeta_value']));
	else
		$row = ap_add_meta($userid, 'history', $actionid, $value, $param );
	
	
	do_action('ap_after_history_'.$value, $userid, $actionid, $param);
	do_action('ap_after_inserting_history', $userid, $actionid, $value, $param);
	return $row;
}

function ap_get_post_history($post_id){
	global $wpdb;
	$post_ids = array();
	$comments_id = array();
	
	$ids = ap_get_child_answers_comm($post_id);
	
	$post_ids[] = $post_id;
	if(isset($ids['posts']))
		$post_ids = $ids['posts'];		
	$post_ids = "('".implode("', '", $post_ids)."')";
	
	if(isset($ids['comments']))
		$comments_id = $ids['comments'];		
	$comments_id = "('".implode("', '", $comments_id)."')";
	
	return ap_get_all_meta(false, 20, "SELECT *, UNIX_TIMESTAMP(apmeta_date) as unix_date FROM " .$wpdb->prefix ."ap_meta where apmeta_type = 'history' AND (apmeta_actionid IN $post_ids OR (apmeta_actionid IN $comments_id AND apmeta_value IN ('commented', 'edited_comment', 'deleted_comment', 'updated_comment'))) ");
}

function ap_history_name($slug, $parm = ''){
	$names = array(
		'new_question' 		=> __('Asked', 'ap'),
		'new_answer' 		=> __('Answer', 'ap'),
		'new_comment' 		=> __('Comment', 'ap'),
		'new_comment_answer'=> __('Comment on answer', 'ap'),
		'edit_question' 	=> __('Edited question', 'ap'),
		'edit_answer' 		=> __('Edited answer', 'ap'),
		'edit_comment' 		=> __('Edited comment', 'ap'),
	);
	$names = apply_filters('ap_history_name', $names);
	
	if(isset($names[$slug]))
		return $names[$slug];
	
	return $slug;	
}


function ap_get_latest_history($post_id){
	global $wpdb;
	$query = $wpdb->prepare('SELECT apmeta_id as meta_id, apmeta_userid as user_id, apmeta_actionid as action_id, apmeta_value as question_id, apmeta_param as type FROM '. $wpdb->prefix .'ap_meta WHERE apmeta_type="history" AND apmeta_value=%d ORDER BY apmeta_date DESC', $post_id);
	return $wpdb->get_row($query, ARRAY_A);
}

function ap_get_latest_history_html($post_id){
	$history = ap_get_latest_history($post_id);

	if($history)
		$html = '<span class="ap-post-history">'.sprintf( __('%s by %s', 'ap'), ap_history_name($history['type']), ap_user_display_name($history['user_id']) ).'</span>';
	else
		$html = '<span class="ap-post-history">'.sprintf( __('Asked by %s', 'ap'), ap_user_display_name() ).'</span>';
		
	return apply_filters('ap_latest_history_html', $html);
}
