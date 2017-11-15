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
 * @return Object Return instance of @see AnsPress\Activity_Helper().
 * @since 4.1.2
 */
function ap_activity_object() {
	if ( ! anspress()->activity ) {
		anspress()->activity = AnsPress\Activity_Helper::get_instance();
	}

	return anspress()->activity;
}

/**
 * Insert activity into database. This function is an alias  of @see AnsPress\Activity_Helper::insert().
 *
 * @param array $args Arguments for insert. All list of arguments can be seen at @see AnsPress\Activity_Helper::insert().
 * @return WP_Error|integer Returns last inserted id or `WP_Error` on fail.
 *
 * @since 4.1.2 Introduced
 */
function ap_activity_add( $args = [] ) {
	return ap_activity_object()->insert( $args );
}

/**
 * Delete all activities related to a post.
 *
 * If given post is a question then it delete all activities by column `activity_q_id` else
 * by `activity_a_id`. More detail about activity delete can be found here @see AnsPress\Activity_Helper::delete()
 *
 * @param  WP_Post|integer $post_id WordPress post object or post ID.
 * @return WP_Error|integer Return numbers of rows deleted on success.
 * @since 4.1.2
 */
function ap_delete_post_activity( $post_id ) {
	$_post = ap_get_post( $post_id );

	// Check if AnsPress posts.
	if ( ! ap_is_cpt( $_post ) ) {
		return new WP_Error( 'not_cpt', __( 'Not AnsPress posts', 'anspress-question-answer' ) );
	}

	$where = [];

	if ( 'question' === $_post->post_type ) {
		$where['q_id'] = $_post->ID;
	} else {
		$where['a_id'] = $_post->ID;
	}

	// Delete all activities by post id.
	return ap_activity_object()->delete( $where );
}

/**
 * Delete all activities related to a comment.
 *
 * More detail about activity delete can be found here @see AnsPress\Activity_Helper::delete()
 *
 * @param Comment|integer $comment_id WordPress comment object or comment ID.
 * @return WP_Error|integer Return numbers of rows deleted on success.
 * @since  4.1.2
 */
function ap_delete_comment_activity( $comment_id ) {
	if ( 'anspress' !== get_comment_type( $comment_id ) ) {
		return;
	}

	// Delete all activities by post id.
	return ap_activity_object()->delete( [ 'c_id' => $comment_id ] );
}

/**
 * Delete all activities related to a user.
 *
 * More detail about activity delete can be found here @see AnsPress\Activity_Helper::delete()
 *
 * @param User|integer $user_id WordPress user object or user ID.
 * @return WP_Error|integer Return numbers of rows deleted on success.
 * @since  4.1.2
 */
function ap_delete_user_activity( $user_id ) {
	// Delete all activities by post id.
	return ap_activity_object()->delete( [ 'user_id' => $user_id ] );
}

function ap_activity_parse( $activity ) {
	if ( ! is_object( $activity ) ) {
		return false;
	}

	$new = [];

	// Rename keys.
	foreach ( $activity as $key => $value ) {
		$new[ str_replace( 'activity_', '', $key ) ] = $value;
	}

	$new = (object) $new;

	if ( ap_activity_object()->action_exists( $new->action ) ) {
		$new->action = ap_activity_object()->get_action( $new->action );
	}

	return $new;
}
