<?php
/**
 * AnsPress notification query class.
 *
 * @package    AnsPress
 * @subpackage Notifications Addon
 * @author     Rahul Aryan <rah12@live.com>
 * @license    GPL-3.0+
 * @link       https://anspress.net
 * @copyright  2014 Rahul Aryan
 * @since        4.0.0
 */

namespace AnsPress;

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
class Notifications extends \AnsPress_Query {

	/**
	 * Verbs
	 *
	 * @var array
	 */
	public $verbs = [];

	/**
	 * Initialize the class.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = [] ) {
		$this->ids['reputation'] = [];
		$this->pos['reputation'] = [];
		$this->verbs             = ap_notification_verbs();
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
		$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ap_notifications WHERE noti_user_id = %d {$ref_id_q} {$ref_type_q} {$verb_q} {$seen_q} ORDER BY noti_date {$order} LIMIT {$this->offset},{$this->per_page}", $this->args['user_id'] );

		$this->objects     = $wpdb->get_results( $query ); // WPCS: DB call okay.
		$count_query       = $wpdb->prepare( "SELECT count(noti_id) FROM {$wpdb->prefix}ap_notifications WHERE noti_user_id = %d {$ref_id_q} {$ref_type_q} {$verb_q} {$seen_q}", $this->args['user_id'] );
		$this->total_count = $wpdb->get_var( apply_filters( 'ap_notifications_found_rows', $count_query, $this ) );
		$this->prefetch();
		parent::query();
	}

	/**
	 * Prefetch posts, comments and other data.
	 */
	public function prefetch() {
		foreach ( (array) $this->objects as $key => $noti ) {
			if ( ! empty( $noti->noti_ref_id ) ) {
				$current_verb = $this->verb_args( $noti->noti_verb );

				if ( in_array( $current_verb['ref_type'], [ 'question', 'answer', 'post' ], true ) ) {
					$this->add_prefetch_id( 'post', $noti->noti_ref_id, $key );
				}

				if ( 'comment' === $current_verb['ref_type'] ) {
					$this->add_prefetch_id( 'comment', $noti->noti_ref_id, $key );
				}

				if ( 'reputation' === $current_verb['ref_type'] ) {
					$this->add_prefetch_id( 'reputation', $noti->noti_ref_id, $key );
				}
			}

			if ( ! empty( $noti->noti_actor ) ) {
				$this->add_prefetch_id( 'user', $noti->noti_actor );
			}
		}

		$this->prefetch_posts();
		$this->prefetch_comments();
		$this->prefetch_actors();
		$this->prefetch_reputations();
	}

