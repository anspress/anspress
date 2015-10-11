<?php
/**
 * Common AnsPress admin functions
 *
 * @link http://anspress.io
 * @since unknown
 *
 * @package AnsPress
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Return number of flagged posts
 * @return object
 * @since unknown
 */
function ap_flagged_posts_count() {
	return ap_total_posts_count( 'both', 'flag' );
}


/**
 * Register anspress option tab and fields
 * @param  string $group_slug     slug for links
 * @param  string $group_title    Page title
 * @param  array  $fields         fields array.
 * @return void
 * @since 2.0.0-alpha2
 */
function ap_register_option_group($group_slug, $group_title, $fields, $form = true) {
	$fields = apply_filters( 'ap_option_group_'.$group_slug, $fields );
	ap_append_to_global_var( 'ap_option_tabs', $group_slug , array( 'title' => $group_title, 'fields' => $fields, 'form' => $form ) );
}

/**
 * Output option tab nav
 * @return void
 * @since 2.0.0-alpha2
 */
function ap_options_nav() {
	global $ap_option_tabs;
	$active = (isset( $_REQUEST['option_page'] )) ? $_REQUEST['option_page'] : 'general' ;

	$menus = array();

	foreach ( $ap_option_tabs as $k => $args ) {
		$link 		= admin_url( "admin.php?page=anspress_options&option_page={$k}" );
		$menus[$k] 	= array( 'title' => $args['title'], 'link' => $link );
	}

	/**
	 * FILTER: ap_option_tab_nav
	 * filter is applied before showing option tab navigation
	 * @var array
	 * @since  2.0.0-alpha2
	 */
	$menus = apply_filters( 'ap_option_tab_nav', $menus );

	$o = '<ul id="ap_opt_nav" class="nav nav-tabs">';
	foreach ( $menus as $k => $m ) {
		$class = ! empty( $m['class'] ) ? ' '. $m['class'] : '';
			$o .= '<li'.( $active == $k ? ' class="active"' : '' ).'><a href="'. $m['link'] .'" class="ap-user-menu-'.$k.$class.'">'.$m['title'].'</a></li>';
	}
	$o .= '</ul>';

	echo $o;
}

/**
 * Display fields group options. Uses AnsPress_Form to renders fields.
 * @return void
 * @since 2.0.0
 */
function ap_option_group_fields() {
	global $ap_option_tabs;

	$active = (isset( $_REQUEST['option_page'] )) ? sanitize_text_field( $_REQUEST['option_page'] ) : 'general' ;

	if ( empty( $ap_option_tabs ) && is_array( $ap_option_tabs ) ) {
		return;
	}

	$fields = $ap_option_tabs[$active]['fields'];

	if($ap_option_tabs[$active]['form']){

		$args = array(
			'name'              => 'options_form',
			'is_ajaxified'      => false,
			'submit_button'     => __( 'Save options', 'ap' ),
			'nonce_name'        => 'nonce_option_form',
			'fields'            => $fields,
		);

		$form = new AnsPress_Form( $args );

		echo '<div class="ap-optionform-title">';
		echo '<strong>'. $ap_option_tabs[$active]['title'] .'</strong>';
		echo '</div>';
		echo $form->get_form();

	}else{
		call_user_func($fields);
	}
}

/**
 * Update user role
 * @param  string $role_slug Role slug.
 * @param  array  $caps      Allowed caps array.
 * @return boolean
 */
function ap_update_caps_for_role($role_slug, $caps = array()){

	$role_slug = sanitize_text_field( $role_slug );

	$role = get_role( $role_slug );



	if( !$role || !is_array($caps) ){
		return false;
	}

	$ap_roles = new AP_Roles;

	$all_caps = $ap_roles->base_caps + $ap_roles->mod_caps;

	foreach($all_caps as $cap => $val){

		if( isset($caps[$cap])){
			$role->add_cap( $cap );
		}else{
			$role->remove_cap( $cap );
		}

	}

	return true;
}
