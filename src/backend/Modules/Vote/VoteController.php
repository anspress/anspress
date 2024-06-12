<?php
/**
 * Vote controller.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Vote;

use AnsPress\Classes\AbstractController;
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

		$votes = $this->voteService->getVoteCount( $postId, 'vote' );

		return $this->response( array( 'votes' => $votes ) );
	}

	/**
	 * Create a new vote.
	 */
	public function createVote(): WP_REST_Response {
		if ( ! is_user_logged_in() ) {
			return $this->unauthorized();
		}

		// Check for permission.
		$this->checkPermission( 'vote:create' );

		// Validate request data.
		$data = $this->validate(
			array(
				'post_id'   => 'required|numeric|exists:posts,ID',
				'vote_type' => 'required|string|in:voteup,votedown',
			)
		);

		$postObj = get_post( $data['post_id'] );

		$vote['vote_user_id']  = get_current_user_id();
		$vote['vote_value']    = 'voteup' === $data['vote_type'] ? 1 : -1;
		$vote['vote_type']     = 'vote';
		$vote['vote_rec_user'] = (int) $postObj->post_author;
		$vote['vote_post_id']  = $data['post_id'];

		$vote = $this->voteService->create( $vote );

		if ( ! $vote ) {
			return $this->serverError( __( 'Failed to create vote', 'anspress-question-answer' ) );
		}

		return $this->response(
			array(
				'snackbar' => __( 'Vote added', 'anspress-question-answer' ),
				'voteData' => $this->voteService->getPostVoteData( $vote->vote_post_id ),
			)
		);
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
			return $this->notFound( __( 'Failed to undo vote', 'anspress-question-answer' ) );
		}

		// Check for permission.
		$this->checkPermission( 'vote:delete', array( 'vote' => $vote ) );

		$deleted = $this->voteService->delete( $vote->vote_id );

		if ( ! $deleted ) {
			return $this->serverError( __( 'Failed to undo vote', 'anspress-question-answer' ) );
		}

		return $this->response(
			array(
				'snackbar' => __( 'Vote added', 'anspress-question-answer' ),
				'voteData' => $this->voteService->getPostVoteData( $vote->vote_post_id ),
			)
		);
	}
}
