<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http:/anspress.io
 * @copyright 2014 Rahul Aryan
 */

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php'; }

/**
 * Check if given anser/post is selected as a best answer
 * You should use ap_answer_is_best instead
 *
 * @param  false|integer $post_id
 * @return boolean
 * @since unknown
 * @deprecated 2.1
 */
function ap_is_best_answer($post_id = false) {
	_deprecated_function( 'ap_is_best_answer', '2.1', 'ap_answer_is_best' );
	if ( $post_id === false ) {
		$post_id = get_the_ID(); }

	$meta = get_post_meta( $post_id, ANSPRESS_BEST_META, true );
	if ( $meta ) { return true; }

	return false;
}

/**
 * Check if answer is selected for given question
 * @param  false|integer $question_id
 * @return boolean
 */
function ap_is_answer_selected($question_id = false) {
	_deprecated_function( 'ap_is_answer_selected', '2.1', 'ap_question_best_answer_selected' );
	if ( $question_id === false ) {
		$question_id = get_the_ID(); }

	$meta = get_post_meta( $question_id, ANSPRESS_SELECTED_META, true );

	if ( ! $meta ) {
		return false; }

	return true;
}

function ap_have_ans($id) {
	_deprecated_function( 'ap_have_ans', '2.1', 'ap_have_answers' );
	if ( ap_count_all_answers( $id ) > 0 ) {
		return true; }

	return false;
}

function ap_post_subscribers_count($post_id) {
	_deprecated_function( 'ap_post_subscribers_count', '2.2.0.1', 'ap_subscribers_count' );
	$post_id = $post_id ? $post_id : get_question_id();
	return ap_meta_total_count( 'subscriber', $post_id );
}

function ap_questions_tab($current_url = '') {
	_deprecated_function( 'ap_questions_tab', '2.3', 'ap_question_sorting' );

	ap_question_sorting( $current_url );
}

/**
 * Insert history to database
 * @param  boolean|integer $userid     If not set current user_id will be used.
 * @param  integer         $post_id    Post ID.
 * @param  string          $value      Meta value.
 * @param  string          $param      Meta params.
 * @return false|integer
 * @deprecated 2.4 This function is replaced by @see ap_new_activity()
 */
function ap_add_history($userid = false, $post_id, $value, $param = null) {
	_deprecated_function( 'ap_add_history', '2.4', 'ap_new_activity' );
}

/**
 * Get last active time
 * @param  integer $post_id Question or answer ID.
 * @since  2.0.1
 * @deprecated 2.4 This function is replaced by @see ap_post_active_time()
 */
function ap_last_active_time($post_id = false, $html = true) {
	_deprecated_function( 'ap_last_active_time', '2.4', 'ap_post_active_time' );
}

/**
 * Convert history slug to proper titles.
 * @param  string $slug History slug.
 * @param  string $parm Extra parameters.
 * @deprecated 2.4 This function is replaced by @see ap_activity_short_title()
 */
function ap_history_title( $slug, $parm = '') {
	_deprecated_function( 'ap_history_title', '2.4', 'ap_activity_short_title' );
}

/**
 * Get HTML formatted latest history of post.
 * @param  integer $post_id Question or answer ID.
 * @param  boolean $initial Show created time if no history found.
 * @param  boolean $avatar  Show avatar?
 * @param  boolean $icon    Show Icon?
 * @deprecated 2.4 This function is replaced by @see ap_latest_post_activity_html()
 */
function ap_get_latest_history_html($post_id, $initial = false, $avatar = false, $icon = false) {
	_deprecated_function( 'ap_get_latest_history_html', '2.4', 'ap_latest_post_activity_html' );
}

/**
 * Get the latest history html
 * @param  integer $post_id Post ID.
 * @deprecated 2.4 This function is deprecated.
 */
function ap_get_latest_history($post_id) {
	_deprecated_function( 'ap_get_latest_history', '2.4' );
}

/**
 * Delete history meta and post history meta
 * @param  boolean|integer $user_id     If not set current user_id will be used.
 * @param  integer         $action_id  Action ID.
 * @param  string          $value      Meta value.
 * @param  string          $param      Meta params.
 * @deprecated 2.4 This function is deprecated.
 */
function ap_delete_history($user_id, $action_id, $value, $param = null) {
	_deprecated_function( 'ap_delete_history', '2.4', 'ap_delete_activity' );
}
