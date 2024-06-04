<?php
/**
 * Core module.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Modules\Core;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CoreModule.
 *
 * @package AnsPress\Modules\Core
 */
class CoreModule extends AbstractModule {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		$this->loadTextdomain();

		add_action( 'wpmu_new_blog', array( $this, 'createBlogTables' ), 10, 6 );
		add_filter( 'wpmu_drop_tables', array( $this, 'dropBlogTables' ), 10, 2 );
		add_filter( 'block_categories_all', array( $this, 'registerBlockCategory' ), 1 );
	}

	/**
	 * Init method.
	 *
	 * @return void
	 */
	public function loadTextdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'anspress-question-answer' );
		$loaded = load_textdomain(
			'anspress-question-answer',
			trailingslashit( WP_LANG_DIR ) . "anspress-question-answer/anspress-question-answer-{$locale}.mo"
		);

		if ( ! $loaded ) {
			load_plugin_textdomain( 'anspress-question-answer', false, Plugin::getPathTo( '/languages/' ) );
		}
	}

	/**
	 * Creating table whenever a new blog is created
	 *
	 * @access public
	 * @static
	 *
	 * @param  integer $blog_id Blog id.
	 */
	public static function createBlogTables( $blog_id ) {
		if ( is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
			switch_to_blog( $blog_id ); // @codingStandardsIgnoreLine
			require_once __DIR__ . '/activate.php';
			\AP_Activate::get_instance( true );
			restore_current_blog();
		}
	}

	/**
	 * Deleting the table whenever a blog is deleted
	 *
	 * @access public
	 * @static
	 *
	 * @param  array $tables  Table names.
	 * @param  int   $blog_id Blog ID.
	 *
	 * @return array
	 */
	public static function dropBlogTables( $tables, $blog_id ) {
		if ( empty( $blog_id ) || 1 === (int) $blog_id || $blog_id !== $GLOBALS['blog_id'] ) {
			return $tables;
		}

		global $wpdb;

		$tables[] = $wpdb->prefix . 'ap_views';
		$tables[] = $wpdb->prefix . 'ap_qameta';
		$tables[] = $wpdb->prefix . 'ap_activity';
		$tables[] = $wpdb->prefix . 'ap_votes';
		return $tables;
	}

	/**
	 * Register block category.
	 *
	 * @param mixed $categories Categories.
	 * @return array
	 */
	public function registerBlockCategory( $categories ) {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'anspress',
					'title' => __( 'AnsPress', 'anspress-question-answer' ),
				),
			)
		);
	}
}
