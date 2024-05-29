<?php
/**
 * Authorization class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Exceptions\GeneralException;
use WP_User;

/**
 * Authorization class.
 */
class Auth {
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
	 * Check if the current user has the given ability for the model.
	 *
	 * @param mixed         $ability Ability.
	 * @param AbstractModel $model Model.
	 * @return bool
	 */
	public static function currentUserCan( $ability, AbstractModel $model ): bool {
		$user = self::user();

		if ( ! $user ) {
			return false;
		}

		return self::check( $ability, $model, $user );
	}

	/**
	 * Check if the user has the given ability for the model.
	 *
	 * @param string        $ability The ability to check.
	 * @param AbstractModel $model The model instance.
	 * @param WP_User|null  $user The user object.
	 * @return bool True if the user has the ability, false otherwise.
	 * @throws GeneralException If the policy does not have the given ability method.
	 */
	public static function check( string $ability, AbstractModel $model = null, ?WP_User $user = null ) {
		$policy = Plugin::getPolicy( get_class( $model ) );

		// Check if the policy has a before method.
		$before = $policy->before( $ability, $user );

		if ( null !== $before ) {
			return $before;
		}

		// Check if the policy has the ability method.
		if ( ! method_exists( $policy, $ability ) ) {
			throw new GeneralException( 'Policy does not have the given ability method.' );
		}

		return $policy->$ability( $user, $model );
	}
}
