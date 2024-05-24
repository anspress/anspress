<?php
/**
 * Array class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Array class.
 *
 * @since 5.0.0
 */
class Arr {
	/**
	 * Convert a dot notation array to a nested array.
	 *
	 * @param array $dotNotationArray Dot notation array.
	 * @return array
	 */
	public static function fromDotNotation( array $dotNotationArray ) {
		$result       = array();
		$indexTracker = array(); // To track the indices for empty keys at each level.

		foreach ( $dotNotationArray as $key => $value ) {
			$keys    = explode( '.', $key );
			$current = &$result;
			$path    = ''; // To track the current path for index tracking.

			foreach ( $keys as $index => $keyPart ) {
				// Handle empty key parts as numeric indices.
				if ( '' === $keyPart || '*' === $keyPart ) {
					if ( ! isset( $indexTracker[ $path ] ) ) {
						$indexTracker[ $path ] = 0;
					}
					$keyPart = $indexTracker[ $path ]++;
				}

				// Check if $keyPart is numeric.
				if ( is_numeric( $keyPart ) ) {
					$keyPart = (int) $keyPart; // Convert to integer for numeric keys.
				}

				// If this is the last key part, assign the value.
				if ( count( $keys ) - 1 === $index ) {
					$current[ $keyPart ] = $value;
				} else {
					// If the key part doesn't exist or isn't an array, initialize it as an array.
					if ( ! isset( $current[ $keyPart ] ) || ! is_array( $current[ $keyPart ] ) ) {
						$current[ $keyPart ] = array();
					}

					// Move the reference deeper into the array.
					$current = &$current[ $keyPart ];
				}

				// Update the path for the next level.
				$path .= $keyPart . '.';
			}
		}

		return $result;
	}


	/**
	 * Convert a nested array to a dot notation array.
	 *
	 * @param array  $nestedArray Nested array.
	 * @param string $parentKey Parent key.
	 * @param array  $result Result array.
	 * @return array
	 */
	public static function toDotNotation( array $nestedArray, $parentKey = '', &$result = array() ) {
		foreach ( $nestedArray as $key => $value ) {
			// Create a new key for the dot notation format.
			$dotKey = '' === $parentKey ? $key : $parentKey . '.' . $key;

			if ( is_array( $value ) && ! empty( $value ) ) {
				// If the value is a non-empty array, recursively process it.
				self::toDotNotation( $value, $dotKey, $result );
			} else {
				// Otherwise, add the value to the result array.
				$result[ $dotKey ] = $value;
			}
		}

		return $result;
	}
}
