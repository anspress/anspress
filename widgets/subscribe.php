<?php
/**
 * AnsPress subscribe question
 * Widget for showing subscribe button
 * @package AnsPress
 * @author Rahul Aryan <rah12@live.com>
 * @license GPL 2+ GNU GPL licence above 2+
 * @link http://wp3.in
 * @since 2.0.0-alpha2
 *  
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class AnsPress_Subscribe_Widget extends WP_Widget {

	public function AnsPress_Subscribe_Widget() {
		// Instantiate the parent object
		parent::__construct( false, __('AnsPress Subscribe', 'ap') );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		ap_subscribe_btn_html();
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Subscribe questions', 'ap' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}

function ap_subscribe_register_widgets() {
	register_widget( 'AnsPress_Subscribe_Widget' );
}

add_action( 'widgets_init', 'ap_subscribe_register_widgets' );