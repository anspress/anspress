<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php';
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

function ap_qa_on_post() {
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
function ap_user_can_delete( $post_id ) {
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


/**
 * Question meta to display.
 *
 * @param false|integer $question_id question id.
 * @return string
 * @since 2.0.1
 * @deprecated 4.0.0 Replaced by `ap_question_metas`.
 */
function ap_display_question_metas( $question_id = false ) {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_question_metas' );
	return ap_question_metas( $question_id );
}

/**
 * Echo active question ID
 * @since 2.1
 * @deprecated 4.0.0 Replaced by `the_ID`.
 */
function ap_question_the_ID() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'the_ID' );
	echo ap_question_get_the_ID();
}

/**
 * Return question ID active in loop
 * @return integer|false
 * @since 2.1
 * @deprecated 4.0.0 Replaced by `get_the_ID`.
 */
function ap_question_get_the_ID() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'get_the_ID' );
	return ap_question_the_object()->ID;

	return false;
}

/**
 * Output active question vote button
 * @return 2.1
 * @deprecated 4.0.0
 */
function ap_question_the_vote_button() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'Use ap_vote_btn' );
	ap_vote_btn( ap_question_the_object() );
}

function ap_question_the_active_time($question_id = false) {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_recent_post_activity' );
	echo ap_question_get_the_active_time();
}

function ap_question_get_the_active_time($question_id = false) {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_get_recent_post_activity' );
	$question_id = ap_parameter_empty( $question_id, @get_the_ID() );
	return ap_latest_post_activity_html( $question_id );
}

/**
 * Get active question post status
 * @return void
 * @since 2.1
 */
function ap_question_the_status() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_status' );

	if ( ap_question_the_object()->post_status == 'private_post' ) {
		echo '<span class="ap-post-type private ap-notice gray">'.__( 'Private', 'anspress-question-answer' ).'</span>';
	} elseif ( ap_question_the_object()->post_status == 'moderate' ) {
		echo '<span class="ap-post-type moderate ap-notice yellow">'.__( 'Moderate', 'anspress-question-answer' ).'</span>';
	} elseif ( ap_question_the_object()->post_status == 'closed' ) {
		echo '<span class="ap-post-type closed ap-notice red">'.__( 'Closed', 'anspress-question-answer' ).'</span>';
	}
}

/**
 * Echo active question permalink
 * @return void
 * @since 2.1
 */
function ap_question_the_permalink() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'Use WP inbuilt function the_permalink()' );
	echo ap_question_get_the_permalink();
}

/**
 * Return active question permalink
 * @return string
 * @since 2.1
 */
function ap_question_get_the_permalink() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'Use WP inbuilt function get_the_permalink()' );
	return get_the_permalink( get_the_ID() );
}

/**
 * Return active question answer count
 * @return integer
 * @since 2.1
 */
function ap_question_get_the_answer_count() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_get_answer_count' );
	return ap_get_answers_count( get_the_ID() );
}

function ap_question_the_answer_count() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_answer_count' );
	$count = ap_question_get_the_answer_count();
	echo '<a class="ap-questions-count ap-questions-acount" href="'.ap_answers_link().'">'. sprintf( _n( '%s ans', '%s ans', $count, 'anspress-question-answer' ), '<span>'.$count.'</span>' ).'</a>';
}

/**
 * Return question author avatar
 * @param  integer $size
 * @return string
 * @since 2.1
 */
function ap_question_get_the_author_avatar($size = 45) {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_get_author_avatar' );
	return get_avatar( ap_question_get_author_id(), $size );
}

function ap_question_the_author_avatar($size = 45) {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_author_avatar' );
	echo ap_question_get_the_author_avatar( $size );
}

/**
 * Return the author profile link
 * @return string
 * @since 2.1
 */
function ap_question_get_the_author_link() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_profile_link' );
	return ap_user_link( ap_question_get_author_id() );
}

/**
 * Echo active question total vote
 * @return void
 * @since 2.1
 */
function ap_question_the_net_vote() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_votes_net' );
	if ( ! ap_opt( 'disable_voting_on_question' ) ) {
		?>
            <span class="ap-questions-count ap-questions-vcount">
                <span><?php echo ap_question_get_the_net_vote(); ?></span>
                <?php  _e( 'votes', 'anspress-question-answer' ); ?>
            </span>
        <?php
	}
}

/**
 * Return count of net vote of a question
 * @return integer
 * @since 2.1
 */
function ap_question_get_the_net_vote() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_get_votes_net' );
	return ap_net_vote( ap_question_the_object() );
}

/**
 * echo user profile link
 * @return 2.1
 */
function ap_question_the_author_link() {
	_deprecated_function( __FUNCTION__, '4.0.0',  'ap_profile_link' );
	echo ap_user_link();
}

