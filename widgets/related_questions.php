<?php
class AP_Related_questions extends WP_Widget {

	function AP_Related_questions() {
		// Instantiate the parent object
		parent::__construct( false, 'AnsPress Related Questions' );
	}

	public function widget( $args, $instance ) {
		$title 			= apply_filters( 'widget_title', $instance['title'] );
		$avatar 		= $instance[ 'avatar' ];
		$show_selected 	= $instance[ 'show_selected' ];
		$show_activity 	= $instance[ 'show_activity' ];
		$show_answers	= $instance[ 'show_answers' ];
		$show_vote 		= $instance[ 'show_vote' ];
		$show_views 	= $instance[ 'show_views' ];
		$show_category	= $instance[ 'show_category' ];
		$show_tags		= $instance[ 'show_tags' ];

		echo $args['before_widget'];
		if ( ! empty( $title ) ) 
			echo $args['before_title'] . $title . $args['after_title'];

	
		$question_args=array(
			'ap_query' 		=> 'related',
			'ap_title' 		=> get_the_title(get_question_id()),
			'post_type' 	=> 'question',
			'post__not_in' 	=> array(get_question_id()),
			'post_status' 	=> 'publish',
			'showposts' 	=> ap_opt('question_per_page'),
		);
	
		$question = new WP_Query( $question_args );
		include ap_get_theme_location('questions-widget.php');
		echo $args['after_widget'];
		wp_reset_postdata();
	}

	public function form( $instance ) {
		$title 			= __( 'Related Questions', 'ap' );
		$avatar 		= 30;
		$show_selected 	= false;
		$show_activity 	= true;
		$show_answers	= true;
		$show_vote 		= false;
		$show_views 	= false;
		$show_category	= false;
		$show_tags		= false;
		$order			= 'active';
		$label			= '';
		
		if ( isset( $instance[ 'title' ] ) )
			$title = $instance[ 'title' ];
		
		if ( isset( $instance[ 'avatar' ] ) )
			$avatar = $instance[ 'avatar' ];
		
		if ( isset( $instance[ 'show_selected' ] ) )
			$show_selected = $instance[ 'show_selected' ];
		
		if ( isset( $instance[ 'show_activity' ] ) )
			$show_activity = $instance[ 'show_activity' ];
		
		if ( isset( $instance[ 'show_vote' ] ) )
			$show_vote = $instance[ 'show_vote' ];
			
		if ( isset( $instance[ 'show_views' ] ) )
			$show_views = $instance[ 'show_views' ];
		
		if ( isset( $instance[ 'show_category' ] ) )
			$show_category = $instance[ 'show_category' ];		
		
		if ( isset( $instance[ 'show_answers' ] ) )
			$show_answers = $instance[ 'show_answers' ];
			
		if ( isset( $instance[ 'show_tags' ] ) )
			$show_tags = $instance[ 'show_tags' ];
		
		if ( isset( $instance[ 'order' ] ) )
			$order = $instance[ 'order' ];
		
		if ( isset( $instance[ 'label' ] ) )
			$label = $instance[ 'label' ];
		
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
			<label for="<?php echo $this->get_field_id( 'show_selected' ); ?>"><?php _e( 'Show selected:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_selected' ); ?>" name="<?php echo $this->get_field_name( 'show_selected' ); ?>" type="checkbox" value="1" <?php checked( $show_selected, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_activity' ); ?>"><?php _e( 'Show activity:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_activity' ); ?>" name="<?php echo $this->get_field_name( 'show_activity' ); ?>" type="checkbox" value="1" <?php checked( $show_activity, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_vote' ); ?>"><?php _e( 'Show vote:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_vote' ); ?>" name="<?php echo $this->get_field_name( 'show_vote' ); ?>" type="checkbox" value="1" <?php checked( $show_vote, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_views' ); ?>"><?php _e( 'Show view:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_views' ); ?>" name="<?php echo $this->get_field_name( 'show_views' ); ?>" type="checkbox" value="1" <?php checked( $show_views, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_answers' ); ?>"><?php _e( 'Show answers:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_answers' ); ?>" name="<?php echo $this->get_field_name( 'show_answers' ); ?>" type="checkbox" value="1" <?php checked( $show_answers, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_category' ); ?>"><?php _e( 'Show category:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_category' ); ?>" name="<?php echo $this->get_field_name( 'show_category' ); ?>" type="checkbox" value="1" <?php checked( $show_category, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_tags' ); ?>"><?php _e( 'Show tags:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_tags' ); ?>" name="<?php echo $this->get_field_name( 'show_tags' ); ?>" type="checkbox" value="1" <?php checked( $show_tags, 1 ); ?>>
		</p>

		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['avatar'] = ( ! empty( $new_instance['avatar'] ) ) ? strip_tags( $new_instance['avatar'] ) : '';

		$instance['show_activity'] = ( isset( $new_instance['show_activity'] ) ) ?  (bool)$new_instance['show_activity']  : false;
		$instance['show_answers'] = ( isset( $new_instance['show_answers'] ) ) ? (bool) $new_instance['show_answers']  : false;
		$instance['show_views'] = ( isset( $new_instance['show_views'] ) ) ? (bool) $new_instance['show_views']  : false;
		$instance['show_vote'] = ( isset( $new_instance['show_vote'] ) ) ? (bool) $new_instance['show_vote']  : false;
		$instance['show_selected'] = ( isset( $new_instance['show_selected'] ) ) ? (bool) $new_instance['show_selected']  : false;
		$instance['show_category'] = ( isset( $new_instance['show_category'] ) ) ? (bool) $new_instance['show_category']  : false;
		$instance['show_tags'] = ( isset( $new_instance['show_tags'] ) ) ? (bool) $new_instance['show_tags']  : false;


		return $instance;
	}
}

function ap_rquestions_register_widgets() {
	register_widget( 'AP_Related_questions' );
}

add_action( 'widgets_init', 'ap_rquestions_register_widgets' );