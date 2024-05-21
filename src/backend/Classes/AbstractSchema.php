<?php
/**
 * Abstract schema class.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Classes;

use AnsPress\Exceptions\InvalidColumnException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class AbstractSchema
 *
 * @package AnsPress\Classes
 */
abstract class AbstractSchema {
	/**
	 * Get the schema's table name.
	 *
	 * @return string
	 */
	abstract public function getTableName(): string;

	/**
	 * Get the schema's primary key.
	 *
	 * @return string
	 */
	abstract public function getPrimaryKey(): string;

	/**
	 * Get the schema's columns.
	 *
	 * @return array<string, string>
	 */
	abstract public function getColumns(): array;


	/**
	 * Get the format string for preparing a SQL query.
	 *
	 * @param string $column The column name.
	 * @return string The format string.
	 * @throws InvalidColumnException If the column does not exist.
	 */
	public function getFormatString( string $column ): string {
		$validFormats = array( '%s', '%d', '%f' );

		$columns = $this->getColumns();

		if ( isset( $columns[ $column ] ) && in_array( $columns[ $column ], $validFormats, true ) ) {
			return $columns[ $column ];
		}

		throw new InvalidColumnException( esc_attr( "Invalid column: $column" ) );
	}

	/**
	 * Get formats for all passed columns in order.
	 *
	 * @param array $columns The columns to get formats for.
	 * @return string[] The format strings.
	 */
	public function getFormatStrings( array $columns ): array {
		return array_map( array( $this, 'getFormatString' ), $columns );
	}

	/**
	 * Check if a column is valid.
	 *
	 * @param string $column The column name.
	 * @return bool
	 */
	public function isValidColumn( string $column ): bool {
		return isset( $this->getColumns()[ $column ] );
	}

	/**
	 * Get foramt of a column by name.
	 *
	 * @param string $column The column name.
	 * @return string|null The column format.
	 */
	public function getColumnFormat( string $column ): string {
		return $this->getColumns()[ $column ] ?? null;
	}
}
