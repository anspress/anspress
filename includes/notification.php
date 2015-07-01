<?php
/**
 * AnsPress notification functions.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan secondary
 */

class AnsPress_Notifications
{
	var $args = array();
	var $current = -1;
	var $count;
	var $in_the_loop;
	var $notifications;
	var $notification;
	var $total_count;
	var $total_pages;
	var $paged;
	var $per_page = 20;
	var $offset;
	var $cache_key;
	var $result;

	public function __construct($args = '')
	{
		// grab the current page number and set to 1 if no page number is set
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $offset = $this->per_page * ($paged - 1);

		$this->args = wp_parse_args( $args, array(
				'per_page' 	=> $this->per_page,
				'offset' 	=> $offset,
				'paged' 	=> $paged,
				'user_id' 	=> get_current_user_id(),
				'unread' 	=> true,
				'read' 		=> true,
			));

		$this->paged 		= $this->args['paged'];
		$this->offset 		= $this->args['offset'];
		$this->per_page 	= $this->args['per_page'];
		$this->query();
	}

	public function query()
	{
		global $wpdb;

		$read_unread = '';

		if($this->args['unread'] && $this->args['read'])
			$read_unread .= "apmeta_type = 'notification' OR apmeta_type = 'unread_notification'";			

		elseif($this->args['read'])
			$read_unread .= "apmeta_type = 'notification'";

		else
			$read_unread .= "apmeta_type = 'unread_notification'";

		$query = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS apmeta_id as id, apmeta_userid as user_id, apmeta_actionid as affected_user_id, apmeta_param as type, apmeta_value as args, apmeta_type as is_unread, apmeta_date as date FROM ".$wpdb->prefix."ap_meta WHERE (".$read_unread.") AND apmeta_actionid = %d ORDER BY CASE apmeta_type WHEN 'unread_notification' THEN 1 ELSE -1 END DESC, apmeta_date DESC LIMIT %d,%d", $this->args['user_id'], $this->offset, $this->per_page);

		$this->cache_key 		= md5($query);
		$result 				= wp_cache_get( $this->cache_key, 'ap');
		$this->total_count 		= wp_cache_get( $this->cache_key.'_count', 'ap');

		if($result === false){
			$result = $wpdb->get_results($query);
			wp_cache_set( $this->cache_key, $result, 'ap' );

			$this->total_count = $wpdb->get_var( apply_filters( 'ap_notification_found_rows', "SELECT FOUND_ROWS()", $this ) );		
			wp_cache_set( $this->cache_key.'_count', $this->total_count, 'ap' );
		}

		$this->notifications 	= $result;

		$this->total_pages 		= ceil($this->total_count / $this->per_page);
		$this->count 			= count($result);
	}

	private function set_args(){
		if($this->notification && !is_array($this->notification->args)){	
			$this->notification->args 				= wp_parse_args( urldecode($this->notification->args) );
			$this->notification->is_unread 			= $this->notification->is_unread == 'unread_notification' ? true : false;
			$this->notification->content 			= ap_sprintf_assoc(ap_get_notification_title($this->notification->type, $this->notification->args), $this->notification->args );
			$this->notification->icon 				= ap_get_notification_icon($this->notification->type);
			
			if(isset($this->notification->args['permalink']))
				$this->notification->args['permalink'] 	= add_query_arg(array('ap_notification_read' => $this->id() ), $this->notification->args['permalink']);
		}
	}

	public function notifications()
    {
        if ( $this->current + 1 < $this->count ) {
            return true;
        } 
        elseif ( $this->current + 1 == $this->count ) {

            do_action('ap_notifications_loop_end');
            
            // Do some cleaning up after the loop
            $this->rewind_notification();
        }

        $this->in_the_loop = false;
        return false;
    }

    /**
     * Rewind the notification and reset index.
     */
    public function rewind_notification() {
        $this->current = -1;
        if ( $this->count > 0 ) {
            $this->notification = $this->notifications[0];
        }
    }

    /**
     * Check if there are notifications in loop
     *
     * @return bool
     */
    public  function has_notifications() {
        if ( $this->count )
            return true;

        return false;
    }

    /**
     * Set up the next notification and iterate index.
     *
     * @return object The next notification to iterate over.
     */
    public function next_notification() { 

        $this->current++;
        $this->notification = $this->notifications[$this->current];

        $this->set_args();
        return $this->notification;
    }

    /**
     * Set up the current notification inside the loop.
     */
    public function the_notification() {

    	global $ap_notification;
        $this->in_the_loop 		= true;
        $this->notification     = $this->next_notification();
        $ap_notification 		= $this->notification;

        // loop has just started
        if ( 0 == $this->current ) {

            /**
             * Fires if the current notification is the first in the loop.
             */
            do_action( 'ap_notification_loop_start' );
        }

    }

