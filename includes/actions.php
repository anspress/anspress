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

		add_action( 'wp_loaded', array( $this, 'flush_rules' ) );
		add_filter( 'mce_buttons', array($this, 'editor_buttons'), 10, 2 );
		///add_filter( 'teeny_mce_plugins', array($this, 'editor_plugins'), 10, 2 );
		
		add_filter( 'wp_insert_post_data', array($this, 'wp_insert_post_data'), 10, 2 );
		add_filter( 'ap_form_contents_filter', array($this, 'sanitize_description') );

		add_action( 'wp', array( $this, 'remove_head_items' ), 10 );
		add_action('wp_head', array($this, 'wp_head'), 11);

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
		ap_do_event('edit_answer', $post_id, get_current_user_id(), $post->post_parent);

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

		$post_type = get_post_type( $comment->comment_post_ID );

		if ($post_type == 'question') {
			// set updated meta for sorting purpose
			update_post_meta($comment->comment_post_ID, ANSPRESS_UPDATED_META, current_time( 'mysql' ));

			// add participant
			//ap_add_parti($comment->comment_post_ID, $comment->user_id, 'comment');

			// subscribe to current question
			ap_add_question_subscriber($comment->comment_post_ID, $comment->user_id);

		}elseif($post_type == 'answer'){

			$post_id = wp_get_post_parent_id($comment->comment_post_ID);
			// set updated meta for sorting purpose
			update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
			// add participant only
			//ap_add_parti($post_id, $comment->user_id, 'comment');

			ap_add_question_subscriber($post_id, $comment->user_id);
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
		$pages = anspress()->pages;
		if(!empty($items) && is_array($items))
			foreach ( $items as $key => $item ) {
				foreach($pages as $slug => $args){	

					if(strpos($item->url, strtoupper('ANSPRESS_PAGE_URL_'.$slug)) !== FALSE ){
						$item->url = ap_get_link_to($slug);
						$item->classes[] = 'anspress-page-link';
						$item->classes[] = 'anspress-page-'.$slug;
						
						if(get_query_var('ap_page') == $slug)
							$item->classes[] = 'anspress-active-menu-link';
					}
					
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
				}
			}
		}
		return $class;
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

	public function remove_head_items(){
		if(is_anspress()){
			remove_action('wp_head', 'rsd_link');
			remove_action('wp_head', 'wlwmanifest_link');
			remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
			remove_action('wp_head', 'rel_canonical');
			remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0 );
			remove_action('wp_head', 'feed_links_extra', 3 );
			remove_action('wp_head', 'feed_links', 2 );
		}
	}

	public function wp_head(){
		if(is_anspress()){
			$q_feed = get_post_type_archive_feed_link( 'question' );
			$a_feed = get_post_type_archive_feed_link( 'answer' );
			echo '<link rel="alternate" type="application/rss+xml" title="'.__('Question feed', 'ap').'" href="'.$q_feed.'" />';
			echo '<link rel="alternate" type="application/rss+xml" title="'.__('Answers feed', 'ap').'" href="'.$a_feed.'" />';
		}
		
		if(is_question()){
			echo '<link rel="canonical" href="'.get_permalink(get_question_id()).'">';
			echo '<link rel="shortlink" href="'.wp_get_shortlink(get_question_id()).'" />';
		}
	}

}