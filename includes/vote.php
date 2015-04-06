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


class AnsPress_Vote_Ajax extends AnsPress_Ajax
{
	public function __construct()
	{
		add_action( 'ap_ajax_subscribe_question', array($this, 'subscribe_question') ); 
		add_action( 'ap_ajax_vote', array($this, 'vote') ); 
		add_action( 'ap_ajax_flag_post', array($this, 'flag_post') ); 
	}

	public function subscribe_question()
	{
		if(!wp_verify_nonce( $_POST['__nonce'], 'subscribe_'. (int)$_POST['question_id'] ) ){
			ap_send_json(ap_ajax_responce('something_wrong'));
			return;
		}

		if(!is_user_logged_in()){
			ap_send_json(ap_ajax_responce('please_login'));
			return;
		}

		if(wp_verify_nonce( $_POST['__nonce'], 'subscribe_'. (int)$_POST['question_id'] )){

			$question_id = (int)$_POST['question_id'];

			$is_subscribed = ap_is_user_subscribed( $question_id );	
			$userid		 = get_current_user_id();	

			if($is_subscribed){
				// if already subscribed then remove	
				ap_remove_vote('subscriber', $userid, $question_id);
				
				$counts = ap_post_subscribers_count($question_id);
				
				//update post meta
				update_post_meta($question_id, ANSPRESS_SUBSCRIBER_META, $counts);
				
				//register an action
				do_action('ap_removed_subscriber', $question_id, $counts);

				ap_send_json(ap_ajax_responce(array('message' => 'unsubscribed', 'action' => 'unsubscribed', 'container' => '#subscribe_'.$question_id, 'do' => 'updateHtml', 'html' => ap_icon('unmute', true).__('subscribe', 'ap'))));
				return;
			}else{
				ap_add_vote($userid, 'subscriber', $question_id);
				$counts = ap_post_subscribers_count($question_id);
				
				//update post meta
				update_post_meta($question_id, ANSPRESS_SUBSCRIBER_META, $counts);
				
				//register an action
				do_action('ap_added_subscriber', $question_id, $counts);
				
				ap_send_json(ap_ajax_responce(array('message' => 'subscribed', 'action' => 'subscribed', 'container' => '#subscribe_'.$question_id, 'do' => 'updateHtml', 'html' => ap_icon('mute', true).__('unsubscribe', 'ap'))));
			}
			
		}
	}

	/**
	 * Process voting button
	 * @return void
	 * @since 2.0.1.1
	 */
	public function vote()
	{
		$post_id = (int)$_POST['post_id'];

		if(!wp_verify_nonce( $_POST['__nonce'], 'vote_'. $post_id ) ){
			ap_send_json(ap_ajax_responce('something_wrong'));
			return;
		}

		if(!is_user_logged_in()){
			ap_send_json(ap_ajax_responce('please_login'));
			return;
		}

		$post = get_post($post_id);
		if($post->post_author == get_current_user_id()){
			ap_send_json(ap_ajax_responce('cannot_vote_own_post'));
			return;
		}

		$type = sanitize_text_field( $_POST['type'] );

		$type 	= ($type == 'up' ? 'vote_up' : 'vote_down') ;
		$userid = get_current_user_id();
		
		$is_voted = ap_is_user_voted($post_id, 'vote', $userid) ;

		if(is_object($is_voted) && $is_voted->count > 0){
			// if user already voted and click that again then reverse
			if($is_voted->type == $type){
				ap_remove_vote($type, $userid, $post_id);
				$counts = ap_post_votes($post_id);
	
				//update post meta
				update_post_meta($post_id, ANSPRESS_VOTE_META, $counts['net_vote']);
				
				do_action('ap_undo_vote', $post_id, $counts);
				
				$action = 'undo';
				$count = $counts['net_vote'] ;
				do_action('ap_undo_'.$type, $post_id, $counts);

				ap_send_json(ap_ajax_responce(array('action' => $action, 'type' => $type, 'count' => $count, 'message' => 'undo_vote')));
			}else{
				ap_send_json(ap_ajax_responce('undo_vote_your_vote'));
			}				
				
		}else{

			ap_add_vote($userid, $type, $post_id);				
			$counts = ap_post_votes($post_id);
			
			//update post meta
			update_post_meta($post_id, ANSPRESS_VOTE_META, $counts['net_vote']);				
			do_action('ap_'.$type, $post_id, $counts);			
				
			$action = 'voted';
			$count = $counts['net_vote'] ;
			ap_send_json(ap_ajax_responce(array('action' => $action, 'type' => $type, 'count' => $count, 'message' => 'voted')));
		}			

	}

