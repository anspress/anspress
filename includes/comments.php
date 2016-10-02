<?php

class AnsPress_Comment_Hooks
{
	public static function comments_data( $post_id, $editing = false ) {
		$data = array(
			'current_user_avatar' => get_avatar( get_current_user_id(), 30 ),
		);

		// Check if user can comment, if so then send form data.
		if ( ap_user_can_comment( $post_id ) ) {
			$data['load_form'] = true;
			$data['form'] = array(
				'nonce' => wp_create_nonce( $post_id.'_comment' ),
				'post_id' => $post_id,
				'key' => $post_id.'Comments',
			);
		}

		$comments = get_comments( [ 'post_id' => $post_id, 'order' => 'ASC' ] );
		$DataComments = array();
		foreach ( (array) $comments as $c ) {
			if ( $editing != $c->comment_ID ) {
				$DataComments[] = array(
					'id' => $c->comment_ID,
					'avatar' => '<a href="'.ap_user_link( $c->user_id ).'" '. ap_hover_card_attributes( $c->user_id, false ) .'>'. get_avatar( $c->user_id, 30 ) .'</a>',
					'user_link' => ap_user_link( $c->user_id ),
					'user_name' => ap_user_display_name($c->user_id ),
					'iso_date' => date( 'c', strtotime($c->comment_date ) ),
					'time' => ap_human_time( $c->comment_date_gmt, false ),
					'content' => $c->comment_content,
					'approved' => $c->comment_approved,
					'class' => comment_class( 'ap-comment', $c->comment_ID, null, false ),
					'actions' => array_values(ap_get_comment_actions($c, $post_id ) ),
				);
			} elseif ( ap_user_can_edit_comment( $c->comment_ID, get_current_user_id() ) ) {
				$data['form']['comment_ID'] = $c->comment_ID;
				$data['form']['content'] = $c->comment_content;
				$data['form']['subscribed'] = ap_is_user_subscribed( $c->comment_ID, 'comment', $c->user_id );
			}
		}

		if ( ! empty( $DataComments ) ) {
			$data['comments'] = $DataComments;
		}

		return $data;
	}
	/**
	 * Return comment form.
	 * @since 2.0.1
	 * @since 3.0.0 Moved from AnsPress_Ajax class.
	 */
	public static function load_comments() {
		if ( ! ap_verify_nonce( 'comment_form_nonce' ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		$args = ap_sanitize_unslash('args', 'request' );

	    $result = array(
			'ap_responce' => true,
			'action' => 'load_comment_form',
			'template' => 'comments',
		);

	    if ( isset( $args[1] ) ) {
			$comment_id = (integer) $args[1];

			// Check if user can edit comment.
			if ( ! ap_user_can_edit_comment( $comment_id, get_current_user_id() ) ) {
				ap_ajax_json( 'no_permission' );
			}

			$result['html'] = ap_get_comment_form( $comment->comment_post_ID, $comment->comment_ID );
			$result['container'] = '#post-c-'.$comment->comment_post_ID;
			$result['key'] = $comment->comment_post_ID.'Comments';
			$result['apData'] = array();
			ap_ajax_json( $result );
	    }

		$post_id = (int) $args[0];

		// Check if they have permission to comment.
		/*
		if ( ! ap_user_can_comment( $post_id ) ) {
			ap_ajax_json( 'no_permission' );
		}*/

		$result['appendTo'] = '#post-c-'.$post_id;
		$result['do'] = [ 'addClass' => [ 'context', 'ajax-disabled' ] ];
		$result['key'] = $post_id.'Comments';

		$result['apData'] = SELF::comments_data( $post_id );

		ap_ajax_json( $result );
	}

	public static function edit_comment_form() {
		if ( ! is_user_logged_in() || ! ap_verify_nonce( 'comment_form_nonce' ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		$comment_ID = ap_sanitize_unslash('comment_ID', 'request' );

		// Check if user can edit comment.
		if ( ! ap_user_can_edit_comment( $comment_ID, get_current_user_id() ) ) {
			ap_ajax_json( 'no_permission' );
		}

		$comment = get_comment( $comment_ID );

	    $result = array(
			'ap_responce' => true,
			'action' => 'load_comment_form',
			'template' => 'comments',
		);

		$result['key'] = $comment->comment_post_ID.'Comments';

		$result['apData'] = SELF::comments_data( $comment->comment_post_ID, $comment_ID );
		ap_ajax_json( $result );
	}

	/**
	 * Process comment submission.
	 * @since 3.0.0
	 */
	public static function submit_comment() {
		$post_id = (integer) ap_sanitize_unslash('post_id', 'request' );
		$comment_ID = (integer) ap_sanitize_unslash('comment_ID', 'request' );
		$content = ap_sanitize_unslash( 'content', 'request' );

		if ( ! is_user_logged_in() || ! ap_verify_nonce( $post_id . '_comment' ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		// Check if comment content is not empty.
		if ( empty( $content ) ) {
			ap_ajax_json( 'comment_content_empty' );
		}

		// Check if they have permission to comment.
		if ( ! ap_user_can_comment( $post_id ) ) {
			ap_ajax_json( 'no_permission' );
		}

		// Check if user can edit comment.
		if ( ! empty( $comment_ID ) && ! ap_user_can_edit_comment( $comment_ID, get_current_user_id() ) ) {
			ap_ajax_json( 'no_permission' );
		}

		$post = get_post( $post_id );

		if ( ! $post || empty( $post->post_status ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		if ( in_array( $post->post_status, array( 'draft', 'pending', 'trash' ) ) ) {
			ap_ajax_json( 'draft_comment_not_allowed' );
		}

		$filter_type = ! empty( $comment_ID ) ? 'ap_before_updating_comment' : 'ap_before_inserting_comment';

		/**
		 * Filter comment content before inserting to DB.
		 * @param bool 		$apply_filter 	Apply this filter.
		 * @param string 	$content 		Un-filtered comment content.
		 */
		$filter = apply_filters( $filter_type, false, $content );

		if ( true === $filter && is_array( $filter ) ) {
			ap_ajax_json( $filter );
		}

		// If comment_ID exists then update comment.
		if ( ! empty( $comment_ID ) ) {
			SELF::update_comment( $post_id, $comment_ID, $content );
		}

		// Get current user object.
		$user = wp_get_current_user();
		if ( ! $user->exists() ) {
			ap_ajax_json( 'no_permission' );
		}

		$commentdata = array(
			'comment_post_ID' 		=> $post->ID,
			'comment_author' 		=> wp_slash( $user->display_name ),
			'comment_author_email' 	=> wp_slash( $user->user_email ),
			'comment_author_url' 	=> wp_slash( $user->user_url ),
			'comment_content' 		=> trim( $content ),
			'comment_type' 			=> 'anspress',
			'comment_parent' 		=> 0,
			'user_id' 				=> $user->ID,
		);

		// Insert new comment and get the comment ID
		$comment_id = wp_new_comment( $commentdata );

		if ( false !== $comment_id ) {
			// Add comment subscriber if checked about new notification.
			if ( isset( $_POST['notify'] ) ) {
				ap_add_comment_subscriber( $comment_id, $user->ID );
			}

			$comment = get_comment( $comment_id );
			do_action( 'ap_after_new_comment', $comment );

			$count = get_comment_count( $comment->comment_post_ID );

			$result = array(
				'action' 		=> 'new_comment',
				'status' 		=> true,
				'comment_ID' 	=> $comment->comment_ID,
				'comment_post_ID' => $comment->comment_post_ID,
				'comment_content' => $comment->comment_content,
				'message' 		=> 'comment_success',
				'view' 			=> array(
					'comments_count_'.$comment->comment_post_ID => '('.$count['approved'].')',
					'comment_count_label_'.$comment->comment_post_ID => sprintf( _n( 'One comment', '%d comments',$count['approved'], 'anspress-question-answer' ), $count['approved'] ),
					),
				);

			$result['key'] = $comment->comment_post_ID.'Comments';

			$result['apData'] = SELF::comments_data( $comment->comment_post_ID );
			ap_ajax_json( $result );
		}

		// If execution reached to this point then there must be something wrong.
		ap_ajax_json( 'something_wrong' );
	}

	/**
	 * Updates comment.
	 * @since 3.0.0
	 */
	public static function update_comment( $post_id, $comment_ID, $comment_content ) {
		$comment = get_comment( $comment_ID );

		$subscribed = ap_is_user_subscribed( $comment->comment_post_ID, 'comment', $comment->user_id );
		$content_changed = $comment_content != $comment->comment_content;
		$subscription = false;

		// Toggle subscription.
		if ( isset( $_POST['notify'] ) && ! $subscribed ) {
			$subscription = ap_add_comment_subscriber( $comment_ID, $comment->user_id );
		} elseif ( ! isset( $_POST['notify'] ) && $subscribed ) {
			$subscription = ap_remove_comment_subscriber( $comment_ID, $comment->user_id );
		}

		if ( ! $content_changed && $subscription ) {
			ap_ajax_json( [
				'message' => __('Your comment subscription updated successfully', 'anspress-question-answer' ),
				'message_type' => 'success',
			] );
		}

		if ( ! $content_changed ) {
			ap_ajax_json( [
				'message' => __('Nothing changed!', 'anspress-question-answer' ),
				'message_type' => 'warning',
			] );
		}

		$updated = wp_update_comment( array(
			'comment_ID' => $comment_ID,
			'comment_content' => $comment_content,
		) );

		if ( $updated ) {

			$result = array(
				'action' 			=> 'edit_comment',
				'comment_ID' 		=> $comment->comment_ID,
				'comment_post_ID' 	=> $comment->comment_post_ID,
				'message' 			=> 'comment_edit_success',
			);

			$result['key'] = $comment->comment_post_ID.'Comments';

			$result['apData'] = SELF::comments_data( $comment->comment_post_ID );
			ap_ajax_json( $result );
		}
	}

	/**
	 * Ajax action for deleting comment.
	 * @since 2.0.0
	 * @since 3.0.0 Moved from ajax.php to here.
	 */
	public static function delete_comment() {
	    if ( ! isset( $_POST['comment_ID'] ) || ! ap_user_can_delete_comment( (integer) $_POST['comment_ID'] ) || ! ap_verify_nonce( 'delete_comment' ) ) {
	    	ap_ajax_json( 'something_wrong' );
	    }

	    $comment_id = (integer) $_POST['comment_ID'];

		$comment = get_comment( $comment_id );

		// Check if deleting comment is locked.
		if ( ap_comment_delete_locked( $comment->comment_ID ) && ! is_super_admin() ) {
			ap_ajax_json( array(
				'message_type' => 'warning',
				'message' => sprintf(
					__( 'This post was created %s, its locked hence you cannot delete it.', 'anspress-question-answer' ),
					ap_human_time( $comment->comment_date_gmt, false )
				),
			) );
		}

		$delete = wp_delete_comment( (integer) $comment->comment_ID, true );

		if ( $delete ) {
			do_action( 'ap_unpublish_comment', $comment );
			do_action( 'ap_after_deleting_comment', $comment );
			$count = get_comment_count( $comment->comment_post_ID );
			$result = array(
				'action' 		=> 'delete_comment',
				'comment_ID' 	=> $comment->comment_ID,
				'message' 		=> 'comment_delete_success',
				'do' 			=> array( 'remove_if_exists' => '#comment-'.$comment->comment_ID ),
				'view' 			=> array(
						'comments_count_'.$comment->comment_post_ID => '('.$count['approved'].')',
						'comment_count_label_'.$comment->comment_post_ID => sprintf( _n( 'One comment', '%d comments', $count['approved'], 'anspress-question-answer' ), $count['approved'] ),
					),
			);

			$result['key'] = $comment->comment_post_ID.'Comments';
			$result['apData'] = SELF::comments_data( $comment->comment_post_ID );
			ap_ajax_json($result );
		}

	    ap_ajax_json( 'something_wrong' );
	}

	/**
	 * Modify comment query args for showing pending comments to moderator.
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
		$args = ap_sanitize_unslash('args', 'request' );
		if ( ! ap_verify_nonce( 'approve_comment_'. (int) $args[0] ) || ! ap_user_can_approve_comment( ) ) {
	    	ap_ajax_json( 'something_wrong' );
	    }

	    $comment_id = (int) $args[0];

		$success = wp_set_comment_status( $comment_id, 'approve' );
		if ( $success ) {
			ap_ajax_json( array(
				'action' 		=> 'approve_comment',
				'comment_ID' 	=> $comment_id,
				'message' 		=> __('Comment approved successfully.', 'anspress-question-answer' ),
				'do'			=> array(
					'removeClass' => [ '#comment-'.$comment_id, 'unapproved' ],
					array(
						[ 'action' => 'remove_if_exists', 'args' => '#comment-'.$comment_id .' .comment-awaiting-moderation' ],
						[ 'action' => 'remove_if_exists', 'args' => '#comment-'.$comment_id .' .ap-comment-approve' ],
					)
				),
			) );
		}

		ap_ajax_json( 'something_wrong' );
	}
}

/**
 * Load comment form button.
 * @param 	bool $echo Echo html.
 * @return 	string
 * @since 	0.1
 */
function ap_comment_btn_html($echo = false) {
	global $post;
	// if ( ap_user_can_comment( $post->ID ) ) {
	if ( $post->post_type == 'question' && ap_opt( 'disable_comments_on_question' ) ) {
		return;
	}

	if ( $post->post_type == 'answer' && ap_opt( 'disable_comments_on_answer' ) ) {
		return;
	}

		$nonce = wp_create_nonce( 'comment_form_nonce' );
		$comment_count = get_comments_number( get_the_ID() );
		$output = '<a href="#comments-'.get_the_ID().'" class="comment-btn ap-tip" data-action="ajax_btn" data-query="load_comments::'.$nonce.'::'.get_the_ID().'" title="'.__( 'Comments', 'anspress-question-answer' ).'">'.__( 'Comment', 'anspress-question-answer' ).'<span class="ap-data-view ap-view-count-'.$comment_count.'" data-view="comments_count_'.get_the_ID().'">('.$comment_count.')</span></a>';

	if ( $echo ) {
		echo $output;
	} else {
		return $output;
	}
	// }
}

function ap_get_comment_actions( $comment_id, $post_id ) {
	$comment = get_comment( $comment_id );
	$post_o = get_post( $post_id );

	if ( ! $post_o->post_type == 'question' || ! $post_o->post_type == 'answer' ) {
		return;
	}

	$actions = array();

	if ( ap_user_can_edit_comment( $comment->comment_ID ) ) {
		$nonce = wp_create_nonce( 'comment_form_nonce' );
		$actions['edit'] = '<a class="comment-edit-btn" href="#" data-toggle="#li-comment-'.$comment->comment_ID.'" data-action="editComment" data-query="ap_ajax_action=edit_comment_form&__nonce='.$nonce.'&comment_ID='.$comment->comment_ID.'">'.__( 'Edit', 'anspress-question-answer' ).'</a>';
	}

	if ( ap_user_can_delete_comment( $comment->comment_ID ) ) {
		$nonce = wp_create_nonce( 'delete_comment' );
		$actions['delete'] = '<a class="comment-delete-btn" href="#" data-toggle="#li-comment-'.$comment->comment_ID.'" data-action="deleteComment" data-query="ap_ajax_action=delete_comment&__nonce='.$nonce.'&comment_ID='.$comment->comment_ID.'">'.__( 'Delete', 'anspress-question-answer' ).'</a>';
	}

	if ( '0' != $comment->comment_approved && is_user_logged_in() ) {
		$actions['flag'] = ap_get_comment_flag_btn( $comment->comment_ID );
	}

	if ( '0' == $comment->comment_approved && ap_user_can_approve_comment( ) ) {
		$nonce = wp_create_nonce( 'approve_comment_'.$comment->comment_ID );
		$actions['approve'] = '<a class="ap-comment-approve" href="#" data-action="ajax_btn" data-query="approve_comment::'.$nonce.'::'.$comment->comment_ID.'">'.__( 'Approve', 'anspress-question-answer' ).'</a>';
	}

	/*
     * For filtering comment action buttons.
     * @param array $actions Comment actions.
     * @since   2.0.0
	 */
	return apply_filters( 'ap_comment_actions_buttons', $actions );
}

/**
 * Output comment action links
 */
function ap_comment_actions_buttons() {
	global $comment;
	$post_o = get_post( $comment->comment_post_ID );
	$actions = ap_get_comment_actions($comment, $comment->comment_post_ID );
	foreach ( (array) $actions as $k => $action ) {
		echo '<span class="ap-comment-action ap-action-'.esc_attr( $k ).'">'.$action.'</span>';
	}
}

/**
 * Get comment form.
 * @param  bool|integer $comment_id Comment ID.
 * @return string
 * @since  3.0.0
 */
function ap_get_comment_form( $post_id = false, $comment_id = false ) {
	global $ap_comment;

	if ( false !== $comment_id ) {
		$ap_comment = get_comment( $comment_id );
	}

	ob_start();
	?>
        <form class="ap-comment-form" method="POST" id="ap-commentform">        
			<?php include ap_get_theme_location( 'comment-form.php' ); ?>
			<input type="hidden" name="__nonce" value="<?php echo wp_create_nonce( $post_id.'_comment' ); ?>" />
			<input type="hidden" name="post_id" value="<?php echo $post_id; ?>" />
            <input type="hidden" name="action" value="ap_ajax" />
            <?php if ( false !== $comment_id ) :   ?>
            	<input type="hidden" name="comment_ID" value="<?php echo $comment_id; ?>" />
            <?php endif; ?>
            <input type="hidden" name="ap_ajax_action" value="submit_comment" />
        </form>
	<?php
	$o = ob_get_clean();

	/**
	 * Filter AnsPress comment form.
	 * @param string $o Form html.
	 * @since 3.0.0
	 */
	return apply_filters( 'ap_get_comment_form', $o );
}

function ap_comment_form( $post_id = false, $comment_id = false ) {
	echo ap_get_comment_form( $post_id, $comment_id );
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

