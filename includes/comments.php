<?php
/**
 * AnsPress comments handling.
 *
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-3.0+
 * @link         https://anspress.io
 * @copyright    2014 Rahul Aryan
 * @package      AnsPress
 * @subpackage   Comments Hooks
 */

/**
 * Comments class
 */
class AnsPress_Comment_Hooks {

	/**
	 * Comment data.
	 *
	 * @param integer $post_id Post ID.
	 * @param boolean $editing Editing mode.
	 * @return array
	 */
	public static function comments_data( $post_id, $editing = false, $offset = 0 ) {
		$user_id = get_current_user_id();

		$args = array(
			'post_id' => $post_id,
			'order'   => 'ASC',
			'status'  => 'approve',
			'number' 	=> ap_opt( 'comment_number' ),
			'offset' 	=> $offset,
		);

		// Always include current user comments.
		if ( ! empty( $user_id ) && $user_id > 0 ) {
			$args['include_unapproved'] = [ $user_id ];
		}

		if ( ap_user_can_approve_comment( ) ) {
			$args['status'] = 'all';
		}

		$comments = get_comments( $args );

		$comments_arr = array();
		foreach ( (array) $comments as $c ) {
			$comments_arr[] = ap_comment_ajax_data( $c );
		}

		if ( ! empty( $comments_arr ) ) {
			return $comments_arr;
		}

		return [];
	}

	/**
	 * Ajax callback for loading comments.
	 *
	 * @since 2.0.1
	 * @since 3.0.0 Moved from AnsPress_Ajax class.
	 */
	public static function load_comments() {
		$post_id = ap_sanitize_unslash( 'post_id', 'r' );

		if ( ! check_ajax_referer( 'comment_form_nonce', '__nonce', false ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Unable to load comments', 'anspress-question-answer' ) ],
			));
		}

		$offset = ap_sanitize_unslash( 'offset', 'r', 0 );
		$shown = $offset + ap_opt( 'comment_number' );
		$count = get_comment_count( $post_id );
		$more = floor( (int) $count['all'] - $shown );

		$result = array(
			'success'       => true,
			'action'        => 'load_comment_form',
			'collapsed'     => ( $count['all'] > $shown ),
			'collapsed_msg' => sprintf( __( 'Show %d more comments', 'anspress-question-answer' ), $more ),
			'offset'        => $shown,
			'comments'      => SELF::comments_data( $post_id, false, $offset ),
		);

