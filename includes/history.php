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
     * Initialize the class
     * and styles.
     */
    public function __construct()
    {
		add_action('ap_after_new_answer', array($this, 'new_answer'));		
		add_action('ap_after_update_question', array($this, 'edit_question'));
		add_action('ap_after_update_answer', array($this, 'edit_answer'));
		add_action('ap_publish_comment', array($this, 'new_comment'));
		add_action('ap_select_answer', array($this, 'select_answer'), 10, 3);
		add_action('ap_unselect_answer', array($this, 'unselect_answer'), 10, 3);
		//add_action( 'set_object_terms', array($this, 'after_label_added'), 10, 2 );
		//add_action( 'deleted_term_relationships', array($this, 'after_label_removed'), 10, 2 );
		
	}
	public function new_answer($answer_id) {
		$post = get_post($answer_id);
		ap_add_history(get_current_user_id(), $post->post_parent, '', 'new_answer');
	}
	
	public function edit_question($post_id) {
		ap_add_history(get_current_user_id(), $post_id, '', 'edit_question');
	}
	
	public function edit_answer($post_id) {
		ap_add_history(get_current_user_id(), $post_id, '', 'edit_answer');
	}
	
	public function new_comment($comment){
		$post = get_post($comment->comment_post_ID); 
		if($post->post_type == 'question'){
			ap_add_history($comment->user_id, $comment->comment_post_ID, $comment->comment_ID, 'new_comment');
		}else{
			$answer = get_post($comment->comment_post_ID);
			ap_add_history($comment->user_id, $answer->post_parent, $comment->comment_ID, 'new_comment_answer');
			ap_add_history($comment->user_id, $comment->comment_post_ID, $comment->comment_ID, 'new_comment_answer');
		}
	}
	
	public function select_answer($user_id, $question_id, $answer_id){
		ap_add_history($user_id, $question_id, $answer_id, 'answer_selected');
	}
	
	public function unselect_answer($user_id, $question_id, $answer_id){
		ap_add_history($user_id, $question_id, $answer_id, 'answer_unselected');
	}

}

/**
 * @param string $param
 */
function ap_add_history($userid = false, $post_id, $value, $param=NULL){

	if(!$userid)
		$userid = get_current_user_id();
	
	$opts = array('userid' => $userid, 'actionid' => $post_id, 'value' => $value, 'param' =>$param);
	$opts = apply_filters('ap_add_history_parms', $opts );
	
	extract($opts);
	
	$last_history = ap_get_latest_history($value);

	if($last_history && $last_history['user_id'] == $userid && $last_history['type'] == $param && $last_history['value'] == $value && @$last_history['action_id'] == $post_id){
		$row = ap_update_meta(
			array('apmeta_userid' => $userid, 'apmeta_actionid' => $post_id, 'apmeta_value' => $value, 'apmeta_param' =>$param),
			array('apmeta_userid' => $last_history['user_id'], 'apmeta_actionid' => $last_history['action_id'], 'apmeta_value' => $last_history['value'], 'apmeta_param' => $last_history['type']));
		update_post_meta( $post_id, '__ap_history', array('type' => $param, 'user_id' => $userid, 'date' => current_time( 'mysql' )) );
	}else{
		$row = ap_add_meta($userid, 'history', $post_id, $value, $param );
		update_post_meta( $post_id, '__ap_history', array('type' => $param, 'user_id' => $userid, 'date' => current_time( 'mysql' )) );
	}
	
	do_action('ap_after_history_'.$value, $userid, $post_id, $param);
	do_action('ap_after_inserting_history', $userid, $post_id, $value, $param);
	return $row;
}

function ap_delete_history($user_id, $action_id, $value, $param = null){
	$row = ap_delete_meta(array('apmeta_userid' => $user_id, 'apmeta_type' => 'history', 'apmeta_actionid' => $action_id, 'apmeta_value' => $value, 'apmeta_param' => $param));

	if($row){
		$last_activity = ap_get_latest_history($action_id);
		update_post_meta( $action_id, '__ap_history', array('type' => $last_activity['type'], 'user_id' => $last_activity['user_id'], 'time' => $last_activity['date']) );
	}

	return $row;
}

