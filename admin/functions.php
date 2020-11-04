<?php
/**
 * Common AnsPress admin functions
 *
 * @link https://anspress.net
 * @package AnsPress
 * @author Rahul Aryan <rah12@live.com>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Return number of flagged posts.
 *
 * @return object
 * @since unknown
 */
function ap_flagged_posts_count() {
	return ap_total_posts_count( 'both', 'flag' );
}

/**
 * Update user role.
 *
 * @param  string $role_slug Role slug.
 * @param  array  $caps      Allowed caps array.
 * @return boolean
 */
function ap_update_caps_for_role( $role_slug, $caps = array() ) {
	$role_slug = sanitize_text_field( $role_slug );
	$role      = get_role( $role_slug );

	if ( ! $role || ! is_array( $caps ) ) {
		return false;
	}

	$ap_roles = new AP_Roles();
	$all_caps = $ap_roles->base_caps + $ap_roles->mod_caps;

	foreach ( (array) $all_caps as $cap => $val ) {
		if ( isset( $caps[ $cap ] ) ) {
			$role->add_cap( $cap );
		} else {
			$role->remove_cap( $cap );
		}
	}

	return true;
}

/**
 * Check if AnsPress admin assets need to be loaded.
 *
 * @return boolean
 * @since  3.0.0
 */
function ap_load_admin_assets() {
	$page = get_current_screen();
	$load = 'question' === $page->post_type || 'answer' === $page->post_type || strpos( $page->base, 'anspress' ) !== false || 'nav-menus' === $page->base || 'admin_page_ap_select_question' === $page->base || 'admin_page_anspress_update' === $page->base;

	/**
	 * Filter ap_load_admin_assets to load admin assets in custom page.
	 *
	 * @param boolean $load Pass a boolean value if need to load assets.
	 * @return boolean
	 */
	return apply_filters( 'ap_load_admin_assets', $load );
}

