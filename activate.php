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

class AP_Activate
{
	/**
	 * Instance of this class.
	 * @var      object
	 */
	protected static $instance = null;
	public $charset_collate;
	public $tables = array();
	public $network_wide;

	/**
	 * Return an instance of this class.
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance( $network_wide = '' ) {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			anspress();
			self::$instance = new self;
			global $network_wide;
			$network_wide = $network_wide;
		}

		return self::$instance;
	}

	public function __construct() {
		global $network_wide;
		$this->network_wide = $network_wide;

		// Append table names in $wpdb.
		ap_append_table_names();

		if ( $this->network_wide ) {
			$this->network_activate();
		} else {
			$this->activate();
		}
	}

	public function meta_table() {
		global $wpdb;

		if ( $wpdb->get_var( "show tables like '{$wpdb->ap_meta}'" ) != $wpdb->ap_meta ) {
			$this->tables[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->ap_meta.'` (
                `apmeta_id` bigint(20) NOT NULL AUTO_INCREMENT,
                `apmeta_userid` bigint(20) DEFAULT NULL,
                `apmeta_type` varchar(256) DEFAULT NULL,
                `apmeta_actionid` bigint(20) DEFAULT NULL,
                `apmeta_value` text,
                `apmeta_param` LONGTEXT DEFAULT NULL,
                `apmeta_date` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`apmeta_id`)
	            )'.$this->charset_collate.';';
		}
	}

	public function activity_table() {
		global $wpdb;

		if ( $wpdb->get_var( "show tables like '{$wpdb->ap_activity}'" ) != $wpdb->ap_activity ) {
			$this->tables[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->ap_activity.'` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                `user_id` bigint(20) DEFAULT NULL,
                `secondary_user` bigint(20) DEFAULT NULL,
                `type` varchar(256) DEFAULT NULL,
                `parent_type` varchar(256) DEFAULT NULL,
                `status` varchar(256) DEFAULT NULL,
                `content` LONGTEXT DEFAULT NULL,
                `permalink` text DEFAULT NULL,
                `question_id` bigint(20) DEFAULT NULL,
                `answer_id` bigint(20) DEFAULT NULL,
                `item_id` bigint(20) DEFAULT NULL,
                `term_ids` LONGTEXT DEFAULT NULL,
                `created` timestamp NULL DEFAULT NULL,
                `updated` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
			    )'.$this->charset_collate.';';
		}
	}

	/**
	 * AnsPress activity meta table.
	 */
	public function activity_meta_table() {
		global $wpdb;

		if ( $wpdb->get_var( "show tables like '{$wpdb->ap_activitymeta}'" ) != $wpdb->ap_activitymeta ) {
			$this->tables[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->ap_activitymeta."` (
                  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                  `ap_activity_id` bigint(20) unsigned NOT NULL DEFAULT '0',
                  `meta_key` varchar(255) DEFAULT NULL,
                  `meta_value` longtext,
                  PRIMARY KEY (`meta_id`)
				)".$this->charset_collate.';';
		}
	}

	/**
	 * AnsPress notification table.
	 */
	public function notification_table() {
		global $wpdb;

		if ( $wpdb->get_var( "show tables like '{$wpdb->ap_notifications}'" ) != $wpdb->ap_notifications ) {
			$this->tables[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->ap_notifications.'` (
                `noti_id` bigint(20) NOT NULL AUTO_INCREMENT,
                `noti_activity_id` bigint(20) NOT NULL,
                `noti_user_id` bigint(20) NOT NULL,
                `noti_status` varchar(225) NOT NULL,                
                `noti_date` timestamp NOT NULL,
                PRIMARY KEY (`noti_id`)
	        )'.$this->charset_collate.';';
		}
	}

	/**
	 * AnsPress subscriber table.
	 */
	public function subscribers_table() {
		global $wpdb;

		if ( $wpdb->get_var( "show tables like '{$wpdb->ap_subscribers}'" ) != $wpdb->ap_subscribers ) {
			$this->tables[] = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->ap_subscribers.'` (
                `subs_id` bigint(20) NOT NULL AUTO_INCREMENT,               
                `subs_user_id` bigint(20) NOT NULL,
                `subs_question_id` bigint(20) NOT NULL,
                `subs_item_id` bigint(20) NOT NULL,
                `subs_activity` varchar(225) NOT NULL,
                `subs_answer_id` bigint(20) NOT NULL,
                PRIMARY KEY (`subs_id`)
	        )'.$this->charset_collate.';';
		}

