<?php
/**
 * Question controller.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Question;

use AnsPress\Classes\AbstractController;

use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Question controller class.
 */
class QuestionController extends AbstractController {
	/**
	 * Question service.
	 *
	 * @param QuestionService $questionService Question service.
	 * @return void
	 */
	public function __construct( public QuestionService $questionService ) {
	}

	/**
	 * Delete question.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function deleteQuestion() {
		$this->assureLoggedIn();

		$data = $this->validate(
			array(
				'post_id' => 'required|numeric|exists:posts,id|post_type:question',
			),
		);

		$post = get_post( $data['post_id'] );

		$this->checkPermission( 'question:delete', array( 'question' => $post ) );

		$this->questionService->deleteQuestion( $post->ID );

		$this->addMessage( 'success', __( 'Question deleted successfully.', 'anspress-question-answer' ) );

		return $this->response(
			array(
				'redirect' => ap_base_page_link(),
			)
		);
	}
}
