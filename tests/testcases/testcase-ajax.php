<?php
/**
 * PHPUnit tests for testing the Ajax calls.
 */

namespace AnsPress\WPTestUtils\WPIntegration;

use Yoast\WPTestUtils\WPIntegration\TestCase;

abstract class TestCaseAjax extends TestCase {

	protected $_last_response = '';
	protected $_error_level = 0;

	public function set_up() {
		parent::set_up();

		// Require the Ajax related files.
		require_once ANSPRESS_DIR . 'admin/ajax.php';
		require_once ANSPRESS_DIR . 'includes/ajax-hooks.php';

		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}
		set_current_screen( 'ajax' );
		add_action( 'clear_auth_cookie', array( $this, 'logout' ) );
		$this->_error_level = error_reporting();
		error_reporting( $this->_error_level & ~E_WARNING );
	}

	public function tear_down() {
		$_POST = array();
		remove_filter( 'wp_die_ajax_handler', array( $this, 'getDieHandler' ), 1, 1 );
		remove_action( 'clear_auth_cookie', array( $this, 'logout' ) );
		error_reporting( $this->_error_level );
		set_current_screen( 'front' );
		parent::tear_down();
	}

	public function getDieHandler() {
		return array( $this, 'dieHandler' );
	}

	public function dieHandler( $message ) {
		$this->_last_response .= ob_get_clean();
		if ( '' === $this->_last_response ) {
			if ( is_scalar( $message ) ) {
				throw new \WPAjaxDieStopException( (string) $message );
			} else {
				throw new \WPAjaxDieStopException( '0' );
			}
		} else {
			throw new \WPAjaxDieContinueException( $message );
		}
	}

	protected function _handleAjax( $action ) {
		ini_set( 'implicit_flush', false );
		ob_start();

		$_POST['action'] = $action;
		$_REQUEST = $_POST;

		do_action( 'wp_ajax_' . $_REQUEST['action'] );

		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) ) {
			$this->_last_response = $buffer;
		}
	}
}
