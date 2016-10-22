<?php
/**
 * Filter for post query
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 * @since 2.0.1
 */

class AnsPress_Query_Filter
{
	public static function init_actions() {
		// add_meta_box( 'ap_ans_parent_q','Parent Question', array($this, 'ans_parent_q_metabox'),'answer','side', 'high' );
	}

	public static function custom_post_location( $location ) {
		remove_filter('redirect_post_location', __FUNCTION__, 99 );
		$location = add_query_arg('message', 99, $location );
		return $location;
	}

	public static function ans_notice() {
		echo '<div class="error">
           <p>' . __('Please fill parent question field, Answer was not saved!', 'anspress-question-answer' ) . '</p>
        </div>';
	}

	public static function ans_parent_q_metabox( $answer ) {
		echo '<input type="hidden" name="ap_ans_noncename" id="ap_ans_noncename" value="' .wp_create_nonce( plugin_basename(__FILE__ ) ) . '" />';
		echo '<input type="hidden" name="ap_q" id="ap_q" value="'.$answer->post_parent.'" />';
		echo '<input type="text" name="ap_q_search" id="ap_q_search" value="'.get_the_title($answer->post_parent ).'" />';
	}

	/**
	 * Filter WP_Query query to include all users subscribed items.
	 * @param  array  $sql   Sql Query.
	 * @param  object $args  WP_Query args.
	 * @return array
	 */
	public static function user_favorites( $sql, $query ) {
		global $wpdb;
		if ( isset($args->query['ap_query'] ) && $args->query['ap_query'] == 'user_favorites' ) {
			$sql['join'] = 'LEFT JOIN '.$wpdb->prefix.'ap_meta apmeta ON apmeta.apmeta_actionid = ID '.$sql['join'];
			$sql['where'] = 'AND apmeta.apmeta_userid = post_author AND apmeta.apmeta_type ="favorite" '.$sql['where'];
		}
		return $sql;
	}

	/**
	 * Add AnsPress post status to post edit select box.
	 */
	public static function append_post_status_list() {
		 global $post;
		 $complete = '';
		 $label = '';

		if ( $post->post_type == 'question' || $post->post_type == 'answer' ) {
			if ( $post->post_status == 'moderate' ) {
				 $complete = ' selected=\'selected\'';
				 $label = '<span id=\'post-status-display\'>'.__('Moderate', 'anspress-question-answer' ).'</span>';
			} elseif ( $post->post_status == 'private_post' ) {
				 $complete = ' selected=\'selected\'';
				 $label = '<span id=\'post-status-display\'>'.__('Private Post', 'anspress-question-answer' ).'</span>';
			}
				?>

				<?php
				echo '<script>
                      jQuery(document).ready(function(){
						   jQuery("select#post_status").append("<option value=\'moderate\' '.$complete.'>'.__('Moderate', 'anspress-question-answer' ).'</option>");
						   jQuery("select#post_status").append("<option value=\'private_post\' '.$complete.'>'.__('Private Post', 'anspress-question-answer' ).'</option>");
						   jQuery(".misc-pub-section label").append("'.$label.'");
                      });
			  </script>';
		}
	}


	public static function question_feed() {
		include ap_get_theme_location('feed-question.php' );
	}

	public static function ap_question_subscription_query($sql, $query) {

		// First check if this is right query to append filters
		if ( isset($query->query['ap_query'] ) && $query->query['ap_query'] == 'ap_subscription_query' ) {
			global $wpdb;

			/*$sql['join'] = 'JOIN '.$wpdb->prefix."ap_meta apmeta ON $wpdb->posts.ID = apmeta.apmeta_actionid";
			$sql['where'] = $sql['where']." AND apmeta.apmeta_type='subscriber' AND apmeta.apmeta_userid='".$query->query['user_id']."'";*/
		}

		return $sql;
	}

	/**
	 * Filter post object to check if user is allowed to read answer.
	 * If no, then strip contents.
	 * @param  array  $posts Post objects.
	 * @param  object $query WP_Query class.
	 * @return array
	 * @since  2.4.6
	 */
	public static function restricted_answer_contents( $posts, $query ) {
		foreach ( (array) $posts as $key => $p ) {
			if ( $p->post_type == 'answer' && ! ap_user_can_read_answer( $p ) ) {
				$message = array(
					'private_post' => __('Answer is private, only moderator and participants can read.', 'anspress-question-answer' ),
					'moderate' => __('Answer is pending approval by moderator. ', 'anspress-question-answer' ),
					'publish' => __('You do not have permission to read this answer. ', 'anspress-question-answer' ),
				);
				$calss = $p->post_status == 'moderate' ? 'yellow' : 'gray';
				$posts[$key]->post_content = sprintf('<div class="ap-notice %s clearfix"><i class="apicon-lock"></i><span>%s</span></div>', $calss, $message[ $p->post_status ] );
			}
		}

		return $posts;
	}

}
