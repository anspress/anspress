<?php
/**
 * AnsPress leader board widget.
 *
 * @package AnsPress
 * @author Rahul Aryan <rah12@live.com>
 * @license GPL 2+ GNU GPL licence above 2+
 * @link https://anspress.net
 * @since 4.3.0
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

/**
 * Class for AnsPress question leaderboard widget.
 *
 * @since unknown
 */
class AnsPress_Leaderboard_Widget extends WP_Widget {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		parent::__construct(
			'ap_leaderboard_widget',
			__( '(AnsPress) User Leaderboard', 'anspress-question-answer' ),
			array( 'description' => __( 'Shows users leaderboard.', 'anspress-question-answer' ) )
		);
	}

	/**
	 * Get top users from database.
	 *
	 * @param int $interval Interval in days.
	 * @param int $limit    Limit of users.
	 * @return object
	 */
	private function get_top_users( $interval, $limit ) {
		global $wpdb;

		$interval = absint( $interval );
		$limit    = absint( $limit );
		$cap_key  = $wpdb->prefix . 'capabilities';

		return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT users.ID, users.display_name, sum(rep_ev.points) AS aggregated  FROM $wpdb->ap_reputations rep
				INNER JOIN $wpdb->ap_reputation_events rep_ev ON rep.rep_event = rep_ev.slug
				INNER JOIN $wpdb->users users ON users.ID = rep.rep_user_id
				INNER JOIN $wpdb->usermeta meta ON meta.user_id = users.ID AND meta.meta_key = %s AND meta_value NOT LIKE %s
				WHERE rep.rep_date > current_date - interval %d day
				GROUP BY rep.rep_user_id
				ORDER BY aggregated DESC, users.ID ASC
				LIMIT %d",
				$cap_key,
				"%{$wpdb->esc_like('administrator')}%",
				$interval,
				$limit
			)
		);
	}

	/**
	 * Widget render method.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$title         = ! empty( $instance['title'] ) ? $instance['title'] : '';
		$avatar_size   = ! empty( $instance['avatar_size'] ) ? $instance['avatar_size'] : 40;
		$show_users    = ! empty( $instance['show_users'] ) ? $instance['show_users'] : 12;
		$users_per_row = ! empty( $instance['users_per_row'] ) ? $instance['users_per_row'] : 4;
		$interval      = ! empty( $instance['interval'] ) ? $instance['interval'] : 30;

		/**
		 * Filters the widget title.
		 *
		 * @param string $title Widget title.
		 * @since 1.0.0
		 */
		$title = apply_filters( 'widget_title', $title );

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		echo '<div class="ap-widget-inner">';

		if ( ap_is_addon_active( 'reputation.php' ) ) {
			echo '<div class="ap-leaderbaord-widget">';

			$users = $this->get_top_users( $interval, $show_users );

			if ( empty( $users ) ) {
				esc_attr_e( 'No users found with reputation.', 'anspress-question-answer' );
			} else {
				foreach ( $users as $user ) {
					echo '<a href="' . esc_url( ap_user_link( $user->ID ) ) . '" class="ap-leaderbaord-user" style="width: ' . absint( 100 / $users_per_row ) . '%">';
					echo '<div class="ap-leaderbaord-user-img">';
					echo get_avatar( $user->ID, $avatar_size );
					echo '</div>';
					echo '<div class="ap-leaderbaord-user-name">';
					echo esc_html( $user->display_name );
					echo '<span class="ap-leaderbaord-point"><b>' . (int) $user->aggregated . '</b> ' . esc_attr__( 'Points', 'anspress-question-answer' ) . '</span>';
					echo '</div>';
					echo '</a>';
				}
			}

			echo '</div>';
		} else {
			esc_attr_e( 'Reputation add-on is not active.', 'anspress-question-answer' );
		}

		echo '</div>';

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Widget form.
	 *
	 * @param array $instance Instance of widget.
	 * @return string
	 */
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'AnsPress Leader board', 'anspress-question-answer' );
		}

		$avatar_size   = ! empty( $instance['avatar_size'] ) ? absint( $instance['avatar_size'] ) : 40;
		$show_users    = ! empty( $instance['show_users'] ) ? absint( $instance['show_users'] ) : 12;
		$users_per_row = ! empty( $instance['users_per_row'] ) ? absint( $instance['users_per_row'] ) : 4;
		$interval      = ! empty( $instance['interval'] ) ? absint( $instance['interval'] ) : 30;
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'interval' ) ); ?>"><?php esc_attr_e( 'Interval (in days):', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'interval' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'interval' ) ); ?>" type="number" value="<?php echo esc_attr( $interval ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'avatar_size' ) ); ?>"><?php esc_attr_e( 'Avatar size:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'avatar_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'avatar_size' ) ); ?>" type="number" value="<?php echo esc_attr( $avatar_size ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_users' ) ); ?>"><?php esc_attr_e( 'Show users:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'show_users' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_users' ) ); ?>" type="number" value="<?php echo esc_attr( $show_users ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'users_per_row' ) ); ?>"><?php esc_attr_e( 'Users per row:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'users_per_row' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'users_per_row' ) ); ?>" type="number" value="<?php echo esc_attr( $users_per_row ); ?>">
		</p>
		<?php

		return 'noform';
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title']         = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['avatar_size']   = ( ! empty( $new_instance['avatar_size'] ) ) ? absint( $new_instance['avatar_size'] ) : 40;
		$instance['show_users']    = ( ! empty( $new_instance['show_users'] ) ) ? absint( $new_instance['show_users'] ) : 12;
		$instance['users_per_row'] = ( ! empty( $new_instance['users_per_row'] ) ) ? absint( $new_instance['users_per_row'] ) : 4;
		$instance['interval']      = ( ! empty( $new_instance['interval'] ) ) ? absint( $new_instance['interval'] ) : 30;

		return $instance;
	}
}

/**
 * Callback function to register stats widget.
 *
 * @return void
 *
 * phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed
 */
function ap_leaderboard_register_widgets() {
	register_widget( 'AnsPress_Leaderboard_Widget' );
}

add_action( 'widgets_init', 'ap_leaderboard_register_widgets' );
