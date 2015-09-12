<?php
/**
 * AnsPress activity handler
 *
 * @package  	AnsPress
 * @license  	http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     	http://anspress.io
 * @since 		2.0
 */

/**
 * AnsPress activity handler
 */
class AnsPress_Activity {

}

/**
 * Insert and update AnsPress activity
 * If id is passed then existing activity will be updated.
 *
 * @param  array $args {
 *     Array of arguments.
 *     @type integer $id      			Activity id. If activity id is passed then activity will be updated.
 *     @type integer $user_id      		Main user ID of this activity. Default is current user.
 *     @type integer $secondary_user    Secondary user id.
 *     @type string  $secondary_user    Type of activity.
 *     @type string  $content    	    Activity content.
 *     @type integer $item_id    	    Related item id.
 *     @type integer $secondary_id	    Related secondary item id.
 *     @type string  $created	    	Created date.
 *     @type string  $updated	    	Updated date.
 * }
 * @return boolean|integer
 */
function ap_insert_activity( $args ) {
	global $wpdb;
	$user_id = get_current_user_id();

	$defaults = array(
		'user_id' => $user_id,
		'secondary_user' => 0,
		'type' => '',
		'status' => 'publish',
		'content' => '',
		'question_id' => '',
		'item_id' => '',
		'created' => '',
		'updated' => '',
	);

	$activity_id = isset( $args['id'] ) ? intval( $args['id'] ) : false;

	$args = wp_parse_args( $args, $defaults );

	$args['user_id'] = intval( $args['user_id'] );
	$args['secondary_user'] = intval( $args['secondary_user'] );
	$args['type'] = sanitize_text_field( wp_unslash( $args['type'] ) );
	$args['content'] = wp_kses_post( wp_unslash( $args['content'] ) );
	$args['question_id'] = intval( $args['question_id'] );
	$args['item_id'] = intval( $args['item_id'] );

	if ( empty( $args['created'] ) || '0000-00-00 00:00:00' == $args['created'] ) {
		$args['created'] = current_time( 'mysql' );
	} else {
		$args['created'] = $args['created'];
	}

	if ( empty( $args['updated'] ) || '0000-00-00 00:00:00' == $args['updated'] ) {
		$args['updated'] = current_time( 'mysql' );
	} else {
		$args['updated'] = $args['updated'];
	}

	// Remove extra args.
	$coulmns = array_intersect_key( $args, $defaults );

	if ( false === $activity_id ) {
		$row = $wpdb->insert(
			$wpdb->ap_activity,
			$coulmns,
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
			)
		);
		if ( false !== $row ) {
			do_action( 'ap_after_inserting_activity', $wpdb->insert_id, $args );
			return $wpdb->insert_id;
		}
	} else {

		$row = $wpdb->update(
			$wpdb->ap_activity,
			$coulmns,
			array( 'id' => $activity_id ),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%d',
				'%d',
				'%s',
				'%s',
			),
			array( '%d' )
		);

		if ( false !== $row ) {
			wp_cache_delete( $id, 'ap_activity' );
			do_action( 'ap_after_updating_activity', $id, $args );
			return $row;
		}
	}

	return false;
}

/**
 * Return activity action title
 * @param  array $args Activity arguments.
 * @return string
 * @since 2.4
 */
function ap_get_activity_action_title($args) {
	$type = $args['type'];

	$content = '';

	$primary_user_link = ap_user_link( $args['user_id'] );
	$primary_user_name = ap_user_display_name( array( 'user_id' => $args['user_id'] ) );
	$user = '<a href="'. $primary_user_link .'">'.$primary_user_name.'</a>';

	switch ( $type ) {

		case 'new_question':
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s asked question %s', 'ap' ), $user, $question_title );
			break;

		case 'new_answer':
			$answer_title = '<a href="'. get_permalink( $args['item_id'] ) .'">'. get_the_title( $args['item_id'] ) .'</a>';
			$content .= sprintf( __( '%s answered on %s', 'ap' ), $user, $answer_title );
			break;

		case 'new_comment':
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$comment = '<span class="ap-comment-excerpt"><a href="'. get_comment_link( $args['item_id'] ) .'">'. get_comment_excerpt( $args['item_id'] ) .'</a></span>';
			$content .= sprintf( __( '%s commented on question %s %s', 'ap' ), $user, $question_title, $comment );
			break;

		case 'new_comment_answer':
			$title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$comment = '<span class="ap-comment-excerpt"><a href="'. get_comment_link( $args['item_id'] ) .'">'. get_comment_excerpt( $args['item_id'] ) .'</a></span>';
			$content .= sprintf( __( '%s commented on answer %s %s', 'ap' ), $user, $title, $comment );
			break;

		case 'edit_question':
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s edited question %s', 'ap' ), $user, $question_title );
			break;

		case 'edit_answer':
			$answer_title = '<a href="'. get_permalink( $args['item_id'] ) .'">'. get_the_title( $args['item_id'] ) .'</a>';
			$content .= sprintf( __( '%s edited answer %s', 'ap' ), $user, $answer_title );
			break;

		case 'edit_comment':
			$comment = '<a href="'. get_comment_link( $args['item_id'] ) .'">'. get_comment_excerpt( $args['item_id'] ) .'</a>';
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s edited comment on %s %s', 'ap' ), $user, $question_title, $comment );
			break;

		case 'answer_selected':
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s selected best answer for %s', 'ap' ), $user, $question_title );
			break;

		case 'answer_unselected':
			$question_title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s unselected best answer for question %s', 'ap' ), $user, $question_title );
			break;

		case 'status_updated':
			$title = '<a href="'. get_permalink( $args['question_id'] ) .'">'. get_the_title( $args['question_id'] ) .'</a>';
			$content .= sprintf( __( '%s updated status of question %s', 'ap' ), $user, $title );
			break;

		case 'status_updated_answer':
			$title = '<a href="'. get_permalink( $args['item_id'] ) .'">'. get_the_title( $args['item_id'] ) .'</a>';
			$content .= sprintf( __( '%s updated status of answer %s', 'ap' ), $user, $title );
			break;
	}

	return apply_filters( 'ap_activity_action_title', $content, $args );
}

