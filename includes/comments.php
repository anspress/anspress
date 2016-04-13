<?php

class AnsPress_Comment_Hooks
{
	/**
	 * Return comment form.
	 * @since 2.0.1
	 * @since 3.0.0 Moved from AnsPress_Ajax class.
	 */
	public static function load_comment_form() {
		if ( ! is_user_logged_in() || ! ap_verify_nonce( 'comment_form_nonce' ) ) {
			ap_ajax_json( 'something_wrong' );
		}

	    $result = array(
			'ap_responce' => true,
			'action' => 'load_comment_form',
		);

	    if ( isset( $_POST['comment_ID'] ) ) {
			$comment_id = (integer) $_POST['comment_ID'];

			// Check if user can edit comment.
			if ( ! ap_user_can_edit_comment( $comment_id, get_current_user_id() ) ) {
				ap_ajax_json( 'no_permission' );
			}

			$comment = get_comment( $comment_id );
			$result['html'] = ap_get_comment_form( $comment->comment_post_ID, $comment->comment_ID );
			$result['container'] = '#post-c-'.$comment->comment_post_ID;
			ap_ajax_json( $result );
	    }

		$post_id = (integer) $_POST['post'];

		// Check if they have permission to comment.
		if ( ! ap_user_can_comment( $post_id ) ) {
			ap_ajax_json( 'no_permission' );
		}

		$result['html'] = ap_get_comment_form( $post_id );
		$result['container'] = '#post-c-'.$post_id;

		ap_ajax_json( $result );
	}

