<?php
/**
 * AnsPress search widget
 * An ajax based search widget for searching questions and answers
 *
 * @author Rahul Aryan <rah12@live.com>
 * @license GPL 3+ GNU GPL licence above 3+
 * @link https://anspress.net
 * @since 2.0.0
 * @package AnsPress
 * @subpackage Widget
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * (AnsPress) Search Widget class.
 */
class AP_Search_Widget extends WP_Widget {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		parent::__construct(
			'AP_Search_Widget',
			__( '(AnsPress) Search', 'anspress-question-answer' ),
			array( 'description' => __( 'Question and answer search form.', 'anspress-question-answer' ) )
		);
	}

	/**
	 * Output widget
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Widget instance.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';

		/**
		 * This filter is documented in widgets/question_stats.php
		 */
		$title = apply_filters( 'widget_title', $title );

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		ap_get_template_part( 'search-form' );

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Widget form
	 *
	 * @param array $instance Widget instance.
	 * @return string
	 */
	public function form( $instance ) {
		$title = __( 'Search questions', 'anspress-question-answer' );

		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}

		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
		return 'noform';
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

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}

/**
 * Register (AnsPress) Search widget.
 *
 * @return void
 */
function ap_search_register_widgets() {
	register_widget( 'AP_Search_Widget' );
}
add_action( 'widgets_init', 'ap_search_register_widgets' );
