<?php
/**
 * Filter for post query
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 * @since 2.0.1
 */

class AnsPress_Query_Filter
{

    /**
     * Initialize the class
     */
    public function __construct()
    {
		
		add_action( 'posts_clauses', array($this, 'answer_sort_newest'), 10, 2 );
		add_action( 'posts_clauses', array($this, 'user_favorites'), 10, 2 );
        // TODO: move to admin
		add_action('admin_footer-post.php', array($this, 'append_post_status_list'));
		
		add_action( 'posts_clauses', array($this, 'main_question_query'), 10, 2 );

    }


	public function init_actions(){
		//add_meta_box( 'ap_ans_parent_q','Parent Question', array($this, 'ans_parent_q_metabox'),'answer','side', 'high' );
		
		//	
	}

    public function custom_post_location($location)
    {
        remove_filter('redirect_post_location', __FUNCTION__, 99);
        $location = add_query_arg('message', 99, $location);
        return $location;
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
		
		 if($post->post_type == 'question' || $post->post_type == 'answer'){
			  if($post->post_status == 'moderate'){
				   $complete = ' selected=\'selected\'';
				   $label = '<span id=\'post-status-display\'>'.__('Moderate', 'ap').'</span>';
			  }elseif($post->post_status == 'private_post'){
				   $complete = ' selected=\'selected\'';
				   $label = '<span id=\'post-status-display\'>'.__('Private Post', 'ap').'</span>';
			  }elseif($post->post_status == 'closed'){
				   $complete = ' selected=\'selected\'';
				   $label = '<span id=\'post-status-display\'>'.__('Closed', 'ap').'</span>';
			  }
			  ?>
			  
			  <?php
			  echo '<script>
					  jQuery(document).ready(function(){
						   jQuery("select#post_status").append("<option value=\'moderate\' '.$complete.'>'.__('Moderate', 'ap').'</option>");
						   jQuery("select#post_status").append("<option value=\'private_post\' '.$complete.'>'.__('Private Post', 'ap').'</option>");
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