	/**
	 * Process comment submission.
	 * @since 3.0.0
	 */
	public static function submit_comment() {
		$post_id = (integer) $_POST['post_id'];
		if ( ! is_user_logged_in() || ! ap_verify_nonce( $post_id . '_comment' ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		// Check if comment content is not empty.
		if ( empty( $_POST['content'] ) ) {
			ap_ajax_json( 'comment_content_empty' );
		}

		// Check if they have permission to comment.
		if ( ! ap_user_can_comment( $post_id ) ) {
			ap_ajax_json( 'no_permission' );
		}

		// Check if user can edit comment.
		if ( isset( $_POST['comment_ID'] ) && ! ap_user_can_edit_comment( (integer) $_POST['comment_ID'], get_current_user_id() ) ) {
			ap_ajax_json( 'no_permission' );
		}

		$post = get_post( $post_id );

		if ( ! $post || empty( $post->post_status ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		if ( in_array( $post->post_status, array( 'draft', 'pending', 'trash' ) ) ) {
			ap_ajax_json( 'draft_comment_not_allowed' );
		}

		$filter_type = isset( $_POST['comment_ID'] ) ? 'ap_before_updating_comment' : 'ap_before_inserting_comment';

		/**
		 * Filter comment content before inserting to DB.
		 * @param bool 		$apply_filter 	Apply this filter.
		 * @param string 	$content 		Un-filtered comment content.
		 */
		$filter = apply_filters( $filter_type, false, $_POST['content'] );

		if ( true === $filter && is_array( $filter ) ) {
			ap_ajax_json( $filter );
		}

		// If comment_ID exists then update comment.
		if ( isset( $_POST['comment_ID'] ) ) {
			SELF::update_comment();
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
			'comment_content' 		=> trim( $_POST['content'] ),
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

			ob_start();
			ap_comment( $comment );
			$html = ob_get_clean();

			ap_ajax_json( array(
				'action' 		=> 'new_comment',
				'status' 		=> true,
				'comment_ID' 	=> $comment->comment_ID,
				'comment_post_ID' => $comment->comment_post_ID,
				'comment_content' => $comment->comment_content,
				'html' 			=> $html,
				'message' 		=> 'comment_success',
				'view' 			=> array(
					'comments_count_'.$comment->comment_post_ID => '('.$count['approved'].')',
					'comment_count_label_'.$comment->comment_post_ID => sprintf( _n( 'One comment', '%d comments',$count['approved'], 'anspress-question-answer' ), $count['approved'] ),
					),
				)
			);
		}

		// If execution reached to this point then there must be something wrong.
		ap_ajax_json( 'something_wrong' );
	}

	/**
	 * Updates comment.
	 * @since 3.0.0
	 */
	public static function update_comment() {
		$comment_id = (integer) $_POST['comment_ID'];
		$comment = get_comment( $comment_id );
		$comment_content = trim( $_POST['content'] );

		$subscribed = ap_is_user_subscribed( $comment->comment_post_ID, 'comment', $comment->user_id );
		$content_changed = $comment_content != $comment->comment_content;
		$subscription = false;

		// Toggle subscription.
		if ( isset( $_POST['notify'] ) && ! $subscribed ) {
			$subscription = ap_add_comment_subscriber( $comment_id, $comment->user_id );
		} elseif ( ! isset( $_POST['notify'] ) && $subscribed ) {
			$subscription = ap_remove_comment_subscriber( $comment_id, $comment->user_id );
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
			'comment_ID' => $comment_id,
			'comment_content' => $comment_content,
		) );

		if ( $updated ) {
			$comment = get_comment( $comment_id );

			ob_start();
			comment_text( $comment_id );
			$html = ob_get_clean();

			ap_ajax_json( array(
				'action' 			=> 'edit_comment',
				'comment_ID' 		=> $comment->comment_ID,
				'comment_post_ID' 	=> $comment->comment_post_ID,
				'comment_content' 	=> $comment->comment_content,
				'html' 				=> $html,
				'message' 			=> 'comment_edit_success',
			) );
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
			$date = mysql2date('U', $comment->comment_date_gmt );

			ap_ajax_json( array(
				'message_type' => 'warning',
				'message' => sprintf(
					__( 'This post was created %s, its locked hence you cannot delete it.', 'anspress-question-answer' ),
					ap_human_time( $date )
				),
			) );
		}

		$delete = wp_delete_comment( (integer) $comment->comment_ID, true );

		if ( $delete ) {
			do_action( 'ap_unpublish_comment', $comment );
			do_action( 'ap_after_deleting_comment', $comment );
			$count = get_comment_count( $comment->comment_post_ID );
			ap_ajax_json( array(
				'action' 		=> 'delete_comment',
				'comment_ID' 	=> $comment->comment_ID,
				'message' 		=> 'comment_delete_success',
				'view' 			=> array(
						'comments_count_'.$comment->comment_post_ID => '('.$count['approved'].')',
						'comment_count_label_'.$comment->comment_post_ID => sprintf( _n( 'One comment', '%d comments', $count['approved'], 'anspress-question-answer' ), $count['approved'] ),
					),
			) );
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

	public static function approve_comment(){
		$args = $_POST['args'];
		if ( ! ap_verify_nonce( 'approve_comment_'. (int) $args[0] ) || ! ap_user_can_approve_comment( ) ) {
	    	ap_ajax_json( 'something_wrong' );
	    }

	    $comment_id = (int) $args[0];

		$success = wp_set_comment_status( $comment_id, 'approve' );
		if( $success ){
			ap_ajax_json( array(
				'action' 		=> 'approve_comment',
				'comment_ID' 	=> $comment_id,
				'message' 		=> __('Comment approved successfully', 'anspress-question-answer'),
				'do'			=> array( 
					'removeClass' => [ '#comment-'.$comment_id, 'unapproved' ],
					array(
						['action' => 'remove_if_exists', 'args' => '#comment-'.$comment_id .' .comment-awaiting-moderation'],
						['action' => 'remove_if_exists', 'args' => '#comment-'.$comment_id .' .ap-comment-approve'],
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
	if ( ap_user_can_comment( $post->ID ) ) {

		if ( $post->post_type == 'question' && ap_opt( 'disable_comments_on_question' ) ) {
			return;
		}

		if ( $post->post_type == 'answer' && ap_opt( 'disable_comments_on_answer' ) ) {
			return;
		}

		$nonce = wp_create_nonce( 'comment_form_nonce' );
		$comment_count = get_comments_number( get_the_ID() );
		$output = '<a href="#comments-'.get_the_ID().'" class="comment-btn ap-tip" data-action="load_comment_form" data-query="ap_ajax_action=load_comment_form&post='.get_the_ID().'&__nonce='.$nonce.'" title="'.__( 'Comments', 'anspress-question-answer' ).'">'.__( 'Comment', 'anspress-question-answer' ).'<span class="ap-data-view ap-view-count-'.$comment_count.'" data-view="comments_count_'.get_the_ID().'">('.$comment_count.')</span></a>';

		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
}

/**
 * Output comment action links
 */
function ap_comment_actions_buttons() {
	global $comment;
	$post_o = get_post( $comment->comment_post_ID );

	if ( ! $post_o->post_type == 'question' || ! $post_o->post_type == 'answer' ) {
		return;
	}

	$actions = array();

	if ( ap_user_can_edit_comment( get_comment_ID() ) ) {
		$nonce = wp_create_nonce( 'comment_form_nonce' );
		$actions['edit'] = '<a class="comment-edit-btn" href="#" data-toggle="#li-comment-'.get_comment_ID().'" data-action="load_comment_form" data-query="ap_ajax_action=load_comment_form&comment_ID='.get_comment_ID().'&__nonce='.$nonce.'">'.__( 'Edit', 'anspress-question-answer' ).'</a>';
	}

	if ( ap_user_can_delete_comment( get_comment_ID() ) ) {
		$nonce = wp_create_nonce( 'delete_comment' );
		$actions['delete'] = '<a class="comment-delete-btn" href="#" data-toggle="#li-comment-'.get_comment_ID().'" data-action="delete_comment" data-query="ap_ajax_action=delete_comment&comment_ID='.get_comment_ID().'&__nonce='.$nonce.'">'.__( 'Delete', 'anspress-question-answer' ).'</a>';
	}

	if ( '0' != $comment->comment_approved && is_user_logged_in() ) {
		$actions['flag'] = ap_get_comment_flag_btn( get_comment_ID() );
	}

	if ( '0' == $comment->comment_approved && ap_user_can_approve_comment( ) ) {
		$nonce = wp_create_nonce( 'approve_comment_'.get_comment_ID() );
		$actions['approve'] = '<a class="ap-comment-approve" href="#" data-action="ajax_btn" data-query="approve_comment::'.$nonce.'::'.get_comment_ID().'">'.__( 'Approve', 'anspress-question-answer' ).'</a>';
	}

	/*
     * For filtering comment action buttons.
     * @param array $actions Comment actions.
     * @since   2.0.0
	 */
	$actions = apply_filters( 'ap_comment_actions_buttons', $actions );
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
	return current_time( 'timestamp', true ) < ( get_comment_date( 'U', $comment_ID ) + (integer) ap_opt( 'disable_delete_after' ) );
}

