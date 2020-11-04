<?php
/**
 * Installation and activation of anspress, register hooks that are fired when the plugin is activated.
 *
 * @package     AnsPress
 * @copyright   Copyright (c) 2013, Rahul Aryan
 * @license     https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @since       0.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activate AnsPress.
 */
class AP_Activate {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Char set.
	 *
	 * @var string
	 */
	public $charset_collate;

	/**
	 * Tables
	 *
	 * @var array
	 */
	public $tables = array();

	/**
	 * Network wide activate.
	 *
	 * @var boolean
	 */
	public $network_wide;

	/**
	 * Return an instance of this class.
	 *
	 * @param string|boolean $network_wide Actiavte plugin network wide.
	 * @return object A single instance of this class.
	 */
	public static function get_instance( $network_wide = '' ) {

		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			anspress();
			self::$instance          = new self();
			$GLOBALS['network_wide'] = $network_wide;
		}

		return self::$instance;
	}

	/**
	 * Construct class.
	 */
	public function __construct() {
		global $network_wide;
		$this->network_wide = $network_wide;
		$this->disable_ext();
		$this->delete_options();
		$this->enable_addons();
		$this->reactivate_addons();

		// Append table names in $wpdb.
		ap_append_table_names();

		if ( $this->network_wide ) {
			$this->network_activate();
		} else {
			$this->activate();
		}
	}

	/**
	 * Disable old AnsPress extensions.
	 */
	public function disable_ext() {
		deactivate_plugins(
			[
				'categories-for-anspress/categories-for-anspress.php',
				'tags-for-anspress/tags-for-anspress.php',
				'anspress-email/anspress-email.php',
				'question-labels/question-labels.php',
				'anspress-paid-membership/anspress-paid-membership.php',
			]
		);
	}

	/**
	 * Delete old AnsPress options.
	 *
	 * @since 4.1.5
	 */
	public function delete_options() {
		$settings = get_option( 'anspress_opt', [] );
		unset( $settings['user_page_title_questions'] );
		unset( $settings['user_page_slug_questions'] );
		unset( $settings['user_page_title_answers'] );
		unset( $settings['user_page_slug_answers'] );
		update_option( 'anspress_opt', $settings );
		wp_cache_delete( 'anspress_opt', 'ap' );
		wp_cache_delete( 'ap_default_options', 'ap' );
	}

	/**
	 * Enable default addons.
	 *
	 * @since unknown
	 * @since 4.1.6 Enable category add-on by default.
	 * @since 4.1.8 Fixed #425
	 */
	public function enable_addons() {
		ap_activate_addon( 'reputation.php' );
		ap_activate_addon( 'email.php' );
		ap_activate_addon( 'categories.php' );
	}

	/**
	 * Ap_qameta table.
	 *
	 * @since 4.1.8 Added primary key.
	 */
	public function qameta_table() {
		global $wpdb;

		// @codingStandardsIgnoreLine
		$this->tables[] = 'CREATE TABLE ' . $wpdb->ap_qameta . ' (
			post_id bigint(20) NOT NULL,
			selected_id bigint(20) DEFAULT NULL,
			comments bigint(20) DEFAULT 0,
			answers bigint(20) DEFAULT 0,
			ptype varchar(100) DEFAULT NULL,
			featured tinyint(1) DEFAULT 0,
			selected tinyint(1) DEFAULT 0,
			votes_up bigint(20) DEFAULT 0,
			votes_down bigint(20) DEFAULT 0,
			subscribers TEXT DEFAULT NULL,
			views bigint(20) DEFAULT 0,
			closed tinyint(1) DEFAULT 0,
			flags bigint(20) DEFAULT 0,
			terms LONGTEXT DEFAULT NULL,
			attach LONGTEXT DEFAULT NULL,
			activities LONGTEXT DEFAULT NULL,
			fields LONGTEXT DEFAULT NULL,
			roles varchar(100) DEFAULT NULL,
			last_updated timestamp NULL DEFAULT NULL,
			PRIMARY KEY  (post_id)
		)' . $this->charset_collate . ';';
	}

	/**
	 * AnsPress ap_votes table.
	 */
	public function votes_table() {
		global $wpdb;

		$this->tables[] = 'CREATE TABLE ' . $wpdb->ap_votes . ' (
				vote_id bigint(20) NOT NULL AUTO_INCREMENT,
				vote_post_id bigint(20) NOT NULL,
				vote_user_id bigint(20) NOT NULL,
				vote_rec_user bigint(20) NOT NULL,
				vote_type varchar(100) DEFAULT NULL,
				vote_value varchar(100) DEFAULT NULL,
				vote_date timestamp NULL DEFAULT NULL,
				PRIMARY KEY  (vote_id),
				KEY vote_post_id (vote_post_id)
			)' . $this->charset_collate . ';';
	}

	/**
	 * AnsPress views table.
	 */
	public function views_table() {
		global $wpdb;

		$this->tables[] = 'CREATE TABLE ' . $wpdb->ap_views . ' (
				view_id bigint(20) NOT NULL AUTO_INCREMENT,
				view_user_id bigint(20) DEFAULT NULL,
				view_type varchar(100) DEFAULT NULL,
				view_ref_id bigint(20) DEFAULT NULL,
				view_ip varchar(39),
				view_date timestamp NULL DEFAULT NULL,
				PRIMARY KEY  (view_id),
				KEY view_user_id (view_user_id)
			)' . $this->charset_collate . ';';
	}

	/**
	 * AnsPress reputation table.
	 *
	 * @since 4.1.8 Added index for column rep_user_id and rep_ref_id.
	 */
	public function reputation_table() {
		global $wpdb;

		$this->tables[] = 'CREATE TABLE ' . $wpdb->ap_reputations . ' (
				rep_id bigint(20) NOT NULL AUTO_INCREMENT,
				rep_user_id bigint(20) DEFAULT NULL,
				rep_event varchar(100) DEFAULT NULL,
				rep_ref_id bigint(20) DEFAULT NULL,
				rep_date timestamp NULL DEFAULT NULL,
				PRIMARY KEY  (rep_id),
				KEY rep_user_id (rep_user_id),
				KEY rep_ref_id (rep_ref_id)
			)' . $this->charset_collate . ';';
	}

	/**
	 * AnsPress email subscription related table.
	 *
	 * @since 4.1.8 Added index for column subs_user_id and subs_ref_id.
	 */
	public function subscribers_table() {
		global $wpdb;

		$this->tables[] = 'CREATE TABLE ' . $wpdb->ap_subscribers . ' (
				subs_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				subs_user_id bigint(20) UNSIGNED NOT NULL,
				subs_ref_id bigint(20) UNSIGNED NOT NULL,
				subs_event varchar(100) NOT NULL,
				PRIMARY KEY  (subs_id),
				KEY subs_user_id (subs_user_id),
				KEY subs_ref_id (subs_ref_id)
			)' . $this->charset_collate . ';';
	}

	/**
	 * AnsPress activity table.
	 *
	 * @since 4.1.2
	 * @since 4.1.8 Added index column for activity_q_id, activity_a_id, activity_user_id.
	 */
	public function activity_table() {
		global $wpdb;

		// Check activity table exists.
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->ap_activity'" ) === $wpdb->ap_activity ) {
			// Delete old activity table if exists.
			$existing_table = $wpdb->get_row( "SHOW COLUMNS FROM $wpdb->ap_activity LIKE 'secondary_user'" );

			if ( ! empty( $existing_table ) && 'secondary_user' === $existing_table->Field ) {
				$wpdb->query( "DROP TABLE IF EXISTS $wpdb->ap_activity" );
			}
		}

		$this->tables[] = 'CREATE TABLE ' . $wpdb->ap_activity . ' (
				activity_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				activity_action varchar(45) NOT NULL,
				activity_q_id bigint(20) UNSIGNED NOT NULL,
				activity_a_id bigint(20) UNSIGNED NULL,
				activity_c_id bigint(20) UNSIGNED NULL,
				activity_user_id bigint(20) UNSIGNED NOT NULL,
				activity_date timestamp NULL DEFAULT NULL,
				PRIMARY KEY  (activity_id),
				KEY activity_q_id (activity_q_id),
				KEY activity_a_id (activity_a_id),
				KEY activity_user_id (activity_user_id)
			)' . $this->charset_collate . ';';
	}

	/**
	 * Insert and update tables
	 */
	public function insert_tables() {
		global $wpdb;
		$this->charset_collate = ! empty( $wpdb->charset ) ? 'DEFAULT CHARACTER SET ' . $wpdb->charset : '';

		$this->qameta_table();
		$this->votes_table();
		$this->views_table();
		$this->reputation_table();
		$this->subscribers_table();
		$this->activity_table();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		if ( count( $this->tables ) > 0 ) {
			foreach ( $this->tables as $table ) {
				dbDelta( $table );
			}
		}
	}

	/**
	 * Create base pages, add roles, add caps and create tables
	 */
	public function activate() {

		// add roles.
		$ap_roles = new AP_Roles();
		$ap_roles->add_roles();
		$ap_roles->add_capabilities();

		if ( ap_opt( 'ap_version' ) !== AP_VERSION ) {
			ap_opt( 'ap_installed', 'false' );
			ap_opt( 'ap_version', AP_VERSION );
		}

		if ( get_option( 'anspress_db_version' ) === '' ) {
			update_option( 'anspress_using_previous', 'true' );
		}

		$this->insert_tables();
		update_option( 'anspress_db_version', AP_DB_VERSION );
		update_option( 'anspress_opt', get_option( 'anspress_opt' ) + ap_default_options() );

		// Create main pages.
		ap_create_base_page();

		ap_opt( 'ap_flush', 'true' );
	}

	/**
	 * Network activate.
	 */
	public function network_activate() {
		global $wpdb;

		// Get all blogs in the network and activate plugin on each one
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" ); // db call ok, cache ok.

		foreach ( (array) $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id ); // @codingStandardsIgnoreLine
			$this->activate();
			restore_current_blog();
		}
	}

	/**
	 * As of version 4.1.8 addons names are changed hence make
	 * sure to reactivate previously active addons.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	public function reactivate_addons() {
		$active_addons = get_option( 'anspress_addons', [] );

		foreach ( $active_addons as $file => $active ) {
			if ( false !== strpos( $file, 'free/' ) ) {
				// Get current addons from database.
				$addons = get_option( 'anspress_addons', [] );

				// Delete old addon name from option and update.
				unset( $addons[ $file ] );
				update_option( 'anspress_addons', $addons );

				// Try to activate by new name.
				$new_addon_name = str_replace( 'free/', '', $file );

				// New names of addon.
				$new_name = array(
					'category'     => 'categories',
					'tag'          => 'tags',
					'notification' => 'notifications',
				);

				// Replace old name by new name.
				if ( isset( $new_name[ $new_addon_name ] ) ) {
					$new_addon_name = $new_name[ $new_addon_name ];
				}

				ap_activate_addon( $new_addon_name );
			}
		}
	}
}
