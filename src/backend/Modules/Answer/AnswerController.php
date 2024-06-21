<?php
/**
 * Answer controller.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractController;
use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\ValidationException;
use InvalidArgumentException;
use WP_Query;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Answer controller class.
 */
class AnswerController extends AbstractController {
	/**
	 * Answer service.
	 *
	 * @param AnswerService $answerService Answer service.
	 * @return void
	 */
	public function __construct( public AnswerService $answerService ) {
	}

	/**
	 * Load answer form.
	 *
	 * @return WP_REST_Response Response.
	 * @throws ValidationException If validation fails.
	 */
	public function loadAnswerForm(): WP_REST_Response {
		if ( ! Auth::isLoggedIn() ) {
			return $this->unauthorized();
		}

		$data = $this->validate(
			array( 'post_id' => 'required|numeric|exists:posts,id' ),
		);

		$post = get_post( $data['post_id'] );

		if ( ! $post || 'question' !== $post->post_type ) {
			throw new ValidationException( array( 'post_id' => esc_attr__( 'Invalid post id', 'anspress-question-answer' ) ) );
		}

		// @todo: check for user access to the question.

		return $this->response(
			array(
				'answer-formHtml' => Plugin::loadView(
					'src/frontend/single-question/answer-form.php',
					array(
						'question'    => $post,
						'form_loaded' => true,
					),
					false
				),
				'load_easymde'    => 'anspress-answer-content',
			)
		);
	}

	/**
	 * Create answer.
	 *
	 * @return WP_REST_Response Response object.
	 * @throws ValidationException If validation fails.
	 */
	public function createAnswer(): WP_REST_Response {
		if ( ! Auth::isLoggedIn() ) {
			return $this->unauthorized();
		}

		$data = $this->validate(
			array(
				'post_content' => 'required|min:1|max:5000',
				'post_id'      => 'required|numeric|exists:posts,ID|post_type:question',
			)
		);

		$answer = $this->answerService->createAnswer(
			array(
				'post_content' => $data['post_content'],
				'question_id'  => $data['post_id'],
				'post_author'  => get_current_user_id(),
			)
		);

		$query = new WP_Query(
			array(
				'post_type'   => 'answer',
				'post_status' => 'publish',
				'p'           => $answer->ID,
			)
		);

		$html = '';
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$html = Plugin::loadView( 'src/frontend/single-question/item.php', array(), false );
			}
		}

		wp_reset_postdata();

		return $this->response(
			array(
				'answer-formMessages' => array(
					array(
						'message' => esc_attr__( 'Answer posted successfully.', 'anspress-question-answer' ),
					),
				),
				'appendHtmlTo'        => array( '[data-anspressel="answers-items"]' => $html ),
				'answer-formHtml'     => Plugin::loadView(
					'src/frontend/single-question/answer-form.php',
					array(
						'question'    => get_post( $data['post_id'] ),
						'form_loaded' => false,
					),
					false
				),
			)
		);
	}
}
