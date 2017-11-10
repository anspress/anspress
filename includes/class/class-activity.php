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
				'label'    => __( 'New question', 'anspress-question-answer' ),
				'ref_type' => 'question',
			),
			'edit_q' => array(
				'label'    => __( 'Edit question', 'anspress-question-answer' ),
				'ref_type' => 'question',
			),
			'new_a' => array(
				'label'    => __( 'New answer', 'anspress-question-answer' ),
				'ref_type' => 'answer',
			),
			'edit_a' => array(
				'label'    => __( 'Edit answer', 'anspress-question-answer' ),
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
	 * @param integer       $action  Activity action id.
	 * @param integer       $ref_id  Reference item id.
	 * @param integer|false $user_id User id for this activity. Default value is current_user_id().
	 * @param integer       $date    Timestamp of activity.
	 * @return boolean|integer Returns last inserted id or `false` on fail.
	 * @since 4.1.2
	 */
	public function insert( $action, $ref_id, $user_id = false, $date = false ) {
		global $wpdb;

		$action  = sanitize_text_field( $action );
		$ref_id  = intval( $ref_id );

		// Check if valid action.
		if ( ! $this->action_exists( $action ) ) {
			return WP_Error( 'not_valid_action', __( 'Not a valid action', 'anspress-question-answer' ) );
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

		return true;
	}

}
