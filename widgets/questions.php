<?php
class AP_Questions_Widget extends WP_Widget {

	function AP_Questions_Widget() {
		// Instantiate the parent object
		parent::__construct( false, 'AnsPress Questions' );
	}

	public function widget( $args, $instance ) {
		$title 			= apply_filters( 'widget_title', $instance['title'] );
		$avatar 		= $instance[ 'avatar' ];
		$order 			= $instance[ 'order' ];
		$show_selected 	= $instance[ 'show_selected' ];
		$show_activity 	= $instance[ 'show_activity' ];
		$show_answers	= $instance[ 'show_answers' ];
		$show_vote 		= $instance[ 'show_vote' ];
		$show_views 	= $instance[ 'show_views' ];
		$show_category	= $instance[ 'show_category' ];
		$show_tags		= $instance[ 'show_tags' ];
		$label			= $instance[ 'label' ];
		$limit			= $instance[ 'limit' ];

		echo $args['before_widget'];
		if ( ! empty( $title ) ) 
			echo $args['before_title'] . $title . $args['after_title'];
		

			
		if(empty($label ))
			$label = '';

		
		$question_args=array(
			'ap_query' 		=> 'main_questions',
			'post_type' 	=> 'question',
			'post_status' 	=> 'publish',
			'showposts' 	=> $limit,
		);
		
		if($order == 'active'){				
			$question_args['ap_query'] = 'main_questions_active';
			$question_args['orderby'] = 'meta_value';
			$question_args['meta_key'] = ANSPRESS_UPDATED_META;
			$question_args['meta_query'] = array(
				'relation' => 'OR',
				array(
					'key' => ANSPRESS_UPDATED_META,
					'compare' => 'NOT EXISTS',
				),
			);	
			
		}elseif($order == 'voted'){
			$question_args['orderby'] = 'meta_value_num';
			$question_args['meta_key'] = ANSPRESS_VOTE_META;
		}elseif($order == 'answers'){
			$question_args['orderby'] = 'meta_value_num';
			$question_args['meta_key'] = ANSPRESS_ANS_META;
		}elseif($order == 'unanswered'){
			$question_args['orderby'] = 'meta_value';
			$question_args['meta_key'] = ANSPRESS_ANS_META;
			$question_args['meta_value'] = '0';

		}elseif($order == 'oldest'){
			$question_args['orderby'] = 'date';
			$question_args['order'] = 'ASC';
		}
		
		if ($label != ''){
			$question_args['tax_query'] = array(
				array(
					'taxonomy' => 'question_label',
					'field' => 'slug',
					'terms' => $label
				)
			);				
		}
		$question = new WP_Query( $question_args );
		include ap_get_theme_location('questions-widget.php');
		echo $args['after_widget'];
		wp_reset_postdata();
	}

