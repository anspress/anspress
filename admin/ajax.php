<?php
/**
 * AnsPresss admin ajax class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 */
class AnsPress_Admin_Ajax
{
	/**
	 * Initialize admin ajax
	 */
	public function __construct() {
		anspress()->add_action( 'wp_ajax_ap_taxo_rename', $this, 'ap_taxo_rename' );
		anspress()->add_action( 'wp_ajax_ap_delete_flag', $this, 'ap_delete_flag' );
		anspress()->add_action( 'ap_ajax_ap_clear_flag', $this, 'clear_flag' );
		anspress()->add_action( 'ap_ajax_ap_admin_vote', $this, 'ap_admin_vote' );
	}

	/**
	 * Ajax cllback for updating old taxonomy question_tags to question_tag
	 */
	public function ap_taxo_rename() {

		if ( current_user_can( 'manage_options' ) ) {
			global $wpdb;

			$wpdb->query( 'UPDATE '.$wpdb->prefix."term_taxonomy SET taxonomy = 'question_tag' WHERE  taxonomy = 'question_tags'" );

			ap_opt( 'tags_taxo_renamed', 'true' );
		}

		die();
	}

	/**
	 * Delete post flag
	 */
	public function ap_delete_flag() {

		$id = (int) sanitize_text_field( $_POST['id'] );
		if ( wp_verify_nonce( $_POST['__nonce'], 'flag_delete'.$id ) && current_user_can( 'manage_options' ) ) {
			return ap_delete_meta( false, $id );
		}
		die();
	}

	/**
	 * Clear post flags.
	 * @since 2.4.6
	 */
	public function clear_flag() {
		$args = $_POST['args'];
		if ( current_user_can( 'manage_options' ) && wp_verify_nonce( $_POST['__nonce'], 'clear_flag_'. $args[0] ) ) {
			ap_delete_all_post_flags( $args[0] );
			ap_set_flag_count( $args[0], 0 );
			die( _e('0' ) );
		}
		die();
	}

	/**
	 * Handle ajax vote in wp-admin post edit screen.
	 * Cast vote as anonymous use with ID 0, so that when this vote never get
	 * rest if user vote.
	 * @since 2.5
	 */
	public function ap_admin_vote() {
		$args = $_POST['args'];

		if ( current_user_can( 'manage_options' ) && wp_verify_nonce( $_POST['__nonce'], 'admin_vote' ) ) {
			$post = ap_get_post( $args[0] );

			if ( $post ) {
				$type = $args[1] == 'up' ? 'vote_up'  : 'vote_down';
				$inserted = ap_vote_insert( $post->ID, 0, $type );

				if( false !== $inserted ) {

				}
				echo $count['net_vote'];
			}
		}
		die();
	}

}
