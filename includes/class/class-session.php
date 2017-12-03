<?php
/**
 * AnsPress session handler for managing custom session for users.
 *
 * @link       https://anspress.io/anspress
 * @since      4.1.5
 * @author     Rahul Aryan <support@anspress.io>
 * @package    AnsPress
 * @subpackage Session
 */

namespace AnsPress;

/**
 * AnsPress session handler for managing custom session
 * for logged in and non logged in users.
 *
 * @package AnsPress
 * @since   4.1.5
 */
class Session {
	/**
	 * Instance.
	 *
	 * @var Instance
	 */
	protected static $instance = null;

	/**
	 * Cookie and session name.
	 *
	 * @var string
	 */
	private $name = 'anspress_session';

	/**
	 * The cookie path.
	 *
	 * @var string
	 */
	private $cookie_path = COOKIEPATH;

	/**
	 * The cookie domain.
	 *
	 * @var string|null
	 */
	private $cookie_domain = null;

	/**
	 * When will cookie and session will expire.
	 *
	 * @var string
	 */
	private $expires = DAY_IN_SECONDS;

	/**
	 * The session ID.
	 *
	 * @var string
	 */
	private $id;

	/**
	 * Get current instance.
	 *
	 * @return AnsPress\Session
	 */
	public static function init() {
		// Create an object.
		null === self::$instance && self::$instance = new self;
		return self::$instance; // Return the object.
	}

	/**
	 * Initialize the class.
	 */
	public function __construct() {
		if ( isset( $_COOKIE[ $this->name ] ) ) {
			$this->id = stripslashes( ap_sanitize_unslash( $_COOKIE[ $this->name ] ) );
		} else {
			$this->id = $this->generate_id();
			$this->set_cookie();
		}
	}

	/**
	 * Set the session cookie.
	 */
	protected function set_cookie()	{
		if ( ! headers_sent() ) {
			@setcookie( $this->name, $this->id, time() + $this->expires, $this->cookie_path, $this->cookie_domain );
		}
	}

	/**
	 * Delete session cookie.
	 *
	 * @return void
	 */
	protected function delete_cookie() {
		if ( ! headers_sent() ) {
			@setcookie( $this->name, '', time() - 42000, $this->cookie_path, $this->cookie_domain );
		}
	}

	/**
	 * Generate a cryptographically strong unique ID for the session token.
	 *
	 * @return string
	 */
	protected function generate_id() {
		if ( ! class_exists( 'PasswordHash' ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
		}

		$hasher = new \PasswordHash( 8, false );

		return md5( $hasher->get_random_bytes( 32 ) );
	}

	/**
	 * Get an offset by from AnsPress session.
	 *
	 * @param string $key Offset key.
	 * @return mixed
	 * @since 4.1.5
	 */
	public function get( $key ) {
		$cache = get_transient( 'anspress_session_' . $this->id );
		if ( false === $cache ) {
			return;
		}

		if ( isset( $cache[ $key ] ) ) {
			return $cache[ $key ];
		}
	}

	/**
	 * Set offset in AnsPress session.
	 *
	 * @param string     $key Offset key.
	 * @param mixed|null $val Offset value. Delete key value pair if this is `null`.
	 * @return void
	 * @since 4.1.5
	 */
	public function set( $key, $val = null ) {
		$cache = get_transient( 'anspress_session_' . $this->id );

		if ( false === $cache ) {
			$cache = [ $key => $val ];
		}

		$cache[ $key ] = $val;

		set_transient( 'anspress_session_' . $this->id, $cache, $this->expires );
	}

	/**
	 * Set a question id in session's questions offset.
	 *
	 * @param integer $id Question id.
	 * @return void
	 * @since 4.1.5
	 */
	public function set_question( $id ) {
		$questions = $this->get( 'questions' );

		if ( ! $questions ) {
			$questions = [];
		}

		$questions[] = $id;

		$this->set( 'questions', $questions );
	}

	/**
	 * Set an answer id in session's answers offset.
	 *
	 * @param integer $id Answer id.
	 * @return void
	 * @since 4.1.5
	 */
	public function set_answer( $id ) {
		$answers = $this->get( 'answers' );

		if ( ! $answers ) {
			$answers[] = $id;
		}

		$this->set( 'answers', $answers );
	}

	/**
	 * Delete all session data or just a key=>value pair.
	 *
	 * @param null|string $key Name of key. On `null` all session data is deleted.
	 * @return void
	 * @since 4.1.5
	 */
	public function delete( $key = null ) {
		// Delete all session data if no key set.
		if ( null === $key ) {
			delete_transient( 'anspress_session_' . $this->id );
			return;
		}

		$this->set( $key );
	}
}