    public function id()
    {
    	if($this->in_the_loop)
    		return $this->notification->id;
    }

    public function is_unread()
    {
    	if($this->in_the_loop)
    		return $this->notification->is_unread;
    }

    public function permalink()
    {
    	if($this->in_the_loop)
    		return $this->notification->args['permalink'];
    }

    public function the_pagination()
    {
        $base = ap_user_link( $this->args['user_id'], 'notification' ) . '/%_%';
        ap_pagination($this->paged, $this->total_pages, $base);
    }
}

function ap_get_user_notifications($args = ''){
	return new AnsPress_Notifications($args);
}

function ap_has_notifications(){
	global $ap_notifications;

	return $ap_notifications->has_notifications();
}

function ap_notifications(){
	global $ap_notifications;
	return $ap_notifications->notifications();
}

function ap_the_notification(){
	global $ap_notifications;
	return $ap_notifications->the_notification();
}

function ap_notification_object(){
	global $ap_notification;

	if($ap_notification)
		return $ap_notification;
}

function ap_notification_the_id(){
	echo ap_notification_id();
}

	function ap_notification_id(){
		$notification = ap_notification_object();

		if($notification)
			return $notification->id;
	}

function ap_notification_is_unread(){
	$notification = ap_notification_object();

	if($notification)
		return $notification->is_unread;
}

function ap_notification_the_permalink(){
	echo ap_notification_permalink();
}

	function ap_notification_permalink(){
		$notification = ap_notification_object();

		if($notification)
			return $notification->args['permalink'];
	}

function ap_notification_the_type(){
	echo ap_notification_type();
}

	function ap_notification_type(){
		$notification = ap_notification_object();

		if($notification)
			return $notification->type;
	}

function ap_notification_the_icon(){
	echo ap_notification_icon();
}
	function ap_notification_icon(){
		$notification = ap_notification_object();

		if($notification)
			return $notification->icon;
	}

function ap_notification_the_content(){
	echo ap_notification_content();
}
	function ap_notification_content(){
		$notification = ap_notification_object();

		if($notification)
			return $notification->content;
	}

function ap_notification_the_date(){
	printf(__('%s ago', 'ap'), ap_human_time(ap_notification_date(), false));
}
	function ap_notification_date(){
		$notification = ap_notification_object();

		if($notification)
			return $notification->date;
	}

function ap_notification_pagination(){
	global $ap_notifications;

	$ap_notifications->the_pagination();
}

/**
 * Insert notification in ap_meta table
 *  
 * @param  integer  		$current_user_id   	User id of user triggering this hook
 * @param  integer  		$affected_user_id  	User id of user who is being affected
 * @param  string 			$notification_type 	Type of notification
 * @param  boolean|array 	$args              	arguments for the notification
 * @return integer|boolean
 */
function ap_insert_notification( $current_user_id, $affected_user_id, $notification_type, $args = false){

	//if effected user id and current user id are same or is anonymous then return
	if( $affected_user_id <= 0 || $affected_user_id == $current_user_id)
		return;

	//Convert array to query string
	if($args === false)
		$args = array();

	switch ($notification_type) {
		case 'new_answer':
		case 'question_update':
		case 'answer_update':
			$post 					= get_post($args['post_id']);			
			$args['permalink'] 		= get_permalink( $post->ID );
			$args['post_title'] 	= ap_truncate_chars($post->post_title, 50);

			break;

		case 'comment_on_question':
		case 'comment_on_answer':
			$post 					= get_post($args['post_id']);
			$args['permalink'] 		= get_comment_link( $args['comment_id'] );
			$args['post_title'] 	= ap_truncate_chars($post->post_title, 50);
			break;

		case 'vote_up':
			$post 					= get_post($args['post_id']);
			$args['user_id'] 		= $current_user_id;
			$args['permalink'] 		= get_permalink( $post->ID );
			$args['post_title'] 	= ap_truncate_chars($post->post_title, 50);
			break;
		
		case 'new_follower':
			$args['permalink'] 		= ap_user_link($current_user_id);
			$args['user_id'] 		= $current_user_id;
			break;

		case 'answer_selected':
			$post 					= get_post($args['post_id']);
			$args['permalink'] 		= get_permalink( $post->ID );
			$args['post_title'] 	= ap_truncate_chars($post->post_title, 50);	
			break;

		case 'received_reputation':
			$args['reputation'] 	= $args['reputation'];
			$args['permalink'] 		= ap_user_link($affected_user_id, 'reputation');
			break;
		
		default:
			$args = apply_filters( 'ap_notification_args', $args, func_get_args() );
			break;
	}

	$args = urlencode(strip_tags(build_query( $args )));

	$row = ap_add_meta($current_user_id, 'unread_notification', $affected_user_id, $args, $notification_type);

	if($row !== false)
		do_action('ap_insert_notification', $current_user_id, $affected_user_id, $notification_type, $args);

	return $row;
}

