<?php
/**
 * AnsPress activity helper class.
 *
 * @package      AnsPress
 * @subpackage   Activity Class
 * @copyright    Copyright (c) 2013, Rahul Aryan
 * @author       Rahul Aryan <rah12@live.com>
 * @license      GPL-3.0+
 * @since        4.1.2
 */

namespace AnsPress;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use WP_Error;

/**
 * Class which has helper methods for AnsPress activities.
 *
 * @since 4.1.2
 */
class Activity_Helper {

	/**
	 * Refers to a single instance of this class.
	 *
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * The current table name
	 *
	 * @var boolean|string
	 */
	private $table = false;

	/**
	 * The activity actions.
	 *
	 * @var array
	 * @since 4.1.2
	 */
	private $actions = [];

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @return Activity A single instance of this class.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
			self::hooks();
		}

		return self::$instance;
	}

	/**
	 * Constructor for the database class to inject the table name
	 */
	private function __construct() {
		global $wpdb;
		$this->table = $wpdb->ap_activity;

		$this->prepare_actions();
	}

	/**
	 * Register all hooks of activities.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	private static function hooks() {
		anspress()->add_action( 'before_delete_post', __CLASS__, '_before_delete' );
		anspress()->add_action( 'delete_comment', __CLASS__, '_delete_comment' );
		anspress()->add_action( 'delete_user', __CLASS__, '_delete_user' );
		anspress()->add_action( 'ap_ajax_more_activities', __CLASS__, '_ajax_more_activities' );
	}

	/**
	 * Callback for `before_delete_post`.
	 *
	 * Deletes activities related to a post.
	 *
	 * @param integer $post_id Post id.
	 * @return void
	 * @since 4.1.2
	 */
	public static function _before_delete( $post_id ) {
		$_post = ap_get_post( $post_id );

		// Return if not AnsPress cpt.
		if ( ! ap_is_cpt( $_post ) ) {
			return;
		}

		ap_delete_post_activity( $post_id );
	}

	/**
	 * Callback for `delete_comment`.
	 *
	 * Deletes activities related to a comment.
	 *
	 * @param integer $comment_id Comment id.
	 * @return void
	 * @since 4.1.2
	 */
	public static function _delete_comment( $comment_id ) {
		ap_delete_comment_activity( $comment_id );
	}

	public static function _ajax_more_activities() {
		// Check ajax referer.
		if ( ! check_ajax_referer( 'load_activities', '__nonce', false ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		$activities = new \AnsPress\Activity(
			array(
				'paged' => max( 1, ap_isset_post_value( 'paged', 1 ) ),
			)
		);

		ob_start();
		include ap_get_theme_location( 'activities/activities.php' );
		$html = ob_get_clean();

		ap_ajax_json(
			array(
				'success' => true,
				'html'    => $html,
				'cb'      => 'loadedMoreActivities',
			)
		);
	}

	/**
	 * Callback for `delete_user`.
	 *
	 * Deletes activities related to a user.
	 *
	 * @param integer $user_id User id.
	 * @return void
	 * @since 4.1.2
	 */
	public static function _delete_user( $user_id ) {
		ap_delete_user_activity( $user_id );
	}

	/**
	 * Prepare all actions of activity. Numeric keys are used here so that we can save
	 * space while saving in database.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	public function prepare_actions() {
		$defaults = array(
			'new_q'               => array(
				'ref_type' => 'question',
				'verb'     => __( 'Asked question', 'anspress-question-answer' ),
				'icon'     => 'apicon-question',
			),
			'edit_q'              => array(
				'ref_type' => 'question',
				'verb'     => __( 'Edited question', 'anspress-question-answer' ),
				'icon'     => 'apicon-pencil',
			),
			'new_a'               => array(
				'ref_type' => 'answer',
				'verb'     => __( 'Answered question', 'anspress-question-answer' ),
				'icon'     => 'apicon-answer',
			),
			'edit_a'              => array(
				'ref_type' => 'answer',
				'verb'     => __( 'Edited answer', 'anspress-question-answer' ),
				'icon'     => 'apicon-answer',
			),
			'status_publish'      => array(
				'ref_type' => 'post',
				'verb'     => __( 'Changed status to publish', 'anspress-question-answer' ),
				'icon'     => 'apicon-flag',
			),
			'status_future'       => array(
				'ref_type' => 'post',
				'verb'     => __( 'Changed publish date to future', 'anspress-question-answer' ),
				'icon'     => 'apicon-flag',
			),
			'status_moderate'     => array(
				'ref_type' => 'post',
				'verb'     => __( 'Changed status to moderate', 'anspress-question-answer' ),
				'icon'     => 'apicon-flag',
			),
			'status_private_post' => array(
				'ref_type' => 'post',
				'verb'     => __( 'Changed visibility to private', 'anspress-question-answer' ),
				'icon'     => 'apicon-flag',
			),
			'status_trash'        => array(
				'ref_type' => 'post',
				'verb'     => __( 'Trashed', 'anspress-question-answer' ),
				'icon'     => 'apicon-trashcan',
			),
			'featured'            => array(
				'ref_type' => 'question',
				'verb'     => __( 'Marked as featured question', 'anspress-question-answer' ),
				'icon'     => 'apicon-star',
			),
			'closed_q'            => array(
				'ref_type' => 'question',
				'verb'     => __( 'Marked as closed', 'anspress-question-answer' ),
				'icon'     => 'apicon-alert',
			),
			'new_c'               => array(
				'ref_type' => 'comment',
				'verb'     => __( 'Posted new comment', 'anspress-question-answer' ),
				'icon'     => 'apicon-comments',
			),
			'edit_c'              => array(
				'ref_type' => 'comment',
				'verb'     => __( 'Edited comment', 'anspress-question-answer' ),
				'icon'     => 'apicon-comments',
			),
			'selected'            => array(
				'ref_type' => 'answer',
				'verb'     => __( 'Selected answer as best', 'anspress-question-answer' ),
				'icon'     => 'apicon-check',
			),
			'unselected'          => array(
				'ref_type' => 'answer',
				'verb'     => __( 'Unselected an answer', 'anspress-question-answer' ),
				'icon'     => 'apicon-check',
			),
		);

		/**
		 * Filter allows adding new activity actions. This hook is only called once
		 * while class initiate. Hence, later filters will not work. Also make sure
		 * to keep array key length less then 20 characters.
		 *
		 * @param array $actions Originally registered actions.
		 * @since 4.1.2
		 */
		$actions = apply_filters( 'ap_activity_actions', $defaults );

		// We have to check for array key length and must keep it below 20 characters.
		foreach ( $actions as $key => $action ) {
			$this->actions[ $key ] = $action;
		}
	}

	/**
	 * Return all registered actions of AnsPress.
	 *
	 * @return array
	 * @since 4.1.2
	 */
	public function get_actions() {
		return $this->actions;
	}

	/**
	 * Return a single registered action of AnsPress.
	 *
	 * @return array
	 * @since 4.1.2
	 */
	public function get_action( $key ) {
		if ( $this->action_exists( $key ) ) {
			return $this->actions[ $key ];
		}

		return [];
	}

	/**
	 * Check if activity action exists.
	 *
	 * @param string $action Action key, must be below 20 characters.
	 * @return boolean
	 * @since 4.1.2
	 */
	public function action_exists( $action ) {
		// Get only actions key.
		$action_keys = array_keys( $this->actions );
		return in_array( $action, $action_keys, true );
	}

	/**
	 * Insert activity data into the database. `$q_id` argument cannot be
	 * left blank as it is required.
	 *
	 * @param array $args {
	 *      Arguments for insert query.
	 *
	 *      @type string  $action  Registered action key.
	 *      @type integer $q_id    Question id. This is a required argument.
	 *      @type integer $a_id    Answer id. This argument is optional.
	 *      @type integer $c_id    Comment id. This argument is optional.
	 *      @type integer $user_id User id. This is optional.
	 *      @type string  $date    Date of activity. Current time is used by default.
	 * }
	 * @since 4.1.2
	 * @since 4.1.8 Add GMT offset in `current_time`.
	 */
	public function insert( $args = [] ) {
		global $wpdb;

		$args = wp_parse_args(
			$args, array(
				'action'  => '',
				'q_id'    => 0,
				'a_id'    => 0,
				'c_id'    => 0,
				'user_id' => get_current_user_id(),
				'date'    => current_time( 'mysql', true ),
			)
		);

		// Check if question id exists.
		if ( empty( $args['q_id'] ) ) {
			return new WP_Error( 'question_id_empty', __( 'Question id is required.', 'anspress-question-answer' ) );
		}

		// Check if valid action.
		if ( ! $this->action_exists( $args['action'] ) ) {
			return new WP_Error( 'not_valid_action', __( 'Not a valid action', 'anspress-question-answer' ) );
		}

		// split date for validation.
		$mm         = substr( $args['date'], 5, 2 );
		$jj         = substr( $args['date'], 8, 2 );
		$aa         = substr( $args['date'], 0, 4 );
		$valid_date = wp_checkdate( $mm, $jj, $aa, $args['date'] );

		// Validate date.
		if ( ! $valid_date ) {
			return new WP_Error( 'invalid_date', __( 'Invalid date', 'anspress-question-answer' ) );
		}

		// Insert.
		$inserted = $wpdb->insert(
			$this->table,
			array(
				'activity_action'  => $args['action'],
				'activity_q_id'    => $args['q_id'],
				'activity_a_id'    => $args['a_id'],
				'activity_c_id'    => $args['c_id'],
				'activity_user_id' => $args['user_id'],
				'activity_date'    => $args['date'],
			),
			array(
				'%s',
				'%d',
				'%d',
				'%d',
				'%d',
				'%s',
			)
		);

		if ( ! $inserted ) {
			return new WP_Error( 'insert_activity_failed', __( 'Failed to insert activity', 'anspress-question-answer' ) );
		}

		/**
		 * Hook called right after an activity get inserted to database.
		 *
		 * @param array $args  Insert arguments.
		 * @since 4.1.2
		 */
		do_action( 'ap_activity_inserted', $args );

		return $wpdb->insert_id;
	}

	/**
	 * Get activity by activity_id.
	 *
	 * @param integer $activity_id Activity id.
	 * @return boolean|object
	 * @since 4.1.2
	 */
	public function get_activity( $activity_id ) {
		global $wpdb;

		if ( empty( $activity_id ) ) {
			return false;
		}

		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->ap_activity WHERE activity_id = %d", $activity_id ) );
	}

	/**
	 * Delete single and multiple activity from database.
	 *
	 * @param array $where {
	 *      Where clause for delete query.
	 *
	 *      @type string  $action  Activity action name.
	 *      @type integer $q_id    Question id. This is a required argument.
	 *      @type integer $a_id    Answer id. This is optional.
	 *      @type integer $c_id    Comment id. This is optional.
	 *      @type integer $user_id Activity user id. This is optional.
	 *      @type string  $date    Activity date. This is optional.
	 * }
	 * @return WP_Error|integer Return numbers of rows deleted on success.
	 * @since 4.1.2
	 */
	public function delete( $where ) {
		global $wpdb;

		$where = wp_array_slice_assoc( $where, [ 'action', 'a_id', 'c_id', 'q_id', 'user_id', 'date' ] );
		$types = [];
		$cols  = [];

		foreach ( $where as $key => $value ) {
			if ( in_array( $key, [ 'action', 'date' ], true ) ) {
				$types[] = '%s';
			} else {
				$types[] = '%d';
			}

			$cols[ 'activity_' . $key ] = $value;
		}

		// Check if there are columns.
		if ( empty( $cols ) ) {
			return new WP_Error( 'no_cols', __( 'No columns found in where clue', 'anspress-question-answer' ) );
		}

		$deleted = $wpdb->delete( $this->table, $cols, $types ); // DB call okay, DB cache okay.

		if ( false === $deleted ) {
			return new WP_Error( 'failed_to_delete', __( 'Failed to delete activity rows.', 'anspress-question-answer' ) );
		}

		/**
		 * Hook triggered right after an AnsPress activity is deleted from database.
		 *
		 * @param array $where Where clauses.
		 * @since 4.1.2
		 */
		do_action( 'ap_activity_deleted', $where );

		return $deleted;
	}

}
