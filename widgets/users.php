<?php
class AP_Users_Widget extends WP_Widget {

	function AP_Users_Widget() {
		// Instantiate the parent object
		parent::__construct( false, 'AnsPress Users' );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$number = $instance['number'] ;

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		$user_a = array(
			'number'    	=> $number,
			'ap_query'  	=> 'sort_points',
			'meta_key'		=> 'ap_points',
			'orderby' 		=> 'meta_value'
		);
		// The Query
		$users = new WP_User_Query( $user_a );
		include(ap_get_theme_location('users-widget.php'));
		
		
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Users', 'ap' );
		}
		$avatar 		= 30;
		$number 		= 5;
		
		if ( isset( $instance[ 'avatar' ] ) )
			$avatar = $instance[ 'avatar' ];
		
		if ( isset( $instance[ 'number' ] ) ) 
			$number = $instance[ 'number' ];
			
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'avatar' ); ?>"><?php _e( 'Avatar:', 'ap' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'avatar' ); ?>" name="<?php echo $this->get_field_name( 'avatar' ); ?>" type="text" value="<?php echo esc_attr( $avatar ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Show', 'ap' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>">
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['number'] = ( ! empty( $new_instance['number'] ) ) ? strip_tags( $new_instance['number'] ) : 5;

		return $instance;
	}
}

function ap_users_register_widgets() {
	register_widget( 'AP_Users_Widget' );
}

add_action( 'widgets_init', 'ap_users_register_widgets' );