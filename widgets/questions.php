<?php
class AP_Questions_Widget extends WP_Widget {

	public function AP_Questions_Widget() {
		// Instantiate the parent object
		parent::__construct( false, '(AnsPress) Questions', array('desc' => __('Shows list of question shorted by option', 'ap')) );
	}

	public function widget( $args, $instance ) {
		$title 			= apply_filters( 'widget_title', $instance['title'] );
		$order 			= $instance[ 'order' ];
		$limit			= $instance[ 'limit' ];

		echo $args['before_widget'];
		if ( ! empty( $title ) ) 
			echo $args['before_title'] . $title . $args['after_title'];
		
		$question_args=array(
			'showposts' 	=> $limit,
			'orderby' 		=> $order,
		);
		
		
		$questions = ap_get_questions( $question_args );
		ap_get_template_part('widget-questions');
		echo $args['after_widget'];
		wp_reset_postdata();
	}

	public function form( $instance ) {
		$title 			= __( 'Questions', 'ap' );
		$order			= 'active';
		$limit			= 5;
		
		if ( isset( $instance[ 'title' ] ) )
			$title = $instance[ 'title' ];
		
		if ( isset( $instance[ 'order' ] ) )
			$order = $instance[ 'order' ];

		if ( isset( $instance[ 'limit' ] ) )
			$limit = $instance[ 'limit' ];
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order by:', 'ap' ); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
				<option <?php selected($order, 'active'); ?> value="active"><?php _e( 'Active', 'ap' ); ?></option>
				<option <?php selected($order, 'newest'); ?> value="newest"><?php _e( 'Newest', 'ap' ); ?></option>
				<option <?php selected($order, 'voted'); ?> value="voted"><?php _e( 'Voted', 'ap' ); ?></option>
				<option <?php selected($order, 'answers'); ?> value="answers"><?php _e( 'Answers', 'ap' ); ?></option>
				<option <?php selected($order, 'unanswered'); ?> value="unanswered"><?php _e( 'Unanswered', 'ap' ); ?></option>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>">
		</p>

		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['avatar'] = ( ! empty( $new_instance['avatar'] ) ) ? strip_tags( $new_instance['avatar'] ) : '';
		$instance['order'] = ( ! empty( $new_instance['order'] ) ) ? strip_tags( $new_instance['order'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : 5;

		return $instance;
	}
}

function ap_questions_register_widgets() {
	register_widget( 'AP_Questions_Widget' );
}

add_action( 'widgets_init', 'ap_questions_register_widgets' );