	/**
	 * Flag a post as inappropriate
	 * @return void
	 * @since 2.0.0-alpha2
	 */
	public function flag_post()
	{
		$post_id = (int)$_POST['post_id'];
		if(!wp_verify_nonce( $_POST['__nonce'], 'flag_'. $post_id  ) && is_user_logged_in()){
			ap_send_json(ap_ajax_responce('something_wrong'));
			return;
		}

		$userid = get_current_user_id();
		$is_flagged = ap_is_user_flagged( $post_id );
		
		if($is_flagged){
			ap_send_json(ap_ajax_responce(array('message' => 'already_flagged')));
			echo json_encode(array('action' => false, 'message' => __('You already flagged this post', 'ap')));			
		}else{

			ap_add_flag($userid, $post_id);
				
			$count = ap_post_flag_count( $post_id );
			
			//update post meta
			update_post_meta($post_id, ANSPRESS_FLAG_META, $count);
			ap_send_json(ap_ajax_responce(array('message' => 'flagged', 'action' => 'flagged', 'view' => array($post_id.'_flag_count' => $count),  'count' => $count)));
		}			
		
		die();
	}

}
new AnsPress_Vote_Ajax();

class anspress_vote
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
		add_action( 'the_post', array($this, 'ap_append_vote_count') );
		// vote for closing, ajax request
		add_action( 'wp_ajax_ap_vote_for_close', array($this, 'ap_vote_for_close') ); 
		add_action( 'wp_ajax_nopriv_ap_vote_for_close', array($this, 'ap_nopriv_vote_for_close') ); 
		
    }

	/**
	 * Append variable to post Object
	 * @param  Object $post
	 * @return object
	 * @since unknown
	 */
	public function ap_append_vote_count($post){
		if($post->post_type == 'question' || $post->post_type == 'answer'){
             if(is_object($post)){

                //$votes = ap_post_votes($post->ID);              
                // net vote
               // $post->voted_up     = $votes['voted_up'];
               // $post->voted_down   = $votes['voted_down'];
                $post->net_vote     = ap_net_vote_meta($post->ID);
            }
        }
	}

	
	public function ap_add_to_subscribe_nopriv(){
		echo json_encode(array('action'=> false, 'message' =>__('Please login for adding question to your subscribe', 'ap')));
		die();
	}
	
	public function ap_vote_for_close(){
		$args = explode('-', sanitize_text_field($_POST['args']));
		if(wp_verify_nonce( $args[1], 'close_'.$args[0] )){

			$voted_closed = ap_is_user_voted_closed($args[0]);
			$type =  'close';
			$userid = get_current_user_id();
			
			if($voted_closed){
				// if already in voted for close then remove it
				$row = ap_remove_vote($type, $userid, $args[0]);
	
				$counts = ap_post_close_vote($args[0]);
				//update post meta
				update_post_meta($args[0], ANSPRESS_CLOSE_META, $counts);
				
				$result = apply_filters('ap_cast_unclose_result', array('row' => $row, 'action' => 'removed', 'text' => __('Close','ap').' ('.$counts.')', 'title' => __('Vote for closing', 'ap'), 'message' => __('Your close request has been removed', 'ap') ));
				
			}else{
				$row = ap_add_vote($userid, $type, $args[0]);

				$counts = ap_post_close_vote($args[0]);
				//update post meta
				update_post_meta($args[0], ANSPRESS_CLOSE_META, $counts);

				$result = apply_filters('ap_cast_close_result', array('row' => $row, 'action' => 'added', 'text' => __('Close','ap').' ('.$counts.')', 'title' => __('Undo your vote', 'ap'), 'message' => __('Your close request has been sent', 'ap') ));

			}
			
		}else{
			$result = array('action' => false, 'message' => _('Something went wrong', 'ap'));
		}
		
		die(json_encode($result));
	}
	
	public function ap_nopriv_vote_for_close(){
		echo json_encode(array('action'=> false, 'message' =>__('Please login for requesting closing this question.', 'ap')));
		die();
	}	

}

/**
 * @param string $type
 */
function ap_add_vote($userid, $type, $actionid){	
	return ap_add_meta($userid, $type, $actionid );
}

/**
 * @param string $type
 */
function ap_remove_vote($type, $userid, $actionid){
	return ap_delete_meta(array('apmeta_type' => $type, 'apmeta_userid' => $userid, 'apmeta_actionid' => $actionid));
}

/**
 * @param string $type
 */
function ap_count_vote($userid = false, $type, $actionid =false, $value = 1){
	global $wpdb;
	if(!$userid){
		return ap_meta_total_count($type, $actionid);		
	}elseif($userid && !$actionid){
		return ap_meta_total_count($type, false, $userid);
	}
}

// get $post up votes
function ap_up_vote($echo = false){
	global $post;
	
	if($echo) echo $post->voted_up;
	else return $post->voted_up;
}

