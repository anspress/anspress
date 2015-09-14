<?php
/**
 * Installation and activation of anspress, register hooks that are fired when the plugin is activated.
 *
 * @package     AnsPress
 * @copyright   Copyright (c) 2013, Rahul Aryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }


/**
 * Create base pages, add roles, add caps and create tables
 * @param $network_wide
 */
function anspress_activate( $network_wide ) {

	// add roles
	$ap_roles = new AP_Roles;
	$ap_roles->add_roles();
	$ap_roles->add_capabilities();

	ap_create_base_page();

	if ( ap_opt( 'ap_version' ) != AP_VERSION ) {
		ap_opt( 'ap_installed', 'false' );
		ap_opt( 'ap_version', AP_VERSION );
	}

	global $wpdb;
	/**
	 * Run DB quries only if AP_DB_VERSION does not match
	 */
	if ( ap_opt( 'ap_db_version' ) != AP_DB_VERSION ) {

		$charset_collate = ! empty( $wpdb->charset ) ? 'DEFAULT CHARACTER SET '.$wpdb->charset : '';

		$meta_table = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'ap_meta` (
            `apmeta_id` bigint(20) NOT NULL AUTO_INCREMENT,
            `apmeta_userid` bigint(20) DEFAULT NULL,
            `apmeta_type` varchar(256) DEFAULT NULL,
            `apmeta_actionid` bigint(20) DEFAULT NULL,
            `apmeta_value` text,
            `apmeta_param` LONGTEXT DEFAULT NULL,
            `apmeta_date` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`apmeta_id`)
            )'.$charset_collate.';';

		$activity_table = 'CREATE TABLE IF NOT EXISTS `'.$wpdb->prefix.'ap_activity` (
		    `id` bigint(20) NOT NULL AUTO_INCREMENT,
		    `user_id` bigint(20) DEFAULT NULL,
		    `secondary_user` bigint(20) DEFAULT NULL,
		    `type` varchar(256) DEFAULT NULL,
		    `status` varchar(256) DEFAULT NULL,
		    `content` LONGTEXT DEFAULT NULL,
		    `action` text DEFAULT NULL,
		    `permalink` text DEFAULT NULL,
		    `question_id` bigint(20) DEFAULT NULL,
		    `item_id` bigint(20) DEFAULT NULL,
		    `secondary_id` bigint(20) DEFAULT NULL,
		    `created` timestamp NULL DEFAULT NULL,
		    `updated` timestamp NULL DEFAULT NULL,
		    PRIMARY KEY (`id`)
		    )'.$charset_collate.';';

		$activity_meta = "CREATE TABLE IF NOT EXISTS `".$wpdb->base_prefix."ap_activitymeta` (
			  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `ap_activity_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			  `meta_key` varchar(255) DEFAULT NULL,
			  `meta_value` longtext,
			  PRIMARY KEY (`meta_id`)
			)".$charset_collate.";";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( $meta_table );
		dbDelta( $activity_table );
		dbDelta( $activity_meta );

		ap_opt( 'ap_db_version', AP_DB_VERSION );
	}

	if ( ! get_option( 'anspress_opt' ) ) {
		update_option( 'anspress_opt', ap_default_options() );
	} else { 	update_option( 'anspress_opt', get_option( 'anspress_opt' ) + ap_default_options() ); }

	ap_opt( 'ap_flush', 'true' );
	flush_rewrite_rules( false );
}
