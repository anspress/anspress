<?php
/**
 * AnsPress ajax abstract class.
 *
 * @author     Rahul Aryan <support@rahularyan.com>
 * @copyright  2014 anspress.net & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.net
 * @package    AnsPress
 * @since      4.1.8
 */

namespace AnsPress\Classes;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * A class to be used as a base for all ajax classes.
 *
 * @since 4.1.8
 */
abstract class Ajax extends \AnsPress\Singleton {
	/**
	 * The response array.
	 *
	 * @var array
	 */
	private $res = [];

	/**
	 * The ajax action.
	 *
	 * @var string
	 */
	public $action = '';

	/**
	 * Ajax status.
	 *
	 * @var boolean
	 */
	public $success = false;

	/**
	 * The default nonce key.
	 *
	 * @var string
	 */
	public $nonce_key = 'ap_default';

	/**
	 * All request values.
	 *
	 * @var array
	 */
	public $req;

	/**
	 * Class constructor.
	 */
	protected function __construct() {
		$this->set_action();
		$this->verify_nonce();
		$this->verify_permission();

		if ( is_user_logged_in() ) {
			$this->logged_in();
		} else {
			$this->nopriv();
		}

		$this->send();
	}

	/**
	 * Set ajax action.
	 */
	protected function set_action() {
		$this->action = 'ap_' . strtolower( ( new \ReflectionClass( $this ) )->getShortName() );
	}

	/**
	 * Method for logged in users.
	 *
	 * @return void
	 */
	public function logged_in() {

	}

	/**
	 * Method for non logged in users.
	 *
	 * @return void
	 */
	public function nopriv() {

	}

	/**
	 * Add response key => value pair.
	 *
	 * @param string $key Key.
	 * @param mixed  $val Value.
	 * @return void
	 */
	public function add_res( $key, $val ) {
		$this->res[ $key ] = $val;
	}

	/**
	 * Verify nonce and die if failed.
	 *
	 * Nonce key is stored in `$nonce_key` property. To bypass verification
	 * simply set `$nonce_key` to an empty value.
	 *
	 * @return void
	 */
	private function verify_nonce() {
		if ( empty( $this->nonce_key ) ) {
			return;
		}

		$nonce = ap_sanitize_unslash( '__nonce', 'r' );

		// Verify nonce.
		if ( ! wp_verify_nonce( $nonce, $this->nonce_key ) ) {
			$this->snackbar( __( 'Trying to cheat?!', 'anspress-question-answer' ) );
			$this->send();
		}
	}

	/**
	 * Verify permission of a user.
	 *
	 * By default this method need to override from child class.
	 * If not overridden then ajax will always fail at this point.
	 *
	 * @return void
	 */
	protected function verify_permission() {
		$this->set_fail();
		$this->snackbar( __( 'You don\'t have enough permissions to do this action.', 'anspress-question-answer' ) );
		$this->send();
	}

	/**
	 * Set ajax as success.
	 *
	 * @return void
	 */
	public function set_success() {
		$this->success = true;
	}

	/**
	 * Set ajax as fail.
	 *
	 * @return void
	 */
	public function set_fail() {
		$this->success = false;
	}

	/**
	 * Add snackbar.
	 *
	 * @param string $msg Snackbar message.
	 * @return void
	 */
	public function snackbar( $msg ) {
		$this->res['snackbar'] = array(
			'message' => $msg,
		);
	}

	/**
	 * Get and set request.
	 *
	 * @param string $key Request key.
	 * @param mixed  $val Request value.
	 * @return mixed
	 */
	public function req( $key, $val = null ) {
		if ( null === $val && ! isset( $this->req[ $key ] ) ) {
			return;
		}

		if ( null === $val ) {
			return $this->req[ $key ];
		}

		$this->req[ $key ] = $val;
	}

	/**
	 * Send ajax response.
	 *
	 * @return void
	 */
	public function send() {
		$this->add_res( 'success', $this->success );
		$this->add_res( 'action', $this->action );

		// Add snackbar message by default if not success.
		if ( false === $this->success && empty( $this->res['snackbar'] ) ) {
			$this->snackbar( __( 'Something went wrong.', 'anspress-question-answer' ) );
		}

		ap_send_json( $this->res );
	}
}