	/**
	 * Pre fetch post contents and append to object.
	 */
	public function prefetch_posts() {

		if ( empty( $this->ids['post'] ) ) {
			return;
		}

		global $wpdb;

		$ids_str = esc_sql( sanitize_comma_delimited( $this->ids['post'] ) );
		$posts   = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE ID in ({$ids_str})" );

		foreach ( (array) $posts as $_post ) {
			$this->append_ref_data( 'post', $_post->ID, $_post );
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
		$comments = $wpdb->get_results( "SELECT c.*, p.post_type, p.post_title FROM {$wpdb->comments} c LEFT JOIN $wpdb->posts p ON c.comment_post_ID = p.ID WHERE comment_ID in ({$ids})" );

		foreach ( (array) $comments as $_comment ) {
			$this->append_ref_data( 'comment', $_comment->comment_ID, $_comment );
		}
	}

	/**
	 * Prefetch actors user object.
	 */
	public function prefetch_actors() {
		if ( empty( $this->ids['user'] ) ) {
			return;
		}

		ap_post_author_pre_fetch( $this->ids['user'] );
	}

	/**
	 * Prefetch notification reputations.
	 */
	public function prefetch_reputations() {
		global $wpdb;

		if ( empty( $this->ids['reputation'] ) ) {
			return;
		}

		$ids         = esc_sql( sanitize_comma_delimited( $this->ids['reputation'] ) );
		$reputations = $wpdb->get_results( "SELECT rep_id, rep_event FROM {$wpdb->ap_reputations} WHERE rep_id in ({$ids})" );

		foreach ( (array) $reputations as $rep ) {
			$rep->points = ap_get_reputation_event_points( $rep->rep_event );
			$this->append_ref_data( 'reputation', $rep->rep_id, $rep );
		}
	}

	/**
	 * Return verb arguments.
	 *
	 * @param string $key Verb key.
	 * @return array|null
	 */
	public function verb_args( $key ) {
		if ( isset( $this->verbs[ $key ] ) ) {
			return $this->verbs[ $key ];
		}
	}

	/**
	 * Include current item template.
	 */
	public function item_template() {
		$verb = $this->verb_args( $this->object->noti_verb );
		$file = ap_get_theme_location( 'addons/notification/' . sanitize_file_name( $verb['ref_type'] . '.php' ) );

		if ( file_exists( $file ) ) {
			include $file;
		}
	}

	/**
	 * Set up the current notification inside the loop.
	 */
	public function the_notification() {
		parent::the_object();
	}

	public function get_ref_id() {
		return $this->object->noti_ref_id;
	}

	public function get_ref_type() {
		return $this->object->noti_ref_type;
	}

	/**
	 * Get the permalink of notification based on ref_id and ref_type.
	 *
	 * @return string
	 */
	public function get_permalink() {
		if ( in_array( $this->get_ref_type(), [ 'question', 'answer', 'post' ], true ) ) {
			return ap_get_short_link( [ 'ap_p' => $this->get_ref_id() ] );
		} elseif ( 'comment' === $this->get_ref_type() ) {
			return ap_get_short_link( [ 'ap_c' => $this->get_ref_id() ] );
		} elseif ( 'reputation' === $this->get_ref_type() ) {
			return ap_get_short_link(
				[
					'ap_u' => $this->object->noti_user_id,
					'sub'  => 'reputations',
				]
			);
		}
	}

	/**
	 * Echo permalink, Alice of `get_permalink`.
	 */
	public function the_permalink() {
		echo esc_url( $this->get_permalink() );
	}

	/**
	 * Get actor ID.
	 *
	 * @return integer
	 */
	public function get_actor() {
		if ( ! $this->hide_actor() ) {
			return ap_user_display_name( $this->object->noti_actor ); // xss okay.
		} else {
			return __( 'Someone', 'anspress-question-answer' );
		}
	}

	/**
	 * Echo actor display name.
	 */
	public function the_actor() {
		echo esc_html( $this->get_actor() );
	}

	/**
	 * Return avatar of notification actor.
	 *
	 * @param integer|string $size Size of the avatar.
	 * @return string
	 */
	public function actor_avatar( $size = 35 ) {
		if ( ! $this->hide_actor() ) {
			return get_avatar( $this->object->noti_actor, $size );
		}
	}

	/**
	 * Echo actor avatar.
	 *
	 * @param integer|string $size Size of the avatar.
	 */
	public function the_actor_avatar( $size = 40 ) {
		if ( ! $this->hide_actor() ) {
			echo $this->actor_avatar( $size ); // WPCS: xss okay.
		}
	}

	/**
	 * Get the verb of a notification.
	 */
	public function get_verb() {
		$key       = $this->object->noti_verb;
		$verb_text = '';

		if ( isset( $this->verbs[ $key ] ) ) {
			$args      = $this->verbs[ $key ];
			$verb_text = $args['label'];

			$args = array(
				'%cpt%'    => __( 'post', 'anspress-question-answer' ),
				'%points%' => number_format_i18n( 0 ),
			);

			if ( isset( $this->object->ref ) ) {
				if ( isset( $this->object->ref->post_type ) ) {
					if ( 'question' === $this->object->ref->post_type ) {
						$args['%cpt%'] = __( 'question', 'anspress-question-answer' );
					} elseif ( 'answer' === $this->object->ref->post_type ) {
						$args['%cpt%'] = __( 'answer', 'anspress-question-answer' );
					}
				}

				if ( isset( $this->object->ref->points ) ) {
					$args['%points%'] = number_format_i18n( $this->object->ref->points );
				}
			}

			$verb_text = strtr( $verb_text, $args );
		}

		return $verb_text;
	}

	/**
	 * Echo verb.
	 */
	public function the_verb() {
		echo $this->get_verb(); // WPCS: xss okay.
	}

	/**
	 * Return date of a notification.
	 *
	 * @return string
	 */
	public function get_date() {
		return $this->object->noti_date;
	}

	/**
	 * Echo human readble date.
	 */
	public function the_date() {
		echo ap_human_time( $this->get_date(), false ); // WPCS: xss okay.
	}

	/**
	 * Return ref title.
	 *
	 * @return string
	 */
	public function get_ref_title() {
		if ( isset( $this->object->ref ) ) {
			$verb_args = $this->verb_args( $this->object->noti_verb );

			if ( in_array( $verb_args['ref_type'], [ 'post', 'comment' ], true ) && isset( $this->object->ref->post_title ) ) {
				return ap_truncate_chars( $this->object->ref->post_title, 80 );
			}
		}
	}

	/**
	 * Print ref title.
	 */
	public function the_ref_title() {
		echo esc_html( $this->get_ref_title() );
	}

	/**
	 * Return notification points if ref_type is reputation.
	 *
	 * @return integer
	 */
	public function get_reputation_points() {
		if ( isset( $this->object->ref ) && isset( $this->object->ref->points ) ) {
			return $this->object->ref->points;
		}
	}

	/**
	 * Print reputtaion points.
	 */
	public function the_reputation_points() {
		echo esc_attr( $this->get_reputation_points() );
	}

	/**
	 * Hide actor.
	 *
	 * @return boolean
	 */
	public function hide_actor() {
		$args = $this->verb_args( $this->object->noti_verb );
		return (bool) $args['hide_actor'];
	}

	/**
	 * Return icon of notification.
	 *
	 * @return string
	 */
	public function get_icon() {
		$args = $this->verb_args( $this->object->noti_verb );
		return $args['icon'];
	}

	/**
	 * Print icon of notification if defined.
	 */
	public function the_icon() {
		echo esc_attr( $this->get_icon() );
	}

}
