<?php
/**
 * File system class.
 *
 * @package AnsPress\Core
 * @since 5.0.0
 */

namespace AnsPress\Core\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File system class.
 *
 * @since 5.0.0
 */
class FileSystem {
	/**
	 * Get path to AnsPress directory.
	 *
	 * @since 5.0.0
	 */
	public static function getRootDir(): string {
		return plugin_dir_path( ANSPRESS_PLUGIN_ROOT_FILE );
	}

	/**
	 * Get path to a file or dir in AnsPress directory.
	 *
	 * @since 5.0.0
	 * @param string $path Path to file or dir.
	 */
	public static function getPathTo( $path ): string {
		return wp_normalize_path( self::getRootDir() . DIRECTORY_SEPARATOR . $path );
	}

	/**
	 * Get url to a file or dir in AnsPress directory.
	 *
	 * @since 5.0.0
	 * @param string $path Path to file or dir. By default it will return url to AnsPress directory.
	 */
	public static function getUrlTo( $path = '' ): string {
		return plugins_url( $path, self::getRootDir() );
	}
}