/**
 * Sanitize, format and then insert activity
 * @param  array $args Activity arguments.
 * @return boolean
 */
function ap_new_activity( $args = array() ) {
	$user_id = get_current_user_id();

	$defaults = array(
		'user_id' => $user_id,
		'secondary_user' => 0,
		'type' => '',
		'status' => 'publish',
		'content' => '',
		'question_id' => '',
		'item_id' => '',
		'created' => '',
		'updated' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	if ( isset( $args['type'] ) && empty( $args['content'] ) ) {
		$args['content'] = ap_get_activity_action_title( $args );
	}

	return ap_insert_activity( $args );
}

/**
 * Get an activity by ID.
 * @param  	integer $id Activity id.
 * @return 	boolean|object
 * @since 	2.4
 */
function ap_get_activity($id) {
	global $wpdb;

	if ( ! is_integer( $id ) ) {
		return false;
	}

	$row = wp_cache_get( $id, 'ap_activity' );

	if ( false !== $row ) {
		return $row;
	}

	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->ap_activity WHERE id = %d", $id ) );

	wp_cache_set( $id, $row, 'ap_activity' );

	return $row;
}


/**
 * Get the latest history html
 * @param  integer $post_id Post ID.
 * @return string
 */
function ap_post_activity_meta( $post_id = false ) {
	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	return get_post_meta( $post_id, '__ap_activity', true );
}

/**
 * Activity type to human readable title.
 * @param  string $type Activity type.
 * @return string
 */
function ap_activity_short_title( $type ) {
	$title = array(
		'new_question' 		=> __( 'asked', 'ap' ),
		'new_answer' 		=> __( 'answered', 'ap' ),
		'new_comment' 		=> __( 'commented', 'ap' ),
		'new_comment_answer' => __( 'commented on answer', 'ap' ),
		'edit_question' 	=> __( 'edited question', 'ap' ),
		'edit_answer' 		=> __( 'edited answer', 'ap' ),
		'edit_comment' 		=> __( 'edited comment', 'ap' ),
		'answer_selected' 	=> __( 'selected answer', 'ap' ),
		'answer_unselected' => __( 'unselected answer', 'ap' ),
		'status_updated' 	=> __( 'updated status', 'ap' ),
		'best_answer' 		=> __( 'selected as best answer', 'ap' ),
		'unselected_best_answer' 	=> __( 'unselected as best answer', 'ap' ),
	);

	$title = apply_filters( 'ap_activity_short_title', $title );

	if ( isset( $title[ $type ] ) ) {
		return $title[ $type ];
	}

	return $type;
}

/**
 * Get last active time
 * @param  integer $post_id Question or answer ID.
 * @return boolean $html    HTML formatted?
 * @since  2.0.1
 */
function ap_post_active_time($post_id = false, $html = true) {

	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	$post = get_post( $post_id );

	$activity = ap_post_activity_meta( $post_id );

	if ( ! $activity ) {
		$activity['date'] = get_the_time( 'c', $post_id );
		$activity['user_id'] = $post->post_author;
		$activity['type'] 	= 'new_'.$post->post_type;
	}

	if ( ! $html ) {
		return $activity['date'];
	}

	$title = ap_activity_short_title( $activity['type'] );
	$title = esc_html( '<span class="ap-post-history">'.sprintf( __( '%s %s about %s ago', 'ap' ), ap_user_display_name( $activity['user_id'] ), $title, '<time datetime="'. mysql2date( 'c', $activity['date'] ) .'">'.ap_human_time( mysql2date( 'U', $activity['date'] ) ).'</time>' ).'</span>' );

	return sprintf( __( 'Active %s ago', 'ap' ), '<a class="ap-tip" href="#" title="'. $title .'"><time datetime="'. mysql2date( 'c', $activity['date'] ) .'">'.ap_human_time( mysql2date( 'U', $activity['date'] ) ) ).'</time></a>';
}

/**
 * Get latest activity of question or answer.
 * @param  integer $post_id Question or answer ID.
 * @return string
 */
function ap_latest_post_activity_html($post_id = false) {

	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	$post = get_post( $post_id );
	$activity = ap_post_activity_meta( $post_id );

	if ( ! $activity ) {
		$activity['date'] 	= get_the_time( 'c', $post_id );
		$activity['user_id'] = $post->post_author;
		$activity['type'] 	= 'new_'.$post->post_type;
	}

	$html = '';

	if ( $activity ) {

		$title = ap_activity_short_title( $activity['type'] );

		$html .= '<span class="ap-post-history">';
		$html .= sprintf( __( ' %s %s %s ago', 'ap' ),
			'<a href="'.ap_user_link( $activity['user_id'] ).'">'. ap_user_display_name( $activity['user_id'] ) .'</a>',
			$title,
			'<time datetime="'. mysql2date( 'c', $activity['date'] ) .'">'. ap_human_time( $activity['date'], false ) .'</time>'
		);
		$html .= '</span>';
	}

	if ( $html ) {
		return apply_filters( 'ap_latest_post_activity_html', $html );
	}

	return false;
}

/**
 * Get activities
 * @param  string|array $args Arguments.
 * @return string
 * @since  2.4
 */
function ap_get_activities( $args = '' ) {

	return $result;
}
