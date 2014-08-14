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
	
	}
}

function ap_get_last_history($actionid){
	$last = ap_get_all_meta(array(
		'where' 	=> array(
			'apmeta_type' => array('value' => 'history'),
			'apmeta_actionid' => array('value' => $actionid)
		),
		'orderby' 	=> array(
			'apmeta_date' => array('order' => 'DESC')
		)
	), 1);
	
	if(isset($last[0]))
		return (array)$last[0];
	
	return false;
}

function ap_add_history($userid = false, $actionid, $value, $param=NULL){
	if(!$userid)
		$userid = get_current_user_id();
	
	$opts = array('userid' => $userid, 'actionid' => $actionid, 'value' => $value, 'param' =>$param);
	$opts = apply_filters('ap_add_history_parms', $opts );
	
	extract($opts);
	
	$last_history = ap_get_last_history($actionid);

	if($last_history && $last_history['apmeta_userid'] == $userid && $last_history['apmeta_actionid'] == $actionid && $last_history['apmeta_value'] == $value)
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
		'asked' 			=> __('Asked', 'ap'),
		'answered' 			=> __('Answered', 'ap'),
		'commented' 		=> sprintf(__('Commented on %s', 'ap'), $parm) ,
		'edited_question' 	=> __('Edited question', 'ap'),
		'edited_answer' 	=> __('Edited answer', 'ap'),
		'edited_comment' 	=> __('Edited comment', 'ap'),
	);
	$names = apply_filters('ap_history_name', $names);
	
	if(isset($names[$slug]))
		return $names[$slug];
	
	return $slug;	
}

function ap_get_post_history_list($post_id){
	$history = ap_get_post_history($post_id);	
	echo '<ul class="ap-history-list">';
	foreach($history as $h){
		echo '<li>'.ap_history_name($h->apmeta_value, $h->apmeta_param).'</li>';
	}
	echo '</ul>';
}