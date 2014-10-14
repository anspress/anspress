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

class AP_Messages
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
		add_filter('ap_user_page_menu', array($this, 'ap_user_page_menu'), 10, 2);
		add_action('wp_ajax_ap_send_message', array($this, 'ap_send_message'));
		add_action('wp_ajax_ap_new_conversation', array($this, 'ap_new_conversation'));
		add_action('wp_ajax_ap_show_conversation', array($this, 'ap_show_conversation'));
		add_action('wp_ajax_ap_new_message_form', array($this, 'ap_new_message_form'));
		add_action('wp_ajax_ap_search_users', array($this, 'ap_search_users'));
		add_action('wp_ajax_ap_load_conversations', array($this, 'ap_load_conversations'));
		add_action('wp_ajax_ap_message_search', array($this, 'ap_message_search'));
		add_action('wp_ajax_ap_message_edit_form', array($this, 'ap_message_edit_form'));
		add_action('wp_ajax_ap_edit_message', array($this, 'ap_edit_message'));
		add_action('wp_ajax_ap_delete_message', array($this, 'ap_delete_message'));
    }
	
	public function ap_user_page_menu($links, $userid){
		if(is_my_profile() && ap_current_user_page_is('messages'))
			$links['new_message'] = array( 'name' => __('New message', 'ap'), 'link' => '#', 'icon' => 'ap-icon-mail', 'attributes' => 'data-button="ap-new-message"');
		return $links;
	}
	
	public function ap_send_message(){
		if(wp_verify_nonce($_POST['_nonce'], 'new_message')){
			$id = ap_new_message($_POST['conversation'], $_POST['message-content']);
			if($id === false)		
				$result = array(
					'status' 	=> false,
					'message' 	=> __('Something went wrong, message not sent.', 'ap')
				);
			else
				$result = array(
					'status' 	=> true,
					'message' 	=> __('Message sent successfully.', 'ap'),
					'html' 		=> ap_get_message_html(ap_get_message_by_id($id), true)
				);
		}else{
			$result = array(
				'status' 	=> false,
				'message' 	=> __('Something went wrong, message not sent.', 'ap')
			);
		}
		die(json_encode($result));
	}
	public function ap_new_conversation(){
		if(wp_verify_nonce($_POST['_nonce'], 'new_message') && ap_user_can_message()){
			
			$validation =  array();
			
			if(strlen($_POST['recipient']) == 0){
				$validation['recipient'] 	= __('You must add at least once recipient', 'ap');
				$validation['error'] 		= true;
			}
			
			if(strlen($_POST['message-content']) == 0 || $_POST['message-content'] == ''){
				$validation['message-content'] 	= __('Message cannot be left blank.', 'ap');
				$validation['error'] 		= true;
			}
			
			if(isset($validation['error'])){
				$result = array(
					'status' 	=> 'validation_falied',
					'message' 	=> __('Some fields empty, please check message form.', 'ap'),
					'error' => $validation
				);
				
			}else{
				$id = ap_new_message(false, $_POST['message-content'], $_POST['recipient']);
				if($id === false)		
					$result = array(
						'status' 	=> false,
						'message' 	=> __('Something went wrong, message not sent.', 'ap')
					);
				else{		
					$message = ap_get_message_by_id($id);
					
					ob_start();
					ap_get_conversation_list($message->message_conversation);
					$html = ob_get_clean();
					
					$result = array(
						'status' 	=> true,
						'message' 	=> __('Message sent successfully.', 'ap'),
						'html' 		=> $html
					);
				}
			}
		}else{
			$result = array(
				'status' 	=> false,
				'message' 	=> __('Something went wrong, message not sent.', 'ap')
			);
		}
		die(json_encode($result));
	}
	
	public function ap_show_conversation(){
		$args = explode('-', sanitize_text_field($_POST['args']));
		if(wp_verify_nonce($args[0], 'show-conversation')){
			$id = sanitize_text_field($args[1]);
			ob_start();
			ap_get_conversation_list($id);
			$html = ob_get_clean();
			
			$result = array(
				'status' 	=> true,
				'html' 		=> $html,
				'message'	=> __('Conversation loaded', 'ap'),
			);
		}
		else{
			$result = array(
				'status' 	=> false,
				'message'	=> __('Failed to load conversation', 'ap'),
			);
		}
		die(json_encode($result));
	}
	
	public function ap_new_message_form(){
		if(ap_user_can_message()){
			$result = array( 
			'status' => true,
			'html' => ap_new_message_form()
			);
		}else{
			$result = array(
				'status' 	=> false,
				'message' 	=> __('Something went wrong, message not sent.', 'ap')
			);
		}
		die(json_encode($result));
	}
	
	public function ap_search_users(){
		$search_string = sanitize_text_field($_POST['q']);
		$users = new WP_User_Query(array(
			'search'         => "*{$search_string}*",
			'search_columns' => array(
				'user_login',
				'user_nicename',
				'user_email',
				'user_email',
			),
			'exclude' => array(get_current_user_id())
		));
		$users_found = $users->get_results();
		$users =  array();
		if($users_found){
			foreach ($users_found as $user)
				$users[] = array('name' =>$user->display_name, 'id' => $user->ID, 'avatar' => get_avatar( $user->ID, 20 ));
		}
		$result = array('status' => true, 'items' => $users);
		
		die(json_encode($result)); 
	}
	
	public function ap_load_conversations(){
		if(wp_verify_nonce($_POST['args'], 'conversations_list')){
			$offset = sanitize_text_field($_POST['offset']);
			ob_start();
			ap_conversations_list(get_current_user_id(), $offset);
			$html = ob_get_clean();
			
			$result = array(
				'status' 	=> true,
				'message' 	=> __('More conversations loaded', 'ap'),
				'html' 		=> $html,
			);
		}else
			$result = array(
				'status' 	=> false,
				'message' 	=> __('Failed to load conversations.', 'ap')
			);
		die(json_encode($result));
	}
	
	public function ap_message_search(){
		if(wp_verify_nonce($_GET['_nonce'], 'search_message')){
			ob_start();
			ap_conversations_list(get_current_user_id(), 0, 50, true);
			$html = ob_get_clean();
			$result = array(
				'status' 	=> true,
				'html' 		=> $html,
			);
		}else{
			$result = array(
				'status' 	=> false,
				'message' 		=> __('Something went wrong! cannot search for conversations.', 'ap'),
			);
		}
		die(json_encode($result));
	}
	
	public function ap_message_edit_form(){
		$args = explode('-', sanitize_text_field( $_POST['args'] ));
		if(wp_verify_nonce($args[1], 'edit_message_form')){
			$message = ap_get_message_by_id($args[0]);
			
			if($message && $message->message_sender == get_current_user_id()){
				$result = array(
					'status' 	=> true,
					'message' 		=> __('Message edit form loaded.', 'ap'),
					'html' => '<form id="ap-edit-message" method="post" data-action="ap-edit-message">
							<div class="form-group">
								<textarea class="form-control autogrow" id="message-content" name="message-content" placeholder="'.__('Type your message', 'ap').'">'.stripslashes_deep($message->message_content).'</textarea>
							</div>
							<button type="submit" class="ap-btn">'.__('Send', 'ap').'</button>
							<input type="hidden" name="action" value="ap_edit_message" />
							<input type="hidden" name="id" value="'.$message->message_id.'" />
							<input type="hidden" name="_nonce" value="'.wp_create_nonce('edit_message').'" />
						</form>'
					);
			}else{
				$result = array(
					'status' 	=> false,
					'message' 	=> __('You do not have permission to edit.', 'ap'),
				);
			}
		}else{
			$result = array(
				'status' 	=> false,
				'message' 		=> __('Something went wrong! load form.', 'ap'),
			);
		}
		die(json_encode($result));
	}
	
	public function ap_edit_message(){
		if(wp_verify_nonce($_POST['_nonce'], 'edit_message')){
			$message = ap_get_message_by_id(sanitize_text_field($_POST['id']));
			
			if($message && $message->message_sender == get_current_user_id()){
				$content = wp_kses(esc_html($_POST['message-content']), array(
					'a' => array(
						'href' => array(),
						'title' => array()
					),
					'p' => array(),
					'br' => array(),
					'em' => array(),
					'pre' => array(),
				));
				$update = ap_update_message_content($message->message_id, $content);
				if($update){
					$message = ap_get_message_by_id($message->message_id);
					$result = array(
						'status' 	=> true,
						'message' 		=> __('Message updated successfully.', 'ap'),
						'html' => apply_filters('the_content', stripslashes_deep($message->message_content))
						);
				}else
					$result = array(
						'status' 	=> false,
						'message' 		=> __('Message not updated', 'ap'),
						);
			}else{
				$result = array(
					'status' 	=> false,
					'message' 	=> __('You do not have permission to edit.', 'ap'),
				);
			}
		}else{
			$result = array(
				'status' 	=> false,
				'message' 		=> __('Something went wrong! load form.', 'ap'),
			);
		}
		die(json_encode($result));
	}
	
	public function ap_delete_message(){
		$args = explode('-', sanitize_text_field( $_POST['args'] ));
		if(wp_verify_nonce($args[1], 'delete_message')){
			$message = ap_get_message_by_id($args[0]);
			
			if($message && $message->message_sender == get_current_user_id()){
				ap_delete_message($message->message_id);
				$result = array(
					'status' 	=> true,
					'message' 		=> __('Message delete successfully.', 'ap')
					);
			}else{
				$result = array(
					'status' 	=> false,
					'message' 	=> __('You do not have permission to delete.', 'ap'),
				);
			}
		}else{
			$result = array(
				'status' 	=> false,
				'message' 		=> __('Something went wrong! cannot delete message.', 'ap'),
			);
		}
		die(json_encode($result));
	}

}

