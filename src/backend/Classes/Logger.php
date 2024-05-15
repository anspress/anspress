<?php
/**
 * Logger class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Interfaces\SingletonInterface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to log messages.
 *
 * @since 5.0.0
 */
class Logger implements SingletonInterface {
	const LOG_LEVEL_ERROR   = 'error';
	const LOG_LEVEL_WARNING = 'warning';
	const LOG_LEVEL_INFO    = 'info';
	const LOG_LEVEL_DEBUG   = 'debug';

	/**
	 * Writes a log message to the WordPress debug log.
	 *
	 * @param string $level   The log level (error, warning, info, debug).
	 * @param string $message The log message.
	 * @param mixed  $data    Optional. Additional data to log.
	 */
	public function log( string $level, string $message, $data = null ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG &&
			defined( 'WP_DEBUG_LOG' ) && ! WP_DEBUG_LOG &&
			self::LOG_LEVEL_DEBUG === $level
			) {
			return;
		}

		$logMessage = 'AnsPress: ' . gmdate( '[Y-m-d H:i:s]' ) . " [$level] $message";

		if ( null !== $data ) {
			$logMessage .= "\n" . self::formatData( $data );
		}

		$logMessage .= "\n";

		error_log( $logMessage ); // @codingStandardsIgnoreLine
	}

	/**
	 * Formats data for logging.
	 *
	 * @param mixed $data The data to format.
	 * @return string The formatted data.
	 */
	public function formatData( $data ): string {
		if ( is_array( $data ) || is_object( $data ) ) {
			return print_r( $data, true ); // @codingStandardsIgnoreLine
		} else {
			return $data;
		}
	}

	/**
	 * Log an error message.
	 *
	 * @param string $message The error message.
	 * @param mixed  $data   Optional. Additional data to log.
	 * @return void
	 */
	public function error( string $message, $data = null ) {
		$this->log( self::LOG_LEVEL_ERROR, $message, $data );
	}

	/**
	 * Log a warning message.
	 *
	 * @param string $message The warning message.
	 * @param mixed  $data Optional. Additional data to log.
	 * @return void
	 */
	public function warning( string $message, $data = null ) {
		$this->log( self::LOG_LEVEL_WARNING, $message, $data );
	}

	/**
	 * Log a info message.
	 *
	 * @param string $message The warning message.
	 * @param mixed  $data Optional. Additional data to log.
	 * @return void
	 */
	public function info( string $message, $data = null ) {
		$this->log( self::LOG_LEVEL_INFO, $message, $data );
	}

	/**
	 * Log a debug message.
	 *
	 * @param string $message The warning message.
	 * @param mixed  $data Optional. Additional data to log.
	 * @return void
	 */
	public function debug( string $message, $data = null ) {
		$this->log( self::LOG_LEVEL_DEBUG, $message, $data );
	}

	/**
	 * Prevent cloning of the instance of the class.
	 *
	 * @return void
	 */
	public function __clone() {
	}

	/**
	 * Prevent unserializing of the instance of the class.
	 *
	 * @return void
	 */
	public function __wakeup() {
	}
}
