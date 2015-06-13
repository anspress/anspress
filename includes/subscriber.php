<?php
/**
 * AnsPress subscribe and subscriber related functions
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * Insert subscriber for question or term
 * @param  integer  		$user_id   	WP user ID
 * @param  integer  		$action_id 	Question ID or Term ID
 * @param  boolean|integer 	$sub_id    	Any sub ID
 * @param  boolean|integer 	$type      	Type of subscriber, empty string for question
 * @return bollean|integer
 */
function ap_add_subscriber($user_id, $action_id, $type = false, $sub_id = false){

	if($type === 'category')
		$subscribe_type =  'category' ;

	elseif($type === 'tag')
		$subscribe_type =  'tag' ;

	else
		$subscribe_type =  '' ;
	
	$row = ap_add_meta($user_id, 'subscriber', $action_id, $sub_id, $subscribe_type);

	if($row !== false)
		do_action('ap_added_subscriber', $action_id, $subscribe_type, $sub_id);

	return $row;
}

/**
 * Remove subscriber for question or term
 * @param  integer  		$user_id   	WP user ID
 * @param  integer  		$action_id 	Question ID or Term ID
 * @param  boolean|integer 	$sub_id    	Any sub ID
 * @param  boolean|integer 	$type      	Type of subscriber, empty string for question
 * @return bollean|integer
 */
function ap_remove_subscriber($user_id, $action_id, $type = false, $sub_id = false){
	global $wpdb;

	if($type == 'category')
		$subscribe_type =  "AND apmeta_value = 'category'";

	elseif($type == 'tag')
		$subscribe_type =  "AND apmeta_value = 'tag'" ;

	else
		$subscribe_type =  '' ;

	$param = "";

	if($sub_id !== false)
		$param =  $wpdb->prepare("AND apmeta_param = %d", $sub_id);	

	$row = $wpdb->query( 
		$wpdb->prepare( 
			"DELETE FROM ".$wpdb->prefix."ap_meta
			 WHERE apmeta_actionid = %d
			 AND apmeta_userid = %d
			 AND apmeta_type = 'subscriber' 
			 ".$subscribe_type." ".$param,
	        $action_id, $user_id
        )
	);

	if(FALSE !== $row)
		do_action('ap_removed_subscriber', $user_id, $action_id, $sub_id, $type);

	return $row;
}

/**
 * Check if user is subscribed to question or term
 * @param  integer $action_id Question id or term id
 * @param  integer $user_id User id, default is current user id
 * @param  string|boolean $type Type of subscription, default is question
 * @return boolean
 * @since unknown
 */
function ap_is_user_subscribed($action_id, $user_id = false, $type = false){
	
	if($user_id === false)
		$user_id = get_current_user_id();

	if($user_id > 0){

		if($type === 'category')
			$subscribe_type =  'category' ;

		elseif($type === 'tag')
			$subscribe_type =  'tag' ;

		else
			$subscribe_type =  false ;

		$row = ap_meta_user_done('subscriber', $user_id, $action_id, $subscribe_type);
		
		return $row > 0 ? true : false;
	}

	return false;
}

/**
 * Return the count of subscribers for question or term
 * @param  integer $action_id Question id or term_id
 * @param  string $type Type of subscription
 * @return integer
 */
function ap_subscribers_count($action_id = false, $type = ''){
	$subscribe_type = $type != '' ? 'subscriber_'.$type : 'subscriber' ;
	$action_id = $action_id ? $action_id : get_question_id();
	return ap_meta_total_count( $subscribe_type, $action_id );
}

/**
 * Return subscriber count in human readable format
 * @return string
 * @since 2.0.0-alpha2
 */
function ap_subscriber_count_html($post = false)
{
	if(!$post)
		global $post;
	
	$subscribed = ap_is_user_subscribed($post->ID);
	$total_subscribers = ap_subscribers_count($post->ID);

	if( $total_subscribers =='1' && $subscribed)
		return __('Only you are subscribed to this question.', 'ap'); 
	elseif($subscribed)
		return sprintf( __( 'You and <strong>%s people</strong> subscribed to this question.', 'ap' ), ($total_subscribers -1));
	elseif($total_subscribers == 0)
		return __( 'No one is subscribed to this question.', 'ap' );
	else
		return sprintf( __( '<strong>%d people</strong> subscribed to this question.', 'ap' ), $total_subscribers);	
}

