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
use AnsPress\Exceptions\GeneralException;
use AnsPress\Exceptions\ValidationException;

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
	 * Vote type.
	 */
	const VOTE = 'vote';

	/**
	 * Flag type.
	 */
	const FLAG = 'flag';

	/**
	 * Create a new vote.
	 *
	 * @param array $data Vote data.
	 *
	 * @return null|VoteModel  Vote model.
	 * @throws ValidationException If validation fails.
	 */
	public function create( array $data ): ?VoteModel {
		if ( empty( $data['vote_user_id'] ) && Auth::isLoggedIn() ) {
			$data['vote_user_id'] = Auth::user()->ID;
		}

		$validator = new Validator(
			$data,
			array(
				'vote_user_id'  => 'required|numeric|exists:users,ID',
				'vote_rec_user' => 'nullable|numeric|exists:users,ID',
				'vote_type'     => 'required|string|max:120',
				'vote_post_id'  => 'required|numeric',
				'vote_value'    => 'required',
			)
		);

		$validated = $validator->validated();

		$vote = new VoteModel();

		$vote->fill( $validated );

		$saved = $vote->save();

		// Update votes count.
		ap_update_votes_count( $data['vote_post_id'] );

		return $saved;
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

		$deleted = $vote->delete();

		// Update votes count.
		ap_update_votes_count( $vote->vote_post_id );

		return $deleted;
	}

	/**
	 * Add post vote.
	 *
	 * @param int    $postId Post ID.
	 * @param string $voteValue Vote value.
	 * @param int    $userId User ID.
	 * @return VoteModel|null
	 * @throws ValidationException If user already voted.
	 */
	public function addPostVote( int $postId, string $voteValue, int $userId = 0 ): ?VoteModel {
		// Check if user already voted.
		$userVote = $this->getUserVote( $userId, $postId, self::VOTE );

		if ( $userVote ) {
			throw new ValidationException( array( '*' => esc_attr__( 'You have already voted on this post.', 'anspress-question-answer' ) ) );
		}

		$postAuthor = get_post_field( 'post_author', $postId );
		return $this->create(
			array(
				'vote_user_id'  => $userId,
				'vote_post_id'  => $postId,
				'vote_type'     => self::VOTE,
				'vote_value'    => '-1' === $voteValue ? '-1' : '1',
				'vote_rec_user' => empty( $postAuthor ) ? null : (int) $postAuthor,
			)
		);
	}

	/**
	 * Get votes.
	 *
	 * @param array $where Where clause.
	 * @param array $args  Arguments.
	 * @return array
	 */
	public function getVotes( array $where, array $args = array() ) {
		global $wpdb;

		$columns = wp_array_slice_assoc(
			$where,
			array( 'vote_post_id', 'vote_type', 'vote_user_id', 'vote_rec_user', 'vote_value' )
		);

		$args = wp_parse_args(
			$args,
			array(
				'limit'  => 10,
				'offset' => 0,
			)
		);

		$tableName = VoteModel::getSchema()->getTableName();

		$sql = "SELECT * FROM {$tableName} WHERE 1=1 ";

		foreach ( $columns as $column => $value ) {
			if ( empty( $value ) ) {
				continue;
			}

			$columnFormat = VoteModel::getSchema()->getColumnFormat( $column );
			$sql         .= $wpdb->prepare( " AND {$column} = {$columnFormat}", $value ); // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.NotPrepared
		}

		$sql .= $wpdb->prepare( ' LIMIT %d, %d', (int) $args['offset'], (int) $args['limit'] );

		return VoteModel::findMany( $sql );
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
	 * Get votes count on a post.
	 *
	 * @param int    $refId Reference ID.
	 * @param string $type Vote type.
	 * @param string $value Vote value.
	 * @return int
	 */
	public function getVotesCount( int $refId, string $type, ?string $value = null ): array {
		global $wpdb;

		$table = VoteModel::getSchema()->getTableName();

		$sql = $wpdb->prepare(
			"SELECT COUNT(*) as total, vote_value FROM {$table} WHERE vote_post_id = %d AND vote_type = %s ", // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.NotPrepared
			$refId,
			$type
		);

		if ( $value ) {
			$sql .= $wpdb->prepare( ' AND vote_value = %s', $value );
		}

		$sql .= ' GROUP BY vote_value';

		$votesGroup = $wpdb->get_results( $sql , ARRAY_A ); // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.NotPrepared

		$votesGroup = wp_list_pluck( $votesGroup, 'total', 'vote_value' );

		if ( 'vote' === $type ) {
			$votesGroup = array(
				'votes_up'   => (int) $votesGroup['1'] ?? 0,
				'votes_down' => (int) $votesGroup['-1'] ?? 0,
				'votes_net'  => (int) ( $votesGroup['1'] ?? 0 ) - ( $votesGroup['-1'] ?? 0 ),
			);
		}

		return $votesGroup;
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

	/**
	 * Get vote data.
	 *
	 * @param int $postId Post ID.
	 * @return array
	 */
	public function getPostVoteData( int $postId ): array {
		$post     = ap_get_post( $postId );
		$userVote = $this->getUserVote( get_current_user_id(), $postId, self::VOTE );

		return array(
			'postId'           => $postId,
			'votesUp'          => $post->votes_up,
			'votesDown'        => $post->votes_down,
			'votesNet'         => $post->votes_net,
			'currentUserVoted' => $userVote ? ( '-1' == $userVote->vote_value ? 'votedown' : 'voteup' ) : null, // @codingStandardsIgnoreLine Universal.Operators.StrictComparisons.LooseEqual
		);
	}

	/**
	 * Add a vote.
	 *
	 * @param int $postId Post ID.
	 * @param int $userId User ID.
	 * @return VoteModel|null
	 */
	public function addPostFlag( int $postId, int $userId = 0 ): ?VoteModel {
		return $this->create(
			array(
				'vote_user_id'  => $userId,
				'vote_post_id'  => $postId,
				'vote_type'     => self::FLAG,
				'vote_value'    => 1,
				'vote_rec_user' => (int) get_post_field( 'post_author', $postId ),
			),
			__( 'You have already flagged this post', 'anspress-question-answer' )
		);
	}

	/**
	 * Remove a flag.
	 *
	 * @param int $postId Post ID.
	 * @param int $userId User ID.
	 * @return bool
	 * @throws GeneralException If no flag found and user is not provided.
	 */
	public function removePostFlag( int $postId, int $userId = 0 ): bool {
		$userId = $userId ?? Auth::getID();

		if ( ! $userId ) {
			throw new GeneralException( esc_attr__( 'User ID is required.', 'anspress-question-answer' ) );
		}

		$vote = $this->getUserVote( $userId, $postId, self::FLAG );

		if ( ! $vote ) {
			throw new GeneralException( esc_attr__( 'No flag found for this post by given user.', 'anspress-question-answer' ) );
		}

		return $this->delete( $vote->ID );
	}

	/**
	 * Remove all flags from a post.
	 *
	 * @param int $postId Post ID.
	 * @return bool
	 */
	public function removeAllPostFlags( int $postId ): bool {
		global $wpdb;

		$deleted = $wpdb->delete( // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->prefix . 'ap_votes',
			array(
				'vote_post_id' => $postId,
				'vote_type'    => self::FLAG,
			),
			array( '%d', '%s' )
		);

		if ( $deleted ) {
			ap_set_flag_count( $postId, $this->getPostFlagsCount( $postId ) );
		}

		return (bool) $deleted;
	}

	/**
	 * Count flag votes.
	 *
	 * @param int $postId Post ID.
	 * @return int
	 */
	public function getPostFlagsCount( int $postId ): int {
		return $this->getVoteCount( $postId, self::FLAG );
	}

	/**
	 * Check if user already flagged a post.
	 *
	 * @param int $postId Post ID.
	 * @param int $userId User ID.
	 * @return bool
	 */
	public function hasUserFlaggedPost( int $postId, ?int $userId = null ): bool {
		$userId = $userId ?? Auth::getID();

		if ( ! $userId ) {
			return false;
		}

		return $this->getUserVote( $userId, $postId, self::FLAG ) ? true : false;
	}

	/**
	 * Update total flagged question and answer count.
	 */
	public function recountAndUpdateTotalFlagged(): void {
		$opt                      = get_option( 'anspress_global', array() );
		$opt['flagged_questions'] = ap_total_posts_count( 'question', self::FLAG );
		$opt['flagged_answers']   = ap_total_posts_count( 'answer', self::FLAG );

		update_option( 'anspress_global', $opt );
	}

	/**
	 * Return total flagged post count.
	 *
	 * @return array
	 */
	public function getTotalFlaggedPost(): array {
		$opt['flagged_questions'] = ap_total_posts_count( 'question', self::FLAG );

		$opt['flagged_answers'] = ap_total_posts_count( 'answer', self::FLAG );

		return array(
			'questions' => $opt['flagged_questions'],
			'answers'   => $opt['flagged_answers'],
		);
	}
}