// get $post down votes
function ap_down_vote($echo = false){
	global $post;
	
	if($echo) echo $post->voted_down;
	else return $post->voted_down;
}

// get $post net votes
function ap_net_vote($post =false){
	if(!$post)
		global $post;
	$net= $post->net_vote;
	return $net ? $net : 0;
}

function ap_net_vote_meta($post_id =false){
	if(!$post_id)
		$post_id = get_the_ID();
	$net= get_post_meta($post_id, ANSPRESS_VOTE_META, true);
	return $net ? $net : 0;
}

function ap_post_votes($postid){
	$vote = array();
	//voted up count
	$vote['voted_up'] = ap_meta_total_count('vote_up', $postid);
	
	//voted down count
	$vote['voted_down'] = ap_meta_total_count('vote_down', $postid);
	
	// net vote
	$vote['net_vote'] = $vote['voted_up'] - $vote['voted_down'];

	return $vote;
}

/**
 * Check if user voted on given post.
 * @param  	integer $actionid
 * @param  	string $type     
 * @param  	int $userid   
 * @return 	boolean           
 * @since 	2.0
 */
function ap_is_user_voted($actionid, $type, $userid = false){
	if(false === $userid)
		$userid = get_current_user_id();

	if($type == 'vote' && is_user_logged_in()){
		global $wpdb;
		
		$query = $wpdb->prepare('SELECT apmeta_type as type, IFNULL(count(*), 0) as count FROM ' .$wpdb->prefix .'ap_meta where (apmeta_type = "vote_up" OR apmeta_type = "vote_down") and apmeta_userid = %d and apmeta_actionid = %d GROUP BY apmeta_type', $userid, $actionid);
		
		$key = md5($query);

		$user_done = wp_cache_get($key, 'counts');

		if($user_done === false){
			$user_done = $wpdb->get_row($query);	
			wp_cache_set($key, $user_done, 'counts');
		}
			
		return $user_done;
		
	}elseif(is_user_logged_in()){
		$done = ap_meta_user_done($type, $userid, $actionid);
		return $done > 0 ? true : false;
	}
	return false;
}

//check if user added post to subscribe
function ap_is_user_subscribed($postid){
	if(is_user_logged_in()){
		$userid = get_current_user_id();
		$done = ap_meta_user_done('subscriber', $userid, $postid);
		return $done > 0 ? true : false;
	}
	return false;
}

function ap_post_subscribers_count($postid = false){
	$postid = $postid ? $postid : get_question_id();
	return ap_meta_total_count('subscriber', $postid);
}

/**
 * Output voting button
 * @param  int $post 
 * @return null|string
 * @since 0.1
 */
function ap_vote_btn($post = false, $echo = true){
	if(false === $post)
		global $post;

	if('answer' == $post->post_type && ap_opt('disable_voting_on_answer'))
		return;

	if('question' == $post->post_type && ap_opt('disable_voting_on_question'))
		return;
		
	$nonce 	= wp_create_nonce( 'vote_'.$post->ID );
	$vote 	= ap_is_user_voted( $post->ID , 'vote');

	$voted 	= $vote ? true : false;
	$type 	= $vote ? $vote->type : '';

	ob_start();
	?>
		<div data-id="<?php echo $post->ID; ?>" class="ap-vote net-vote" data-action="vote">
			<a class="<?php echo ap_icon('vote_up') ?> ap-tip vote-up<?php echo $voted ? ' voted' :''; echo ($type == 'vote_down') ? ' disable' :''; ?>" data-query="ap_ajax_action=vote&type=up&post_id=<?php echo $post->ID; ?>&__nonce=<?php echo $nonce ?>" href="#" title="<?php _e('Up vote this post', 'ap'); ?>"></a>
			
			<span class="net-vote-count" data-view="ap-net-vote" itemprop="upvoteCount"><?php echo ap_net_vote(); ?></span>
			
			<a data-tipposition="bottom" class="<?php echo ap_icon('vote_down') ?> ap-tip vote-down<?php echo $voted ? ' voted' :''; echo ($type == 'vote_up') ? ' disable' :''; ?>" data-query="ap_ajax_action=vote&type=down&post_id=<?php echo $post->ID; ?>&__nonce=<?php echo $nonce ?>" href="#" title="<?php _e('Down vote this post', 'ap'); ?>"></a>
		</div>
	<?php
	$html = ob_get_clean();

	if($echo){
		echo $html;
	}else{
		return $html;
	}
}

/**
 * Output subscribe btn HTML
 * @return string
 * @since 2.0.1
 */
