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
					'post_type'=>'question'				
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
