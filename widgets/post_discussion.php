<?php
class AP_PostDiscussion_Widget extends WP_Widget {

	public function AP_PostDiscussion_Widget() {
		// Instantiate the parent object
		parent::__construct( false, 'AnsPress Post Discussion' );
	}

	public function widget( $args, $instance ) {

		echo $args['before_widget'];
		ap_qa_on_post();
		echo $args['after_widget'];
	}

	public function form( $instance ) {		
		?>
		<p>
			<?php _e('No options', 'anspress-question-answer'); ?>
		</p>
		<?php 
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		return $instance;
	}
}

function ap_postDiscussion_register_widgets() {
	register_widget( 'AP_PostDiscussion_Widget' );
}

add_action( 'widgets_init', 'ap_postDiscussion_register_widgets' );