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

/**
 * Callback function for auto loading class on demand.
 *
 * @param string $class Name of class.
 * @return boolean True if files is included.
 * @since 4.1.8
 */
function autoloader( $class ) {
	if ( false === strpos( $class, 'AnsPress\\' ) ) {
		return;
	}

	// Replace AnsPress\Pro\ and change to lowercase to fix WPCS warning.
	$class    = strtolower( str_replace( 'AnsPress\\', '', $class ) );
	$filename = ANSPRESS_DIR . str_replace( '_', '-', str_replace( '\\', '/', $class ) ) . '.php';

	// Check if file exists before including.
	if ( file_exists( $filename ) ) {
		require_once $filename;

		// Check class exists.
		if ( class_exists( $class ) ) {
			return true;
		}
	}

	return false;
}

spl_autoload_register( __NAMESPACE__ . '\\autoloader' );