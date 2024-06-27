<?php
/**
 * Answer schema.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Answer;

use AnsPress\Classes\AbstractSchema;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Answer schema class.
 */
class AnswerSchema extends AbstractSchema {
	/**
	 * Get the schema's table name.
	 *
	 * @return string
	 */
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix . 'posts';
	}

	/**
	 * Get the schema's primary key.
	 *
	 * @return string
	 */
	public function getPrimaryKey(): string {
		return 'ID';
	}

	/**
	 * Get the schema's columns.
	 *
	 * @return array<string, string>
	 */
	public function getColumns(): array {
		return array(
			'ID'                    => '%d',
			'post_author'           => '%d',
			'post_date'             => '%s',
			'post_date_gmt'         => '%s',
			'post_content'          => '%s',
			'post_title'            => '%s',
			'post_excerpt'          => '%s',
			'post_status'           => '%s',
			'comment_status'        => '%s',
			'ping_status'           => '%s',
			'post_password'         => '%s',
			'post_name'             => '%s',
			'to_ping'               => '%s',
			'pinged'                => '%s',
			'post_modified'         => '%s',
			'post_modified_gmt'     => '%s',
			'post_content_filtered' => '%s',
			'post_parent'           => '%d',
			'guid'                  => '%s',
			'menu_order'            => '%d',
			'post_type'             => '%s',
			'post_mime_type'        => '%s',
			'comment_count'         => '%d',
		);
	}
}