		ap_ajax_json( $result );
	}

	/**
	 * Process new comment submission.
	 *
	 * @since 3.0.0
	 */
	public static function new_comment() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'r' );
		$content = ap_sanitize_unslash( 'content', 'r' );

		if ( ! is_user_logged_in() || ! ap_verify_nonce( 'new-comment' ) || ! ap_user_can_comment( $post_id ) ) {
			ap_ajax_json( array(
				'success'  => false,
				'snackbar' => [ 'message' => __( 'Unable to post comment', 'anspress-question-answer' ) ],
			) );
		}

		// Check if comment content is not empty.
		if ( empty( $content ) ) {
			ap_ajax_json( array(
				'success'  => false,
				'snackbar' => [ 'message' => __( 'Sorry, you cannot post a blank comment', 'anspress-question-answer' ) ],
			) );
		}

		$_post = ap_get_post( $post_id );

		$type = 'question' === $_post->post_type ? __( 'question', 'anspress-question-answer' ) : __( 'answer', 'anspress-question-answer' );

		// Check if not restricted post type.
		if ( in_array( $_post->post_status, [ 'draft', 'pending', 'trash' ], true ) ) {
			ap_ajax_json( array(
				'success'  => false,
				'snackbar' => [ 'message' => sprintf( __( 'Commenting is not allowed on draft, pending or deleted %s', 'anspress-question-answer' ), $type ) ],
			) );
		}

		// Get current user object.
		$user = wp_get_current_user();
		if ( ! $user->exists() ) {
			ap_ajax_json( array(
				'success'  => false,
				'snackbar' => [ 'message' => __( 'Sorry, you cannot post a comment', 'anspress-question-answer' ) ],
			) );
		}

		$commentdata = array(
			'comment_post_ID' 		   => $_post->ID,
			'comment_author' 		     => wp_slash( $user->display_name ),
			'comment_author_email' 	 => wp_slash( $user->user_email ),
			'comment_author_url' 	   => wp_slash( $user->user_url ),
			'comment_content' 		   => trim( $content ),
			'comment_type' 			     => 'anspress',
			'comment_parent' 		     => 0,
			'user_id' 				       => $user->ID,
		);

		/**
		 * Filter comment content before inserting to DB.
		 *
		 * @param bool 		$apply_filter 	Apply this filter.
		 * @param string 	$content 		Un-filtered comment content.
		 */
		$commentdata = apply_filters( 'ap_pre_insert_comment', $commentdata );

		// Insert new comment and get the comment ID.
		$comment_id = wp_new_comment( $commentdata, true );

		if ( ! is_wp_error( $comment_id ) && false !== $comment_id ) {
			$c = get_comment( $comment_id );
			do_action( 'ap_after_new_comment', $c );

			$count = get_comment_count( $c->comment_post_ID );

			$result = array(
				'success'    => true,
				'comment'      => ap_comment_ajax_data( $c ),
				'action' 		  => 'new-comment',
				'commentsCount' => [ 'text' => sprintf( _n( '%d Comment', '%d Comments', $count['all'], 'anspress-question-answer' ), $count['all'] ), 'number' => $count['all'], 'unapproved' => $count['awaiting_moderation'] ],
				'snackbar'   => [ 'message' => __( 'Comment successfully posted', 'anspress-question-answer' ) ],
			);

			ap_ajax_json( $result );
		}

		// Lastly output error message.
		ap_ajax_json( array(
			'success' => false,
			'snackbar' => [ 'message' => $comment_id->get_error_message() ],
		) );
	}

	/**
	 * Updates comment.
	 *
	 * @since 3.0.0
	 */
	public static function edit_comment() {
		$comment_id = ap_sanitize_unslash( 'comment_ID', 'r' );
		$content = ap_sanitize_unslash( 'content', 'r' );

		if ( ! is_user_logged_in() || ! ap_verify_nonce( 'edit-comment-' . $comment_id ) || ! ap_user_can_edit_comment( $comment_id ) ) {
			ap_ajax_json( array(
				'success'  => false,
				'snackbar' => [ 'message' => __( 'Sorry, you cannot edit this comment', 'anspress-question-answer' ) ],
			) );
		}

		$comment = get_comment( $comment_id );

		// Check if content is changed.
		if ( $content === $comment->comment_content || empty( $content ) ) {
			ap_ajax_json( [
				'success' => false,
				'snackbar' => [ 'message' => __( 'No change detected, edit comment and then try', 'anspress-question-answer' ) ],
			] );
		}

		$updated = wp_update_comment( array(
			'comment_ID'      => $comment_id,
			'comment_content' => $content,
		) );

		if ( $updated ) {
			$c = get_comment( $comment_id );
			$count = get_comment_count( $c->comment_post_ID );
			$result = array(
				'success'       => true,
				'comment'       => ap_comment_ajax_data( $c ),
				'action' 		     => 'edit-comment',
				'commentsCount' => [ 'text' => sprintf( _n( '%d Comment', '%d Comments', $count['all'], 'anspress-question-answer' ), $count['all'] ), 'number' => $count['all'], 'unapproved' => $count['awaiting_moderation'] ],
				'snackbar'      => [ 'message' => __( 'Comment updated successfully', 'anspress-question-answer' ) ],
			);
			ap_ajax_json( $result );
		}

		ap_ajax_json( array(
			'success'  => false,
			'snackbar' => [ 'message' => __( 'Unable to update comment', 'anspress-question-answer' ) ],
		) );
	}

	/**
	 * Ajax action for deleting comment.
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved from ajax.php to here.
	 */
	public static function delete_comment() {
		$comment_id = (int) ap_sanitize_unslash( 'comment_ID', 'r' );

		if ( ! $comment_id || ! ap_user_can_delete_comment( $comment_id ) || ! ap_verify_nonce( 'delete_comment' ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Failed to delete comment', 'anspress-question-answer' ) ],
			) );
		}

		$_comment = get_comment( $comment_id );

		// Check if deleting comment is locked.
		if ( ap_comment_delete_locked( $_comment->comment_ID ) && ! is_super_admin() ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => sprintf( __( 'This comment was created %s. Its locked hence you cannot delete it.', 'anspress-question-answer' ), ap_human_time( $_comment->comment_date_gmt, false ) ) ],
			) );
		}

		$delete = wp_delete_comment( (integer) $_comment->comment_ID, true );

		if ( $delete ) {
			do_action( 'ap_unpublish_comment', $_comment );
			do_action( 'ap_after_deleting_comment', $_comment );

			$count = get_comment_count( $_comment->comment_post_ID );

			ap_ajax_json( array(
				'success'       => true,
				'snackbar'      => [ 'message' => __( 'Comment successfully deleted', 'anspress-question-answer' ) ],
				'action'        => 'delete_comment',
				'commentsCount' => [ 'text' => sprintf( _n( '%d Comment', '%d Comments', $count['all'], 'anspress-question-answer' ), $count['all'] ), 'number' => $count['all'], 'unapproved' => $count['awaiting_moderation'] ],
			) );
		}
	}

	/**
	 * Modify comment query args for showing pending comments to moderator.
	 *
	 * @param  array $args Comment args.
	 * @return array
	 * @since  3.0.0
	 */
	public static function comments_template_query_args( $args ) {
		if ( ap_user_can_approve_comment() ) {
			$args['status'] = 'all';
		}
		return $args;
	}

	/**
	 * Ajax callback to approve comment.
	 */
	public static function approve_comment() {
		$comment_id = (int) ap_sanitize_unslash( 'comment_ID', 'r' );

		if ( ! ap_verify_nonce( 'approve_comment_' . $comment_id ) || ! ap_user_can_approve_comment( ) ) {
			ap_ajax_json( array(
				'success'  => false,
				'snackbar' => [ 'message' => __( 'Sorry, unable to approve comment', 'anspress-question-answer' ) ],
			) );
		}

		$success = wp_set_comment_status( $comment_id, 'approve' );
		$_comment = get_comment( $comment_id );
		$count = get_comment_count( $_comment->comment_post_ID );

		if ( $success ) {
			$_comment  = get_comment( $comment_id );
			ap_ajax_json( array(
				'success'      => true,
				'action' 		   => 'comment_approved',
				'model'      	 => [ 'approved' => '1', 'actions' => ap_comment_actions( $_comment ) ],
				'comment_ID' 	 => $comment_id,
				'commentsCount' => [ 'text' => sprintf( _n( '%d Comment', '%d Comments', $count['all'], 'anspress-question-answer' ), $count['all'] ), 'number' => $count['all'], 'unapproved' => $count['awaiting_moderation'] ],
				'snackbar'     => [ 'message' => __( 'Comment approved successfully.', 'anspress-question-answer' ) ],
			) );
		}
	}

	/**
	 * Manipulate question and answer comments link.
	 *
	 * @param string     $link    The comment permalink with '#comment-$id' appended.
	 * @param WP_Comment $comment The current comment object.
	 * @param array      $args    An array of arguments to override the defaults.
	 */
	public static function comment_link( $link, $comment, $args ) {
		$_post = ap_get_post( $comment->comment_post_ID );

		if ( ! in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
			return $link;
		}

		$permalink = get_permalink( $_post );
		return $permalink . '#/comment/' . $comment->comment_ID;
	}

	/**
	 * Ajax callback to get a single comment.
	 */
	public static function get_comment() {
		$comment_id = ap_sanitize_unslash( 'comment_id', 'r' );
		$c = get_comment( $comment_id );

		// Check if user can read post.
		if ( ! ap_user_can_read_post( $c->comment_post_ID ) ) {
			wp_die();
		}

		if ( '1' !== $c->comment_approved && ! ( ap_user_can_delete_comment( $c->comment_ID ) || ap_user_can_approve_comment( $c->comment_ID ) ) ) {
			wp_die();
		}

		ap_ajax_json( array(
			'success' => true,
			'comment' => ap_comment_ajax_data( $c, false ),
		) );
	}
}

