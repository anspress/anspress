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
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Create base pages, add roles, add caps and create tables
 * @param $network_wide
 */
function anspress_activate( $network_wide ) {
	$category_ext = 'categories-for-anspress/categories-for-anspress.php';

	$category_error = false;
	if(file_exists(WP_PLUGIN_DIR.'/'.$category_ext)){
		$category_ext_data = get_plugin_data( WP_PLUGIN_DIR.'/'.$category_ext);
		$category_error = !version_compare ( $category_ext_data['Version'], '1.3.5', '>=') ? true : false;
	}	

	$tag_ext = 'tags-for-anspress/tags-for-anspress.php';

	$tag_error = false;
	if(file_exists(WP_PLUGIN_DIR.'/'.$tag_ext)){
		$tag_ext_data = get_plugin_data( WP_PLUGIN_DIR.'/'.$tag_ext);
		$tag_error = !version_compare ( $tag_ext_data['Version'], '1.2.7', '>=') ? true : false;
	}

	if ( $category_error || $tag_error ) {
	    echo '<h3>'.__('Please update all AnsPress extensions before activating. <a target="_blank" href="http://anspress.io/questions/ask/">Ask for help</a>', 'ap').'</h3>';
	    @trigger_error(__('Please update all AnsPress extensions before activating.', 'ap'), E_USER_ERROR);
	}

	// add roles
	$ap_roles = new AP_Roles;
	$ap_roles->add_roles();
	$ap_roles->add_capabilities();

	ap_create_base_page();
	
	if( ap_opt ('ap_version') != AP_VERSION ) {
		ap_opt('ap_installed', 'false');
		ap_opt('ap_version', AP_VERSION);
	}
	
	global $wpdb;
	/**
	 * Run DB quries only if AP_DB_VERSION does not match
	 */
	if( ap_opt ('ap_db_version') != AP_DB_VERSION ) {	
	
		$charset_collate = !empty($wpdb->charset) ? "DEFAULT CHARACTER SET ".$wpdb->charset : '';

		$meta_table = "CREATE TABLE IF NOT EXISTS `".$wpdb->base_prefix."ap_meta` (
				  `apmeta_id` bigint(20) NOT NULL AUTO_INCREMENT,
				  `apmeta_userid` bigint(20) DEFAULT NULL,
				  `apmeta_type` varchar(256) DEFAULT NULL,
				  `apmeta_actionid` bigint(20) DEFAULT NULL,
				  `apmeta_value` text,
				  `apmeta_param` LONGTEXT DEFAULT NULL,
				  `apmeta_date` timestamp NULL DEFAULT NULL,
				  PRIMARY KEY (`apmeta_id`)
				)".$charset_collate.";";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta ($meta_table);
		
		ap_opt ('ap_db_version', AP_DB_VERSION);
	}

	
	if(!get_option('anspress_opt'))
		update_option('anspress_opt', ap_default_options());
	else
		update_option('anspress_opt', get_option('anspress_opt') + ap_default_options());
		
	
	ap_opt('ap_flush', 'true'); 
	flush_rewrite_rules( false );
}
