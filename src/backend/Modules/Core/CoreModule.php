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
use AnsPress\Classes\RestRouteHandler;
use WP_REST_Server;

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

		add_action( 'init', array( $this, 'registerCommonBlock' ) );

		add_action( 'enqueue_block_assets', array( $this, 'registerBlockAssets' ) );
		add_filter( 'query_vars', array( $this, 'addQueryVars' ) );
		add_action( 'enqueue_block_assets', array( $this, 'enqueueBlockAssets' ) );

		add_action( 'rest_api_init', array( $this, 'registerRoutes' ) );
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

	/**
	 * Register block.
	 *
	 * @return void
	 */
	public function registerCommonBlock() {
		$assetInfo = include Plugin::getPathTo( 'build/frontend/common/index.asset.php' );

		wp_register_style(
			'anspress-common',
			Plugin::getUrlTo( 'build/frontend/common/index.css' ),
			false,
			$assetInfo['version']
		);

		$viewInfo = include Plugin::getPathTo( 'build/frontend/common/view.asset.php' );

		wp_register_script(
			'anspress-view',
			Plugin::getUrlTo( 'build/frontend/common/view.js' ),
			$assetInfo['dependencies'],
			$assetInfo['version'],
			true
		);
	}

	/**
	 * Register block assets.
	 *
	 * @return void
	 */
	public function registerBlockAssets() {
		wp_enqueue_style( 'anspress-common' );
		wp_enqueue_script( 'anspress-view' );
	}

	/**
	 * Add query vars.
	 *
	 * @param mixed $qvars Query vars.
	 * @return mixed
	 */
	public function addQueryVars( $qvars ) {
		$qvars[] = 'ap_question_paged';
		$qvars[] = 'ap_cat_paged';
		$qvars[] = 'ap_tag_paged';

		return $qvars;
	}

	/**
	 * Enqueue block assets.
	 *
	 * @return void
	 */
	public function enqueueBlockAssets() {
		wp_enqueue_style( 'anspress-fonts', ap_get_theme_url( 'css/fonts.css' ), array(), AP_VERSION );
	}

	/**
	 * Register routes.
	 *
	 * @return void
	 */
	public function registerRoutes() {
		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/comments',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( CommentController::class, 'createComment' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);

		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/comments/(?P<comment_id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( CommentController::class, 'deleteComment' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id'    => array(
						'required' => true,
						'type'     => 'integer',
					),
					'comment_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);

		register_rest_route(
			'anspress/v1',
			'/post/(?P<post_id>\d+)/comments',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => fn( $req ) => RestRouteHandler::run( array( CommentController::class, 'showComments' ), $req ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'post_id' => array(
						'required' => true,
						'type'     => 'integer',
					),
				),
			)
		);
	}
}
