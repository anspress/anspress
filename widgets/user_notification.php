<?php
/**
 * AnsPress user notifications widget
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 * @license GPL 3+ GNU GPL licence above 3+
 * @link    https://anspress.io
 * @since   2.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AnsPress breadcrumbs widget.
 */
class AnsPress_User_Notifications_Widget extends WP_Widget {

	/**
	 * Initialize the class
	 */
	public function __construct() {
		parent::__construct(
			'ap_user_notifications_widget',
			__( '(AnsPress) User Notifications', 'anspress-question-answer' ),
			array( 'description' => __( 'Show logged in user notifications', 'anspress-question-answer' ) )
		);
	}

	/**
	 * Output widget
	 * @param  array $args     Widget arguments.
	 * @param  array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		global $ap_activities;
        $ap_activities = ap_get_activities( array( 'per_page' => 20, 'notification' => true, 'user_id' => ap_get_displayed_user_id() ) );

		echo $args['before_widget'];

		ap_get_template_part( 'widgets/notifications' );

		echo $args['after_widget'];
	}
}

/**
 * Register breadcrumbs widget
 */
function register_anspress_user_notifications() {
	register_widget( 'AnsPress_User_Notifications_Widget' );
}
add_action( 'widgets_init', 'register_anspress_user_notifications' );
