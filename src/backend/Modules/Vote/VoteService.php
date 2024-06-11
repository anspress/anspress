<?php
/**
 * Vote service.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Vote;

use AnsPress\Classes\AbstractService;
use AnsPress\Classes\Auth;
use AnsPress\Classes\Validator;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vote service.
 *
 * @since 5.0.0
 */
class VoteService extends AbstractService {
	/**
	 * Create a new vote.
	 *
	 * @param array $data Vote data.
	 * @return null|VoteModel  Vote model.
	 */
	public function create( array $data ): ?VoteModel {
		if ( empty( $data['vote_user_id'] ) && Auth::isLoggedIn() ) {
			$data['vote_user_id'] = Auth::user()->ID;
		}

		$validator = new Validator(
			$data,
			array(
				'vote_user_id'  => 'required|numeric|exists:users,ID',
				'vote_rec_user' => 'numeric|exists:users,ID',
				'vote_type'     => 'required|string|max:120',
				'vote_post_id'  => 'required|numeric',
				'vote_value'    => 'required',
			)
		);

		$validated = $validator->validated();

		$vote = new VoteModel();

		$vote->fill( $validated );

		return $vote->save();
	}

	/**
	 * Delete a vote.
	 *
	 * @param int $voteId Vote ID.
	 * @return bool
	 */
	public function delete( int $voteId ): bool {
		$vote = VoteModel::find( $voteId );

		if ( ! $vote ) {
			return false;
		}

		return $vote->delete();
	}

	/**
	 * Get user casted vote.
	 *
	 * @param int    $userId User ID.
	 * @param int    $postId Reference ID.
	 * @param string $type Vote type.
	 * @return VoteModel|null
	 */
	public function getUserVote( int $userId, int $postId, string $type ): ?VoteModel {
		global $wpdb;

		$models = VoteModel::findMany(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}ap_votes WHERE vote_user_id = %d AND vote_post_id = %d AND vote_type = %s LIMIT 1",
				$userId,
				$postId,
				$type
			)
		);

		if ( empty( $models ) ) {
			return null;
		}

		return $models[0];
	}

	/**
	 * Get vote count on a post.
	 *
	 * @param int    $refId Reference ID.
	 * @param string $type Vote type.
	 * @return int
	 */
	public function getVoteCount( int $refId, string $type ): int {
		global $wpdb;

		$vote_count = $wpdb->get_var( // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}ap_votes WHERE vote_post_id = %d AND vote_type = %s",
				$refId,
				$type
			)
		);

		return (int) $vote_count;
	}

	/**
	 * Get vote counts by user.
	 *
	 * @param int    $user_id User ID.
	 * @param string $type Vote type.
	 * @return int
	 */
	public function getVoteCountByUser( int $user_id, string $type ): int {
		global $wpdb;

		$vote_count = $wpdb->get_var( // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}ap_votes WHERE vote_user_id = %d AND vote_type = %s",
				$user_id,
				$type
			)
		);

		return (int) $vote_count;
	}
}
