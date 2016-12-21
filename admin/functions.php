<?php
/**
 * Common AnsPress admin functions
 *
 * @link https://anspress.io
 * @since unknown
 *
 * @package AnsPress
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
 * Register anspress option tab and fields.
 *
 * @param  string  $group_slug     slug for links.
 * @param  string  $group_title    Page title.
 * @param  array   $fields         fields array.
 * @param  boolean $form           Show form.
 * @return void
 * @since 2.0.0
 */
function ap_register_option_group( $group_slug, $group_title, $fields, $form = true ) {
	global $ap_option_tabs;
	$fields = apply_filters( 'ap_option_group_' . $group_slug, $fields );
	$ap_option_tabs[ $group_slug ] = array( 'title' => $group_title, 'fields' => $fields, 'form' => $form );
}

/**
 * Output option tab nav.
 *
 * @return void
 * @since 2.0.0-alpha2
 */
function ap_options_nav() {
	$groups = ap_get_option_groups();
	$active = ap_sanitize_unslash( 'option_page', 'p' ) ? ap_sanitize_unslash( 'option_page', 'p' ) : 'general' ;
	$menus = array();
	$icons = array(
		'general'    => 'apicon-home',
		'layout'     => 'apicon-eye',
		'pages'      => 'apicon-pin',
		'question'   => 'apicon-question',
		'users'      => 'apicon-users',
		'permission' => 'apicon-lock',
		'moderate'   => 'apicon-flag',
		'roles'      => 'apicon-user',
		'categories' => 'apicon-category',
		'tags'       => 'apicon-tag',
		'labels'     => 'apicon-tag',
	);

	foreach ( (array) $groups as $k => $args ) {
		$link 		= admin_url( "admin.php?page=anspress_options&option_page={$k}" );
		$icon 		= isset( $icons[ $k ] ) ? esc_attr( $icons[ $k ] ) : 'apicon-gear';
		$menus[ $k ] 	= array( 'title' => $args['title'], 'link' => $link, 'icon' => $icon );
	}

	/**
	 * Filter is applied before showing option tab navigation.
	 *
	 * @var array
	 * @since  2.0.0
	 */
	$menus = apply_filters( 'ap_option_tab_nav', $menus );

	$o = '<h2 class="nav-tab-wrapper">';

	foreach ( (array) $menus as $k => $m ) {
		$class = ! empty( $m['class'] ) ? ' ' . $m['class'] : '';
		$o .= '<a href="' . esc_url( $m['link'] ) . '" class="nav-tab ap-user-menu-' . esc_attr( $k . $class ) . ( $active === $k ? '  nav-tab-active' : '' ) . '"><i class="' . $m['icon'] . '"></i>' . esc_attr( $m['title'] ) . '</a>';
	}

	$o .= '</h2>';

	echo $o; // xss okay.
}

/**
 * Display fields group options. Uses AnsPress_Form to renders fields.
 *
 * @since 2.0.0
 */
function ap_option_group_fields() {
	$groups = ap_get_option_groups();
	$active = ap_sanitize_unslash( 'option_page', 'request', 'general' );

	if ( empty( $groups ) && is_array( $groups ) ) {
		return;
	}

	$setions = $groups[ $active ]['fields'];

	$i = 0;
	$ap_active_section = ap_isset_post_value( 'ap_active_section', '' );

	foreach ( (array) $setions as $section_title => $fields ) {
		if ( is_array( $fields ) ) {

			$fields[] = array(
				'name' => 'fields_group',
				'type' => 'hidden',
				'value' => $active,
			);

			$fields[] = array(
				'name' => 'ap_active_section',
				'type' => 'hidden',
				'value' => $i,
			);

			$args = array(
				'name'              => 'options_form',
				'is_ajaxified'      => false,
				'submit_button'     => __( 'Save options', 'anspress-question-answer' ),
				'nonce_name'        => 'nonce_option_form',
				'fields'            => $fields,
			);

			$form = new AnsPress_Form( $args );
			echo '<div class="postbox' . ( $ap_active_section == (string) $i ? '' : ' closed' ) . '">';
			echo '<h3 data-index="' . esc_attr( $i ) . '"><span>' . esc_html( $section_title ) . '</span></h3>';
			echo '<div class="inside">';
			echo $form->get_form(); // xss okay.
			echo '</div>';
			echo '</div>';

		} elseif ( function_exists( $fields ) ) {
			echo '<div class="postbox' . ( $ap_active_section == (string) $i ? '' : ' closed' ) . '">';
			echo '<h3 data-index="' . esc_attr( $i ) . '"><span>' . esc_html( $section_title ) . '</span></h3>';
			echo '<div class="inside">';
			call_user_func( $fields );
			echo '</div>';
			echo '</div>';
		}
		$i++;
	}
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
	$role = get_role( $role_slug );

	if ( ! $role || ! is_array( $caps ) ) {
		return false;
	}

	$ap_roles = new AP_Roles;
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
 * Return all option groups.
 *
 * @return array
 * @since 3.0.0
 */
function ap_get_option_groups() {
	global $ap_option_tabs;
	do_action( 'ap_option_groups' );

	return apply_filters( 'ap_get_option_groups', $ap_option_tabs );
}

/**
 * Check if AnsPress admin assets need to be loaded.
 *
 * @return boolean
 * @since  3.0.0
 */
function ap_load_admin_assets() {
	$page = get_current_screen();
	$load = 'question' === $page->post_type || 'answer' === $page->post_type || strpos( $page->base, 'anspress' ) !== false || 'nav-menus' === $page->base || 'admin_page_ap_select_question' === $page->base;

	/**
	 * Filter ap_load_admin_assets to load admin assets in custom page.
	 *
	 * @param boolean $load Pass a boolean value if need to load assets.
	 * @return boolean
	 */
	return apply_filters( 'ap_load_admin_assets', $load );
}
