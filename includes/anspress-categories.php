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

class AP_Categories
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
		//Register question categories
        add_action('init', array($this, 'register_question_categories'), 1);
		add_filter('term_link', array($this, 'custom_category_link'), 10, 3);
    }
	
	public function register_question_categories(){
		if(ap_opt('enable_categories')){
			$categories_labels = array(
				'name' => __('Question Categories', 'ap'),
				'singular_name' => _x('Category', 'ap'),
				'all_items' => __('All Categories', 'ap'),
				'add_new_item' => _x('Add New Category', 'ap'),
				'edit_item' => __('Edit Category', 'ap'),
				'new_item' => __('New Category', 'ap'),
				'view_item' => __('View Category', 'ap'),
				'search_items' => __('Search Category', 'ap'),
				'not_found' => __('Nothing Found', 'ap'),
				'not_found_in_trash' => __('Nothing found in Trash', 'ap'),
				'parent_item_colon' => ''
			);
			register_taxonomy('question_category', array('question'), array(
				'hierarchical' => true,
				'labels' => $categories_labels,
				'rewrite' => false
			));
		}
	}
	
	public function custom_category_link($url, $term, $taxonomy){
		if(ap_opt('enable_categories')){
			/* change category link if permalink not enabled */
			if ( 'question_category' == $term->taxonomy && !get_option('permalink_structure')) {
				return add_query_arg( array('question_category' => false, 'page_id' => ap_opt('base_page'), 'qcat_id' =>$term->term_id), $url );
				
			}elseif('question_category' == $term->taxonomy && get_option('permalink_structure')){
				return ap_get_link_to('category/'.$term->slug);
			}
		}
		return $url;
	}

}

function ap_question_categories_html($post_id = false, $list = true, $label = false){
	if(!ap_opt('enable_categories'))
		return;
	
	if(!$post_id)
		$post_id = get_the_ID();
		
	$cats = get_the_terms( $post_id, 'question_category' );
	
	if($cats){
		if($list){
			$o = '<ul class="question-categories">';
			foreach($cats as $c){
				$o .= '<li><a href="'.esc_url( get_term_link($c)).'" title="'.$c->description.'">'. $c->name .'</a></li>';
			}
			$o .= '</ul>';
			echo $o;
		}else{
			$o = 'Categories:';
			if($label)
				$o = $label;
				
			$o .= ' <span class="question-categories-list">';
			foreach($cats as $c){
				$o .= '<a href="'.esc_url( get_term_link($c)).'" title="'.$c->description.'">'. $c->name .'</a>';
			}
			$o .= '</span>';
			echo $o;
		}
	}

}


function ap_category_details(){
	if(!ap_opt('enable_categories'))
		return;
		
	$var = get_query_var('question_category');

	$category = get_term_by('slug', $var, 'question_category');

	echo '<div class="clearfix">';
	echo '<h3><a href="'.get_category_link( $category ).'">'. $category->name .'</a></h3>';
	echo '<div class="ap-taxo-meta">';
	echo '<span class="count">'. $category->count .' '.__('Questions', 'ap').'</span>';	
	echo '<a class="aicon-rss feed-link" href="' . get_term_feed_link($category->term_id, 'question_category') . '" title="Subscribe to '. $category->name .'" rel="nofollow"></a>';
	echo '</div>';
	echo '</div>';
	
	echo '<p class="desc clearfix">'. $category->description .'</p>';
	
	$child = get_terms( array('taxonomy' => 'question_category'), array( 'parent' => $category->term_id, 'hierarchical' => false, 'hide_empty' => false )); 
				   
	if($child) : 
		echo '<ul class="ap-child-list clearfix">';
			foreach($child as $key => $c) :
				echo '<li><a class="taxo-title" href="'.get_category_link( $c ).'">'.$c->name.'<span>'.$c->count.'</span></a>';
				echo '</li>';
			endforeach;
		echo'</ul>';
	endif;	
}
function ap_child_cat_list($parent){
	$categories = get_terms( array('taxonomy' => 'question_category'), array( 'parent' => $parent, 'hide_empty' => false ));
	
	if($categories){
		echo '<ul class="child clearfix">';	
		foreach	($categories as $cat){
			echo '<li><a href="'.get_category_link( $cat ).'">' .$cat->name.'<span>'.$cat->count.'</span></a></li>';
		}
		echo '</ul>';
	}
}

function ap_question_have_category($post_id = false){
	if(!$post_id)
		$post_id = get_the_ID();
		
	if(!ap_opt('enable_categories'))
		return false;
	
	$categories = wp_get_post_terms( $post_id, 'question_category');
	if(!empty($categories))
		return true;
	
	return false;
}