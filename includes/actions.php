<?php
/**
 * All actions of AnsPress
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
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
		//add_action( 'after_setup_theme', array($this, 'after_setup_theme') );
		add_action( 'ap_after_new_question', array($this, 'after_new_question'), 10, 2 );
		add_action( 'ap_after_new_answer', array($this, 'after_new_answer'), 10, 2 );

		add_action( 'ap_after_update_question', array($this, 'ap_after_update_question'), 10, 2 );
		add_action( 'ap_after_update_answer', array($this, 'ap_after_update_answer'), 10, 2 );

		add_action('wp_trash_post', array($this, 'trash_post_action'));
		add_action('untrash_post', array($this, 'untrash_ans_on_question_untrash'));

		add_action('pre_comment_approved', array($this, 'pre_comment_approved'), 99, 2);

		add_filter('query_vars', array($this, 'query_var'));
		add_filter( 'wp_get_nav_menu_items', array($this, 'update_menu_url'));

		add_action( 'wp_loaded', array( $this, 'flush_rules' ) );
		add_filter('get_avatar', array($this, 'get_avatar'), 10, 5);

		add_action('generate_rewrite_rules', array( $this, 'rewrites'), 1);
		
	}

	/**
     * Actions to do after after theme setup
     * @return void
     * @since 2.0.0-alpha2
     */
    public function after_setup_theme()
    {

    }

	/**
	 * Things to do after creating a question
	 * @param  int $post_id
	 * @return void
	 * @since 1.0
	 */
	public function after_new_question($post_id)
	{

		$user_id = get_current_user_id();
		update_post_meta($post_id, ANSPRESS_VOTE_META, '0');
		update_post_meta($post_id, ANSPRESS_SUBSCRIBER_META, '0');
		update_post_meta($post_id, ANSPRESS_CLOSE_META, '0');
		update_post_meta($post_id, ANSPRESS_FLAG_META, '0');
		update_post_meta($post_id, ANSPRESS_VIEW_META, '0');
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		update_post_meta($post_id, ANSPRESS_SELECTED_META, false);
		
		//ap_add_history($user_id, $post_id, 'asked');
		ap_add_parti($post_id, $user_id, 'question');
		
		//update answer count
		update_post_meta($post_id, ANSPRESS_ANS_META, '0');

		do_action('ap_after_inserting_question', $post_id);
		ap_do_event('new_question', $post_id, $user_id);

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
		
		$user_id = get_current_user_id();	
		$question = get_post($post->post_parent);
		// set default value for meta
		update_post_meta($post_id, ANSPRESS_VOTE_META, '0');
		
		// set updated meta for sorting purpose
		update_post_meta($question->ID, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		
		ap_add_parti($question->ID, $user_id, 'answer');			
		
		// get existing answer count
		$current_ans = ap_count_published_answers($question->ID);
		
		//update answer count
		update_post_meta($question->ID, ANSPRESS_ANS_META, $current_ans);
		
		update_post_meta($post_id, ANSPRESS_BEST_META, 0);
		
		do_action('ap_after_inserting_answer', $post_id);
		ap_do_event('new_answer', $post_id, $user_id, $question->ID);
	}

	public function ap_after_update_question($post_id){
		// set updated meta for sorting purpose
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));

		ap_do_event('edit_question', $post_id, get_current_user_id());
	}

	public function ap_after_update_answer($post_id, $post)
	{
		
		update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		update_post_meta($post->post_parent, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
		
		//update answer count
		$current_ans = ap_count_published_answers($post->post_parent);
		update_post_meta($post->post_parent, ANSPRESS_ANS_META, $current_ans);
		ap_do_event('edit_answer', $post_id, get_current_user_id(), $post->post_parent);
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
			ap_do_event('delete_question', $post->ID, $post->post_author);
			ap_remove_parti($post->ID, $post->post_author, 'question');
			$arg = array(
			  'post_type' => 'answer',
			  'post_status' => 'publish',
			  'post_parent' => $post_id,
			  'showposts' => -1,
			);
			$ans = get_posts($arg);
			if($ans>0){
				foreach( $ans as $p){
					ap_do_event('delete_answer', $p->ID, $p->post_author);
					ap_remove_parti($p->post_parent, $p->post_author, 'answer');
					wp_trash_post($p->ID);
				}
			}
		}

		if( $post->post_type == 'answer') {
			$ans = ap_count_published_answers($post->post_parent);
			ap_do_event('delete_answer', $post->ID, $post->post_author);
			ap_remove_parti($post->post_parent, $post->post_author, 'answer');
			
			//update answer count
			update_post_meta($post->post_parent, ANSPRESS_ANS_META, $ans-1);
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
			ap_do_event('untrash_question', $post->ID, $post->post_author);
			ap_add_parti($post->ID, $post->post_author, 'question');
			
			$arg = array(
			  'post_type' => 'answer',
			  'post_status' => 'trash',
			  'post_parent' => $post_id,
			  'showposts' => -1
			);
			$ans = get_posts($arg);
			if($ans>0){
				foreach( $ans as $p){
					ap_do_event('untrash_answer', $p->ID, $p->post_author);
					ap_add_parti($p->ID, $p->post_author, 'answer');
					wp_untrash_post($p->ID);
				}
			}
		}
		
		if( $post->post_type == 'answer') {
			$ans = ap_count_published_answers( $post->post_parent );
			ap_do_event('untrash_answer', $post->ID, $post->post_author);
			ap_add_parti($post->post_parent, $post->post_author, 'answer');
			
			//update answer count
			update_post_meta($post->post_parent, ANSPRESS_ANS_META, $ans+1);
		}
	}

	/**
	 * Actions to run after posting a comment
	 * @param  int $approved
	 * @param  object $commentdata
	 * @return null|integer   
	 */
	public function pre_comment_approved($approved , $commentdata){
		if($approved =='1' ){
			$post_type = get_post_type( $commentdata['comment_post_ID'] );

			if ($post_type == 'question') {
				// set updated meta for sorting purpose
				update_post_meta($commentdata['comment_post_ID'], ANSPRESS_UPDATED_META, current_time( 'mysql' ));

				// add participant
				ap_add_parti($commentdata['comment_post_ID'], $commentdata['user_ID'], 'comment');

			}elseif($post_type == 'answer'){
				$post_id = wp_get_post_parent_id($commentdata['comment_post_ID']);
				// set updated meta for sorting purpose
				update_post_meta($post_id, ANSPRESS_UPDATED_META, current_time( 'mysql' ));
				// add participant only
				ap_add_parti($post_id, $commentdata['user_ID'], 'comment');
			}
		}else{
			return $approved;
		}
	}

	/**
	 * Register query vars
	 * @param  array $query_vars 
	 * @return string[]             
	 */
	public function query_var( $query_vars) {

		$query_vars[] = 'edit_post_id';
		$query_vars[] = 'ap_nonce';
		$query_vars[] = 'question_id';
		$query_vars[] = 'question';
		$query_vars[] = 'question_name';
		$query_vars[] = 'answer_id';
		$query_vars[] = 'answer';
		$query_vars[] = 'ask';
		$query_vars[] = 'ap_page';
		$query_vars[] = 'qcat_id';
		$query_vars[] = 'qcat';
		$query_vars[] = 'qtag_id';
		$query_vars[] = 'q_tag';
		$query_vars[] = 'q_cat';

		$query_vars[] = 'label';
		$query_vars[] = 'user';
		$query_vars[] = 'user_page';
		$query_vars[] = 'ap_s';
		$query_vars[] = 'message_id';
		$query_vars[] = 'parent';
		
		return $query_vars;
	}

	public function update_menu_url( $items ) {		
		// Iterate over the items
		foreach ( $items as $key => $item ) {
			
			if('http://ANSPRESS_BASE_PAGE_URL' == $item->url)
				$item->url = get_permalink(ap_opt('base_page'));
			
			if('http://ANSPRESS_ASK_PAGE_URL' == $item->url)
				$item->url = ap_get_link_to('ask');
			
			if('http://ANSPRESS_CATEGORIES_PAGE_URL' == $item->url)
				$item->url = ap_get_link_to('categories');
			
			if('http://ANSPRESS_TAGS_PAGE_URL' == $item->url)
				$item->url = ap_get_link_to('tags');
			
			if('http://ANSPRESS_USERS_PAGE_URL' == $item->url)
				$item->url = ap_get_link_to('users');
			
			if('http://ANSPRESS_USER_PROFILE_URL' == $item->url)
				$item->url = ap_user_link(get_current_user_id());
		}

		return $items;
	}

	public function flush_rules(){
		// Check the option we set on activation.
		if (ap_opt('ap_flush')) {
			flush_rewrite_rules( );
			ap_opt('ap_flush', 'false');
		}
	}

	/**
	 * Override get_avatar
	 * @param  string $avatar      
	 * @param  integar|string $id_or_email
	 * @param  string $size        
	 * @param  string $default     
	 * @param  string $alt         
	 * @return string              
	 */
	public function get_avatar($avatar, $id_or_email, $size, $default, $alt)
    {
        if (!empty($id_or_email)) {
            if (is_object($id_or_email)) {
                $allowed_comment_types = apply_filters('get_avatar_comment_types', array( 'comment' ));
                if (! empty($id_or_email->comment_type) && ! in_array($id_or_email->comment_type, (array) $allowed_comment_types)) {
                    return $avatar;
                }

                if (! empty($id_or_email->user_id)) {
                    $id = (int) $id_or_email->user_id;
                    $user = get_userdata($id);
                    if ($user) {
                        $id_or_email = $user->ID;
                    }
                }
            } elseif (is_email($id_or_email)) {
                $u = get_user_by('email', $id_or_email);
                $id_or_email = $u->ID;
            }

            $resized = ap_get_resized_avatar($id_or_email, $size);

            if ($resized) {
                return "<img alt='{$alt}' src='{$resized}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
            }

            return $avatar;
        }
    }

    public function rewrites() {  
		global $wp_rewrite;  
		global $ap_rules;
		
		unset($wp_rewrite->extra_permastructs['question']); 
        unset($wp_rewrite->extra_permastructs['answer']); 
		
		$base_page_id 		= ap_opt('base_page');
		
		$slug = ap_base_page_slug().'/';

		$new_rules = array(  
			
			$slug. "parent/([^/]+)/?" => "index.php?page_id=".$base_page_id."&parent=".$wp_rewrite->preg_index(1),		
			
			$slug. "category/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=category&q_cat=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2),   
			
			$slug. "tag/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=tag&q_tag=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2), 
			
			$slug. "category/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=category&q_cat=".$wp_rewrite->preg_index(1),
			
			$slug. "tag/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=tag&q_tag=".$wp_rewrite->preg_index(1),

			/* question */
			$slug . "([^/]+)/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&question_name=".$wp_rewrite->preg_index(1)."&question_id=".$wp_rewrite->preg_index(2)."&paged=".$wp_rewrite->preg_index(3),
			
			$slug."([^/]+)/([^/]+)/?" => "index.php?page_id=".$base_page_id."&question_name=".$wp_rewrite->preg_index(1)."&question_id=".$wp_rewrite->preg_index(2),
			
			$slug. "page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&paged=".$wp_rewrite->preg_index(1),  
			
			
			$slug. "([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2),
			
			$slug. "user/([^/]+)/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=user&user=". $wp_rewrite->preg_index(1)."&user_page=". $wp_rewrite->preg_index(2)."&paged=".$wp_rewrite->preg_index(3),
			
			$slug. "user/([^/]+)/([^/]+)/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=user&user=". $wp_rewrite->preg_index(1)."&user_page=". $wp_rewrite->preg_index(2)."&message_id=". $wp_rewrite->preg_index(3),
			
			$slug. "user/([^/]+)/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=user&user=". $wp_rewrite->preg_index(1)."&user_page=". $wp_rewrite->preg_index(2),
			
			$slug. "user/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=user&user=".$wp_rewrite->preg_index(1),
			
			$slug. "search/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=search&ap_s=". $wp_rewrite->preg_index(1),			
			
			$slug. "ask/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=ask&parent=".$wp_rewrite->preg_index(1),
			
			$slug. "([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=".$wp_rewrite->preg_index(1),
			
			//"feed/([^/]+)/?" => "index.php?feed=feed&parent=".$wp_rewrite->preg_index(1),
		);  
		$ap_rules = $new_rules;

		return $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;  
	}  



}