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
		add_action( 'wp_ajax_ap_vote_on_post', array($this, 'ap_vote_on_post') ); 
		add_action( 'wp_ajax_nopriv_ap_vote_on_post', array($this, 'ap_vote_nopriv') ); 
		add_action( 'wp_ajax_ap_add_to_favorite', array($this, 'ap_add_to_favorite') ); 
		add_action( 'wp_ajax_nopriv_ap_add_to_favorite', array($this, 'ap_add_to_favorite_nopriv') ); 
		
		// vote for closing, ajax request
		add_action( 'wp_ajax_ap_vote_for_close', array($this, 'ap_vote_for_close') ); 
		add_action( 'wp_ajax_nopriv_ap_vote_for_close', array($this, 'ap_nopriv_vote_for_close') ); 
		
		// Follow user
		add_action( 'wp_ajax_ap_follow', array($this, 'ap_follow') ); 
		add_action( 'wp_ajax_nopriv_ap_follow', array($this, 'ap_follow') ); 
		
		add_action( 'wp_ajax_ap_submit_flag_note', array($this, 'ap_submit_flag_note') ); 
    }

		
	function ap_append_vote_count($post){
		if(!is_question() && ($post->post_type == 'question' || $post->post_type == 'answer')){
			$post->net_vote = ap_net_vote_meta($post->ID);
			$post->selected 	= get_post_meta($post->ID, ANSPRESS_SELECTED_META, true);
			$post->closed 		= get_post_meta($post->ID, ANSPRESS_CLOSE_META, true);
		}elseif($post->post_type == 'question' || $post->post_type == 'answer'){
			
			//voted up count
			if(is_object($post)){
				$votes = ap_post_votes($post->ID);				
				// net vote
				$post->voted_up 	= $votes['voted_up'];
				$post->voted_down 	= $votes['voted_down'];
				$post->net_vote 	= $votes['voted_up'] - $votes['voted_down'];
				
				//closed count
				$post->closed 		= get_post_meta($post->ID, ANSPRESS_CLOSE_META, true);
				$post->selected 	= get_post_meta($post->ID, ANSPRESS_SELECTED_META, true);
				
				//flagged count
				$post->flag = get_post_meta($post->ID, ANSPRESS_FLAG_META, true);
				
				//favorite count
				$post->favorite 	= get_post_meta($post->ID, ANSPRESS_FAV_META, true);
				
				$post->voted_closed = ap_is_user_voted_closed();					
				$post->flagged = ap_is_user_flagged();

				//if current logged in user voted
				if(is_user_logged_in()){
					$post->favorited 	= ap_is_user_favorite($post->ID);
					$userid = get_current_user_id();
					$post->user_voted_up = ap_is_user_voted($post->ID, 'vote_up', $userid);	
					$post->user_voted_down = ap_is_user_voted($post->ID, 'vote_down', $userid);	
				}
			}
		}
	}

	
	// process ajax voting request 	 
	function ap_vote_on_post(){
		$args = explode('-', sanitize_text_field($_POST['args']));
		if(wp_verify_nonce( $args[2], 'vote_'.$args[1] )){

			$value 	= $args[0] == 'up' ? 1 : -1;
			$type 	= $args[0] == 'up' ? 'vote_up' : 'vote_down' ;
			$userid = get_current_user_id();
			
			$is_voted = ap_is_user_voted($args[1], 'vote', $userid) ;

			if(is_object($is_voted) && $is_voted->count > 0){
				// if user already voted and click that again then reverse
				if($is_voted->type == $type){
					$row = ap_remove_vote($type, $userid, $args[1]);
					$counts = ap_post_votes($args[1]);
					
					//update post meta
					update_post_meta($args[1], ANSPRESS_VOTE_META, $counts['net_vote']);
					
					do_action('ap_undo_vote', $args[1], $counts, $value);
					
					$action = 'undo';
					$count = $counts['net_vote'] ;
					$message = __('Your vote has been removed', 'ap');
					
					$result = apply_filters('ap_undo_vote_result', array('row' => $row, 'action' => $action, 'type' => $args[0], 'count' => $count, 'message' => $message));
					
					ap_do_event('undo_'.$type, $args[1], $counts);

				}else{
					$result = array('action' => false, 'message' => __('Undo your vote first', 'ap'));
				}				
					
			}else{
				
				$row = ap_add_vote($userid, $type, $args[1]);				
				$counts = ap_post_votes($args[1]);
				
				//update post meta
				update_post_meta($args[1], ANSPRESS_VOTE_META, $counts['net_vote']);				
				do_action('ap_voted_'.$type, $args[1], $counts);
				
					
				$action = 'voted';
				$count = $counts['net_vote'] ;
				$message = __('Thank you for voting', 'ap');
				
				$result = apply_filters('ap_cast_vote_result', array('row' => $row, 'action' => $action, 'type' => $args[0], 'count' => $count, 'message' => $message));
				ap_do_event($type, $args[1], $counts);
			}			
			
		}else{
			$result = array('action' => false, 'message' => __('Unable to process your vote, try again', 'ap'));
		}
		
		die(json_encode($result));
	}
	
	function ap_vote_nopriv(){
		echo json_encode(array('action'=> false, 'message' =>__('Please login for voting on question & answer', 'ap')));
		die();
	}

		
	// add to favorite ajax action	 
	function ap_add_to_favorite(){
		$args = explode('-', sanitize_text_field($_POST['args']));
		
		if(wp_verify_nonce( $args[1], 'favorite_'.$args[0] )){

			$is_favorite = ap_is_user_favorite($args[0]);	
			$userid		 = get_current_user_id();	
			
			if($is_favorite){
				// if already in favorite list then remove	
				$row = ap_remove_vote('favorite', $userid, $args[0]);
				
				$counts = ap_post_favorite($args[0]);
				
				//update post meta
				update_post_meta($args[0], ANSPRESS_FAV_META, $counts);
				
				//register an action
				do_action('ap_removed_favorite', $args[1], $counts);
				
				$title = __('Add to favorite list', 'ap');
				$action = 'removed';
				$message = __('Removed question from your favorite list', 'ap');
			}else{
				$row = ap_add_vote($userid, 'favorite', $args[0]);
				$counts = ap_post_favorite($args[0]);
				
				//update post meta
				update_post_meta($args[0], ANSPRESS_FAV_META, $counts);
				
				//register an action
				do_action('ap_added_favorite', $args[0], $counts);
				
				$title = __('Remove from favorite list', 'ap');
				$message = __('Added question to your favorite list', 'ap');
				$action = 'added';
			}
			if( $counts =='1' && $action == 'added')
				$text = __('You favorited this question', 'ap'); 
			elseif($action == 'added')
				$text = sprintf( __( 'You and %s others favorited this question', 'ap' ), ($counts -1));
			else
				$text =  sprintf( __( '%s people favorited this question', 'ap' ), $counts);
			
			$result = apply_filters('ap_favorite_result', array('row' => $row, 'action' => $action, 'count' => '&#9733; '.$counts, 'title' => $title, 'text' => $text, 'message' => $message));
			echo json_encode($result);
			
		}else{
			echo json_encode(array('action'=> false, 'message' =>__('Failed to process this action', 'ap')));	
		}
		
		die();
	}

	
	function ap_add_to_favorite_nopriv(){
		echo json_encode(array('action'=> false, 'message' =>__('Please login for adding question to your favorite', 'ap')));
		die();
	}
	
	function ap_vote_for_close(){
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
	
	function ap_nopriv_vote_for_close(){
		echo json_encode(array('action'=> false, 'message' =>__('Please login for requesting closing this question.', 'ap')));
		die();
	}
	
	public function ap_follow(){
		$args = $_POST['args'];
		if(wp_verify_nonce( $args['nonce'], 'follow_'.$args['user'] )){
			$userid = (int)sanitize_text_field($args['user']);
			
			$user_following = ap_is_user_voted($userid, 'follow', get_current_user_id());
			
			$user 			= get_userdata( $userid );
			$user_name 		= $user->data->display_name;
			if (!is_user_logged_in()){
				$action = 'pleazelogin';
				$message = sprintf(__('Register or log in to follow %s', 'ap'), $user_name);
			}	
			elseif(!$user_following){
				$row 	= ap_add_vote(get_current_user_id(), 'follow', $userid);
				$action = 'follow';
				$text 	= __('Unfollow','ap');
				$title 	= sprintf(__('Unfollow %s', 'ap'), $user_name);
				$message = sprintf(__('You are now following %s', 'ap'), $user_name);
			}else{
				$row = ap_remove_vote('follow', get_current_user_id(), $userid);
				$action = 'unfollow';
				$text 	= __('Follow','ap');
				$title 	= sprintf(__('Follow %s', 'ap'), $user_name);
				$message = sprintf(__('You unfollowed %s', 'ap'), $user_name);
			}
				
			if($row !== FALSE){
				$followers = ap_count_vote(false, 'follow', $userid);
				$following = ap_count_vote(get_current_user_id(), 'follow');
				update_user_meta( $userid, AP_FOLLOWERS_META, $followers);
				update_user_meta( get_current_user_id(), AP_FOLLOWING_META, $following);
				
				
				
				$result = apply_filters('ap_follow_result', array('row' => $row, 'action' => $action, 'text' => $text, 'id' => $userid, 'title' => $title, 'message' => $message, 'following_count' => $following, 'followers_count' => $followers ));
				
				echo json_encode($result);
			}else{
				echo json_encode(array('action' => false, 'message' => _('Unable to process your request, please try again.', 'ap')));
			}

		}else{
			echo json_encode(array('action' => false, 'message' => _('Something went wrong', 'ap')));
		}
		die();
	}
	
	// vote for closing, ajax request 
	public function ap_submit_flag_note(){
		$args = explode('-', sanitize_text_field($_POST['args']));
		$note_id = sanitize_text_field($_POST['note_id']);
		$other_note = sanitize_text_field($_POST['other_note']);
		
		if(wp_verify_nonce( $args[1], 'flag_submit_'.$args[0] )){
			global $wpdb;
			$userid = get_current_user_id();
			$is_flagged = ap_is_user_flagged($args[0]);
			
			if($is_flagged){
				// if already then return
				echo json_encode(array('action' => false, 'message' => __('You already flagged this post', 'ap')));			
			}else{
				if($note_id != 'other')
					$row = ap_add_flag($userid, $args[0], $note_id);
				else
					$row = ap_add_flag($userid, $args[0], NULL, $other_note);
					
				$counts = ap_post_flag_count($args[0]);
				//update post meta
				update_post_meta($args[0], ANSPRESS_FLAG_META, $counts);
				
				echo json_encode(array('row' => $row, 'action' => 'flagged', 'text' => __('Flag','ap').' ('.$counts.')','title' =>  __('You have flagged this post', 'ap'), 'message' => __('This post is notified to moderator. Thank you for helping us', 'ap')));
			}
			
		}else{
			echo '0'.__('Please try again', 'ap');	
		}
		
		die();
	}
}

