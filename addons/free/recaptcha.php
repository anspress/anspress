<?php
/**
 * An AnsPress add-on to check and filter bad words in
 * question, answer and comments. Add restricted words
 * after activating addon.
 *
 * @author    Rahul Aryan <support@rahularyan.com>
 * @copyright 2014 AnsPress.io & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   WordPress/AnsPress/BadWords
 *
 * Addon Name:    reCaptcha
 * Addon URI:     https://anspress.io
 * Description:   Add reCaptcha support in AnsPress form.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Bad words filter hooks.
 */
class AnsPress_reCcaptcha {
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 * @since 2.4.8 Removed `$ap` args.
	 */
	public static function init( ) {
		anspress()->add_action( 'ap_option_groups', __CLASS__, 'options' );

	}

	/**
	 * Register Categories options
	 */
	public static function options() {
		// Register recpatcha options.
		ap_register_option_section( 'addons', 'recpatcha',  __( 'reCaptcha', 'anspress-question-answer' ), [
			array(
				'name'  => 'enable_recaptcha',
				'label' => __( 'Enable reCaptcha', 'anspress-question-answer' ),
				'desc'  => __( 'Use this for preventing spam posts.', 'anspress-question-answer' ),
				'type'  => 'checkbox',
			) ,
			array(
				'name'  => 'recaptcha_site_key',
				'label' => __( 'Recaptcha site key', 'anspress-question-answer' ),
				'desc'  => __( 'Enter your site key, if you dont have it get it from here https://www.google.com/recaptcha/admin', 'anspress-question-answer' ),
			) ,
			array(
				'name'  => 'recaptcha_secret_key',
				'label' => __( 'Recaptcha secret key', 'anspress-question-answer' ),
				'desc'  => __( 'Enter your secret key', 'anspress-question-answer' ),
			) ,
		]);
	}

}

AnsPress_reCcaptcha::init();
