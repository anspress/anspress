<?php
/**
 * An AnsPress add-on to check and filter bad words in
 * question, answer and comments. Add restricted words
 * after activating addon.
 *
 * @author     Rahul Aryan <support@rahularyan.com>
 * @copyright  2014 AnsPress.io & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.io
 * @package    AnsPress
 * @subpackage reCaptcha Addon
 *
 * @anspress-addon
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

use AnsPress\Form\Field\Captcha as Captcha;

/**
 * Include captcha field.
 */
require_once ANSPRESS_ADDONS_DIR . '/free/recaptcha/class-captcha.php';

/**
 * The reCaptcha class.
 */
class AnsPress_reCcaptcha {
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since 2.4.8 Removed `$ap` args.
	 */
	public static function init() {
		ap_add_default_options([
			'recaptcha_method'  => 'post',
		]);

		anspress()->add_action( 'ap_form_addon-free_recaptcha', __CLASS__, 'options' );
		anspress()->add_action( 'ap_question_form_fields', __CLASS__, 'ap_question_form_fields', 10, 2 );
		anspress()->add_action( 'ap_answer_form_fields', __CLASS__, 'ap_question_form_fields', 10, 2 );
		anspress()->add_action( 'ap_comment_form_fields', __CLASS__, 'ap_question_form_fields', 10, 2 );
	}

	/**
	 * Register Categories options
	 */
	public static function options() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'recaptcha_site_key' => array(
					'label' => __( 'Recaptcha site key', 'anspress-question-answer' ),
					'desc'  => __( 'Enter your site key, if you dont have it get it from here https://www.google.com/recaptcha/admin', 'anspress-question-answer' ),
					'value' => $opt['recaptcha_site_key'],
				),
				'recaptcha_secret_key' => array(
					'label' => __( 'Recaptcha secret key', 'anspress-question-answer' ),
					'desc'  => __( 'Enter your secret key', 'anspress-question-answer' ),
					'value' => $opt['recaptcha_secret_key'],
				),
				'recaptcha_method' => array(
					'label'   => __( 'Recaptcha Method', 'anspress-question-answer' ),
					'desc'    => __( 'Select method to use when verification keeps failing', 'anspress-question-answer' ),
					'type'    => 'select',
					'options' => array(
						'curl' => 'CURL',
						'post' => 'POST',
					),
					'value'   => $opt['recaptcha_method'],
				),
			),
		);

		return $form;
	}

	/**
	 * Add captcha field in question and answer form.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 * @since 4.1.0
	 */
	public static function ap_question_form_fields( $form ) {
		if ( ap_show_captcha_to_user() ) {
			$form['fields']['captcha'] = array(
				'label' => __( 'Prove that you are a human', 'anspress-question-answer' ),
				'type'  => 'captcha',
				'order' => 100,
			);
		}

		return $form;
	}

}

AnsPress_reCcaptcha::init();
