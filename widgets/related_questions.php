<?php
class AP_Related_questions extends WP_Widget {

	public function AP_Related_questions() {
		// Instantiate the parent object
		parent::__construct( false, '(AnsPress) Related Questions', array('description' => 'For showing related question. This widget will only work in question page.') );
	}

	public function widget( $args, $instance ) {
		global $questions;

		$title 			= apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		echo '<div class="ap-widget-inner">';
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
				array(
					'taxonomy' => 'question_tag',
					'field'    => 'term_id',
					'terms'    => $tags_in,
				)
			),
			'showposts' 	=> 5,
			'post__not_in' 	=> array(get_question_id()),
		);

		$questions = ap_get_questions($question_args);
		include ap_get_theme_location('widget-related_questions.php');
		wp_reset_postdata();
		echo '</div>';
		echo $args['after_widget'];

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