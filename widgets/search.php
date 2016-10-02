<?php
/**
 * AnsPress search widget
 * An ajax based search widget for searching questions and answers
 * @package AnsPress
 * @author Rahul Aryan <support@anspress.io>
 * @license GPL 2+ GNU GPL licence above 2+
 * @link https://anspress.io
 * @since 2.0.0-alpha2
 *  
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

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

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		?>
			<form id="ap-search-form" class="ap-search-form" action="<?php echo ap_get_link_to('search'); ?>" method="GET">
				<div class="ap-qaf-inner">
					<input class="form-control" type="text" name="ap_s" id="ap-quick-ask-input" placeholder="<?php _e('Search questions & answers', 'anspress-question-answer'); ?>" value="<?php echo sanitize_text_field(get_query_var('ap_s')); ?>" autocomplete="off" />
					<button type="submit" ><?php _e('Search', 'anspress-question-answer'); ?></button>
				</div>
			</form>
		<?php
		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Search questions', 'anspress-question-answer' );
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'anspress-question-answer' ); ?></label> 
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

function ap_search_register_widgets() {
	register_widget( 'AP_Search_Widget' );
}

add_action( 'widgets_init', 'ap_search_register_widgets' );