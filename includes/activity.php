<?php
/**
 * AnsPress activity handler
 *
 * @package  	AnsPress
 * @license  	http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     	http://anspress.io
 * @since 		2.0
 */

/**
 * AnsPress activity handler
 */
class AnsPress_Activity_Query
{
	/**
	 * The loop iterator.
	 *
	 * @access public
	 * @var integer
	 */
	var $current_activity = -1;

	/**
	 * The number of activities returned by the paged query.
	 *
	 * @access public
	 * @var int
	 */
	var $activity_count;

	/**
	 * Array of activities located by the query.
	 *
	 * @access public
	 * @var array
	 */
	var $activities;

	/**
	 * The activity object currently being iterated on.
	 *
	 * @access public
	 * @var object
	 */
	var $activity;

	/**
	 * A flag for whether the loop is currently being iterated.
	 *
	 * @access public
	 * @var bool
	 */
	var $in_the_loop;

	/**
	 * The total number of activities matching the query parameters.
	 *
	 * @access public
	 * @var int
	 */
	var $total_activity_count;

	/**
	 * Items to show per page
	 *
	 * @access public
	 * @var int
	 */
	var $per_page;

	/**
	 * Total pages
	 *
	 * @access public
	 * @var int
	 */
	var $total_pages = 1;

	/**
	 * Paged
	 *
	 * @access public
	 * @var int
	 */
	var $paged;

	/**
	 * offset
	 *
	 * @access public
	 * @var int
	 */
	var $offset;

	var $query;
	var $meta_query_sql;

	/**
	 * Initialize the class
	 * @param string|array $args
	 */
	public function __construct($args = '') {
		global $wpdb;
		ap_wpdb_tables();
		$this->per_page = 20;

		// Grab the current page number and set to 1 if no page number is set.
		$this->paged = isset( $args['paged'] ) ? (int) $args['paged'] : 1;

		$this->offset = $this->per_page * ($this->paged - 1);

		$this->args = wp_parse_args( $args, array(
			'number' 	=> $this->per_page,
			'offset' 	=> $this->offset,
			'orderby' 	=> 'date',
			'order' 	=> 'DESC',
		));

		// Process meta query arguments.
		if ( isset( $this->args['meta_query'] ) ) {
			$meta_query = new WP_Meta_Query();
			$meta_query->parse_query_vars( $this->args['meta_query'] );
			$this->meta_query_sql = $meta_query->get_sql(
				'ap_activity',
				$wpdb->ap_activity,
				'id',
				null
			);
		}

		$this->parse_query();

		$this->total_activity_count = $wpdb->get_var( apply_filters( 'ap_found_activity_query', 'SELECT FOUND_ROWS()', $this ) );

	}

	/**
	 * Build the MySql query
	 */
	public function parse_query() {
		global $wpdb;

		$query = 'SELECT SQL_CALC_FOUND_ROWS * from '.$wpdb->prefix.'ap_activity ';

		if ( isset( $this->meta_query_sql['join'] ) ) {
			$query .= $this->meta_query_sql['join'];
		}

		$query .= $this->where_clauses( $this->args );

		if ( isset( $this->meta_query_sql['where'] ) ) {
			$query .= $this->meta_query_sql['where'];
		}

		$query .= $this->order_clauses( $this->args );

		if ( isset( $this->args['numbers'] ) ) {
			$query .= ' LIMIT '.$this->per_page;
		}

		$this->query = $query;

		$cache_key = md5( $this->query );

		$cached_result = wp_cache_get( $cache_key, 'ap_activities' );

		if ( false !== $cached_result ) {
			$this->activities = $cached_result;
		} else {
			$this->activities = $wpdb->get_results( $query );
			$this->activity_count = $wpdb->num_rows;
			$this->cache_activity();
		}

	}