function ap_subscribe_btn_html($question_id = false){
	if(!$question_id)
		$question_id = get_question_id();
	
	//$total_favs = ap_post_subscribers_count($question_id);
	$subscribed = ap_is_user_subscribed($question_id);

	$nonce = wp_create_nonce( 'subscribe_'.$question_id );
	$title = (!$subscribed) ? ap_icon('unmute', true).(__('subscribe', 'ap')) : ap_icon('mute', true).(__('unsubscribe', 'ap'));

	?>
		<div class="ap-subscribe<?php echo ($subscribed) ? ' active' :''; ?> clearfix">
			<a id="<?php echo 'subscribe_'.$question_id; ?>" href="#" class="ap-btn subscribe-btn <?php echo ($subscribed) ? ' active' :''; ?>" data-query="ap_ajax_action=subscribe_question&question_id=<?php echo $question_id ?>&__nonce=<?php echo $nonce ?>" data-action="ap_subscribe" data-args="<?php echo $question_id.'-'.$nonce; ?>"><?php echo $title ?></a>
		</div>
	<?php
}


/* ------------close button----------------- */

// post close vote count
function ap_post_close_vote($postid = false){
	global $post;

	$postid = $postid ? $postid : $post->ID;
	return ap_meta_total_count('close', $postid);
}

//check if user voted for close
function ap_is_user_voted_closed($postid = false){	
	if(is_user_logged_in()){
		global $post;
		$postid = $postid ? $postid : $post->ID;
		$userid = get_current_user_id();
		$done = ap_meta_user_done('close', $userid, $postid);
		return $done > 0 ? true : false;		
	}
	return false;
}

//TODO: re-add closing system as an extension
function ap_close_vote_html(){
	if(!is_user_logged_in())
		return;
		
	global $post;
	$nonce = wp_create_nonce( 'close_'.$post->ID );
	$title = (!$post->voted_closed) ? (__('Vote for closing', 'ap')) : (__('Undo your vote', 'ap'));
	?>
		<a id="<?php echo 'close_'.$post->ID; ?>" data-action="close-question" class="close-btn<?php echo ($post->voted_closed) ? ' closed' :''; ?>" data-args="<?php echo $post->ID.'-'.$nonce; ?>" href="#" title="<?php echo $title; ?>">
			<?php _e('Close ', 'ap'); echo ($post->closed > 0 ? '<span>('.$post->closed.')</span>' : ''); ?>
		</a>	
	<?php
}


/* ---------------Flag btn-------------------
------------------------------------------- */
/**
 * @param integer $actionid
 */
function ap_add_flag($userid, $actionid, $value =NULL, $param =NULL){	
	return ap_add_meta($userid, 'flag', $actionid, $value, $param );
}

// count flags on the post
function ap_post_flag_count($postid=false){
	global $post;

	$postid = $postid ? $postid : $post->ID;
	return ap_meta_total_count('flag', $postid);
}

//check if user flagged on post
function ap_is_user_flagged($postid = false){
	if(is_user_logged_in()){
		global $post;
		$postid = $postid ? $postid : $post->ID;
		$userid = get_current_user_id();
		$done = ap_meta_user_done('flag', $userid, $postid);
		return $done > 0 ? true : false;
	}
	return false;
}

/**
 * Flag button html
 * @return string 
 * @since 0.9
 */
function ap_flag_btn_html($echo = false){
	if(!is_user_logged_in())
		return;
		
	global $post;
	$flagged 	= ap_is_user_flagged();
	$total_flag = ap_post_flag_count();
	$nonce 		= wp_create_nonce( 'flag_'.$post->ID );
	$title 		= (!$flagged) ? (__('Flag this post', 'ap')) : (__('You have flagged this post', 'ap'));
	
	$output ='<a id="flag_'.$post->ID.'" data-query="ap_ajax_action=flag_post&post_id='.$post->ID .'&__nonce='.$nonce.'" data-action="ap_subscribe" class="flag-btn'. (!$flagged ? ' can-flagged' :'') .'" href="#" title="'.$title.'">'. __('Flag ', 'ap') . '<span class="ap-data-view ap-view-count-'.$total_flag.'" data-view="'.$post->ID .'_flag_count">'.$total_flag.'</span></a>';

	if($echo)
		echo $output;
	else
		return $output;
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
	$total_subscribers = ap_post_subscribers_count($post->ID);

	if( $total_subscribers =='1' && $subscribed)
		return __('Only you are subscribed to this question.', 'ap'); 
	elseif($subscribed)
		return sprintf( __( 'You and <strong>%s people</strong> subscribed to this question.', 'ap' ), ($total_subscribers -1));
	elseif($total_subscribers == 0)
		return __( 'No one is subscribed to this question.', 'ap' );
	else
		return sprintf( __( '<strong>%d people</strong> subscribed to this question.', 'ap' ), $total_subscribers);	
}