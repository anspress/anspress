<?php
/**
 * This file holds all custom wp cli commands of AnsPress.
 *
 * @since 4.0.5
 * @package AnsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implements custom wp-cli commands.
 */
class AnsPress_Cli extends WP_CLI_Command {

	/**
	 * Prints current version of AnsPress.
	 *
	 * ## EXAMPLES
	 *
	 *     wp anspress version
	 *
	 * @when after_wp_load
	 */
	public function version() {
		WP_CLI::success( 'Currently installed version of AnsPress is ' . AP_VERSION );
	}

	/**
	 * Upgrade AnsPress 3.x data to 4.x.
	 *
	 * Warning! This will delete/edit/rename lots of mysql rows and
	 * tables, so make sure to do a full backup before running this command.
	 *
	 * ## OPTIONS
	 *
	 * [--yes]
	 * : Passing this will not ask for backup confirm.
	 *
	 * ## EXAMPLES
	 *
	 *     wp anspress upgrade --yes
	 *
	 * @when after_wp_load
	 *
	 * @param array $args       Arguments.
	 * @param mixed $assoc_args Options.
	 */
	public function upgrade( $args, $assoc_args ) {
		print( "=== Starting upgrade process ===\n\r" );

		// Confirm before proceeding.
		WP_CLI::confirm( 'Make sure you had backed up your website before starting upgrade process. Do you wish to proceed further?', $assoc_args );

		AnsPress_Upgrader::get_instance();

		print( "\n\r=== Upgrade process completed ===\n\r" );
	}

	/**
	 * Activate an addon.
	 *
	 * ## OPTIONS
	 *
	 * <addon>
	 * : Addon file name to activate.
	 *
	 * ## EXAMPLES
	 *
	 *     wp anspress activate_addon free/avatar.php
	 *
	 * @when after_wp_load
	 * @param array $args       Arguments.
	 * @param mixed $assoc_args Options.
	 */
	public function activate_addon( $args, $assoc_args ) {
		_deprecated_function( __METHOD__, '5.0.0', 'ap_activate_addon' );

		return \WP_CLI::error( __( 'This command is deprecated since 5.0.0', 'anspress-question-answer' ) );
	}
}
