<?php
/**
 * MigrationManager class.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Classes;

use AnsPress\Interfaces\MigrationInterface;
use Exception;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to manage running migrations.
 *
 * @since 5.0.0
 */
class MigrationManager {
	/**
	 * Option key to store the installed migrations.
	 *
	 * @var string
	 */
	const MIGRATION_OPT_KEY = 'anspress_migrations';

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
	protected $migrationsDir;

	/**
	 * Constructor to initialize the migration manager.
	 *
	 * @param string $migrationDir Directory where migration files are located.
	 */
	public function __construct( $migrationDir ) {
		$this->migrationsDir = $migrationDir;
		$this->loadMigrations();
	}

	/**
	 * Get the installted migration file names.
	 *
	 * @return array
	 */
	public static function getMigrationsInstalled(): array {
		return (array) get_option( self::MIGRATION_OPT_KEY, array() );
	}

	/**
	 * Add migration to installed list.
	 *
	 * @param string $migration Migration name.
	 * @return void
	 */
	public static function addMigration( string $migration ) {
		$installedMigrations = self::getMigrationsInstalled();

		if ( ! in_array( $migration, $installedMigrations, true ) ) {
			$installedMigrations[] = $migration;
			update_option( self::MIGRATION_OPT_KEY, $installedMigrations );
		}
	}

	/**
	 * Load migrations from the specified directory.
	 */
	protected function loadMigrations(): void {
		$migrationFiles = glob( $this->migrationsDir . '/*.php' );

		if ( empty( $migrationFiles ) ) {
			return;
		}

		// Remove index.php from the list of migration files.
		$migrationFiles = array_diff( $migrationFiles, array( $this->migrationsDir . '/index.php' ) );
		$migrationFiles = array_map( 'basename', $migrationFiles );

		$installedMigrations = Plugin::getMigrationsInstalled();

		// Remove already installed migrations but note that installed migration is just file basename not path.
		$migrationFiles = array_diff( $migrationFiles, $installedMigrations );

		if ( empty( $migrationFiles ) ) {
			return;
		}

		// Sort migration files by name.
		sort( $migrationFiles );

		foreach ( $migrationFiles as $file ) {
			if ( is_file( $file ) ) {
				$this->migrations[] = $file;
			}
		}
	}

	/**
	 * Runs all pending migrations.
	 */
	public function runMigrations() {
		if ( empty( $this->migrations ) ) {
			return;
		}

		foreach ( $this->migrations as $migrationBaseName ) {
			$migrationFile = $this->migrationsDir . '/' . $migrationBaseName;

			if ( ! file_exists( $migrationFile ) ) {
				Plugin::get( Logger::class )->warning( 'Migration file not found: ' . $migrationFile );
				continue;
			}

			$migration = include $migrationFile;

			if ( ! $migration instanceof MigrationInterface ) {
				Plugin::get( Logger::class )->warning( 'Migration file must return an instance of MigrationInterface.' );

				continue;
			}

			try {
				$migration->run();
				$this->addMigration( $migrationBaseName );
			} catch ( Exception $e ) {
				Plugin::get( Logger::class )->error( 'Error running migration: ' . $migrationBaseName, $e->getMessage() );
			}
		}

		// Update installed migration version.
		Plugin::updateInstalledDbVersion();
	}
}
