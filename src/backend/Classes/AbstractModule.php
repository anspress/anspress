<?php
/**
 * Base class for modules.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Interfaces\ModuleInterface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for services.
 *
 * @since 5.0.0
 */
abstract class AbstractModule implements ModuleInterface {

}
