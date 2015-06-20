<?php
/**
 * All actions of AnsPress
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

class AnsPress_Actions
{
	/**
	 * Initialize the class
	 * @since 2.0.1
	 */
	public function __construct()
	{
		new AnsPress_Post_Status;
		new AnsPress_Rewrite;
		
		AP_History::get_instance();

		add_action( 'ap_processed_new_question', array($this, 'after_new_question'), 1, 2 );
		add_action( 'ap_processed_new_answer', array($this, 'after_new_answer'), 1, 2 );

		add_action( 'ap_processed_update_question', array($this, 'ap_after_update_question'), 1, 2 );
		add_action( 'ap_processed_update_answer', array($this, 'ap_after_update_answer'), 1, 2 );

		add_action( 'before_delete_post', array($this, 'before_delete'));	

		add_action( 'wp_trash_post', array($this, 'trash_post_action'));
		add_action( 'untrash_post', array($this, 'untrash_ans_on_question_untrash'));

		add_action( 'comment_post', array($this, 'new_comment_approve'), 10, 2);
		add_action( 'comment_unapproved_to_approved', array($this, 'comment_approve'));
		add_action( 'comment_approved_to_unapproved', array($this, 'comment_unapproved'));
		add_action( 'trashed_comment', array($this, 'comment_trash'));
		add_action( 'delete_comment ', array($this, 'comment_trash'));
		add_action( 'ap_publish_comment', array($this, 'publish_comment'));
		add_action( 'ap_unpublish_comment', array($this, 'unpublish_comment'));
		add_filter( 'wp_get_nav_menu_items', array($this, 'update_menu_url'));
		add_filter( 'nav_menu_css_class', array($this, 'fix_nav_current_class'), 10, 2 );
		add_filter( 'walker_nav_menu_start_el', array($this, 'walker_nav_menu_start_el'), 10, 4 );

		add_action( 'wp_loaded', array( $this, 'flush_rules' ) );
		add_filter( 'mce_buttons', array($this, 'editor_buttons'), 10, 2 );
		///add_filter( 'teeny_mce_plugins', array($this, 'editor_plugins'), 10, 2 );
		
		add_filter( 'wp_insert_post_data', array($this, 'wp_insert_post_data'), 10, 2 );
		add_filter( 'ap_form_contents_filter', array($this, 'sanitize_description') );		
		add_action( 'safe_style_css', array($this, 'safe_style_css'), 11);
		add_action( 'save_post', array($this, 'base_page_update'), 10, 2);
		add_action( 'ap_added_follower', array($this, 'ap_added_follower'), 10, 2);
		add_action( 'ap_removed_follower', array($this, 'ap_added_follower'), 10, 2);
		add_action( 'ap_vote_casted', array($this, 'update_user_vote_casted_count'), 10, 4);
		add_action( 'ap_vote_removed', array($this, 'update_user_vote_casted_count'), 10, 4);
		add_action( 'ap_added_follower', array($this, 'notify_user_about_follower'), 10, 2);
		add_action( 'ap_vote_casted', array($this, 'notify_upvote'), 10, 4);
	}

	/**
	 * Things to do after creating a question
	 * @param  int $post_id
	 * @return void
	 * @since 1.0
	 */
	public function after_new_question($post_id, $post)
	{
		update_post_meta($post_id, ANSPRESS_VOTE_META, '0');
		update_post_meta($post_id, ANSPRESS_SUBSCRIBER_META, '0');
		update_post_meta($post_id, ANSPRESS_CLOSE_META, '0');
		update_post_meta($post_id, ANSPRESS_FLAG_META, '0');
		update_post_meta($post_id, ANSPRESS_VIEW_META, '0');
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		update_post_meta($post_id, ANSPRESS_SELECTED_META, false);

		// subscribe to current question
		ap_add_question_subscriber($post_id);
		
		//update answer count
		update_post_meta($post_id, ANSPRESS_ANS_META, '0');

		ap_update_user_questions_count_meta($post_id);

		/**
		 * ACTION: ap_after_new_question
		 * action triggered after inserting a question
		 * @since 0.9
		 */
		do_action('ap_after_new_question', $post_id, $post);
	}
	
	/**
	 * Things to do after creating an answer
	 * @param  int $post_id
	 * @param  object $post
	 * @return void
	 * @since 2.0.1
	 */
	public function after_new_answer($post_id, $post)
	{
		
		$question = get_post($post->post_parent);
		// set default value for meta
		update_post_meta($post_id, ANSPRESS_VOTE_META, '0');
		
		// set updated meta for sorting purpose
		update_post_meta($question->ID, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));

		// subscribe to current question
		ap_add_question_subscriber($question->ID);	
		
		// get existing answer count
		$current_ans = ap_count_published_answers($question->ID);
		
		//update answer count
		update_post_meta($question->ID, ANSPRESS_ANS_META, $current_ans);
		
		update_post_meta($post_id, ANSPRESS_BEST_META, 0);

		ap_update_user_answers_count_meta($post_id);

		ap_insert_notification( $post->post_author, $question->post_author, 'new_answer', array('post_id' => $post_id) );
		
		/**
		 * ACTION: ap_after_new_answer
		 * action triggered after inserting an answer
		 * @since 0.9
		 */
		do_action('ap_after_new_answer', $post_id, $post);
	}

	public function ap_after_update_question($post_id, $post){
		// set updated meta for sorting purpose
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));

		ap_insert_notification( get_current_user_id(), $post->post_author, 'question_update', array('post_id' => $post_id) );

		/**
		 * ACTION: ap_after_new_answer
		 * action triggered after inserting an answer
		 * @since 0.9
		 */
		do_action('ap_after_update_question', $post_id, $post);
	}

	public function ap_after_update_answer($post_id, $post)
	{
		
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		update_post_meta($post->post_parent, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		
		//update answer count
		$current_ans = ap_count_published_answers($post->post_parent);
		update_post_meta($post->post_parent, ANSPRESS_ANS_META, $current_ans);
		ap_insert_notification( get_current_user_id(), $post->post_author, 'answer_update', array('post_id' => $post_id) );

		/**
		 * ACTION: ap_processed_update_answer
		 * action triggered after inserting an answer
		 * @since 0.9
		 */
		do_action('ap_after_update_answer', $post_id, $post);
	}

	public function before_delete($post_id){
		$post = get_post($post_id);
		if($post->post_type == 'question' || $post->post_type == 'answer' ){
			do_action('ap_before_delete_'.$post->post_type, $post->ID, $post->post_author);
			ap_delete_meta(array('apmeta_actionid' => $post->ID));
		}
	}
	/**
	 * if a question is sent to trash, then move its answers to trash as well
	 * @param  int
	 * @return void
	 * @since 2.0.0
	 */
	public function trash_post_action ($post_id) {
		$post = get_post( $post_id );

		if( $post->post_type == 'question') {
			do_action('ap_trash_question', $post);
			//ap_remove_parti($post->ID, $post->post_author, 'question');
			ap_delete_meta(array('apmeta_type' => 'flag', 'apmeta_actionid' => $post->ID));
			$arg = array(
			  'post_type' => 'answer',
			  'post_status' => 'publish',
			  'post_parent' => $post_id,
			  'showposts' => -1,
			);
			$ans = get_posts($arg);
			if($ans>0){
				foreach( $ans as $p){					
					//ap_remove_parti($p->post_parent, $p->post_author, 'answer');
					do_action('ap_trash_multi_answer', $post);
					ap_delete_meta(array('apmeta_type' => 'flag', 'apmeta_actionid' => $p->ID));
					wp_trash_post($p->ID);
				}
			}
		}

		if( $post->post_type == 'answer') {
			$ans = ap_count_published_answers($post->post_parent);
			$ans = $ans > 0 ? $ans - 1 : 0;
			do_action('ap_trash_answer', $post);
			//ap_remove_parti($post->post_parent, $post->post_author, 'answer');
			ap_delete_meta(array('apmeta_type' => 'flag', 'apmeta_actionid' => $post->ID));
			ap_remove_question_subscriber($post->post_parent, $post->post_author);
			//update answer count
			update_post_meta($post->post_parent, ANSPRESS_ANS_META, $ans);
		}
	}

	/**
	 * if questions is restored then restore its answers too.
	 * @param  int
	 * @return void
	 * @since 2.0.0
	 */
	public function untrash_ans_on_question_untrash ($post_id) {
		$post = get_post( $post_id );
		
		if( $post->post_type == 'question') {
			do_action('ap_untrash_question', $post->ID);
			//ap_add_parti($post->ID, $post->post_author, 'question');
			
			$arg = array(
			  'post_type' => 'answer',
			  'post_status' => 'trash',
			  'post_parent' => $post_id,
			  'showposts' => -1
			);
			$ans = get_posts($arg);
			if($ans>0){
				foreach( $ans as $p){
					do_action('ap_untrash_answer', $p->ID);
					//ap_add_parti($p->ID, $p->post_author, 'answer');
					wp_untrash_post($p->ID);
				}
			}
		}
		
		if( $post->post_type == 'answer') {
			$ans = ap_count_published_answers( $post->post_parent );
			do_action('untrash_answer', $post->ID, $post->post_author);
			//ap_add_parti($post->post_parent, $post->post_author, 'answer');
			
			//update answer count
			update_post_meta($post->post_parent, ANSPRESS_ANS_META, $ans+1);
		}
	}

	public function new_comment_approve($comment_id, $approved)
	{
		if($approved === 1){
			$comment = get_comment($comment_id);
			do_action('ap_publish_comment', $comment);
		}
	}

	public function comment_approve($comment)
	{
		do_action('ap_publish_comment', $comment);
	}

	public function comment_unapprove($comment)
	{
		do_action('ap_unpublish_comment', $comment);
	}
	public function comment_trash($comment_id)
	{
		$comment = get_comment($comment_id);
		do_action('ap_unpublish_comment', $comment);
	}

	/**
	 * Actions to run after posting a comment
	 * @param  object $comment
	 * @return null|integer   
	 */
	public function publish_comment($comment){
		$comment = (object) $comment;

		$post = get_post( $comment->comment_post_ID );

		if ($post->post_type == 'question') {
			// set updated meta for sorting purpose
			update_post_meta($comment->comment_post_ID, ANSPRESS_UPDATED_META, current_time( 'mysql' ));

			// add participant
			//ap_add_parti($comment->comment_post_ID, $comment->user_id, 'comment');

			// subscribe to current question
			ap_add_question_subscriber($comment->comment_post_ID, $comment->user_id);
			ap_insert_notification( $comment->user_id, $post->post_author, 'comment_on_question', array('post_id' => $post->ID, 'comment_id' => $comment->comment_ID ) );

		}elseif($post->post_type == 'answer'){

			$post_id = wp_get_post_parent_id($comment->comment_post_ID);
			// set updated meta for sorting purpose
			update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
			ap_add_question_subscriber($post_id, $comment->user_id);

			ap_insert_notification( $comment->user_id, $post->post_author, 'comment_on_answer', array('post_id' => $post->ID, 'comment_id' => $comment->comment_ID) );
		}

	}

	public function unpublish_comment($comment){
		$comment = (object) $comment;
		$post = get_post( $comment->comment_post_ID );

		if ($post->post_type == 'question') {
			//ap_remove_parti($comment->comment_post_ID, $comment->user_id, 'comment');
			ap_remove_question_subscriber($post->ID, $comment->user_id);

		}elseif($post->post_type == 'answer'){
			$post_id = wp_get_post_parent_id($comment->comment_post_ID);
			//ap_remove_parti($post_id, $comment->user_id, 'comment');
			ap_remove_question_subscriber($post_id, $comment->user_id);
		}
	}

	/**
	 * Update AnsPress pages URL dynimacally
	 * @param  array $items
	 * @return array
	 */
	public function update_menu_url( $items ) {
		if(is_admin())
			return $items;

		$pages = anspress()->pages;

		$pages['profile'] 		= array('title' => __('My profile', 'ap'), 'show_in_menu' => true, 'logged_in' => true);
		$pages['notification'] 	= array('title' => __('My notification', 'ap'), 'show_in_menu' => true, 'logged_in' => true);
		
		$pages['ask'] 				= array();
		$pages['question'] 			= array();
		$pages['users'] 			= array();
		$pages['user'] 				= array();

		$page_url = array();
		
		foreach($pages as $slug => $args){
			$page_url[$slug] = 'ANSPRESS_PAGE_URL_'.strtoupper($slug);
		}

		if(!empty($items) && is_array($items))
			foreach ( $items as $key => $item ) {			
				
				if(false !== $slug = array_search(str_replace(array('http://', 'https://'), '', $item->url), $page_url)){
					$page = $pages[$slug];

					if(isset($page['logged_in']) && $page['logged_in'] && !is_user_logged_in())
						unset($items[$key]);

					if($slug == 'profile')
						$item->url = is_user_logged_in() ? ap_user_link(get_current_user_id()) : wp_login_url( );
					else
						$item->url = ap_get_link_to($slug);

					$item->classes[] = 'anspress-page-link';
					$item->classes[] = 'anspress-page-'.$slug;
					
					if(get_query_var('ap_page') == $slug)
						$item->classes[] = 'anspress-active-menu-link';
				}

			}

		return $items;
	}

	/**
	 * add current-menu-item class in AnsPress pages
	 * @param  array $class
	 * @param  object $item
	 * @return array
	 * @since  2.1
	 */
	public function fix_nav_current_class( $class, $item ) {		
		$pages = anspress()->pages;
		if(!empty($item) && is_object($item)){
			foreach($pages as $slug => $args){
				if(in_array('anspress-page-link', $class)){
					if(ap_get_link_to(get_query_var('ap_page')) != $item->url){
						$pos = array_search('current-menu-item', $class);
						unset($class[$pos]);
					}
					if(!in_array('ap-dropdown', $class) && (in_array('anspress-page-notification', $class) || in_array('anspress-page-profile', $class)) )
						$class[] = 'ap-dropdown';
				}
			}
		}
		return $class;
	}

	/**
	 * Add user dropdown and notification menu
	 * @param  string 		$item_output		Menu html
	 * @param  object 		$item        		Menu item object
	 * @param  integer 		$depth       		Menu depth
	 * @param  object 		$args
	 * @return string
	 */
	public function walker_nav_menu_start_el($item_output, $item, $depth, $args) {

		if(!is_user_logged_in() && (in_array('anspress-page-profile', $item->classes) || in_array('anspress-page-notification', $item->classes) )) {
			$item_output = '';
		}

		if(in_array('anspress-page-profile', $item->classes) && is_user_logged_in()){

			$menus = ap_get_user_menu(get_current_user_id());

			$active_user_page   = get_query_var('user_page');

	    	$active_user_page   = $active_user_page ? $active_user_page : 'about';

			$item_output = '<a id="ap-user-menu-anchor" class="ap-dropdown-toggle"  href="#">'.get_avatar(get_current_user_id(), 20).ap_user_display_name(get_current_user_id()).ap_icon('chevron-down', true).'</a>';
			
			$item_output .= '<ul id="ap-user-menu-link" class="ap-dropdown-menu ap-user-dropdown-menu">';
			
			foreach($menus as $m){
				
				$class = !empty($m['class']) ? ' '.$m['class'] : '';

	            $item_output .= '<li'.($active_user_page == $m['slug'] ? ' class="active"' : '').'><a href="'.$m['link'].'" class="ap-user-link-'.$m['slug'].$class.'">'.$m['title'].'</a></li>';
	        }

			$item_output .= '</ul>';

		}elseif(in_array('anspress-page-notification', $item->classes) && is_user_logged_in()){

			$item_output = '<a id="ap-user-notification-anchor" class="ap-dropdown-toggle '.ap_icon('globe').'" href="#">'.ap_get_the_total_unread_notification(false, false).'</a>';
		
			global $ap_notifications;

			ob_start();

	        $ap_notifications = ap_get_user_notifications(array('per_page' => 10));
	        
	        ap_get_template_part('user/notification-dropdown');

	        $item_output .= ob_get_clean();
			
		}

		return $item_output;

	} 

	/**
	 * Check if flushing rewrite rule is needed
	 * @return void
	 */
	public function flush_rules(){
		if (ap_opt('ap_flush') != 'false') {
			flush_rewrite_rules( );
			ap_opt('ap_flush', 'false');
		}
	}

	public function editor_buttons( $buttons, $editor_id )
	{
		if(is_anspress())
	    	return array( 'bold', 'italic', 'underline', 'strikethrough', 'bullist', 'numlist', 'link', 'unlink', 'blockquote', 'pre' );

	   	return $buttons;
	}

	public function editor_plugins( $plugin, $editor_id )
	{
		if(is_anspress()){
	    	$plugin[] = 'wpautoresize';
		}

	   	return $plugin;
	}

	/**
	 * Filter post so that anonymous author should not be replaced
	 * @param  array $data
	 * @param  array $args
	 * @return array
	 * @since 2.2 
	 */
	public function wp_insert_post_data( $data, $args )
	{

		if($args['post_type'] == 'question' || $args['post_type'] == 'answer'){
			if($args['post_author'] == '0')
				$data['post_author'] = '0';
		}

		return $data;
	}

	public function sanitize_description($contents){
		$contents = ap_trim_traling_space($contents);
		$contents = ap_replace_square_bracket($contents);

		return $contents;
	}

	public function safe_style_css($attr)
	{
		global $ap_kses_checkc; // Check if wp_kses is called by AnsPress
		if(isset($ap_kses_check) && $ap_kses_check){
			$attr = array('text-decoration', 'text-align');
		}
		return $attr;
	}

	public function base_page_update($post_id, $post)
	{
		if(wp_is_post_revision( $post ))
			return;

		if($post_id == ap_opt('base_page'))
			ap_opt('ap_flush', 'true');
	}

	/**
	 * Update total followers and following count meta
	 * @param  integer  $user_to_follow
	 * @param  integer  $current_user_id
	 * @return void
	 */
	public function ap_added_follower($user_to_follow, $current_user_id)
	{
		// update total followers count meta
		update_user_meta( $user_to_follow, '__total_followers', ap_followers_count($user_to_follow) );

		// update total following count meta
		update_user_meta( $current_user_id, '__total_following', ap_following_count($current_user_id) );
	}


	public function update_user_vote_casted_count($userid, $type, $actionid, $receiving_userid)
	{
		// Update total casted vote of user
		update_user_meta( $userid, '__up_vote_casted', ap_count_vote($userid, 'vote_up') );
		update_user_meta( $userid, '__down_vote_casted', ap_count_vote($userid, 'vote_down'));

		// Update total received vote of user
		update_user_meta( $receiving_userid, '__up_vote_received', ap_count_vote(false, 'vote_up', false, $receiving_userid) );
		update_user_meta( $receiving_userid, '__down_vote_received', ap_count_vote(false, 'vote_down', false, $receiving_userid));
	}

	public function notify_upvote($userid, $type, $actionid, $receiving_userid)
	{
		if($type == 'vote_up')
			ap_insert_notification( $userid, $receiving_userid, 'vote_up', array('post_id' => $actionid) );
	}

	public function notify_user_about_follower($user_to_follow, $current_user_id)
	{
		ap_insert_notification( $current_user_id, $user_to_follow, 'new_follower');
	}
}