<?php
/**
 * AnsPress class auto loader.
 *
 * @link         https://anspress.net/anspress
 * @since        1.0.0
 * @author       Rahul Aryan <rah12@live.com>
 * @package      AnsPressPro
 */

namespace AnsPress;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Callback function for auto loading class on demand.
 *
 * @param string $class_name Name of class.
 * @return boolean True if files is included.
 * @since 4.1.8
 */
function autoloader( $class_name ) {
	if ( false === strpos( $class_name, 'AnsPress\\' ) ) {
		return;
	}

	// Replace AnsPress\Pro\ and change to lowercase to fix WPCS warning.
	$class_name = strtolower( str_replace( 'AnsPress\\', '', $class_name ) );
	$filename   = ANSPRESS_DIR . str_replace( '_', '-', str_replace( '\\', '/', $class_name ) ) . '.php';

	// Check if file exists before including.
	if ( file_exists( $filename ) ) {
		require_once $filename;

		// Check class exists.
		if ( class_exists( $class_name ) ) {
			return true;
		}
	}

	return false;
}

spl_autoload_register( __NAMESPACE__ . '\\autoloader' );
