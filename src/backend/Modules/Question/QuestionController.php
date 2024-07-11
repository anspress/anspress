<?php
/**
 * Question controller.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Question;

use AnsPress\Classes\AbstractPostController;
use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Classes\Str;
use AnsPress\Classes\TemplateHelper;
use AnsPress\Exceptions\ValidationException;
use AnsPress\Exceptions\HTTPException;
use AnsPress\Modules\Subscriber\SubscriberService;
use InvalidArgumentException;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Question controller class.
 */
class QuestionController extends AbstractPostController {
	/**
	 * Question service.
	 *
	 * @param QuestionService $questionService Question service.
	 * @return void
	 */
	public function __construct( public QuestionService $questionService ) {
	}

	/**
	 * Question action handler.
	 *
	 * @return WP_REST_Response Response.
	 */
	public function actions(): WP_REST_Response {
		$data = $this->validate(
			array(
				'question_id' => 'required|numeric|exists:posts,id|post_type:question',
				'action'      => 'required|string',
			)
		);

		$action = Str::toCamelCase( 'action ' . $data['action'] );

		if ( method_exists( $this, $action ) ) {
			return $this->$action( (int) $data['question_id'] );
		}

		return $this->notFound(
			sprintf(
				// translators: %s: action name.
				esc_attr__( 'Invalid %s action.', 'anspress-question-answer' ),
				$action
			)
		);
	}

	/**
	 * Delete question.
	 *
	 * @param int $questionId The ID of the question.
	 * @return WP_REST_Response Response.
	 */
	public function actionDeleteQuestion( int $questionId ): WP_REST_Response {
		$this->assureLoggedIn();

		$post = get_post( $questionId );

		$this->checkPermission( 'question:delete', array( 'question' => $post ) );

		$this->questionService->deleteQuestion( $post->ID );

		$this->addMessage( 'success', __( 'Question deleted successfully.', 'anspress-question-answer' ) );

		return $this->response(
			array(
				'redirect' => ap_base_page_link(),
			)
		);
	}

	/**
	 * Toggle the closed state of a question.
	 *
	 * @param int $questionId The ID of the question.
	 * @return WP_REST_Response Response.
	 */
	public function actionToggleClosedState( int $questionId ) {
		$this->assureLoggedIn();

		$post = ap_get_post( $questionId );

		$this->checkPermission( 'question:close', array( 'question' => $post ) );

		$newState = $this->questionService->toggleQuestionClosedState( $post->ID );

		$this->addMessage(
			'success',
			'closed' === $newState ? __( 'Question closed successfully.', 'anspress-question-answer' ) :
			__( 'Question opened successfully.', 'anspress-question-answer' )
		);

		return $this->response(
			array(
				'reload' => true,
			)
		);
	}

	/**
	 * Toggle the featured state of a question.
	 *
	 * @param int $questionId The ID of the question.
	 * @return WP_REST_Response Response.
	 */
	public function actionToggleFeatured( int $questionId ) {
		$this->assureLoggedIn();

		$post = ap_get_post( $questionId );

		$this->checkPermission( 'question:feature', array( 'question' => $post ) );

		$newState = $this->questionService->toggleQuestionFeaturedState( $post->ID );

		$this->addMessage(
			'success',
			'featured' === $newState ? __( 'Question featured successfully.', 'anspress-question-answer' ) :
			__( 'Question unfeatured successfully.', 'anspress-question-answer' )
		);

		return $this->response(
			array(
				'reload' => true,
			)
		);
	}

	/**
	 * Load answer form.
	 *
	 * @param int $questionId The ID of the question.
	 * @return WP_REST_Response Response.
	 * @throws ValidationException If validation fails.
	 */
	public function actionLoadAnswerForm( int $questionId ): WP_REST_Response {
		$this->assureLoggedIn();

		$this->validate(
			array(
				'form_loaded' => 'nullable|bool',
			),
		);

		$post = get_post( $questionId );

		$this->checkPermission( 'answer:create', array( 'question' => $post ) );

		$this->replaceHtml(
			'[data-anspress-id="answer-form-c-' . $post->ID . '"]',
			TemplateHelper::loadRestBlockPart(
				$this->request,
				'src/frontend/single-question/php/answer-form.php',
				array(
					'question'    => $post,
					'form_loaded' => (bool) $this->getParam( 'form_loaded', false ),
				)
			)
		);

		return $this->response(
			array(
				'load_tinymce' => 'anspress-answer-content',
			)
		);
	}

	/**
	 * Load question form.
	 *
	 * @param int $questionId The ID of the question.
	 * @return WP_REST_Response Response.
	 */
	public function actionSubscribe( int $questionId ): WP_REST_Response {
		$this->assureLoggedIn();

		$post = get_post( $questionId );

		$this->checkPermission( 'subscriber:create', array( 'ref' => $post ) );

		// Check if user is already subscribed.
		$subscribed = Plugin::get( SubscriberService::class )
			->isSubscribedToQuestion( $post->ID, Auth::getID() );

		if ( $subscribed ) {
			Plugin::get( SubscriberService::class )->destroy( $subscribed->subs_id );

			$this->addMessage( 'success', __( 'Unsubscribed from question successfully.', 'anspress-question-answer' ) );

			return $this->response(
				array(
					'reload' => true,
				)
			);
		}

		Plugin::get( SubscriberService::class )->subscribeToQuestion( $post->ID, Auth::getID() );

		$this->addMessage( 'success', __( 'Subscribed to question successfully.', 'anspress-question-answer' ) );

		return $this->response(
			array(
				'reload' => true,
			)
		);
	}

	/**
	 * Create a new question.
	 *
	 * @return WP_REST_Response  Response.
	 */
	public function create(): WP_REST_Response {
		$data = $this->validate(
			array(
				'question_title'      => 'required|string|max:255|min:5',
				'question_content'    => 'required|string|min:10',
				'question_tags'       => 'array',
				'question_tags.*'     => 'nullable|numeric',
				'question_category'   => 'array',
				'question_category.*' => 'nullable|numeric',
				'private_question'    => 'nullable|bool',
			)
		);

		$question = $this->questionService->createQuestion(
			array(
				'post_title'        => $data['question_title'],
				'post_content'      => $data['question_content'],
				'post_author'       => Auth::getID(),
				'private_question'  => $data['private_question'],
				'question_tags'     => $data['question_tags'],
				'question_category' => $data['question_category'],
			)
		);

		$this->addMessage( 'success', __( 'Question created successfully.', 'anspress-question-answer' ) );

		return $this->response(
			array(
				'redirect' => get_permalink( $question ),
			)
		);
	}
}
