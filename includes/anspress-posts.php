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

class anspress_posts
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
		//Register Custom Post types and taxonomy
        add_action('init', array($this, 'create_cpt_tax'), 0);
		
		// custom columns in CPT question
        add_filter('manage_edit-question_columns', array( $this, 'cpt_question_columns'));
		
		// custom columns in CPT answer
        add_filter('manage_edit-answer_columns', array($this,'cpt_answer_columns'));
		
		// custom columns data
        add_action('manage_posts_custom_column', array($this, 'custom_columns_value'));
		
		// Sortable question CPT columns
        add_filter('manage_edit-question_sortable_columns', array($this, 'admin_column_sort_flag'));
		
		// Sortable answer CPT columns
        add_filter('manage_edit-answer_sortable_columns', array($this, 'admin_column_sort_flag'));
		
		// Sortable flag columns
        add_action('pre_get_posts', array( $this, 'admin_column_sort_flag_by'));
		
        add_action('manage_answer_posts_custom_column', array($this, 'answer_row_actions'), 10, 2);
		
        add_filter('wp_insert_post_data', array($this, 'post_data_check'), 99);
        add_filter('post_updated_messages', array($this,'post_custom_message'));
		
		add_action('post_type_link',array($this, 'ans_post_type_link'),10,2);
		
		add_action( 'admin_init', array( $this, 'init_actions' ) ); 
		add_action( 'init', array($this, 'ap_make_post_parent_public') );
		add_action( 'save_post', array($this, 'ans_parent_post'), 0, 2 );	
		add_action('wp_ajax_search_questions', array($this, 'suggest_questions'));
		add_filter( 'post_type_link', array($this, 'custom_question_link'), 10, 2 );
		add_filter('term_link', array($this, 'custom_category_link'), 10, 3);
		add_filter('term_link', array($this, 'custom_tags_link'), 10, 3);
		add_filter('get_pagenum_link', array($this, 'custom_page_link'));

    }
	public function init_actions(){
		add_meta_box( 'ap_ans_parent_q','Parent Question', array($this, 'ans_parent_q_metabox'),'answer','side', 'high' );
			
		
		add_action('wp_trash_post', array($this, 'trash_ans_on_question_trash'));
		add_action('untrash_post', array($this, 'untrash_ans_on_question_untrash'));
		add_action('delete_post', array($this, 'delete_ans_on_question_delete'));		

	}
    // Register Custom Post Type    
    public function create_cpt_tax()
    {
		// get the base page object
        $base_page_slug = ap_opt('base_page_slug');
		
		// get the base slug, if base page was set to home page then dont use any slug
        $slug = (ap_opt('base_page') !== get_option('page_on_front')) ? $base_page_slug.'/' : '';
        
		// question CPT labels
        $labels = array(
            'name' 				=> _x('Questions', 'Post Type General Name', 'anspress'),
            'singular_name' 	=> _x('Question', 'Post Type Singular Name', 'anspress'),
            'menu_name' 		=> __('Questions', 'anspress'),
            'parent_item_colon' => __('Parent Question:', 'anspress'),
            'all_items' 		=> __('All Questions', 'anspress'),
            'view_item' 		=> __('View Question', 'anspress'),
            'add_new_item' 		=> __('Add New Question', 'anspress'),
            'add_new' 			=> __('New Question', 'anspress'),
            'edit_item' 		=> __('Edit Question', 'anspress'),
            'update_item' 		=> __('Update Question', 'anspress'),
            'search_items' 		=> __('Search question', 'anspress'),
            'not_found' 		=> __('No question found', 'anspress'),
            'not_found_in_trash' => __('No questions found in Trash', 'anspress')
        );
		
		// question CPT arguments
        $args   = array(
            'label' => __('question', 'anspress'),
            'description' => __('Question', 'anspress'),
            'labels' => $labels,
            'supports' => array(
                'title',
                'editor',
                'author',
                'comments',
                'trackbacks',
                'revisions',
                'custom-fields'
            ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'menu_icon' => ANSPRESS_URL . '/assets/question.png',
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => false,
            'query_var' => 'apq',
            'capability_type' => 'post',
            'rewrite' => false
        );
		// register CPT question
        register_post_type('question', $args);
        
		// Answer CPT labels
        $ans_labels = array(
            'name' 			=> _x('Answers', 'Post Type General Name', 'anspress'),
            'singular_name' => _x('Answer', 'Post Type Singular Name', 'anspress'),
            'menu_name' => __('Answers', 'anspress'),
            'parent_item_colon' => __('Parent Answer:', 'anspress'),
            'all_items' => __('All Answers', 'anspress'),
            'view_item' => __('View Answer', 'anspress'),
            'add_new_item' => __('Add New Answer', 'anspress'),
            'add_new' => __('New answer', 'anspress'),
            'edit_item' => __('Edit answer', 'anspress'),
            'update_item' => __('Update answer', 'anspress'),
            'search_items' => __('Search answer', 'anspress'),
            'not_found' => __('No answer found', 'anspress'),
            'not_found_in_trash' => __('No answer found in Trash', 'anspress')
        );
		
		// Answers CPT arguments
        $ans_args   = array(
            'label' => __('answer', 'anspress'),
            'description' => __('Answer', 'anspress'),
            'labels' => $ans_labels,
            'supports' => array(
                'editor',
                'author',
                'comments',
                'revisions',
                'custom-fields'
            ),
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => false,
            'menu_icon' => ANSPRESS_URL . '/assets/answer.png',
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'post'
        );
		// register CPT answer
        register_post_type('answer', $ans_args);
        
        $categories_labels = array(
            'name' => __('Question Categories', 'anspress'),
            'singular_name' => _x('Category', 'anspress'),
            'all_items' => __('All Categories', 'anspress'),
            'add_new_item' => _x('Add New Category', 'anspress'),
            'edit_item' => __('Edit Category', 'anspress'),
            'new_item' => __('New Category', 'anspress'),
            'view_item' => __('View Category', 'anspress'),
            'search_items' => __('Search Category', 'anspress'),
            'not_found' => __('Nothing Found', 'anspress'),
            'not_found_in_trash' => __('Nothing found in Trash', 'anspress'),
            'parent_item_colon' => ''
        );
        $tags_labels       = array(
            'name' => __('Question Tags', 'anspress'),
            'singular_name' => _x('Tag', 'anspress'),
            'all_items' => __('All Tags', 'anspress'),
            'add_new_item' => _x('Add New Tag', 'anspress'),
            'edit_item' => __('Edit Tag', 'anspress'),
            'new_item' => __('New Tag', 'anspress'),
            'view_item' => __('View Tag', 'anspress'),
            'search_items' => __('Search Tag', 'anspress'),
            'not_found' => __('Nothing Found', 'anspress'),
            'not_found_in_trash' => __('Nothing found in Trash', 'anspress'),
            'parent_item_colon' => ''
        );
        
        register_taxonomy('question_category', array('question'), array(
            'hierarchical' => true,
            'labels' => $categories_labels,
            'rewrite' => array(
                'slug' => $slug . 'category',
                'with_front' => true
            )
        ));
        register_taxonomy('question_tags', array('question'), array(
            'hierarchical' => false,
            'labels' => $tags_labels,
            'rewrite' => array(
                'slug' => $slug . 'tag',
                'with_front' => true
            )
        ));
		 // Check the option we set on activation.
		if (get_option('ap_flush') == 'true') {
			flush_rewrite_rules();
			delete_option('ap_flush');
		}
        
    }
    
    // custom columns in CPT question
    public function cpt_question_columns($columns)
    {
        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "asker" => __('Asker', 'anspress'),
            "status" => __('Status', 'anspress'),
            "title" => __('Title', 'anspress'),
            "question_category" => __('Category', 'anspress'),
            "question_tags" => __('Tags', 'anspress'),
            "answers" => __('Ans', 'anspress'),
            "comments" => __('Comments', 'anspress'),
            "vote" => __('Vote', 'anspress'),
            "flag" => __('Flag', 'anspress'),
            "date" => __('Date', 'anspress')
        );
        return $columns;
    }
    
    // custom columns in CPT answer
    public function cpt_answer_columns($columns)
    {
        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "answerer" => __('Answerer', 'anspress'),
            "parent_question" => __('Question', 'anspress'),
            "answer_content" => __('Content', 'anspress'),
            "comments" => __('Comments', 'anspress'),
            "vote" => __('Vote', 'anspress'),
            "flag" => __('Flag', 'anspress'),
            "date" => __('Date', 'anspress')
        );
        return $columns;
    }
    
    public function custom_columns_value($column)
    {
        global $post;
        if ('asker' == $column || 'answerer' == $column) {
            echo get_avatar(get_the_author_meta('user_email'), 40);
        } elseif (ANSPRESS_CAT_TAX == $column) {
            /* Get the genres for the post. */
            $category = get_the_terms($post->ID, ANSPRESS_CAT_TAX);
            
            /* If terms were found. */
            if (!empty($category)) {
                $out = array();
                
                /* Loop through each term, linking to the 'edit posts' page for the specific term. */
                foreach ($category as $cat) {
                    $out[] = edit_term_link($cat->name, '', '', $cat, false);
                }
                /* Join the terms, separating them with a comma. */
                echo join(', ', $out);
            }
            
            /* If no terms were found, output a default message. */
            else {
                _e('--');
            }
        } elseif (ANSPRESS_TAG_TAX == $column) {
            /* Get the genres for the post. */
            $terms = get_the_terms($post->ID, ANSPRESS_TAG_TAX);
            
            /* If terms were found. */
            if (!empty($terms)) {
                $out = array();
                
                /* Loop through each term, linking to the 'edit posts' page for the specific term. */
                foreach ($terms as $term) {
                    $out[] = sprintf('<a href="%s">%s</a>', esc_url(add_query_arg(array(
                        'post_type' => $post->post_type,
                        ANSPRESS_TAG_TAX => $term->slug
                    ), 'edit.php')), esc_html(sanitize_term_field('name', $term->name, $term->term_id, ANSPRESS_TAG_TAX, 'display')));
                }
                /* Join the terms, separating them with a comma. */
                echo join(', ', $out);
            }
            
            /* If no terms were found, output a default message. */
            else {
                _e('No Tags');
            }
        } elseif ('answers' == $column) {
            /* Get the genres for the post. */
            $an_count_args = array(
                'post_type' => 'answer',
                'post_status' => 'publish',
                'post_parent' => $post->ID,
                'showposts' => -1,
            );
            
            $a_count = count(get_posts($an_count_args));
            
            /* If terms were found. */
            if (!empty($a_count)) {
                
                echo '<a class="ans-count" title="' . $a_count . __('answers', 'anspress') . '" href="' . esc_url(add_query_arg(array(
                    'post_type' => 'answer',
                    'post_parent' => $post->ID
                ), 'edit.php')) . '">' . $a_count . '</a>';
            }
            
            /* If no terms were found, output a default message. */
            else {
                echo '<a class="ans-count" title="0' . __('answers', 'anspress') . '">0</a>';
            }
        } elseif ('parent_question' == $column) {
            echo '<a class="parent_question" href="' . esc_url(add_query_arg(array(
                'post' => $post->post_parent,
                'action' => 'edit'
            ), 'post.php')) . '"><strong>' . get_the_title($post->post_parent) . '</strong></a>';
        } elseif ('status' == $column) {
            echo '<span class="question-status ' . ap_get_question_status() . '">' . ap_get_question_status() . '</span>';
        } elseif ('vote' == $column) {
            echo '<span class="vote-count' . ($post->flag ? ' zero' : '') . '">' . $post->net_vote . '</span>';
        } elseif ('flag' == $column) {
            echo '<span class="flag-count' . ($post->flag ? ' flagged' : '') . '">' . $post->flag . '</span>';
        }
    }
    
    //make flag sortable
    public function admin_column_sort_flag($columns)
    {
        $columns['flag'] = 'flag';
        return $columns;
    }
    
    public function admin_column_sort_flag_by($query)
    {
        if (!is_admin())
            return;
        
        $orderby = $query->get('orderby');
        
        if ('flag' == $orderby) {
            $query->set('meta_key', ANSPRESS_FLAG_META);
            $query->set('orderby', 'meta_value_num');
        }
    }
    
    
   
    
    public function answer_row_actions($column, $post_id)
    {
        global $post, $mode;
        
        if ('answer_content' != $column)
            return;
        
        $content = get_the_excerpt();
        // get the first 80 words from the content and added to the $abstract variable
        preg_match('/^([^.!?\s]*[\.!?\s]+){0,40}/', strip_tags($content), $abstract);
        // pregmatch will return an array and the first 80 chars will be in the first element 
        echo $abstract[0] . '...';
        
        //First set up some variables
        $actions          = array();
        $post_type_object = get_post_type_object($post->post_type);
        $can_edit_post    = current_user_can($post_type_object->cap->edit_post, $post->ID);
        
        //Actions to delete/trash
        if (current_user_can($post_type_object->cap->delete_post, $post->ID)) {
            if ('trash' == $post->post_status) {
                $_wpnonce           = wp_create_nonce('untrash-post_' . $post_id);
                $url                = admin_url('post.php?post=' . $post_id . '&action=untrash&_wpnonce=' . $_wpnonce);
                $actions['untrash'] = "<a title='" . esc_attr(__('Restore this item from the Trash')) . "' href='" . $url . "'>" . __('Restore') . "</a>";
                
            } elseif (EMPTY_TRASH_DAYS) {
                $actions['trash'] = "<a class='submitdelete' title='" . esc_attr(__('Move this item to the Trash')) . "' href='" . get_delete_post_link($post->ID) . "'>" . __('Trash') . "</a>";
            }
            if ('trash' == $post->post_status || !EMPTY_TRASH_DAYS)
                $actions['delete'] = "<a class='submitdelete' title='" . esc_attr(__('Delete this item permanently')) . "' href='" . get_delete_post_link($post->ID, '', true) . "'>" . __('Delete Permanently') . "</a>";
        }
        if ($can_edit_post)
			$actions['edit'] = '<a href="' . get_edit_post_link($post->ID, '', true) . '" title="' . esc_attr(sprintf(__('Preview &#8220;%s&#8221;'),$post->title)) . '" rel="permalink">' . __('Edit') . '</a>';
			
        //Actions to view/preview
        if (in_array($post->post_status, array(
            'pending',
            'draft',
            'future'
        ))) {
            if ($can_edit_post)
                $actions['view'] = '<a href="' . esc_url(add_query_arg('preview', 'true', get_permalink($post->ID))) . '" title="' . esc_attr(sprintf(__('Preview &#8220;%s&#8221;'),$post->title)) . '" rel="permalink">' . __('Preview') . '</a>';
            
        } elseif ('trash' != $post->post_status) {
            $actions['view'] = '<a href="' . get_permalink($post->ID) . '" title="' . esc_attr(__('View &#8220;%s&#8221; question')) . '" rel="permalink">' . __('View') . '</a>';
        }
        
        //***** END  -- Our actions  *******//
        
        //Echo the 'actions' HTML, let WP_List_Table do the hard work
		$WP_List_Table = new WP_List_Table();
        echo $WP_List_Table->row_actions($actions);
    }
    
    public function post_data_check($data)
    {
        global $pagenow;
        if ($pagenow == 'post.php' && $data['post_type'] == 'answer') {
            $parent_q = isset($_REQUEST['ap_q']) ? $_REQUEST['ap_q'] : $data['post_parent'];
            if (!isset($parent_q) || $parent_q == '0' || $parent_q == '') {
                add_filter('redirect_post_location', array(
                    $this,
                    'custom_post_location'
                ), 99);
                return;
            }
        }
        
        return $data;
    }
    
    public function custom_post_location($location)
    {
        remove_filter('redirect_post_location', __FUNCTION__, 99);
        $location = add_query_arg('message', 99, $location);
        return $location;
    }
    
    public function post_custom_message($messages)
    {
        global $post;
        
        if ($post->post_type == 'answer' && isset($_REQUEST['message']) && $_REQUEST['message'] == 99)
            add_action('admin_notices', array(
                $this,
                'ans_notice'
            ));
        
        return $messages;
    }
    
    public function ans_notice()
    {
        echo '<div class="error">
           <p>' . __('Please fill parent question field, Answer was not saved!', 'anspress') . '</p>
        </div>';
    }
	
	public function ans_parent_q_metabox( $answer ) {
		echo '<input type="hidden" name="ap_ans_noncename" id="ap_ans_noncename" value="' .wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
		echo '<input type="hidden" name="ap_q" id="ap_q" value="'.$answer->post_parent.'" />';
		echo '<input type="text" name="ap_q_search" id="ap_q_search" value="'.get_the_title($answer->post_parent).'" />';
	}
	
	// set question for the answer
	public function ans_parent_post( $post_id, $post ) {
		
		if ( !isset($_POST['ap_ans_noncename']) || !wp_verify_nonce( $_POST['ap_ans_noncename'], plugin_basename(__FILE__) )) {
			return $post->ID;
		}
		if ( !current_user_can( 'edit_post', $post->ID ))
			return $post->ID;
		
		// return on autosave
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
		
		if ( $post->post_type == 'answer' ) {
			$parent_q = sanitize_text_field($_POST['ap_q']);
			if( !isset( $parent_q ) || $parent_q == '0' || $parent_q =='' ){
				return $post->ID;
			}else{
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $parent_q ), array( 'ID' => $post->ID ) );
			}
			
		}
	}
	// make post_parent public for admin_init
	public function ap_make_post_parent_public() {
		if ( is_admin() )
			$GLOBALS['wp']->add_query_var( 'post_parent' );
	}
	
	 
	//if a question is sent to trash, send all ans as well
	public function trash_ans_on_question_trash ($post_id) {
		$post_type = get_post_type( $post_id );
		if( $post_type == 'question') {
			$arg = array(
			  'post_type' => 'answer',
			  'post_status' => 'publish',
			  'post_parent' => $post_id,
			  'showposts' => -1,
			  'caller_get_posts'=> 1
			);
			$ans = get_posts($arg);
			if($ans>0){
				foreach( $ans as $p)
					wp_trash_post($p->ID);
			}
		}
	}

	//if questions was restored then restore its answers as well	
	public function untrash_ans_on_question_untrash ($post_id) {
		$post_type = get_post_type( $post_id );
		if( $post_type == 'question') {
			$arg = array(
			  'post_type' => 'answer',
			  'post_status' => 'trash',
			  'post_parent' => $post_id,
			  'showposts' => -1,
			  'caller_get_posts'=> 1
			);
			$ans = get_posts($arg);
			if($ans>0){
				foreach( $ans as $p)
					wp_untrash_post($p->ID);
			}
		}
	}

	//if questions was deleted then delete its answers as well	
	public function delete_ans_on_question_delete ($post_id) {
		$post_type = get_post_type( $post_id );
		if( $post_type == 'question') {
			$arg = array(
			  'post_type' => 'answer',
			  'post_status' => 'trash',
			  'post_parent' => $post_id,
			  'showposts' => -1,
			  'caller_get_posts'=> 1
			);
			$ans = get_posts($arg);
			if($ans>0){
				foreach( $ans as $p)
					wp_delete_post($p->ID);
			}
		}
	}
	public function ans_post_type_link($link,$post) {
	  $post_type = 'answer';
	  if ($post->post_type==$post_type) {
		$post_data = get_post($post->post_parent);
		$link = get_post_type_archive_link('question').$post_data->post_name ."#answer_{$post->ID}";
	  }
	  return $link;
	}	
	function suggest_questions() {
		// Query for suggestions  
		$posts = get_posts( array(  
			's' =>$_REQUEST['term'],  
			'post_type'=> 'question'
		) );  
	  
		// Initialise suggestions array  
		$suggestions=array();  
	   // global $post;  
		foreach ($posts as $post): setup_postdata($post);  
			// Initialise suggestion array  
			$suggestion = array();  
			$suggestion['label'] = esc_html($post->post_title);  
			$suggestion['id'] = $post->ID;  
	  
			// Add suggestion to suggestions array  
			$suggestions[]= $suggestion;  
		endforeach;  
	 
		// JSON encode and echo  
		$response = $_GET["callback"] . "(" . json_encode($suggestions) . ")";  
		echo $response;  
	  
		// Don't forget to exit!  
		exit;  
	}

	public function custom_question_link( $url, $post ) {
		if ( 'question' == get_post_type( $post ) ) {
			if(get_option('permalink_structure'))
				return str_replace('?apq=', ap_base_page_slug() .'question/'.$post->ID.'/', $url); 
			else
				return add_query_arg( array('apq' => false, 'page_id' => ap_opt('base_page'), 'question_id' =>$post->ID), $url );
		}
		return $url;
	}
	
	public function custom_category_link($url, $term, $taxonomy){
		
	    if ( 'question_category' == $term->taxonomy ) {
			if(get_option('permalink_structure'))
				return add_query_arg( array('question_category' => false, 'page_id' => ap_opt('base_page'), 'qcat_id' =>$term->term_id), $url );
			else
				return add_query_arg( array('question_category' => false, 'page_id' => ap_opt('base_page'), 'qcat_id' =>$term->term_id), $url );
		}
		return $url;
	}
	
	public function custom_tags_link($url, $term, $taxonomy){
	    if ( 'question_tags' == $term->taxonomy ) {
			return add_query_arg( array('question_tags' => false, 'p' => ap_opt('base_page'), 'qtag_id' =>$term->term_id), $url );
		}
		return $url;
	}
	
	public function custom_page_link( $result ){
		//print_r($result);
		if(ap_opt('base_page') == get_option('page_on_front'))
			$result = str_replace('?paged', '?page_id='.ap_opt('base_page').'&paged', $result);
		return $result ;
	}
	
	
}