function ap_new_conversation_id( $sender){
	return ap_add_meta($sender, 'conversation');
}

function ap_add_recipients($recipient, $sender, $conversation_id){
	$recipient = sanitize_comma_delimited($recipient);
	$sender = filter_var($sender, FILTER_SANITIZE_NUMBER_INT);
	$users = array_unique(explode(',', str_replace(' ', '', $recipient.','.$sender)));

	if(!empty($users))
		foreach ($users as $user)
			ap_add_meta($user, 'recipient', $conversation_id);
}

function ap_get_recipient($conversation_id){
	global $wpdb;
	$result = $wpdb->get_results($wpdb->prepare('SELECT apmeta_userid as user FROM '.$wpdb->prefix.'ap_meta WHERE apmeta_type = "recipient" AND apmeta_actionid = %d', $conversation_id), ARRAY_A);
	
	if ($result){
		$ids = array();
		foreach($result as $r)
			$ids[] = $r['user'];
		
		return $ids;
	}

	return false;	
}

function ap_get_conversation_id($recipient, $sender){
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare('SELECT apmeta_id FROM '.$wpdb->prefix.'ap_meta WHERE (FIND_IN_SET(%d ,apmeta_value) OR FIND_IN_SET(%d, apmeta_value)) AND apmeta_type = "conversation"', $recipient, $sender));
}

