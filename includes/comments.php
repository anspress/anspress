<?php
/**
 * AnsPress comments handling.
 *
 * @author       Rahul Aryan <rah12@live.com>
 * @license      GPL-3.0+
 * @link         https://anspress.net
 * @copyright    2014 Rahul Aryan
 * @package      AnsPress
 * @subpackage   Comments Hooks
 */

/**
 * Comments class
 */
class AnsPress_Comment_Hooks {

	/**
	 * Filter comments array to include only comments which user can read.
	 *
	 * @param array $comments Comments.
	 * @return array
	 * @since 4.1.0
	 */
	public static function the_comments( $comments ) {
		foreach ( $comments as $k => $c ) {
			if ( 'anspress' === $c->comment_type && ! ap_user_can_read_comment( $c ) ) {
				unset( $comments[ $k ] );
			}
		}

		return $comments;
	}

	/**
	 * Ajax callback for loading comments.
	 *
	 * @since 2.0.1
	 * @since 3.0.0 Moved from AnsPress_Ajax class.
	 *
	 * @category haveTest
	 */
	public static function load_comments() {
		global $avatar_size;
		$paged      = 1;
		$comment_id = ap_sanitize_unslash( 'comment_id', 'r' );

		if ( ! empty( $comment_id ) ) {
			$_comment = get_comment( $comment_id );
			$post_id  = $_comment->comment_post_ID;
		} else {
			$post_id = ap_sanitize_unslash( 'post_id', 'r' );
			$paged   = max( 1, ap_isset_post_value( 'paged', 1 ) );
		}

		$_post = ap_get_post( $post_id );

		$args = array(
			'show_more' => false,
		);

		if ( ! empty( $_comment ) ) {
			$avatar_size         = 60;
			$args['comment__in'] = $_comment->comment_ID;
		}

		ob_start();
		ap_the_comments( $post_id, $args );
		$html = ob_get_clean();

		$type = 'question' === $_post->post_type ? __( 'Question', 'anspress-question-answer' ) : __( 'Answer', 'anspress-question-answer' );

		$result = array(
			'success' => true,
			'html'    => $html,
		);

		ap_ajax_json( $result );
	}

	/**
	 * Modify comment query args for showing pending comments to moderator.
	 *
	 * @param  array $args Comment args.
	 * @return array
	 * @since  3.0.0
	 */
	public static function comments_template_query_args( $args ) {
		global $question_rendered;

		if ( true === $question_rendered && is_singular( 'question' ) ) {
			return false;
		}

		if ( ap_user_can_approve_comment() ) {
			$args['status'] = 'all';
		}

		return $args;
	}

