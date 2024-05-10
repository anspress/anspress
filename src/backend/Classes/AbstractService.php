<?php
/**
 * Base class for services.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Interfaces\ModuleInterface;
use AnsPress\Interfaces\SingletonInterface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for services.
 *
 * @since 5.0.0
 */
abstract class AbstractService implements SingletonInterface {
	use TraitSingleton;
}
