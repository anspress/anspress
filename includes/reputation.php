<?php
/**
 * AnsPress reputation functions.
 *
 * @package   WordPress/AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 * @since 4.0.0
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Insert reputation.
 *
 * @param string          $event Event type.
 * @param integer         $ref_id Reference ID (post or comment ID).
 * @param integer|boolean $user_id User ID.
 * @return boolean
 * @since 4.0.0
 */
function ap_insert_reputation( $event, $ref_id, $user_id = false ) {

	// Don't do insert notification if defined.
	if ( defined( 'AP_DISABLE_INSERT_REP' ) && AP_DISABLE_INSERT_REP ) {
		return;
	}

	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $user_id ) || empty( $event ) ) {
		return false;
	}

	$insert = $wpdb->insert( // phpcs:ignore WordPress.DB
		$wpdb->ap_reputations,
		array(
			'rep_user_id' => $user_id,
			'rep_event'   => sanitize_text_field( $event ),
			'rep_ref_id'  => $ref_id,
			'rep_date'    => current_time( 'mysql', true ),
		),
		array( '%d', '%s', '%d', '%s' )
	);

	if ( false === $insert ) {
		return false;
	}

	$new_id = $wpdb->insert_id;

	// Update user meta.
	ap_update_user_reputation_meta( $user_id );

	/**
	 * Trigger action after inserting a reputation.
	 */
	do_action( 'ap_insert_reputation', $new_id, $user_id, $event, $ref_id );

	return $new_id;
}

/**
 * Get reputation.
 *
 * @param string          $event Event type.
 * @param integer         $ref_id Reference ID (post or comment ID).
 * @param integer|boolean $user_id User ID.
 * @return array
 */
function ap_get_reputation( $event, $ref_id, $user_id = false ) {
	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$reputation = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->ap_reputations WHERE rep_user_id = %d AND rep_ref_id = %d AND rep_event = %s", $user_id, $ref_id, $event ) );  // phpcs:ignore WordPress.DB

	return $reputation;
}

/**
 * Delete reputation by user_id and event.
 *
 * @param  string          $event Reputation event.
 * @param  integer         $ref_id Reference ID.
 * @param  integer|boolean $user_id User ID.
 * @return boolean|integer
 * @since 4.0.0
 */
function ap_delete_reputation( $event, $ref_id, $user_id = false ) {
	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$deleted = $wpdb->delete( // phpcs:ignore WordPress.DB
		$wpdb->ap_reputations,
		array(
			'rep_user_id' => $user_id,
			'rep_event'   => sanitize_text_field( $event ),
			'rep_ref_id'  => $ref_id,
		),
		array( '%d', '%s', '%d' )
	);

	if ( false === $deleted ) {
		return false;
	}

	// Update user meta.
	ap_update_user_reputation_meta( $user_id );

	/**
	 * Trigger action after deleting a reputation.
	 */
	do_action( 'ap_delete_reputation', $deleted, $user_id, $event );

	return $deleted;
}

/**
 * Register reputation event.
 *
 * @param string $event_slug Event slug.
 * @param array  $args Points to award for this reputation.
 * @since 4.0.0
 */
function ap_register_reputation_event( $event_slug, $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'icon'   => 'apicon-reputation',
			'parent' => 'post',
		)
	);

	$event_slug          = sanitize_title( $event_slug );
	$args['label']       = esc_attr( $args['label'] );
	$args['description'] = esc_html( $args['description'] );
	$args['icon']        = esc_attr( $args['icon'] );

	$custom_points                               = get_option( 'anspress_reputation_events' );
	$args['points']                              = isset( $custom_points[ $event_slug ] ) ? (int) $custom_points[ $event_slug ] : (int) $args['points'];
	anspress()->reputation_events[ $event_slug ] = $args;
}

/**
 * Get all reputation events.
 *
 * @since 4.0.0
 */
function ap_get_reputation_events() {
	if ( ! empty( anspress()->reputation_events ) ) {
		do_action( 'ap_reputation_events' );
	}

	return anspress()->reputation_events;
}

/**
 * Get reputation event points.
 *
 * @param string $event event slug.
 * @return integer
 * @since 4.0.0
 */
function ap_get_reputation_event_points( $event ) {
	$events = ap_get_reputation_events();

	if ( isset( $events[ $event ] ) ) {
		return $events[ $event ]['points'];
	}

	return 0;
}

/**
 * Get reputation event points.
 *
 * @param string $event event slug.
 * @return integer
 * @since 4.0.0
 */
