<?php
/**
 * Question policy.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Question;

use AnsPress\Classes\AbstractPolicy;
use AnsPress\Classes\PostHelper;
use WP_User;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Question policy class.
 */
class QuestionPolicy extends AbstractPolicy {
	public const POLICY_NAME = 'question';

	/**
	 * Ability list.
	 *
	 * @var array
	 */
	protected array $abilities = array(
		'list'                   => array(
			'question',
		),
		'view'                   => array(
			'question',
		),
		'create'                 => array(),
		'update'                 => array(
			'question',
		),
		'delete'                 => array(
			'question',
		),
		'close'                  => array(
			'question',
		),
		'feature'                => array(
			'question',
		),
		'set_status_to_publish'  => array(
			'question',
		),
		'set_status_to_moderate' => array(
			'question',
		),
		'set_status_to_private'  => array(
			'question',
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
		if ( ! self::isUserIdEmpty( $user ) && $user->has_cap( 'manage_options' ) ) {
			return true;
		}

		return null;
	}

	/**
	 * Determine if the given user can view the specified model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being viewed.
	 * @return bool True if the user is authorized to view the model, false otherwise.
	 */
	public function view( ?WP_User $user, array $context = array() ): bool {
		// Allow all to view publish question.
		if ( 'publish' === self::getContextItemField( $context, 'question', 'post_status' ) ) {
			return true;
		}

		if ( self::isAuthorOfItem( $user, $context, 'question', 'post_author' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can create a new model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The context of the ability.
	 * @return bool True if the user is authorized to create the model, false otherwise.
	 */
	public function create( ?WP_User $user, array $context = array() ): bool {
		if ( ! $user ) {
			return false;
		}

		return true;
	}

	/**
	 * Determine if the given user can update the specified model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being updated.
	 * @return bool True if the user is authorized to update the model, false otherwise.
	 */
	public function update( ?WP_User $user, array $context ): bool {
		if (
			! empty( $user?->user_id ) &&
			! empty( $context['question'] ) &&
			is_object( $context['question'] ) &&
			$context['question']->post_author == $user->ID // @codingStandardsIgnoreLine
			) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can delete the specified model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being deleted.
	 * @return bool True if the user is authorized to delete the model, false otherwise.
	 */
	public function delete( ?WP_User $user, array $context ): bool {

		if (
			! empty( $user?->user_id ) &&
			! empty( $context['question'] ) &&
			is_object( $context['question'] ) &&
			$context['question']->post_author == $user->ID // @codingStandardsIgnoreLine
			) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can list the commenta.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being listed.
	 * @return bool True if the user is authorized to list the model, false otherwise.
	 */
	public function list( ?WP_User $user, array $context ): bool {
		return true;
	}

	/**
	 * Determine if the given user can close the specified model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being closed.
	 * @return bool True if the user is authorized to close the model, false otherwise.
	 */
	public function close( ?WP_User $user, array $context ): bool {
		if (
			! empty( $user?->user_id ) &&
			! empty( $context['question'] ) &&
			is_object( $context['question'] ) &&
			$context['question']->post_author == $user->ID && // @codingStandardsIgnoreLine
			$user->has_cap( 'ap_close_question' )
			) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can feature the specified model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being featured.
	 * @return bool True if the user is authorized to feature the model, false otherwise.
	 */
	public function feature( ?WP_User $user, array $context ): bool {
		if (
			! empty( $user?->user_id ) &&
			! empty( $context['question'] ) &&
			is_object( $context['question'] ) &&
			$context['question']->post_author == $user->ID // @codingStandardsIgnoreLine
			&& $user->has_cap( 'ap_toggle_featured' )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can set the status to publish.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being set to publish.
	 * @return bool True if the user is authorized to set the model to publish, false otherwise.
	 */
	public function set_status_to_publish( ?WP_User $user, array $context ): bool {
		if ( ! empty( $user?->ID ) ) {
			return false;
		}

		// Check user has capability ap_change_status_other.
		if ( $user->has_cap( 'ap_change_status_other' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can set the status to moderate.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being set to moderate.
	 * @return bool True if the user is authorized to set the model to moderate, false otherwise.
	 */
	public function set_status_to_moderate( ?WP_User $user, array $context ): bool {
		if ( self::isUserIdEmpty( $user ) ) {
			return false;
		}

		if ( empty( $context['question'] ) || PostHelper::isModerateStatus( $context['question'] ) ) {
			return false;
		}

		$isAuthor = self::isAuthorOfItem( $user, $context, 'question', 'post_author' );

		// Check user has capability ap_change_status_other.
		if ( $isAuthor || $user->has_cap( 'ap_change_status_other' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can set the status to private.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being set to private.
	 * @return bool True if the user is authorized to set the model to private, false otherwise.
	 */
	public function set_status_to_private( ?WP_User $user, array $context ): bool {
		if ( self::isUserIdEmpty( $user ) ) {
			return false;
		}

		if ( empty( $context['question'] ) && PostHelper::isPrivateStatus( $context['question'] ) ) {
			return false;
		}

		$isAuthor = self::isAuthorOfItem( $user, $context, 'question', 'post_author' );

		if ( $isAuthor || $user->has_cap( 'ap_change_status_other' ) ) {
			return true;
		}

		return false;
	}
}
