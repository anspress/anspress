<?php
/**
 * Award reputation to user based on activities.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @copyright 2014 AnsPress.io & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   WordPress/AnsPress/Email
 *
 * Addon Name:    Reputation
 * Addon URI:     https://anspress.io
 * Description:   Award reputation to user based on activities.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Reputation hooks.
 */
class AnsPress_Reputation_Hooks {

	/**
	 * Init class.
	 */
	public static function init() {
		SELF::register_default_events();

		ap_add_default_options([
			'user_page_title_reputations'  => __( 'Reputations', 'anspress-question-answer' ),
			'user_page_slug_reputations'   => 'reputations',
		]);

		anspress()->add_action( 'ap_option_groups', __CLASS__, 'load_options', 20 );
		anspress()->add_action( 'wp_ajax_ap_save_events', __CLASS__, 'ap_save_events' );
		anspress()->add_action( 'ap_after_new_question', __CLASS__, 'new_question', 10, 2 );
		anspress()->add_action( 'ap_after_new_answer', __CLASS__, 'new_answer', 10, 2 );
		anspress()->add_action( 'ap_untrash_question', __CLASS__, 'new_question', 10, 2 );
		anspress()->add_action( 'ap_trash_question', __CLASS__, 'trash_question', 10, 2 );
		anspress()->add_action( 'ap_before_delete_question', __CLASS__, 'trash_question', 10, 2 );
		anspress()->add_action( 'ap_untrash_answer', __CLASS__, 'new_answer', 10, 2 );
		anspress()->add_action( 'ap_trash_answer', __CLASS__, 'trash_answer', 10, 2 );
		anspress()->add_action( 'ap_before_delete_answer', __CLASS__, 'trash_answer', 10, 2 );
		anspress()->add_action( 'ap_select_answer', __CLASS__, 'select_answer' );
		anspress()->add_action( 'ap_unselect_answer', __CLASS__, 'unselect_answer' );
		anspress()->add_action( 'ap_vote_up', __CLASS__, 'vote_up' );
		anspress()->add_action( 'ap_vote_down', __CLASS__, 'vote_down' );
		anspress()->add_action( 'ap_undo_vote_up', __CLASS__, 'undo_vote_up' );
		anspress()->add_action( 'ap_undo_vote_down', __CLASS__, 'undo_vote_down' );
		anspress()->add_action( 'ap_publish_comment', __CLASS__, 'new_comment' );
		anspress()->add_action( 'ap_unpublish_comment', __CLASS__, 'delete_comment' );
		anspress()->add_filter( 'user_register', __CLASS__, 'user_register' );
		anspress()->add_action( 'delete_user', __CLASS__, 'delete_user' );
		anspress()->add_filter( 'ap_user_display_name', __CLASS__, 'display_name', 10, 2 );
		anspress()->add_filter( 'ap_pre_fetch_question_data', __CLASS__, 'pre_fetch_post' );
		anspress()->add_filter( 'ap_pre_fetch_answer_data', __CLASS__, 'pre_fetch_post' );
		anspress()->add_filter( 'bp_before_member_header_meta', __CLASS__, 'bp_profile_header_meta' );
		anspress()->add_filter( 'ap_user_pages', __CLASS__, 'ap_user_pages' );
		anspress()->add_filter( 'ap_ajax_load_more_reputation', __CLASS__, 'load_more_reputation' );
		anspress()->add_filter( 'ap_bp_nav', __CLASS__, 'ap_bp_nav' );
		anspress()->add_filter( 'ap_bp_page', __CLASS__, 'ap_bp_page', 10, 2 );
	}

	/**
	 * Register reputation options
	 */
	public static function load_options() {
		ap_register_option_group( 'reputation', __( 'Reputation', 'anspress-question-answer' ) );
		ap_register_option_section( 'reputation', 'events', __( 'Events', 'anspress-question-answer' ), 'ap_option_events_view' );

		global $ap_option_tabs;

		$ap_option_tabs['addons']['sections']['profile.php']['fields'][] = array(
			'name'  => 'user_page_title_reputations',
			'label' => __( 'Reputations page title', 'anspress-question-answer' ),
			'desc'  => __( 'Custom title for user profile reputations page', 'anspress-question-answer' ),
		);

		$ap_option_tabs['addons']['sections']['profile.php']['fields'][] = array(
			'name'  => 'user_page_slug_reputations',
			'label' => __( 'Reputations page slug', 'anspress-question-answer' ),
			'desc'  => __( 'Custom slug for user profile reputations page', 'anspress-question-answer' ),
		);
	}

