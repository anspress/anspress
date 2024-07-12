<?php
/**
 * Answer policy class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractPolicy;
use AnsPress\Classes\PostHelper;
use WP_User;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Answer policy class.
 */
class AnswerPolicy extends AbstractPolicy {
	public const POLICY_NAME = 'answer';

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
			'answer',
		),
		'create'                 => array(
			'question',
		),
		'update'                 => array(
			'answer',
		),
		'delete'                 => array(
			'answer',
		),
		'select'                 => array(
			'answer',
		),
		'unselect'               => array(
			'answer',
		),
		'set_status_to_publish'  => array(
			'answer',
		),
		'set_status_to_moderate' => array(
			'answer',
		),
		'set_status_to_private'  => array(
			'answer',
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
		if ( ! empty( $user?->ID ) && $user->has_cap( 'manage_options' ) ) {
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
		if (
			! empty( $user?->ID ) &&
			! empty( $context['answer'] ) &&
			is_object( $context['answer'] ) &&
			$context['answer']->user_id == $user->user_id // @codingStandardsIgnoreLine
		) {
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
		$postPermission = ap_opt( 'post_answer_per' );

		if ( empty( $context['question'] ) || empty( $context['question']?->ID ) ) {
			return false;
		}

		// Do not allow user to answer the question if it is closed.
		if ( is_post_closed( $context['question'] ) ) {
			return false;
		}

		if ( ! self::isUserIdEmpty( $user ) && self::isAuthorOfItem( $user, $context, 'question', 'post_author' ) ) {
			return true;
		}

		if ( 'anyone' === $postPermission ) {
			return true;
		}

		if ( 'logged_in' === $postPermission && ! self::isUserIdEmpty( $user ) ) {
			return true;
		}

		if ( 'have_cap' === $postPermission && $user?->has_cap( 'ap_new_answer' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can update the specified model.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being updated.
	 * @return bool True if the user is authorized to update the model, false otherwise.
	 */
	public function update( ?WP_User $user, array $context ): bool {
		if ( self::isUserIdEmpty( $user ) ) {
			return false;
		}

		if ( self::isAuthorOfItem( $user, $context, 'answer', 'post_author' ) ) {
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
		if ( self::isUserIdEmpty( $user ) ) {
			return false;
		}

		if ( self::isAuthorOfItem( $user, $context, 'answer', 'post_author' ) ) {
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
		if ( empty( $context['post'] ) ) {
			return false;
		}

		if ( ! empty( $user?->ID ) && $context['post']->post_author == $user->ID ) { // @codingStandardsIgnoreLine
			return true;
		}

		// If post not published then only author can view the post.
		if ( 'publish' === $context['post']->post_status ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can select the answer.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being selected.
	 * @return bool True if the user is authorized to select the model, false otherwise.
	 */
	public function select( ?WP_User $user, array $context ): bool {
		if ( self::isUserIdEmpty( $user ) ) {
			return false;
		}

		if ( empty( $context['answer'] ) ) {
			return false;
		}

		$question = get_post( $context['answer']->post_parent );

		if ( PostHelper::isAuthor( $question, $user->ID ) ) { // @codingStandardsIgnoreLine
			return true;
		}

		return false;
	}

	/**
	 * Determine if the given user can unselect the answer.
	 *
	 * @param WP_User|null $user The current user attempting the action.
	 * @param array        $context The model instance being unselected.
	 * @return bool True if the user is authorized to unselect the model, false otherwise.
	 */
	public function unselect( ?WP_User $user, array $context ): bool {
		if ( self::isUserIdEmpty( $user ) ) {
			return false;
		}

		if ( empty( $context['answer'] ) ) {
			return false;
		}

		$question = get_post( $context['answer']->post_parent );

		if ( PostHelper::isAuthor( $question, $user->ID ) ) { // @codingStandardsIgnoreLine
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
		if ( self::isUserIdEmpty( $user ) ) {
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

		if ( empty( $context['answer'] ) || PostHelper::isModerateStatus( $context['answer'] ) ) {
			return false;
		}

		$isAuthor = self::isAuthorOfItem( $user, $context, 'answer', 'post_author' );

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

		if ( empty( $context['answer'] ) && PostHelper::isPrivateStatus( $context['answer'] ) ) {
			return false;
		}

		$isAuthor = self::isAuthorOfItem( $user, $context, 'answer', 'post_author' );

		if ( $isAuthor || $user->has_cap( 'ap_change_status_other' ) ) {
			return true;
		}

		return false;
	}
}
