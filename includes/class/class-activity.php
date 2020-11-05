<?php
/**
 * The AnsPress activity query.
 *
 * @package    AnsPress
 * @subpackage Activity Query
 * @author     Rahul Aryan <rah12@live.com>
 * @license    GPL-3.0+
 * @link       https://anspress.net
 * @copyright  2014 Rahul Aryan
 * @since        4.1.2
 */

namespace AnsPress;

use AnsPress_Query;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The activity query.
 *
 * Query wrapper for fetching and parsing AnsPress activities. In actual
 * there is not any group in activities. To prevent activities having same
 * ref type to show in main tree. In simple words, we are just checking if
 * next activity in loop have same ref id and if so then do a child loop to
 * group them in one activity.
 *
 * @param array|string $args arguments passed to class.
 * @since 4.1.2
 */
class Activity extends AnsPress_Query {

	/**
	 * Verbs
	 *
	 * @var array
	 */
	public $verbs = [];

	/**
	 * Items to show per page
	 *
	 * @access public
	 * @var int
	 */
	var $per_page = 30;

	/**
	 * In group.
	 *
	 * @var boolean
	 */
	var $in_group = false;

	/**
	 * Initialize the activity class.
	 *
	 * By default if no arguments are passed then all activities of site will
	 * be shown.
	 *
	 * @param array $args {
	 *      Arguments.
	 *
	 *      @type integer $q_id     Question id, to fetch activities only related to a specific question.
	 *      @type integer $a_id     Answer id, to fetch activities only related to a specific answer.
	 *      @type integer $number   Activities to show per page.
	 *      @type integer $orderby  name of column to order activities. Valid values are:
	 *    `activity_date`, `activity_q_id`, `activity_a_id`, `activity_user_id`.
	 *      @type integer $order    Activity order. `DESC` is default order.
	 * }
	 * @since 4.1.2
	 */
	public function __construct( $args = [] ) {
		$this->paged  = isset( $args['paged'] ) ? (int) $args['paged'] : 1;
		$this->offset = $this->per_page * ( $this->paged - 1 );

		$this->args = wp_parse_args(
			$args, array(
				'number'        => $this->per_page,
				'offset'        => $this->offset,
				'orderby'       => 'activity_date',
				'order'         => 'DESC',
				'exclude_roles' => [ 'administrator' ],
			)
		);

		// Check if valid orderby argument.
		$valid_orderby = [ 'activity_q_id', 'activity_a_id', 'activity_c_id', 'activity_date' ];
		if ( ! in_array( $this->args['orderby'], $valid_orderby, true ) ) {
			$this->args['orderby'] = 'activity_date';
		}

		$this->per_page = $this->args['number'];
		$this->query();
	}

	/**
	 * Prepare and fetch notifications from database.
	 *
	 * @since 4.1.2
	 * @since 4.1.8 Added `exclude_roles`.
	 */
	public function query() {
		global $wpdb;

		$sql = array(
			'fields'  => 'a.*',
			'where'   => [],
			'orderby' => 'a.' . $this->args['orderby'],
			'order'   => ( 'DESC' === $this->args['order'] ? 'DESC' : 'ASC' ),
		);

		// Add q_id to where clause.
		if ( isset( $this->args['q_id'] ) ) {
			$sql['where'][] = $wpdb->prepare( 'AND a.activity_q_id = %d', (int) $this->args['q_id'] );
		}

		// Add a_id to where clause.
		if ( isset( $this->args['a_id'] ) ) {
			$sql['where'][] = $wpdb->prepare( 'AND a.activity_a_id = %d', (int) $this->args['a_id'] );
		}

		// Add c_id to where clause.
		if ( isset( $this->args['c_id'] ) ) {
			$sql['where'][] = $wpdb->prepare( 'AND a.activity_c_id = %d', (int) $this->args['c_id'] );
		}

		// Add user_id to where clause.
		if ( isset( $this->args['user_id'] ) ) {
			$sql['where'][] = $wpdb->prepare( 'AND a.activity_user_id = %d', (int) $this->args['user_id'] );
		}

		$exclude = '';
		// Add user_id to where clause.
		if ( ! empty( $this->args['exclude_roles'] ) ) {
			$cap_key = $wpdb->prefix . 'capabilities';

			$role_like = '';
			$total     = count( $this->args['exclude_roles'] );
			$i         = 1;

			foreach ( $this->args['exclude_roles'] as $r ) {
				$role_like .= $wpdb->prepare( 'um.meta_value NOT LIKE %s', '%' . sanitize_title( $wpdb->esc_like( $r ) ) . '%' );
				if ( $total > $i ) {
					$role_like .= ' AND ';
				}

				$i++;
			}

			if ( ! empty( $role_like ) ) {
				$exclude = "LEFT JOIN {$wpdb->usermeta} um ON um.user_id = a.activity_user_id";
				$sql['where'][] = "AND ( um.meta_key = '{$cap_key}' AND ( {$role_like} ) )";
			}
		}

		$where = implode( ' ', $sql['where'] );

		$query = "SELECT SQL_CALC_FOUND_ROWS {$sql['fields']} FROM {$wpdb->ap_activity} a $exclude WHERE 1=1 {$where} ORDER BY {$sql['orderby']} {$sql['order']} LIMIT {$this->offset},{$this->per_page}";

		$this->objects = $wpdb->get_results( $query ); // WPCS: DB call okay.
		$this->total_count( '' );

		$activities = [];
		foreach ( $this->objects as $activity ) {
			$activity     = ap_activity_parse( $activity );
			$activities[] = $activity;
		}

		$this->objects = $activities;

		$this->prefetch();

		parent::query();
	}

