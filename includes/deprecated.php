<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
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

/**
 * Insert notification in ap_meta table
 *
 * @param  integer       $current_user_id    User id of user triggering this hook.
 * @param  integer       $affected_user_id   User id of user who is being affected.
 * @param  string        $notification_type  Type of notification.
 * @param  boolean|array $args               arguments for the notification.
 * @deprecated 2.4 This function is deprecated and replaced by ap_new_notification.
 */
function ap_insert_notification( $current_user_id, $affected_user_id, $notification_type, $args = false) {
	_deprecated_function( 'ap_insert_notification', '2.4', 'ap_new_notification' );
}

/**
 * Insert subscriber for question or term
 * @param  integer         $user_id    WP user ID
 * @param  integer         $action_id  Question ID or Term ID
 * @param  boolean|integer $sub_id     Any sub ID
 * @param  boolean|integer $type       Type of subscriber, empty string for question
 * @deprecated 2.4 This function is deprecated and replaced by ap_new_subscriber.
 */
function ap_add_subscriber($user_id, $action_id, $type = false, $sub_id = false) {
	_deprecated_function( 'ap_add_subscriber', '2.4', 'ap_new_subscriber' );
}

/**
 * Subscribe a question
 * @param  integer         $question_id
 * @param  boolean|integer $user_id
 * @deprecated 2.4 This function is deprecated.
 */
function ap_add_question_subscriber($question_id, $user_id = false, $type = '', $secondary_id = '') {
	_deprecated_function( 'ap_add_question_subscriber', '2.4' );
}

/**
 * Unscubscribe user from a question
 * @param  integer         $question_id Questions ID
 * @param  boolean|integer $user_id
 * @deprecated 2.4 This function is deprecated.
 */
function ap_remove_question_subscriber($question_id, $user_id = false) {
	_deprecated_function( 'ap_remove_question_subscriber', '2.4' );
}

/**
 * Check if there are notifications in loop
 * @return boolean|null
 */
function ap_has_notifications() {
	_deprecated_function( 'ap_has_notifications', '2.4' );
}

function ap_notifications() {
	_deprecated_function( 'ap_notifications', '2.4' );
}

function ap_the_notification() {
	_deprecated_function( 'ap_the_notification', '2.4' );
}

function ap_notification_object() {
	_deprecated_function( 'ap_notification_object', '2.4' );
}

/**
 * Output notification id.
 */
function ap_notification_the_id() {
	_deprecated_function( 'ap_notification_the_id', '2.4' );
}

/**
 * Get notification id
 * @return integer
 */
function ap_notification_id() {
	_deprecated_function( 'ap_notification_id', '2.4' );
}

function ap_notification_the_permalink() {
	_deprecated_function( 'ap_notification_the_permalink', '2.4' );
}

function ap_notification_permalink() {
	_deprecated_function( 'ap_notification_permalink', '2.4' );
}

function ap_notification_the_type() {
	_deprecated_function( 'ap_notification_the_type', '2.4' );
}

function ap_notification_type() {
	_deprecated_function( 'ap_notification_type', '2.4' );
}

function ap_notification_the_content() {
	_deprecated_function( 'ap_notification_the_content', '2.4' );
}

function ap_notification_content() {
	_deprecated_function( 'ap_notification_content', '2.4' );
}

/**
 * Return notification title
 * @param  string $notification   notification type
 * @deprecated 2.4 This function is deprecated.
 * @since  2.3
 */
function ap_get_notification_title($notification, $args) {
	_deprecated_function( 'ap_get_notification_title', '2.4' );
}

/**
 * Output notification date
 */
function ap_notification_the_date() {
	_deprecated_function( 'ap_notification_the_date', '2.4' );
}

/**
 * Return notification date.
 * @return string
 */
function ap_notification_date() {
	_deprecated_function( 'ap_notification_date', '2.4' );
}

function ap_notification_the_icon() {
	_deprecated_function( 'ap_notification_the_icon', '2.4' );
}
function ap_notification_icon() {
	_deprecated_function( 'ap_notification_icon', '2.4' );
}

function ap_notification_pagination() {
	_deprecated_function( 'ap_notification_pagination', '2.4' );
}

/**
 * Restore __ap_history meta of question or answer
 * @param  integer $post_id
 * @deprecated since 2.4
 */
function ap_restore_question_history($post_id) {
	_deprecated_function( 'ap_restore_question_history', '2.4' );
}

/**
 * Remove new answer history from ap_meta table and update post meta history
 * @param  integer $answer_id
 * @deprecated since 2.4
 */
function ap_remove_new_answer_history($answer_id) {
	_deprecated_function( 'ap_remove_new_answer_history', '2.4' );
}

function ap_qa_on_post(){
	_deprecated_function( 'ap_qa_on_post', '2.4' );
}

/**
 * Check if a user can edit answer on a question
 * @param  integer $post_id Answer id.
 * @return boolean
 * @deprecated since 2.4.7
 */
function ap_user_can_edit_ans( $post_id ) {
	_deprecated_function( 'ap_user_can_edit_ans', '2.4.6', 'ap_user_can_edit_answer' );
	return ap_user_can_edit_answer( $post_id );
}

/**
 * Check if user can delete AnsPress posts
 * @param  integer $post_id Question or answer ID.
 * @return boolean
 * @deprecated since 2.4.7
 */
function ap_user_can_delete( $post_id ){
	_deprecated_function( __FUNCTION__, '2.4.7', 'ap_user_can_delete_post' );
	return ap_user_can_delete_post( $post );
}

/**
 * Output question list sorting dropdown.
 * @param string $current_url current page url.
 * @since 2.3
 * @deprecated since 3.0.0
 */
function ap_question_sorting($current_url = '') {
	_deprecated_function( __FUNCTION__, '3.0.0' );
}