	/**
	 * Register default reputation events.
	 */
	public static function register_default_events() {
		ap_register_reputation_event( 'register', array(
			'points'      => 10,
			'label'       => __( 'Registration', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user account is created', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Registered', 'anspress-question-answer' ),
			'parent' 			=> 'question',
		) );

		ap_register_reputation_event( 'ask', array(
			'points'      => 2,
			'label'       => __( 'Asking', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user asks a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-question',
			'activity'    => __( 'Asked a question', 'anspress-question-answer' ),
			'parent' 			=> 'question',
		) );

		ap_register_reputation_event( 'answer', array(
			'points'      => 5,
			'label'       => __( 'Answering', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user answer a question', 'anspress-question-answer' ),
			'icon'        => 'apicon-answer',
			'activity'    => __( 'Posted an answer', 'anspress-question-answer' ),
			'parent' 			=> 'answer',
		) );

		ap_register_reputation_event( 'comment', array(
			'points'      => 2,
			'label'       => __( 'Commenting', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user comment on question or answer', 'anspress-question-answer' ),
			'icon'        => 'apicon-comments',
			'activity'    => __( 'Commented on a post', 'anspress-question-answer' ),
			'parent' 			=> 'comment',
		) );

		ap_register_reputation_event( 'select_answer', array(
			'points'      => 2,
			'label'       => __( 'Selecting an Answer', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user select an answer for thier question', 'anspress-question-answer' ),
			'icon'        => 'apicon-check',
			'activity'    => __( 'Selected an answer as best', 'anspress-question-answer' ),
			'parent' 			=> 'question',
		) );

		ap_register_reputation_event( 'best_answer', array(
			'points'      => 10,
			'label'       => __( 'Answer selected as best', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user\'s answer selected as best', 'anspress-question-answer' ),
			'icon'        => 'apicon-check',
			'activity'    => __( 'Answer was selected as best', 'anspress-question-answer' ),
			'parent' 			=> 'answer',
		) );

		ap_register_reputation_event( 'received_vote_up', array(
			'points'      => 10,
			'label'       => __( 'Received up vote', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user receive an upvote', 'anspress-question-answer' ),
			'icon'        => 'apicon-thumb-up',
			'activity'    => __( 'Received an upvote', 'anspress-question-answer' ),
		) );

		ap_register_reputation_event( 'received_vote_down', array(
			'points'      => -2,
			'label'       => __( 'Received down vote', 'anspress-question-answer' ),
			'description' => __( 'Points awarded when user receive a down vote', 'anspress-question-answer' ),
			'icon'        => 'apicon-thumb-down',
			'activity'    => __( 'Received a down vote', 'anspress-question-answer' ),
		) );

		ap_register_reputation_event( 'given_vote_up', array(
			'points'      => 0,
			'label'       => __( 'Gives an up vote', 'anspress-question-answer' ),
			'description' => __( 'Points taken from user when they give a vote up', 'anspress-question-answer' ),
			'icon'        => 'apicon-thumb-up',
			'activity'    => __( 'Given an up vote', 'anspress-question-answer' ),
		) );

		ap_register_reputation_event( 'given_vote_down', array(
			'points'      => 0,
			'label'       => __( 'Gives down vote', 'anspress-question-answer' ),
			'description' => __( 'Points taken from user when user give a down vote', 'anspress-question-answer' ),
			'icon'        => 'apicon-thumb-down',
			'activity'    => __( 'Given a down vote', 'anspress-question-answer' ),
		) );
	}

	/**
	 * Save reputation events.
	 */
	public static function ap_save_events() {
		check_ajax_referer( 'ap-save-events', '__nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$events_point = ap_isset_post_value( 'events', 'r' );
		$points = [];

		foreach ( ap_get_reputation_events() as $slug => $event ) {
			if ( isset( $events_point[ $slug ] ) && (int) $events_point[ $slug ] !== (int) $event['points'] ) {
				$points[ sanitize_text_field( $slug ) ] = (int) $events_point[ $slug ];
			}
		}

		update_option( 'anspress_reputation_events', $points );

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_attr__( 'Successfully updated reputation points!', 'anspress-question-answer' ) . '</p></div>';

		wp_die();
	}

	/**
	 * Add reputation for user for new question.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function new_question( $post_id, $_post ) {
		ap_insert_reputation( 'ask', $post_id, $_post->post_author );
	}

	/**
	 * Add reputation for new answer.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function new_answer( $post_id, $_post ) {
		ap_insert_reputation( 'answer', $post_id, $_post->post_author );
	}

	/**
	 * Update reputation when a question is deleted.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post Post object.
	 */
	public static function trash_question( $post_id, $_post ) {
		ap_delete_reputation( 'ask', $post_id, $_post->post_author );
	}

	/**
	 * Update reputation when a answer is deleted.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post Post object.
	 */
	public static function trash_answer( $post_id, $_post ) {
		ap_delete_reputation( 'answer', $post_id, $_post->post_author );
	}

	/**
	 * Award reputation when best answer selected.
	 *
	 * @param object $_post Post object.
	 */
	public static function select_answer( $_post ) {
		ap_insert_reputation( 'best_answer', $_post->ID, $_post->post_author );
		$question = get_post( $_post->post_parent );

		// Award select answer points to question author only.
		if ( get_current_user_id() === $question->post_author ) {
			ap_insert_reputation( 'select_answer', $_post->ID );
		}
	}

	/**
	 * Award reputation when user get an upvote.
	 *
	 * @param object $_post Post object.
	 */
	public static function unselect_answer( $_post ) {
		ap_delete_reputation( 'best_answer', $_post->ID, $_post->post_author );
		$question = get_post( $_post->post_parent );
		ap_delete_reputation( 'select_answer', $_post->ID, $question->post_author );
	}

	/**
	 * Award reputation when user recive an up vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function vote_up( $post_id ) {
		$_post = get_post( $post_id );
		ap_insert_reputation( 'received_vote_up', $_post->ID, $_post->post_author );
		ap_insert_reputation( 'given_vote_up', $_post->ID );
	}

	/**
	 * Award reputation when user recive an down vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function vote_down( $post_id ) {
		$_post = get_post( $post_id );
		ap_insert_reputation( 'received_vote_down', $_post->ID, $_post->post_author );
		ap_insert_reputation( 'given_vote_down', $_post->ID );
	}

	/**
	 * Award reputation when user recive an up vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function undo_vote_up( $post_id ) {
		$_post = get_post( $post_id );
		ap_delete_reputation( 'received_vote_up', $_post->ID, $_post->post_author );
		ap_delete_reputation( 'given_vote_up', $_post->ID );
	}

	/**
	 * Award reputation when user recive an down vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function undo_vote_down( $post_id ) {
		$_post = get_post( $post_id );
		ap_delete_reputation( 'received_vote_down', $_post->ID, $_post->post_author );
		ap_delete_reputation( 'given_vote_down', $_post->ID );
	}

	/**
	 * Award reputation on new comment.
	 *
	 * @param  object $comment WordPress comment object.
	 */
	public static function new_comment( $comment ) {
		ap_insert_reputation( 'comment', $comment->comment_ID, $comment->user_id );
	}

	/**
	 * Undo reputation on deleting comment.
	 *
	 * @param  object $comment Comment object.
	 */
	public static function delete_comment( $comment ) {
		ap_delete_reputation( 'comment', $comment->comment_ID, $comment->user_id );
	}

	/**
	 * Award reputation when user register.
	 *
	 * @param integer $user_id User Id.
	 */
	public static function user_register( $user_id ) {
		ap_insert_reputation( 'register', $user_id, $user_id );
	}

	/**
	 * Delete all reputation of user when user get deleted.
	 *
	 * @param integer $user_id User ID.
	 */
	public static function delete_user( $user_id ) {
		global $wpdb;
		$delete = $wpdb->delete( $wpdb->ap_reputations, [ 'repu_user_id' => $user_id ], [ '%d' ] ); // WPCS: db call okay, db cache okay.

		if ( false !== $delete ) {
			do_action( 'ap_bulk_delete_reputations_of_user', $user_id );
		}
	}

	/**
	 * Append user reputations in display name.
	 *
	 * @param string $name User display name.
	 * @param array  $args Arguments.
	 * @return string
	 */
	public static function display_name( $name, $args ) {
		if ( $args['user_id'] > 0 ) {

			if ( $args['html'] ) {
				$reputation = ap_get_user_reputation_meta( $args['user_id'] );
				return $name . '<a href="' . ap_user_link( $args['user_id'] ) . '?tab=reputations" class="ap-user-reputation" title="' . __( 'Reputation', 'anspress-question-answer' ) . '">' . $reputation . '</a>';
			}
		}

		return $name;
	}

	/**
	 * Pre fetch user reputations.
	 *
	 * @param array $ids Pre fetching ids.
	 */
	public static function pre_fetch_post( $ids ) {
		if ( ! empty( $ids['user_ids'] ) ) {
			ap_get_users_reputation( $ids['user_ids'] );
		}
	}

	/**
	 * Show reputation points of user in BuddyPress profile meta.
	 */
	public static function bp_profile_header_meta() {
		echo '<span class="ap-user-meta ap-user-meta-reputation">' . sprintf( __( '%s Reputation', 'anspress-question-answer' ), ap_get_user_reputation_meta( bp_displayed_user_id() ) ) . '</span>';
	}

	/**
	 * Adds reputations tab in AnsPress authors page.
	 */
	public static function ap_user_pages() {
		anspress()->user_pages[] = array(
			'slug'  => 'reputations',
			'label' => __( 'Reputations', 'anspress-question-answer' ),
			'icon'  => 'apicon-reputation',
			'cb'    => [ __CLASS__, 'reputation_page' ],
			'order' => 5,
		);
	}

	/**
	 * Display reputation tab content in AnsPress author page.
	 */
	public static function reputation_page() {
		$user_id = get_query_var( 'ap_user_id' );

		$reputations = new AnsPress_Reputation_Query( [ 'user_id' => $user_id ] );
		include ap_get_theme_location( 'addons/reputation/index.php' );
	}

	/**
	 * Ajax callback for loading more reputations.
	 */
	public static function load_more_reputation() {
		check_admin_referer( 'load_more_reputation', '__nonce' );

		$user_id = ap_sanitize_unslash( 'user_id', 'r' );
		$paged = ap_sanitize_unslash( 'current', 'r', 1 ) + 1;

		ob_start();
		$reputations = new AnsPress_Reputation_Query( [ 'user_id' => $user_id, 'paged' => $paged ] );
		while ( $reputations->have() ) : $reputations->the_reputation();
			include ap_get_theme_location( 'addons/reputation/item.php' );
		endwhile;
		$html = ob_get_clean();

		$paged = $reputations->total_pages > $paged ? $paged : 0;

		ap_ajax_json( array(
			'success' => true,
			'args'    => [ 'ap_ajax_action' => 'load_more_reputation', '__nonce' => wp_create_nonce( 'load_more_reputation' ), 'current' => (int) $paged, 'user_id' => $user_id ],
			'html'    => $html,
			'element' => '.ap-reputations tbody',
		) );
	}

	/**
	 * Add reputations nav link in BuddyPress profile.
	 *
	 * @param array $nav Nav menu.
	 * @return array
	 */
	public static function ap_bp_nav( $nav ) {
		$nav[] = [ 'name' => __( 'Reputations', 'anspress-question-answer' ), 'slug' => 'reputations' ];
		return $nav;
	}

	/**
	 * Add BuddyPress reputation page callback.
	 *
	 * @param array $cb Callback function.
	 * @param string $template Template.
	 * @param array
	 */
	public static function ap_bp_page( $cb, $template ) {

		if ( 'reputations' === $template ) {
			return [ __CLASS__, 'bp_reputation_page' ];
		}
		return $cb;
	}

	public static function bp_reputation_page() {
		$user_id = bp_displayed_user_id();

		$reputations = new AnsPress_Reputation_Query( [ 'user_id' => $user_id ] );
		include ap_get_theme_location( 'addons/reputation/index.php' );
	}
}

AnsPress_Reputation_Hooks::init();

/**
 * Option event view.
 */
function ap_option_events_view() {
	include ANSPRESS_DIR . '/admin/views/reputation-events.php';
}

