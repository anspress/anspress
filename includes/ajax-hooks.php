<?php
/**
 * Register all ajax hooks.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 * @package   AnsPress/ajax
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register all ajax callback
 */
class AnsPress_Ajax
{
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	public function __construct() {
	    anspress()->add_action( 'ap_ajax_suggest_similar_questions', $this, 'suggest_similar_questions' );
	    anspress()->add_action( 'ap_ajax_load_comments', 'AnsPress_Comment_Hooks', 'load_comments' );
	    anspress()->add_action( 'ap_ajax_edit_comment_form', 'AnsPress_Comment_Hooks', 'edit_comment_form' );
	    anspress()->add_action( 'ap_ajax_delete_comment', 'AnsPress_Comment_Hooks', 'delete_comment' );
	    anspress()->add_action( 'ap_ajax_select_best_answer', $this, 'select_best_answer' );
	    anspress()->add_action( 'ap_ajax_delete_post', $this, 'delete_post' );
	    anspress()->add_action( 'ap_ajax_permanent_delete_post', $this, 'permanent_delete_post' );
	    anspress()->add_action( 'ap_ajax_restore_post', $this, 'restore_post' );
	    anspress()->add_action( 'ap_ajax_change_post_status', 'AnsPress_Post_Status', 'change_post_status' );
	    anspress()->add_action( 'ap_ajax_set_featured', $this, 'set_featured' );
	    anspress()->add_action( 'ap_ajax_follow', $this, 'follow' );
	    anspress()->add_action( 'ap_ajax_hover_card', $this, 'hover_card' );
	    anspress()->add_action( 'ap_ajax_delete_notification', $this, 'delete_notification' );
	    anspress()->add_action( 'ap_ajax_markread_notification', $this, 'markread_notification' );
	    anspress()->add_action( 'ap_ajax_set_notifications_as_read', $this, 'set_notifications_as_read' );

	    anspress()->add_action( 'ap_ajax_subscribe', 'AnsPress_Subscriber_Hooks', 'subscribe' );
	    anspress()->add_action( 'ap_ajax_vote', 'AnsPress_Vote', 'vote' );

	    // Flag ajax callbacks.
	    anspress()->add_action( 'ap_ajax_flag_post', 'AnsPress_Flag', 'flag_post' );
	    anspress()->add_action( 'ap_ajax_flag_comment', 'AnsPress_Flag', 'flag_comment' );

	    anspress()->add_action( 'ap_ajax_delete_activity', $this, 'delete_activity' );
	    anspress()->add_action( 'ap_ajax_submit_comment', 'AnsPress_Comment_Hooks','submit_comment' );
	    anspress()->add_action( 'ap_ajax_approve_comment', 'AnsPress_Comment_Hooks','approve_comment' );
	    anspress()->add_action( 'ap_hover_card_user', __CLASS__, 'hover_card_user' );
	    anspress()->add_action( 'ap_ajax_post_actions_dp', 'AnsPress_Theme', 'post_actions_dp' );
	    anspress()->add_action( 'ap_ajax_user_dp', 'AnsPress_User', 'user_dp' );
	    anspress()->add_action( 'ap_ajax_list_filter', 'AnsPress_Theme', 'list_filter' );
	    anspress()->add_action( 'ap_ajax_load_tinymce_assets', __CLASS__, 'load_tinymce_assets' );
	    anspress()->add_action( 'wp_ajax_ap_cover_upload', 'AnsPress_User', 'cover_upload' );
		anspress()->add_action( 'wp_ajax_ap_avatar_upload', 'AnsPress_User', 'avatar_upload' );
		anspress()->add_action( 'ap_ajax_filter_search', __CLASS__, 'filter_search' );
		anspress()->add_action( 'ap_ajax_convert_to_post', __CLASS__, 'convert_to_post' );
		anspress()->add_action( 'ap_ajax_delete_attachment', __CLASS__, 'delete_attachment' );

	}

