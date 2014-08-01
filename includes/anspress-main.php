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

		// Load plugin text domain
		add_action( 'wp_loaded', array( $this, 'flush_rules' ) );
		add_action( 'after_setup_theme', array( $this, 'load_plugin_textdomain' ) );
		
		add_action( 'wp', array( $this, 'remove_head_items' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		add_filter('query_vars', array($this, 'query_var'));
		
		add_action('post_type_link', array( $this, 'answer_link'),10,2);
		add_action('generate_rewrite_rules', array( $this, 'rewrites'));
		
		// Add specific CSS class by filter
		add_filter('body_class', array($this, 'body_class'));
		
		
		
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
			flush_rewrite_rules( false );
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

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain('ap', false, dirname(plugin_basename(__FILE__)) . '/languages/');
	}
	
	//add author_more to query vars
	public function query_var( $query_vars) {

		$query_vars[] = 'edit_q';
		$query_vars[] = 'ap_nonce';
		$query_vars[] = 'edit_a';
		$query_vars[] = 'question_id';
		$query_vars[] = 'question';
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
		
		unset($wp_rewrite->extra_permastructs['question']); 
        unset($wp_rewrite->extra_permastructs['answer']); 
		
		$base_page_id = ap_opt('base_page');
		$base_page_slug = ap_opt('base_page_slug');

		
		$slug = ap_base_page_slug();
		$new_rules = array(  
			$slug. "question/([^/]+)/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&question=".$wp_rewrite->preg_index(1)."&question_id=".$wp_rewrite->preg_index(2)."&paged=".$wp_rewrite->preg_index(3),
			
			$slug. "question/([^/]+)/([^/]+)/?" => "index.php?page_id=".$base_page_id."&question=".$wp_rewrite->preg_index(1)."&question_id=".$wp_rewrite->preg_index(2),			
			
			$slug. "category/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&question_category=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2),   
			
			$slug. "tag/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&question_tags=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2), 
			
			$slug. "category/([^/]+)/?" => "index.php?page_id=".$base_page_id."&question_category=".$wp_rewrite->preg_index(1),
			$slug. "tag/([^/]+)/?" => "index.php?page_id=".$base_page_id."&question_tags=".$wp_rewrite->preg_index(1),
			
			$slug. "page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&paged=".$wp_rewrite->preg_index(1),  
			
			
			$slug. "([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2),  
			
			$slug. "([^/]+)/([^/]+)/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=".$wp_rewrite->preg_index(1)."&user=". $wp_rewrite->preg_index(2)."&user_page=". $wp_rewrite->preg_index(3),
			
			$slug. "([^/]+)/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=".$wp_rewrite->preg_index(1)."&user=".$wp_rewrite->preg_index(2),			
			
			$slug. "([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=".$wp_rewrite->preg_index(1),			

		);  
		return $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;  
	}  
	
	public function body_class($classes){
		// add anspress class to body
		$classes[] = 'anspress';
		// return the $classes array
		return $classes;
	}

}