function ap_question_get_the_author_id() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	echo ap_question_get_author_id();
}

function ap_question_get_author_id() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	$question = ap_question_the_object();
	return $question->post_author;
}

/**
 * echo current question post_parent
 * @since 2.1
 */
function ap_question_the_post_parent() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	echo ap_question_get_the_post_parent();
}

/**
 * Returns the question post parent ID
 * @return integer
 * @since 2.1
 */
function ap_question_get_the_post_parent() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	$question = ap_question_the_object();

	return $question->post_parent;
}

/**
 * Echo time current question was active
 * @return void
 * @since 2.1
 */
function ap_question_the_active_ago() {
	_deprecated_function( __FUNCTION__, '4.0.0', 'ap_last_active' );
	echo ap_human_time( ap_question_get_the_active_ago(), false );
}

/**
 * Return the question active ago time
 * @return string
 * @since 2.1
 */
function ap_question_get_the_active_ago() {
	_deprecated_function( __FUNCTION__, '4.0.0', 'ap_get_last_active' );
	return ap_last_active( get_the_ID() );
}

/**
 * Echo view count for current question
 * @since 2.1
 */
function ap_question_the_view_count() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	echo ap_question_get_the_view_count();
}

/**
 * Return total view count
 * @return integer
 * @since 2.1
 */
function ap_question_get_the_view_count() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	return ap_get_qa_views( get_the_ID() );
}

/**
 * Echo questions subscriber count
 * @since 2.1
 */
function ap_question_the_subscriber_count() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	echo ap_question_get_the_subscriber_count();
}

/**
 * Return the subscriber count for active question
 * @return integer
 * @since 2.1
 */
function ap_question_get_the_subscriber_count() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	return ap_subscribers_count( get_the_ID() );
}

/**
 * Check if best answer is selected for question.
 * @param  false|integer $question_id
 * @return boolean
 */
function ap_question_best_answer_selected($question_id = false) {
	_deprecated_function( __FUNCTION__, '4.0.0', 'ap_have_answer_selected' );
	if ( false === $question_id ) {
		$question_id = get_the_ID();
	}

	// Get question post meta.
	$meta = get_post_meta( $question_id, ANSPRESS_SELECTED_META, true );

	if ( ! $meta ) {
		return false;
	}

	return true;
}

/**
 * Output question created time.
 * @param  boolean|integer $question_id Question ID.
 * @param  string          $format      Format of time.
 */
function ap_question_the_time($question_id = false, $format = 'U') {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

function ap_question_get_the_time($question_id = false, $format = '') {
	_deprecated_function( __FUNCTION__, '4.0.0', 'ap_get_time' );
	$question_id = ap_parameter_empty( $question_id, @get_the_ID() );
	return get_post_time( $format, true, $question_id, true );
}

function ap_question_the_object() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	global $questions, $post;

	if ( $questions ) {
		return $questions->post;
	}

	return $post;
}

function ap_question_the_time_relative($question_id = false) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	echo ap_question_get_the_time_relative( $question_id );
}

function ap_question_get_the_time_relative($question_id = false) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	$question_id = ap_parameter_empty( $question_id, @get_the_ID() );
	return ap_question_get_the_time( $question_id, 'U' );
}

/**
 * Count total vote count of a post.
 * @param  boolean $post_id Post id.
 * @return integer
 */
function ap_net_vote_meta( $post_id = false ) {
	_deprecated_function( __FUNCTION__, '4.0.0', 'ap_get_votes_net' );
}

// get $post up votes
function ap_up_vote($echo = false) {
	_deprecated_function( __FUNCTION__, '4.0.0' );

	global $post;

	if ( $echo ) {
		echo $post->voted_up;
	} else {
		return $post->voted_up;
	}
}

// get $post down votes
function ap_down_vote($echo = false) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	
	global $post;

	if ( $echo ) {
		echo $post->voted_down;
	} else {
		return $post->voted_down;
	}
}

/**
 * Get net vote count of a post.
 * @param  int|object $post Post object or ID.
 * @return int
 */
function ap_net_vote( $post = false ) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

function ap_post_votes($post_id) {
	_deprecated_function( __FUNCTION__, '4.0.0', 'ap_meta_post_votes' );
	return ap_meta_post_votes($post_id);
}

/**
 * Remove vote for post and also update post meta.
 * @param  integer $current_userid    User ID of user casting the vote.
 * @param  string  $type              Type of vote, "vote_up" or "vote_down".
 * @param  integer $actionid          Post ID.
 * @param  integer $receiving_userid  User ID of user receiving the vote.
 * @return array|false
 * @since  2.5
 */
function ap_remove_post_vote( $type, $current_userid, $actionid, $receiving_userid ) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
	$row = ap_remove_vote($type, $current_userid, $actionid, $receiving_userid );

	if ( false !== $row ) {
		return ap_update_votes_count( $actionid );
	}

	return false;
}