	/**
	 * Prefetch question, answer, comment and user data.
	 *
	 * Firstly it group ids and then fetch data by ids in single query
	 * and then cache each of them. Using this method reduces mysql queries
	 * and also speed up site.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	private function prefetch() {
		foreach ( (array) $this->objects as $key => $activity ) {
			// Add question and answer id.
			if ( ! empty( $activity->q_id ) ) {
				$this->add_prefetch_id( 'post', $activity->q_id );
				$this->add_prefetch_id( 'post', $activity->a_id );
			}

			// Add comment ID.
			if ( ! empty( $activity->c_id ) ) {
				$this->add_prefetch_id( 'comment', $activity->c_id );
			}

			// Add user ID.
			if ( ! empty( $activity->user_id ) ) {
				$this->add_prefetch_id( 'user', $activity->user_id );
			}
		}

		$this->prefetch_posts();
		$this->prefetch_actors();
		$this->prefetch_comments();
	}

	/**
	 * Pre fetch question and answers and cache them.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	private function prefetch_posts() {
		if ( empty( $this->ids['post'] ) ) {
			return;
		}

		global $wpdb;

		$ids_str = esc_sql( sanitize_comma_delimited( $this->ids['post'] ) );
		$posts   = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE ID in ({$ids_str})" );

		// Cache all posts.
		foreach ( $posts as $_post ) {
			wp_cache_set( $_post->ID, $_post, 'posts' );
		}
	}

	/**
	 * Prefetch actors of activities and cache them.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	private function prefetch_actors() {
		if ( empty( $this->ids['user'] ) ) {
			return;
		}

		ap_post_author_pre_fetch( $this->ids['user'] );
	}

	/**
	 * Pre fetch comments of activities and cache them.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	private function prefetch_comments() {
		global $wpdb;

		if ( empty( $this->ids['comment'] ) ) {
			return;
		}

		$ids      = esc_sql( sanitize_comma_delimited( $this->ids['comment'] ) );
		$comments = $wpdb->get_results( "SELECT * FROM {$wpdb->comments} WHERE comment_ID in ({$ids})" );

		// Cache comments.
		foreach ( $comments as $_comment ) {
			wp_cache_set( $_comment->comment_ID, $_comment, 'comment' );
		}
	}

	/**
	 * Check if current activity have group.
	 *
	 * @return void
	 */
	public function have_group() {
		if ( $this->current + 1 < $this->count && $this->have_group_items() ) {
			return true;
		}

		return false;
	}

