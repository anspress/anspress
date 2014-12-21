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
		add_action('ap_event_edit_question', array($this, 'edit_question'), 10, 2);
		add_action('ap_event_edit_answer', array($this, 'edit_answer'), 10, 3);
		add_action('ap_event_new_comment', array($this, 'new_comment'), 10, 3);
		add_action('ap_event_select_answer', array($this, 'select_answer'), 10, 3);
		add_action('ap_event_unselect_answer', array($this, 'unselect_answer'), 10, 3);
		add_action( 'set_object_terms', array($this, 'after_label_added'), 10, 2 );
		add_action( 'deleted_term_relationships', array($this, 'after_label_removed'), 10, 2 );
		
	}
	public function new_answer($answer_id, $userid, $question_id) {
		//ap_add_history($userid, $question_id, $answer_id, 'new_answer');
		ap_add_history($userid, $question_id, '', 'new_answer');
	}
	
	public function edit_question($post_id, $user_id) {
		ap_add_history($user_id, $post_id, '', 'edit_question');
	}
	
	public function edit_answer($postid, $userid, $question_id) {
		ap_add_history($userid, $postid, '', 'edit_answer');
	}
	
	public function new_comment($comment, $post_type, $question_id){
		if($post_type == 'question'){
			ap_add_history($comment->user_id, $comment->comment_post_ID, $comment->comment_ID, 'new_comment');
		}else{
			ap_add_history($comment->user_id, $question_id, $comment->comment_ID, 'new_comment_answer');
			ap_add_history($comment->user_id, $comment->comment_post_ID, $comment->comment_ID, 'new_comment_answer');
		}
	}
	
	public function select_answer($user_id, $question_id, $answer_id){
		ap_add_history($user_id, $question_id, $answer_id, 'answer_selected');
	}
	
	public function unselect_answer($user_id, $question_id, $answer_id){
		ap_add_history($user_id, $question_id, $answer_id, 'answer_unselected');
	}
	public function after_label_added($object_id, $terms){
		if(!is_user_logged_in())
			return false;
			
		ap_add_history(get_current_user_id(), $object_id, implode(',', $terms), 'added_label');
	}
	
	public function after_label_removed($object_id, $tt_ids){
		if(!is_user_logged_in())
			return false;
			
		ap_add_history(get_current_user_id(), $object_id, implode(',', $tt_ids), 'removed_label');
	}
}

function ap_add_history($userid = false, $post_id, $value, $param=NULL){

	if(!$userid)
		$userid = get_current_user_id();
	
	$opts = array('userid' => $userid, 'actionid' => $post_id, 'value' => $value, 'param' =>$param);
	$opts = apply_filters('ap_add_history_parms', $opts );
	
	extract($opts);
	
	$last_history = ap_get_latest_history($value);

	if($last_history && $last_history['user_id'] == $userid && $last_history['type'] == $param && $last_history['value'] == $value && $last_history['action_id'] == $post_id){
		$row = ap_update_meta(
			array('apmeta_userid' => $userid, 'apmeta_actionid' => $post_id, 'apmeta_value' => $value, 'apmeta_param' =>$param),
			array('apmeta_userid' => $last_history['user_id'], 'apmeta_actionid' => $last_history['action_id'], 'apmeta_value' => $last_history['value'], 'apmeta_param' => $last_history['type']));
	}else{
		$row = ap_add_meta($userid, 'history', $post_id, $value, $param );
	}
	
	do_action('ap_after_history_'.$value, $userid, $post_id, $param);
	do_action('ap_after_inserting_history', $userid, $post_id, $value, $param);
	return $row;
}

function es_delete_history($user_id, $action_id, $value, $param = null){
	return ap_delete_meta(array('apmeta_userid' => $user_id, 'apmeta_type' => 'history', 'apmeta_actionid' => $action_id, 'apmeta_value' => $value, 'apmeta_param' => $param));
}

function ap_get_post_history($post_id){
	if(!$post_id)
		return;
	
	global $wpdb;
	
	$query = $wpdb->prepare('SELECT *, UNIX_TIMESTAMP(apmeta_date) as unix_date FROM ' .$wpdb->prefix .'ap_meta where apmeta_type = "history" AND apmeta_actionid = %d', $post_id);
	
	return ap_get_all_meta(false, 20, $query);
}

function ap_history_name($slug, $parm = ''){
	$names = array(
		'new_question' 		=> __('asked', 'ap'),
		'new_answer' 		=> __('answered', 'ap'),
		'new_comment' 		=> __('commented', 'ap'),
		'new_comment_answer'=> __('comment on answer', 'ap'),
		'edit_question' 	=> __('edited question', 'ap'),
		'edit_answer' 		=> __('edited answer', 'ap'),
		'edit_comment' 		=> __('edited comment', 'ap'),
		'answer_selected' 	=> __('selected answer', 'ap'),
		'answer_unselected' => __('unselected answer', 'ap'),
		'added_label' 		=> sprintf(__('added the %s', 'ap'), $parm),
		'removed_label' 	=> sprintf(__('removed %s', 'ap'), $parm),
	);
	$names = apply_filters('ap_history_name', $names);
	
	if(isset($names[$slug]))
		return $names[$slug];
	
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

function ap_get_latest_history_html($post_id, $avatar = false, $icon = false){
	$history = ap_get_latest_history($post_id);

	$html = '';
	if($history){
		if($icon)
			$html .= '<span class="'.ap_icon($history['type']).' ap-tlicon"></span>';
			
		if($avatar)
			$html .= '<a class="ap-savatar" href="'.ap_user_link($history['user_id']).'">'.get_avatar($history['user_id'], 22).'</a>';		
		
		if($history['type'] == 'added_label' || $history['type'] == 'removed_label'){
			$label = '';
			$terms = get_terms( 'question_label', array( 'include' => explode(',', $history['value'])) );
			
			if($terms)
				foreach($terms as $term){
					$label .= ap_label_html($term);
				}
			$label .= ' '._n('label', 'labels', count($terms), 'ap');
			$title = ap_history_name($history['type'], $label); 
		}else{
			$title = ap_history_name($history['type']);
		}
		
		$html .= '<span class="ap-post-history">'.sprintf( __('%s %s about <time class="updated" datetime="'. mysql2date('c', $history['date']) .'">%s</time> ago', 'ap'), ap_user_display_name($history['user_id']), $title, ap_human_time( mysql2date('U', $history['date'])) ).'</span>';

		
	}elseif(!$icon){
		$html = '<span class="ap-post-history">'.sprintf( __('Asked by %s', 'ap'), ap_user_display_name() ).'</span>';
	}
	
	if($html)	
		return apply_filters('ap_latest_history_html', $html);
	
	return false;
}
