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


class anspress {

	/**
	 * Unique identifier
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 */
	protected $plugin_slug = 'anspress';

	/**
	 * Instance of this class.
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	private function __construct() {
	
		remove_action('pre_comment_on_post', 'dsq_pre_comment_on_post');
		// Load plugin text domain
		add_action( 'wp_loaded', array( $this, 'flush_rules' ) );
		
		add_action( 'wp', array( $this, 'remove_head_items' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		add_filter('query_vars', array($this, 'query_var'));
		
		add_action('post_type_link', array( $this, 'answer_link'),10,2);
		add_action('generate_rewrite_rules', array( $this, 'rewrites'), 1);
		
		// Add specific CSS class by filter
		add_filter('body_class', array($this, 'body_class'));
		
		add_action('ap_page_top', array($this, 'check_rewrite_rules'));
		add_action('wp_head', array($this, 'wp_head'));
		
		//update menu url
		add_filter( 'wp_get_nav_menu_items', array($this, 'update_menu_url'));
		
	}
	

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}
	
	public function flush_rules(){

		// Check the option we set on activation.
		if (get_option('ap_flush')) {
			flush_rewrite_rules( );
			delete_option('ap_flush');
		}
	}
	
	public function remove_head_items(){
		if(is_page(ap_opt('base_page'))){
			remove_action('wp_head', 'rsd_link');
			remove_action('wp_head', 'wlwmanifest_link');
			remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
			remove_action('wp_head', 'rel_canonical');
			remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'feed_links', 2 );
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

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}
		delete_option('ap_flush'); 
		flush_rewrite_rules();
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	
	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	
	//add author_more to query vars
	public function query_var( $query_vars) {

		$query_vars[] = 'edit_q';
		$query_vars[] = 'ap_nonce';
		$query_vars[] = 'edit_a';
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
		$query_vars[] = 'qtag';
		$query_vars[] = 'sort';
		$query_vars[] = 'label';
		$query_vars[] = 'user';
		$query_vars[] = 'user_page';
		$query_vars[] = 'ap_s';
		$query_vars[] = 'message_id';
		$query_vars[] = 'parent';
		
		return $query_vars;
	}
	
	/* Answer link */	
	public function answer_link($link, $post) {
		if (isset($post) && $post->post_type== 'answer') {
			$link = get_permalink($post->post_parent) ."#answer_{$post->ID}";
		}
		return $link;
	}

	 
	public function rewrites() {  
		global $wp_rewrite;  
		global $ap_rules;
		
		unset($wp_rewrite->extra_permastructs['question']); 
        unset($wp_rewrite->extra_permastructs['answer']); 
		
		$base_page_id 		= ap_opt('base_page');
		$base_page_slug 	= ap_opt('base_page_slug');
		$q_prefix 			= strlen(ap_opt('question_prefix')) > 0 ? ap_opt('question_prefix').'/' : '';
		
		$slug = ap_base_page_slug();
		
		$question_slug = ap_opt('question_prefix');
		$question_slug = strlen($question_slug) > 0 ? $question_slug.'/' : '';
		
		$new_rules = array(  
			
			$slug. "parent/([^/]+)/?" => "index.php?page_id=".$base_page_id."&parent=".$wp_rewrite->preg_index(1),
			
			/* question */
			$slug. $question_slug . "([^/]+)/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&question_id=".$wp_rewrite->preg_index(1)."&question_name=".$wp_rewrite->preg_index(2)."&paged=".$wp_rewrite->preg_index(3),
			
			$slug. $question_slug ."([^/]+)/([^/]+)/?" => "index.php?page_id=".$base_page_id."&question_id=".$wp_rewrite->preg_index(1)."&question_name=".$wp_rewrite->preg_index(2),
			
			$slug. "category/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&question_category=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2),   
			
			$slug. "tag/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&question_tags=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2), 
			
			$slug. "category/([^/]+)/?" => "index.php?page_id=".$base_page_id."&question_category=".$wp_rewrite->preg_index(1),
			
			$slug. "tag/([^/]+)/?" => "index.php?page_id=".$base_page_id."&question_tags=".$wp_rewrite->preg_index(1),
			
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
	
	public function body_class($classes){
		// add anspress class to body
		if(is_anspress())
			$classes[] = 'anspress';
			
		// return the $classes array
		return $classes;
	}
	
	public function check_rewrite_rules(){
		if(!is_super_admin())
			return;
			
		global $wp_rewrite;
		global $ap_rules;
		
		$show = false;
		
		$rewrite_rules_array = array();
		$rewrite_rules = get_option( 'rewrite_rules' );
		
		if ( !$rewrite_rules )
			$rewrite_rules = array();
		
		if ( !$ap_rules )
			$ap_rules = array();

		$wp_rewrite->rewrite_rules();

		$site_rules 	= array_keys($rewrite_rules);
		$maybe_missing 	= array_keys($ap_rules);
		
		foreach( $maybe_missing as $rule ) {
			if ( !in_array( $rule, $site_rules ) ) {
				$rewrite_rules_array[$rule] = array('source' => 'missing');
			}
		}

		if(count($rewrite_rules_array)> 0)
			$show = true;
			
		if($show)
			echo '<div class="ap-missing-rules">'.__('Rewrite rules are missing', 'ap').'<a href="#" data-args="'.wp_create_nonce('anspress_install').'">'.__('Click here to fix', 'ap').'</a></div>';

	}
	
	function update_menu_url( $items ) {		
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

}
