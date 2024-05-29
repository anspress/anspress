<?php
/**
 * Abstract policy class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Classes\AbstractModel;
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
	 * Determine if the given user can view the specified model.
	 *
	 * @param WP_User       $user The current user attempting the action.
	 * @param AbstractModel $model The model instance being viewed.
	 * @return bool True if the user is authorized to view the model, false otherwise.
	 */
	public function view( WP_User $user, AbstractModel $model ): bool {
		return false;
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

	/**
	 * Determine if the given user can update the specified model.
	 *
	 * @param WP_User       $user The current user attempting the action.
	 * @param AbstractModel $model The model instance being updated.
	 * @return bool True if the user is authorized to update the model, false otherwise.
	 */
	public function update( WP_User $user, AbstractModel $model ): bool {
		return false;
	}

	/**
	 * Determine if the given user can delete the specified model.
	 *
	 * @param WP_User       $user The current user attempting the action.
	 * @param AbstractModel $model The model instance being deleted.
	 * @return bool True if the user is authorized to delete the model, false otherwise.
	 */
	public function delete( WP_User $user, AbstractModel $model ): bool {
		return false;
	}
}
