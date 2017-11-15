<?php
/**
 * The AnsPress activity query.
 *
 * @package    AnsPress
 * @subpackage Activity Query
 * @author     Rahul Aryan <support@anspress.io>
 * @license    GPL-3.0+
 * @link       https://anspress.io
 * @copyright  2014 Rahul Aryan
 * @since 		 4.1.2
 */

namespace AnsPress;
use AnsPress_Query;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The activity query.
 * Query wrapper for fetching AnsPress activities.
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

	var $in_same_loop = false;

	/**
	 * Initialize the class.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = [] ) {
		$this->paged = isset( $args['paged'] ) ? (int) $args['paged'] : 1;
		$this->offset = $this->per_page * ($this->paged - 1);

		$this->args = wp_parse_args( $args, array(
			'number' 	=> $this->per_page,
			'offset' 	=> $this->offset,
			'order' 	=> 'DESC',
		));

		$this->per_page = $this->args['number'];
		$this->query();
	}

	/**
	 * Prepare and fetch notifications from database.
	 */
	public function query() {
		global $wpdb;

		$sql = array(
			'fields'  => '*',
			'where'   => [],
			'orderby' => 'activity_date',
			'order'   => ( 'DESC' === $this->args['order'] ? 'DESC' : 'ASC' ),
		);


		// Add q_id to where clause.
		if ( isset( $this->args['q_id'] ) ) {
			$sql['where'][] = $wpdb->prepare( 'AND activity_q_id = %d', (int) $this->args['q_id'] );
		}

		// Add a_id to where clause.
		if ( isset( $this->args['a_id'] ) ) {
			$sql['where'][] = $wpdb->prepare( 'AND activity_a_id = %d', (int) $this->args['a_id'] );
		}

		// Add c_id to where clause.
		if ( isset( $this->args['c_id'] ) ) {
			$sql['where'][] = $wpdb->prepare( 'AND activity_c_id = %d', (int) $this->args['c_id'] );
		}

		// Add user_id to where clause.
		if ( isset( $this->args['user_id'] ) ) {
			$sql['where'][] = $wpdb->prepare( 'AND activity_user_id = %d', (int) $this->args['user_id'] );
		}

		$where = implode( ' ', $sql['where'] );

		$query = "SELECT SQL_CALC_FOUND_ROWS {$sql['fields']} FROM {$wpdb->ap_activity} WHERE 1=1 {$where} ORDER BY {$sql['orderby']} {$sql['order']} LIMIT {$this->offset},{$this->per_page}";

		$key = md5( $query );
		$this->objects = wp_cache_get( $key, 'ap_activities' );
		$this->total_count = wp_cache_get( 'ap_' . $key, 'counts' );

		// If no cache found then get from DB.
		if ( false === $this->objects ) {
			$this->objects = $wpdb->get_results( $query ); // WPCS: DB call okay.
			$this->total_count = $wpdb->get_var( apply_filters( 'ap_activities_found_rows', 'SELECT FOUND_ROWS()', $this ) );

			$activities = [];
			foreach ( $this->objects as $activity ) {
				$activity = ap_activity_parse( $activity );
				$activities[] = $activity;
			}

			$this->objects = $activities;

			wp_cache_set( 'ap_' . $key, $this->total_count, 'counts' );
			wp_cache_set( $key, $activities, 'ap_activities' );
		}

		parent::query();
	}

	public function same_question_activities() {
		if ( $this->current + 1 < $this->count && $this->have_same_q_activities() ) {
			return true;
		}

		return false;
	}

	public function same_activities_start() {
		if ( $this->have_same_q_activities() ) {
			$this->in_same_loop = true;
			$this->current--;
		}
	}

	public function same_activities_end() {
		$this->in_same_loop = false;
	}

	public function have_same_q_activities() {
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

	public function count_same_q_activities() {

		if ( $this->have_same_q_activities() ) {
			$next = $this->current + 1;
			$count = 0;
			$current_obj = $this->object;
			$next_obj = $this->objects[ $next ];

			for ( $i = $next; $i < $this->count; $i++ ) {
				if ( $current_obj->q_id == $next_obj->q_id ) {
					$count++;
				} else {
					break;
				}

				if ( $i + 1 < $this->count ) {
					$current_obj = $this->objects[ $i ];
					$next_obj = $this->objects[ $i + 1 ];
				}
			}
		}

		return $count;
	}

	public function has_action() {
		if ( is_array( $this->object->action ) ) {
			return true;
		}

		return false;
	}

	public function get_the_verb() {
		if ( ! $this->has_action() || empty( $this->object->action['verb'] ) ) {
			return;
		}

		$verb = $this->object->action['verb'];
		return sprintf( $verb, ap_user_display_name( $this->get_user_id() ) );
	}

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

	public function get_user_id() {
		return $this->object->user_id;
	}

	public function get_the_date() {
		return $this->object->date;
	}

	public function get_the_icon() {
		if ( is_array( $this->object->action ) || ! empty( $this->object->action['icon'] ) ) {
			return $this->object->action['icon'];
		}

		return 'apicon-pulse';
	}

	public function the_icon() {
		echo esc_attr( $this->get_the_icon() );
	}

	public function the_ref_content() {
		include ap_get_theme_location( 'activities/activity-ref-content.php' );
	}

	public function when( $object ) {
		$date = strtotime( $object->date );

		if ( $date >= strtotime( '-30 minutes' ) ) {
			$when = __( 'Just now', 'anspress-question-answer' );
		} elseif ( $date >= strtotime( '-24 hours' ) ) {
			$when = __( 'Today', 'anspress-question-answer' );
		} elseif ( $date >= strtotime( '-48 hours' ) ) {
			$when = __( 'Yesterday', 'anspress-question-answer' );
		} else {
			$when = __( 'Later', 'anspress-question-answer' );
		}

		return $when;
	}

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
}
