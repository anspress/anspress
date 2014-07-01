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
		add_action( 'wp_ajax_ap_add_to_favourite', array($this, 'ap_add_to_favourite') ); 
		add_action( 'wp_ajax_nopriv_ap_add_to_favourite', array($this, 'ap_add_to_favourite_nopriv') ); 
    }

	
	// fired on uninstall
	public function uninstall() {
		if ( ap_opt('clear_databse')) {
			
			global $wpdb;
			$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'ap_vote');
		}
	}
		
	function ap_append_vote_count($post){
		if($post->post_type == 'question' || $post->post_type == 'answer'){
			global $wpdb;
			
			//voted up count
			if(is_object($post)){
				$post->voted_up = $wpdb->get_var( "SELECT IFNULL(count(*), 0) FROM " .$wpdb->prefix ."ap_vote where (type = 'vote_post' and `value` = '1') and actionid = $post->ID");
				//voted down count
				$post->voted_down = $wpdb->get_var( "SELECT IFNULL(count(*), 0) FROM " .$wpdb->prefix ."ap_vote where (type = 'vote_post' and `value` = '-1') and actionid = $post->ID");
				
				// net vote
				$post->net_vote = $post->voted_up - $post->voted_down;
				
				//closed count
				$post->closed = ap_post_close_vote();
				
				//flagged count
				$post->flag = ap_post_flag_count();
				
				//favourite count
				$post->favourite = ap_post_favourite();
				
				$post->voted_closed = ap_is_user_voted_closed();
					
				$post->flagged = ap_is_user_flagged();
				
				$post->favourited = $wpdb->get_var( "SELECT count(*) FROM " .$wpdb->prefix ."ap_vote where (type = 'favourite' and `value` = '1') and (`userid` = ".get_current_user_id()." and actionid = $post->ID)");
					
				//if current logged in user voted
				if(is_user_logged_in()){		
					$user_voted = $wpdb->get_var( "SELECT `value` FROM " .$wpdb->prefix ."ap_vote where `type` = 'vote_post' and ( `userid` = ".get_current_user_id()." and `actionid` = $post->ID)");
					$post->user_voted = strlen($user_voted) ? $user_voted : 0;		
				}
			}
		}
	}

	
	// process ajax voting request 	 
	function ap_vote_on_post(){
		$args = explode('-', sanitize_text_field($_POST['args']));
		if(wp_verify_nonce( $args[2], 'vote_'.$args[1] )){
			global $wpdb;
			$value = $args[0] == 'up' ? 1 : -1;
			$is_voted = ap_is_user_voted($args[1]);
			
			if($is_voted){
				// if user already voted and click that again then reverse
				if($is_voted == $value){
					$row = $wpdb->delete($wpdb->prefix.'ap_vote', array('userid'=> get_current_user_id(), 'actionid' =>$args[1], 'type'=> 'vote_post'), array('%d', '%d', '%s') );	
					$counts = ap_post_votes($args[1]);
					
					//update post meta
					update_post_meta($args[1], ANSPRESS_VOTE_META, $counts['net_vote']);
					do_action('ap_undo_vote', $args[1], $value);
					echo $row.'_'.'undo_'.$args[0].'_'.$counts['net_vote'] ;
				}else{
					echo '0_'. __('Please undo your previous vote','ap');
				}
					
			}else{
				$row = $wpdb->insert( $wpdb->prefix.'ap_vote', array('userid'=> get_current_user_id(), 'actionid' =>$args[1], 'type'=> 'vote_post', 'value' => $value), array('%d', '%d', '%s', '%d') );
				$counts = ap_post_votes($args[1]);
				
				//update post meta
				update_post_meta($args[1], ANSPRESS_VOTE_META, $counts['net_vote']);
				
				if($value > 0)
					do_action('ap_voted_up', $args[1]);
				else
					do_action('ap_voted_down', $args[1]);
					
				echo $row.'_'.'voted_'.$args[0].'_'.$counts['net_vote'] ;
			}
			
		}else{
			echo '0'. __('Please try again', 'ap');	
		}
		
		die();
	}

		
	// add to favourite ajax action	 
	function ap_add_to_favourite(){
		$args = explode('-', sanitize_text_field($_POST['args']));
		
		if(wp_verify_nonce( $args[1], 'favourite_'.$args[0] )){
			global $wpdb;
			$is_favourited = ap_is_user_favourited($args[0]);	
			
			if($is_favourited){
				// if already in favourite list then remove	
				$row = $wpdb->delete($wpdb->prefix.'ap_vote', array('userid'=> get_current_user_id(), 'actionid' =>$args[0], 'type'=> 'favourite'), array('%d', '%d', '%s') );	
				
				$counts = ap_post_favourite($args[0]);
				//update post meta
				update_post_meta($args[0], ANSPRESS_FAV_META, $counts);
				$title = __('Add to favourite list', 'ap');
				$action = 'removed';			
			}else{
				$row = $wpdb->insert( $wpdb->prefix.'ap_vote', array('userid'=> get_current_user_id(), 'actionid' =>$args[0], 'type'=> 'favourite', 'value' => 1), array('%d', '%d', '%s', '%d') );
				$counts = ap_post_favourite($args[0]);
				//update post meta
				update_post_meta($args[0], ANSPRESS_FAV_META, $counts);
				$title = __('Remove from favourite list', 'ap');
				$action = 'added';
			}
			if( $counts =='1' && $action == 'added')
				$text = __('You favorited this question', 'ap'); 
			elseif($action == 'added')
				$text = sprintf( __( 'You and %s people favorited this question', 'ap' ), ($counts -1));
			else
				$text =  sprintf( __( '%s people favorited this question', 'ap' ), $counts);
			
			$result = json_encode(array('row' => $row, 'action' => $action, 'count' => $counts, 'title' => $title, 'text' => $text));
			echo $result;
			
		}else{
			echo json_encode(array('action'=> false, 'title' =>__('Please try again', 'ap')));	
		}
		
		die();
	}

	
	function ap_add_to_favourite_nopriv(){
		echo '0_'.__('Please login or register to add this question to favourite', 'ap');
		die();
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

function ap_post_votes($postid){
	global $wpdb;
	$vote = array();
	//voted up count
	$vote['voted_up'] = $wpdb->get_var( "SELECT IFNULL(count(*), 0) FROM " .$wpdb->prefix ."ap_vote where (type = 'vote_post' and `value` = '1') and actionid = $postid");
	//voted down count
	$vote['voted_down'] = $wpdb->get_var( "SELECT IFNULL(count(*), 0) FROM " .$wpdb->prefix ."ap_vote where (type = 'vote_post' and `value` = '-1') and actionid = $postid");
	
	// net vote
	$vote['net_vote'] = $vote['voted_up'] - $vote['voted_down'];
	
	return $vote;
}

//check if user voted on the post
function ap_is_user_voted($postid){
	if(is_user_logged_in()){
		global $wpdb;
		$user_voted = $wpdb->get_var( "SELECT `value` FROM " .$wpdb->prefix ."ap_vote where `type` = 'vote_post' and ( `userid` = ".get_current_user_id()." and `actionid` = $postid)");
		return strlen($user_voted) ? $user_voted : false;		
	}	
	return false;
}

//checck if user added post to favourite
function ap_is_user_favourited($postid){
	if(is_user_logged_in()){
		global $wpdb;
		return $wpdb->get_var( "SELECT count(*) FROM " .$wpdb->prefix ."ap_vote where (type = 'favourite' and `value` = '1') and (`userid` = ".get_current_user_id()." and actionid = $postid)");	
	}
	return;
}

function ap_post_favourite($postid = false){
	//favourite count
	global $post;
	global $wpdb;
	$postid = $postid ? $postid : $post->ID;
	return $wpdb->get_var( "SELECT count(*) FROM " .$wpdb->prefix ."ap_vote where (type = 'favourite' and `value` = '1') and actionid = $postid");
}



// voting html
function ap_vote_html($post = false){
	if(!$post_id)
		global $post;
		
	$nonce = wp_create_nonce( 'vote_'.$post->ID );

	?>
		<div data-action="vote" class="ap-voting net-vote">
			<a class="vote-up<?php echo ($post->user_voted ==1) ? ' voted' :''; echo ($post->user_voted ==-1) ? ' disable' :''; ?>" data-args="up-<?php echo $post->ID.'-'.$nonce; ?>" href="#" title="<?php _e('Up vote this post', 'ap'); ?>">&#9650;</a>
			<span class="net-vote-count"><?php echo ap_net_vote(); ?></span>
			<a class="vote-down<?php echo ($post->user_voted == -1) ? ' voted' :''; echo ($post->user_voted == 1) ? ' disable' :''; ?>" data-args="down-<?php echo $post->ID.'-'.$nonce; ?>" href="#" title="<?php _e('Down vote this post', 'ap'); ?>">&#9660;</a>
		</div>
	<?php
}


/* -----------------------------------------------------
---------- Favourite button---------------------------- */

// favourite button
function ap_favourite_html($post = false){
	if(!$post)
		global $post;
		
	$nonce = wp_create_nonce( 'favourite_'.$post->ID );
	$title = (!$post->favourited) ? (__('Add to favourite list', 'ap')) : (__('Remove from favourite list', 'ap'));
	?>
		<div class="favourite-c">
			<a id="<?php echo 'favourite_'.$post->ID; ?>" class="btn btn-default btn-xs favourite-btn aicon-star<?php echo ($post->favourited) ? ' added' :''; ?>" data-args="<?php echo $post->ID.'-'.$nonce; ?>" href="#"title="<?php echo $title; ?>"><?php echo $post->favourite; ?></a>
			<span> 
				<?php  
					if( $post->favourite =='1' && $post->favourited)
						_e('You favorited this question', 'ap'); 
					elseif($post->favourited)
						printf( __( 'You and %s people favorited this question', 'ap' ), ($post->favourite -1));
					else
						printf( __( '%s people favorited this question', 'ap' ), $post->favourite); 
				?>
			</span>
		</div>
	<?php
}



/* ------------close button----------------- */

// post close vote count
function ap_post_close_vote($postid = false){
	global $post;
	global $wpdb;
	$postid = $postid ? $postid : $post->ID;
	
	return $wpdb->get_var( "SELECT count(*) FROM " .$wpdb->prefix ."ap_vote where (type = 'close' and `value` = '1') and actionid = $postid");
}

//check if user voted for close
function ap_is_user_voted_closed($postid = false){
	if(is_user_logged_in()){
		global $post;
		global $wpdb;
		$postid = $postid ? $postid : $post->ID;
		return $wpdb->get_var( "SELECT count(*) FROM " .$wpdb->prefix ."ap_vote where type = 'close' and (`userid` = ".get_current_user_id()." and actionid = $postid)");	
	}
	return;
}

// closing vote html
function ap_close_vote_html(){
	global $post;
	$nonce = wp_create_nonce( 'close_'.$post->ID );
	$title = (!$post->voted_closed) ? (__('Vote for closing', 'ap')) : (__('Undo your vote', 'ap'));
	?>
		<a id="<?php echo 'close_'.$post->ID; ?>" class="close-btn<?php echo ($post->voted_closed) ? ' closed' :''; ?>" data-args="<?php echo $post->ID.'-'.$nonce; ?>" href="#" title="<?php echo $title; ?>"><?php _e('Close ', 'ap'); echo ($post->voted_closed > 0 ? '<span>'.$post->voted_closed.'</span>' : ''); ?></a>	
	<?php
}


// vote for closing, ajax request
add_action( 'wp_ajax_ap_vote_for_close', 'ap_vote_for_close' );  
function ap_vote_for_close(){
	$args = explode('-', sanitize_text_field($_POST['args']));
	if(wp_verify_nonce( $args[1], 'close_'.$args[0] )){
		global $wpdb;
		$is_favourited = ap_is_user_voted_closed($args[0]);
		
		if($is_favourited){
			// if already in voted for close then remove it
			$row = $wpdb->delete($wpdb->prefix.'ap_vote', array('userid'=> get_current_user_id(), 'actionid' =>$args[0], 'type'=> 'close'), array('%d', '%d', '%s') );	
			$counts = ap_post_close_vote($args[0]);
			//update post meta
			update_post_meta($args[0], ANSPRESS_CLOSE_META, $counts);
			echo $row.'_'.'removed_'.__('close','ap').'('.$counts.')_'. __('Vote for closing', 'ap');			
		}else{
			$row = $wpdb->insert( $wpdb->prefix.'ap_vote', array('userid'=> get_current_user_id(), 'actionid' =>$args[0], 'type'=> 'close', 'value' => 1), array('%d', '%d', '%s', '%d') );
			$counts = ap_post_close_vote($args[0]);
			//update post meta
			update_post_meta($args[0], ANSPRESS_CLOSE_META, $counts);
			echo $row.'_'.'added_'.__('close','ap').'('.$counts.')_'. __('Undo your vote', 'ap');	
		}
		
	}else{
		echo '0'.__('Please try again', 'ap');	
	}
	
	die();
}


/* ---------------Flag btn-------------------
------------------------------------------- */

// count flags on the post
function ap_post_flag_count($postid=false){
	global $post;
	global $wpdb;
	$postid = $postid ? $postid : $post->ID;
	return $wpdb->get_var( "SELECT count(*) FROM " .$wpdb->prefix ."ap_vote where type = 'flag' and actionid = $postid");
}

//check if user flagged on post
function ap_is_user_flagged($postid = false){
	if(is_user_logged_in()){
		global $post;
		global $wpdb;
		$postid = $postid ? $postid : $post->ID;
		return $wpdb->get_var( "SELECT count(*) FROM " .$wpdb->prefix ."ap_vote where type = 'flag' and (`userid` = ".get_current_user_id()." and actionid = $postid)");	
	}
	return;
}

// flag button html
function ap_flag_btn_html(){
	global $post;
	$nonce = wp_create_nonce( 'flag_'.$post->ID );
	$title = (!$post->flagged) ? (__('Flag this post', 'ap')) : (__('You have flagged this post', 'ap'));
	?>
		<a id="<?php echo 'flag_'.$post->ID; ?>" class="flag-btn<?php echo (!$post->flagged) ? ' can-flagged' :''; ?>" data-args="<?php echo $post->ID.'-'.$nonce; ?>" href="#<?php echo 'flag_modal_'.$post->ID; ?>" title="<?php echo $title; ?>"><?php _e('Flag ', 'ap'); echo ($post->flag > 0 ? '<span>'.$post->flag.'</span>':''); ?></a>
	<?php
}

// vote for closing, ajax request
add_action( 'wp_ajax_ap_flag_note_modal', 'ap_flag_note_modal' );  
function ap_flag_note_modal(){
	$args = explode('-', sanitize_text_field($_POST['args']));
	if(wp_verify_nonce( $args[1], 'flag_'.$args[0] )){
		$nonce = wp_create_nonce( 'flag_submit_'.$args[0] );
		?>
		<div class="modal flag-note" id="<?php echo 'flag_modal_'.$args[0]; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		  <div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel"><?php _e('I am flagging this post because', 'ap'); ?></h4>
			  </div>
			  <div class="modal-body">
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
			  <div class="modal-footer">
				<button id="submit-flag-question" type="button" data-update="<?php echo $args[0]; ?>" data-args="<?php echo $args[0].'-'.$nonce; ?>" class="btn btn-primary btn-sm"><?php _e('Flag post', 'ap'); ?></button>
			  </div>
			</div>
		  </div>
		</div>
		<?php
		
	}else{
		echo '0_'.__('Please try again', 'ap');	
	}
	
	die();
}

// vote for closing, ajax request
add_action( 'wp_ajax_ap_submit_flag_note', 'ap_submit_flag_note' );  
function ap_submit_flag_note(){
	$args = explode('-', sanitize_text_field($_POST['args']));
	$note_id = sanitize_text_field($_POST['note_id']);
	$other_note = sanitize_text_field($_POST['other_note']);
	
	if(wp_verify_nonce( $args[1], 'flag_submit_'.$args[0] )){
		global $wpdb;
		$is_flagged = ap_is_user_flagged($args[0]);
		
		if($is_flagged){
			// if already then return
			echo '0_'. __('You already flagged this post', 'ap');			
		}else{
			if($note_id != 'other')
				$row = $wpdb->insert( $wpdb->prefix.'ap_vote', array('userid'=> get_current_user_id(), 'actionid' =>$args[0], 'type'=> 'flag', 'value' => $note_id), array('%d', '%d', '%s', '%d') );
			else
				$row = $wpdb->insert( $wpdb->prefix.'ap_vote', array('userid'=> get_current_user_id(), 'actionid' =>$args[0], 'type'=> 'flag', 'note' => $other_note), array('%d', '%d', '%s', '%s') );
				
			$counts = ap_post_flag_count($args[0]);
			//update post meta
			update_post_meta($args[0], ANSPRESS_FLAG_META, $counts);
			echo $row.'_flagged_'.__('flag','ap').'('.$counts.')_'. __('You have flagged this post', 'ap');
		}
		
	}else{
		echo '0'.__('Please try again', 'ap');	
	}
	
	die();
}