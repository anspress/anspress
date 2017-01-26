<?php
/**
 * AnsPress notification query class.
 *
 * @package   WordPress/AnsPress-Pro
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 * @since 		1.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * User notification query
 * Query wrapper for fetching notifications.
 *
 * @param array|string $args arguments passed to class.
 * @since 1.0.0
 */
class AnsPress_Notification_Query extends AnsPress_Query {

	/**
	 * Initialize the class.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = [] ) {
		parent::__construct( $args );
	}

	/**
	 * Prepare and fetch notifications from database.
	 */
	public function query() {
		global $wpdb;

		$ref_id_q = '';
		if ( isset( $this->args['ref_id'] ) ) {
			$ref_id_q = $wpdb->prepare( 'AND noti_ref_id = %d', (int) $this->args['ref_id'] );
		}

		$ref_type_q = '';
		if ( isset( $this->args['ref_type'] ) ) {
			$ref_type_q = $wpdb->prepare( 'AND noti_ref_type = %s', sanitize_title( $this->args['ref_type'] ) );
		}

		$verb_q = '';
		if ( isset( $this->args['verb'] ) ) {
			$verb_q = $wpdb->prepare( 'AND noti_verb = %s', $this->args['verb'] );
		}

		$seen_q = '';
		if ( isset( $this->args['seen'] ) ) {
			$seen_q = $wpdb->prepare( 'AND noti_seen = %d', (bool) $this->args['seen'] );
		}

		$order = 'DESC' === $this->args['order'] ? 'DESC' : 'ASC';
		$query = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}ap_notifications WHERE noti_user_id = %d {$ref_id_q} {$ref_type_q} {$verb_q} {$seen_q} ORDER BY noti_date {$order} LIMIT {$this->offset},{$this->per_page}", $this->args['user_id'] );

		$key = md5( $query );
		$this->objects = wp_cache_get( $key, 'ap_notifications' );
		$this->total_count = wp_cache_get( $key . '_count', 'ap_notifications_count' );

		if ( false === $this->objects ) {
			$this->objects = $wpdb->get_results( $query ); // WPCS: DB call okay.
			$this->total_count = $wpdb->get_var( apply_filters( 'ap_notifications_found_rows', 'SELECT FOUND_ROWS()', $this ) );
			wp_cache_set( $key.'_count', $this->total_count, 'ap_notifications_count' );
			wp_cache_set( $key, $this->objects, 'ap_notifications' );
		}

		parent::query();
	}

	public function prefetch() {
		foreach ( (array) $this->notifications as $key => $rep ) {
			$event = $this->events[ $rep->rep_event ];

			if ( ! isset( $this->ids[ $event['parent'] ] ) ) {
				$this->ids[ $event['parent'] ] = [];
			}

			$this->ids[ $event['parent'] ][] = $rep->rep_ref_id;
			$this->notifications[ $key ]->parent = $event['parent'];
			$this->pos[ $rep->rep_ref_id ] = $key;
		}

		$this->prefetch_posts();
		$this->prefetch_comments();
	}

	/**
	 * Pre fetch post contents and append to object.
	 */
	public function prefetch_posts() {
		global $wpdb;

		$ids = array_merge( $this->ids['post'], $this->ids['answer'], $this->ids['question'] );

		$ids = esc_sql( sanitize_comma_delimited( $ids ) );
		$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE ID in ({$ids})" );

		foreach ( (array) $posts as $_post ) {
			$this->notifications[ $this->pos[ $_post->ID ] ]->ref = $_post;
		}
	}

	/**
	 * Pre fetch comments and append data to object.
	 */
	public function prefetch_comments() {
		global $wpdb;

		if ( empty( $this->ids['comment'] ) ) {
			return;
		}

		$ids = esc_sql( sanitize_comma_delimited( $this->ids['comment'] ) );
		$comments = $wpdb->get_results( "SELECT * FROM {$wpdb->comments} WHERE comment_ID in ({$ids})" );

		foreach ( (array) $comments as $_comment ) {
			$this->notifications[ $this->pos[ $_comment->comment_ID ] ]->ref = $_comment;
		}
	}


	/**
	 * Set up the current notification inside the loop.
	 */
	public function the_notification() {
		parent::the_object();
	}
}
