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
	function ( $class_name ): void {
		if ( false === strpos( $class_name, 'AnsPress\\' ) ) {
			return;
		}

		$class_name = str_replace( 'AnsPress\\', '', $class_name );
		$filename   = wp_normalize_path( __DIR__ . '/' . str_replace( '\\', '/', $class_name ) . '.php' );

		// Check if file exists before including.
		if ( file_exists( $filename ) ) {
			require_once $filename;
		} else {
			throw new \Exception(
				wp_sprintf(
				// translators: 1: Class name, 2: File name.
					esc_html__( 'Class %1$s not found', 'anspress-question-answer' ),
					esc_html( $class_name )
				)
			);
		}
	}
);