	/**
	 * Ajax callback to approve comment.
	 */
	public static function approve_comment() {
		$comment_id = (int) ap_sanitize_unslash( 'comment_id', 'r' );

		if ( ! ap_verify_nonce( 'approve_comment_' . $comment_id ) || ! ap_user_can_approve_comment() ) {
			ap_ajax_json(
				array(
					'success'  => false,
					'snackbar' => [ 'message' => __( 'Sorry, unable to approve comment', 'anspress-question-answer' ) ],
				)
			);
		}

		$success  = wp_set_comment_status( $comment_id, 'approve' );
		$_comment = get_comment( $comment_id );
		$count    = get_comment_count( $_comment->comment_post_ID );

		if ( $success ) {
			$_comment = get_comment( $comment_id );
			ap_ajax_json(
				array(
					'success'       => true,
					'cb'            => 'commentApproved',
					'comment_ID'    => $comment_id,
					'post_ID'       => $_comment->comment_post_ID,
					'commentsCount' => array(
						'text'       => sprintf(
							_n( '%d Comment', '%d Comments', $count['all'], 'anspress-question-answer' ),
							$count['all']
						),
						'number'     => $count['all'],
						'unapproved' => $count['awaiting_moderation'],
					),
					'snackbar'      => array( 'message' => __( 'Comment approved successfully.', 'anspress-question-answer' ) ),
				)
			);
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
	 * Change comment_type while adding comments for question or answer.
	 *
	 * @param array $commentdata Comment data array.
	 * @return array
	 * @since 4.1.0
	 */
	public static function preprocess_comment( $commentdata ) {
		if ( ! empty( $commentdata['comment_post_ID'] ) ) {
			$post_type = get_post_type( $commentdata['comment_post_ID'] );

			if ( in_array( $post_type, [ 'question', 'answer' ], true ) ) {
				$commentdata['comment_type'] = 'anspress';
			}
		}

		return $commentdata;
	}

	/**
	 * Override comments template for single question page.
	 * This will prevent post comments below single question.
	 *
	 * @param string $template Template.
	 * @return string
	 *
	 * @since 4.1.11
	 */
	public static function comments_template( $template ) {
		if ( is_singular( 'question' ) || is_anspress() ) {
			$template = ap_get_theme_location( 'post-comments.php' );
		}

		return $template;
	}
}

/**
 * Load comment form button.
 *
 * @param   mixed $_post Echo html.
 * @return  string
 * @since   0.1
 * @since   4.1.0 Added @see ap_user_can_read_comments() check.
 * @since   4.1.2 Hide comments button if comments are already showing.
 */
function ap_comment_btn_html( $_post = null ) {

	if ( ! ap_user_can_read_comments( $_post ) ) {
		return;
	}

	$_post = ap_get_post( $_post );

	if ( 'question' === $_post->post_type && ap_opt( 'disable_comments_on_question' ) ) {
		return;
	}

	if ( 'answer' === $_post->post_type && ap_opt( 'disable_comments_on_answer' ) ) {
		return;
	}

	$comment_count = get_comments_number( $_post->ID );
	$args          = wp_json_encode(
		[
			'post_id' => $_post->ID,
			'__nonce' => wp_create_nonce( 'comment_form_nonce' ),
		]
	);

	$unapproved = '';

	if ( ap_user_can_approve_comment() ) {
		$unapproved_count = ! empty( $_post->fields['unapproved_comments'] ) ? (int) $_post->fields['unapproved_comments'] : 0;
		$unapproved       = '<b class="unapproved' . ( $unapproved_count > 0 ? ' have' : '' ) . '" ap-un-commentscount title="' . esc_attr__( 'Comments awaiting moderation', 'anspress-question-answer' ) . '">' . $unapproved_count . '</b>';
	}

	$output = ap_new_comment_btn( $_post->ID, false );

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
		$actions[] = array(
			'label' => __( 'Edit', 'anspress-question-answer' ),
			'href'  => '#',
			'query' => array(
				'action'     => 'comment_modal',
				'__nonce'    => wp_create_nonce( 'edit_comment_' . $comment->comment_ID ),
				'comment_id' => $comment->comment_ID,
			),
		);
	}

	if ( ap_user_can_delete_comment( $comment->comment_ID ) ) {
		$actions[] = array(
			'label' => __( 'Delete', 'anspress-question-answer' ),
			'href'  => '#',
			'query' => array(
				'__nonce'        => wp_create_nonce( 'delete_comment_' . $comment->comment_ID ),
				'ap_ajax_action' => 'delete_comment',
				'comment_id'     => $comment->comment_ID,
			),
		);
	}

	if ( '0' === $comment->comment_approved && ap_user_can_approve_comment() ) {
		$actions[] = array(
			'label' => __( 'Approve', 'anspress-question-answer' ),
			'href'  => '#',
			'query' => array(
				'__nonce'        => wp_create_nonce( 'approve_comment_' . $comment->comment_ID ),
				'ap_ajax_action' => 'approve_comment',
				'comment_id'     => $comment->comment_ID,
			),
		);
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
 *
 * @param  integer $comment_ID     Comment ID.
 * @return bool
 * @since  3.0.0
 */
function ap_comment_delete_locked( $comment_ID ) {
	$comment       = get_comment( $comment_ID );
	$commment_time = mysql2date( 'U', $comment->comment_date_gmt ) + (int) ap_opt( 'disable_delete_after' );
	return current_time( 'timestamp', true ) > $commment_time;
}

/**
 * Output comment wrapper.
 *
 * @param mixed $_post Post ID or object.
 * @param array $args  Arguments.
 * @param array $single Is on single page? Default is `false`.
 *
 * @return void
 * @since 2.1
 * @since 4.1.0 Added two args `$_post` and `$args` and using WP_Comment_Query.
 * @since 4.1.1 Check if valid post and post type before loading comments.
 * @since 4.1.2 Introduced new argument `$single`.
 */
function ap_the_comments( $_post = null, $args = [], $single = false ) {
	// If comment number is 0 then dont show on single question.
	if ( $single && ap_opt( 'comment_number' ) < 1 ) {
		return;
	}

	global $comment;

	$_post = ap_get_post( $_post );

	// Check if valid post.
	if ( ! $_post || ! in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
		echo '<div class="ap-comment-no-perm">' . __( 'Not a valid post ID.', 'anspress-question-answer' ) . '</div>';
		return;
	}

	if ( ! ap_user_can_read_comments( $_post ) ) {
		echo '<div class="ap-comment-no-perm">' . __( 'Sorry, you do not have permission to read comments.', 'anspress-question-answer' ) . '</div>';

		return;
	}

	if ( 'question' === $_post->post_type && ap_opt( 'disable_comments_on_question' ) ) {
		return;
	}

	if ( 'answer' === $_post->post_type && ap_opt( 'disable_comments_on_answer' ) ) {
		return;
	}

	if ( 0 == get_comments_number( $_post->ID ) ) {
		if ( ! $single ) {
			echo '<div class="ap-comment-no-perm">' . __( 'No comments found.', 'anspress-question-answer' ) . '</div>';
		}
		return;
	}

	$user_id = get_current_user_id();
	$paged   = (int) max( 1, ap_isset_post_value( 'paged', 1 ) );

	$default = array(
		'post_id'       => $_post->ID,
		'order'         => 'ASC',
		'status'        => 'approve',
		'number'        => $single ? ap_opt( 'comment_number' ) : 99,
		// 'type'      => 'anspress',
		'show_more'     => true,
		'no_found_rows' => false,
	);

	// Always include current user comments.
	if ( ! empty( $user_id ) && $user_id > 0 ) {
		$default['include_unapproved'] = [ $user_id ];
	}

	if ( ap_user_can_approve_comment() ) {
		$default['status'] = 'all';
	}

	$args = wp_parse_args( $args, $default );
	if ( $paged > 1 ) {
		$args['offset'] = ap_opt( 'comment_number' );
	}

	$query = new WP_Comment_Query( $args );
	if ( 0 == $query->found_comments && ! $single ) {
		echo '<div class="ap-comment-no-perm">' . __( 'No comments found.', 'anspress-question-answer' ) . '</div>';
		return;
	}

	foreach ( $query->comments as $c ) {
		$comment = $c;
		ap_get_template_part( 'comment' );
	}

	echo '<div class="ap-comments-footer">';
	if ( $query->max_num_pages > 1 && $single ) {
		echo '<a class="ap-view-comments" href="#/comments/' . $_post->ID . '/all">' . sprintf( __( 'Show %s more comments', 'anspress-question-answer' ), $query->found_comments - ap_opt( 'comment_number' ) ) . '</a>';
	}

	echo '</div>';
}

/**
 * A wrapper function for @see ap_the_comments() for using in
 * post templates.
 *
 * @return void
 * @since 4.1.2
 */
function ap_post_comments() {
	echo '<apcomments id="comments-' . esc_attr( get_the_ID() ) . '" class="have-comments">';
	ap_the_comments( null, [], true );
	echo '</apcomments>';

	// New comment button.
	echo ap_comment_btn_html( get_the_ID() );
}

/**
 * Return or print new comment button.
 *
 * @param integer $post_id Post id.
 * @param boolean $echo    Return or echo. Default is echo.
 * @return string|void
 * @since 4.1.8
 */
function ap_new_comment_btn( $post_id, $echo = true ) {
	if ( ap_user_can_comment( $post_id ) ) {
		$output = '';

		$btn_args = wp_json_encode( array(
			'action'  => 'comment_modal',
			'post_id' => $post_id,
			'__nonce' => wp_create_nonce( 'new_comment_' . $post_id ),
		) );

		$output .= '<a href="#" class="ap-btn-newcomment" aponce="false" apajaxbtn apquery="' . esc_js( $btn_args ) . '">';
		$output .= esc_attr__( 'Add a Comment', 'anspress-question-answer' );
		$output .= '</a>';

		if ( false === $echo ) {
			return $output;

		}

		echo $output;
	}
}
