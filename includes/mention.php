<?php
/**
 * Mention functionality
 * Handle AnsPress mention functionality.
 *
 * @link 	https://anspress.io/
 * @since 	2.4
 * @author  Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Mention hooks
 */
class AP_Mentions_Hooks{

	/**
	 * Initialize class
	 * @since 2.4.8 Removed `$ap` args.
	 */
	public function __construct() {
		anspress()->add_filter( 'ap_pre_insert_question', __CLASS__, 'linkyfy_mentions' );
		anspress()->add_filter( 'ap_pre_insert_answer', __CLASS__, 'linkyfy_mentions' );
		anspress()->add_filter( 'ap_pre_update_question', __CLASS__, 'linkyfy_mentions' );
		anspress()->add_filter( 'ap_pre_update_answer', __CLASS__, 'linkyfy_mentions' );
		anspress()->add_action( 'ap_ajax_search_mentions', __CLASS__, 'search_mentions' );
	}

	/**
	 * Linkyfy mentions before contents get inserted to database.
	 * @param  string $content Post content.
	 * @return string
	 */
	public static function linkyfy_mentions($post_arr) {
		$post_arr['post_content'] = ap_linkyfy_mentions( $post_arr['post_content'] );
		return $post_arr;
	}

	/**
	 * Search metion user name and login.
	 * @since 3.0.0
	 */
	public static function search_mentions( ) {
		if ( ! ap_verify_default_nonce() ) {
			wp_die();
		}

		$term = ap_sanitize_unslash( 'term', 'request' );
		wp_send_json( ap_search_mentions( false, $term ) );
	}
}

/**
 * Surround mentions with anchor tag.
 * @param  string $content Post content.
 * @return string
 */
function ap_linkyfy_mentions($content) {
	if ( ! ap_opt( 'base_before_user_perma' ) ) {
		$base = home_url( '/'.ap_get_user_page_slug().'/' );
	} else {
		$base = ap_get_link_to( ap_get_user_page_slug() );
	}
var_dump($content);
	// Find mentions and wrap with anchor.
	$content = preg_replace( '/(?!<a[^>]*?>)@(\w+)(?![^<]*?<\/a>)/', '<a class="ap-mention-link" href="'.$base.'$1">@$1</a> ', $content );
	var_dump($content);

	return $content;
}

/**
 * Find mentioned users in a post.
 * @param  string $content Post or comment contents.
 * @return array|false
 */
function ap_find_mentioned_users( $content ) {
	global $wpdb;

	// Find all mentions in content.
	preg_match_all( '/(?:[\s.]|^)@(\w+)/', $content, $matches );

	if ( is_array( $matches ) && count( $matches ) > 0 && ! empty( $matches[0] ) ) {
		$user_logins = array();

		// Remove duplicates.
		$unique_logins = array_unique( $matches[0] );

		foreach ( $unique_logins as $user_login ) {
			$user_logins[] = sanitize_title_for_query( sanitize_user( wp_unslash( $user_login ), true ) );
		}

		if ( count( $user_logins ) == 0 ) {
			return false;
		}

		$user_logins_s = "'". implode( "','", $user_logins ) ."'";

		$key = md5( $user_logins_s );

		$cache = wp_cache_get( $key, 'ap_user_ids' );

		if ( false !== $cache ) {
			return $cache;
		}

		$query = $wpdb->prepare( "SELECT id, user_login FROM $wpdb->users WHERE user_login IN ($user_logins_s)" );

		$result = $wpdb->get_results( $query );

		wp_cache_set( $key, $result, 'ap_user_ids' );

		return $result;
	}

	return false;
}

/**
 * Return current question users with login and display name.
 * If search is passed then it will search for user.
 * @param  boolean|integer $question_id Question iD.
 * @param  boolean|string  $search      Search string.
 * @return array
 * @since  3.0.0
 */
function ap_search_mentions( $question_id = false, $search = false ) {
	global $wpdb;

	if ( false === $question_id ) {
		$question_id = get_question_id();
	}

	if ( false !== $search ) {
		$search = sanitize_text_field( $search );
		$query = $wpdb->prepare( "SELECT DISTINCT u.display_name as name, u.user_login as login FROM $wpdb->users u WHERE display_name LIKE '%%%s%%' OR user_login LIKE '%%%s%%' ", $search, $search );
	} else {
		$query = $wpdb->prepare( "SELECT DISTINCT u.display_name as name, u.user_login as login FROM $wpdb->users u LEFT JOIN $wpdb->ap_activity a ON u.ID = a.user_id WHERE question_id = %d ", $question_id );
	}

	$key = md5( $query );
	$cache = wp_cache_get( $key, 'ap_participants' );

	if ( false !== $cache ) {
		return $cache;
	}

	$rows = $wpdb->get_results( $query );
	wp_cache_set( $key, $rows, 'ap_participants' );

	return $rows;
}