function ap_new_message_form(){

	return '<form id="ap-new-message" method="post" action="" data-action="ap-send-message">
		<div class="form-group">
			<input type="text" id="recipient" class="form-control" placeholder="'.__('Type the name of user', 'ap').'" name="recipient" data-action="ap-suggest-user" />
		</div>
		<div class="form-group">
			<textarea class="form-control autogrow" id="message-content" name="message-content" placeholder="'.__('Type your message', 'ap').'"></textarea>
		</div>
		<button type="submit" class="ap-btn">'.__('Send', 'ap').'</button>
		<input type="hidden" name="action" value="ap_new_conversation" />
		<input type="hidden" name="_nonce" value="'.wp_create_nonce('new_message').'" />
	</form>';

}

function ap_new_message($conversation = false, $content, $recipients = false, $sender =false, $date = false){
	if(!ap_user_can_message())
		return false;
		
	/* get current user id if not set */
	if(!$sender)
		$sender = get_current_user_id();
	
	if(!$conversation){
		$conversation = ap_new_conversation_id($sender);
		ap_add_recipients($recipients, $sender, $conversation);
	}
	
	$recipients = ap_get_recipient($conversation);
	/* check if user is already in conversation */
	if(!in_array ($sender, $recipients))
		return false;
	
	/* get current time in mysql format if not set */
	if(!$date)
		$date = current_time( 'mysql' );
	
	$content = wp_kses(esc_html($content), array(
		'a' => array(
			'href' => array(),
			'title' => array()
		),
		'p' => array(),
		'br' => array(),
		'em' => array(),
		'pre' => array(),
	));
	
	global $wpdb;
	$row = $wpdb->insert( 
		$wpdb->prefix . 'ap_messages', 
		array( 
			'message_conversation' 	=>sanitize_text_field($conversation), 
			'message_content' 	=> $content, 
			'message_sender' 	=> sanitize_text_field($sender),
			'message_date' 		=> $date,
			'message_read' 		=> 0
		), 
		array( 
			'%d', 
			'%s', 
			'%d', 
			'%s', 
			'%d',
		) 
	);
	
	if($row === false)
		return false;
		
	return  $wpdb->insert_id;
}