function ap_get_reputation_event_icon( $event ) {
	$events = ap_get_reputation_events();

	if ( isset( $events[ $event ] ) && isset( $events[ $event ]['icon'] ) ) {
		return $events[ $event ]['icon'];
	}

	return 'apicon-reputation';
}

/**
 * Get reputation event activity.
 *
 * @param string $event event slug.
 * @return string
 * @since 4.0.0
 */
function ap_get_reputation_event_activity( $event ) {
	$events = ap_get_reputation_events();

	if ( isset( $events[ $event ] ) && isset( $events[ $event ]['activity'] ) ) {
		return esc_html( $events[ $event ]['activity'] );
	}

	return esc_html( $event );
}

/**
 * Count reputation points of a user.
 *
 * @param integer $user_id ID of user.
 * @param boolean $group Return total count or group by event count.
 * @since 4.0.0
 */
function ap_get_user_reputation( $user_id, $group = false ) {
	global $wpdb;

	$events = $wpdb->get_results( $wpdb->prepare( "SELECT count(*) as count, rep_event  FROM {$wpdb->ap_reputations} WHERE rep_user_id = %d GROUP BY rep_event", $user_id ) ); // phpcs:ignore WordPress.DB

	$event_counts = array();
	foreach ( (array) $events as $count ) {
		$event_counts[ $count->rep_event ] = $count->count;
	}

	$count = array();

	$reputation_events = ap_get_reputation_events();

	if ( ! empty( $reputation_events ) ) {
		foreach ( $reputation_events as $slug => $event ) {
			$count[ $slug ] = isset( $event_counts[ $slug ] ) ? ( (int) $event_counts[ $slug ] * (int) $event['points'] ) : 0;
		}
	}

	if ( false === $group ) {
		return array_sum( $count );
	}

	return $count;
}

/**
 * Get user reputation from user meta.
 *
 * @param integer|bool $user_id User id.
 * @param boolean      $short Shorten count number.
 * @return string
 * @since 4.0.0
 */
function ap_get_user_reputation_meta( $user_id = false, $short = true ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$meta = get_user_meta( $user_id, 'ap_reputations', true ); // @codingStandardsIgnoreLine.

	if ( false === $short ) {
		return $meta;
	}

	return ap_short_num( $meta );
}

/**
 * Update user reputation meta.
 *
 * @param integer|bool $user_id User id.
 */
function ap_update_user_reputation_meta( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	update_user_meta( $user_id, 'ap_reputations', ap_get_user_reputation( $user_id ) ); // @codingStandardsIgnoreLine
}

/**
 * Get reputation of multiple users.
 *
 * @param  array $user_ids User ids.
 * @return array
 * @since  4.0.0
 */
function ap_get_users_reputation( $user_ids ) {
	global $wpdb;
	$user_counts = array();

	foreach ( (array) $user_ids as $id ) {
		$user_counts[ (int) $id ] = array();
	}

	$sanitized = implode( ',', array_keys( $user_counts ) );
	$query     = "SELECT count(*) as count, rep_event, rep_user_id FROM {$wpdb->ap_reputations} WHERE rep_user_id IN ({$sanitized}) GROUP BY rep_event, rep_user_id";

	$events = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB

	foreach ( (array) $events as $count ) {
		if ( empty( $event_counts[ $count->rep_user_id ] ) ) {
			$event_counts[ $count->rep_user_id ] = array();
		}

		$user_counts[ $count->rep_user_id ][ $count->rep_event ] = $count->count;
	}

	$counts     = array();
	$all_events = ap_get_reputation_events();

	foreach ( $user_counts as $user_id => $events ) {
		$counts[ $user_id ] = array();
		foreach ( $all_events as $slug => $event ) {
			$counts[ $user_id ][ $slug ] = isset( $events[ $slug ] ) ? ( (int) $events[ $slug ] * (int) $event['points'] ) : 0;
		}
	}

	return $counts;
}

/**
 * Insert a reputation event to database.
 *
 * @param string $slug        Reputation event unique slug.
 * @param string $label       Reputation event label, must be less than 100 letters.
 * @param string $description Reputation event description.
 * @param int    $points      Signed point value.
 * @param int    $activity    Activity label.
 * @param string $parent_type Parent type.
 * @param string $icon        Reputation event icon.
 *
 * @return int|WP_Error Return insert event id on success and WP_Error on failure.
 * @since 4.3.0
 * @since 4.4.0 Added new argument `$icon` for setting the respective reputation event icon.
 */
