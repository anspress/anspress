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
	 *
	 * @since 2.4.8 Removed `$ap` args.
	 */
	public static function init() {
		ap_add_default_options([
			'recaptcha_method'   => 'post',
		]);
		anspress()->add_action( 'ap_option_groups', __CLASS__, 'options' );
		anspress()->add_action( 'ap_process_ask_form', __CLASS__, 'verification_response' );
		anspress()->add_action( 'ap_process_answer_form', __CLASS__, 'verification_response' );
		anspress()->add_action( 'ap_ask_form_fields', __CLASS__, 'ap_ask_form_fields', 10, 2 );
		anspress()->add_action( 'ap_answer_form_fields', __CLASS__, 'ap_ask_form_fields', 10, 2 );
	}

	/**
	 * Register Categories options
	 */
	public static function options() {
		// Register recpatcha options.
		ap_register_option_section( 'addons', 'recpatcha',  __( 'reCaptcha', 'anspress-question-answer' ), [
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
			array(
				'name'    => 'recaptcha_method',
				'label'   => __( 'Recaptcha Method', 'anspress-question-answer' ),
				'desc'    => __( 'Select method to use when verification keeps failing', 'anspress-question-answer' ),
				'type'    => 'select',
				'options' => [ 'curl' => 'CURL', 'post' => 'POST' ],
			) ,
		]);
	}

	/**
	 * Send ajax response if capatcha verification fails.
	 * @since 3.0.0
	 */
	public static function verification_response() {
		if ( ap_show_captcha_to_user() && false === SELF::verify_recaptcha() ) {
			ap_ajax_json( array(
				'form' 			=> $_POST['ap_form_action'],
				'message'		=> 'captcha_error',
				'errors'		=> array( 'captcha' => __( 'Bot verification failed.', 'anspress-question-answer' ) ),
			) );
		}
	}

	/**
	 * Check reCaptach verification.
	 *
	 * @return boolean
	 * @since  3.0.0
	 */
	public static function verify_recaptcha() {
		require_once( ANSPRESS_ADDONS_DIR . '/free/recaptcha/autoload.php' );
		$method = ap_opt( 'recaptcha_method' ) === 'curl' ? new \ReCaptcha\RequestMethod\CurlPost() : new \ReCaptcha\RequestMethod\Post();
		$recaptcha = new \ReCaptcha\ReCaptcha( trim( ap_opt( 'recaptcha_secret_key' ) ), $method );
		$ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP ); //@codingStandardsIgnoreLine.
		$captcha_response = ap_sanitize_unslash( 'g-recaptcha-response', 'r' );
		$resp = $recaptcha->verify( $captcha_response, $ip );

		if ( $resp->isSuccess() ) {
			do_action( 'ap_form_captcha_verified' );
			return true;
		}

		return false;
	}

	public static function ap_ask_form_fields( $args, $editing ) {
		global $editing_post;

		if ( ap_show_captcha_to_user() ) {
			// Show recpatcha if key exists and enabled.
			if ( ap_opt( 'recaptcha_site_key' ) == '' ) {
				$html = '<div class="ap-notice red">' . __( 'reCaptach keys missing, please add keys', 'anspress-question-answer' ) . '</div>';
			} else {

				$html = '<div class="g-recaptcha" id="recaptcha" data-sitekey="' . ap_opt( 'recaptcha_site_key' ) . '"></div>';

				$html .= '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=' . get_locale() . '&onload=onloadCallback&render=explicit" async defer></script>';

				ob_start();
				?>
					<script type="text/javascript">
						var onloadCallback = function() {
						widgetId1 = grecaptcha.render("recaptcha", {
							"sitekey" : "<?php echo ap_opt( 'recaptcha_site_key' ); ?>"
							});
						};

						jQuery(document).ready(function(){
							// Rest widget after answer form get submitted
							if(typeof AnsPress !== 'undefined'){
								AnsPress.on('answerFormPosted', function(){
									if(typeof grecaptcha !== 'undefined')
										grecaptcha.reset(widgetId1);
								});
							}
						});

					</script>
				<?php
				$html .= ob_get_clean();
			}

			$args['fields'][] = array(
				'name'  => 'captcha',
				'type'  => 'custom',
				'order' => 100,
				'html' 	=> $html,
			);
		}

		return $args;
	}
}



AnsPress_reCcaptcha::init();
