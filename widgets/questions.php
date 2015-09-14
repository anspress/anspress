<?php
class AP_Questions_Widget extends WP_Widget {

	public function AP_Questions_Widget() {
		// Instantiate the parent object
		parent::__construct( false, '(AnsPress) Questions', array('desc' => __('Shows list of question shorted by option', 'ap')) );
	}

	public function widget( $args, $instance ) {
		global $questions;

		$title 			= apply_filters( 'widget_title', $instance['title'] );
		$order 			= $instance[ 'order' ];
		$limit			= $instance[ 'limit' ];
		$category_ids	= $instance[ 'category_ids' ];

		if(!empty($category_ids)){
			$category_ids = explode(',', str_replace(' ', '', $category_ids));
		}

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		$question_args = array(
			'showposts' 	=> $limit,
			'orderby' 		=> $order,
		);

		if(is_array($category_ids) && count($category_ids) > 0){
			$question_args['tax_query'][] = array(
                'taxonomy' => 'question_category',
                'field'    => 'term_id',
                'terms'    => $category_ids
            );
        }

		$questions = ap_get_questions( $question_args );
		echo '<div class="ap-widget-inner">';
		ap_get_template_part('widget-questions');
		echo '</div>';
		echo $args['after_widget'];
		wp_reset_postdata();
	}

	public function form( $instance ) {
		$title 			= __( 'Questions', 'ap' );
		$order			= 'active';
		$limit			= 5;
		$category_ids   = '';

		if ( isset( $instance[ 'title' ] ) )
			$title = $instance[ 'title' ];

		if ( isset( $instance[ 'order' ] ) )
			$order = $instance[ 'order' ];

		if ( isset( $instance[ 'limit' ] ) )
			$limit = $instance[ 'limit' ];

		if ( isset( $instance[ 'category_ids' ] ) )
			$category_ids = $instance[ 'category_ids' ];

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
		<?php if(taxonomy_exists( 'question_category' )): ?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Category IDs:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'category_ids' ); ?>" name="<?php echo $this->get_field_name( 'category_ids' ); ?>" type="text" value="<?php echo esc_attr( $category_ids ); ?>">
				<small><?php _e('Comma separted AnsPress category ids', 'ap'); ?></small>
			</p>
		<?php endif; ?>
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
		$instance['category_ids'] = ( ! empty( $new_instance['category_ids'] ) ) ? strip_tags( $new_instance['category_ids'] ) : '';

		return $instance;
	}
}

function ap_questions_register_widgets() {
	register_widget( 'AP_Questions_Widget' );
}

add_action( 'widgets_init', 'ap_questions_register_widgets' );