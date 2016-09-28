<?php
/**
 * AnsPress participants question
 * Widget for showing participants button
 * @package AnsPress
 * @author Rahul Aryan <support@anspress.io>
 * @license GPL 2+ GNU GPL licence above 2+
 * @link https://anspress.io
 * @since 2.0.0-alpha2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

class AnsPress_Participants_Widget extends WP_Widget {
	/**
	 * Initialize the class
	 */
	public function __construct() {
		parent::__construct(
			'AnsPress_Participants_Widget',
			__( '(AnsPress) Participants', 'anspress-question-answer' ),
			array( 'description' => __( 'Show question participants', 'anspress-question-answer' ) )
		);

	}

	public function widget( $args, $instance ) {
		$title 			= apply_filters( 'widget_title', $instance['title'] );
		$avatar_size 	= $instance['avatar_size'];

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		ap_get_all_parti( $avatar_size );
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		$title 			= isset( $instance[ 'title' ] ) ? $instance[ 'title' ] : '';
		$avatar_size 	= isset( $instance[ 'avatar_size' ] ) ? $instance[ 'avatar_size' ] : 30;

		?>
        <p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'anspress-question-answer' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'avatar_size' ); ?>"><?php _e( 'Avatar size:', 'anspress-question-answer' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'avatar_size' ); ?>" name="<?php echo $this->get_field_name( 'avatar_size' ); ?>" type="text" value="<?php echo esc_attr( $avatar_size ); ?>">
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
		$instance['avatar_size'] = ( ! empty( $new_instance['avatar_size'] ) ) ? (int) $new_instance['avatar_size'] : 30;

		return $instance;
	}
}

function ap_participants_register_widgets() {
	register_widget( 'AnsPress_Participants_Widget' );
}

add_action( 'widgets_init', 'ap_participants_register_widgets' );

