<?php
/**
 * AnsPress users widget.
 * Display list of users sorting by reputation, date of registration etc.
 *
 * @since  3.0.0  Fixed bug, when page is paginated users list disappear.
 */
class AP_Users_Widget extends WP_Widget {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		parent::__construct(
			'ap_users_widget',
			__( '(AnsPress) Users', 'anspress-question-answer' ),
			array( 'description' => __( 'Shows users based on selected order.', 'anspress-question-answer' ) )
		);
	}

	public function widget( $args, $instance ) {
		global $ap_user_query;
		$title = apply_filters( 'widget_title', $instance['title'] );
		$number = $instance['number'] ;
		$sortby = $instance['sortby'] ;
		$avatar_size = isset( $instance['avatar_size'] ) ? $instance['avatar_size'] : 30;
		echo $args['before_widget'];

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$user_a = array(
			'number'    	=> $number,
			'sortby' 		=> $sortby,
			'paged' 		=> 1,
		);

		// The Query.
		$ap_user_query = ap_has_users( $user_a );

		echo '<div class="ap-widget-inner">';
		//while ( ap_users() ) : ap_the_user();
			include( ap_get_theme_location( 'widgets/users.php' ) );
		//endwhile;
		echo '</div>';

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Users', 'anspress-question-answer' );
		}

		$avatar_size 		= 30;
		$number 		= 5;
		$sortby 		= 'reputation';

		if ( isset( $instance[ 'avatar_size' ] ) ) {
			$avatar_size = $instance[ 'avatar_size' ];
		}

		if ( isset( $instance[ 'number' ] ) ) {
			$number = $instance[ 'number' ];
		}

		if ( isset( $instance[ 'sortby' ] ) ) {
			$sortby = $instance[ 'sortby' ];
		}

		?>
        <p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'avatar_size' ); ?>"><?php _e( 'Avatar:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'avatar_size' ); ?>" name="<?php echo $this->get_field_name( 'avatar_size' ); ?>" type="text" value="<?php echo esc_attr( $avatar_size ); ?>">
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Show:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>">
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'sortby' ); ?>"><?php _e( 'Sort by:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'sortby' ); ?>" name="<?php echo $this->get_field_name( 'sortby' ); ?>" type="text" value="<?php echo esc_attr( $sortby ); ?>">
        </p>
		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['number'] = ( ! empty( $new_instance['number'] ) ) ? strip_tags( $new_instance['number'] ) : 5;
		$instance['sortby'] = ( ! empty( $new_instance['sortby'] ) ) ? strip_tags( $new_instance['sortby'] ) : 5;
		$instance['avatar_size'] = ( ! empty( $new_instance['avatar_size'] ) ) ? strip_tags( $new_instance['avatar_size'] ) : 30;

		return $instance;
	}
}

function ap_users_register_widgets() {
	register_widget( 'AP_Users_Widget' );
}

add_action( 'widgets_init', 'ap_users_register_widgets' );
