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

class AnsPress_Process_Form {
	private $fields;

	private $result;

	private $request;

	private $redirect;

	private $is_ajax = false;
	/**
	 * Initialize the class
	 */
	public function __construct() {
		add_action( 'wp_ajax_ap_ajax', array( $this, 'ap_ajax' ) );
		add_action( 'wp_ajax_nopriv_ap_ajax', array( $this, 'ap_ajax' ) );
	}

	/**
	 * for non ajax form
	 *
	 * @return void
	 */
	public function non_ajax_form() {
		// return if ap_form_action is not set, probably its not our form
		if ( ! isset( $_REQUEST['ap_form_action'] ) || isset( $_REQUEST['ap_ajax_action'] ) ) {
			return;
		}

		$this->request = $_REQUEST;
		$this->process_form();

		if ( ! empty( $this->redirect ) ) {
			wp_redirect( $this->redirect );
			wp_die();
		}
	}

	/**
	 * Handle all anspress ajax requests.
	 *
	 * @return void
	 * @since 2.0.1
	 */
	public function ap_ajax() {
		if ( ! isset( $_REQUEST['ap_ajax_action'] ) ) {
			wp_die();
		}

		$this->request = $_REQUEST;

		if ( isset( $_POST['ap_form_action'] ) ) {
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
		$action = sanitize_text_field( $_POST['ap_form_action'] );

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
