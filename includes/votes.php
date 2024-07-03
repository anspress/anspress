<?php
/**
 * Handle all function related to voting system.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}






/**
 * Get a single vote from database.
 *
 * @param  integer      $post_id Post ID.
 * @param  integer      $user_id User ID.
 * @param  string|array $type    Vote type.
 * @param  string       $value   Vote value.
 * @return boolean|object
 * @since  4.0.0
 */
function ap_get_vote( $post_id, $user_id, $type, $value = '' ) {
	global $wpdb;
	$where = "SELECT * FROM {$wpdb->ap_votes} WHERE 1=1 ";

	if ( ! empty( $type ) ) {
		if ( is_array( $type ) ) {
			$vote_type_in = sanitize_comma_delimited( $type, 'str' );

			if ( ! empty( $vote_type_in ) ) {
				$where .= ' AND vote_type IN (' . $vote_type_in . ')';
			}
		} else {
			$where .= $wpdb->prepare( ' AND vote_type = %s', $type );
		}
	}

	if ( ! empty( $value ) ) {
		if ( is_array( $value ) ) {
			$value_in = sanitize_comma_delimited( $value, 'str' );

			if ( ! empty( $value_in ) ) {
				$where .= ' AND vote_value IN (' . $value_in . ')';
			}
		} else {
			$where .= $wpdb->prepare( ' AND vote_value = %s', $value );
		}
	}

	$query = $where . $wpdb->prepare( ' AND vote_post_id = %d AND  vote_user_id = %d LIMIT 1', $post_id, $user_id );

	$vote = $wpdb->get_row( $query ); // phpcs:ignore WordPress.DB

	if ( ! empty( $vote ) ) {
		return $vote;
	}

	return false;
}