/**
 * Return notification title
 * @param  string	$notification 	notification type
 * @return string
 * @since  2.3
 */
function ap_get_notification_title($notification, $args){

	$title = array(
		'new_question' 				=> __('New question <b>##post_title</b>', 'ap'),
		'new_answer' 				=> __('New answer on <b>##post_title</b>', 'ap'),
		'question_update' 			=> __('Your question <b>##post_title</b> has been edited', 'ap'),
		'answer_update' 			=> __('Your answer on <b>##post_title</b> has been edited', 'ap'),
		'comment_on_question' 		=> __('New comment on question <b>##post_title</b>', 'ap'),
		'comment_on_answer' 		=> __('New comment on answer <b>##post_title</b>', 'ap'),
		'new_follower' 				=> sprintf(__('<b>%s</b> started following you', 'ap'), ap_user_display_name($args['user_id'])),
		'vote_up' 					=> sprintf(__('%s up voted on your post <b>##post_title</b>', 'ap'), ap_user_display_name($args['user_id'])),
		'answer_selected' 			=> __('Your answer on <b>##post_title</b> has been selected as best', 'ap'),
		'received_reputation' 		=> __('You have received <b>##reputation</b> reputation points', 'ap'),
	);

	$title = apply_filters( 'ap_notification_title', $title );
	
	if(isset($title[$notification]))
		return $title[$notification];
}

function ap_get_notification_icon($type){
	
	$icons = array(
		'new_question' 				=> ap_icon('question', true),
		'new_answer' 				=> ap_icon('answer', true),
		'question_update' 			=> ap_icon('pencil', true),
		'answer_update' 			=> ap_icon('pencil', true),
		'comment_on_question' 		=> ap_icon('comment', true),
		'comment_on_answer' 		=> ap_icon('comment', true),
		'new_follower' 				=> ap_icon('user-check', true),
		'vote_up' 					=> ap_icon('thumb-up', true),
		'answer_selected' 			=> ap_icon('check', true),
		'received_reputation' 		=> ap_icon('reputation', true),
	);

	$icons = apply_filters( 'ap_get_notification_icon', $icons );

	if(isset($icons[$type]))
		return $icons[$type];
}

function ap_get_the_total_unread_notification($user_id = false, $echo = false){
	$count = ap_get_total_unread_notification();

	$count = $count >= 10 ? __('9+', 'ap') : $count;
	$count = $count != 0 ? '<span class="counter" data-view="notification_count">'.$count.'</span>' : '';

	if($echo)
		echo $count;

	return $count;
}
	/**
	 * Count total numbers of unread notification
	 * @param  boolean|integer 		$user_id 
	 * @return integer
	 * @since  2.3
	 */
	function ap_get_total_unread_notification($user_id = false){
		
		if($user_id === false)
			$user_id = get_current_user_id();

		return ap_meta_user_done('unread_notification', false, $user_id);
	}


function ap_delete_notification($meta_id = false, $current_user_id = false, $affected_user_id = false, $type = false){
	global $wpdb;

	if($meta_id !== false){
		$row = ap_delete_meta(false, $meta_id);		
	}else{
		$row = $wpdb->query( 
			$wpdb->prepare( 
				"DELETE FROM ".$wpdb->prefix."ap_meta
				 WHERE apmeta_actionid = %d
				 AND apmeta_userid = %d
				 AND apmeta_param = %s
				 AND (apmeta_type = 'notification' OR apmeta_type = 'unread_notification')
				 ",
		        $affected_user_id, $current_user_id, $type
	        )
		);
	}

	if(FALSE !== $row)
		do_action('ap_delete_notification', $current_user_id, $affected_user_id, $type);

	return $row;
}

function ap_get_notification_by_id($id){
	return ap_get_meta(array('apmeta_id' => (int) $id));
}

/**
 * Set all unread notifications as read
 * @param  integer $user_id 
 * @return integer
 */
function ap_notification_mark_all_read($user_id){
	return ap_update_meta(array('apmeta_type' => 'notification'), array('apmeta_type' => 'unread_notification','apmeta_actionid' => (int)$user_id));
}

/**
 * Mark a notification as read
 * @param  integer 				$id 		notification id
 * @return integer|boolean		Return FALSE on failure
 */
function ap_notification_mark_as_read($id){
	$row = ap_update_meta(array('apmeta_type' => 'notification'), array('apmeta_id' => (int)$id));
	
	if($row !== false)
		do_action('ap_notification_marked_as_read', $id);

	return $row;
}