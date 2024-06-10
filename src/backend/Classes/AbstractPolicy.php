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
use InvalidArgumentException;
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
	 * Policy name.
	 *
	 * @var string
	 */
	public $policyName = '';

	/**
	 * List of abilities that the policy can handle.
	 *
	 * @var array
	 */
	public array $abilities = array();

	/**
	 * Constructor.
	 *
	 * @param string $policyName Policy name.
	 * @return void
	 */
	public function __construct( string $policyName ) {
		$this->policyName = $policyName;
	}

	/**
	 * Validation for the context of the ability.
	 *
	 * @param string $ability Ability name.
	 * @param array  $context Context arr.
	 * @return bool
	 */
	public function validateContext( string $ability, array $context = array() ): bool {
		if ( ! isset( $this->abilities[ $ability ] ) ) {
			return false;
		}

		// If ability context is empty, skip validation.
		if ( empty( $this->abilities[ $ability ] ) ) {
			return true;
		}

		// Check if does not have any required keys then return false.
		if ( ! empty( array_diff( $this->abilities[ $ability ], array_keys( $context ) ) ) ) {
			return false;
		}

		return true;
	}

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
		return null;
	}

	/**
	 * Determine if the given user can create a new model.
	 *
	 * @param string  $ability The ability being checked (e.g., 'view', 'create').
	 * @param WP_User $user The current user attempting the action.
	 * @param array   $context The context of the ability.
	 * @return bool True if the user is authorized to create the model, false otherwise.
	 * @throws InvalidArgumentException If the ability is invalid.
	 */
	public function check( string $ability, WP_User $user, array $context = array() ): bool {
		// Check if the ability is valid.
		if ( ! in_array( $ability, array_keys( $this->abilities ), true ) ) {
			throw new InvalidArgumentException( 'Invalid ability provided.' );
		}

		// If ability has a method then call it.
		if ( method_exists( $this, $ability ) ) {
			return $this->$ability( $user, $context );
		}

		// Check if the user has the ability to perform the action.
		if ( ! $user->has_cap( $this->policyName . ':' . $ability ) ) {
			return false;
		}

		return true;
	}
}