function ap_insert_reputation_event( $slug, $label, $description, $points, $activity, $parent_type = '', $icon = '' ) {
	global $wpdb;

	$slug     = sanitize_key( $slug );
	$existing = ap_get_reputation_event_by_slug( $slug );

	if ( $existing ) {
		return new WP_Error( 'already_exits' );
	}

	$inserted = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->ap_reputation_events,
		array(
			'slug'        => $slug,
			'label'       => $label,
			'description' => $description,
			'points'      => $points,
			'activity'    => $activity,
			'parent'      => $parent_type,
			'icon'        => $icon,
		),
		array(
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
		)
	);

	if ( $inserted ) {
		$inserted_id = $wpdb->insert_id;

		// Delete cache.
		wp_cache_delete( 'all', 'ap_get_all_reputation_events' );

		/**
		 * Hook called right after inserting a reputation event.
		 *
		 * @param object $event Reputation event id.
		 * @since 4.3.0
		 */
		do_action( 'ap_inserted_reputation_event', $inserted_id );

		return $inserted_id;
	}

	return new WP_Error( 'failed_to_insert_rep_event' );
}

/**
 * Get a reputation event by slug.
 *
 * @param string $slug Event slug.
 * @return object
 * @since 4.3.0
 */
function ap_get_reputation_event_by_slug( $slug ) {
	global $wpdb;

	return $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare( "SELECT * FROM $wpdb->ap_reputation_events WHERE slug = %s LIMIT 1", $slug )
	);
}

/**
 * Delete a reputation event by slug.
 *
 * @param string $slug Event slug.
 * @return true|WP_Error
 * @since 4.3.0
 */
function ap_delete_reputation_event_by_slug( $slug ) {
	global $wpdb;

	$event = ap_get_reputation_event_by_slug( $slug );

	if ( ! $event ) {
		return new WP_Error( 'rep_event_not_exits' );
	}

	$rows = $wpdb->delete( // phpcs:ignore WordPress.DB
		$wpdb->ap_reputation_events,
		array( 'slug' => $slug ),
		array( '%s' )
	);

	if ( $rows ) {
		/**
		 * Hook called right after deleting a reputation event.
		 *
		 * @param object $event Reputation event object.
		 * @since 4.3.0
		 */
		do_action( 'ap_deleted_reputation_event', $event );

		// Delete cache.
		wp_cache_delete( 'all', 'ap_get_all_reputation_events' );

		return true;
	}

	return new WP_Error( 'failed_to_delete_rep_event' );
}

/**
 * Delete a reputation event by slug.
 *
 * @param array $args Arguments.
 * @return true|WP_Error
 * @since 4.3.0
 */
