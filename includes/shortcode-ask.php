<?php
/**
 * Ask form sortcode
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class AnsPress_Ask_Shortcode {

	/**
	 * Output for anspress_ask shortcode
	 * @param  $atts
	 * @param  string $content
	 * @since 2.0.1
	 */
	public static function anspress_ask($atts, $content = ''){
		ob_start();
		echo '<div class="anspress-container">';
			
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');
			
			// include theme file
			include ap_get_theme_location('ask.php');

		echo '</div>';
		return ob_get_clean();

	}

	
}
