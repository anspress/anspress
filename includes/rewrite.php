<?php
/**
 * Plugin rewrite rules and query variables
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

/** 
 * This class handle all rewrite rules and define quesry varibale of anspress
 * @since 2.0.0-beta
 */
class AnsPress_Rewrite
{
	/**
	 * Initialize the class
	 */
	public function __construct()
	{
		add_filter('query_vars', array($this, 'query_var'));
		add_filter( 'query_vars', array($this, 'home_query_var'), 9999 );
		add_action('generate_rewrite_rules', array( $this, 'rewrites'), 1);
		add_filter( 'paginate_links', array($this, 'bp_com_paged') );
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
		$query_vars[] = 'ap_s';
		$query_vars[] = 'message_id';
		$query_vars[] = 'parent';

		if(!is_home())
			$query_vars[] = 'ap_sort';
		
		return $query_vars;
	}

	public function home_query_var( $query_vars )
	{
		$key = array_search( 'ap_sort', $query_vars );

	    if( false !== $key ) {

	        unset( $query_vars[ $key ] );

	    }

	    return $query_vars;
	}

	/**
	 * Rewrite rules
	 * @return array
	 */
	public function rewrites() 
	{  
		global $wp_rewrite;  
		global $ap_rules;
		
		unset($wp_rewrite->extra_permastructs['question']); 
        unset($wp_rewrite->extra_permastructs['answer']); 
		
		$base_page_id 		= ap_opt('base_page');

		$base_page = get_post($base_page_id);
		
		$slug = ap_base_page_slug().'/';

		$new_rules = array(  
			
			//$slug. "parent/([^/]+)/?" => "index.php?page_id=".$base_page_id."&parent=".$wp_rewrite->preg_index(1),		
 
			$slug. "category/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=category&q_cat=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2),   
			
			$slug. "tag/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=tag&q_tag=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2), 
			
			$slug. "category/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=category&q_cat=".$wp_rewrite->preg_index(1),
			
			$slug. "tag/([^/]+)/?" => "index.php?page_id=".$base_page_id."&ap_page=tag&q_tag=".$wp_rewrite->preg_index(1),

			$slug. "page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&paged=".$wp_rewrite->preg_index(1), 

			/* question */
			$slug . "([^/]+)/([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&question_name=".$wp_rewrite->preg_index(1)."&question_id=".$wp_rewrite->preg_index(2)."&paged=".$wp_rewrite->preg_index(3),
			
			$slug."([^/]+)/([^/]+)/?" => "index.php?page_id=".$base_page_id."&question_name=".$wp_rewrite->preg_index(1)."&question_id=".$wp_rewrite->preg_index(2),
			
			$slug. "([^/]+)/page/?([0-9]{1,})/?$" => "index.php?page_id=".$base_page_id."&ap_page=".$wp_rewrite->preg_index(1)."&paged=".$wp_rewrite->preg_index(2),
			
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

	public function bp_com_paged($args)
	{
		if(function_exists('bp_current_component')){
			$bp_com = bp_current_component();
			
			if('questions' == $bp_com || 'answers' == $bp_com)
				return preg_replace('/page.([0-9]+)./', '?paged=$1', $args);
		}

		return $args;
	}
}