function ap_get_message_by_id($id){		
	global $wpdb;	
	$query = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix . 'ap_messages WHERE message_id = %d', $id);
	
	return $wpdb->get_row($query);
}

function ap_update_message_content($id, $content){
	if(!ap_user_can_message())
		return false;
		
	global $wpdb;	
	return $wpdb->update(
		$wpdb->prefix . 'ap_messages',		
		array('message_content' => $content),
		array('message_id' => $id), 
		array('%s'), array('%d')
		);
}

function ap_delete_message($id){
	global $wpdb;	
	return $wpdb->delete(
		$wpdb->prefix . 'ap_messages',		
		array('message_id' => $id), 
		array('%d')
		);
}

function ap_get_conversation($id){		
	global $wpdb;	
	
	$query = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix . 'ap_messages WHERE message_conversation = %d', $id);
	
	return $wpdb->get_results($query);
}

function ap_get_conversations($user, $offset= 0, $limit = 10){
	if(!$user)
		$user = get_current_user_id();
	global $wpdb;	
	
	$offset = $offset* $limit;
	
	$query = $wpdb->prepare('SELECT *, (SELECT GROUP_CONCAT(apmeta_userid) FROM '.$wpdb->prefix . 'ap_meta WHERE  apmeta_actionid = m.message_conversation AND apmeta_type = "recipient") as users FROM '.$wpdb->prefix . 'ap_messages m JOIN  '.$wpdb->prefix . 'ap_meta meta ON meta.apmeta_userid = %d AND meta.apmeta_actionid = m.message_conversation AND meta.apmeta_type= "recipient" JOIN (SELECT MAX(message_id) as message_id FROM '.$wpdb->prefix . 'ap_messages GROUP BY message_conversation) m2 WHERE m2.message_id = m.message_id GROUP BY m.message_conversation ORDER BY m.message_date DESC LIMIT %d, %d', $user, $offset, $limit);
	
	return $wpdb->get_results($query);
}

