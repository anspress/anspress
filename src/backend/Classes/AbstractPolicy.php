<?php
/**
 * Abstract policy class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Classes\AbstractModel;
use AnsPress\Interfaces\ModelInterface;
use AnsPress\Interfaces\PolicyInterface;
use WP_User;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract policy class.
 *
 * @package AnsPress\Classes
 */
abstract class AbstractPolicy implements PolicyInterface {
	/**
	 * Perform pre-authorization checks before any specific policy method.
	 *
	 * This method can be used to implement global checks that apply to all actions.
	 * Returning a non-null value will bypass the specific policy checks.
	 *
	 * @param string       $ability The ability being checked (e.g., 'view', 'create').
	 * @param WP_User|null $user The current user attempting the action.
	 * @return bool|null Null to proceed to specific policy method, or a boolean to override.
	 */
	public function before( string $ability, ?WP_User $user ): ?bool {
		return null;
	}

	/**
	 * Determine if the given user can create a new model.
	 *
	 * @param WP_User $user The current user attempting the action.
	 * @return bool True if the user is authorized to create the model, false otherwise.
	 */
	public function create( WP_User $user ): bool {
		return false;
	}
}
