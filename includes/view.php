<?php
/**
 * AnsPress.
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
 * Views hooks
 */
class AP_Views {
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 * @since 2.4.8 Removed `$ap` args.
	 */
	public function __construct( ) {
		anspress()->add_action( 'template_redirect', $this, 'insert_views' );
	}

	/**
	 * Insert view count on loading single question page.
	 * @param  string $template Template name.
	 */
	public function insert_views($template) {
		// Log current time as user meta, so later we can check when user was active.
		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), '__last_active', current_time('mysql' ) );
		}

		if ( is_question() ) {
			ap_insert_views(get_question_id(), 'question' );
		}

		if ( is_ap_user() && ap_get_displayed_user_id() != get_current_user_id() && ap_get_displayed_user_id() ) {
			ap_insert_views(ap_get_displayed_user_id(), 'profile' );
		}
	}
}

/**
 * Insert view data in ap_meta table and update post meta ANSPRESS_VIEW_META
 * @param  integer $data_id
 * @param  string  $type
 * @return boolean
 */
function ap_insert_views($data_id, $type) {
	if ( $type == 'question' ) {

		$userid = get_current_user_id();

		// log in DB only if not viewed before and not anonymous
		if ( ! ap_is_already_viewed(get_current_user_id(), $data_id ) && $userid != 0 ) {

			/**
			 * FILTER: ap_log_ip_view
			 * Toggle ip logging for view count
			 * @var boolean
			 */
			$log_ip = apply_filters( 'ap_log_ip_view', true );

			$ip = $log_ip ? $_SERVER['REMOTE_ADDR'] : '';

			ap_add_meta($userid, 'post_view', $data_id, $ip );
		}

		$view = ap_get_qa_views($data_id );

		$view = $view + 1;

		update_post_meta( $data_id, ANSPRESS_VIEW_META, apply_filters('ap_insert_views', $view ) );

		do_action('after_insert_views', $data_id, $view );

		return true;

	} elseif ( $type == 'profile' ) {

		$userid = get_current_user_id();

		// log in DB only if not viewed before and not anonymous
		if ( ! ap_is_already_viewed(get_current_user_id(), $data_id, 'profile_view' ) && $userid != 0 ) {
			ap_add_meta($userid, 'profile_view', $data_id, $_SERVER['REMOTE_ADDR'] ); }

		$view = ap_get_profile_views($data_id );

		$view = $view + 1;

		update_user_meta( $data_id, '__profile_views', apply_filters('ap_insert_views', $view ) );

		do_action('after_insert_views', $data_id, $view );

		return true;
	}
	return false;
}

/**
 * Get question or answer view count from meta.
 * @param  boolean|integer $id Post ID.
 * @return integer
 */
function ap_get_qa_views( $id = false ) {
	if ( false === $id ) {
		$id = get_the_ID();
	}

	$views = (int) get_post_meta( $id, ANSPRESS_VIEW_META, true );
	$views = empty($views ) ? 1 : $views;

	/**
	 * Filter post view count.
	 * @param integer $views Original view count.
	 * @since unknown
	 */
	$views = apply_filters('ap_post_views', $views );

	return (int) $views;
}

/**
 * Get total view of user profile.
 * @param  boolean|integer $user_id User ID.
 * @return integer
 */
function ap_get_profile_views( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = ap_get_displayed_user_id();
	}

	$views = (int) get_user_meta( $user_id, '__profile_views', true );
	$views = empty($views ) ? 1 : $views;

	/**
	 * Filter profile view count.
	 * @param integer Original view count.
	 * @since 2.4
	 */
	$views = apply_filters('ap_profile_views', $views );

	return $views;
}

/**
 * Get total view count from ap meta table.
 * @param integer $id Post or user Id.
 * @return integer
 */
function ap_get_views_db($id) {
	return ap_meta_total_count('post_view', $id );
}

/**
 * Check if user already viewd post or user profile.
 * @param integer $data_id Data ID.
 * @return boolean
 */
function ap_is_already_viewed( $user_id, $data_id, $type ='post_view' ) {
	$ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
	$done = ap_meta_user_done( $type, $user_id, $data_id, false, $ip );
	return $done > 0 ? true : false;
}
