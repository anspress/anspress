<?php
/**
 * Migration class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Interfaces\MigrationInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to manage running migrations.
 *
 * @since 5.0.0
 */
class Migrator {

	/**
	 * Array to store all registered migrations.
	 *
	 * @var array
	 */
	protected $migrations = array();

	/**
	 * Directory where migration files are located.
	 *
	 * @var string
	 */
	protected $migrationDir;

	/**
	 * Constructor to initialize the migration manager.
	 *
	 * @param string $migrationDir Directory where migration files are located.
	 */
	public function __construct( $migrationDir ) {
		$this->migrationDir = $migrationDir;
		$this->loadMigrations();
	}

	/**
	 * Load migrations from the specified directory.
	 */
	protected function loadMigrations() {
		$migrationFiles = glob( $this->migrationDir . '/*.php' );

		// Remove index.php from the list of migration files.
		$migrationFiles = array_diff( $migrationFiles, array( $this->migrationDir . '/index.php' ) );

		if ( empty( $migrationFiles ) ) {
			return;
		}

		foreach ( $migrationFiles as $file ) {
			if ( is_file( $file ) ) {
				$migration = include $file;

				if ( $migration instanceof MigrationInterface ) {
					$this->registerMigration( $migration );
				}
			}
		}
	}

	/**
	 * Register a migration.
	 *
	 * @param MigrationInterface $migration The migration instance.
	 */
	public function registerMigration( MigrationInterface $migration ) {
		$this->migrations[] = $migration;
	}

	/**
	 * Run all pending migrations.
	 */
	public function runMigrations() {
		$ranMigrations = get_option( Plugin::MIGRATION_OPT_KEY, array() );

		foreach ( $this->migrations as $migration ) {
			$migrationName = get_class( $migration );

			if ( ! in_array( $migrationName, $ranMigrations, true ) ) {
				$migration->run();
				$ranMigrations[] = $migrationName;
				update_option( Plugin::MIGRATION_OPT_KEY, $ranMigrations );
			}
		}
	}
}