	public function form( $instance ) {
		$title 			= __( 'Questions', 'ap' );
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
		$limit			= 5;
		
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
			
		if ( isset( $instance[ 'limit' ] ) )
			$limit = $instance[ 'limit' ];
		
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
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Order by:', 'ap' ); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
				<option <?php selected($order, 'active'); ?> value="active"><?php _e( 'Active', 'ap' ); ?></option>
				<option <?php selected($order, 'newest'); ?> value="newest"><?php _e( 'Newest', 'ap' ); ?></option>
				<option <?php selected($order, 'voted'); ?> value="voted"><?php _e( 'Voted', 'ap' ); ?></option>
				<option <?php selected($order, 'answers'); ?> value="answers"><?php _e( 'Answers', 'ap' ); ?></option>
				<option <?php selected($order, 'unanswered'); ?> value="unanswered"><?php _e( 'Unanswered', 'ap' ); ?></option>
				<option <?php selected($order, 'oldest'); ?> value="oldest"><?php _e( 'Oldest', 'ap' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'label' ); ?>"><?php _e( 'Label:', 'ap' ); ?></label> 
			<select class="widefat" id="<?php echo $this->get_field_id( 'label' ); ?>" name="<?php echo $this->get_field_name( 'label' ); ?>">
				<option value=""></option>
				<?php
					$terms = get_terms( 'question_label', array( 'hide_empty' => true, 'orderby' => 'count' ) );
					if ( !empty( $terms ) ) :
					foreach($terms as $t):
				?>
					<option <?php selected($label, $t->slug); ?> value="<?php echo $t->slug; ?>"><?php echo $t->name; ?></option>	
				<?php
					endforeach;
					endif; 
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_selected' ); ?>"><?php _e( 'Show selected:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_selected' ); ?>" name="<?php echo $this->get_field_name( 'show_selected' ); ?>" type="checkbox" value="<?php echo esc_attr( $show_selected ); ?>" <?php checked( $show_selected, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_activity' ); ?>"><?php _e( 'Show activity:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_activity' ); ?>" name="<?php echo $this->get_field_name( 'show_activity' ); ?>" type="checkbox" value="<?php echo esc_attr( $show_activity ); ?>" <?php checked( $show_activity, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_vote' ); ?>"><?php _e( 'Show vote:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_vote' ); ?>" name="<?php echo $this->get_field_name( 'show_vote' ); ?>" type="checkbox" value="<?php echo esc_attr( $show_vote ); ?>" <?php checked( $show_vote, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_views' ); ?>"><?php _e( 'Show view:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_views' ); ?>" name="<?php echo $this->get_field_name( 'show_views' ); ?>" type="checkbox" value="<?php echo esc_attr( $show_views ); ?>" <?php checked( $show_views, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_answers' ); ?>"><?php _e( 'Show answers:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_answers' ); ?>" name="<?php echo $this->get_field_name( 'show_answers' ); ?>" type="checkbox" value="<?php echo esc_attr( $show_answers ); ?>" <?php checked( $show_answers, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_category' ); ?>"><?php _e( 'Show category:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_category' ); ?>" name="<?php echo $this->get_field_name( 'show_category' ); ?>" type="checkbox" value="<?php echo esc_attr( $show_category ); ?>" <?php checked( $show_category, 1 ); ?>>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'show_tags' ); ?>"><?php _e( 'Show tags:', 'ap' ); ?></label> 
			<input id="<?php echo $this->get_field_id( 'show_tags' ); ?>" name="<?php echo $this->get_field_name( 'show_tags' ); ?>" type="checkbox" value="<?php echo esc_attr( $show_tags ); ?>" <?php checked( $show_tags, 1 ); ?>>
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
		$instance['show_activity'] = ( ! empty( $new_instance['show_activity'] ) ) ? strip_tags( $new_instance['show_activity'] ) : 0;
		$instance['show_answers'] = ( ! empty( $new_instance['show_answers'] ) ) ? strip_tags( $new_instance['show_answers'] ) : 0;
		$instance['show_views'] = ( ! empty( $new_instance['show_views'] ) ) ? strip_tags( $new_instance['show_views'] ) : 0;
		$instance['show_vote'] = ( ! empty( $new_instance['show_vote'] ) ) ? strip_tags( $new_instance['show_vote'] ) : 0;
		$instance['show_selected'] = ( ! empty( $new_instance['show_selected'] ) ) ? strip_tags( $new_instance['show_selected'] ) : 0;
		$instance['show_category'] = ( ! empty( $new_instance['show_category'] ) ) ? strip_tags( $new_instance['show_category'] ) : 0;
		$instance['show_tags'] = ( ! empty( $new_instance['show_tags'] ) ) ? strip_tags( $new_instance['show_tags'] ) : 0;
		$instance['label'] = ( ! empty( $new_instance['label'] ) ) ? strip_tags( $new_instance['label'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : 5;

		return $instance;
	}
}

function ap_questions_register_widgets() {
	register_widget( 'AP_Questions_Widget' );
}

add_action( 'widgets_init', 'ap_questions_register_widgets' );