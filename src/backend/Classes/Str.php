<?php
/**
 * Str class.
 *
 * @package AnsPress
 * @since 5.0.0
 * @author Rahul Aryan <rahul@zenprojects.com>
 */

namespace AnsPress\Classes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Str
 *
 * @package AnsPress\Classes
 */
class Str {
	/**
	 * Convert string to snake case.
	 *
	 * @param string $value String to convert.
	 * @param string $delimiter Delimiter.
	 * @return string
	 */
	public static function toSnakeCase( $value, $delimiter = '_' ) {
		if ( ! ctype_lower( $value ) ) {
			$value = preg_replace( '/\s+/u', '', ucwords( $value ) );

			$value = mb_strtolower( (string) preg_replace( '/(.)(?=[A-Z])/u', '$1' . $delimiter, $value ), 'UTF-8' );
		}

		return $value;
	}

	/**
	 * Convert string to camel case.
	 *
	 * @param string $value String to convert.
	 * @return string
	 */
	public static function toCamelCase( $value ) {
		return lcfirst( str_replace( ' ', '', ucwords( strtr( $value, '_-', '  ' ) ) ) );
	}
}
