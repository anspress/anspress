<?php
/**
 * AnsPress user profile blocks
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_User_Profile_Blocks {

	/**
	 * Initialize the class
	 * @return void 
	 * @since  2.0
	 */
	public function __construct()
	{
		//add_action('ap_user_profile_block', array($this, 'about_me'));		
	}


	/**
	 * Hook about me block in user profile
	 * @return void
	 * @since 2.0
	 */
	public function about_me()
	{
		

	}

	
}
