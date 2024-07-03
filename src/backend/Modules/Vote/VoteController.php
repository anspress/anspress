<?php
/**
 * Vote controller.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Vote;

use AnsPress\Classes\AbstractController;
use AnsPress\Classes\Auth;
use WP_REST_Response;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vote controller.
 *
 * @package AnsPress\Modules\Vote
 */
class VoteController extends AbstractController {
	/**
	 * Constructor.
	 *
	 * @param VoteService $voteService Vote service.
	 * @return void
	 */
	public function __construct( private VoteService $voteService ) {}

	/**
	 * Get votes for a post.
	 *
	 * @return WP_REST_Response
	 */
	public function getPostVotes(): WP_REST_Response {
		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		$postId = (int) $this->getParam( 'post_id' );

		$votes = $this->voteService->getVotesCount( $postId, 'vote' );

		return $this->response( array( 'votes' => $votes ) );
	}

	/**
	 * Create a new vote.
	 */
	public function createVote(): WP_REST_Response {
		$this->assureLoggedIn();

		// Validate request data.
		$data = $this->validate(
			array(
				'post_id'   => 'required|numeric|exists:posts,ID',
				'vote_type' => 'required|string|in:voteup,votedown',
			)
		);

		$postObj = get_post( $data['post_id'] );

		// Check for permission.
		$this->checkPermission( 'vote:create', array( 'post' => $postObj ) );

		$vote = $this->voteService->addPostVote(
			$data['post_id'],
			'voteup' === $data['vote_type'] ? 1 : -1,
			Auth::getID()
		);

		if ( ! $vote ) {
			$this->addMessage( 'error', __( 'Failed to create vote', 'anspress-question-answer' ) );

			return $this->serverError( __( 'Failed to create vote', 'anspress-question-answer' ) );
		}

		$this->addMessage( 'success', __( 'Vote added', 'anspress-question-answer' ) );

		$this->setData( 'vote:' . $postObj->ID, $this->voteService->getPostVoteData( $vote->vote_post_id ) );

		return $this->response();
	}

	/**
	 * Undo an existing vote.
	 *
	 * @return WP_REST_Response
	 */
	public function undoVote(): WP_REST_Response {
		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		// Validate request data.
		$data = $this->validate(
			array(
				'post_id' => 'required|numeric|exists:posts,ID',
			)
		);

		$vote = $this->voteService->getUserVote( get_current_user_id(), $data['post_id'], 'vote' );

		// Check if vote exists.
		if ( ! $vote ) {
			return $this->notFound( __( 'No vote records found.', 'anspress-question-answer' ) );
		}

		// Check for permission.
		$this->checkPermission( 'vote:delete', array( 'vote' => $vote ) );

		$deleted = $this->voteService->delete( $vote->vote_id );

		if ( ! $deleted ) {
			return $this->serverError( __( 'Failed to undo vote', 'anspress-question-answer' ) );
		}

		$this->addMessage( 'success', __( 'Vote removed', 'anspress-question-answer' ) );

		$this->setData( 'vote:' . $vote->vote_post_id, $this->voteService->getPostVoteData( $vote->vote_post_id ) );

		return $this->response();
	}
}
