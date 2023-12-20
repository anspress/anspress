<?php
/**
 * PHPUnit tests for testing the Ajax calls.
 */

namespace AnsPress\WPTestUtils\WPIntegration;

use Yoast\WPTestUtils\WPIntegration\TestCase;

abstract class TestCaseAjax extends TestCase {

	protected $_last_response = '';

	protected static $actions = array(
		'suggest_similar_questions',
		'load_tinymce',
		'load_comments',
		'edit_comment_form',
		'edit_comment',
		'approve_comment',
		'vote',
		'delete_comment',
		'post_actions',
		'action_toggle_featured',
		'action_close',
		'action_toggle_delete_post',
		'action_delete_permanently',
		'action_status',
		'action_convert_to_post',
		'action_flag',
		'delete_attachment',
		'load_filter_order_by',
		'subscribe',
		'comment_modal',
		'nopriv_comment_modal',
		'ap_toggle_best_answer',
		'ap_repeatable_field',
		'nopriv_ap_repeatable_field',
		'ap_form_question',
		'nopriv_ap_form_question',
		'ap_form_answer',
		'nopriv_ap_form_answer',
		'ap_form_comment',
		'nopriv_ap_form_comment',
		'ap_search_tags',
		'nopriv_ap_search_tags',
		'ap_image_upload',
		'ap_upload_modal',
		'nopriv_ap_upload_modal',
	);

	public function set_up() {
		parent::set_up();

		// Require the Ajax related files.
		require_once ANSPRESS_DIR . 'includes/ajax-hooks.php';

		foreach ( self::$actions as $action ) {
			if ( function_exists( 'wp_ajax_' . $action ) ) {
				add_action( 'wp_ajax_' . $action, $action, 1 );
			}
			if ( function_exists( 'ap_ajax_' . $action ) ) {
				add_action( 'ap_ajax_' . $action, $action, 1 );
			}

			add_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
			if ( ! defined( 'DOING_AJAX' ) ) {
				define( 'DOING_AJAX', true );
			}
			set_current_screen( 'ajax' );
			add_action( 'clear_auth_cookie', array( $this, 'logout' ) );
		}
	}

	public function tear_down() {
		parent::tear_down();
		$_POST = array();
		remove_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
		remove_action( 'clear_auth_cookie', array( $this, 'logout' ) );
		set_current_screen( 'front' );
	}

	public function logout() {
		unset( $GLOBALS['current_user'] );
		$cookies = array( AUTH_COOKIE, SECURE_AUTH_COOKIE, LOGGED_IN_COOKIE, USER_COOKIE, PASS_COOKIE );
		foreach ( $cookies as $c ) {
			unset( $_COOKIE[ $c ] );
		}
	}

	public function getDieHandler() {
		return array( $this, 'dieHandler' );
	}

	public function dieHandler( $message ) {
		$this->_last_response .= ob_get_clean();
		ob_end_clean();
		if ( '' === $this->_last_response ) {
			if ( is_scalar( $message ) ) {
				throw new \Exception( (string) $message );
			} else {
				throw new \Exception( '0' );
			}
		} else {
			throw new \Exception( $message );
		}
	}

	protected function _setRole( $role ) {
		$post    = $_POST;
		$user_id = self::factory()->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );
		$_POST = array_merge( $_POST, $post );
	}

	protected function _handleAjax( $action ) {
		ini_set( 'implicit_flush', false );
		ob_start();

		$_POST['action'] = $action;
		$_REQUEST = $_POST;

		if ( function_exists( 'wp_ajax_' . $_REQUEST['action'] ) ) {
			do_action( 'wp_ajax_' . $_REQUEST['action'], null );
		}
		if ( function_exists( 'ap_ajax_' . $_REQUEST['action'] ) ) {
			do_action( 'ap_ajax_' . $_REQUEST['action'], null );
		}

		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) ) {
			$this->_last_response = $buffer;
		}
	}
}
