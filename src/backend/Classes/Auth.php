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
use InvalidArgumentException;
use WP_User;

/**
 * Authorization class.
 *
 * @package AnsPress\Classes
 */
class Auth {


	/**
	 * Bindings.
	 *
	 * @var array
	 */
	private array $bindings = array();

	/**
	 * Constructor.
	 *
	 * @param array $policies Policies.
	 * @throws GeneralException If the policy class does not exist.
	 * @throws GeneralException If the policy class is not a subclass of Abstract Policy.
	 */
	public function __construct( array $policies ) {
		// Check policy class exists.
		foreach ( $policies as $policy ) {
			if ( ! class_exists( $policy ) ) {
				throw new GeneralException( 'Policy class does not exist.' );
			}

			if ( ! is_subclass_of( $policy, AbstractPolicy::class ) ) {
				throw new GeneralException( 'Policy class must be a subclass of Abstract Policy.' );
			}

			// Lazy loading.
			$this->bindings[ $policy::POLICY_NAME ] = fn() => new $policy();
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
	 * Return the ID of the current user.
	 *
	 * @return int
	 */
	public static function getID(): int {
		return (int) get_current_user_id();
	}

	/**
	 * Check if the current user has the given ability.
	 *
	 * @param string $ability The ability to check.
	 * @param array  $context The context.
	 * @return bool True if the user has the ability, false otherwise.
	 */
	public static function currentUserCan( string $ability, array $context = array() ): bool {
		$user = self::user();

		$instance = Plugin::get( self::class );

		return $instance->check( $ability, $context, $user );
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
	public function check( string $ability, array $context = array(), ?WP_User $user = null ) {
		$abilityParts = explode( ':', $ability );

		$policyName = $abilityParts[0];

		if ( count( $abilityParts ) < 2 ) {
			throw new GeneralException( 'Invalid ability format, it must be policyName:ability.' );
		}

		if ( ! isset( $this->bindings[ $policyName ] ) ) {
			throw new GeneralException( 'Policy does not exist.' );
		}

		$policy = $this->bindings[ $policyName ];

		if ( ! $policy instanceof AbstractPolicy ) {
			$this->bindings[ $policyName ] = $policy();
		}

		$policy = $this->bindings[ $policyName ];

		// Check if the policy has a before method.
		$before = $policy->before( $ability, $user, $context );

		if ( null !== $before ) {
			return $before;
		}

		if ( ! $policy->isValidAbility( $abilityParts[1] ) ) {
			throw new GeneralException( 'Policy ' . esc_attr( $abilityParts[0] ) . ' does not have the ' . esc_attr( $abilityParts[1] ) . ' ability registered.' );
		}

		if ( ! $policy->validateContext( $abilityParts[1], $context ) ) {
			throw new GeneralException( 'Invalid context passed to ' . esc_attr( $ability ) . ' policy.' );
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

		$instance = Plugin::get( self::class );
		if ( ! $instance->check( $ability, $context, $user ) ) {
			throw new AuthException( 'User is not authorized to perform this action.' );
		}
	}

	/**
	 * Get the abilities for the participant role.
	 *
	 * @return array
	 * @todo Replcae this with anspress:ability before release.
	 */
	public static function getParticipantAbilities(): array {
		$abilities = array(
			'ap_read_question',
			'ap_read_answer',
			'ap_read_comment',
			'ap_new_question',
			'ap_new_answer',
			'ap_new_comment',
			'ap_edit_question',
			'ap_edit_answer',
			'ap_edit_comment',
			'ap_delete_question',
			'ap_delete_answer',
			'ap_delete_comment',
			'ap_vote_up',
			'ap_vote_down',
			'ap_vote_flag',
			'ap_vote_close',
			'ap_upload_cover',
			'ap_change_status',
		);

		/**
		 * Filter the abilities for the participant role.
		 *
		 * @param array $abilities Abilities.
		 * @return array
		 * @since 5.0.0
		 */
		return apply_filters( 'anspress/auth/participant/abilities', $abilities );
	}

	/**
	 * Get the abilities for the moderator role.
	 *
	 * @return array
	 * @todo Replcae this with anspress:ability before release.
	 */
	public static function getModeratorAbilities(): array {
		$abilities = array(
			'ap_edit_others_question',
			'ap_edit_others_answer',
			'ap_edit_others_comment',
			'ap_delete_others_question',
			'ap_delete_others_answer',
			'ap_delete_others_comment',
			'ap_delete_post_permanent',
			'ap_view_private',
			'ap_view_moderate',
			'ap_view_future',
			'ap_change_status_other',
			'ap_approve_comment',
			'ap_no_moderation',
			'ap_restore_posts',
			'ap_toggle_featured',
			'ap_toggle_best_answer',
			'ap_close_question',
		);

		/**
		 * Filter the abilities for the moderator role.
		 *
		 * @param array $abilities Abilities.
		 * @return array
		 * @since 5.0.0
		 */
		return apply_filters( 'anspress/auth/moderator/abilities', $abilities );
	}

	/**
	 * Get all abilities.
	 *
	 * @return array
	 */
	public static function allAbilities(): array {
		return array_merge( self::getParticipantAbilities(), self::getModeratorAbilities() );
	}

	/**
	 * Check if the given ability is valid.
	 *
	 * @param string $ability The ability to check.
	 * @return bool True if the ability is valid, false otherwise.
	 */
	public static function isValidAbility( string $ability ): bool {
		return in_array( $ability, self::allAbilities(), true );
	}
}
