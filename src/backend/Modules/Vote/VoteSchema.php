<?php
/**
 * Vote schema.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Vote;

use AnsPress\Classes\AbstractSchema;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class VoteSchema
 *
 * @package AnsPress\Modules\Vote
 */
class VoteSchema extends AbstractSchema {
	/**
	 * Get the schema's table name.
	 *
	 * @return string
	 */
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix . 'ap_votes';
	}

	/**
	 * Get the schema's primary key.
	 *
	 * @return string
	 */
	public function getPrimaryKey(): string {
		return 'vote_id';
	}

	/**
	 * Get the schema's columns.
	 *
	 * @return array<string, string>
	 */
	public function getColumns(): array {
		return array(
			'vote_id'       => '%d',
			'vote_post_id'  => '%d',
			'vote_user_id'  => '%d',
			'vote_rec_user' => '%d',
			'vote_type'     => '%s',
			'vote_value'    => '%d',
			'vote_date'     => '%s',
		);
	}
}
