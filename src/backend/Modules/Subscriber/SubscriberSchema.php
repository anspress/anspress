<?php
/**
 * Subscriber schema.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Subscriber;

use AnsPress\Classes\AbstractSchema;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SubscriberSchema
 *
 * @package AnsPress\Modules\Subscriber
 */
class SubscriberSchema extends AbstractSchema {
	/**
	 * Get the schema's table name.
	 *
	 * @return string
	 */
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix . 'ap_subscribers';
	}

	/**
	 * Get the schema's primary key.
	 *
	 * @return string
	 */
	public function getPrimaryKey(): string {
		return 'subs_id';
	}

	/**
	 * Get the schema's columns.
	 *
	 * @return array<string, string>
	 */
	public function getColumns(): array {
		return array(
			'subs_id'      => '%d',
			'subs_user_id' => '%d',
			'subs_ref_id'  => '%d',
			'subs_event'   => '%s',
		);
	}
}
