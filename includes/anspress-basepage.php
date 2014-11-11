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

class AP_BasePage {

	/**
	 * Instance of this class.
	 */
	protected static $instance = null;
	/**
	 * Return an instance of this class.
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
	
	public function __construct(){
		add_shortcode( 'anspress', array( $this, 'ap_base_page_sc' ) );
	}

	public function ap_base_page_sc( $atts, $content="" ) {
		if(!is_question()){			
			$question_args = ap_base_page_main_query();
		}
		
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		
		if(is_question()){
			$args = array(
					'p'=> get_question_id(), 
					'post_type'=>'question',
				);
			$question = new WP_Query( $args );
		}elseif(is_question_tag()){
			$question_args['tax_query'] = array(		
					array(
						'taxonomy' => 'question_tags',
						'field' => 'id',
						'terms' => array( get_question_tag_id() )
					)
				);
					
			$question = new WP_Query( $question_args );
			$tag = $question->get_queried_object();
		}elseif(is_question_cat()){
			$question_args['tax_query'] = array(		
					array(
						'taxonomy' => 'question_category',
						'field' => 'id',
						'terms' => array( get_question_cat_id() )
					)
				);
			
			$question = new WP_Query( $question_args );
			$category = $question->get_queried_object();
		}elseif(is_question_tags()){
			$paged 			= (get_query_var('paged')) ? get_query_var('paged') : 1;
			$per_page    	= ap_opt('tags_per_page');
			$total_terms 	= wp_count_terms('question_tags'); 	
			$offset      	= $per_page * ( $paged - 1) ;
			$args = array(
				'number'		=> $per_page,
				'offset'       	=> $offset,
				'hide_empty'    => false,
				'orderby'       => 'count',
				'order'         => 'DESC',
			);

			$tags = get_terms( 'question_tags' , $args); 
		}elseif(is_question_categories()){
			$paged 			= (get_query_var('paged')) ? get_query_var('paged') : 1;
			$per_page    	= ap_opt('categories_per_page');
			$total_terms 	= wp_count_terms('question_category'); 	
			$offset      	= $per_page * ( $paged - 1) ;
			$cat_args = array(
				'parent' 		=> 0,
				'number'		=> $per_page,
				'offset'       	=> $offset,
				'hide_empty'    => false,
				'orderby'       => 'count',
				'order'         => 'DESC',
			);

			$categories = get_terms( 'question_category' , $cat_args); 
		}elseif(is_ap_users()){
			global $current_user_meta;
			
			$count_args  = array(
				'fields'    => 'all_with_meta',
				'number'    => 999999      
			);
			$user_count_query = new WP_User_Query($count_args);
			$user_count = $user_count_query->get_results();
			// count the number of users found in the query
			$total_users = $user_count ? count($user_count) : 1;

			// how many users to show per page
			$per_page = ap_opt('users_per_page');
			
			// grab the current page number and set to 1 if no page number is set
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			
			// calculate the total number of pages.
			$total_pages = 1;
			$offset = $per_page * ($paged - 1);
			$total_pages = ceil($total_users / $per_page);
			
			$order = get_query_var('sort');
			$base = ap_get_link_to('users') . '/%_%';
			if(empty($order ))
				$order = 'points';
				
			$args = array(				
				'number'    => $per_page,
				'offset'    => $offset
			);
			
			if($order == 'points'){
				$args['ap_query']  	= 'sort_points';
				$args['meta_key'] 	= 'ap_points';
				$args['orderby'] 	= 'meta_value';
				$args['order'] 		= 'DESC';
			}elseif($order == 'newest'){
				$args['orderby'] 	= 'date';
				$args['order'] 		= 'DESC';
			}
			
			// The Query
			$users = new WP_User_Query( $args );
		}elseif(is_ap_user()){
			global $current_user_meta;
			global $user;
			$user 			= get_userdata( ap_get_user_page_user() );
			
			if($user === FALSE){
				echo '<div class="user-not-found">'. __('User not found', 'ap') .'</div>';
				return;
			}
			
			$userid 		= $user->data->ID;
			$display_name 	= $user->data->display_name;
			$username 		= $user->data->user_login;
			$current_user_meta = array_map(	'ap_meta_array_map', get_user_meta($userid));
		}elseif(is_ap_search()){
			$question_args['s'] = sanitize_text_field(get_query_var('ap_s'));
			$question = new WP_Query( $question_args );
		}else{			
			$question = new WP_Query( $question_args );		
		}

		echo '<div class="ap-container">';
		do_action('ap_page_top');
		dynamic_sidebar( 'ap-before' );
		
		if ((ap_current_page_is() == 'question') and (is_active_sidebar('ap-qsidebar' )))
			echo '<div class="ap-dtable">';
		else
			echo '<div>';
		
		
		if ( !is_question() && is_active_sidebar( 'ap-sidebar' ))
			echo '<div class="ap-cl">';
		else
			echo '<div>';
		
		include ap_get_theme_location(ap_get_current_page_template());
		
		if(is_question_tags())
			ap_pagi(ap_get_link_to('tags') . '/%_%', ceil( $total_terms / $per_page ), $paged);
		
		if(is_question_categories())
			ap_pagi(ap_get_link_to('categories') . '/%_%', ceil( $total_terms / $per_page ), $paged);
		
		echo '</div>';
		
		if ( !is_question() && is_active_sidebar( 'ap-sidebar' ) ) {
			echo '<div class="ap-sidebar">';
				dynamic_sidebar( 'ap-sidebar' );
			echo '</div>';
		}
		
		echo '</div>';
		
		if ((ap_current_page_is() == 'question') and (is_active_sidebar('ap-qsidebar' ))) {
			echo'<div class="question-sidebar">';
			dynamic_sidebar( 'ap-qsidebar' );
			echo'</div>';
		}

		if(!ap_opt('author_credits')){
			?>
				<div class="ap-footer clearfix">
					<p class="ap-author-credit"><a href="http://open-wp.com">AnsPress</a> Version <?php echo AP_VERSION; ?></p>
				</div>
			<?php
		}
		wp_reset_postdata();
		echo '</div>';
	}
	
}
function ap_meta_array_map( $a ){ 
	return $a[0]; 
}
