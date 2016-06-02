<?php
class AP_Questions_Widget extends WP_Widget {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		parent::__construct(
			'ap_questions_widget',
			__( '(AnsPress) Questions', 'anspress-question-answer' ),
			array( 'description' => __( 'Shows list of question shorted by option.', 'anspress-question-answer' ) )
		);
	}

	public function widget( $args, $instance ) {
		global $questions;

		$title 			= apply_filters( 'widget_title', $instance['title'] );
		$sort 			= $instance[ 'sort' ];
		$limit			= $instance[ 'limit' ];
		$category_ids	= $instance[ 'category_ids' ];

		if ( ! empty( $category_ids ) ) {
			$category_ids = explode( ',', str_replace( ' ', '', $category_ids ) );
		}

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$question_args = array(
			'showposts' 	=> $limit,
			'sortby' 		=> $sort,
			'paged'			=> 1,
		);

		if ( is_array( $category_ids ) && count( $category_ids ) > 0 ) {
			$question_args['tax_query'][] = array(
				'taxonomy' => 'question_category',
				'field'    => 'term_id',
				'terms'    => $category_ids,
			);
		}

		$questions = ap_get_questions( $question_args );
		echo '<div class="ap-widget-inner">';
		ap_get_template_part( 'widget-questions' );
		echo '</div>';
		echo $args['after_widget'];
		wp_reset_postdata();
	}

	public function form( $instance ) {
		$title 			= __( 'Questions', 'anspress-question-answer' );
		$sort			= 'active';
		$limit			= 5;
		$category_ids   = '';

		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}

		if ( isset( $instance[ 'sort' ] ) ) {
			$sort = $instance[ 'sort' ];
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
			<label for="<?php echo $this->get_field_id( 'sort' ); ?>"><?php _e( 'Sort by:', 'anspress-question-answer' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'sort' ); ?>" name="<?php echo $this->get_field_name( 'sort' ); ?>">
				<option <?php selected( $sort, 'active' ); ?> value="active"><?php _e( 'Active', 'anspress-question-answer' ); ?></option>
				<option <?php selected( $sort, 'newest' ); ?> value="newest"><?php _e( 'Newest', 'anspress-question-answer' ); ?></option>
				<option <?php selected( $sort, 'voted' ); ?> value="voted"><?php _e( 'Voted', 'anspress-question-answer' ); ?></option>
				<option <?php selected( $sort, 'answers' ); ?> value="answers"><?php _e( 'Answers', 'anspress-question-answer' ); ?></option>
				<option <?php selected( $sort, 'unanswered' ); ?> value="unanswered"><?php _e( 'Unanswered', 'anspress-question-answer' ); ?></option>
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
		$instance['sort'] = ( ! empty( $new_instance['sort'] ) ) ? strip_tags( $new_instance['sort'] ) : '';
		$instance['limit'] = ( ! empty( $new_instance['limit'] ) ) ? strip_tags( $new_instance['limit'] ) : 5;
		$instance['category_ids'] = ( ! empty( $new_instance['category_ids'] ) ) ? strip_tags( $new_instance['category_ids'] ) : '';

		return $instance;
	}
}

function ap_questions_register_widgets() {
	register_widget( 'AP_Questions_Widget' );
}

add_action( 'widgets_init', 'ap_questions_register_widgets' );
