<?php
/**
 * Register all ajax hooks.
 *
 * @author       Rahul Aryan <rah12@live.com>
 * @license      GPL-2.0+
 * @link         https://anspress.net
 * @copyright    2014 Rahul Aryan
 * @package      AnsPress
 * @subpackage   Ajax Hooks
 */

// @codeCoverageIgnoreStart
if ( ! defined( 'WPINC' ) ) {
	die;
}
// @codeCoverageIgnoreEnd

/**
 * Register all ajax callback
 */
class AnsPress_Ajax {
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	public static function init() {
		add_action( 'ap_ajax_suggest_similar_questions', array( __CLASS__, 'suggest_similar_questions' ) );

		add_action( 'wp_ajax_ap_image_upload', array( 'AnsPress_Uploader', 'image_upload' ) );
		add_action( 'wp_ajax_ap_upload_modal', array( 'AnsPress_Uploader', 'upload_modal' ) );
		add_action( 'wp_ajax_nopriv_ap_upload_modal', array( 'AnsPress_Uploader', 'upload_modal' ) );
	}

	/**
	 * Show similar questions while asking a question.
	 *
	 * @since 2.0.1
	 */
	public static function suggest_similar_questions() {
		// Die if question suggestion is disabled.
		if ( ap_disable_question_suggestion() ) {
			wp_die( 'false' );
		}

		$keyword = ap_sanitize_unslash( 'value', 'request' );
		if ( empty( $keyword ) || ( ! ap_verify_default_nonce() && ! current_user_can( 'manage_options' ) ) ) {
				wp_die( 'false' );
		}

		$keyword   = ap_sanitize_unslash( 'value', 'request' );
		$is_admin  = (bool) ap_isset_post_value( 'is_admin', false );
		$questions = get_posts(
			array(
				'post_type' => 'question',
				'showposts' => 10,
				's'         => $keyword,
			)
		);

		if ( $questions ) {
				$items = '<div class="ap-similar-questions-head">';
				// translators: %d is count of questions.
				$items .= '<p><strong>' . sprintf( _n( '%d similar question found', '%d similar questions found', count( $questions ), 'anspress-question-answer' ), count( $questions ) ) . '</strong></p>';
				$items .= '<p>' . __( 'We have found some similar questions that have been asked earlier.', 'anspress-question-answer' ) . '</p>';
				$items .= '</div>';

			$items .= '<div class="ap-similar-questions">';

			foreach ( (array) $questions as $p ) {
				$count         = ap_get_answers_count( $p->ID );
				$p->post_title = ap_highlight_words( $p->post_title, $keyword );

				if ( $is_admin ) {
					$items .= '<div class="ap-q-suggestion-item clearfix"><a class="select-question-button button button-primary button-small" href="' . add_query_arg(
						array(
							'post_type'   => 'answer',
							'post_parent' => $p->ID,
						),
						admin_url( 'post-new.php' )
					) . '">' . __( 'Select', 'anspress-question-answer' ) . '</a><span class="question-title">' .
					// translators: %d is total answer count.
					$p->post_title . '</span><span class="acount">' . sprintf( _n( '%d Answer', '%d Answers', $count, 'anspress-question-answer' ), $count ) . '</span></div>';
				} else {
					// translators: %d is total answer count.
					$items .= '<a class="ap-sqitem clearfix" target="_blank" href="' . get_permalink( $p->ID ) . '"><span class="acount">' . sprintf( _n( '%d Answer', '%d Answers', $count, 'anspress-question-answer' ), $count ) . '</span><span class="ap-title">' . $p->post_title . '</span></a>';
				}
			}

			$items .= '</div>';
			$result = array(
				'status' => true,
				'html'   => $items,
			);
		} else {
			$result = array(
				'status'  => false,
				'message' => __( 'No related questions found.', 'anspress-question-answer' ),
			);
		}

		ap_ajax_json( $result );
	}

	/**
	 * Send JSON response and terminate.
	 *
	 * @param array|string $result Ajax response.
	 */
	public static function send( $result ) {
		ap_send_json( ap_ajax_responce( $result ) );
	}
}
