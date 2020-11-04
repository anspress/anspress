<?php
/**
 * AnsPress questions widget form.
 *
 * @package    AnsPress
 * @subpackage Widget
 * @author     Rahul Aryan <rah12@live.com>
 * @license    GPL 3+ GNU GPL licence above 3+
 * @link       https://anspress.net
 * @since      2.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The question widget class.
 */
class AP_Questions_Widget extends WP_Widget {

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		parent::__construct(
			'ap_questions_widget',
			__( '(AnsPress) Questions', 'anspress-question-answer' ),
			array( 'description' => __( 'Shows list of question shorted by option.', 'anspress-question-answer' ) )
		);
	}

	/**
	 * Widget render
	 *
	 * @param array $args Arguments.
	 * @param array $instance Widget arguments.
	 * @return void
	 */
	public function widget( $args, $instance ) {
		$instance = wp_parse_args( $instance, array(
			'widget_title' => __( 'Questions', 'anspress-question-answer' ),
			'order_by'     => 'active',
		) );

		/**
		 * This filter is documented in widgets/question_stats.php
		 */
		$title = apply_filters( 'widget_title', $instance['title'] );

		$order_by     = $instance[ 'order_by' ];
		$limit        = $instance[ 'limit' ];
		$category_ids = $instance[ 'category_ids' ];

		if ( ! empty( $category_ids ) ) {
			$category_ids = explode( ',', str_replace( ' ', '', $category_ids ) );
		}

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$question_args = array(
			'showposts' 	 => $limit,
			'ap_order_by'  => $order_by,
			'paged'			   => 1,
		);

		if ( is_array( $category_ids ) && count( $category_ids ) > 0 ) {
			$question_args['tax_query'][] = array(
				'taxonomy' => 'question_category',
				'field'    => 'term_id',
				'terms'    => $category_ids,
			);
		}

		anspress()->questions = ap_get_questions( $question_args );
		echo '<div class="ap-widget-inner">';
		ap_get_template_part( 'widgets/widget-questions' );
		echo '</div>';
		echo $args['after_widget'];

		wp_reset_postdata();
	}

	public function form( $instance ) {
		$title 			= __( 'Questions', 'anspress-question-answer' );
		$order_by		= 'active';
		$limit			= 5;
		$category_ids   = '';

		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}

		if ( isset( $instance[ 'order_by' ] ) ) {
			$order_by = $instance[ 'order_by' ];
		}

		if ( isset( $instance[ 'limit' ] ) ) {
			$limit = $instance[ 'limit' ];
		}

		if ( isset( $instance[ 'category_ids' ] ) ) {
			$category_ids = $instance[ 'category_ids' ];
		}

		?>
        <p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
			<label for="<?php echo $this->get_field_id( 'order_by' ); ?>"><?php _e( 'Order by:', 'anspress-question-answer' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'order_by' ); ?>" name="<?php echo $this->get_field_name( 'order_by' ); ?>">
				<option <?php selected( $order_by, 'active' ); ?> value="active"><?php _e( 'Active', 'anspress-question-answer' ); ?></option>
				<option <?php selected( $order_by, 'newest' ); ?> value="newest"><?php _e( 'Newest', 'anspress-question-answer' ); ?></option>
				<option <?php selected( $order_by, 'voted' ); ?> value="voted"><?php _e( 'Voted', 'anspress-question-answer' ); ?></option>
				<option <?php selected( $order_by, 'answers' ); ?> value="answers"><?php _e( 'Answers', 'anspress-question-answer' ); ?></option>
				<option <?php selected( $order_by, 'unanswered' ); ?> value="unanswered"><?php _e( 'Unanswered', 'anspress-question-answer' ); ?></option>
            </select>
        </p>
		<?php if ( taxonomy_exists( 'question_category' ) ) : ?>
            <p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Category IDs:', 'anspress-question-answer' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'category_ids' ); ?>" name="<?php echo $this->get_field_name( 'category_ids' ); ?>" type="text" value="<?php echo esc_attr( $category_ids ); ?>">
				<small><?php _e( 'Comma separted AnsPress category ids', 'anspress-question-answer' ); ?></small>
            </p>
		<?php endif; ?>
        <p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit:', 'anspress-question-answer' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>">
        </p>

		<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['avatar'] = ( ! empty( $new_instance['avatar'] ) ) ? strip_tags( $new_instance['avatar'] ) : '';
		$instance['order_by'] = ( ! empty( $new_instance['order_by'] ) ) ? strip_tags( $new_instance['order_by'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : 5;
		$instance['category_ids'] = ( ! empty( $new_instance['category_ids'] ) ) ? strip_tags( $new_instance['category_ids'] ) : '';

		return $instance;
	}
}

function ap_questions_register_widgets() {
	register_widget( 'AP_Questions_Widget' );
}

add_action( 'widgets_init', 'ap_questions_register_widgets' );