/**
 * Load comment form button.
 *
 * @param 	mixed $_post Echo html.
 * @return 	string
 * @since 	0.1
 */
function ap_comment_btn_html( $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( 'question' === $_post->post_type  && ap_opt( 'disable_comments_on_question' ) ) {
		return;
	}

	if ( 'answer' === $_post->post_type && ap_opt( 'disable_comments_on_answer' ) ) {
		return;
	}

	$comment_count = get_comments_number( $_post->ID );
	$args = wp_json_encode( [ 'post_id' => $_post->ID, '__nonce' => wp_create_nonce( 'comment_form_nonce' ) ] );

	$unapproved = '';

	if ( ap_user_can_approve_comment() ) {
		$unapproved_count = ! empty( $_post->fields['unapproved_comments'] ) ? (int) $_post->fields['unapproved_comments'] : 0;
		$unapproved = '<b class="unapproved' . ( $unapproved_count > 0 ? ' have' : '' ) . '" ap-un-commentscount title="' . esc_attr__( 'Comments awaiting moderation', 'anspress-question-answer' ) . '">' . $unapproved_count . '</b>';
	}

	// Show comments button.
	$output = '<a href="#comments-' . $_post->ID . '" class="ap-btn ap-btn-comments" ap="comment_btn" ap-query="' . esc_js( $args ) . '">';
	$output .= '<span ap-commentscount-text>' . sprintf( _n( '%d Comment', '%d Comments', $comment_count, 'anspress-question-answer' ), $comment_count ) . '</span>';
	$output .= $unapproved . '</a>';

	// Add comment button.
	$q = '';
	if ( ap_user_can_comment( $_post->ID ) ) {
		$q = wp_json_encode( array(
			'post_id'        => get_the_ID(),
			'__nonce'        => wp_create_nonce( 'new-comment' ),
			'ap_ajax_action' => 'new_comment',
		) );
	}

	$msg = __( 'You do not have permission to comment.', 'anspress-question-answer' );

	if ( ! is_user_logged_in() ) {
		$msg .= ' ' . sprintf( __( '%sLogin%s to post comment', 'anspress-question-answer' ), '<a href="' . wp_login_url( ) . '">', '</a>' );
	}

	$output .= '<a href="#" class="ap-btn-newcomment ap-btn ap-btn-small" ap="new-comment" ap-query="' . esc_js( $q ) . '"' . ( empty( $q ) ? ' ap-msg="' . esc_attr( $msg ) . '"' : '' ) . '>';
	$output .= esc_attr__( 'Add a Comment', 'anspress-question-answer' );
	$output .= '</a>';

	return $output;
}

