<?php
/**
 * AnsPress activity class.
 *
 * @package      AnsPress
 * @subpackage   Activity Class
 * @copyright    Copyright (c) 2013, Rahul Aryan
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-3.0+
 * @since        4.1.2
 */

namespace AnsPress;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use WP_Error;
use PC;

/**
 * Class which has helper functions to get activities from the database.
 *
 * @since 4.1.2
 */
class Activity {

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
			self::$instance = new self;
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
	 * Prepare all actions of activity. Numeric keys are used here so that we can save
	 * space while saving in database.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	public function prepare_actions() {
		$defaults = array(
			'new_q' => array(
				'ref_type' => 'question',
			),
			'edit_q' => array(
				'ref_type' => 'question',
			),
			'new_a' => array(
				'ref_type' => 'answer',
			),
			'edit_a' => array(
				'ref_type' => 'answer',
			),
			'status_publish' => array(
				'ref_type' => [ 'answer', 'question' ],
			),
			'status_future' => array(
				'ref_type' => [ 'answer', 'question' ],
			),
			'status_moderate' => array(
				'ref_type' => [ 'answer', 'question' ],
			),
			'status_private_post' => array(
				'ref_type' => [ 'answer', 'question' ],
			),
			'status_trash' => array(
				'ref_type' => [ 'answer', 'question' ],
			),
			'featured' => array(
				'ref_type' => 'question',
			),
			'closed_q' => array(
				'ref_type' => 'question',
			),
			'new_c' => array(
				'ref_type' => [ 'answer', 'question' ],
			),
			'selected' => array(
				'ref_type' => 'answer',
			),
			'unselected' => array(
				'ref_type' => 'answer',
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
	public function get_action() {
		return $this->actions;
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
	 * Insert activity data into the database.
	 *
	 * @param integer       $action      Activity action id.
	 * @param integer       $ref_id      Reference item id.
	 * @param integer|false $user_id     User id for this activity. Default value is current_user_id().
	 * @param integer       $date        Timestamp of activity.
	 * @return boolean|integer Returns   last inserted id or `false` on fail.
	 * @since 4.1.2
	 */
	public function insert( $action, $ref_id, $user_id = false, $date = false ) {
		global $wpdb;

		$action = sanitize_text_field( $action );
		$ref_id = intval( $ref_id );

		// Check if valid action.
		if ( ! $this->action_exists( $action ) ) {
			return new WP_Error( 'not_valid_action', __( 'Not a valid action', 'anspress-question-answer' ) );
		}

		// Get current user id if $user_id is false.
		if ( false === $user_id ) {
			$user_id = get_current_user_id();
		}

		$user_id = intval( $user_id );

		// Check if validate.
		if ( false !== $date ) {
			$mm = substr( $date, 5, 2 );
			$jj = substr( $date, 8, 2 );
			$aa = substr( $date, 0, 4 );
			$valid_date = wp_checkdate( $mm, $jj, $aa, $date );

			if ( ! $valid_date ) {
				return new WP_Error( 'invalid_date' );
			}
		}

		// If no date passed then use current timestamp.
		if ( false === $date ) {
			$date = current_time( 'mysql' );
		}

		// Insert.
		$inserted = $wpdb->insert(
			$this->table,
			array(
				'activity_action'  => $action,
				'activity_user_id' => $user_id,
				'activity_ref_id'  => $ref_id,
				'activity_date'    => $date,
			),
			array(
				'%s', '%d', '%d', '%s',
			)
		);

		if ( ! $inserted ) {
			return new WP_Error( 'insert_activity_failed', __( 'Failed to insert activity', 'anspress-question-answer' ) );
		}

		/**
		 * Hook called right after an activity get inserted to database.
		 *
		 * @param integer $action  Activity action id.
		 * @param integer $user_id User id for this activity.
		 * @param integer $ref_id  Reference item id.
		 * @param integer $date    Timestamp of activity.
		 * @since 4.1.2
		 */
		do_action( 'ap_activity_inserted', $action, $user_id, $ref_id, $date );

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
	 * Add activity relation. Using this function an activity can be linked
	 * with many data like: post, comment, user etc. Relation makes it easy to
	 * fetch data later.
	 *
	 * @param integer $activity_id Activity id.
	 * @param integer $ref_id      Reference id.
	 * @param string  $rel_type    Reference type.
	 * @return WP_Error|false|integer Returns `false` on error and late inserted ID on success.
	 */
	public function add_relation( $activity_id, $ref_id, $rel_type = 'post' ) {
		global $wpdb;
		$activity = $this->get_activity( $activity_id );

		// Check if valid activity.
		if ( ! $activity ) {
			return new WP_Error( 'not_activity', __( 'Not a valid activity.', 'anspress-question-answer' ) );
		}

		// Check ref id not empty.
		if ( empty( $ref_id ) ) {
			return new WP_Error( 'ref_empty', __( 'Ref ID empty.', 'anspress-question-answer' ) );
		}

		$inserted = $wpdb->insert(
			$wpdb->ap_activity_rel,
			array(
				'rel_activity_id' => $activity_id,
				'rel_ref_id'      => $ref_id,
				'rel_type'    => $rel_type,
			),
			array(
				'%d', '%d', '%s',
			)
		);

		if ( ! $inserted ) {
			return false;
		}

		// Return inserted ID.
		return $wpdb->insert_id;
	}

	/**
	 * Delete single and multiple activity from database.
	 *
	 * @param array $where {
	 * 		Where clause.
	 *
	 * 		@type string  $action  Activity action name.
	 * 		@type integer $ref_id  Activity reference id.
	 * 		@type integer $user_id Activity user id.
	 * 		@type string  $date    Activity date.
	 * }
	 * @return boolean
	 * @since 4.1.2
	 */
	public function delete( $where ) {
		global $wpdb;

		$where = wp_array_slice_assoc( $where, [ 'action', 'ref_id', 'user_id', 'date' ] );
		$types = [];
		$cols = [];

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
			return false;
		}

		/**
		 * Hook triggered right after an AnsPress activity is deleted from database.
		 *
		 * @param array $where Where clauses.
		 * @since 4.1.2
		 */
		do_action( 'ap_activity_deleted', $where );

		return true;
	}

}