function ap_add_vote($userid, $type, $actionid){	
	return ap_add_meta($userid, $type, $actionid );
}

function ap_remove_vote($type, $userid, $actionid){
	return ap_delete_meta(array('apmeta_type' => $type, 'apmeta_userid' => $userid, 'apmeta_actionid' => $actionid));
}

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

//check if user voted on the post
function ap_is_user_voted($actionid, $type, $userid){
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

//check if user added post to favorite
function ap_is_user_favorite($postid){
	if(is_user_logged_in()){
		$userid = get_current_user_id();
		$done = ap_meta_user_done('favorite', $userid, $postid);
		return $done > 0 ? true : false;
	}
	return false;
}

function ap_post_favorite($postid = false){
	//favorite count
	global $post;

	$postid = $postid ? $postid : $post->ID;
	return ap_meta_total_count('favorite', $postid);
}



// voting html
function ap_vote_html($post = false){
	if(!$post)
		global $post;
		
	$nonce = wp_create_nonce( 'vote_'.$post->ID );

	?>
		<div data-action="vote" data-id="<?php echo $post->ID; ?>" class="ap-voting net-vote">
			<a class="ap-icon-thumbsup vote-up<?php echo ($post->user_voted_up) ? ' voted' :''; echo $post->user_voted_down ? ' disable' :''; ?>" data-args="up-<?php echo $post->ID.'-'.$nonce; ?>" href="#" title="<?php _e('Up vote this post', 'ap'); ?>"></a>
			
			<span class="net-vote-count" data-view="ap-net-vote" itemprop="upvoteCount"><?php echo ap_net_vote(); ?></span>
			
			<a class="ap-icon-thumbsdown vote-down<?php echo ($post->user_voted_down) ? ' voted' :''; echo ($post->user_voted_up) ? ' disable' :''; ?>" data-args="down-<?php echo $post->ID.'-'.$nonce; ?>" href="#" title="<?php _e('Down vote this post', 'ap'); ?>"></a>
		</div>
	<?php
}


/* -----------------------------------------------------
---------- favorite button---------------------------- */

// favorite button
function ap_favorite_html($post = false){
	if(!$post)
		global $post;
	
	$nonce = wp_create_nonce( 'favorite_'.$post->ID );
	$title = (!$post->favorite) ? (__('Add to favorite list', 'ap')) : (__('Remove from favorite list', 'ap'));
	?>
		<div class="favorite-c">
			<a id="<?php echo 'favorite_'.$post->ID; ?>" class="favorite-btn <?php echo ($post->favorited) ? ' added' :''; ?>" data-action="ap-favorite" data-args="<?php echo $post->ID.'-'.$nonce; ?>" href="#"title="<?php echo $title; ?>">&#9733; <?php echo $post->favorite; ?></a>
			<span> 
				<?php  
					if( $post->favorite =='1' && $post->favorited)
						_e('You favorited this question', 'ap'); 
					elseif($post->favorited)
						printf( __( 'You and %s others favorited this question', 'ap' ), ($post->favorite -1));
					elseif($post->favorite == 0)
						 _e( 'Be the first to add this question to favorite', 'ap' );
					else
						printf( _n( '%s person favorited this question', '%s persons favorited this question', $post->favorite, 'ap' ), $post->favorite); 
				?>
			</span>
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

// closing vote html
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

// flag button html
function ap_flag_btn_html(){
	if(!is_user_logged_in())
		return;
		
	global $post;
	$nonce = wp_create_nonce( 'flag_'.$post->ID );
	$title = (!$post->flagged) ? (__('Flag this post', 'ap')) : (__('You have flagged this post', 'ap'));
	?>
		<a id="<?php echo 'flag_'.$post->ID; ?>" data-action="flag-modal" class="flag-btn<?php echo (!$post->flagged) ? ' can-flagged' :''; ?>" data-args="<?php echo $post->ID.'-'.$nonce; ?>" href="#<?php echo 'flag_modal_'.$post->ID; ?>" title="<?php echo $title; ?>"><?php _e('Flag ', 'ap'); echo ($post->flag > 0 ? '<span>('.$post->flag.')</span>':''); ?></a>
	<?php
}

// vote for closing, ajax request
add_action( 'wp_ajax_ap_flag_note_modal', 'ap_flag_note_modal' );  
function ap_flag_note_modal(){
	$args = explode('-', sanitize_text_field($_POST['args']));
	if(wp_verify_nonce( $args[1], 'flag_'.$args[0] )){
		$nonce = wp_create_nonce( 'flag_submit_'.$args[0] );
		?>
		<div class="ap-modal flag-note" id="<?php echo 'flag_modal_'.$args[0]; ?>" tabindex="-1" role="dialog">
			<div class="ap-modal-bg"></div>
			<div class="ap-modal-content">
				<div class="ap-modal-header">					
					<h4 class="ap-modal-title"><?php _e('I am flagging this post because', 'ap'); ?><span class="ap-modal-close">&times;</span></h4>
				</div>
				<div class="ap-modal-body">
				<?php 
					if(ap_opt('flag_note'))
					foreach( ap_opt('flag_note') as $k => $note){
						echo '<div class="note clearfix">';
						echo '<div class="note-radio pull-left"><input type="radio" name="note_id" value="'.$k.'" /></div>';
						echo '<div class="note-desc">';
						echo '<h4>'.$note['title'].'</h4>';
						echo '<p>'.$note['description'].'</p>';
						echo '</div>';
						echo '</div>';
					}
				?>
				<div class="note clearfix">
					<div class="note-radio pull-left"><input type="radio" name="note_id" value="other" /></div>
					<div class="note-desc">
						<h4><?php _e('Other (needs moderator attention)', 'ap'); ?></h4>
						<p><?php _e('This post needs a moderator\'s attention. Please describe exactly what\'s wrong. ', 'ap'); ?></p>
						<textarea id="other-note" class="other-note" name="other_note"></textarea>
					</div>
				</div>
				</div>
				<div class="ap-modal-footer">
					<input id="submit-flag-question" type="submit" data-update="<?php echo $args[0]; ?>" data-args="<?php echo $args[0].'-'.$nonce; ?>" class="btn btn-primary btn-sm" value="<?php _e('Flag post', 'ap'); ?>" />
				</div>
			</div>
		  
		  
		</div>
		<?php
		
	}else{
		echo '0_'.__('Please try again', 'ap');	
	}
	
	die();
}

function ap_follow_btn_html($userid, $small = false){
	if(get_current_user_id() == $userid)
		return;
		
	$followed = ap_is_user_voted($userid, 'follow', get_current_user_id());
	$text = $followed ? __('Unfollow', 'ap') : __('Follow', 'ap');
	echo '<a class="btn ap-btn ap-follow-btn '.($followed ? 'ap-unfollow '.ap_icon('unfollow') : ap_icon('follow')).($small ? ' ap-tip' : '').'" href="#" data-action="ap-follow" data-args=\''.json_encode(array('user' => $userid, 'nonce' => wp_create_nonce( 'follow_'.$userid))).'\' title="'.$text.'">'.($small ? '' : $text).'</a>';
}