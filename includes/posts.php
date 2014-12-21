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
		
		//add_action('post_type_link',array($this, 'ans_post_type_link'),10,2);
		
		add_action( 'admin_init', array( $this, 'init_actions' ) ); 
		add_action( 'init', array($this, 'ap_make_post_parent_public') );
		add_action( 'save_post', array($this, 'ans_parent_post'), 0, 2 );	
		add_action( 'save_post', array($this, 'action_on_new_post'), 0, 2 );	
		add_action('wp_ajax_search_questions', array($this, 'suggest_questions'));
		add_filter( 'post_type_link', array($this, 'custom_question_link'), 10, 2 );		
		add_filter('get_pagenum_link', array($this, 'custom_page_link'));
		
		add_action( 'posts_clauses', array($this, 'answer_sort_newest'), 10, 2 );
		add_action( 'posts_clauses', array($this, 'user_favorites'), 10, 2 );
		add_action('admin_footer-post.php', array($this, 'append_post_status_list'));
		
		add_action( 'posts_clauses', array($this, 'main_question_query'), 10, 2 );

    }
	public function init_actions(){
		add_meta_box( 'ap_ans_parent_q','Parent Question', array($this, 'ans_parent_q_metabox'),'answer','side', 'high' );
		add_action('wp_trash_post', array($this, 'trash_post_action'));
		add_action('untrash_post', array($this, 'untrash_ans_on_question_untrash'));
		//add_action('delete_post', array($this, 'delete_action'));		
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
            'name' 				=> _x('Questions', 'Post Type General Name', 'ap'),
            'singular_name' 	=> _x('Question', 'Post Type Singular Name', 'ap'),
            'menu_name' 		=> __('Questions', 'ap'),
            'parent_item_colon' => __('Parent Question:', 'ap'),
            'all_items' 		=> __('All Questions', 'ap'),
            'view_item' 		=> __('View Question', 'ap'),
            'add_new_item' 		=> __('Add New Question', 'ap'),
            'add_new' 			=> __('New Question', 'ap'),
            'edit_item' 		=> __('Edit Question', 'ap'),
            'update_item' 		=> __('Update Question', 'ap'),
            'search_items' 		=> __('Search question', 'ap'),
            'not_found' 		=> __('No question found', 'ap'),
            'not_found_in_trash' => __('No questions found in Trash', 'ap')
        );
		
		// question CPT arguments
        $args   = array(
            'label' => __('question', 'ap'),
            'description' => __('Question', 'ap'),
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
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => true,
            'menu_icon' => ANSPRESS_URL . '/assets/question.png',
			//'show_in_menu' => 'anspress',
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'query_var' => 'apq',
            'capability_type' => 'post',
            'rewrite' => false
        );
		// register CPT question
        register_post_type('question', $args);
        
		// Answer CPT labels
        $ans_labels = array(
            'name' 			=> _x('Answers', 'Post Type General Name', 'ap'),
            'singular_name' => _x('Answer', 'Post Type Singular Name', 'ap'),
            'menu_name' => __('Answers', 'ap'),
            'parent_item_colon' => __('Parent Answer:', 'ap'),
            'all_items' => __('All Answers', 'ap'),
            'view_item' => __('View Answer', 'ap'),
            'add_new_item' => __('Add New Answer', 'ap'),
            'add_new' => __('New answer', 'ap'),
            'edit_item' => __('Edit answer', 'ap'),
            'update_item' => __('Update answer', 'ap'),
            'search_items' => __('Search answer', 'ap'),
            'not_found' => __('No answer found', 'ap'),
            'not_found_in_trash' => __('No answer found in Trash', 'ap')
        );
		
		// Answers CPT arguments
        $ans_args   = array(
            'label' => __('answer', 'ap'),
            'description' => __('Answer', 'ap'),
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
            'show_in_menu' => false,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'menu_icon' => ANSPRESS_URL . '/assets/answer.png',
			//'show_in_menu' => 'anspress',
            'can_export' => true,
            'has_archive' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'capability_type' => 'post',
			'rewrite' => false
        );
		// register CPT answer
        register_post_type('answer', $ans_args);
		
		register_post_status( 'closed', array(
			  'label'                     => __( 'Closed', 'ap' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>' )
		 ) );
		 
		 register_post_status( 'moderate', array(
			  'label'                     => __( 'Moderate', 'ap' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Moderate <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>' )
		 ) );
		 
		 register_post_status( 'private_question', array(
			  'label'                     => __( 'Private Question', 'ap' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Private Question <span class="count">(%s)</span>', 'Private Question <span class="count">(%s)</span>' )
		 ) );
        
    }
    
    // custom columns in CPT question
    public function cpt_question_columns($columns)
    {
        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "asker" => __('Asker', 'ap'),
            "status" => __('Status', 'ap'),
            "title" => __('Title', 'ap'),
            "question_category" => __('Category', 'ap'),
            "question_tags" => __('Tags', 'ap'),
            "answers" => __('Ans', 'ap'),
            "comments" => __('Comments', 'ap'),
            "vote" => __('Vote', 'ap'),
            "flag" => __('Flag', 'ap'),
            "date" => __('Date', 'ap')
        );
        return $columns;
    }
    
    // custom columns in CPT answer
    public function cpt_answer_columns($columns)
    {
        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "answerer" => __('Answerer', 'ap'),
            "parent_question" => __('Question', 'ap'),
            "answer_content" => __('Content', 'ap'),
            "comments" => __('Comments', 'ap'),
            "vote" => __('Vote', 'ap'),
            "flag" => __('Flag', 'ap'),
            "date" => __('Date', 'ap')
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
                
                echo '<a class="ans-count" title="' . $a_count . __('answers', 'ap') . '" href="' . esc_url(add_query_arg(array(
                    'post_type' => 'answer',
                    'post_parent' => $post->ID
                ), 'edit.php')) . '">' . $a_count . '</a>';
            }
            
            /* If no terms were found, output a default message. */
            else {
                echo '<a class="ans-count" title="0' . __('answers', 'ap') . '">0</a>';
            }
        } elseif ('parent_question' == $column) {
            echo '<a class="parent_question" href="' . esc_url(add_query_arg(array(
                'post' => $post->post_parent,
                'action' => 'edit'
            ), 'post.php')) . '"><strong>' . get_the_title($post->post_parent) . '</strong></a>';
        } elseif ('status' == $column) {
            echo '<span class="question-status">' . ap_get_question_label() . '</span>';
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
           <p>' . __('Please fill parent question field, Answer was not saved!', 'ap') . '</p>
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
	
	public function action_on_new_post( $post_id, $post ) {
		// return on autosave
		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
		
		if ( wp_is_post_revision( $post_id ) )
			return;
		
		/* if ( !current_user_can( 'edit_post', $post->ID ))
			return $post->ID; */
		
		if ( $post->post_type == 'question' ) {
			//check if post have updated meta, if not this is a new post :D
			$updated = get_post_meta($post_id, ANSPRESS_UPDATED_META, true);
			if($updated == '')
				do_action('ap_new_question', $post_id, $post);
		}elseif ( $post->post_type == 'answer' ) {
			$updated = get_post_meta($post_id, ANSPRESS_UPDATED_META, true);
			if($updated == '')
				do_action('ap_new_answer', $post_id, $post);
		}
	}
	// make post_parent public for admin_init
	public function ap_make_post_parent_public() {
		if ( is_admin() )
			$GLOBALS['wp']->add_query_var( 'post_parent' );
	}
	
	 
	//if a question is sent to trash, send all ans as well
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
			  'caller_get_posts'=> 1
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
			$ans = ap_count_ans($post->post_parent);
			ap_do_event('delete_answer', $post->ID, $post->post_author);
			ap_remove_parti($post->post_parent, $post->post_author, 'answer');
			
			//update answer count
			update_post_meta($post->post_parent, ANSPRESS_ANS_META, $ans-1);
		}
	}

	//if questions was restored then restore its answers as well	
	public function untrash_ans_on_question_untrash ($post_id) {
		$post = get_post( $post_id );
		
		if( $post->post_type == 'question') {
			ap_do_event('untrash_question', $post->ID, $post->post_author);
			ap_add_parti($post->ID, $post->post_author, 'question');
			
			$arg = array(
			  'post_type' => 'answer',
			  'post_status' => 'trash',
			  'post_parent' => $post_id,
			  'showposts' => -1,
			  'caller_get_posts'=> 1
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
			$ans = ap_count_ans( $post->post_parent );
			ap_do_event('untrash_answer', $post->ID, $post->post_author);
			ap_add_parti($post->post_parent, $post->post_author, 'answer');
			
			//update answer count
			update_post_meta($post->post_parent, ANSPRESS_ANS_META, $ans+1);
		}
	}
	
	public function delete_action($post_id){
		$post = get_post($post_id);
		
		if($post->post_type == 'question')
			ap_do_event('delete_question', $post->ID, $post->post_author);
		
		elseif($post->post_type == 'answer')
			ap_do_event('delete_answer', $post->ID, $post->post_author);
	}
	
	/* Action to do right after deleting a post */
	public function post_delete_action ($post_id) {
		 
		remove_action('after_delete_post', array($this, 'post_delete_action'));
		 
		/* trashed item post type is revison so we have to get its post parent */
		$post = get_post($post_id );
		
		if($post){
			/* if questions was deleted then delete its answers as well */
			$post_type = get_post_type( $post->post_parent );
			if( $post_type == 'question') {
				ap_do_event('delete_question', $post->post_parent, $post->post_author);			
				// remove question participant
				ap_remove_parti($post->post_parent);
				
				$arg = array(
				  'post_type' => 'answer',
				  'post_status' => 'trash',
				  'post_parent' => $post->post_parent,
				  'showposts' => -1,
				  'caller_get_posts'=> 1,
				  'post__not_in' => '',
				);
				$ans = get_posts($arg);
				
				if(count($ans)>0){
					
					foreach( $ans as $p){					
						ap_remove_parti($p->post_parent, $p->post_author, 'answer');
						ap_do_event('delete_answer', $ans->ID, $ans->post_author);
						wp_delete_post($p->ID, true);					
					}
					
				}
			}
			
			/* remove participant answer */
			if( $post_type == 'answer') {
				$ans = get_post($post->post_parent );
				ap_remove_parti($ans->post_parent, $ans->post_author, 'answer');
				ap_do_event('delete_answer', $ans->ID, $ans->post_author);
			}
		}
		add_action('after_delete_post', array($this, 'post_delete_action'));
	}
	
	
	public function ans_post_type_link($link, $post) {
	  $post_type = 'answer';
	  if ($post->post_type==$post_type) {
		$link = get_permalink($post->post_parent) ."#answer_{$post->ID}";
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
			if(get_option('permalink_structure')){
				$question_slug = ap_opt('question_prefix');
				$question_slug = strlen($question_slug) > 0 ? $question_slug.'/' : '';
				return  ap_get_link_to($question_slug.$post->ID.'/'.$post->post_name); 
			}else
				return add_query_arg( array('apq' => false, 'page_id' => ap_opt('base_page'), 'question_id' =>$post->ID), $url );
		}
		return $url;
	}
	
	public function custom_page_link( $result ){
		//print_r($result);
		if(ap_opt('base_page') == get_option('page_on_front'))
			$result = str_replace('?paged', '?page_id='.ap_opt('base_page').'&paged', $result);
		return $result ;
	}
	
	public function answer_sort_newest($sql, $query){
		global $wpdb;
		if(isset($query->query['ap_query']) && $query->query['ap_query'] == 'answer_sort_newest'){		
			$sql['orderby'] = 'IF('.$wpdb->prefix.'postmeta.meta_key = "'.ANSPRESS_BEST_META.'" AND '.$wpdb->prefix.'postmeta.meta_value = 1, 0, 1), '.$sql['orderby'];
		}elseif(isset($query->query['ap_query']) && $query->query['ap_query'] == 'answer_sort_voted'){
			$sql['orderby'] = 'IF(mt1.meta_value = 1, 0, 1), '.$sql['orderby'];
		}
		return $sql;
	}
	
	public function user_favorites($sql, $query){
		global $wpdb;
		if(isset($query->query['ap_query']) && $query->query['ap_query'] == 'user_favorites'){			
			$sql['join'] = 'LEFT JOIN '.$wpdb->prefix.'ap_meta apmeta ON apmeta.apmeta_actionid = ID '.$sql['join'];
			$sql['where'] = 'AND apmeta.apmeta_userid = post_author AND apmeta.apmeta_type ="favorite" '.$sql['where'];
		}
		return $sql;
	}
	
	public function append_post_status_list(){
		 global $post;
		 $complete = '';
		 $label = '';
		
		 if($post->post_type == 'question'){
			  if($post->post_status == 'moderate'){
				   $complete = ' selected=\'selected\'';
				   $label = '<span id=\'post-status-display\'>'.__('Moderate', 'ap').'</span>';
			  }elseif($post->post_status == 'private_question'){
				   $complete = ' selected=\'selected\'';
				   $label = '<span id=\'post-status-display\'>'.__('Private Question', 'ap').'</span>';
			  }elseif($post->post_status == 'closed'){
				   $complete = ' selected=\'selected\'';
				   $label = '<span id=\'post-status-display\'>'.__('Closed', 'ap').'</span>';
			  }
			  ?>
			  
			  <?php
			  echo '<script>
					  jQuery(document).ready(function(){
						   jQuery("select#post_status").append("<option value=\'moderate\' '.$complete.'>'.__('Moderate', 'ap').'</option>");
						   jQuery("select#post_status").append("<option value=\'private_question\' '.$complete.'>'.__('Private Question', 'ap').'</option>");
						   jQuery("select#post_status").append("<option value=\'closed\' '.$complete.'>'.__('Closed', 'ap').'</option>");
						   jQuery(".misc-pub-section label").append("'.$label.'");
					  });
			  </script>';
		 }
	}
	
	public function main_question_query($sql, $query){
		global $wpdb;
		if(isset($query->query['ap_query']) && $query->query['ap_query'] == 'main_questions_active'){
			$sql['orderby'] = 'case when mt1.post_id IS NULL then '.$wpdb->posts.'.post_date else '.$wpdb->postmeta.'.meta_value end DESC';
			//var_dump($sql);
		}elseif(isset($query->query['ap_query']) && $query->query['ap_query'] == 'related'){
			$keywords = explode(' ', $query->query['ap_title']);

			$where = "AND (";
			$i =1;
			foreach ($keywords as $key){
				if(strlen($key) > 1){
					$key = $wpdb->esc_like( $key );
					if($i != 1)
					$where .= "OR ";
					$where .= "(($wpdb->posts.post_title LIKE '%$key%') AND ($wpdb->posts.post_content LIKE '%$key%')) ";
					$i++;
				}
			}
			$where .= ")";
			
			$sql['where'] = $sql['where'].' '.$where;

		}
		return $sql;
	}
	
	public function question_feed(){
		include ap_get_theme_location('feed-question.php');
	}

}
