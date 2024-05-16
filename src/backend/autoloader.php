<?php
/**
 * Autoloader for AnsPress.
 *
 * @since 5.0.0
 * @package AnsPress
 */

// exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	/**
	 * Callback function for auto loading class on demand.
	 *
	 * @param string $class_name Name of class.
	 * @since 5.0.0
	 * @throws \Exception If class not found in file.
	 */
	function ( $className ): void {
		if ( false === strpos( $className, 'AnsPress\\' ) ) {
			return;
		}

		$classNameWithoutBase = str_replace( 'AnsPress\\', '', $className );
		$filename             = __DIR__ . DIRECTORY_SEPARATOR . str_replace( '\\', '/', $classNameWithoutBase ) . '.php';

		// Check if file exists before including.
		if ( file_exists( $filename ) ) {
			require_once $filename;
		} else {
			throw new \Exception( "Class $className not found in $filename." ); // @codingStandardsIgnoreLine
		}
	}
);
