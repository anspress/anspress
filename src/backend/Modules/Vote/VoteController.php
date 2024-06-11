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
	 * @param int $postId Post ID.
	 * @return WP_REST_Response
	 */
	public function getPostVotes( int $postId ): WP_REST_Response {
		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		$votes = $this->voteService->getVoteCount( $postId, 'vote' );

		return $this->response( array( 'votes' => $votes ) );
	}

	/**
	 * Create a new vote.
	 */
	public function createVote(): WP_REST_Response {
		// Chekc for proper nonce.
		$this->validateNonce( 'ap_vote_nonce', '__ap_vote_nonce' );

		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		// Check for permission.
		$this->checkPermission( 'vote:create' );

		// Validate request data.
		$data = $this->validate(
			array(
				'vote'              => 'required|array',
				'vote.vote_type'    => 'required|string|in:upvote,downvote',
				'vote.vote_post_id' => 'required|numeric|exists:posts,ID',
			)
		);

		$vote = $data['vote'];

		$postObj = get_post( $vote['vote_post_id'] );

		$vote['vote_user_id']  = get_current_user_id();
		$vote['vote_value']    = 'upvote' === $vote['vote_type'] ? 1 : -1;
		$vote['vote_type']     = 'vote';
		$vote['vote_rec_user'] = (int) $postObj->post_author;

		$vote = $this->voteService->create( $vote );

		return $this->response( array( 'vote' => $vote->toArray() ) );
	}

	/**
	 * Undo an existing vote.
	 *
	 * @return WP_REST_Response
	 */
	public function undoVote(): WP_REST_Response {
		// Check for proper nonce.
		$this->validateNonce( 'ap_vote_nonce', '__ap_vote_nonce' );

		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		// Validate request data.
		$data = $this->validate(
			array(
				'vote.vote_post_id' => 'required|numeric|exists:posts,ID',
				'vote.vote_type'    => 'required|string|in:upvote,downvote',
			)
		);

		$vote = $data['vote'];

		$vote = $this->voteService->getUserVote( get_current_user_id(), $vote['vote_post_id'], 'vote' );

		// Check if vote exists.
		if ( ! $vote ) {
			return $this->notFound( __( 'Failed to undo vote', 'anspress-question-answer' ) );
		}

		// Check for permission.
		$this->checkPermission( 'vote:delete', array( 'vote' => $vote ) );

		$deleted = $this->voteService->delete( $vote->vote_id );

		if ( ! $deleted ) {
			return $this->serverError( __( 'Failed to undo vote', 'anspress-question-answer' ) );
		}

		return $this->response( array( 'vote' => $vote->toArray() ) );
	}
}
