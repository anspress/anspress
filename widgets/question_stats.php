<?php
/**
 * AnsPress question stats widget.
 *
 * Widget for showing question stats
 *
 * @package AnsPress
 * @author Rahul Aryan <rah12@live.com>
 * @license GPL 2+ GNU GPL licence above 2+
 * @link https://anspress.net
 * @since 2.0.0
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	wp_die();
}

/**
 * Class for AnsPress question status widget.
 *
 * @since unknown
 */
class AnsPress_Stats_Widget extends WP_Widget {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		parent::__construct(
			'ap_stats_widget',
			__( '(AnsPress) Question Stats', 'anspress-question-answer' ),
			array( 'description' => __( 'Shows question stats in single question page.', 'anspress-question-answer' ) )
		);
	}

	/**
	 * Widget render method.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';

		/**
		 * Filters the widget title.
		 *
		 * @param string $title Widget title.
		 * @since 1.0.0
		 */
		$title = apply_filters( 'widget_title', $title );

		echo wp_kses_post( $args['before_widget'] );

		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . $title . $args['after_title'] );
		}

		$ans_count   = ap_get_answers_count( get_question_id() );
		$last_active = ap_get_last_active( get_question_id() );
		$view_count  = ap_get_post_field( 'views', get_question_id() );
		$view_count  = is_numeric( $view_count ) ? (int) $view_count : 0;

		echo '<div class="ap-widget-inner">';

		if ( is_question() ) {
			echo '<ul class="ap-stats-widget">';

			echo '<li><span class="stat-label apicon-pulse">' . esc_attr__( 'Active', 'anspress-question-answer' ) . '</span><span class="stat-value"><time class="published updated" itemprop="dateModified" datetime="' . esc_attr( (string) mysql2date( 'c', $last_active ) ) . '">' . esc_html( $last_active ) . '</time></span></li>';

			$views_count = sprintf(
				// translators: Placeholder contains view count.
				_n( '%d time', '%d times', $view_count, 'anspress-question-answer' ),
				$view_count
			);

			echo '<li><span class="stat-label apicon-eye">' . esc_attr__( 'Views', 'anspress-question-answer' ) . '</span><span class="stat-value">' . esc_attr( $views_count ) . '</span></li>';

			$answers_count = sprintf(
				// translators: First and last contain span and middle is answer count.
				_n( '%2$s1%3$s answer', '%2$s%1$d%3$s answers', $ans_count, 'anspress-question-answer' ), // phpcs:ignore WordPress.WP.I18n
				$ans_count,
				'<span data-view="answer_count">',
				'</span>'
			);

			echo '<li><span class="stat-label apicon-answer">' . esc_attr__( 'Answers', 'anspress-question-answer' ) . '</span><span class="stat-value">' . wp_kses_post( $answers_count ) . '</span></li>';

			echo '</ul>';
		} else {
			esc_attr_e( 'This widget can only be used in single question page', 'anspress-question-answer' );
		}

		echo '</div>';

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Widget form.
	 *
	 * @param array $instance Instance of widget.
	 * @return string
	 */
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = __( 'Question stats', 'anspress-question-answer' );
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
 * Callback function to register stats widget.
 *
 * @return void
 */
function ap_stats_register_widgets() {
	register_widget( 'AnsPress_Stats_Widget' );
}

add_action( 'widgets_init', 'ap_stats_register_widgets' );
