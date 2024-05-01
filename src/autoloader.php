<?php
/**
 * AnsPress class auto loader.
 *
 * @link         https://anspress.net/anspress
 * @since        5.0.0
 * @package      AnsPress\Core
 */

namespace AnsPress\Core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Callback function for auto loading class on demand.
 *
 * @param string $class_name Name of class.
 * @since 5.0.0
 * @throws \Exception If class not found in file.
 */
function autoloader( $class_name ): void {
	if ( false === strpos( $class_name, 'AnsPress\\Core\\' ) ) {
		return;
	}

	$class_name = str_replace( 'AnsPress\\Core\\', '', $class_name );
	$filename   = __DIR__ . DIRECTORY_SEPARATOR . str_replace( '\\', '/', $class_name ) . '.php';

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

spl_autoload_register( 'AnsPress\\Core\\autoloader' );