	/**
	 * Build the where clause for mysql query
	 * @return string
	 */
	public function where_clauses($args) {
		$where = ' WHERE 1=1';

		if ( isset( $args['id'] ) ) {

			$id 	= (int) $args['id'];
			$where .= " AND id = $id";

		} else {

			if ( isset( $args['user_id'] ) ) {

				$ids 	= (int) $args['user_id'];
				$where .= " AND user_id = $ids";

			} elseif ( isset( $args['user_id__in'] ) ) {

				if ( is_array( $args['user_id__in'] ) && count( $args['user_id__in'] ) > 0 ) {

					$ids = '';
					foreach ( $args['user_id__in'] as $u ) {
						$ids .= (int) $u .', ';
					}

					$ids = rtrim( $ids, ', ' );
					$where .= " AND user_id IN ($ids)";

				} else {
					$ids 	= (int) $args['user_id__in'];
					$where .= " AND user_id IN($ids)";
				}
			}

			// Activity status.
			if ( isset( $args['status'] ) ) {
				if ( is_array( $args['status'] ) && count( $args['status'] ) > 0 ) {

					$status = '';
					foreach ( $args['status'] as $s ) {
						$status .= sanitize_text_field( strip_tags( $s ) ) .', ';
					}

					$status = rtrim( $status, ', ' );
					$where .= " AND status IN ($status)";

				} else {
					$status 	= sanitize_text_field( strip_tags( $args['status'] ) );
					$where 		.= " AND status =$status";
				}
			}

			// Activity type.
			if ( isset( $args['type'] ) ) {
				if ( is_array( $args['type'] ) && count( $args['type'] ) > 0 ) {

					$type = '';
					foreach ( $args['type'] as $s ) {
						$type .= sanitize_text_field( strip_tags( $s ) ) .', ';
					}

					$type = rtrim( $type, ', ' );
					$where .= " AND type IN ($type)";

				} else {
					$type 		= sanitize_text_field( strip_tags( $args['type'] ) );
					$where 		.= " AND type =$type";
				}
			}

			if ( isset( $args['question_id'] ) ) {
				$question_id 		= (int) $args['question_id'];
				$where 		.= " AND question_id ='$question_id'";
			}

			if ( isset( $args['item_id'] ) ) {
				$item_id 	= sanitize_text_field( strip_tags( $args['item_id'] ) );
				$where 			.= " AND item_id ='$item_id'";
			}
		}

		return $where;
	}

	/**
	 * Order query
	 * @param  array $args
	 * @return string
	 */
	public function order_clauses($args) {
		$order = '';

		if ( ! isset( $args['orderby'] ) ) {
			$order .= ' ORDER BY id';
		} else {
			$orderby 		= sanitize_text_field( strip_tags( $args['orderby'] ) );
			$orderby_field 	= array( 'id', 'question_id', 'item_id', 'updated', 'created' );
			$orderby 		= in_array( $orderby, $orderby_field ) ? $orderby : 'id';
			$order 			.= " ORDER BY $orderby";
		}

		// Order.
		if ( ! isset( $args['order'] ) ) {
			$order .= ' DESC';
		} else {
			$order .= 'ASC' == $args['order'] ? ' ASC' : ' DESC';
		}

			return $order;
	}

	/**
	 * Cache activity
	 */
	public function cache_activity() {
		if ( $this->activities && count( $this->activities ) > 0 ) {
			foreach ( $this->activities as $activity ) {
				wp_cache_set( $activity->id, 'ap_activity' );
			}
		}
	}

