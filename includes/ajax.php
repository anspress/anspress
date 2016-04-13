<?php
/**
 * Register all ajax hooks.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
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
	    anspress()->add_action( 'ap_ajax_load_comment_form', 'AnsPress_Comment_Hooks', 'load_comment_form' );
	    anspress()->add_action( 'ap_ajax_delete_comment', 'AnsPress_Comment_Hooks', 'delete_comment' );
	    anspress()->add_action( 'ap_ajax_select_best_answer', $this, 'select_best_answer' );
	    anspress()->add_action( 'ap_ajax_delete_post', $this, 'delete_post' );
	    anspress()->add_action( 'ap_ajax_permanent_delete_post', $this, 'permanent_delete_post' );
	    anspress()->add_action( 'ap_ajax_change_post_status', $this, 'change_post_status' );
	    anspress()->add_action( 'ap_ajax_load_user_field_form', $this, 'load_user_field_form' );
	    anspress()->add_action( 'ap_ajax_set_featured', $this, 'set_featured' );
	    anspress()->add_action( 'ap_ajax_follow', $this, 'follow' );
	    anspress()->add_action( 'ap_ajax_user_cover', $this, 'ap_user_card' );
	    anspress()->add_action( 'ap_ajax_delete_notification', $this, 'delete_notification' );
	    anspress()->add_action( 'ap_ajax_markread_notification', $this, 'markread_notification' );
	    anspress()->add_action( 'ap_ajax_set_notifications_as_read', $this, 'set_notifications_as_read' );
	    anspress()->add_action( 'ap_ajax_flag_post', $this, 'flag_post' );
	    anspress()->add_action( 'ap_ajax_subscribe', $this, 'subscribe' );
	    anspress()->add_action( 'ap_ajax_vote', $this, 'vote' );
	    anspress()->add_action( 'ap_ajax_flag_comment', $this, 'flag_comment' );
	    anspress()->add_action( 'ap_ajax_delete_activity', $this, 'delete_activity' );
	    anspress()->add_action( 'ap_ajax_submit_comment', 'AnsPress_Comment_Hooks','submit_comment' );
	    anspress()->add_action( 'ap_ajax_approve_comment', 'AnsPress_Comment_Hooks','approve_comment' );
	}

	/**
	 * Show similar questions when asking a question.
	 *
	 * @since 2.0.1
	 */
	public function suggest_similar_questions() {
	    if ( empty( $_POST['value'] ) || ( ! ap_verify_default_nonce() && ! current_user_can( 'manage_options' ) ) ) {
	        wp_die( 'false' );
	    }

	    $keyword = sanitize_text_field( wp_unslash( $_POST['value'] ) );

	    $is_admin = (bool) ap_isset_post_value('is_admin', false);

	    $questions = get_posts(array(
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
	        $result = array( 'status' => false, 'message' => __( 'No related questions found', 'anspress-question-answer' ) );
	    }

	    ap_ajax_json( $result );
	}

	/**
	 * Ajax action for selecting a best answer.
	 *
	 * @since 2.0.0
	 */
	public function select_best_answer() {

	    $answer_id = (int) $_POST['answer_id'];

	    if ( ! is_user_logged_in() ) {
	        ap_send_json( ap_ajax_responce( 'no_permission' ) );

	        return;
	    }

	    if ( ! wp_verify_nonce( $_POST['__nonce'], 'answer-'.$answer_id ) ) {
	        $this->something_wrong();
	    }

	    $post = get_post( $answer_id );

	    if ( ap_question_best_answer_selected( $post->post_parent ) ) {
	        do_action( 'ap_unselect_answer', $post->post_author, $post->post_parent, $post->ID );

	        update_post_meta( $post->ID, ANSPRESS_BEST_META, 0 );

	        update_post_meta( $post->post_parent, ANSPRESS_SELECTED_META, false );

	        update_post_meta( $post->post_parent, ANSPRESS_UPDATED_META, current_time( 'mysql' ) );

	        if ( ap_opt( 'close_after_selecting' ) ) {
	            wp_update_post( array( 'ID' => $post->post_parent, 'post_status' => 'publish' ) );
	        }

	        ap_update_user_best_answers_count_meta( $post->post_author );
	        ap_update_user_solved_answers_count_meta( $post->post_author );

	        $this->send( array(
	        	'message' 	=> 'unselected_the_answer',
	        	'action' 	=> 'unselected_answer',
	        	'do' 		=> 'reload',
	        ) );

	    } else {
	        do_action( 'ap_select_answer', $post->post_author, $post->post_parent, $post->ID );
	        update_post_meta( $post->ID, ANSPRESS_BEST_META, 1 );
	        update_post_meta( $post->post_parent, ANSPRESS_SELECTED_META, $post->ID );
	        update_post_meta( $post->post_parent, ANSPRESS_UPDATED_META, current_time( 'mysql' ) );

	        if ( ap_opt( 'close_after_selecting' ) ) {
	            wp_update_post( array( 'ID' => $post->post_parent, 'post_status' => 'closed' ) );
	        }

	        ap_update_user_best_answers_count_meta( $post->post_author );
	        ap_update_user_solved_answers_count_meta( $post->post_author );

	        $html = ap_select_answer_btn_html( $answer_id );
	        $this->send( array(
	        	'message' 	=> 'selected_the_answer',
	        	'action' 	=> 'selected_answer',
	        	'do' 		=> 'reload',
	        	'html' 		=> $html,
	        ) );
	    }
	}

	/**
	 * Process ajax trash posts callback
	 */
	public function delete_post() {

		$post_id = (int) $_POST['post_id'];

		$action = 'delete_post_'.$post_id;

		if ( ! wp_verify_nonce( $_POST['__nonce'], $action ) || ! ap_user_can_delete_post( $post_id ) ) {
			$this->something_wrong();
		}

		$post = get_post( $post_id );

		if ( (time() > (get_the_time( 'U', $post->ID ) + (int) ap_opt( 'disable_delete_after' ))) && ! is_super_admin() ) {

			$this->send( array(
				'message_type' => 'warning',
				'message' => sprintf( __( 'This post was created %s, its locked hence you cannot delete it.','anspress-question-answer' ), ap_human_time( get_the_time( 'U', $post->ID ) ) ),
			) );
		}

		wp_trash_post( $post_id );

		if ( $post->post_type == 'question' ) {
			do_action( 'ap_wp_trash_question', $post_id );
			$this->send( array(
				'action' 		=> 'delete_question',
				'do' 			=> array( 'redirect' => ap_base_page_link() ),
				'message' 		=> 'question_moved_to_trash',
			) );
		} else {
			do_action( 'ap_wp_trash_answer', $post_id );
			$current_ans = ap_count_published_answers( $post->post_parent );
			$count_label = sprintf( _n( '1 Answer', '%d Answers', $current_ans, 'anspress-question-answer' ), $current_ans );
			$remove = ( ! $current_ans ? true : false);
			$this->send(array(
				'action' 		=> 'delete_answer',
				'div_id' 		=> '#answer_'.$post_id,
				'count' 		=> $current_ans,
				'count_label' 	=> $count_label,
				'remove' 		=> $remove,
				'message' 		=> 'answer_moved_to_trash',
				'view' 			=> array( 'answer_count' => $current_ans, 'answer_count_label' => $count_label ),
			));
		}
	}

	/**
	 * Handle Ajax callback for permanent delete of post.
	 */
	public function permanent_delete_post() {
		$post_id = (int) $_POST['post_id'];

		$action = 'delete_post_'.$post_id;

		if ( ! ap_verify_nonce( $action ) || ! ap_user_can_permanent_delete() ) {
			$this->something_wrong();
		}

		$post = get_post( $post_id );

		wp_trash_post( $post_id );

		if ( $post->post_type == 'question' ) {
			do_action( 'ap_wp_trash_question', $post_id );
		} else {
			do_action( 'ap_wp_trash_answer', $post_id );
		}

		wp_delete_post( $post_id, true );

		if ( $post->post_type == 'question' ) {
			$this->send( array(
				'action' 		=> 'delete_question',
				'do' 			=> array( 'redirect' => ap_base_page_link() ),
				'message' 		=> 'question_deleted_permanently',
			) );
		} else {
			$current_ans = ap_count_published_answers( $post->post_parent );
			$count_label = sprintf( _n( '1 Answer', '%d Answers', $current_ans, 'anspress-question-answer' ), $current_ans );
			$remove = ( ! $current_ans ? true : false);
			$this->send(array(
				'action' 		=> 'delete_answer',
				'div_id' 		=> '#answer_'.$post_id,
				'count' 		=> $current_ans,
				'count_label' 	=> $count_label,
				'remove' 		=> $remove,
				'message' 		=> 'answer_deleted_permanently',
				'view' 			=> array( 'answer_count' => $current_ans, 'answer_count_label' => $count_label ),
			));
		}
	}

	/**
	 * Handle change post status request.
	 * @since 2.1
	 */
	public function change_post_status() {
	    $post_id = (int) $_POST['post_id'];
	    $status = sanitize_text_field( wp_unslash( $_POST['status'] ) );

	    if ( ! is_user_logged_in() || ! ap_verify_nonce( 'change_post_status_'.$post_id ) || ! ap_user_can_change_status( $post_id ) ) {
	        $this->send( 'no_permission' );
	    } else {

	        $post = get_post( $post_id );
	        if ( ($post->post_type == 'question' || $post->post_type == 'answer') && $post->post_status != $status ) {

	           	$update_data = array();

	            if ( 'publish' == $status ) {
	                $update_data['post_status'] = 'publish';
	            } elseif ( 'moderate' == $status ) {
	                $update_data['post_status'] = 'moderate';
	            } elseif ( 'private_post' == $status ) {
	                $update_data['post_status'] = 'private_post';
	            } elseif ( 'closed' == $status ) {
	                $update_data['post_status'] = 'closed';
	            }

				// Unregister history action for edit.
				remove_action( 'ap_after_new_answer', array( 'AP_History', 'new_answer' ) );
	            remove_action( 'ap_after_new_question', array( 'AP_History', 'new_question' ) );

	            $update_data['ID'] = $post->ID;
	            wp_update_post( $update_data );

	            // ap_add_history( get_current_user_id(), $post_id, '', 'status_updated' );
	            add_action( 'ap_post_status_updated', $post->ID );

	            ob_start();
	            ap_post_status_description( $post->ID );
	            $html = ob_get_clean();

	            $this->send(array(
					'action' 		=> 'status_updated',
					'message' 		=> 'status_updated',
					'do' 			=> array(
						'remove_if_exists' => '#ap_post_status_desc_'.$post->ID,
						'toggle_active_class' => array( '#ap_post_status_toggle_'.$post->ID, '.'.$status ),
						'append_before' => '#ap_post_actions_'.$post->ID,
					),
					'html' 			=> $html,
				));
	        }
	    }
	    $this->something_wrong();
	}

	/**
	 * Load user profile field form
	 */
	public function load_user_field_form() {
		$user_id = get_current_user_id();
		$field_name = sanitize_text_field( wp_unslash( $_POST['field'] ) );

		if ( ! is_user_logged_in() || ! ap_verify_nonce( 'user_field_form_'.$field_name.'_'.$user_id ) ) {
			$this->send( 'no_permission' );
		} else {
			if ( ap_has_users( array( 'ID' => $user_id ) ) ) {
				while ( ap_users() ) {
					ap_the_user();
					$form = ap_user_get_fields(array(
						'show_only' => $field_name,
						'form' 		=> array(
							'field_hidden' 	=> false,
							'hide_footer' 	=> false,
							'show_cancel' 	=> true,
							'is_ajaxified' 	=> true,
							'submit_button' => __( 'Update', 'anspress-question-answer' ),
						),
					));

					$this->send(array(
						'action' 	=> 'user_field_form_loaded',
						'do' 		=> array( 'updateHtml' => '#user_field_form_'.$field_name ),
						'html' 		=> $form->get_form(),
					));
				}
			}
		}
		$this->something_wrong();
	}

	/**
	 * Handle set feature and unfeature ajax callback
	 */
	public function set_featured() {
		$post_id = (int) $_POST['post_id'];

		if ( ! is_super_admin() || ! ap_verify_nonce( 'set_featured_'.$post_id ) ) {
			$this->send( 'no_permission' );
		}

		$post = get_post( $post_id );
		$featured_questions = (array) get_option( 'featured_questions' );

		// Do nothing if post type is not question.
		if ( $post->post_type != 'question' ) {
			$this->something_wrong();
		}

		if ( ! empty( $featured_questions ) && in_array( $post->ID, $featured_questions ) ) {

			foreach ( $featured_questions as $key => $q ) {
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
	public function ap_user_card() {
		if ( ap_opt( 'disable_hover_card' ) ) {
			$this->something_wrong();
		}

		$user_id = (int) $_POST['user_id'];

		if ( ! ap_verify_default_nonce() ) {
			$this->something_wrong();
		}

		global $ap_user_query;
		$ap_user_query = ap_has_users( array( 'ID' => $user_id ) );

		if ( $ap_user_query->has_users() ) {
			while ( ap_users() ) :
				ap_the_user();
				ap_get_template_part( 'user/user-card' );
			endwhile;
		}

		wp_die();
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

		$id = (int) $_POST['id'];

		if ( isset( $_POST['id'] ) && ! ap_verify_nonce( 'ap_markread_notification_'.$id ) && ! is_user_logged_in() ) {
			$this->something_wrong();
		} elseif ( ! ap_verify_nonce( 'ap_markread_notification_'.get_current_user_id() ) && ! is_user_logged_in() ) {
			$this->something_wrong();
		}

		if ( isset( $_POST['id'] ) ) {
			$notification = ap_get_notification_by_id( $id );

			if ( $notification && ( get_current_user_id() == $notification->noti_user_id || is_super_admin()) ) {

				$row = ap_update_notification( array( 'noti_id' => $id, 'noti_user_id' => get_current_user_id() ), array( 'noti_status' => 1 ) );

				if ( false !== $row ) {
					$this->send( array(
						'message' 		=> 'mark_read_notification',
						'action' 		=> 'mark_read_notification',
						'container' 	=> '.ap-notification-'.$notification->noti_id,
						'view' 			=> array( 'notification_count' => ap_get_total_unread_notification() ),
					) );
				}
			}
		} else {
			$row = ap_notification_mark_all_read( get_current_user_id() );

			if ( false !== $row ) {
				$this->send( array(
					'message' 	=> 'mark_read_notification',
					'action' 	=> 'mark_all_read',
					'container' => '#ap-notification-dropdown',
					'view' 		=> array( 'notification_count' => '0' ),
				) );
			}
		}

		$this->something_wrong();
	}

	/**
	 * Handle ajax callback for mark all notification as read
	 */
	public function set_notifications_as_read() {

		$ids = sanitize_text_field( $_POST['ids'] );
		$ids = explode( ',', $ids );

		if ( count( $ids ) == 0 ) {
			wp_die();
		}

		if ( ! ap_verify_default_nonce() && ! is_user_logged_in() ) {
			wp_die();
		}

		foreach ( $ids as $id ) {
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
	 * Ajax callback to process post flag button
	 * @since 2.0.0
	 */
	public function flag_post() {
	    $post_id = (int) $_POST['args'][0];

	    if ( ! ap_verify_nonce( 'flag_'.$post_id ) || ! is_user_logged_in() ) {
	        $this->something_wrong();
	    }

	    $userid = get_current_user_id();
	    $is_flagged = ap_is_user_flagged( $post_id );

	    if ( $is_flagged ) {
	        $this->send( array( 'message' => 'already_flagged' ) );
	    } else {
	        $count = ap_count_flag_vote( 'flag', $post_id );
	        ap_add_flag( $userid, $post_id );

	        $new_count = $count + 1;
			// Update post meta.
			update_post_meta( $post_id, ANSPRESS_FLAG_META, $new_count );
	        $this->send( array(
	        	'message' => 'flagged',
	        	'action' => 'flagged',
	        	'view' => array( $post_id.'_flag_count' => $new_count ),
	        	'count' => $new_count,
	        ) );
	    }

	    $this->something_wrong();
	}

	/**
	 * Process ajax subscribe request.
	 */
	public function subscribe() {
		$action_id = (int) $_POST['args'][0];

		$type = sanitize_text_field( $_POST['args'][1] );

		if ( ! ap_verify_nonce( 'subscribe_'.$action_id.'_'.$type ) ) {
			$this->something_wrong();
		}

		if ( ! is_user_logged_in() ) {
			$this->send( 'please_login' );
		}

		$question_id = 0;

		if ( 'tax_new_q' === $type ) {
			$subscribe_type = 'tax_new_q';
		} else {
			$subscribe_type = 'q_all';
			$question_id = $action_id;
		}

		$user_id = get_current_user_id();

		$is_subscribed = ap_is_user_subscribed( $action_id, $subscribe_type, $user_id );

		$elm = '#subscribe_'.$action_id.' .ap-btn';

		if ( $is_subscribed ) {
			$row = ap_remove_subscriber( $action_id, $user_id, $subscribe_type );

			if ( false !== $row ) {
				$count = ap_subscribers_count( $action_id, $subscribe_type );
				$this->send( array(
					'message' 		=> 'unsubscribed',
					'action' 		=> 'unsubscribed',
					'do' 			=> array( 'updateHtml' => $elm.' .text', 'toggle_active_class' => $elm ),
					'count' 		=> $count,
					'html' 			=> __( 'Follow', 'anspress-question-answer' ),
					'view' 			=> array( 'subscribe_'.$action_id => $count ),
				) );
			}
		} else {

			$row = ap_new_subscriber( $user_id, $action_id, $subscribe_type, $question_id );

			if ( false !== $row ) {
				$count = ap_subscribers_count( $action_id, $subscribe_type );
				$this->send( array(
					'message' 		=> 'subscribed',
					'action' 		=> 'subscribed',
					'do' 			=> array( 'updateHtml' => '#subscribe_'.$action_id.' .text', 'toggle_active_class' => $elm ),
					'count' 		=> $count,
					'html' 			=> __( 'Unfollow', 'anspress-question-answer' ),
					'view' 			=> array( 'subscribe_'.$action_id => $count ),
				) );
			}
		}

		$this->something_wrong();
	}

	/**
	 * Process voting button.
	 * @since 2.0.1.1
	 */
	public function vote() {
	    $post_id = (int) $_POST['post_id'];

	    if ( ! ap_verify_nonce( 'vote_'.$post_id ) ) {
	        ap_ajax_json('something_wrong');
	    }

	    $type = sanitize_text_field( $_POST['type'] );
	    $type = ($type == 'up' ? 'vote_up' : 'vote_down');

	    $userid = get_current_user_id();

	    $post = get_post( $post_id );

	    $thing = ap_user_can_vote_on_post( $post_id, $type, $userid, true );

	    // Check if WP_Error object and send error message code.
	    if ( is_wp_error( $thing ) ) {
	        ap_ajax_json( $thing->get_error_code() );
	    }

	    if ( 'question' == $post->post_type && ap_opt( 'disable_down_vote_on_question' ) && 'vote_down' == $type ) {
	        ap_ajax_json( 'voting_down_disabled' );
	    } elseif ( 'answer' === $post->post_type && ap_opt( 'disable_down_vote_on_answer' ) && 'vote_down' === $type ) {
	        ap_ajax_json( 'voting_down_disabled' );
	    }

	    $is_voted = ap_is_user_voted( $post_id, 'vote', $userid );

	    if ( is_object( $is_voted ) && $is_voted->count > 0 ) {
	        // If user already voted and click that again then reverse.
			if ( $is_voted->type == $type ) {
			    $counts = ap_remove_post_vote( $type, $userid, $post_id, $post->post_author );

				// Update post meta.
				update_post_meta( $post_id, ANSPRESS_VOTE_META, $counts['net_vote'] );

			    do_action( 'ap_undo_vote', $post_id, $counts );
			    do_action( 'ap_undo_'.$type, $post_id, $counts );

			   	ap_ajax_json( array(
			   		'action' 	=> 'undo',
			   		'type' 		=> $type,
			   		'count' 	=> $counts['net_vote'],
			   		'message' 	=> 'undo_vote',
			   	) );
			} else {
			    ap_ajax_json( 'undo_vote_your_vote' );
			}
	    } else {
	        $counts = ap_add_post_vote( $userid, $type, $post_id, $post->post_author );
			// Update post meta.
	        do_action( 'ap_'.$type, $post_id, $counts );
	       	ap_ajax_json( array( 'action' => 'voted', 'type' => $type, 'count' => $counts['net_vote'], 'message' => 'voted' ) );
	    }
	}

	/**
	 * Handle ajax callback if non-logged in user try to subscribe
	 */
	public function ap_add_to_subscribe_nopriv() {
		$this->send( array( 'action' => false, 'message' => __( 'Please login for adding question to your subscribe', 'anspress-question-answer' ) ) );
	}

	/**
	 * Ajax callback for processing comment flag button.
	 * @since 2.4
	 */
	public function flag_comment() {
		$args = $_POST['args'];

	    $comment_id = (int) $args[0];
	    if ( ! ap_verify_nonce( 'flag_'. $comment_id ) || ! is_user_logged_in() ) {
	        $this->something_wrong();
	    }

	    $userid = get_current_user_id();
	    $is_flagged = ap_is_user_flagged_comment( $comment_id );

	    if ( $is_flagged ) {
	        ap_send_json( ap_ajax_responce( array( 'message' => 'already_flagged_comment' ) ) );
	    } else {
	        ap_insert_comment_flag( $userid, $comment_id );

	        $count = ap_comment_flag_count( $comment_id );

	        update_comment_meta( $comment_id, ANSPRESS_FLAG_META, $count );

	        $this->send( array(
	        	'message' 	=> 'flagged_comment',
	        	'action' 	=> 'flagged',
	        	'view' 		=> array( $comment_id.'_comment_flag' => $count ),
	        	'count' 	=> $count,
	        ) );
	    }
	    $this->something_wrong();
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
}
