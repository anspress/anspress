<?php
class AP_followers_Widget extends WP_Widget {

	public function AP_followers_Widget() {
		// Instantiate the parent object
		parent::__construct( false, '(AnsPress) Followers' );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$number = $instance['number'] ;
		$avatar_size = $instance['avatar_size'] ;

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		
		if(is_ap_user()){
			$followers = ap_has_users(array('user_id' => ap_get_displayed_user_id(), 'sortby' => 'followers' ));
	        if($followers->has_users()){
	            include ap_get_theme_location('widgets/followers.php');            
	        }
	        else{
	            _e('No followers yet', 'ap');
	        }
	    }else{
	    	_e('This widget can only be used in user page.', 'ap');
	    }
				
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Followers', 'ap' );
		}
		$avatar_size 		= 30;
		$number 			= 20;
		
		if ( isset( $instance[ 'avatar_size' ] ) )
			$avatar = $instance[ 'avatar_size' ];
		
		if ( isset( $instance[ 'number' ] ) ) 
			$number = $instance[ 'number' ];
			
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'avatar_size' ); ?>"><?php _e( 'Avatar size:', 'ap' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'avatar_size' ); ?>" name="<?php echo $this->get_field_name( 'avatar_size' ); ?>" type="text" value="<?php echo esc_attr( $avatar_size ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Numbers of user to show:', 'ap' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo esc_attr( $number ); ?>">
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['number'] = ( ! empty( $new_instance['number'] ) ) ? strip_tags( $new_instance['number'] ) : 20;
		$instance['avatar_size'] = ( ! empty( $new_instance['avatar_size'] ) ) ? strip_tags( $new_instance['avatar_size'] ) : 30;

		return $instance;
	}
}

function ap_followers_register_widgets() {
	register_widget( 'AP_followers_Widget' );
}

add_action( 'widgets_init', 'ap_followers_register_widgets' );