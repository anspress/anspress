<?php
/*
	Name:reCAPTCHA
	Description: Add reCAPTCHA in form for preventing spam
	Version:1.0
	Author: Rahul Aryan
	Author URI: http://open-wp.com
	Addon URI: http://open-wp.com/anspress
*/


class AP_ReCaptcha_Addon
{
    /**
     * Instance of this class.
     */
    protected static $instance = null;
    /**
     * Return an instance of this class.
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {
        
        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    public function __construct()
    {
		add_action('ap_enqueue', array($this, 'addon_style_script'));
		add_filter( 'ap_question_form_validation', array($this, 'ap_recaptcha_validation') );
		add_filter( 'ap_answer_form_validation', array($this, 'ap_recaptcha_validation') );
		add_filter( 'ap_ask_form_bottom', array($this, 'ap_ask_form_bottom') );
		add_filter( 'ap_answer_form_bottom', array($this, 'ap_ask_form_bottom') );
    }
	
	
	public function addon_style_script(){
		?>
			<script type="text/javascript">
				var recaptch_public = '<?php echo ap_opt('recaptcha_public_key'); ?>';
			</script>
		<?php
		wp_enqueue_script( 'ap-recaptcha', ANSPRESS_ADDON_URL. 'reCaptcha/recaptcha.js', 'jquery', AP_VERSION);
		wp_enqueue_style( 'ap-recaptcha-css', ANSPRESS_ADDON_URL. 'reCaptcha/responsive_recaptcha.css', array(), AP_VERSION);
	}
	public function ap_recaptcha_validation($error){
		require_once('recaptchalib.php');
		$publickey = ap_opt('recaptcha_public_key');
		$privatekey = ap_opt('recaptcha_private_key');
		# was there a reCAPTCHA response?
		if(!is_super_admin() && (ap_opt('captcha_ask') || ap_opt('captcha_answer')))
		if ((isset($_POST["recaptcha_response_field"]) && $_POST["recaptcha_response_field"]) || empty($_POST["recaptcha_response_field"])) {
			$resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);

			if (!$resp->is_valid) {
				$error['recaptcha_response_field'] 	= __('reCAPTCHA is not valid, please try again.', 'ap');
				$error['has_error'] 	= true;
			}
		}
		
		return $error;
	}
	public function ap_ask_form_bottom(){
		if(!is_super_admin() && ap_opt('captcha_answer') && (ap_current_page_is() == 'question')): ?>
		<?php if (ap_opt('enable_captcha_skip') &&  (ap_get_points() < ap_opt('captcha_skip_rpoints')) ): ?>
			<div class="form-group">
				<div id="recaptcha"><?php ap_recaptch_html(); ?></div>
			</div>
		<?php elseif (!ap_opt('enable_captcha_skip')):?>
			<div class="form-group">
				<div id="recaptcha"><?php ap_recaptch_html(); ?></div>
			</div>
		<?php endif;?>
		<?php elseif(!is_super_admin() && ap_opt('captcha_ask') && (ap_current_page_is() == 'ask')): ?>
		<?php if (ap_opt('enable_captcha_skip') &&  (ap_get_points() < ap_opt('captcha_skip_rpoints')) ): ?>
			<div class="form-group">
				<div id="recaptcha"><?php ap_recaptch_html(); ?></div>
			</div>
		<?php elseif (!ap_opt('enable_captcha_skip')):?>
			<div class="form-group">
				<div id="recaptcha"><?php ap_recaptch_html(); ?></div>
			</div>
		<?php endif;?>
		<?php endif; 
	}
}
function ap_recaptch_html(){
	require_once('recaptchalib.php');

	# the response from reCAPTCHA
	$resp = null;
	# the error code from reCAPTCHA, if any
	$error = null;

	if(!is_super_admin() && (ap_opt('captcha_ask') || ap_opt('captcha_answer')))
		echo recaptcha_get_html(ap_opt('recaptcha_public_key'), $error, is_ssl());
}

AP_ReCaptcha_Addon::get_instance();
