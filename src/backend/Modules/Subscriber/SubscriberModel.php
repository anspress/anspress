<?php
/**
 * Subscriber model.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Subscriber;

use AnsPress\Classes\AbstractModel;
use AnsPress\Classes\AbstractSchema;
use AnsPress\Classes\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscriber model.
 *
 * @package AnsPress
 */
class SubscriberModel extends AbstractModel {
	/**
	 * Create the model's schema.
	 *
	 * @return AbstractSchema
	 */
	protected static function createSchema(): AbstractSchema {
		return Plugin::get( SubscriberSchema::class );
	}
}
