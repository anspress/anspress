<?php
/**
 * AnsPress participants functions.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */


/* Insert participant  */
/**
 * @param string $action
 */
function ap_add_parti($post_id, $user_id, $action, $param = false){
	if(is_user_logged_in()){
		$rows = ap_add_meta($user_id, 'parti', $post_id, $action, $param);
		
		/* Update the meta only if successfully created */
		if($rows !== false){
			$current_parti = ap_get_parti($post_id, true);
			update_post_meta($post_id, ANSPRESS_PARTI_META, $current_parti);
		}
	}
}

/* Remove particpants from db when user delete its post or comment */
function ap_remove_parti($post_id, $user_id = false, $value = false){
	$where = array('apmeta_type' => 'parti', 'apmeta_actionid' => $post_id, 'apmeta_userid' => $user_id);
	
	if($value !== false)
		$where['apmeta_value'] = $value;
	
	$rows = ap_delete_meta($where);
	
	/* Update the meta only if successfully deleted */
	if($rows !== false){
		$current_parti = ap_get_parti($post_id, true);
		update_post_meta($post_id, ANSPRESS_PARTI_META, $current_parti );
	}
}

function ap_get_parti($post_id = false, $count = false, $param = false){
	global $wpdb;
	if($count){		
		return ap_meta_total_count('parti', $post_id, false, 'apmeta_userid');
	}else{

		$where = array(
			'apmeta_type' => array('value' => 'parti', 'compare' => '=', 'relation' => 'AND'), 
		);

		if($post_id !== false)
			$where['apmeta_actionid'] = array('value' => $post_id, 'compare' => '=', 'relation' => 'AND');

		if($param !== false)
			$where['apmeta_param'] = array('value' => $param, 'compare' => '=', 'relation' => 'AND');

		return ap_get_all_meta(array(
			'where' => $where,
			'group' => array(
				'apmeta_userid' => array('relation' => 'AND'),
			)));
	}
}

/**
 * Print all particpants of a question
 * @param  integer $avatar_size
 * @param  boolean $post_id
 * @return void
 * @since  0.4
 */
function ap_get_all_parti($avatar_size = 40, $post_id = false){
	if(!$post_id)
		$post_id = get_question_id();
		
	$parti = ap_get_parti($post_id);

	echo '<span class="ap-widget-title">'. sprintf( _n('<span>1</span> Participant', '<span>%d</span> Participants', count($parti), 'anspress-question-answer'), count($parti)) .'</span>';
	
	echo '<div class="ap-participants-list clearfix">';	
	foreach($parti as $p){
		echo'<a title="'.ap_user_display_name($p->apmeta_userid, true).'" href="'.ap_user_link($p->apmeta_userid).'" class="ap-avatar">';
		echo get_avatar($p->apmeta_userid, $avatar_size);
		echo'</a>';
	}	
	echo '</div>';
	
}

function ap_get_parti_emails($post_id){
	$parti = ap_get_parti($post_id);
	
	if(!$parti)
		return false;
	
	$emails = array();
	foreach ($parti as $p){
		$email = get_the_author_meta( 'user_email', $p->apmeta_userid);
		if($email)
			$emails[$p->apmeta_userid] = get_the_author_meta( 'user_email', $p->apmeta_userid);
	}
	return $emails;
}

