<?php
/**
 * AnsPress breadcrumbs widget
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 * @license GPL 2+ GNU GPL licence above 2+
 * @link    https://anspress.io
 * @since   2.0.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AnsPress breadcrumbs widget.
 */
class AnsPress_Breadcrumbs_Widget extends WP_Widget {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		parent::__construct(
			'ap_breadcrumbs_widget',
			__( '(AnsPress) Breadcrumbs', 'anspress-question-answer' ),
			array( 'description' => __( 'Show current anspress page navigation', 'anspress-question-answer' ) )
		);
	}

	/**
	 * Output widget
	 * @param  array $args     Widget arguments.
	 * @param  array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		ap_breadcrumbs();
		echo $args['after_widget'];
	}
}

/**
 * Register breadcrumbs widget
 */
function register_anspress_breadcrumbs() {
	register_widget( 'AnsPress_Breadcrumbs_Widget' );
}
add_action( 'widgets_init', 'register_anspress_breadcrumbs' );
