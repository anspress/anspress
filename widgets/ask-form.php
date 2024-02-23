<?php
/**
 * AnsPress ask widget form.
 *
 * @package AnsPress
 * @author  Rahul Aryan <rah12@live.com>
 * @license GPL 3+ GNU GPL licence above 3+
 * @link    https://anspress.net
 * @since   2.0.0
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Ask from widget.
 */
class AP_Askform_Widget extends WP_Widget {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		parent::__construct(
			'ap_askform_widget',
			__( '(AnsPress) Ask form', 'anspress-question-answer' ),
			array( 'description' => __( 'AnsPress ask form widget', 'anspress-question-answer' ) )
		);
	}

	/**
	 * Render widget.
	 *
	 * @param array $args     Arguments.
	 * @param array $instance Instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';

		/**
		 * This filter is documented in widgets/question_stats.php
		 */
		$title = apply_filters( 'widget_title', $title );
		$title = is_string( $title ) ? $title : '';

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . esc_html( $title ) . $args['after_title'] );
		}

		wp_enqueue_script( 'anspress-theme' );
		?>
		<div id="ap-ask-page" class="ap-widget-inner">
			<?php ap_ask_form(); ?>
		</div>
		<?php
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Form.
	 *
	 * @param array $instance Instacne.
	 * @return string
	 */
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Ask questions', 'anspress-question-answer' );
		}
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php esc_attr_e( 'Title:', 'anspress-question-answer' ); ?>
			</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<?php

		return 'noform';
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Old widget values.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}

/**
 * Register ask form widget.
 *
 * @return void
 */
function ap_quickask_register_widgets(): void {
	register_widget( 'AP_Askform_Widget' );
}
add_action( 'widgets_init', 'ap_quickask_register_widgets' );
