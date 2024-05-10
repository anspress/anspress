<?php
/**
 * Abstract data model.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Classes;

use AnsPress\Interfaces\ModelInterface;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract data model.
 *
 * @package AnsPress
 */
abstract class AbstractModel implements ModelInterface {
	/**
	 * Primary column key.
	 *
	 * @var string
	 */
	protected string $primaryKey = 'id';

	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected string $table;

	/**
	 * Columns and format.
	 *
	 * @var array<string, string>
	 */
	protected array $columnsAndFormat = array();

	/**
	 * Fillable fields.
	 *
	 * @var array<string, string> An associative array of settings.
	 */
	protected array $fillable = array();

	/**
	 * Attributes.
	 *
	 * @var array
	 */
	protected array $attributes = array();

	/**
	 * Constructor.
	 *
	 * @return void
	 * @throws \Exception If table name is not set.
	 */
	public function __construct() {
		// Check if table is set.
		if ( ! isset( $this->table ) ) {
			throw new \Exception( esc_attr( 'Table name is not set.' ) );
		}

		// Check if columns and format is set.
		if ( empty( $this->columnsAndFormat ) ) {
			throw new \Exception( esc_attr( 'Columns and format is not set.' ) );
		}
	}

	/**
	 * Get primary key.
	 *
	 * @return string
	 */
	public function getPrimaryKey(): string {
		return $this->primaryKey;
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public function getTableName(): string {
		global $wpdb;

		return $wpdb->prefix . $this->table;
	}

	/**
	 * Get wpdb format for values.
	 *
	 * @param array $data An array of fillable fields.
	 * @return array An array of fillable fields formats.
	 * @throws \Exception If fillable invlaid format passed.
	 */
	public function getFormatsForValues( array $data ): array {
		if ( empty( $data ) ) {
			return array();
		}

		$formats = array();

		// Remove any fields that are not in the columnsAndFormat array.
		$allowedData = array_intersect( $data, array_keys( $this->columnsAndFormat ) );

		// Check if $allowedData count is same as $data count.
		if ( count( $allowedData ) !== count( $data ) ) {
			throw new \Exception(
				sprintf(
					'Invalid data. Allowed fields are: %s',
					esc_attr(
						implode( ', ', array_keys( $this->columnsAndFormat ) )
					)
				)
			);
		}

		foreach ( $data as $field ) {
			$formats[] = $this->columnsAndFormat[ $field ];
		}

		return $formats;
	}

	/**
	 * Update model.
	 *
	 * @return mixed
	 */
	public function update() {
		global $wpdb;

		$data  = array_intersect_key( (array) $this, array_flip( $this->fillable ) );
		$where = array( $this->primaryKey => $this->{$this->primaryKey} );

		/**
		 * Filter to modify data before saving.
		 *
		 * @param array $data Data to be saved.
		 * @since 5.0.0
		 */
		$data = apply_filters( 'anspress/model/pre_update/' . $this->getTableName(), $data );

		$updated = $wpdb->update( // @codingStandardsIgnoreLine
			$this->getTableName(),
			$data,
			$where,
			$this->getFormatsForValues( $data ),
			$this->getFormatsForValues( $where )
		);

		if ( false === $updated ) {
			/**
			 * Action to run after model update failed.
			 *
			 * @param string $table Table name.
			 * @param array $data Data to be saved.
			 * @since 5.0.0
			 */
			do_action( 'anspress/model/update_failed/' . $this->getTableName(), $data );

			return false;
		}

		/**
		 * Action to run after model update.
		 *
		 * @param string $table Table name.
		 * @param array $data Data to be saved.
		 * @since 5.0.0
		 */
		do_action( 'anspress/model/updated/' . $this->getTableName(), $data );

		return $updated;
	}

	/**
	 * Create model.
	 *
	 * @return false|int
	 */
	public function create() {
		global $wpdb;

		$data = array_intersect_key( $this->attributes, array_flip( $this->fillable ) );

		/**
		 * Filter to modify data before saving.
		 *
		 * @param array $data Data to be saved.
		 * @since 5.0.0
		 */
		$data = apply_filters( 'anspress/model/pre_create/' . $this->getTableName(), $data );

		$created = $wpdb->insert( // @codingStandardsIgnoreLine
			$this->getTableName(),
			$data,
			$this->getFormatsForValues( $data )
		);

		if ( false === $created ) {
			/**
			 * Action to run after model creation failed.
			 *
			 * @param string $table Table name.
			 * @param array $data Data to be saved.
			 * @since 5.0.0
			 */
			do_action( 'anspress/model/create_failed/' . $this->getTableName(), $data );

			return false;
		}

		/**
		 * Action to run after model creation.
		 *
		 * @param string $table Table name.
		 * @param array $data Data to be saved.
		 * @since 5.0.0
		 */
		do_action( 'anspress/model/created/' . $this->getTableName(), $data );

		return $created;
	}

	/**
	 * Delete model.
	 *
	 * @return array
	 */
	public function toArray(): array {
		return (array) $this->attributes;
	}
}