function ap_search_conversations($user, $search, $limit = 10){
	if(!$user)
		$user = get_current_user_id();

	global $wpdb;
	
	$query = $wpdb->prepare('SELECT *, (SELECT GROUP_CONCAT(apmeta_userid) FROM '.$wpdb->prefix . 'ap_meta WHERE  apmeta_actionid = m.message_conversation AND apmeta_type = "recipient") as users FROM '.$wpdb->prefix . 'ap_messages m JOIN  '.$wpdb->prefix . 'ap_meta meta ON meta.apmeta_userid = %d AND meta.apmeta_actionid = m.message_conversation AND meta.apmeta_type= "recipient" JOIN (SELECT MAX(message_id) as message_id FROM '.$wpdb->prefix . 'ap_messages GROUP BY message_conversation) m2 WHERE m2.message_id = m.message_id AND m.message_content LIKE "%%%s%%" GROUP BY m.message_conversation ORDER BY m.message_date DESC LIMIT %d', $user, $wpdb->esc_like($search), $limit);
		
	return $wpdb->get_results($query);
}

function ap_total_conversations($recipient_id){
	if(!$recipient_id)
		$recipient_id = ap_get_user_page_user();
		
	global $wpdb;	
	
	$query = $wpdb->prepare('SELECT count(*) FROM '.$wpdb->prefix . 'ap_meta WHERE apmeta_type= "conversation" AND FIND_IN_SET(%d, apmeta_value)', $recipient_id);
	
	return $wpdb->get_var($query);
}

function ap_get_conversation_users_name($ids){
	$current_user = get_current_user_id();
	$ids = explode(',', str_replace(' ', '', $ids));
	$ids = array_diff($ids, array($current_user));
	$o = '';
	if(!empty($ids) && is_array($ids)){
		$names = array();
		$more = array();
		foreach($ids as $k => $id){
			if ($k < 3)
				$names[] = ap_user_display_name($id, true);
			else
				$more[] = ap_user_display_name($id, true);
		}
		$o .= implode(', ', $names);
		
		if(!empty($more))
			$o .= ', <span class="ap-more ap-tip" title="'.implode('<br /> ', $more).'">'.__('more', 'ap').'</span>';
	}else{
		$o .= ap_user_display_name($ids);
	}	
	
	return $o;
}

function ap_get_conversation_users_avatar($ids, $size = 30){
	$current_user = get_current_user_id();
	$ids = explode(',', str_replace(' ', '', $ids));
	$ids = array_diff($ids, array($current_user));
	$o = '';
	
	if(count($ids) > 1){
		$o .= '<span class="ap-icon-users ap-group-icon" style="height:'.$size.'px; width:'.$size.'px"></span>';
	}else{
		foreach($ids as $id);
			$o .= get_avatar($id, $size);	
	}
	return $o;
}


function ap_conversations_list($recipient_id = false, $offset= 0, $limit = 10, $search = false){	
		
	if(!$recipient_id)
		$recipient_id = get_current_user_id();

	if($search){
		$search_string 	= sanitize_text_field($_GET['s']);
		$conversation 	= ap_search_conversations($recipient_id, $search_string, $limit);
	}else{
		$conversation 	= ap_get_conversations($recipient_id, $offset, $limit);
	}

	if($conversation){
		$count = count($conversation);
		$i = 1;
		echo '<ul class="ap-conversations ap-nav">';
		foreach($conversation as $message) :

		?>	
			<li class="<?php echo $i == 1 ? 'active ' : ''; ?>clearfix">
				<a href="<?php echo ap_user_link(get_current_user_id(), array('user_page' => 'message', 'message_id' => $message->message_conversation)); ?>">
					<time><?php printf( __( '%s ago', 'ap' ), ap_human_time( $message->message_date, false)); ?></time>				
					<div class="ap-avatar"><?php echo ap_get_conversation_users_avatar($message->users) ?></div>
					<div class="ap-message-summery">
						<strong><?php echo ap_get_conversation_users_name($message->users); ?></strong>
						<span>
							<span class="ap-last-action <?php echo $message->message_sender == get_current_user_id() ?'ap-icon-reply' : 'ap-icon-forward'; ?>"></span>
							<?php echo ap_truncate_chars(strip_tags(stripcslashes($message->message_content)), 40); ?></span>
					</div>
				</a>
			</li>			
		<?php
		$i++;
		endforeach;
		if ($count < $limit):
			echo '<li class="ap-no-more-message">'.__('There is no more messages to load', 'ap').'</li>';
		endif;
		echo '</ul>';
	}
	
	ap_total_conversations($recipient_id);
}

