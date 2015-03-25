<?php
class AP_Related_questions extends WP_Widget {

	public function AP_Related_questions() {
		// Instantiate the parent object
		parent::__construct( false, 'AnsPress Related Questions' );
	}

	public function widget( $args, $instance ) {
		$title 			= apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) 
			echo $args['before_title'] . $title . $args['after_title'];

		if(!class_exists('Tags_For_AnsPress')){
			echo 'Tags plugin must be installed for related question. Get <a href="https://wordpress.org/plugins/tags-for-anspress">Tags for AnsPress</a>';
			return;
		}
		$tags = get_the_terms(get_question_id(), 'question_tag' );

		$tags_in = array();

		if($tags)
			foreach($tags as $t)
				$tags_in[] = $t->term_id;

		$question_args=array(
			'tax_query' 	=> array(
				'taxonomy' => 'question_tag',
				'field'    => 'id',
				'terms'    => $tags_in,
			),
			'showposts' 	=> 10,
			'post__not_in' 	=> array(get_question_id()),
		);
			
		$questions = new Question_Query( $question_args );

		include ap_get_theme_location('widget-related_questions.php');
		echo $args['after_widget'];
		wp_reset_postdata();
	}

	public function form( $instance ) {
		$title 			= __( 'Related Questions', 'ap' );

		
		if ( isset( $instance[ 'title' ] ) )
			$title = $instance[ 'title' ];		
		
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

function ap_rquestions_register_widgets() {
	register_widget( 'AP_Related_questions' );
}

add_action( 'widgets_init', 'ap_rquestions_register_widgets' );