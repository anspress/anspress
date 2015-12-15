<?php

/**
 * Post status related codes
 *
 * @link http://anspress.io
 * @since 2.0.1
 * @license GPL2+
 * @package AnsPress
 */

class AnsPress_Post_Status
{
	public function __construct()
	{
		add_action('init', array($this, 'register_post_status'));
	}

	public function register_post_status()
	{
		register_post_status( 'closed', array(
			  'label'                     => __( 'Closed', 'anspress-question-answer' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>', 'anspress-question-answer' )
		 ) );
		 
		 register_post_status( 'moderate', array(
			  'label'                     => __( 'Moderate', 'anspress-question-answer' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Moderate <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>', 'anspress-question-answer' )
		 ) );
		 
		 register_post_status( 'private_post', array(
			  'label'                     => __( 'Private Post', 'anspress-question-answer' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Private Post <span class="count">(%s)</span>', 'Private Post <span class="count">(%s)</span>', 'anspress-question-answer' )
		 ) );
	}
}