/**
 * Comment actions args.
 *
 * @param object|integer $comment Comment object.
 * @return array
 * @since 4.0.0
 */
function ap_comment_actions( $comment ) {
	$comment = get_comment( $comment );
	$actions = [];

	if ( ap_user_can_edit_comment( $comment->comment_ID ) ) {
		$actions[] = [ 'label' => __( 'Edit', 'anspress-question-answer' ), 'cb' => 'edit_comment', 'query' => [ '__nonce' => wp_create_nonce( 'edit-comment-' . $comment->comment_ID ), 'comment_ID' => $comment->comment_ID, 'post_id' => $comment->comment_post_ID, 'ap_ajax_action' => 'edit_comment' ] ];
	}

	if ( ap_user_can_delete_comment( $comment->comment_ID ) ) {
		$actions[] = [ 'label' => __( 'Delete', 'anspress-question-answer' ), 'query' => [ '__nonce' => wp_create_nonce( 'delete_comment' ), 'comment_ID' => $comment->comment_ID, 'ap_ajax_action' => 'delete_comment' ] ];
	}

	if ( '0' === $comment->comment_approved && ap_user_can_approve_comment( ) ) {
		$actions[] = [ 'label' => __( 'Approve', 'anspress-question-answer' ), 'query' => [ '__nonce' => wp_create_nonce( 'approve_comment_' . $comment->comment_ID ), 'comment_ID' => $comment->comment_ID, 'ap_ajax_action' => 'approve_comment' ] ];
	}

	/**
	 * For filtering comment action buttons.
	 *
	 * @param array $actions Comment actions.
	 * @since   2.0.0
	 */
	return apply_filters( 'ap_comment_actions', $actions );
}

/**
 * Check if comment delete is locked.
 * @param  integer $comment_ID     Comment ID.
 * @return bool
 * @since  3.0.0
 */
function ap_comment_delete_locked( $comment_ID ) {
	$comment = get_comment( $comment_ID );
	$commment_time = mysql2date( 'U', $comment->comment_date_gmt ) + (int) ap_opt( 'disable_delete_after' );
	return current_time( 'timestamp', true ) > $commment_time;
}

/**
 * Output comment wrapper.
 *
 * @return void
 * @since 2.1
 */
function ap_the_comments() {
	global $post;
	if ( ( 'answer' === $post->post_type && ! ap_opt( 'disable_comments_on_answer' ) ) || 'question' === $post->post_type ) {
		echo '<apComments id="comments-' . esc_attr( get_the_ID() ) . '"><div class="ap-comments"></div></apComments>';
	}
}

/**
 * Return ajax comment data.
 *
 * @param object $c Comment object.
 * @return array
 * @since 4.0.0
 */
function ap_comment_ajax_data( $c, $actions = true ) {
	return array(
		'ID'        => $c->comment_ID,
		'link'      => get_comment_link( $c ),
		'avatar'    => get_avatar( $c->user_id, 30 ),
		'user_link' => ap_user_link( $c->user_id ),
		'user_name' => ap_user_display_name( $c->user_id ),
		'iso_date'  => date( 'c', strtotime( $c->comment_date ) ),
		'time'      => ap_human_time( $c->comment_date_gmt, false ),
		'content'   => $c->comment_content,
		'approved'  => $c->comment_approved,
		'class'     => implode( ' ', get_comment_class( 'ap-comment', $c->comment_ID, null, false ) ),
		'actions' 	 => $actions ? ap_comment_actions( $c ) : [],
	);
}

