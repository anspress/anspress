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
		add_action('wp_ajax_ap_show_conversation', array($this, 'ap_show_conversation'));
		add_action('wp_ajax_ap_new_message_form', array($this, 'ap_new_message_form'));
		add_action('wp_ajax_ap_search_users', array($this, 'ap_search_users'));
    }
	
	public function ap_user_page_menu($links, $userid){
		if(is_my_profile() && ap_current_user_page_is('messages'))
			$links['new_message'] = array( 'name' => __('New message', 'ap'), 'link' => '#', 'icon' => 'ap-icon-mail', 'attributes' => 'data-button="ap-new-message"');
		return $links;
	}
	
	public function ap_send_message(){
		
		ap_new_message($_POST['conversation'], $_POST['message-content']);
		die();
	}
	
	public function ap_show_conversation(){
		$id = sanitize_text_field($_POST['id']);
		ob_start();
		ap_get_conversation_list($id);
		$html = ob_get_clean();
		
		$result = array(
			'status' 	=> true,
			'html' 		=> $html,
			'message'	=> __('Conversation loaded', 'ap'),
		);
		die(json_encode($result));
	}
	
	public function ap_new_message_form(){
		$result = array( 
		'status' => true,
		'html' => '<form id="ap-new-message" method="post" action="" data-action="ap-new-conversation">
			<input type="text" class="form-control" placeholder="'.__('Type the name of user', 'ap').'" data-action="ap-suggest-user" />
			<textarea class="form-control" name="message-content" placeholder="'.__('Type your message', 'ap').'"></textarea>
			<button type="submit" class="ap-btn">'.__('Send', 'ap').'</button>
			<input type="hidden" name="action" value="ap_new_conversation" />
		</form>'
		);
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
			)
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

}

function ap_new_conversation_id($recipient, $sender){
	return ap_add_meta(NULL, 'conversation', NULL, $recipient.','.$sender);
}

function ap_get_conversation_id($recipient, $sender){
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare('SELECT apmeta_id FROM '.$wpdb->prefix.'ap_meta WHERE (%d IN (apmeta_value) OR %d IN (apmeta_value)) AND apmeta_type = "conversation"', $recipient, $sender));
}

function ap_get_recipient($conversation_id, $sender_id){

	$conver = ap_get_meta(array('apmeta_value' => $conversation_id));
	print_r($conver);
	$conver = explode($conver);
	
	return array_diff($conver, array($sender_id));
}

function ap_new_message($conversation = false, $content, $recipient = false, $sender =false, $date = false){
		
	/* get current user id if not set */
	if(!$sender)
		$sender = get_current_user_id();
	
	if(!$conversation)
		$conversation = ap_new_conversation_id($recipient, $sender);
	
	if(!$recipient)
		$recipient = ap_get_recipient($conversation, $sender);
	
	var_dump($recipient);
		
	/* get current time in mysql format if not set */
	if(!$date)
		$date = current_time( 'mysql' );
		
	global $wpdb;
	$row = $wpdb->insert( 
		$wpdb->prefix . 'ap_messages', 
		array( 
			'message_conversation' 	=>sanitize_text_field($conversation), 
			'message_content' 	=> sanitize_text_field($content), 
			'message_sender' 	=> sanitize_text_field($sender),
			'message_recipient' => sanitize_text_field($recipient),
			'message_date' 		=> $date,
			'message_read' 		=> 0
		), 
		array( 
			'%d', 
			'%s', 
			'%d', 
			'%d', 
			'%s', 
			'%d',
		) 
	);
	
	return $row;
}

function ap_get_conversation($id){		
	global $wpdb;	
	
	$query = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix . 'ap_messages WHERE message_conversation = %d', $id);
	
	return $wpdb->get_results($query);
}

function ap_get_conversations($recipient_id){
	if(!$recipient_id)
		$recipient_id = ap_get_user_page_user();
		
	global $wpdb;	
	
	$query = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix . 'ap_messages m JOIN  '.$wpdb->prefix . 'ap_meta meta ON %d IN (meta.apmeta_value) JOIN (SELECT MAX(message_id) as message_id FROM '.$wpdb->prefix . 'ap_messages GROUP BY message_conversation) m2 WHERE m2.message_id = m.message_id AND meta.apmeta_type= "conversation" AND m.message_conversation = meta.apmeta_id GROUP BY m.message_conversation ORDER BY m.message_date DESC', $recipient_id);
	
	return $wpdb->get_results($query);
}

function ap_conversations_list($recipient_id = false){
	if(!$recipient_id)
		$recipient_id = ap_get_user_page_user();
		
	$conversation = ap_get_conversations($recipient_id);
	if($conversation){
		echo '<ul class="ap-senders ap-nav">';
		foreach($conversation as $message) :
		?>	
			<li class="clearfix" data-action="ap-show-conversation" data-id="<?php echo $message->message_conversation; ?>">
				<div class="ap-avatar"><?php ap_user_avatar($message->message_sender); ?></div>
				<div class="ap-message-summery">
					<strong><?php echo ap_user_display_name($message->message_sender); ?></strong>
					<span><?php echo ap_truncate_chars(stripcslashes($message->message_content), 40); ?></span>
				</div>
			</li>
		<?php
		endforeach;
		echo '</ul>';
	}
}

function ap_get_conversation_list($id){
	$messages = ap_get_conversation($id);
	
	if($messages){
		echo '<ul class="ap-message-log ap-nav">';
			foreach($messages as $m){
				?>
				<li class="clearfix">
					<div class="ap-avatar"><?php ap_user_avatar($m->message_sender); ?></div>
					<div class="ap-message no-overflow">
						<strong><?php echo ap_user_display_name($m->message_sender); ?></strong>
						<?php echo stripcslashes($m->message_content); ?>
					</div>
				</li>
				<?php
			}
		echo '</ul>';
	}
	?>
	<form id="ap-send-message" method="post" action="" data-action="ap-send-message">
		<textarea class="form-control" name="message-content" placeholder="<?php _e('Type your message', 'ap'); ?>"></textarea>
		<button type="submit" class="ap-btn"><?php _e('Send', 'ap'); ?></button>
		<input type="hidden" name="action" value="ap_send_message" />
		<input type="hidden" name="conversation" value="<?php echo $id; ?>" />
	</form>
	<?php
}
