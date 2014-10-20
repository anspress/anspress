<?php
/*
	Name:Realtime
	Description: For showing realtime updates in AnsPress
	Version:1.0
	Author: Rahul Aryan
	Author URI: http://open-wp.com
	Addon URI: http://open-wp.com/anspress/realtime	
*/


class AP_Realtime_Addon
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
		add_action('ap_enqueue', array($this, 'addon_style_script'));
		add_action('ap_event_new_answer', array($this, 'new_answer'), 10, 4);
		add_action('ap_event_vote_up', array($this, 'vote_up'), 10, 2);
		add_action('ap_event_vote_down', array($this, 'vote_down'), 10, 2);
		add_action('ap_event_undo_vote_up', array($this, 'undo_vote_up'), 10, 2);
		add_action('ap_event_undo_vote_down', array($this, 'undo_vote_down'), 10, 2);
		add_action('ap_event_new_comment', array($this, 'new_comment'), 10, 2);
    }
	
	
	public function addon_style_script(){
		?>
			<script type="text/javascript">
				var realtime_process = '<?php echo ANSPRESS_ADDON_URL.'realtime/send.php'; ?>';
				var load_time = '<?php echo current_time('timestamp'); ?>';
				var ap_page = '<?php echo ap_current_page_is(); ?>';
				<?php if(is_question()): ?>
				var qid = '<?php echo get_question_id(); ?>';
				<?php endif; ?>
			</script>
		<?php
		wp_enqueue_script( 'event-source', ANSPRESS_ADDON_URL. 'realtime/eventsource.min.js', 'jquery', AP_VERSION);
		wp_enqueue_script( 'ap-realtime', ANSPRESS_ADDON_URL. 'realtime/realtime.js', 'jquery', AP_VERSION);
		
	}
	public function new_answer($postid, $userid, $question_id, $result) {
		$result['message'] = __('New answer loaded', 'ap');
		ap_push_to_browser('question_'.$question_id, $result);
	}
	
	public function vote_up($postid, $counts) {
		$post = get_post($postid);
		if($post->post_type == 'question'){
			$name = 'question_'.$postid;
		}elseif($post->post_type == 'answer'){			
			$name = 'question_'.$post->post_parent;
		}
		ap_push_to_browser($name, array(
			'action' 	=> 'upvote',
			'post_id' 	=> $postid,
			'net_vote' 	=> $counts['net_vote'],
			'type' 		=> $post->post_type,
		));
	}
	
	public function vote_down($postid, $counts) {	
		$post = get_post($postid);
		if($post->post_type == 'question'){
			$name = 'question_'.$postid;
		}elseif($post->post_type == 'answer'){			
			$name = 'question_'.$post->post_parent;
		}
		ap_push_to_browser($name, array(
			'action' 	=> 'downvote',
			'post_id' 	=> $postid,
			'net_vote' 	=> $counts['net_vote'],
			'type' 		=> $post->post_type,
		));
	}
	
	public function undo_vote_up($postid, $counts) {	
		$post = get_post($postid);
		if($post->post_type == 'question'){
			$name = 'question_'.$postid;
		}elseif($post->post_type == 'answer'){			
			$name = 'question_'.$post->post_parent;
		}
		ap_push_to_browser($name, array(
			'action' 	=> 'voteup_undo',
			'post_id' 	=> $postid,
			'net_vote' 	=> $counts['net_vote'],
			'type' 		=> $post->post_type,
		));
	}
	
	public function undo_vote_down($postid, $counts) {	
		$post = get_post($postid);
		if($post->post_type == 'question'){
			$name = 'question_'.$postid;
		}elseif($post->post_type == 'answer'){			
			$name = 'question_'.$post->post_parent;
		}
		ap_push_to_browser($name, array(
			'action' 	=> 'votedown_undo',
			'post_id' 	=> $postid,
			'net_vote' 	=> $counts['net_vote'],
			'type' 		=> $post->post_type,
		));
	}
	public function new_comment($comment, $post_type){
		$post = get_post($comment->comment_post_ID);
		if($post->post_type == 'question'){
			$name = 'question_'.$post->ID;
		}elseif($post->post_type == 'answer'){			
			$name = 'question_'.$post->post_parent;
		}
		ob_start();
		ap_comment($comment);		
		$html = ob_get_clean();
		
		ap_push_to_browser($name, array(
			'action' 	=> 'comment',
			'message' 	=> __('New comment loaded', 'ap'),
			'comment_id' 	=> $comment->comment_ID,
			'post_id' 	=> $comment->comment_post_ID,
			'type' 		=> $post->post_type,
			'html' 		=> $html,
		));
	}
}

function ap_push_to_browser($name, $parameters){

	require_once('libSSE/libsse.php');
	//$parameters['time'] = current_time('timestamp');
	$data = new SSEData('file',array('path'=>ANSPRESS_ADDON_DIR.DS.'realtime'.DS.'data'));
	
	$exist = $data->get($name);
	$time = current_time('timestamp');
	if(!empty($exist)){
		$old_data = new stdClass();
		$old_data = json_decode($data->get($name));		
		$old_data->$time = $parameters;
		$data->set($name, json_encode($old_data));
	}else{
		$old_data = new stdClass();
		$old_data->$time = $parameters;
		$data->set($name, json_encode($old_data));
	}
}

AP_Realtime_Addon::get_instance();
