<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php';
}

/**
 * Return hover card attributes.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @return void
 *
 * @deprecated 4.1.13
 */
function ap_get_hover_card_attr( $_post = null ) { // phpcs:ignore
	_deprecated_function( __FUNCTION__, '4.1.13' );
}

/**
 * Echo hover card attributes.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @deprecated 4.1.13
 */
function ap_hover_card_attr( $_post = null ) { // phpcs:ignore
	_deprecated_function( __FUNCTION__, '4.1.13' );
}

/**
 * Return response with type and message.
 *
 * @param string $id           messge id.
 * @param bool   $only_message return message string instead of array.
 * @return string
 *
 * @deprecated 4.4.0
 */
function ap_responce_message( $id, $only_message = false ) {
	_deprecated_function( __FUNCTION__, '4.4.0', 'ap_response_message()' );
	return ap_response_message( $id, $only_message );
}

if ( ! function_exists( 'ap_verify_nonce' ) ) {

	/**
	 * Verify the __nonce field.
	 *
	 * @param string $action Action.
	 * @return bool
	 *
	 * @deprecated 4.4.0
	 */
	function ap_verify_nonce( $action ) {
		_deprecated_function( __FUNCTION__, '4.4.0', 'anspress_verify_nonce()' );
		return anspress_verify_nonce( $action );
	}
}
