<?php
/**
 * An AnsPress add-on to check and filter bad words in
 * question, answer and comments. Add restricted words
 * after activating addon.
 *
 * @author     Rahul Aryan <support@rahularyan.com>
 * @copyright  2014 anspress.net & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.net
 * @package    AnsPress
 * @subpackage reCaptcha Addon
 */

namespace Anspress\Addons;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use ReCaptcha\ReCaptcha;

/**
 * Include captcha field.
 */
require_once ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/class-captcha.php';

/**
 * The reCaptcha class.
 */
class Captcha extends \AnsPress\Singleton {
	/**
	 * Instance of this class.
	 *
	 * @var     object
	 * @since 4.1.8
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since 2.4.8 Removed `$ap` args.
	 */
	protected function __construct() {
		ap_add_default_options( [
			'recaptcha_method'        => 'post',
			'recaptcha_exclude_roles' => [ 'ap_moderator' => 1 ],
		]);

		anspress()->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts' );
		anspress()->add_action( 'ap_form_addon-recaptcha', $this, 'options' );
		anspress()->add_action( 'ap_question_form_fields', $this, 'ap_question_form_fields', 10, 2 );
		anspress()->add_action( 'ap_answer_form_fields', $this, 'ap_question_form_fields', 10, 2 );
		anspress()->add_action( 'ap_comment_form_fields', $this, 'ap_question_form_fields', 10, 2 );
	}

	/**
	 * Enqueue script.
	 *
	 * @return void
	 * @since 4.1.9
	 */
	public function enqueue_scripts() {
		wp_register_script( 'grecaptcha', 'https://www.google.com/recaptcha/api.js?hl=' . get_locale() . '&render=explicit' );
		wp_enqueue_script( 'ap-recaptcha', ANSPRESS_URL . 'addons/recaptcha/script.js', [], true );
	}

	/**
	 * Register Categories options
	 */
	public function options() {
		global $wp_roles;
		$opt = ap_opt();

		$roles = [];
		foreach ( $wp_roles->roles as $key => $role ) {
			$roles[ $key ] = $role['name'];
		}

		$form = array(
			'fields' => array(
				'recaptcha_site_key'   => array(
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
				'recaptcha_exclude_roles' => array(
					'label'   => __( 'Hide reCaptcha for roles', 'anspress-question-answer' ),
					'desc'    => __( 'Select roles for which reCaptcha will be hidden.', 'anspress-question-answer' ),
					'type'    => 'checkbox',
					'options' => $roles,
					'value'   => $opt['recaptcha_exclude_roles'],
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
	public function ap_question_form_fields( $form ) {
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

// Initialize the class.
Captcha::init();