function ap_get_all_reputation_events( $args = array() ) {
	global $wpdb;

	$args = wp_parse_args(
		$args,
		array(
			'order_by' => 'rep_events_id',
			'order'    => 'ASC',
			'per_page' => 20,
			'offset'   => 0,
		)
	);

	extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

	$columns = array( 'rep_events_id', 'slug', 'label', 'points', 'parent' );

	$order    = 'ASC' === $args['order'] ? 'ASC' : 'DESC';
	$order_by = sanitize_key( $order_by );
	$order_by = in_array( $order_by, $columns, true ) ? $order_by : 'rep_events_id';
	$per_page = (int) $per_page;
	$offset   = $offset < 0 ? 0 : absint( $offset );

	$order_st = $wpdb->prepare( "ORDER BY %s {$order}", $order_by ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$limit_st = $per_page < 0 ? '' : "LIMIT $offset,$per_page";

	$query = "SELECT * FROM $wpdb->ap_reputation_events WHERE 1=1 $order_st $limit_st";

	$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB

	return $results;
}

/**
 * User reputations loop
 * Query wrapper for fetching reputations of a specific user by ID
 *
 * @param array|string $args arguments passed to class.
 *                           @param string $user_id WordPress user_id, default is current user_id
 *                           @param integer $number Numbers of rows to fetch from database, default is 20
 *                           @param integer $offset Rows to offset
 * @since 4.0.0
 */
class AnsPress_Reputation_Query {
	/**
	 * The loop iterator.
	 *
	 * @access public
	 * @var int
	 */
	public $current = -1;

	/**
	 * The number of rows returned by the paged query.
	 *
	 * @access public
	 * @var int
	 */
	public $count;

	/**
	 * Array of users located by the query.
	 *
	 * @access public
	 * @var array
	 */
	public $reputations;

	/**
	 * The reputation object currently being iterated on.
	 *
	 * @access public
	 * @var object
	 */
	public $reputation;

	/**
	 * A flag for whether the loop is currently being iterated.
	 *
	 * @access public
	 * @var bool
	 */
	public $in_the_loop;

	/**
	 * The total number of rows matching the query parameters.
	 *
	 * @access public
	 * @var int
	 */
	public $total_count;

	/**
	 * Items to show per page
	 *
	 * @access public
	 * @var int
	 */
	public $per_page = 20;

	/**
	 * Total pages count.
	 *
	 * @var int
	 */
	public $total_pages = 1;

	/**
	 * Maximum numbers of pages.
	 *
	 * @var int
	 */
	public $max_num_pages = 0;

	/**
	 * Currently paginated page.
	 *
	 * @var int
	 */
	public $paged;

	/**
	 * Current offset.
	 *
	 * @var int
	 */
	public $offset;

	/**
	 * Array of items without any points.
	 *
	 * @var array
	 */
	public $with_zero_points = array();

	/**
	 * Reputation events.
	 *
	 * @var array
	 */
	public $events;

	/**
	 * Ids for prefetching.
	 *
	 * @var array
	 */
	public $ids = array(
		'post'     => array(),
		'comment'  => array(),
		'question' => array(),
		'answer'   => array(),
	);

	/**
	 * Position.
	 *
	 * @var array
	 */
	public $pos = array();

	/**
	 * Arguments.
	 *
	 * @var array
	 */
	public $args = array();

	/**
	 * Initialize the class.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = array() ) {
		$this->events = ap_get_reputation_events();
		$this->get_events_with_zero_points();

		$this->paged  = isset( $args['paged'] ) ? (int) $args['paged'] : 1;
		$this->offset = $this->per_page * ( $this->paged - 1 );

		$this->args = wp_parse_args(
			$args,
			array(
				'user_id' => 0,
				'number'  => $this->per_page,
				'offset'  => $this->offset,
				'order'   => 'DESC',
			)
		);

		$this->per_page = $this->args['number'];
		$this->query();
	}

	/**
	 * Get events having zero points.
	 */
	public function get_events_with_zero_points() {
		foreach ( (array) $this->events as $slug => $args ) {
			if ( 0 === $args['points'] ) {
				$this->with_zero_points[] = $slug;
			}
		}
	}

	/**
	 * Prepare and fetch reputations from database.
	 */
	private function query() {
		global $wpdb;

		$order    = 'DESC' === $this->args['order'] ? 'DESC' : 'ASC';
		$excluded = sanitize_comma_delimited( $this->with_zero_points, 'str' );

		$not_in = '';
		if ( ! empty( $excluded ) ) {
			$not_in = " AND rep_event NOT IN({$excluded})";
		}

		$query = $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->ap_reputations} WHERE rep_user_id = %d{$not_in} ORDER BY rep_date {$order} LIMIT %d,%d", $this->args['user_id'], $this->offset, $this->per_page ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$result            = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB
		$this->total_count = $wpdb->get_var( apply_filters( 'ap_reputations_found_rows', 'SELECT FOUND_ROWS()', $this ) ); // phpcs:ignore WordPress.DB
		$this->reputations = $result;
		$this->total_pages = ceil( $this->total_count / $this->per_page );
		$this->count       = count( $result );
		$this->prefetch();
	}

	/**
	 * Prefetch related items.
	 */
	public function prefetch() {
		foreach ( (array) $this->reputations as $key => $rep ) {
			$event = $this->events[ $rep->rep_event ];

			if ( ! isset( $this->ids[ $event['parent'] ] ) ) {
				$this->ids[ $event['parent'] ] = array();
			}

			$this->ids[ $event['parent'] ][]   = $rep->rep_ref_id;
			$this->reputations[ $key ]->parent = $event['parent'];
			$this->pos[ $rep->rep_ref_id ]     = $key;
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

		if ( ! empty( $ids ) ) {
			$posts = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE ID in ({$ids})" ); // phpcs:ignore WordPress.DB

			foreach ( (array) $posts as $_post ) {
				$this->reputations[ $this->pos[ $_post->ID ] ]->ref = $_post;
			}
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

		$ids      = esc_sql( sanitize_comma_delimited( $this->ids['comment'] ) );
		$comments = $wpdb->get_results( "SELECT * FROM {$wpdb->comments} WHERE comment_ID in ({$ids})" ); // phpcs:ignore WordPress.DB

		foreach ( (array) $comments as $_comment ) {
			$this->reputations[ $this->pos[ $_comment->comment_ID ] ]->ref = $_comment;
		}
	}

	/**
	 * Check if lopp has reputation.
	 *
	 * @return boolean
	 */
	public function have() {
		if ( $this->current + 1 < $this->count ) {
			return true;
		} elseif ( ( $this->current + 1 ) === $this->count ) {
			do_action( 'ap_reputations_loop_end' );
			// Do some cleaning up after the loop.
			$this->rewind_reputation();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Rewind the reputations and reset index.
	 */
	public function rewind_reputation() {
		$this->current = -1;
		if ( $this->count > 0 ) {
			$this->reputation = $this->reputations[0];
		}
	}
	/**
	 * Check if there are reputations.
	 *
	 * @return bool
	 */
	public function has() {
		if ( $this->count ) {
			return true;
		}

		return false;
	}

	/**
	 * Set up the next reputation and iterate index.
	 *
	 * @return object The next reputation to iterate over.
	 */
	public function next_reputation() {
		++$this->current;
		$this->reputation = $this->reputations[ $this->current ];
		return $this->reputation;
	}

	/**
	 * Set up the current reputation inside the loop.
	 */
	public function the_reputation() {
		$this->in_the_loop = true;
		$this->reputation  = $this->next_reputation();

		// Loop has just started.
		if ( 0 === $this->current ) {
			/**
			 * Fires if the current reputation is the first in the loop.
			 */
			do_action( 'ap_reputation_loop_start' );
		}
	}

	/**
	 * Return current reputation event.
	 *
	 * @return string
	 */
	public function get_event() {
		return $this->reputation->rep_event;
	}

	/**
	 * Echo current reputation event.
	 */
	public function the_event() {
		echo wp_kses_post( $this->get_event() );
	}

	/**
	 * Return current reputation points.
	 *
	 * @return integer
	 */
	public function get_points() {
		return ap_get_reputation_event_points( $this->reputation->rep_event );
	}

	/**
	 * Echo current reputation points.
	 * Alice of `get_points`.
	 */
	public function the_points() {
		echo esc_attr( $this->get_points() );
	}

	/**
	 * Return current reputation date date.
	 *
	 * @return string
	 */
	public function get_date() {
		return $this->reputation->rep_date;
	}

	/**
	 * Echo current reputation date.
	 */
	public function the_date() {
		echo esc_attr( ap_human_time( $this->get_date(), false ) );
	}

	/**
	 * Return current reputation icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return ap_get_reputation_event_icon( $this->reputation->rep_event );
	}

	/**
	 * Echo current reputation icon.
	 */
	public function the_icon() {
		echo esc_attr( $this->get_icon() );
	}

	/**
	 * Return current reputation activity.
	 *
	 * @return string
	 */
	public function get_activity() {
		return ap_get_reputation_event_activity( $this->reputation->rep_event );
	}

	/**
	 * Echo current reputation activity.
	 */
	public function the_activity() {
		echo wp_kses_post( $this->get_activity() );
	}

	/**
	 * Out put reference content.
	 */
	public function the_ref_content() {
		if ( in_array( $this->reputation->parent, array( 'post', 'question', 'answer' ), true ) ) {
			echo '<a class="ap-reputation-ref" href="' . esc_url( ap_get_short_link( array( 'ap_p' => $this->reputation->rep_ref_id ) ) ) . '">';

			if ( ! empty( $this->reputation->ref->post_title ) ) {
				echo '<strong>' . esc_html( $this->reputation->ref->post_title ) . '</strong>';
			}

			if ( ! empty( $this->reputation->ref->post_content ) ) {
				echo '<p>' . esc_html( ap_truncate_chars( wp_strip_all_tags( $this->reputation->ref->post_content ), 200 ) ) . '</p>';
			}

			echo '</a>';
		} elseif ( 'comment' === $this->reputation->parent ) {
			echo '<a class="ap-reputation-ref" href="' . esc_url( ap_get_short_link( array( 'ap_c' => $this->reputation->rep_ref_id ) ) ) . '">';
			if ( ! empty( $this->reputation->ref->comment_content ) ) {
				echo '<p>' . esc_html( ap_truncate_chars( wp_strip_all_tags( $this->reputation->ref->comment_content ), 200 ) ) . '</p>';
			}
			echo '</a>';
		}
	}
}
