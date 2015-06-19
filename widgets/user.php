<?php
class AP_User_Widget extends WP_Widget {

	public function AP_User_Widget() {
		// Instantiate the parent object
		parent::__construct( false, '(AnsPress) Users', array('description' => __('Display current logged in users detail and links.', 'ap')) );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];

		if ( ! empty( $title ) ) 
			echo $args['before_title'] . $title . $args['after_title'];
		
		global $ap_user_query;
        
        if(is_user_logged_in()){
	        $ap_user_query = ap_has_users(array('ID' => ap_get_displayed_user_id() ) );
	        
	        if($ap_user_query->has_users()){
	        	while ( ap_users() ) : ap_the_user(); 
					ap_get_template_part('widgets/user');
				endwhile;
			}
		}else{
			_e('Login to see your profile links', 'ap');
		}
		
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'My profile', 'ap' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}

function ap_user_register_widgets() {
	register_widget( 'AP_User_Widget' );
}

add_action( 'widgets_init', 'ap_user_register_widgets' );