function ap_question_subscribers($action_id = false, $type = '', $avatar_size = 30){
	global $question_category, $question_tag;

	if(!$action_id){
		if(is_question())
			$action_id = get_question_id();
		elseif(is_question_category())
			$action_id = $question_category->term_id;
		elseif(is_question_tag())
			$action_id = $question_tag->term_id;
	}

	if($type=='')
		$type = is_question() ? '' : 'term' ;

	$subscribe_type = $type != '' && $type != 'subscriber' ? $type : 'subscriber' ;

	$subscribers = ap_get_subscribers( $action_id, $subscribe_type );

	if($subscribers){
		echo '<div class="ap-question-subscribers clearfix">';
			echo '<div class="ap-question-subscribers-inner">';
			foreach($subscribers as $subscriber){
				echo '<a href="'.ap_user_link($subscriber->apmeta_userid).'"';
				ap_hover_card_attributes($subscriber->apmeta_userid);
				echo '>'.get_avatar($subscriber->apmeta_userid, $avatar_size).'</a>';
			}
			echo '</div>';
		echo '</div>';
	}
}

/**
 * Return all subscribers of a question
 * @param  integer $question_id
 * @return array
 * @since 2.1
 */
function ap_get_subscribers($action_id, $type = false){
	global $wpdb;

	if($type === 'category')
		$subscribe_type =  'category' ;

	elseif($type === 'tag')
		$subscribe_type =  'tag' ;

	else
		$subscribe_type =  false ;

	$where = array(
		'apmeta_type' => array('value' => 'subscriber', 'compare' => '=', 'relation' => 'AND') 
	);

	if($subscribe_type !== false)
		$where['apmeta_param'] = array('value' => $subscribe_type, 'compare' => '=', 'relation' => 'AND');

	$where['apmeta_actionid'] = array('value' => $action_id, 'compare' => '=', 'relation' => 'AND');

	return ap_get_all_meta(array(
		'where' => $where,
		'group' => array(
			'apmeta_userid' => array('relation' => 'AND'),
		)));
}

/**
 * Subscribe a question
 * @param  integer  		$question_id
 * @param  boolean|integer 	$user_id
 * @return boolean|array
 */
function ap_add_question_subscriber($question_id, $user_id = false){
	$is_subscribed = ap_is_user_subscribed( $question_id );

	if($user_id === false)
		$user_id = get_current_user_id();

	if($user_id < 1)
		return false;

	if(!$is_subscribed){

		ap_add_subscriber($user_id, $question_id);
		
		$counts = ap_subscribers_count($question_id);
		
		//update post meta
		update_post_meta($question_id, ANSPRESS_SUBSCRIBER_META, $counts);

		return array('count' => $counts, 'action' => 'subscribed');
	}

	return false;
}

/**
 * Unscubscribe user from a question
 * @param  integer  $question_id Questions ID
 * @param  boolean|integer $user_id
 * @return boolean|array
 */
function ap_remove_question_subscriber($question_id, $user_id = false){
	$is_subscribed = ap_is_user_subscribed( $question_id );

	if($user_id === false)
		$user_id = get_current_user_id();	

	if($is_subscribed){

		ap_remove_subscriber($user_id, $question_id);
		
		$counts = ap_subscribers_count($question_id);
		
		//update post meta
		update_post_meta($question_id, ANSPRESS_SUBSCRIBER_META, $counts);

		return array('count' => $counts, 'action' => 'unsubscribed');

	}

	return false;
}



/**
 * Output subscribe btn HTML
 * @param boolean|integer $action_id Question ID or Term ID
 * @return string
 * @since 2.0.1
 */
function ap_subscribe_btn_html($action_id = false, $type = false){

	global $question_category, $question_tag;

	if($action_id === false){
		if(is_question())
			$action_id = get_question_id();
		elseif(is_question_category())
			$action_id = $question_category->term_id;
		elseif(is_question_tag())
			$action_id = $question_tag->term_id;
	}
	
	if($type == false){
		if(is_question_category())
			$subscribe_type =  'category' ;
		elseif(is_question_tag())
			$subscribe_type =  'tag' ;
		else
			$subscribe_type =  false ;
	}else{
		if($type === 'category')
			$subscribe_type =  'category' ;
		elseif($type === 'tag')
			$subscribe_type =  'tag' ;
		else
			$subscribe_type =  false ;
	}
	
	$subscribed = ap_is_user_subscribed($action_id, false, $subscribe_type);

	$nonce = wp_create_nonce( 'subscribe_'.$action_id.'_'.$subscribe_type );
	$title = (!$subscribed) ? __('Subscribe', 'ap') : __('Unsubscribe', 'ap');
	
	?>
	<div class="ap-subscribe" id="<?php echo 'subscribe_'.$action_id; ?>">
		<a href="#" class="ap-btn-toggle<?php echo ($subscribed) ? ' active' :''; ?>" data-query="ap_ajax_action=subscribe&action_id=<?php echo $action_id ?>&__nonce=<?php echo $nonce ?>&type=<?php echo $subscribe_type; ?>" data-action="ap_subscribe" data-args="<?php echo $action_id.'-'.$nonce; ?>">
			<span class="apicon-toggle-on"></span>
			<span class="apicon-toggle-off"></span>
		</a>
		<b><?php echo $title ?></b>
	</div>

	<?php
}
