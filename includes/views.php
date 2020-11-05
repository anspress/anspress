<?php
/**
 * AnsPress views.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Views hooks
 */
class AnsPress_Views {
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since 2.4.8 Removed `$ap` args.
	 */
	public static function init() {
		anspress()->add_action( 'shutdown', __CLASS__, 'insert_views' );
		anspress()->add_action( 'ap_before_delete_question', 'AnsPress_Vote', 'delete_votes' );
	}

	/**
	 * Insert view count on loading single question page.
	 *
	 * @param  string $template Template name.
	 */
	public static function insert_views( $template ) {

		if ( is_question() ) {
			// By default do not store views in ap_views table.
			if ( apply_filters( 'ap_insert_view_to_db', false ) ) {
				ap_insert_views( get_question_id(), 'question' );
			}

			// Update qameta.
			ap_update_views_count( get_question_id() );
		}
	}

	/**
	 * Delete views count when post is deleted.
	 *
	 * @param integer $post_id Post ID.
	 * @since 4.0.0
	 */
	public static function delete_views( $post_id ) {
		global $wpdb;

		if ( apply_filters( 'ap_insert_view_to_db', false ) ) {
			$wpdb->delete( $wpdb->ap_views, [ 'view_ref_id' => $post_id ], [ '%d' ] );
		}
	}
}

/**
 * Insert view data in ap_meta table and update qameta.
 *
 * @param  integer|boolean $ref_id Reference ID.
 * @param  string          $type View type, default is question.
 * @param  integer|false   $user_id User ID.
 * @return boolean|integer
 */
function ap_insert_views( $ref_id, $type = 'question', $user_id = false, $ip = false ) {

	global $wpdb;

	if ( empty( $ref_id ) ) {
		return false;
	}

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( false === $ip || false === filter_var( $ip, FILTER_VALIDATE_IP ) ) {
		$ip = $_SERVER['REMOTE_ADDR']; // @codingStandardsIgnoreLine
	}

	// Insert to DB only if not viewed before and not anonymous.
	if ( ! empty( $user_id ) && ! ap_is_viewed( $ref_id, $user_id ) ) {
		$values = array(
			'view_user_id' => $user_id,
			'view_type'    => 'question',
			'view_ref_id'  => $ref_id,
			'view_ip'      => $ip,
			'view_date'    => current_time( 'mysql' ),
		);

		$insert = $wpdb->insert( $wpdb->ap_views, $values, [ '%d', '%s', '%d', '%s', '%s' ] ); // db call okay.

		if ( false !== $insert ) {

			/**
				* Trigger action after inserting a view.
				*
				* @param integer $view_id Newly inserted view id.
				*/
			do_action( 'ap_insert_view', $wpdb->insert_id );

			return $wpdb->insert_id;
		}
	}

	return false;
}


/**
 * Check if user already viewd post or user profile.
 *
 * @param integer|false  $ref_id Reference ID.
 * @param integer        $user_id User ID.
 * @param string         $type View type.
 * @param string|boolean $ip IP address.
 * @return boolean
 */
function ap_is_viewed( $ref_id, $user_id, $type = 'question', $ip = false ) {

	if ( empty( $ref_id ) ) {
		return false;
	}

	global $wpdb;
	$ip_clue = '';

	if ( false !== $ip ) {
		$ip_clue = $wpdb->prepare( " AND view_ip = '%s'", $ip );
	}

	$query = $wpdb->prepare( "SELECT count(*) FROM {$wpdb->ap_views} WHERE view_user_id = %d AND view_ref_id = %d AND view_type = '%s' {$ip_clue}", $user_id, $ref_id, $type ); // @codingStandardsIgnoreLine


	$count = $wpdb->get_var( $query ); // @codingStandardsIgnoreLine

	return $count > 0 ? true : false;
}

/**
 * Get views count from views table.
 *
 * @param integer $ref_id Reference id.
 * @param string  $type   View type.
 * @return integer
 */
function ap_get_views( $ref_id, $type = 'question' ) {
	global $wpdb;
	$query = $wpdb->prepare( "SELECT count(*) FROM {$wpdb->ap_views} WHERE view_ref_id = %d AND view_type = '%s'", $ref_id, $type ); // @codingStandardsIgnoreLine

	$count = (int) $wpdb->get_var( $query ); // @codingStandardsIgnoreLine

	return $count;
}
