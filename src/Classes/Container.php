<?php
/**
 * AnsPress main class.
 *
 * @link         https://anspress.net/anspress
 * @since        5.0.0
 * @package      AnsPress\Core
 */

namespace AnsPress\Core\Classes;

// @codeCoverageIgnoreStart
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// @codeCoverageIgnoreEnd

/**
 * AnsPress container class.
 *
 * @since 5.0.0
 */
class Container {
	/**
	 * Array of services.
	 *
	 * @var \AnsPress\Core\Classes\BaseService[]
	 */
	private $services = array();

	/**
	 * Set service object.
	 *
	 * @param BaseService $serviceObject Service object.
	 * @return void
	 *
	 * @throws \Exception If invalid service.
	 */
	public function set( BaseService $serviceObject ) {
		$this->services[ get_class( $serviceObject ) ] = $serviceObject;
	}

	/**
	 * Method to load services on demand.
	 *
	 * @param class-string $serviceName Service name.
	 * @return T Service object.
	 */
	public function get( string $serviceName ) {
		if ( ! isset( $this->services[ $serviceName ] ) ) {
			$instance = new $serviceName();

			$this->set( $instance );
		}

		return $this->services[ $serviceName ];
	}
}
