<?php
/**
 * AnsPress Captcha type field object.
 *
 * @package    AnsPress
 * @subpackage Fields
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.net>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace AnsPress\Form\Field;

use AnsPress\Form\Field as Field;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Captcha type field object.
 *
 * @since 4.1.0
 */
class Captcha extends Field {
	/**
	 * The field type.
	 *
	 * @var string
	 */
	public $type = 'captcha';

	private $response = null;

	/**
	 * Prepare field.
	 *
	 * @return void
	 */
	protected function prepare() {
		$this->args = wp_parse_args(
			$this->args, array(
				'label' => __( 'AnsPress reCaptcha Field', 'anspress-question-answer' ),
			)
		);
	}

	/**
	 * Validate captcha.
	 *
	 * @return boolean
	 */
	public function sanitize() {
		if ( true === $this->sanitized ) {
			return $this->sanitized_value;
		}

		require_once ANSPRESS_ADDONS_DIR . '/recaptcha/recaptcha/autoload.php';

		if ( ap_opt( 'recaptcha_method' ) === 'curl' ) {
			$method = new \ReCaptcha\RequestMethod\CurlPost();
		} else {
			$method = new \ReCaptcha\RequestMethod\Post();
		}

		$recaptcha = new \ReCaptcha\ReCaptcha(
			trim( ap_opt( 'recaptcha_secret_key' ) ),
			$method
		);

		$ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP ); //@codingStandardsIgnoreLine.
		$captcha_response = ap_sanitize_unslash( 'g-recaptcha-response', 'r' );
		$this->response   = $recaptcha->verify( $captcha_response, $ip );

		$this->sanitized = true;

		if ( $this->response->isSuccess() ) {
			$this->sanitized_value = true;
		} else {
			$this->add_error( 'captcha', __( 'Failed to verify captcha. Please try again.', 'anspress-question-answer' ) );
			$this->sanitized_value = false;
		}
	}

	/**
	 * Field markup.
	 *
	 * @return void
	 */
	public function field_markup() {
		parent::field_markup();

		if ( ap_opt( 'recaptcha_site_key' ) === '' ) {
			$this->add_html( '<div class="ap-notice red">' . __( 'Unable to render captcha. Please add reCpatcha keys in AnsPress options.', 'anspress-question-answer' ) . '</div>' );

			return;
		}

		$this->add_html( '<div class="g-recaptcha load-recaptcha" id="' . $this->id() . '" data-sitekey="' . ap_opt( 'recaptcha_site_key' ) . '"></div>' );

		$this->add_html( "<script type=\"text/javascript\" src=\"https://www.google.com/recaptcha/api.js?hl=" . get_locale() . "&onload=apCpatchaLoaded&render=explicit\"></script>" );

		/** This action is documented in lib/form/class-input.php */
		do_action_ref_array( 'ap_after_field_markup', [ &$this ] );
	}

}
