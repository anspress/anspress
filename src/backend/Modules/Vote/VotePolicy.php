<?php
/**
 * Vote policy.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Vote;

use AnsPress\Classes\AbstractPolicy;
use AnsPress\Classes\Auth;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vote policy class.
 */
class VotePolicy extends AbstractPolicy {
	/**
	 * Get the policy name.
	 *
	 * @return string
	 */
	public static function getPolicyName(): string {
		return 'vote';
	}

	/**
	 * List of abilities that the policy can handle.
	 *
	 * @var array
	 */
	public array $abilities = array(
		'create' => array(),
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
		if ( $user->has_cap( 'vote:view' ) && $context['vote'] && is_object( $context['vote'] ) && (int) $context['vote']->vote_user_id === (int) $user->ID ) {
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
		if ( $user->has_cap( 'vote:update' ) && $context['vote'] && is_object( $context['vote'] ) && (int) $context['vote']->vote_user_id === (int) $user->ID ) {
			return true;
		}

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
		if ( $user->has_cap( 'vote:delete' ) && $context['vote'] && is_object( $context['vote'] ) && (int) $context['vote']->vote_user_id === (int) $user->ID ) {
			return true;
		}

		return false;
	}
}
