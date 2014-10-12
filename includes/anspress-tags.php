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

class AP_Tags
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
		//Register question tags
        add_action('init', array($this, 'register_question_tags'), 1);
		add_filter('term_link', array($this, 'custom_tags_link'), 10, 3);
    }
	
	public function register_question_tags(){
		if(ap_opt('enable_tags')){
			$tags_labels       = array(
				'name' => __('Question Tags', 'ap'),
				'singular_name' => _x('Tag', 'ap'),
				'all_items' => __('All Tags', 'ap'),
				'add_new_item' => _x('Add New Tag', 'ap'),
				'edit_item' => __('Edit Tag', 'ap'),
				'new_item' => __('New Tag', 'ap'),
				'view_item' => __('View Tag', 'ap'),
				'search_items' => __('Search Tag', 'ap'),
				'not_found' => __('Nothing Found', 'ap'),
				'not_found_in_trash' => __('Nothing found in Trash', 'ap'),
				'parent_item_colon' => ''
			);
			register_taxonomy('question_tags', array('question'), array(
				'hierarchical' => false,
				'labels' => $tags_labels,
				'rewrite' => false
			));
		}
	}
	
	public function custom_tags_link($url, $term, $taxonomy){
		if(ap_opt('enable_tags')){
			/* change tags link if permalink not enabled */
			if ( 'question_tags' == $term->taxonomy && !get_option('permalink_structure')) {
				return add_query_arg( array('question_tags' => false, 'p' => ap_opt('base_page'), 'qtag_id' =>$term->term_id), $url );
			}elseif('question_tags' == $term->taxonomy && get_option('permalink_structure')){
				return ap_get_link_to('tag/'.$term->slug);
			}
		}
		return $url;
	}

}


function ap_question_tags_html($post_id = false, $list = true){
	/* return if tags is disabled */
	if(!ap_opt('enable_tags'))
		return;
		
	if(!$post_id)
		$post_id = get_the_ID();
		
	$tags = get_the_terms( $post_id, 'question_tags' );
	
	if($tags){
		if($list){
			$o = '<ul class="question-tags">';
			foreach($tags as $t){
				$o .= '<li><a href="'.esc_url( get_term_link($t)).'" title="'.$t->description.'">'. $t->name .'</a> &times; '.$t->count.'</li>';
			}
			$o .= '</ul>';
			echo $o;
		}else{
			$o = 'Tags:';
			$o .= ' <span class="question-tags-list">';
			foreach($tags as $t){
				$o .= '<a href="'.esc_url( get_term_link($t)).'" title="'.$t->description.'">'. $t->name .'</a><span>,</span>';
			}
			$o .= '</span>';
			echo $o;
		}
	}
}


function ap_tag_details(){
	/* return if tags is disabled */
	if(!ap_opt('enable_tags'))
		return;
		
	$var = get_query_var('question_tags');

	$tag = get_term_by('slug', $var, 'question_tags');
	echo '<div class="clearfix">';
	echo '<h3><a href="'.get_category_link( $tag ).'">'. $tag->name .'</a></h3>';
	echo '<div class="ap-taxo-meta">';
	echo '<span class="count">'. $tag->count .' '.__('Questions', 'ap').'</span>';	
	echo '<a class="aicon-rss feed-link" href="' . get_term_feed_link($tag->term_id, 'question_category') . '" title="Subscribe to '. $tag->name .'" rel="nofollow"></a>';
	echo '</div>';
	echo '</div>';
	
	echo '<p class="desc clearfix">'. $tag->description .'</p>';
}

function ap_question_have_tags($post_id = false){
	if(!$post_id)
		$post_id = get_the_ID();
		
	if(!ap_opt('enable_tags'))
		return false;
	
	$tags = wp_get_post_terms( $post_id, 'question_tags');
	
	if(!empty($tags))
		return true;
	
	return false;
}