function ap_get_post_history($post_id){
	if(!$post_id)
		return;
	
	global $wpdb;
	
	$query = $wpdb->prepare('SELECT *, UNIX_TIMESTAMP(apmeta_date) as unix_date FROM ' .$wpdb->prefix .'ap_meta where apmeta_type = "history" AND apmeta_actionid = %d', $post_id);
	
	return ap_get_all_meta(false, 20, $query);
}

function ap_history_title($slug, $parm = ''){
	$title = array(
		'new_question' 		=> __('asked', 'ap'),
		'new_answer' 		=> __('answered', 'ap'),
		'new_comment' 		=> __('commented', 'ap'),
		'new_comment_answer'=> __('comment on answer', 'ap'),
		'edit_question' 	=> __('edited question', 'ap'),
		'edit_answer' 		=> __('edited answer', 'ap'),
		'edit_comment' 		=> __('edited comment', 'ap'),
		'answer_selected' 	=> __('selected answer', 'ap'),
		'answer_unselected' => __('unselected answer', 'ap'),
		'status_updated' 	=> __('updated status', 'ap'),
	);
	$title = apply_filters('ap_history_name', $title);
	
	if(isset($title[$slug]))
		return $title[$slug];
	
	return $slug;	
}


function ap_get_latest_history($post_id){
	global $wpdb;
	
	$query = $wpdb->prepare('SELECT apmeta_id as meta_id, apmeta_userid as user_id, apmeta_actionid as post_id, apmeta_value as value, apmeta_param as type, apmeta_date as date FROM '. $wpdb->prefix .'ap_meta WHERE apmeta_type="history" AND apmeta_actionid=%d ORDER BY apmeta_date DESC', $post_id);
	
	$key = md5($query);
	$cache = wp_cache_get($key, 'ap_meta');
	
	if($cache !== false)
		return $cache;
	
	$result = $wpdb->get_row($query, ARRAY_A);
	wp_cache_set($key, $result, 'ap_meta');
	
	return $result;
}

/**
 * Get last active time
 * @param  init $post_id
 * @return string
 * @since 2.0.1
 */
function ap_last_active_time($post_id = false, $html = true){
	$post = get_post($post_id);
	$post_id = !$post_id ? get_the_ID() : $post_id;

	$history = ap_get_latest_history($post_id);

	if(!$history){
		$history['date'] = get_the_time('c', $post_id);
		$history['user_id'] = $post->post_author;
		$history['type'] 	= 'new_'.$post->post_type;
	}

	if(!$html)
		return $history['date'];

	$title = ap_history_title($history['type']);
	$title = esc_html('<span class="ap-post-history">'.sprintf( __('%s %s about <time datetime="'. mysql2date('c', $history['date']) .'">%s</time> ago', 'ap'), ap_user_display_name($history['user_id']), $title, ap_human_time( mysql2date('U', $history['date'])) ).'</span>');

	return sprintf( __('Active %s ago', 'ap'), '<a class="ap-tip" href="#" title="'. $title .'"><time datetime="'. mysql2date('c', $history['date']) .'">'.ap_human_time( mysql2date('U', $history['date'])) ).'</time></a>';
}

function ap_get_latest_history_html($post_id, $initial = false, $avatar = false, $icon = false){
	$post = get_post($post_id);
	$history = get_post_meta($post_id, '__ap_history', true);

	if(!$history && $initial){
		$history['date'] 	= get_the_time('c', $post_id);
		$history['user_id'] = $post->post_author;
		$history['type'] 	= 'new_'.$post->post_type;
	}

	$html = '';
	if($history){
		if($icon)
			$html .= '<span class="'.ap_icon($history['type']).' ap-tlicon"></span>';
			
		if($avatar)
			$html .= '<a class="ap-avatar" href="'.ap_user_link($history['user_id']).'">'.get_avatar($history['user_id'], 22).'</a>';		

		$title = ap_history_title($history['type']);
		$html .= '<span class="ap-post-history">'.ap_icon('history', true).sprintf( __(' %s %s <time datetime="'. mysql2date('c', $history['date']) .'">%s</time> ago', 'ap'), ap_user_display_name($history['user_id']), $title, ap_human_time( $history['date'], false)) .'</span>';

		
	}
	
	if($html)	
		return apply_filters('ap_latest_history_html', $html);
	
	return false;
}
