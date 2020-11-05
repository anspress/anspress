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

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php';
}

/**
 * Return hover card attributes.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @return string
 *
 * @deprecated 4.1.13
 */
function ap_get_hover_card_attr( $_post = null ) {
	_deprecated_function( __FUNCTION__, '4.1.13' );
}

/**
 * Echo hover card attributes.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @deprecated 4.1.13
 */
function ap_hover_card_attr( $_post = null ) {
	_deprecated_function( __FUNCTION__, '4.1.13' );
}