	/**
	 * Start group.
	 *
	 * This method must be called before doing a group loop.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	public function group_start() {
		if ( $this->have_group_items() ) {
			$this->in_group = true;
			$this->current--;
		}
	}

	/**
	 * End group loop.
	 *
	 * This method must be called after group while loop.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	public function group_end() {
		$this->in_group = false;
	}

	/**
	 * Check if there are items in a group.
	 *
	 * @return boolean
	 * @since 4.1.2
	 */
	public function have_group_items() {
		$next = $this->current + 1;

		// Return if no item in that index.
		if ( ! isset( $this->objects[ $next ] ) ) {
			return false;
		}

		$next_obj = $this->objects[ $next ];
		if ( is_object( $next_obj ) && $this->object->q_id == $next_obj->q_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Count total items in a group.
	 *
	 * @return integer
	 * @since 4.1.2
	 */
	public function count_group() {
		if ( $this->have_group_items() ) {
			$next        = $this->current + 1;
			$count       = 0;
			$current_obj = $this->object;
			$next_obj    = $this->objects[ $next ];

			for ( $i = $next; $i < $this->count; $i++ ) {
				if ( $current_obj->q_id == $next_obj->q_id ) {
					$count++;
				} else {
					break;
				}

				if ( $i + 1 < $this->count ) {
					$current_obj = $this->objects[ $i ];
					$next_obj    = $this->objects[ $i + 1 ];
				}
			}
		}

		return $count;
	}

	/**
	 * Check if current activity has action.
	 *
	 * @return boolean
	 * @since 4.1.2
	 */
	public function has_action() {
		if ( is_array( $this->object->action ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the verb of current activity.
	 *
	 * @return null|string Returns null if action does not exists.
	 * @since 4.1.2
	 */
	public function get_the_verb() {
		if ( ! $this->has_action() || empty( $this->object->action['verb'] ) ) {
			return;
		}

		$verb = $this->object->action['verb'];
		return sprintf( $verb, ap_user_display_name( $this->get_user_id() ) );
	}

	/**
	 * Echo current activity verb.
	 *
	 * This is a wrapper method for AnsPress\Activity::get_the_verb().
	 *
	 * @return void
	 * @since 4.1.2
	 */
	public function the_verb() {
		echo $this->get_the_verb();
	}

	/**
	 * Return avatar of activity user.
	 *
	 * @param integer $size Size of avatar in pixel.
	 * @return string
	 * @since 4.1.2
	 */
	public function get_avatar( $size = 40 ) {
		return get_avatar( $this->get_user_id(), $size );
	}

	/**
	 * Get user_id of current activity.
	 *
	 * @return integer
	 * @since 4.1.2
	 */
	public function get_user_id() {
		return $this->object->user_id;
	}

	/**
	 * Return the date of activity.
	 *
	 * @return string
	 * @since 4.1.2
	 */
	public function get_the_date() {
		return $this->object->date;
	}

	/**
	 * Get the icon of activity.
	 *
	 * @return string
	 * @since 4.1.2
	 */
	public function get_the_icon() {
		if ( is_array( $this->object->action ) || ! empty( $this->object->action['icon'] ) ) {
			return $this->object->action['icon'];
		}

		return 'apicon-pulse';
	}

	/**
	 * Echo icon of current activity.
	 *
	 * This is a wrapper method for @see AnsPress\Activity::get_the_icon().
	 *
	 * @return void
	 * @since 4.1.2
	 */
	public function the_icon() {
		echo esc_attr( $this->get_the_icon() );
	}

	/**
	 * Output reference content.
	 *
	 * @param boolean $show_question Force to show question content or title.
	 * @return void
	 * @since 4.1.2
	 */
	public function the_ref_content( $show_question = false ) {
		include ap_get_theme_location( 'activities/activity-ref-content.php' );
	}

	/**
	 * Return the human readable date of an activity which can be
	 * compared to other activity.
	 *
	 * @param object $object Activity object.
	 * @return string Default value is date formatted in `D Y` i.e. `Apr 2017`.
	 * @since 4.1.2
	 */
	public function when( $object ) {
		$date = strtotime( $object->date );

		if ( $date >= strtotime( '-30 minutes' ) ) {
			$when = __( 'Just now', 'anspress-question-answer' );
		} elseif ( $date >= strtotime( '-24 hours' ) ) {
			$when = __( 'Today', 'anspress-question-answer' );
		} elseif ( $date >= strtotime( '-48 hours' ) ) {
			$when = __( 'Yesterday', 'anspress-question-answer' );
		} elseif ( $date <= strtotime( '1 year' ) ) {
			$when = date_i18n( 'M', $date );
		} else {
			$when = date_i18n( 'M Y', $date );
		}

		return $when;
	}

	/**
	 * Output human readable date.
	 *
	 * This is a wrapper for method @see AnsPress\Activity::when() but it
	 * output date only when previous activity in loop when value is not same.
	 *
	 * @return void
	 */
	public function the_when() {
		$current_when = $this->when( $this->object );

		if ( $this->current > 0 && isset( $this->objects[ $this->current - 1 ] ) ) {
			$last_when = $this->when( $this->objects[ $this->current - 1 ] );

			if ( $last_when === $current_when ) {
				return;
			}
		}

		echo '<div class="ap-activity-when">' . esc_html( $current_when ) . '</div>';
	}

	/**
	 * Get question ID from activity.
	 *
	 * @return integer
	 * @since 4.1.2
	 */
	public function get_q_id() {
		return $this->object->q_id;
	}

	/**
	 * Output load more button.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	public function more_button() {
		$paged = max( 1, get_query_var( 'paged' ) );

		$args = wp_json_encode(
			array(
				'ap_ajax_action' => 'more_activities',
				'__nonce'        => wp_create_nonce( 'load_activities' ),
				'paged'          => $this->paged + 1,
			)
		);

		echo '<a href="#" class="ap-btn" apajaxbtn apquery="' . esc_js( $args ) . '">' . __( 'Load More', 'anspress-question-answer' ) . '</a>';
	}
}
