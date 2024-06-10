<?php
/**
 * Authorization class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Exceptions\AuthException;
use AnsPress\Exceptions\GeneralException;
use WP_User;

/**
 * Authorization class.
 */
class Auth {
	/**
	 * Auth policies.
	 *
	 * @var array
	 */
	private static array $policies = array();

	/**
	 * Register policies.
	 *
	 * @param array $policies Policies.
	 * @throws GeneralException If the policy class does not exist.
	 * @throws GeneralException If the policy class is not a subclass of Abstract Policy.
	 */
	public static function registerPolicies( array $policies ) {
		// Check policy class exists.
		foreach ( $policies as $policy ) {
			if ( ! class_exists( $policy ) ) {
				throw new GeneralException( 'Policy class does not exist.' );
			}

			if ( ! is_subclass_of( $policy, AbstractPolicy::class ) ) {
				throw new GeneralException( 'Policy class must be a subclass of Abstract Policy.' );
			}

			$policy = new $policy();

			self::$policies[ $policy->getPolicyName() ] = new $policy();
		}
	}

	/**
	 * Check if the user is logged in.
	 *
	 * @return bool
	 */
	public static function isLoggedIn() {
		return is_user_logged_in();
	}

	/**
	 * Return the current user.
	 *
	 * @return WP_User|null
	 */
	public static function user(): WP_User|null {
		if ( ! self::isLoggedIn() ) {
			return null;
		}

		return get_user_by( 'id', get_current_user_id() );
	}

	/**
	 * Check if the current user has the given ability.
	 *
	 * @param string $ability The ability to check.
	 * @param array  $context The context.
	 * @return bool True if the user has the ability, false otherwise.
	 */
	public static function currentUserCan( $ability, array $context = array() ): bool {
		$user = self::user();

		if ( ! $user ) {
			return false;
		}

		return self::check( $ability, $context, $user );
	}

	/**
	 * Check if the user has the given ability.
	 *
	 * @param string       $ability The ability to check.
	 * @param array        $context Context.
	 * @param WP_User|null $user The user object.
	 * @return bool True if the user has the ability, false otherwise.
	 * @throws GeneralException If the ability format is invalid.
	 * @throws GeneralException If the policy does not exist.
	 * @throws GeneralException If the policy does not have the given ability method.
	 */
	public static function check( string $ability, array $context = array(), ?WP_User $user = null ) {
		$abilityParts = explode( ':', $ability );
		$policy       = self::$policies[ $abilityParts[0] ] ?? null;

		if ( count( $abilityParts ) < 2 ) {
			throw new GeneralException( 'Invalid ability format, it must be policyName:ability.' );
		}

		if ( null === $policy ) {
			throw new GeneralException( 'Policy does not exist.' );
		}

		// Check if the policy has a before method.
		$before = $policy->before( $ability, $user, $context );

		if ( null !== $before ) {
			return $before;
		}

		if ( ! $policy->validateContext( $abilityParts[1], $context ) ) {
			throw new GeneralException( 'Invalid context.' );
		}

		if ( ! $user ) {
			return false;
		}

		return $policy->check( $abilityParts[1], $user, $context );
	}

	/**
	 * Check if the user has the given ability for the model and throw an exception if not.
	 *
	 * @param string       $ability The ability to check.
	 * @param array        $context Context.
	 * @param WP_User|null $user The user object.
	 * @return void
	 * @throws AuthException If the user is not authorized to perform the action.
	 */
	public static function checkAndThrow( string $ability, array $context = array(), ?WP_User $user = null ) {
		if ( null === $user ) {
			$user = self::user();
		}

		if ( ! self::check( $ability, $context, $user ) ) {
			throw new AuthException( 'User is not authorized to perform this action.' );
		}
	}
}