	public function activities() {

		if ( $this->current_activity + 1 < $this->activity_count ) {
			return true;
		} elseif ( $this->current_activity + 1 == $this->activity_count ) {

			do_action( 'ap_activity_loop_end' );
			// Do some cleaning up after the loop
			$this->rewind_activity();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Check if there are activities in loop
	 * @return bool
	 */
	public function has_activities() {
	    if ( $this->activity_count ) {
	        return true; }

	    return false;
	}

	/**
	 * Set up the next activity and iterate index.
	 * @return object The next activity to iterate over.
	 */
	public function next_activity() {
		$this->current_activity++;
		$this->activity = $this->activities[ $this->current_activity ];

		return $this->activity;
	}

	/**
	 * Rewind the users and reset user index.
	 */
	public function rewind_activity() {
		$this->current_activity = -1;
		if ( $this->activity_count > 0 ) {
			$this->activity = $this->activities[0];
		}
	}

	/**
	 * Set up the current activity inside the loop.
	 */
	public function the_activity() {
		global $ap_the_activity;

		$this->in_the_loop  = true;
		$this->activity         = $this->next_activity();
		$ap_the_activity        = $this->activity;

		// loop has just started
		if ( 0 == $this->current_activity ) {

			/**
			 * Fires if the current activity is the first in the loop.
			 */
			do_action( 'ap_activity_loop_start' );
		}

	}
}

/**
 * Set AnsPress tables in $wpdb if not already set.
 */
function ap_wpdb_tables(){
	global $wpdb;

	// Add ap_activity in $wpdb if not defined already.
	if ( ! isset( $wpdb->ap_activity ) ) {
		$wpdb->ap_activity = $wpdb->prefix . 'ap_activity';
	}

	// Add ap_activitymeta in $wpdb if not defined already.
	if ( ! isset( $wpdb->ap_activitymeta ) ) {
		$wpdb->ap_activitymeta = $wpdb->prefix . 'ap_activitymeta';
	}
}

/**
 * Insert and update AnsPress activity
 * If id is passed then existing activity will be updated.
 *
 * @param  array $args {
 *     Array of arguments.
 *     @type integer $id      			Activity id. If activity id is passed then activity will be updated.
 *     @type integer $user_id      		Main user ID of this activity. Default is current user.
 *     @type integer $secondary_user    Secondary user id.
 *     @type string  $secondary_user    Type of activity.
 *     @type string  $content    	    Activity content.
 *     @type integer $item_id    	    Related item id.
 *     @type integer $secondary_id	    Related secondary item id.
 *     @type string  $created	    	Created date.
 *     @type string  $updated	    	Updated date.
 * }
 * @return boolean|integer
 */
function ap_insert_activity( $args ) {
	global $wpdb;
	$user_id = get_current_user_id();

	$defaults = array(
		'user_id' => $user_id,
		'secondary_user' => 0,
		'type' => '',
		'parent_type' => 'post',
		'status' => 'publish',
		'content' => '',
		'permalink' => '',
		'question_id' => '',
		'answer_id' => '',
		'item_id' => '',
		'created' => '',
		'updated' => '',
	);

	$activity_id = isset( $args['id'] ) ? intval( $args['id'] ) : false;

	$args = wp_parse_args( $args, $defaults );

	$args['user_id'] = intval( $args['user_id'] );
	$args['secondary_user'] = intval( $args['secondary_user'] );
	$args['type'] = sanitize_text_field( wp_unslash( $args['type'] ) );
	$args['parent_type'] = sanitize_text_field( wp_unslash( $args['parent_type'] ) );
	$args['status'] = sanitize_text_field( wp_unslash( $args['status'] ) );
	$args['content'] = esc_html( wp_kses_post( wp_unslash( $args['content'] ) ) );
	$args['permalink'] = esc_url( $args['permalink'] );
	$args['question_id'] = (int) $args['question_id'];
	$args['answer_id'] = (int) $args['answer_id'];
	$args['item_id'] = (int) $args['item_id'];

	if ( empty( $args['created'] ) || '0000-00-00 00:00:00' == $args['created'] ) {
		$args['created'] = current_time( 'mysql' );
	} else {
		$args['created'] = $args['created'];
	}

	if ( empty( $args['updated'] ) || '0000-00-00 00:00:00' == $args['updated'] ) {
		$args['updated'] = current_time( 'mysql' );
	} else {
		$args['updated'] = $args['updated'];
	}

	// Remove extra args.
	$coulmns = array_intersect_key( $args, $defaults );

	$format = array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s' );

	if ( false === $activity_id ) {
		$row = $wpdb->insert(
			$wpdb->ap_activity,
			$coulmns,
			$format
		);
		if ( false !== $row ) {
			do_action( 'ap_after_inserting_activity', $wpdb->insert_id, $args );
			return $wpdb->insert_id;
		}
	} else {

		$row = $wpdb->update(
			$wpdb->ap_activity,
			$coulmns,
			array( 'id' => $activity_id ),
			$format,
			array( '%d' )
		);

		if ( false !== $row ) {
			wp_cache_delete( $activity_id, 'ap_activity' );
			do_action( 'ap_after_updating_activity', $id, $args );
			return $row;
		}
	}

	return false;
}

/**
 * Return activity action title
 * @param  array $args Activity arguments.
 * @return string
 * @since 2.4
 */
function ap_get_activity_action_title($args) {
	$type = $args['type'];

	$content = '';

	$primary_user_link = ap_user_link( $args['user_id'] );
	$primary_user_name = ap_user_display_name( array( 'user_id' => $args['user_id'] ) );
	$user = '<a href="'. $primary_user_link .'">'.$primary_user_name.'</a>';

	switch ( $type ) {

		case 'new_question':
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s asked question %s', 'ap' ), $user, $question_title );
			break;

		case 'new_answer':
			$answer_title = '<a href="'. get_permalink( $args['item_id'] ) .'">'. get_the_title( $args['item_id'] ) .'</a>';
			$content .= sprintf( __( '%s answered on %s', 'ap' ), $user, $answer_title );
			break;

		case 'new_comment':
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$comment = '<span class="ap-comment-excerpt"><a href="'. get_comment_link( $args['item_id'] ) .'">'. get_comment_excerpt( $args['item_id'] ) .'</a></span>';
			$content .= sprintf( __( '%s commented on question %s %s', 'ap' ), $user, $question_title, $comment );
			break;

		case 'new_comment_answer':
			$title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$comment = '<span class="ap-comment-excerpt"><a href="'. get_comment_link( $args['item_id'] ) .'">'. get_comment_excerpt( $args['item_id'] ) .'</a></span>';
			$content .= sprintf( __( '%s commented on answer %s %s', 'ap' ), $user, $title, $comment );
			break;

		case 'edit_question':
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s edited question %s', 'ap' ), $user, $question_title );
			break;

		case 'edit_answer':
			$answer_title = '<a href="'. get_permalink( $args['item_id'] ) .'">'. get_the_title( $args['item_id'] ) .'</a>';
			$content .= sprintf( __( '%s edited answer %s', 'ap' ), $user, $answer_title );
			break;

		case 'edit_comment':
			$comment = '<a href="'. get_comment_link( $args['item_id'] ) .'">'. get_comment_excerpt( $args['item_id'] ) .'</a>';
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s edited comment on %s %s', 'ap' ), $user, $question_title, $comment );
			break;

		case 'answer_selected':
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s selected best answer for %s', 'ap' ), $user, $question_title );
			break;

		case 'answer_unselected':
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s unselected best answer for question %s', 'ap' ), $user, $question_title );
			break;

		case 'status_updated':
			$title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s updated status of question %s', 'ap' ), $user, $title );
			break;

		case 'status_updated_answer':
			$title = '<a href="'. get_permalink( $args['item_id'] ) .'">'. get_the_title( $args['item_id'] ) .'</a>';
			$content .= sprintf( __( '%s updated status of answer %s', 'ap' ), $user, $title );
			break;
	}

	return apply_filters( 'ap_activity_action_title', $content, $args );
}

/**
 * Sanitize, format and then insert activity
 * @param  array $args Activity arguments.
 * @return boolean
 */
function ap_new_activity( $args = array() ) {
	$user_id = get_current_user_id();

	$defaults = array(
		'user_id' => $user_id,
		'secondary_user' => 0,
		'type' => '',
		'parent_type' => 'post',
		'status' => 'publish',
		'content' => '',
		'permalink' => '',
		'question_id' => '',
		'answer_id' => '',
		'item_id' => '',
		'created' => '',
		'updated' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( isset( $args['type'] ) && empty( $args['content'] ) ) {
		$args['content'] = ap_get_activity_action_title( $args );
	}

	return ap_insert_activity( $args );
}

/**
 * Get an activity by ID.
 * @param  	integer $id Activity id.
 * @return 	boolean|object
 * @since 	2.4
 */
function ap_get_activity($id) {
	global $wpdb;

	if ( ! is_integer( $id ) ) {
		return false;
	}

	$row = wp_cache_get( $id, 'ap_activity' );

	if ( false !== $row ) {
		return $row;
	}

	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->ap_activity WHERE id = %d", $id ) );

	wp_cache_set( $id, $row, 'ap_activity' );

	return $row;
}


/**
 * Get the latest history html
 * @param  integer $post_id Post ID.
 * @return string
 */
function ap_post_activity_meta( $post_id = false ) {
	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	return get_post_meta( $post_id, '__ap_activity', true );
}

/**
 * Activity type to human readable title.
 * @param  string $type Activity type.
 * @return string
 */
function ap_activity_short_title( $type ) {
	$title = array(
		'new_question' 		=> __( 'asked', 'ap' ),
		'new_answer' 		=> __( 'answered', 'ap' ),
		'new_comment' 		=> __( 'commented', 'ap' ),
		'new_comment_answer' => __( 'commented on answer', 'ap' ),
		'edit_question' 	=> __( 'edited question', 'ap' ),
		'edit_answer' 		=> __( 'edited answer', 'ap' ),
		'edit_comment' 		=> __( 'edited comment', 'ap' ),
		'answer_selected' 	=> __( 'selected answer', 'ap' ),
		'answer_unselected' => __( 'unselected answer', 'ap' ),
		'status_updated' 	=> __( 'updated status', 'ap' ),
		'best_answer' 		=> __( 'selected as best answer', 'ap' ),
		'unselected_best_answer' 	=> __( 'unselected as best answer', 'ap' ),
	);

	$title = apply_filters( 'ap_activity_short_title', $title );

	if ( isset( $title[ $type ] ) ) {
		return $title[ $type ];
	}

	return $type;
}

/**
 * Get last active time
 * @param  integer $post_id Question or answer ID.
 * @return boolean $html    HTML formatted?
 * @since  2.0.1
 */
function ap_post_active_time($post_id = false, $html = true) {

	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	$post = get_post( $post_id );

	$activity = ap_post_activity_meta( $post_id );

	if ( ! $activity ) {
		$activity['date'] = get_the_time( 'c', $post_id );
		$activity['user_id'] = $post->post_author;
		$activity['type'] 	= 'new_'.$post->post_type;
	}

	if ( ! $html ) {
		return $activity['date'];
	}

	$title = ap_activity_short_title( $activity['type'] );
	$title = esc_html( '<span class="ap-post-history">'.sprintf( __( '%s %s about %s ago', 'ap' ), ap_user_display_name( $activity['user_id'] ), $title, '<time datetime="'. mysql2date( 'c', $activity['date'] ) .'">'.ap_human_time( mysql2date( 'U', $activity['date'] ) ).'</time>' ).'</span>' );

	return sprintf( __( 'Active %s ago', 'ap' ), '<a class="ap-tip" href="#" title="'. $title .'"><time datetime="'. mysql2date( 'c', $activity['date'] ) .'">'.ap_human_time( mysql2date( 'U', $activity['date'] ) ) ).'</time></a>';
}

/**
 * Get latest activity of question or answer.
 * @param  integer $post_id Question or answer ID.
 * @return string
 */
function ap_latest_post_activity_html($post_id = false) {

	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	$post = get_post( $post_id );
	$activity = ap_post_activity_meta( $post_id );

	if ( ! $activity ) {
		$activity['date'] 	= get_the_time( 'c', $post_id );
		$activity['user_id'] = $post->post_author;
		$activity['type'] 	= 'new_'.$post->post_type;
	}

	$html = '';

	if ( $activity ) {

		$title = ap_activity_short_title( $activity['type'] );

		$html .= '<span class="ap-post-history">';
		$html .= sprintf( __( ' %s %s %s ago', 'ap' ),
			'<a href="'.ap_user_link( $activity['user_id'] ).'">'. ap_user_display_name( $activity['user_id'] ) .'</a>',
			$title,
			'<a href="'. get_permalink( $post ) .'"><time datetime="'. mysql2date( 'c', $activity['date'] ) .'">'. ap_human_time( $activity['date'], false ) .'</time></a>'
		);
		$html .= '</span>';
	}

	if ( $html ) {
		return apply_filters( 'ap_latest_post_activity_html', $html );
	}

	return false;
}

/**
 * Get activities query
 * @param  string|array $args Arguments.
 * @return string
 * @since  2.4
 */
function ap_get_activities( $args = '' ) {
	$activities = new AnsPress_Activity_Query( $args );
	return $activities;
}

/**
 * Get the ids of activity of a post
 * @param  integer $post_id Post id.
 * @return object
 */
function ap_post_activities_id($post_id) {
	global $wpdb;

	$post_arr = get_post( $post_id );

	if ( ! $post_arr ) {
		return;
	}

	$where = 'WHERE ';

	if ( 'question' == $post_arr->post_type ) {
		$where .= "question_id = ". $post_arr->ID;
	} elseif ( 'answer' == $post_arr->post_type ) {
		$where .= "answer_id = ". $post_arr->ID;
	}

	return $wpdb->get_col( "SELECT id FROM $wpdb->ap_activity $where");

}

function ap_activity_ids_by_item_id( $item_id, $type ){
	global $wpdb;

	return $wpdb->get_col( $wpdb->prepare( "SELECT id FROM $wpdb->ap_activity WHERE item_id = %d AND parent_type = %s", $item_id, $type) );
}

/**
 * Update multiple activities.
 * @param  array $where     	Where clauses.
 * @param  array $values 		Columns to update.
 * @return boolean|integer
 */
function ap_update_activities( $where, $columns ) {
	global $wpdb;
	$where_s = array();
	$where_f = array();
	$coulmns_f = array();

	if ( isset( $where['user_id'] ) ) {
		$where_s['user_id'] = (int) $where['user_id'];
		$where_f[] = '%d';
	}

	if ( isset( $where['secondary_user'] ) ) {
		$where_s['secondary_user'] = (int) $where['secondary_user'];
		$where_f[] = '%d';
	}

	if ( isset( $where['type'] ) ) {
		$where_s['type'] = sanitize_text_field( wp_unslash( $where['type'] ) );
		$where_f[] = '%s';
	}

	if ( isset( $where['status'] ) ) {
		$where_s['status'] = sanitize_text_field( wp_unslash( $where['status'] ) );
		$where_f[] = '%s';
	}

	if ( isset( $where['item_id'] ) ) {
		$where_s['item_id'] = (int) $where['item_id'];
		$where_f[] = '%d';
	}

	if ( isset( $where['question_id'] ) ) {
		$where_s['question_id'] = (int) $where['question_id'];
		$where_f[] = '%d';
	}

	if ( count( $where ) == 0 ) {
		return false;
	}

	if ( count( $columns ) == 0 ) {
		return false;
	}

	foreach ( $columns as $key => $value ) {
		if ( 'user_id' == $key || 'secondary_user' == $key || 'question_id' == $key || 'item_id' == $key ) {
			$coulmns_f[] = '%d';
			$columns[ $key ] = (int) $value;
		} else {
			$coulmns_f[] = '%s';
			$columns[ $key ] = sanitize_text_field( wp_unslash( $value ) );
		}
	}

	$row = $wpdb->update( $wpdb->ap_activity, $columns, $where_s, $coulmns_f, $where_f );

	if ( false !== $row ) {
		// wp_cache_delete( $activity_id, 'ap_activity' );
		return $row;
	}
}

/**
 * Change status of post activity.
 * @param  integer $post_id Post id.
 * @param  string  $status  Activity status.
 * @return boolean|integer
 */
function ap_change_post_activities_status( $post_id, $status ) {
	$postarr = get_post( $post_id );

	if ( ! $postarr ) {
		return;
	}

	$status = sanitize_text_field( $status );

	$where = array();
	$columns = array();

	if ( 'question' == $postarr->post_type ) {
		$where['question_id'] = $post_id;
		$columns['status'] = $status;
	} elseif ( 'answer' == $postarr->post_type ) {
		$where['answer_id'] = $post_id;
		$where['question_id'] = $postarr->post_parent;
		$columns['status'] = $status;
	}

	return ap_update_activities( $where, $columns );
}

/**
 * Delete an activity
 * @param  integer $id Activity id.
 * @return boolean|integer
 */
function ap_delete_activity($id) {
	global $wpdb;
	do_action( 'ap_before_deleting_activity', $id );
	$row = $wpdb->delete( $wpdb->ap_activity, array( 'id' => $id ), array( '%d' ) );

	if ( false !== $row ) {
		do_action( 'ap_after_deleting_activity', $id );
		return $row;
	}

	return $row;
}
