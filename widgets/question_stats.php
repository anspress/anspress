<?php
/**
 * AnsPress question stats widget
 * Widget for showing question stats
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

class AnsPress_Stats_Widget extends WP_Widget {

	function AnsPress_Stats_Widget() {
		// Instantiate the parent object
		parent::__construct( false, __('AnsPress Stats', 'ap') );
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$ans_count 		= ap_count_answer_meta();
		$last_active 	= ap_last_active(get_the_ID());
		$total_favs 	= ap_post_subscribers_count(get_the_ID());
		$view_count 	= ap_get_qa_views();


		echo '<ul class="ap-stats-widget">';
		echo '<li>'.ap_icon('history', true). sprintf( __( 'Last activity <time class="updated" itemprop="dateUpdated" datetime="%s">%s</time> Ago.', 'ap' ), mysql2date('c', $last_active),  ap_human_time( mysql2date('U', $last_active))).'</li>';
		echo '<li>'.ap_icon('view', true). sprintf( __('<strong>%d views</strong> on this question.', 'ap'), $view_count).'</li>' ;		
		echo '<li>'.ap_icon('answer', true). sprintf( _n('<a href="#answers">1 Answer</a> on this question.', '<a href="#answers">%d Answer</a> on this question.', $ans_count, 'ap'), $ans_count).'</li>' ;		
		echo '<li>'.ap_icon('mail', true). ap_subscriber_count_html() .'</li>';
		echo '</ul>';

		echo $args['after_widget'];
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Question stats', 'ap' );
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

function ap_stats_register_widgets() {
	register_widget( 'AnsPress_Stats_Widget' );
}

add_action( 'widgets_init', 'ap_stats_register_widgets' );