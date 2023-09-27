<?php
/**
 * AnsPress process form.
 *
 * @link     https://anspress.net
 * @since    2.0.1
 * @license  GPL 3+
 * @package  AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Process AnsPress forms.
 *
 * @since unknown
 * @since 4.2.0 Fixed: CS bugs.
 */
class AnsPress_Process_Form {
	/**
	 * Results to send in ajax callback.
	 *
	 * @var array
	 */
	private $result;

	/**
	 * Link to redirect.
	 *
	 * @var string
	 */
	private $redirect;

	/**
	 * Used for property assignment.
	 *
	 * @var object
	 */
	public $request;

	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action( 'wp_ajax_ap_ajax', array( $this, 'ap_ajax' ) );
		add_action( 'wp_ajax_nopriv_ap_ajax', array( $this, 'ap_ajax' ) );
	}

	/**
	 * For non ajax form.
	 *
	 * @return void
	 */
	public function non_ajax_form() {
		$form_action = ap_isset_post_value( 'ap_form_action' );
		$ajax_action = ap_isset_post_value( 'ap_ajax_action' );

		// return if ap_form_action is not set, probably its not our form.
		if ( ! $form_action || $ajax_action ) {
			return;
		}

		$this->request = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->process_form();

		if ( ! empty( $this->redirect ) ) {
			wp_redirect( $this->redirect ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
			exit;
		}
	}

	/**
	 * Handle all anspress ajax requests.
	 *
	 * @return void
	 * @since 2.0.1
	 */
	public function ap_ajax() {
		$ajax_action = ap_isset_post_value( 'ap_ajax_action' );
		$form_action = ap_isset_post_value( 'ap_form_action' );

		if ( ! $ajax_action ) {
			exit;
		}

		$this->request = $_REQUEST; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $form_action ) {
			$this->is_ajax = true;
			$this->process_form();
			ap_ajax_json( $this->result );
		} else {
			$action = ap_sanitize_unslash( 'ap_ajax_action', 'r' );

			/**
				* ACTION: ap_ajax_[$action]
				* Action for processing Ajax requests
			 *
				* @since 2.0.1
				*/
			do_action( 'ap_ajax_' . $action );
		}

		// If reached to this point then there is something wrong.
		ap_ajax_json( 'something_wrong' );
	}


	/**
	 * Process form based on action value.
	 *
	 * @return void
	 * @since 2.0.1
	 * @deprecated 4.1.5
	 */
	public function process_form() {
		$form_action = ap_isset_post_value( 'ap_form_action' );
		$action      = sanitize_text_field( $form_action );

		/**
		 * ACTION: ap_process_form_[action]
		 * process form
		 *
		 * @since 2.0.1
		 * @deprecated 4.1.0
		 */
		do_action( 'ap_process_form_' . $action );
	}
}
