<?php
/**
 * Class for AnsPress base page shortcode
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

class AnsPress_BasePage_Shortcode {

	protected static $instance = NULL;

    public static function get_instance()
    {
        // create an object
        NULL === self::$instance and self::$instance = new self;

        return self::$instance; // return the object
    }

	/**
	 * Control the output of [anspress] shortcode
	 * @param  array $atts
	 * @param  string $content
	 * @return string
	 * @since 2.0.0-beta
	 */
	public function anspress_sc( $atts, $content="" ) {
		
		global $questions, $wp;

		//var_dump($wp->query_vars);

		ob_start();
		echo '<div class="anspress-container">';
			
			/**
			 * ACTION: ap_before
			 * Action is fired before loading AnsPress body.
			 */
			do_action('ap_before');
			
			// include theme file
			ap_page();

			
		echo '</div>';
		return ob_get_clean();
		wp_reset_postdata();		
	}
	
}

