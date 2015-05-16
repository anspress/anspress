<?php
/**
 * AnsPress participants question
 * Widget for showing participants button
 * @package AnsPress
 * @author Rahul Aryan <support@anspress.io>
 * @license GPL 2+ GNU GPL licence above 2+
 * @link http://anspress.io
 * @since 2.0.0-alpha2
 *  
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class AnsPress_Breadcrumbs_Widget extends WP_Widget {

	public function AnsPress_Breadcrumbs_Widget() {
		// Instantiate the parent object
		parent::__construct( false, __('(AnsPress) Breadcrumbs', 'ap') );
	}

	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		ap_breadcrumbs();

		echo $args['after_widget'];
	}

	public function form( $instance ) {

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

	}
}

function ap_breadcrumbs_register_widgets() {
	register_widget( 'AnsPress_Breadcrumbs_Widget' );
}

add_action( 'widgets_init', 'ap_breadcrumbs_register_widgets' );