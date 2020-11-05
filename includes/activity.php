<?php
/**
 * AnsPress activity helper functions.
 *
 * @package      AnsPress
 * @subpackage   Activity
 * @copyright    Copyright (c) 2013, Rahul Aryan
 * @author       Rahul Aryan <rah12@live.com>
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

/**
 * Parse raw activity returned from database. Rename column name
 * append action data.
 *
 * @param object $activity Activity object returned from database.
 * @return object|false
 * @since 4.1.2
 */
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

	// Append actions data if exists.
	if ( ap_activity_object()->action_exists( $new->action ) ) {
		$new->action = ap_activity_object()->get_action( $new->action );
	}

	return $new;
}

/**
 * Return recent activity of question or answer.
 *
 * @param Wp_Post|integer|false $_post       WordPress post object or false for global post.
 * @param null                  $deprecated  Deprecated.
 * @return object|false Return parsed activity on success else false.
 * @since 4.1.2
 * @since 4.1.8 Deprecated argument `$get_cached`.
 */
function ap_get_recent_activity( $_post = false, $deprecated = null ) {
	if ( null !== $deprecated ) {
		_deprecated_argument( __FUNCTION__, '4.1.8' );
	}

	global $wpdb;
	$_post = ap_get_post( $_post );

	// Return if not anspress posts.
	if ( ! ap_is_cpt( $_post ) ) {
		return;
	}

	$type     = $_post->post_type;
	$column   = 'answer' === $type ? 'a_id' : 'q_id';

	$q_where = '';

	if ( 'q_id' === $column && is_question() ) {
		$q_where = " AND (activity_a_id = 0 OR activity_action IN('new_a', 'unselected','selected') )";
	}

	$activity = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->ap_activity} WHERE activity_{$column} = %d$q_where ORDER BY activity_date DESC LIMIT 1", $_post->ID ) );

	// Parse.
	if ( $activity ) {
		$activity = ap_activity_parse( $activity );
	}

	return $activity;
}

/**
 * Output recent activities of a post.
 *
 * @param Wp_Post|integer|null $_post WordPress post object or null for global post.
 * @param boolean              $echo  Echo or return. Default is `echo`.
 * @param boolean              $query_db  Get rows from database. Default is `false`.
 * @return void|string
 */
function ap_recent_activity( $_post = null, $echo = true, $query_db = null ) {
	$html     = '';
	$_post    = ap_get_post( $_post );
	$activity = ap_get_recent_activity( $_post );

	if ( $activity ) {
		$html .= '<span class="ap-post-history">';
		$html .= '<a href="' . ap_user_link( $activity->user_id ) . '" itemprop="author" itemscope itemtype="http://schema.org/Person"><span itemprop="name">' . ap_user_display_name( $activity->user_id ) . '</span></a>';
		$html .= ' ' . esc_html( $activity->action['verb'] );

		if ( 'answer' === $activity->action['ref_type'] ) {
			$link = ap_get_short_link( [ 'ap_a' => $activity->a_id ] );
		} elseif ( 'comment' === $activity->action['ref_type'] ) {
			$link = ap_get_short_link( [ 'ap_c' => $activity->c_id ] );
		} else {
			$link = ap_get_short_link( [ 'ap_q' => $activity->q_id ] );
		}

		$html .= ' <a href="' . esc_url( $link ) . '">';
		$html .= '<time itemprop="dateModified" datetime="' . mysql2date( 'c', $activity->date ) . '">' . ap_human_time( $activity->date, false ) . '</time>';
		$html .= '</a>';
		$html .= '</span>';
	} else {
		// Fallback to old activities.
		$html = ap_latest_post_activity_html( $post_id = false, ! is_question() );
	}

	/**
	 * Filter recent post activity html.
	 *
	 * @param string $html HTML wrapped activity.
	 * @since 4.1.2
	 */
	$html = apply_filters( 'ap_recent_activity', $html );

	if ( false === $echo ) {
		return $html;
	}

	echo $html;
}

/**
 * Prefetch activities of posts.
 *
 * @param array  $ids Array of post ids.
 * @param string $col Column.
 * @return object|false
 * @since 4.1.2
 */
function ap_prefetch_recent_activities( $ids, $col = 'q_id' ) {
	global $wpdb;

	$ids_string = esc_sql( sanitize_comma_delimited( $ids ) );
	$col        = 'q_id' === $col ? 'q_id' : 'a_id';

	if ( empty( $ids_string ) ) {
		return;
	}

	$q_where = '';

	if ( 'q_id' === $col && is_question() ) {
		$q_where = " AND (activity_a_id = 0 OR activity_action IN('new_a', 'unselected','selected') )";
	}

	$query = "SELECT t1.* FROM {$wpdb->ap_activity} t1 NATURAL JOIN (SELECT max(activity_date) AS activity_date FROM {$wpdb->ap_activity} WHERE activity_{$col} IN({$ids_string})$q_where GROUP BY activity_{$col}) t2 ORDER BY t2.activity_date";

	$activity = $wpdb->get_results( $query );

	foreach ( $activity as $a ) {
		$a = ap_activity_parse( $a );
	}

	return $activity;
}
