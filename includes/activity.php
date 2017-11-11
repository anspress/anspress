<?php
/**
 * AnsPress activity helper functions.
 *
 * @package      AnsPress
 * @subpackage   Activity
 * @copyright    Copyright (c) 2013, Rahul Aryan
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-3.0+
 * @since        4.1.2
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Get the global AnsPress activity instance.
 *
 * @return Object Return instance of @see AnsPress\Activity().
 * @since 4.1.2
 */
function ap_activity_object() {
	if ( ! anspress()->activity ) {
		anspress()->activity = AnsPress\Activity::get_instance();
	}

	return anspress()->activity;
}

/**
 * Insert activity into database. This function is an alias  of @see AnsPress\Activity::insert().
 *
 * @param integer       $post_id Activity question id.
 * @param integer       $action  Activity action id.
 * @param integer       $ref_id  Reference item id.
 * @param integer|false $user_id User id for this activity. Default value is current_user_id().
 * @param integer|false $date    Timestamp of activity, default value `false`.
 * @return boolean|integer Returns last inserted id or `false` on fail.
 *
 * @since 4.1.2 Introduced
 */
function ap_activity_for_post( $post_id, $action, $ref_id, $user_id = false, $date = false ) {
	$activity_id = ap_activity_object()->insert( $action, $ref_id, $user_id, $date );

	// If not error.
	if ( false !== $activity_id && ! is_wp_error( $activity_id ) ) {
		$post = ap_get_post( $post_id );

		$added_relation = ap_activity_object()->add_relation( $activity_id, $post_id );

		// If answer post then add question as relation too.
		if ( 'answer' === $post->post_type ) {
			ap_activity_object()->add_relation( $activity_id, $post->post_parent );
		}

		if ( false !== $added_relation ) {
			return $activity_id;
		}
	}

	return $activity_id;
}
