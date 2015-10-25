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

class AP_Mentions_Hooks{
	public function __construct($ap) {
		$ap->add_filter( 'ap_form_contents_filter', $this, 'linkyfy_mentions' );
	}

	/**
	 * Linkyfy mentions before contents get inserted to database.
	 * @param  string $content Post content.
	 * @return string
	 */
	public function linkyfy_mentions($content) {
		return ap_linkyfy_mentions( $content );
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

	// Find mentions and wrap with anchor.
	return preg_replace( '/(?:[\s.]|^)@(\w+)/', '<a class="ap-mention-link" href="'.$base.'$1">@$1</a> ', $content );
}

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