function ap_message_actions_buttons($message){
	$actions = array(
		'edit' => array('label' => __('Edit message', 'ap'), 'attributes' => ' href="#" data-button="ap-edit-message" data-args="'.$message->message_id.'-'.wp_create_nonce('edit_message_form').'"'),
		'delete' => array('label' => __('Delete message', 'ap'), 'attributes' => ' href="#" data-button="ap-delete-message" data-args="'.$message->message_id.'-'.wp_create_nonce('delete_message').'"'),
	);
	
	$actions = apply_filters('ap_message_actions_buttons', $actions);
	
	$o = '';
	foreach($actions as $k => $a){
		$o .= '<li><a '.$a['attributes'].'>'.$a['label'].'</a></li>';
	}
	
	return $o;
}

function ap_get_message_html($message, $return = true){
	$link = ap_user_link($message->message_sender);
	$o = '<li class="ap-message clearfix">
			<div class="ap-avatar">'.get_avatar($message->message_sender, 30).'</div>
			<div class="no-overflow">
			<div class="ap-message-head clearfix">
				<strong><a href="'.$link.'">'.ap_user_display_name($message->message_sender, true).'</a></strong>
				';	
				if($message->message_sender == get_current_user_id()){				
				$o .='<div class="ap-message-btns ap-dropdown">
					<a href="#" class="btn ap-btn ap-dropdown-toggle ap-icon-arrow-down"></a>
					<ul class="ap-dropdown-menu">
						'.ap_message_actions_buttons($message).'
					</ul>
				</div>';
				}
				$o .='<time>'.sprintf( __( '%s ago', 'ap' ), ap_human_time( $message->message_date, false)).'</time>
			</div>
			<div class="ap-message no-overflow" data-view="ap-message-content">					
				'. apply_filters('the_content', stripslashes_deep($message->message_content)).'
			</div>
			</div>
		</li>';
			
	$o = apply_filters('ap_get_message_html', $o);
		
	if($return)
		return $o;
	
	echo $o;
}

function ap_get_conversation_list($id){
	$recipients = ap_get_recipient($id);
	/* check if user is already in conversation */
	if(!is_user_logged_in() && !in_array (get_current_user_id(), $recipients))
		return false;

	$messages = ap_get_conversation($id);
	
	if($messages){
		echo '<div class="ap-conversation-users clearfix">';
			echo '<strong>'.__('Participants', 'ap').'</strong>';
			$users = ap_get_recipient($id);
			foreach($users as $u){
				echo '<a class="ap-user" href="'.ap_user_link($u).'">';
				echo get_avatar($u, 18);
				if($u == get_current_user_id())
					echo __('You', 'ap');
				else
					echo ap_user_display_name($u, true);
				echo '</a>';
			}
		echo '</div>';
		echo '<ul class="ap-message-log ap-nav">';			
			foreach($messages as $m){
				echo ap_get_message_html($m);
			}
		echo '</ul>';
	}
	if(ap_user_can_message()){
		?>
		<form id="ap-send-message" method="post" action="" data-action="ap-send-message">
			<textarea class="form-control autogrow" name="message-content" placeholder="<?php _e('Type your message', 'ap'); ?>"></textarea>
			<button type="submit" class="ap-btn"><?php _e('Send', 'ap'); ?></button>
			<input type="hidden" name="action" value="ap_send_message" />
			<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('new_message'); ?>" />
			<input type="hidden" name="conversation" value="<?php echo $id; ?>" />
		</form>
		<?php
	}
}

function ap_message_btn_html($userid, $display_name){
	echo '<a class="btn ap-btn ap-follow-btn ap-icon-paperplane" href="'.ap_user_link(get_current_user_id(), array('user_page' => 'messages')).'/?to='.$userid.'&dname='.$display_name.'">'.__('Message', 'ap').'</a>';
}