/**
 * Get question subscribers count from post meta.
 * @param  intgere|object $question Question object.
 * @return integer
 */
function ap_question_subscriber_count( $question ) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Echo active answer id
 * @return void
 * @since 2.1
 */
function ap_answer_the_answer_id() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Get the active answer id
 * @return integer
 * @since 2.1
 */
function ap_answer_get_the_answer_id() {
	_deprecated_function( __FUNCTION__, '4.0.0' );

}

/**
 * Echo active answer question id
 * @return void
 * @since 2.1
 */
function ap_answer_the_question_id() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Get the active answer question id
 * @return integer
 * @since 2.1
 */
function ap_answer_get_the_question_id() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Check if current answer is selected as a best
 * @param integer|boolean $answer_id Answer ID.
 * @return boolean
 * @since 2.1
 */
function ap_answer_is_best($answer_id = false) {
	_deprecated_function( __FUNCTION__, '4.0.0', 'ap_is_selected' );
}
/**
 * Return the author profile link
 * @return string
 * @since 2.1
 */
function ap_answer_get_the_author_link() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}
/**
 * Return answer author avatar
 * @param  integer $size Avatar size.
 * @return string
 * @since 2.1
 */
function ap_answer_get_the_author_avatar($size = 45) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Output current answer author avatar
 * @param  boolean|integer $size Size of avatar.
 */
function ap_answer_the_author_avatar($size = false) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Echo user profile link
 * @since 2.1
 */
function ap_answer_the_author_link() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

function ap_answer_get_author_id() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Output active answer vote button
 * @since 2.1
 */
function ap_answer_the_vote_button() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Echo time current answer was active
 * @return void
 * @since 2.1
 */
function ap_answer_the_active_ago() {
	_deprecated_function( __FUNCTION__, '4.0.0', 'ap_last_active' );
}

/**
 * Return the answer active ago time
 * @return string
 * @since 2.1
 */
function ap_answer_get_the_active_ago() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Echo active answer permalink
 * @return void
 * @since 2.1
 */
function ap_answer_the_permalink() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Return active answer permalink
 * @return string
 * @since 2.1
 */
function ap_answer_get_the_permalink() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Echo active answer total vote
 * @return void
 * @since 2.1
 */
function ap_answer_the_net_vote() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Return count of net vote of a answer
 * @return integer
 * @since 2.1
 */
function ap_answer_get_the_net_vote() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

function ap_answer_the_vote_class() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Get vote class of active answer
 * @return string
 */
function ap_answer_get_the_vote_class() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}
/**
 * Output answer active time
 * @param  boolean|integer $answer_id Answer ID.
 */
function ap_answer_the_active_time($answer_id = false) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Return last active time of answer
 * @param  boolean|integer $answer_id Answer ID.
 * @return string
 */
function ap_answer_get_the_active_time($answer_id = false) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Output answer time in human readable format
 * @param  boolean|integer $answer_id      If outside of loop, post ID can be passed.
 * @param  integer         $format         WP time format.
 * @return void
 */
function ap_answer_the_time($answer_id = false, $format = 'U') {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Return answer time
 * @param  boolean|integer $answer_id      If outside of loop, post ID can be passed.
 * @param  integer         $format         WP time format.
 * @return string
 */
function ap_answer_get_the_time($answer_id = false, $format = '') {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Output count of total numbers of Answers
 * @since 2.1
 */
function ap_answer_the_count() {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Return the count of total numbers of Answers
 * @return integer
 * @since 2.1
 */
function ap_answer_get_the_count() {
	_deprecated_function( __FUNCTION__, '4.0.0', 'ap_total_posts_found' );
}

function ap_count_answer_meta($post_id = false) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Return flag count of question and answer from meta.
 * @param  integer $post_id Question/Answer Id.
 * @return integer
 */
function ap_flagged_post_meta( $post_id ) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Count post flag votes.
 * @param integer $postid
 * @return int
 */
function ap_post_flag_count($postid = false) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Add vote meta.
 *
 * @param int    $current_userid    User ID of user casting the vote
 * @param string $type              Type of vote, "vote_up" or "vote_down"
 * @param int    $actionid          Post ID
 * @param int    $receiving_userid User ID of user receiving the vote. @since 2.3
 *
 * @return integer
 */
function ap_add_vote( $current_userid, $type, $actionid, $receiving_userid, $count = 1 ) {
	_deprecated_function( __FUNCTION__, '4.0.0' );
}

/**
 * Count post vote count meta.
 * @param  integer $post_id Post id.
 * @return integer
 */
function ap_meta_post_votes($post_id) {
	_deprecated_function( __FUNCTION__, '4.0.0', 'ap_count_votes' );
}