		// Check if answer_id column exists if not add it.
		ap_db_subscriber_answer_id_col();
	}

	/**
	 * Check subscribers table for old column names.
	 * If exists then rename it.
	 */
	public function fix_subscribers_table() {
		global $wpdb;
		$subscriber_cols = $wpdb->get_results("SHOW COLUMNS FROM {$wpdb->prefix}ap_subscribers" );
		$subscriber_old_cols = array(
			'id' 			=> 'bigint(20) NOT NULL AUTO_INCREMENT',
			'user_id' 		=> 'bigint(20)',
			'question_id' 	=> 'bigint(20)',
			'item_id' 		=> 'bigint(20)',
			'activity' 		=> 'varchar(225 )',
		);

		if ( $subscriber_cols ) {

			foreach ( $subscriber_cols as $col ) {
				if ( in_array($col->Field, array_keys($subscriber_old_cols ) ) ) {
					$wpdb->query( "ALTER TABLE `{$wpdb->prefix}ap_subscribers` CHANGE {$col->Field} subs_{$col->Field} ".$subscriber_old_cols[$col->Field] );
				}
			}
		}
	}

	/**
	 * Insert and update tables
	 */
	public function insert_tables() {
		global $wpdb;
		$this->charset_collate = ! empty( $wpdb->charset ) ? 'DEFAULT CHARACTER SET '.$wpdb->charset : '';

		$this->meta_table();
		$this->activity_table();
		$this->activity_meta_table();
		$this->notification_table();
		$this->subscribers_table();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		if ( count( $this->tables ) > 0 ) {
			foreach ( $this->tables as $table ) {
				dbDelta( $table );
			}
		}

		/*
        if( $activity_cols ){
            if ( !in_array($col->Field, 'term_ids' ) )
                $wpdb->query( "ALTER TABLE `{$wpdb->prefix}ap_activity` ADD term_ids LONGTEXT after item_id;" );
		}*/

		$this->fix_subscribers_table();
	}

	/**
	 * Create base pages, add roles, add caps and create tables
	 */
	public function activate( ) {

		// add roles.
		$ap_roles = new AP_Roles;
		$ap_roles->add_roles();
		$ap_roles->add_capabilities();

		ap_create_base_page();

		if (  in_array(ap_opt( 'ap_version' ), array( '2.3.8', '2.4-beta1', '2.4-beta2', '2.4-beta3', '2.4-beta4' ) )  ) {
			update_option( 'ap_update_helper', true );
		}

		if ( ap_opt( 'ap_version' ) != AP_VERSION ) {
			ap_opt( 'ap_installed', 'false' );
			ap_opt( 'ap_version', AP_VERSION );
		}

		$this->insert_tables();
		ap_opt('db_version', AP_DB_VERSION );
		update_option( 'anspress_opt', get_option( 'anspress_opt' ) + ap_default_options() );

		ap_opt( 'ap_flush', 'true' );
		flush_rewrite_rules( false );
	}

	public function network_activate() {
		global $wpdb;
		// $current_blog = $wpdb->blogid;
		// Get all blogs in the network and activate plugin on each one
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			$this->activate();
			restore_current_blog();
		}
	}
}

/**
 * Check if DB version in database is not equal to current.
 * @return boolean
 */
function ap_db_version_is_lower() {
	ap_opt( 'db_version', 18 );
	$opt = ap_opt('db_version' );
	if ( ! empty( $opt ) && $opt > AP_DB_VERSION  ) {
		return false;
	}

	return true;
}

/**
 * Add `subs_answer_id` col in ap_subscribers table.
 * @since 3.0.0
 */
function ap_db_subscriber_answer_id_col() {
	global $wpdb;
	if ( $wpdb->get_var( "show tables like '{$wpdb->ap_subscribers}'" ) == $wpdb->ap_subscribers ) {
		$ap_subscribers_cols = $wpdb->get_col("SHOW COLUMNS FROM `$wpdb->ap_subscribers`" );
		if ( ! in_array( 'subs_answer_id', $ap_subscribers_cols ) ) {
			$wpdb->query( "ALTER TABLE `$wpdb->ap_subscribers` ADD subs_answer_id bigint(20) NOT NULL after subs_question_id;" );
		}
	}
}
