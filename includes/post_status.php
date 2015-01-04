<?php

/**
 * Post status related codes
 *
 * @link http://wp3.in
 * @since 2.0
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
			  'label'                     => __( 'Closed', 'ap' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Closed <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>' )
		 ) );
		 
		 register_post_status( 'moderate', array(
			  'label'                     => __( 'Moderate', 'ap' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Moderate <span class="count">(%s)</span>', 'Moderate <span class="count">(%s)</span>' )
		 ) );
		 
		 register_post_status( 'private_question', array(
			  'label'                     => __( 'Private Question', 'ap' ),
			  'public'                    => true,
			  'show_in_admin_all_list'    => false,
			  'show_in_admin_status_list' => true,
			  'label_count'               => _n_noop( 'Private Question <span class="count">(%s)</span>', 'Private Question <span class="count">(%s)</span>' )
		 ) );
	}
}

?>