<?php
/**
 * Vote policy.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Vote;

use AnsPress\Classes\AbstractPolicy;
use AnsPress\Modules\Answer\AnswerModel;
use AnsPress\Modules\Question\QuestionModel;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vote policy class.
 */
class VotePolicy extends AbstractPolicy {
	const POLICY_NAME = 'vote';

	/**
	 * List of abilities that the policy can handle.
	 *
	 * @var array
	 */
	public array $abilities = array(
		'create' => array(
			'post',
		),
		'view'   => array(
			'vote',
		),
		'update' => array(
			'vote',
		),
		'delete' => array(
			'vote',
		),
	);

	/**
	 * Perform pre-authorization checks before any specific policy method.
	 *
	 * This method can be used to implement global checks that apply to all actions.
	 * Returning a non-null value will bypass the specific policy checks.
	 *
	 * @param string       $ability The ability being checked (e.g., 'view', 'create').
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The context of the ability.
	 * @return bool|null Null to proceed to specific policy method, or a boolean to override.
	 */
	public function before( string $ability, ?WP_User $user, array $context = array() ): ?bool {
		if ( $user && $user->has_cap( 'manage_options' ) ) {
			return true;
		}

		return null;
	}

	/**
	 * Allow user to view their own vote.
	 *
	 * @param WP_User $user User.
	 * @param array   $context Context array, vote is required.
	 * @return bool
	 */
	public function view( WP_User $user, array $context ): bool {
		$voteType = self::getContextItemField( $context, 'vote', 'vote_type' );

		// Flag vote can be viewed by author.
		if ( VoteModel::FLAG === $voteType && (
			self::isUserIdEmpty( $user ) || ! self::isAuthorOfItem( $user, $context, 'vote', 'vote_user_id' )
		) ) {
			return false;
		}

		return true;
	}

	/**
	 * Allow user to create a vote.
	 *
	 * @param WP_User $user User.
	 * @param array   $context Context array.
	 * @return bool
	 */
	public function create( WP_User $user, array $context ): bool {
		if ( self::isUserIdEmpty( $user ) ) {
			return false;
		}

		$postType = self::getContextItemField( $context, 'post', 'post_type' );

		// Allow if question or answer.
		if ( in_array( $postType, array( QuestionModel::POST_TYPE, AnswerModel::POST_TYPE ), true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Allow user to update their own vote.
	 *
	 * @param WP_User $user User.
	 * @param array   $context Context array, vote is required.
	 * @return bool
	 */
	public function update( WP_User $user, array $context ): bool {
		// At the moment we do not allow user to update their vote.
		return false;
	}

	/**
	 * Allow user to delete their own vote.
	 *
	 * @param WP_User $user User.
	 * @param array   $context Context array, vote is required.
	 * @return bool
	 */
	public function delete( WP_User $user, array $context ): bool {
		if ( self::isUserIdEmpty( $user ) ) {
			return false;
		}

		if ( self::isAuthorOfItem( $user, $context, 'vote', 'vote_user_id' ) ) {
			return true;
		}

		return false;
	}
}
