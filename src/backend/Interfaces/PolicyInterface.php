<?php
/**
 * Policy interface.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Interfaces;

use AnsPress\Classes\AbstractModel;
use WP_User;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interface PolicyInterface
 *
 * This interface defines the methods required for policy classes to handle
 * authorization logic for different actions (view, create, update, delete) on models.
 *
 * @package AnsPress
 */
interface PolicyInterface {
	/**
	 * Get the policy name.
	 *
	 * @return string
	 */
	public static function getPolicyName(): string;
}
