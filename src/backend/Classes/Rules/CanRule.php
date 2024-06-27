<?php
/**
 * Can rule.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes\Rules;

use AnsPress\Classes\Plugin;
use AnsPress\Classes\Validator;
use AnsPress\Interfaces\ValidationRuleInterface;
use AnsPress\Classes\Auth;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Can rule.
 *
 * @since 5.0.0
 */
class CanRule implements ValidationRuleInterface {

	/**
	 * Ability.
	 *
	 * @var string
	 */
	protected string $ability;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected string $modelName;

	/**
	 * Model ID.
	 *
	 * @var string|null
	 */
	protected ?string $modelId;

	/**
	 * User ID.
	 *
	 * @var int|null
	 */
	protected ?int $userId;

	/**
	 * Constructor.
	 *
	 * @param string      $ability Ability to check.
	 * @param string      $modelName Model name.
	 * @param string|null $modelId Model ID.
	 * @param string|null $userId User ID.
	 */
	public function __construct( string $ability, string $modelName, ?string $modelId = null, ?string $userId = null ) {
		$this->ability   = $ability;
		$this->modelName = $modelName;
		$this->modelId   = $modelId;
		$this->userId    = empty( $userId ) ? Auth::user()?->ID : (int) $userId;
	}

	/**
	 * Get rule name.
	 *
	 * @return string
	 */
	public function ruleName(): string {
		return 'can';
	}

	/**
	 * Get message.
	 *
	 * @return string
	 */
	public function message(): string {
		return 'User is not authorized to perform for :attribute.';
	}

	/**
	 * Validate the rule.
	 *
	 * @param string    $attribute Attributes.
	 * @param mixed     $value Value.
	 * @param array     $parameters Parameters.
	 * @param Validator $validator Validator.
	 * @return bool
	 */
	public function validate( string $attribute, mixed $value, array $parameters, Validator $validator ): bool {
		$policy = Plugin::getPolicy( $this->modelName );

		$user = get_user_by( 'id', $this->userId );

		if ( ! $user ) {
			return false;
		}

		$modelClass = $this->modelName;

		$model = $this->modelId ? $modelClass::find( $this->modelId ) : new $modelClass();

		return Auth::check( $this->ability, $model, $user );
	}
}
