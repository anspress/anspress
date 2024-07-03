<?php
/**
 * Holds class responsible for upgrading 3.x data to 4.x.
 *
 * @package AnsPress
 * @since 4.0.5
 */

/**
 * AnsPress upgrader class.
 *
 * @since 4.0.5
 */
class AnsPress_Upgrader {
	/**
	 * Singleton instance.
	 *
	 * @return AnsPress_Upgrader
	 */
	public static function get_instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new AnsPress_Upgrader();
		}

		return $instance;
	}

	/**
	 * Private ctor so nobody else can instance it
	 */
	private function __construct() {
		$this->check_tables();
	}

	/**
	 * Check if tables are updated, if not create it first.
	 */
	public function check_tables() {
		if ( get_option( 'anspress_db_version' ) !== AP_DB_VERSION ) {
			$activate = AP_Activate::get_instance();
			$activate->insert_tables();
			update_option( 'anspress_db_version', AP_DB_VERSION );
		}
	}
}
