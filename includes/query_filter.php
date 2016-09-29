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
	 * Filter WP_Query query for answers loop.
	 * @param  array  $sql   Sql Query.
	 * @param  object $args  WP_Query args.
	 * @return array
	 */
	public static function answer_sort($sql, $args) {
		global $wpdb;

		if ( isset($args->query['ap_query'] ) && $args->query['ap_query'] == 'answer_sort_newest' ) {
			$sql['orderby'] = 'IF('.$wpdb->prefix.'postmeta.meta_key = "'.ANSPRESS_BEST_META.'" AND '.$wpdb->prefix.'postmeta.meta_value = 1, 0, 1), '.$sql['orderby'];
		} elseif ( isset($args->query['ap_query'] ) && $args->query['ap_query'] == 'answer_sort_voted' ) {
			$sql['orderby'] = 'IF(mt1.meta_value = 1, 0, 1), '.$sql['orderby'];
		} elseif ( isset($args->query['ap_query'] ) && $args->query['ap_query'] == 'order_answer_to_top' ) {
			$sql['orderby'] = $wpdb->prepare($wpdb->posts.'.ID=%d desc', $args->query['order_answer_id'] ).', '.$sql['orderby'];
		}

		return $sql;
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
			} elseif ( $post->post_status == 'closed' ) {
				 $complete = ' selected=\'selected\'';
				 $label = '<span id=\'post-status-display\'>'.__('Closed', 'anspress-question-answer' ).'</span>';
			}
				?>

				<?php
				echo '<script>
                      jQuery(document).ready(function(){
						   jQuery("select#post_status").append("<option value=\'moderate\' '.$complete.'>'.__('Moderate', 'anspress-question-answer' ).'</option>");
						   jQuery("select#post_status").append("<option value=\'private_post\' '.$complete.'>'.__('Private Post', 'anspress-question-answer' ).'</option>");
						   jQuery("select#post_status").append("<option value=\'closed\' '.$complete.'>'.__('Closed', 'anspress-question-answer' ).'</option>");
						   jQuery(".misc-pub-section label").append("'.$label.'");
                      });
			  </script>';
		}
	}

	/**
	 * Filter WP_Query query to include current users private posts
	 * Also order featured post to top.
	 *
	 * @param  array $sql   WP_Query sql query parts
	 * @param  array $query WP_Query class reference
	 * @return array
	 */
	public static function main_question_query( $sql, $query ) {

		// First check if this is right query to append filters
		if ( isset($query->query['ap_query'] ) && $query->query['ap_query'] == 'featured_post' ) {
			global $wpdb;

			$post_status = '';

			$query_status = $query->query['post_status'];

			// Build the post_status mysql query
			if ( ! empty($query_status ) ) {

				if ( is_array($query_status ) ) {

					$i = 1;

					foreach ( $query_status as $status ) {
						$post_status .= $wpdb->posts.".post_status = '".$status."'";

						if ( count($query_status ) != $i ) {
							$post_status .= ' OR '; } else {
							$post_status .= ')'; }

							$i++;
					}
				} else {
					$post_status .= $wpdb->posts.".post_status = '".$query_status."' ";
				}
			}

			// Replace post_status query
			if ( ($pos = strpos($sql['where'], $post_status )) !== false ) {

				$pos = $pos + strlen($post_status );

				$author_query = $wpdb->prepare(' OR ( '.$wpdb->posts.'.post_author = %d AND '.$wpdb->posts.".post_status NOT IN ('draft','trash','auto-draft','inherit') ) ", get_current_user_id() );

				$sql['where'] = substr_replace($sql['where'],$author_query, $pos, 0 );

			}

			$featured = get_option('featured_questions' );

			if ( ! empty( $featured ) ) {
				$featured = array_unique( $featured );
				$post_ids = '';
				$last_id = end($featured );

				// Convert posts ids to comma separated string.
				foreach ( (array) $featured as $id ) {
					if ( ! empty( $id ) && is_numeric( $id ) ) {
						$post_ids .= (int) $id;
					}
					if ( $id != $last_id ) {
						$post_ids .= ',';
					}
				}

				// To make sure sanitize comma separated string.
				$sanitised_ids = sanitize_comma_delimited( $post_ids );

				if ( ! empty( $sanitised_ids ) ) {
					$sql['orderby'] = " $wpdb->posts.ID IN ( $sanitised_ids ) DESC, ". $sql['orderby'];
				}
			}
		}

		return $sql;
	}

	public static function ap_answers_query($sql, $query) {
		if ( ! isset($query->query['ap_query'] ) && isset($query->query['ap_answers_query'] ) && @$query->args['only_best_answer'] !== true && is_user_logged_in() && isset($query->args['meta_query'] ) ) {

			global $wpdb;

			$meta = '';

			if ( count($query->args['meta_query'] ) == 1 ) {
				$meta = 'AND ( '.$wpdb->postmeta.".meta_key = '_ap_best_answer' AND CAST(".$wpdb->postmeta.".meta_value AS CHAR) != '1' )"; } else {
				$meta = "AND ( mt1.meta_key = '_ap_best_answer' AND CAST(mt1.meta_value AS CHAR) != '1' )"; }

				$sql['where'] = $sql['where'].$wpdb->prepare(' OR ( '.$wpdb->posts.'.post_author = %d AND '.$wpdb->posts.".post_type ='answer' AND ".$wpdb->posts.".post_parent = %d $meta ", get_current_user_id(), $query->args['question_id'] );

				if ( ! is_super_admin( ) ) {
					$sql['where'] = $sql['where'] .' AND post_status NOT IN("trash", "draft", "future", "pending")';
				}

				$sql['where'] = $sql['where'].' )';
		}

		return $sql;

	}

	public static function question_feed() {
		include ap_get_theme_location('feed-question.php' );
	}

	public static function ap_question_subscription_query($sql, $query) {

		// First check if this is right query to append filters
		if ( isset($query->query['ap_query'] ) && $query->query['ap_query'] == 'ap_subscription_query' ) {
			global $wpdb;

			$sql['join'] = 'JOIN '.$wpdb->prefix."ap_meta apmeta ON $wpdb->posts.ID = apmeta.apmeta_actionid";
			$sql['where'] = $sql['where']." AND apmeta.apmeta_type='subscriber' AND apmeta.apmeta_userid='".$query->query['user_id']."'";
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
