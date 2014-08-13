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

class anspress_shortcodes {

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
			$order = get_query_var('sort');
			$label = sanitize_text_field(get_query_var('label'));
			if(empty($order ))
				$order = 'active';//ap_opt('answers_sort');
				
			if(empty($label ))
				$label = '';
				
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			
			$question_args=array(
				'post_type' => 'question',
				'post_status' => 'publish',
				'showposts' => ap_opt('question_per_page'),
				'paged' => $paged
			);
			
			if($order == 'active'){				
				$question_args['orderby'] = 'meta_value';
				$question_args['meta_key'] = ANSPRESS_UPDATED_META;	
				
			}elseif($order == 'voted'){
				$question_args['orderby'] = 'meta_value_num';
				$question_args['meta_key'] = ANSPRESS_VOTE_META;
			}elseif($order == 'answers'){
				$question_args['orderby'] = 'meta_value_num';
				$question_args['meta_key'] = ANSPRESS_ANS_META;
			}elseif($order == 'unanswered'){
				$question_args['orderby'] = 'meta_value';
				$question_args['meta_key'] = ANSPRESS_ANS_META;
				$question_args['meta_value'] = '0';

			}elseif($order == 'oldest'){
				$question_args['orderby'] = 'date';
				$question_args['order'] = 'ASC';
			}
			
			if ($label != ''){
				$question_args['tax_query'] = array(
					array(
						'taxonomy' => 'question_label',
						'field' => 'slug',
						'terms' => $label
					)
				);				
			}
			
			$question_args = apply_filters('ap_main_query_args', $question_args);
		}
		
		if(is_question()){
			$args = array(
					'p'=> get_question_id(), 
					'post_type'=>'question',			
					'post_status'=> array('moderate', 'publish'),
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
		}elseif(is_ap_users()){
			global $current_user_meta;
			
			$count_args  = array(
				'role'      => 'Subscriber',
				'fields'    => 'all_with_meta',
				'number'    => 999999      
			);
			$user_count_query = new WP_User_Query($count_args);
			$user_count = $user_count_query->get_results();
			// count the number of users found in the query
			$total_users = $user_count ? count($user_count) : 1;

			// how many users to show per page
			$users_per_page = ap_opt('users_per_page');
			
			// grab the current page number and set to 1 if no page number is set
			$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
			
			// calculate the total number of pages.
			$total_pages = 1;
			$offset = $users_per_page * ($paged - 1);
			$total_pages = ceil($total_users / $users_per_page);
			
			$args = array(
				'number'    => $users_per_page,
				'offset'    => $offset
			);
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
			$current_user_meta = array_map( function( $a ){ return $a[0]; }, get_user_meta($userid));
		}else{			
			$question = new WP_Query( $question_args );		
		}
		
		echo '<div class="ap-container">';
		include ap_get_theme_location(ap_get_current_page_template());
		
		if(is_ap_users()){
			$base = ap_get_link_to('users') . '/%_%';
			$user_pagi = paginate_links( array(
				'base' => $base, // the base URL, including query arg
				'format' => 'paged/%#%', // this defines the query parameter that will be used, in this case "p"
				'prev_text' => __('&laquo; Previous', 'ap'), // text for previous page
				'next_text' => __('Next &raquo;', 'ap'), // text for next page
				'total' => $total_pages, // the total number of pages we have
				'current' => $paged, // the current page
				'end_size' => 1,
				'mid_size' => 5,
				'type' => 'array'
			));
			if($user_pagi){
				echo '<ul class="ap-pagination clearfix">';
					echo '<li><span class="page-count">'. sprintf(__('Page %d of %d', 'ap'), $paged, $total_pages) .'</span></li>';
					foreach($user_pagi as $pagi){
						echo '<li>'. $pagi .'</li>';
					}
				echo '</ul>';
			}
		}
		
		if(!ap_opt('author_credits')){
			?>
				<div class="ap-footer">
					<p class="ap-author-credit"><?php _e('Coded with &hearts; by', 'ap'); ?> <a href="https://rahularyan.com">Rahul Aryan</a> | AnsPress Version <?php echo AP_VERSION; ?></p>
				</div>
			<?php
		}
		echo '</div>';
		
		//if(is_question() || is_question_tag() || is_question_cat())
		wp_reset_postdata();
	}
	
}
