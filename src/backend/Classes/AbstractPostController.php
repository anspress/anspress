<?php
/**
 * Common controller for post types.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Classes;

use AnsPress\Classes\AbstractController;
use AnsPress\Exceptions\HTTPException;
use AnsPress\Exceptions\ValidationException;
use AnsPress\Modules\Answer\AnswerService;
use AnsPress\Modules\Question\QuestionService;
use AnsPress\Modules\Vote\VoteService;
use InvalidArgumentException;
use WP_Query;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Question controller class.
 */
class AbstractPostController extends AbstractController {
	/**
	 * Report a post.
	 *
	 * @param int $postId The ID of the post.
	 * @return WP_REST_Response Response.
	 */
	public function actionReport( int $postId ) {
		$this->assureLoggedIn();

		$post = ap_get_post( $postId );

		if ( Plugin::get( VoteService::class )->hasUserFlaggedPost( $post->ID ) ) {
			return $this->badRequest( __( 'You have already reported this post.', 'anspress-question-answer' ) );
		}

		$voted = Plugin::get( VoteService::class )->addPostFlag( $post->ID, Auth::getID() );

		if ( ! $voted ) {
			return $this->serverError( __( 'Failed to report this post.', 'anspress-question-answer' ) );
		}

		$this->addMessage(
			'success',
			esc_attr__( 'Thank you for reporting this post.', 'anspress-question-answer' )
		);

		return $this->response();
	}

	/**
	 * Set post as moderate.
	 *
	 * @param int $postId Id of the post.
	 * @return WP_REST_Response Response.
	 * @throws ValidationException If validation fails.
	 */
	public function actionMakeModerate( int $postId ) {
		$this->assureLoggedIn();

		$post = ap_get_post( $postId );

		if ( ! PostHelper::isValidPostType( $post->post_type ) ) {
			return $this->badRequest(
				__( 'Invalid post type.', 'anspress-question-answer' )
			);
		}

		$this->checkPermission(
			$post->post_type . ':set_status_to_moderate',
			array(
				$post->post_type => $post,
			)
		);

		$service = Plugin::get( PostHelper::isQuestion( $post ) ? QuestionService::class : AnswerService::class );

		$service->updatePostStatusToModerate( $post->ID );

		$this->addMessage(
			'success',
			__( 'Status updated to moderate.', 'anspress-question-answer' )
		);

		$query = new WP_Query(
			array(
				'p'         => $post->ID,
				'post_type' => $post->post_type,
			)
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->replaceHtml(
					'[data-anspress-id="' . $post->post_type . ':' . $post->ID . '"]',
					TemplateHelper::loadRestBlockPart(
						$this->request,
						'src/frontend/single-question/php/item.php',
						array(
							'post' => get_post(),
						)
					)
				);
			}
		}

		return $this->response();
	}

	/**
	 * Set post as private.
	 *
	 * @param int $postId The ID of the post.
	 * @return WP_REST_Response Response.
	 * @throws ValidationException If validation fails.
	 */
	public function actionMakePrivate( int $postId ) {
		$this->assureLoggedIn();

		$post = ap_get_post( $postId );

		if ( ! PostHelper::isValidPostType( $post->post_type ) ) {
			return $this->badRequest(
				__( 'Invalid post type.', 'anspress-question-answer' )
			);
		}

		$this->checkPermission(
			$post->post_type . ':set_status_to_private',
			array(
				$post->post_type => $post,
			)
		);

		$service = Plugin::get( PostHelper::isQuestion( $post ) ? QuestionService::class : AnswerService::class );

		$service->updatePostStatusToPrivate( $post->ID );

		$this->addMessage(
			'success',
			__( 'Status pdated to private and is only visible to author, admin and moderators.', 'anspress-question-answer' )
		);

		$query = new WP_Query(
			array(
				'p'         => $post->ID,
				'post_type' => $post->post_type,
			)
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->replaceHtml(
					'[data-anspress-id="' . esc_attr( $post->post_type . ':' . $post->ID ) . '"]',
					TemplateHelper::loadRestBlockPart(
						$this->request,
						'src/frontend/single-question/php/item.php',
						array(
							'post' => get_post(),
						)
					)
				);
			}
		}

		return $this->response();
	}

	/**
	 * Set post as public.
	 *
	 * @param int $postId The ID of the post.
	 * @return WP_REST_Response Response.
	 * @throws ValidationException If validation fails.
	 */
	public function actionSetPublish( int $postId ) {
		$this->assureLoggedIn();

		$post = ap_get_post( $postId );

		if ( ! PostHelper::isValidPostType( $post->post_type ) ) {
			return $this->badRequest(
				__( 'Invalid post type.', 'anspress-question-answer' )
			);
		}

		$this->checkPermission(
			$post->post_type . ':set_status_to_publish',
			array(
				$post->post_type => $post,
			)
		);

		$service = Plugin::get( PostHelper::isQuestion( $post ) ? QuestionService::class : AnswerService::class );

		$service->updatePostStatusToPublish( $post->ID );

		$this->addMessage(
			'success',
			__( 'Status updated to public.', 'anspress-question-answer' )
		);

		$query = new WP_Query(
			array(
				'p'         => $post->ID,
				'post_type' => $post->post_type,
			)
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$this->replaceHtml(
					'[data-anspress-id="' . $post->post_type . ':' . $post->ID . '"]',
					TemplateHelper::loadRestBlockPart(
						$this->request,
						'src/frontend/single-question/php/item.php',
						array(
							'post' => get_post(),
						)
					)
				);
			}
		}

		return $this->response();
	}
}