	/**
	 * Show similar questions while asking a question.
	 * @since 2.0.1
	 */
	public function suggest_similar_questions() {
		// Die if question suggestion is disabled.
		if ( ap_disable_question_suggestion( ) ) {
			wp_die( 'false' );
		}

		$keyword = ap_sanitize_unslash( 'value', 'request' );
	    if ( empty( $keyword ) || ( ! ap_verify_default_nonce() && ! current_user_can( 'manage_options' ) ) ) {
	        wp_die( 'false' );
	    }

	    $keyword = ap_sanitize_unslash( 'value', 'request' );
	    $is_admin = (bool) ap_isset_post_value('is_admin', false );

	    $questions = get_posts( array(
			'post_type' => 'question',
			'showposts' => 10,
			's' => $keyword,
		));

	    if ( $questions ) {
	        $items = '<div class="ap-similar-questions-head">';
	        $items .= '<h3>'.ap_icon( 'check', true ).sprintf( __( '%d similar questions found', 'anspress-question-answer' ), count( $questions ) ).'</h3>';
	        $items .= '<p>'.__( 'We\'ve found similar questions that have already been asked, click to read them.', 'anspress-question-answer' ).'</p>';
	        $items .= '</div>';

		    $items .= '<div class="ap-similar-questions">';
	        foreach ( (array) $questions as $p ) {
	            $count = ap_count_answer_meta( $p->ID );
	            $p->post_title = ap_highlight_words( $p->post_title, $keyword );

	            if ( $is_admin ) {
	            	$items .= '<div class="ap-q-suggestion-item clearfix"><a class="select-question-button button button-primary button-small" href="'.add_query_arg( array( 'post_type' => 'answer', 'post_parent' => $p->ID ), admin_url( 'post-new.php' ) ).'">'.__( 'Select', 'anspress-question-answer' ).'</a><span class="question-title">'.$p->post_title.'</span><span class="acount">'.sprintf( _n( '1 Answer', '%d Answers', $count, 'anspress-question-answer' ), $count ).'</span></div>';
	            } else {
		            $items .= '<a class="ap-sqitem clearfix" target="_blank" href="'.get_permalink( $p->ID ).'"><span class="acount">'.sprintf( _n( '1 Answer', '%d Answers', $count, 'anspress-question-answer' ), $count ).'</span><span class="ap-title">'.$p->post_title.'</span></a>';
		        }
	        }

	       	$items .= '</div>';

	        $result = array( 'status' => true, 'html' => $items );
	    } else {
	        $result = array( 'status' => false, 'message' => __( 'No related questions found.', 'anspress-question-answer' ) );
	    }

	    ap_ajax_json( $result );
	}

	/**
	 * Ajax action for selecting a best answer.
	 * @since 2.0.0
	 */
	public function select_best_answer() {
	    $answer_id = (int) ap_sanitize_unslash( 'answer_id', 'request' );

	    if ( ! is_user_logged_in() || ! ap_verify_nonce( 'answer-'.$answer_id ) ) {
	        ap_ajax_json( 'something_wrong' );
	    }

	    $post = get_post( $answer_id );

	    // Unselect best answer if already selected.
	    if ( ap_question_best_answer_selected( $post->post_parent ) ) {
	        ap_unselect_answer( $answer_id );

	        ap_ajax_json( array(
	        	'message' 	=> 'unselected_the_answer',
	        	'action' 	=> 'unselected_answer',
	        	'do' 		=> 'reload',
	        ) );

	    }

	    // Do not allow answer to be selected as best if status is moderate.
	    if ( 'moderate' == $post->post_status ) {
	    	ap_ajax_json( [ 'message_type' => 'warning', 'message' => __( 'Answer with moderate status cannot be selected as best.', 'anspress-question-answer' ) ] );
	    }

	    /**
	     * Trigger right after selecting an answer.
	     * @param integer $post_author Post author ID.
	     * @param integer $question_id Question ID.
	     * @param integer $answer_id   Answer ID.
	     */
		do_action( 'ap_select_answer', $post->post_author, $post->post_parent, $post->ID );

		update_post_meta( $post->ID, ANSPRESS_BEST_META, 1 );
		update_post_meta( $post->post_parent, ANSPRESS_SELECTED_META, $post->ID );
		update_post_meta( $post->post_parent, ANSPRESS_UPDATED_META, current_time( 'mysql' ) );

		if ( ap_opt( 'close_selected' ) ) {
			wp_update_post( array( 'ID' => $post->post_parent, 'post_status' => 'closed' ) );
		}

		ap_update_user_best_answers_count_meta( $post->post_author );
		ap_update_user_solved_answers_count_meta( $post->post_author );

		$html = ap_select_answer_btn_html( $answer_id );
		ap_ajax_json( array(
			'message' 	=> 'selected_the_answer',
			'action' 	=> 'selected_answer',
			'do' 		=> 'reload',
			'html' 		=> $html,
		) );
	}

