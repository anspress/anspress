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

	}


	public static function question_feed() {
		include ap_get_theme_location('feed-question.php' );
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