	/**
	 * Process ajax trash posts callback.
	 */
	public function delete_post() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );

		if ( ! ap_verify_nonce( 'delete_post_'.$post_id ) || ! ap_user_can_delete_post( $post_id ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		$post = get_post( $post_id );

		// Delete lock feature.
		// Do not allow to delete if defined time elapsed.
		if ( (time() > (get_the_time( 'U', $post->ID ) + (int) ap_opt( 'disable_delete_after' ))) && ! is_super_admin() ) {

			ap_ajax_json( array(
				'message_type' => 'warning',
				'message' => sprintf( __( 'This post was created %s, hence you cannot delete it.','anspress-question-answer' ), ap_human_time( get_the_time( 'U', $post->ID ) ) ),
			) );
		}

		wp_trash_post( $post_id );

		// Die if not question or answer post type.
		if ( ! in_array( $post->post_type, [ 'question', 'answer' ] ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		// Delete question.
		if ( $post->post_type == 'question' ) {
			do_action( 'ap_wp_trash_question', $post_id );
			$this->send( array(
				'action' 		=> 'delete_question',
				'do' 			=> array( 'redirect' => ap_base_page_link() ),
				'message' 		=> 'question_moved_to_trash',
			) );
		}

		do_action( 'ap_wp_trash_answer', $post_id );

		$current_ans = ap_count_published_answers( $post->post_parent );
		$count_label = sprintf( _n( '1 Answer', '%d Answers', $current_ans, 'anspress-question-answer' ), $current_ans );
		ap_ajax_json(array(
			'action' 		=> 'delete_answer',
			'div_id' 		=> '#answer_'.$post_id,
			'count' 		=> $current_ans,
			'count_label' 	=> $count_label,
			'remove' 		=> ( ! $current_ans ? true : false ),
			'message' 		=> 'answer_moved_to_trash',
			'view' 			=> array( 'answer_count' => $current_ans, 'answer_count_label' => $count_label ),
		));
	}

	/**
	 * Handle Ajax callback for permanent delete of post.
	 */
	public function permanent_delete_post() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );

		if ( ! ap_verify_nonce( 'delete_post_'.$post_id ) || ! ap_user_can_permanent_delete() ) {
			ap_ajax_json( 'something_wrong' );
		}

		$post = get_post( $post_id );

		// Die if not question or answer post type.
		if ( ! in_array( $post->post_type, [ 'question', 'answer' ] ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		// wp_trash_post( $post_id );
		if ( $post->post_type == 'question' ) {
			/**
			 * Triggered right before deleting question.
			 * @param  integer $post_id question ID.
			 */
			do_action( 'ap_wp_trash_question', $post_id );
		} else {
			/**
			 * Triggered right before deleting answer.
			 * @param  integer $post_id answer ID.
			 */
			do_action( 'ap_wp_trash_answer', $post_id );
		}

		wp_delete_post( $post_id, true );

		if ( $post->post_type == 'question' ) {
			ap_ajax_json( array(
				'action' 		=> 'delete_question',
				'do' 			=> array( 'redirect' => ap_base_page_link() ),
				'message' 		=> 'question_deleted_permanently',
			) );
		}

		$current_ans = ap_count_published_answers( $post->post_parent );
		$count_label = sprintf( _n( '1 Answer', '%d Answers', $current_ans, 'anspress-question-answer' ), $current_ans );

		ap_ajax_json(array(
			'action' 		=> 'delete_answer',
			'div_id' 		=> '#answer_'.$post_id,
			'count' 		=> $current_ans,
			'count_label' 	=> $count_label,
			'remove' 		=> ( ! $current_ans ? true : false),
			'message' 		=> 'answer_deleted_permanently',
			'view' 			=> array( 'answer_count' => $current_ans, 'answer_count_label' => $count_label ),
		));
	}

	/**
	 * Handle Ajax callback for restoring post.
	 */
	public function restore_post() {
		$args = ap_sanitize_unslash( 'args', 'request' );

		if ( ! ap_verify_nonce( 'restore_'. $args[0] ) || ! ap_user_can_restore() ) {
			ap_ajax_json( 'something_wrong' );
		}

		$post = get_post( $args[0] );

		// Die if not question or answer post type.
		if ( ! in_array( $post->post_type, [ 'question', 'answer' ] ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		// Do the thing.
		wp_untrash_post( $post->ID );

		ap_ajax_json(array(
			'action' 		=> 'restore_post',
			'do' 			=> [ 'removeClass' => [ '.post-'.$post->ID, 'status-trash' ], 'remove_if_exists' => '.post-'.$post->ID.' .ap-notice' ],
			'message' 		=> __( 'Post restored successfully', 'anspress-question-answer' ),
			'message_type' => 'success',
		));
	}

	/**
	 * Handle set feature and unfeature ajax callback
	 */
	public function set_featured() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );
		if ( ! is_super_admin() || ! ap_verify_nonce( 'set_featured_'.$post_id ) ) {
			ap_ajax_json( 'no_permission' );
		}

		$post = get_post( $post_id );
		$featured_questions = (array) get_option( 'featured_questions' );

		// Do nothing if post type is not question.
		if ( $post->post_type != 'question' ) {
			ap_ajax_json( __('Only question can be set as featured', 'anspress-question-answer' ) );
		}

		// Check if current question ID is in featured question array.
		if ( ! empty( $featured_questions ) && in_array( $post->ID, $featured_questions ) ) {
			foreach ( (array) $featured_questions as $key => $q ) {
				if ( $q == $post->ID ) {
					unset( $featured_questions[ $key ] );
				}
			}

			update_option( 'featured_questions', $featured_questions );

			$this->send(array(
				'action' 		=> 'unset_featured_question',
				'message' 		=> 'unset_featured_question',
				'do' 			=> array( 'updateHtml' => '#set_featured_'.$post->ID ),
				'html' 			=> __( 'Set as featured', 'anspress-question-answer' ),
			));
		}

		if ( empty( $featured_questions ) ) {
			$featured_questions = array( $post->ID );
		} else {
			$featured_questions[] = $post->ID;
		}

		update_option( 'featured_questions', $featured_questions );

		$this->send(array(
			'action' 		=> 'set_featured_question',
			'message' 		=> 'set_featured_question',
			'do' 			=> array( 'updateHtml' => '#set_featured_'.$post->ID ),
			'html' 			=> __( 'Unset as featured', 'anspress-question-answer' ),
		));

		$this->something_wrong();
	}

	/**
	 * Process follow ajax callback
	 */
	public function follow() {
		$user_to_follow = (int) $_POST['user_id'];
		$current_user_id = get_current_user_id();

		if ( ! ap_verify_nonce( 'follow_'.$user_to_follow.'_'.$current_user_id ) ) {
			$this->something_wrong();
		}

		if ( ! is_user_logged_in() ) {
			$this->send( 'please_login' );
		}

		if ( $user_to_follow == $current_user_id ) {
			$this->send( 'cannot_follow_yourself' );
		}

		$is_following = ap_is_user_following( $user_to_follow, $current_user_id );
		$elm = '#follow_'.$user_to_follow;
		if ( $is_following ) {
			ap_remove_follower( $current_user_id, $user_to_follow );

			$this->send( array(
				'message' 		=> 'unfollow',
				'action' 		=> 'unfollow',
				'do' 			=> array( 'updateText' => array( $elm, __( 'Follow', 'anspress-question-answer' ) ), 'toggle_active_class' => $elm ),
			) );
		} else {
			ap_add_follower( $current_user_id, $user_to_follow );

			$this->send( array(
				'message' 		=> 'follow',
				'action' 		=> 'follow',
				'do' 			=> array( 'updateText' => array( '#follow_'.$user_to_follow, __( 'Unfollow', 'anspress-question-answer' ) ), 'toggle_active_class' => $elm ),
			) );
		}
	}

	/**
	 * Handle Ajax callback for user hover card
	 */
	public function hover_card() {
		if ( ap_opt( 'disable_hover_card' ) ) {
			$this->something_wrong();
		}

		$id = (int) $_POST['id'];
		$type = ap_sanitize_unslash( 'type', 'request', 'user' );

		if ( ! ap_verify_default_nonce() ) {
			$this->something_wrong();
		}

		/**
		 * AP Hover card actions.
		 * @param integer $id ID.
		 */
		do_action('ap_hover_card_'.$type, $id );

		wp_die();
	}

	/**
	 * Output hover card for user.
	 * @param  integer $id User ID.
	 * @since  3.0.0
	 */
	public static function hover_card_user( $id ) {
		$cache = get_transient( 'ap_user_card_'.$id );

		if ( false !== $cache ) {
			ap_ajax_json( $cache );
		}

		global $ap_user_query;
		$ap_user_query = ap_has_users( array( 'ID' => $id ) );

		if ( $ap_user_query->has_users() ) {
			while ( ap_users() ) : ap_the_user();
				$last_seen = !empty(ap_user_get_the_meta( '__last_active' )) ? ap_human_time( ap_user_get_the_meta( '__last_active' ), false ) : __('Never','anspress-question-answer');
				$data = array(
					'template' => 'user-hover',
					'disableAutoLoad' => 'true',
					'apData' => array(
						'id' 			=> ap_user_get_the_ID(),
						'name' 			=> ap_user_get_the_display_name(),
						'mention' 		=> '@'.ap_user_get_the_user_login(),
						'profile_link' 	=> ap_user_get_the_link(),
						'avatar' 		=> ap_user_get_the_avatar( 80 ),
						'signature' 	=> ap_get_user_signature(),
						'reputation' 	=> ap_user_get_the_reputation(),
						'stats'			=> array(
							[ 'label' => __('Answers', 'anspress-question-answer' ), 'count' => ap_user_get_the_meta('__total_answers' ) ],
							[ 'label' => __('Best', 'anspress-question-answer' ), 'count' => ap_user_get_the_meta('__best_answers' ) ],
							[ 'label' => __('Question', 'anspress-question-answer' ), 'count' => ap_user_get_the_meta('__total_questions' ) ],
							[ 'label' => __('comments', 'anspress-question-answer' ), 'count' => ap_user_comment_count() ],
							[ 'label' => __('Followers', 'anspress-question-answer' ), 'count' => ap_user_get_the_meta('__total_followers' ) ],
							[ 'label' => __('Following', 'anspress-question-answer' ), 'count' => ap_user_get_the_meta('__total_following' ) ],
						),
						'active' 		=> sprintf( __( 'Last seen %s', 'anspress-question-answer' ), $last_seen ),
					),
				);
				/**
				 * Filter user hover card data.
				 * @param  array $data Card data.
				 * @return array
				 * @since  3.0.0
				 */
				$data = apply_filters( 'ap_user_hover_data', $data );
				set_transient( 'ap_user_card_'.$id, $data, MINUTE_IN_SECONDS );
				ap_ajax_json( $data );
			endwhile;
		}
	}

	/**
	 * Handle ajax callback for delete notification
	 */
	public function delete_notification() {

		if ( ! ap_verify_nonce( 'delete_notification' ) && ! is_user_logged_in() ) {
			$this->something_wrong();
		}

		$notification = ap_get_notification_by_id( (int) $_POST['id'] );

		if ( $notification && ( get_current_user_id() == $notification->noti_user_id || is_super_admin() ) ) {

			$row = ap_delete_notification( $notification->noti_id );

			if ( false !== $row ) {
				$this->send(
					array(
						'message' 	=> 'delete_notification',
						'action' 	=> 'delete_notification',
						'container' => '#ap-notification-'.$notification->noti_id,
					)
				);
			}
		}

		$this->something_wrong();
	}

	/**
	 * Handle ajax callback for mark all notification as read
	 */
	public function markread_notification() {
		$id = (int) ap_sanitize_unslash('id', 'request' );

		// Check for nonce if notification id is set.
		if ( ! empty( $id ) && ! ap_verify_nonce( 'ap_markread_notification_'.$id ) && ! is_user_logged_in() ) {
			$this->something_wrong();
		}

		// Check for nonce if no notification id.
		if ( empty($id ) && ! ap_verify_nonce( 'ap_markread_notification_'.get_current_user_id() ) && ! is_user_logged_in() ) {
			$this->something_wrong();
		}

		// If id found then only mark that notification as read.
		if ( ! empty( $id ) ) {
			$notification = ap_get_notification_by_id( $id );

			if ( $notification && ( get_current_user_id() == $notification->noti_user_id || is_super_admin()) ) {

				$row = ap_update_notification( array( 'noti_id' => $id, 'noti_user_id' => get_current_user_id() ), array( 'noti_status' => 1 ) );

				if ( false !== $row ) {
					ap_ajax_json( array(
						'message' 		=> 'mark_read_notification',
						'action' 		=> 'mark_read_notification',
						'container' 	=> '.ap-notification-'.$notification->noti_id,
						'view' 			=> array( 'notification_count' => ap_get_total_unread_notification() ),
						'do' => array( 'removeClass' => [ '#ap-notification-'.$notification->noti_id, 'unread' ] ),
					) );
				}
			}
		} // If no id the mark all notifications as read.
		else {
			$row = ap_notification_mark_all_read( get_current_user_id() );

			if ( false !== $row ) {
				ap_ajax_json( array(
					'message' 	=> 'mark_read_notification',
					'action' 	=> 'mark_all_read',
					'container' => '#ap-notification-dropdown',
					'view' 		=> array( 'notification_count' => '0' ),
				) );
			}
		}
	}

	/**
	 * Handle ajax callback for mark all notification as read
	 */
	public function set_notifications_as_read() {
		$ids = ap_sanitize_unslash( 'ids', 'request' );
		$ids = explode( ',', $ids );

		if ( count( $ids ) == 0 ) {
			wp_die();
		}

		if ( ! ap_verify_default_nonce() && ! is_user_logged_in() ) {
			wp_die();
		}

		foreach ( (array) $ids as $id ) {
			$id = (int) $id;
			if ( 0 != $id ) {
				ap_notification_mark_as_read( $id, get_current_user_id() );
			}
		}

		$this->send( array(
			'container' => '#ap-notification-dropdown',
			'view' => array( 'notification_count' => ap_get_total_unread_notification() ),
		) );

		wp_die();
	}


	/**
	 * Terminate the ajax callback and send a JSON response
	 * In browser user will see a message "something went wrong".
	 * @since 2.4
	 */
	public function something_wrong() {
	    $this->send( 'something_wrong' );
	}

	/**
	 * Send JSON response and terminate
	 * @param array|string $result Ajax response.
	 */
	public function send($result) {
	    ap_send_json( ap_ajax_responce( $result ) );
	}

	public function delete_activity() {
	    if ( ! ap_verify_nonce( 'ap_delete_activity' ) || ! is_super_admin() || ! isset( $_POST['args'][0] ) ) {
	        $this->something_wrong();
	    }

	    $activity_id = (int) $_POST['args'][0];

	    $row = ap_delete_activity( $activity_id );

	    if ( false !== $row ) {
	        $this->send( array(
	        	'message' 	=> 'delete_activity',
	        	'action' 	=> 'delete_activity',
	        	'do' 		=> array( 'remove_if_exists' => '#activity-'.$activity_id ),
	        ) );
	    }

	    $this->something_wrong();
	}

	public function comment_form() {
		if ( empty( $_POST['comment'] ) ) {
			$this->send( 'comment_content_empty' );
		}

		$comment_post_ID = (int) $_POST['comment_post_ID'];

		if ( ! isset( $_REQUEST['comment_ID'] ) ) {
			// Do security check
			if ( ! ap_user_can_comment( $comment_post_ID ) || ! isset( $_POST['__nonce'] ) || ! wp_verify_nonce( $_POST['__nonce'], 'comment_' . (int) $_POST['comment_post_ID'] ) ) {
				$this->send( 'no_permission' );
			}
		} else {
			if ( ! ap_user_can_edit_comment( (int) $_REQUEST['comment_ID'] ) || ! wp_verify_nonce( $_REQUEST['__nonce'], 'comment_'.(int) $_REQUEST['comment_ID'] ) ) {
				$this->send( 'no_permission' );
			}
		}

		$post = get_post( $comment_post_ID );

		if ( ! $post || empty( $post->post_status ) ) {
			$this->something_wrong();
		}

		if ( in_array( $post->post_status, array( 'draft', 'pending', 'trash' ) ) ) {
			$this->send( 'draft_comment_not_allowed' );
		}

		$filter_type = isset( $_POST['comment_ID'] ) ? 'ap_before_updating_comment' : 'ap_before_inserting_comment';

		$filter = apply_filters( $filter_type, false, $_POST['comment'] );

		if ( true === $filter && is_array( $filter ) ) {
			$this->send( $filter );
		}

		if ( isset( $_POST['comment_ID'] ) ) {
			$comment_id = (int) $_POST['comment_ID'];

			$updated = wp_update_comment( array( 'comment_ID' => $comment_id, 'comment_content' => trim( $_POST['comment'] ) ) );

			if ( $updated ) {
				$comment = get_comment( $comment_id );

				ob_start();
				comment_text( $comment_id );
				$html = ob_get_clean();

				$this->send( array( 'action' => 'edit_comment', 'comment_ID' => $comment->comment_ID, 'comment_post_ID' => $comment->comment_post_ID, 'comment_content' => $comment->comment_content, 'html' => $html, 'message' => 'comment_edit_success' ) );
			}
		} else {
			$user = wp_get_current_user();
			if ( $user->exists() ) {
				$user_ID = $user->ID;
				$comment_author = wp_slash( $user->display_name );
				$comment_author_email = wp_slash( $user->user_email );
				$comment_author_url = wp_slash( $user->user_url );
				$comment_content = trim( $_POST['comment'] );
				$comment_type = 'anspress';

			} else {
				$this->send( 'no_permission' );
			}

			$comment_parent = 0;

			if ( isset( $_POST['comment_ID'] ) ) {
				$comment_parent = absint( $_POST['comment_ID'] );
			}

			$commentdata = compact( 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID' );

			// Automatically approve parent comment.
			if ( ! empty( $_POST['approve_parent'] ) ) {
				$parent = get_comment( $comment_parent );
				if ( $parent && $parent->comment_approved === '0' && $parent->comment_post_ID == $comment_post_ID ) {
					if ( wp_set_comment_status( $parent->comment_ID, 'approve' ) ) {
						$comment_auto_approved = true; }
				}
			}

			$comment_id = wp_new_comment( $commentdata );

			if ( $comment_id > 0 ) {
				$comment = get_comment( $comment_id );
				do_action( 'ap_after_new_comment', $comment );
				ob_start();
				ap_comment( $comment );
				$html = ob_get_clean();
				$count = get_comment_count( $comment->comment_post_ID );
				$this->send( array( 'action' => 'new_comment', 'status' => true, 'comment_ID' => $comment->comment_ID, 'comment_post_ID' => $comment->comment_post_ID, 'comment_content' => $comment->comment_content, 'html' => $html, 'message' => 'comment_success', 'view' => array( 'comments_count_'.$comment->comment_post_ID => '('.$count['approved'].')', 'comment_count_label_'.$comment->comment_post_ID => sprintf( _n( 'One comment', '%d comments', $count['approved'], 'anspress-question-answer' ), $count['approved'] ) ) ) );
			}
		}
	}

	/**
	 * Load tinyMCE assets using ajax.
	 * @since 3.0.0
	 */
	public static function load_tinymce_assets() {
		$settings = ap_tinymce_editor_settings( 'answer' );

		if ( $settings['tinymce'] !== false ) {
			$settings['tinymce'] = array(
				'content_css' => ap_get_theme_url( 'css/editor.css' ),
				'wp_autoresize_on' => true,
			);
		}

		// Include mentions assets.
		if ( ! ap_opt('disable_mentions' ) ) {
			wp_enqueue_script( 'ap-mention-js', ap_get_theme_url( 'min/mention.js' ) , array( 'jquery' ), AP_VERSION, true );
			wp_enqueue_style( 'ap-mention-css', ap_get_theme_url( 'css/mention.css' ) , array(), AP_VERSION, true );
		}
		
		echo '<div class="ap-editor">';
	    wp_editor( '', 'description', $settings );
	    echo '</div>';
	    \_WP_Editors::enqueue_scripts();
	    ob_start();
		print_footer_scripts();
		$scripts = ob_get_clean();
		echo str_replace('jquery-core,jquery-migrate,', '', $scripts );
		\_WP_Editors::editor_js();
	    wp_die();
	}

	/**
	 * Handles ajax callback for list filter search.
	 * @since 3.0.0
	 */
	public static function filter_search() {
		$filter = ap_sanitize_unslash( 'filter', 'request' );
		$search_query = ap_sanitize_unslash( 'val', 'request' );
		do_action('ap_list_filter_search_'.$filter, $search_query );
	}

	/**
	 * Ajax callback for converting a question into a post.
	 * @since 3.0.0
	 */
	public static function convert_to_post() {
		if ( ! ap_verify_default_nonce() || ! is_super_admin( ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		$args = ap_sanitize_unslash( $_POST['args'] );

		$row = set_post_type( $args[0], 'post' );

		// After success trash all answers.
		if ( $row ) {
			global $wpdb;

			// Get IDs of all answer.
			$answer_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d and post_type = 'answer' ", (int) $args[0] ) );

			foreach ( (array) $answer_ids as $id ) {
				wp_delete_post( $id );
			}

			ap_ajax_json( [ 'do' => [ 'redirect' => get_permalink( $args[0] ) ] ] );
		}
	}

	public static function delete_attachment() {
		if ( ! ap_verify_default_nonce() || ! ap_user_can_upload_image() ) {
			ap_ajax_json('no_permission' );
		}

		$args = ap_sanitize_unslash( 'args', 'request' );

		// If user cannot delete then die.
		if ( ! ap_user_can_delete_attachment( $args[0] ) ) {
			ap_ajax_json('no_permission' );
		}

		$row = wp_delete_attachment( $args[0], true );

		if ( false !== $row ) {
			ap_ajax_json( array(
				'action' => 'delete_attachment',
				'attachment_id' => $args[0],
				'do' => array( 'remove_if_exists' => '#'.$args[0] ),
				'message' => __('Attachment deleted permanently','anspress-question-answer' ),
				'message_type' => 'success',
			) );
		}
	}
}
