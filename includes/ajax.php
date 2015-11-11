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

/**
 * Register all ajax callback
 */
class AnsPress_Ajax
{
	/**
	 * AnsPress main class
	 * @var object
	 */
	protected $ap;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 * @param AnsPress $ap Parent class object.
	 */
	public function __construct($ap) {

	    $ap->add_action( 'ap_ajax_suggest_similar_questions', $this, 'suggest_similar_questions' );
	    $ap->add_action( 'ap_ajax_load_comment_form', $this, 'load_comment_form' );
	    $ap->add_action( 'ap_ajax_delete_comment', $this, 'delete_comment' );
	    $ap->add_action( 'ap_ajax_select_best_answer', $this, 'select_best_answer' );
	    $ap->add_action( 'ap_ajax_delete_post', $this, 'delete_post' );
	    $ap->add_action( 'ap_ajax_permanent_delete_post', $this, 'permanent_delete_post' );
	    $ap->add_action( 'ap_ajax_change_post_status', $this, 'change_post_status' );
	    $ap->add_action( 'ap_ajax_load_user_field_form', $this, 'load_user_field_form' );
	    $ap->add_action( 'ap_ajax_set_featured', $this, 'set_featured' );
	    $ap->add_action( 'ap_ajax_follow', $this, 'follow' );
	    $ap->add_action( 'ap_ajax_user_cover', $this, 'ap_user_card' );
	    $ap->add_action( 'ap_ajax_delete_notification', $this, 'delete_notification' );
	    $ap->add_action( 'ap_ajax_markread_notification', $this, 'markread_notification' );
	    $ap->add_action( 'ap_ajax_set_notifications_as_read', $this, 'set_notifications_as_read' );
	    $ap->add_action( 'ap_ajax_flag_post', $this, 'flag_post' );
	    $ap->add_action( 'ap_ajax_subscribe', $this, 'subscribe' );
	    $ap->add_action( 'ap_ajax_vote', $this, 'vote' );
	    $ap->add_action( 'ap_ajax_flag_comment', $this, 'flag_comment' );
	    $ap->add_action( 'ap_ajax_delete_activity', $this, 'delete_activity' );
	}

	/**
	 * Show similar questions when asking a question.
	 *
	 * @since 2.0.1
	 */
	public function suggest_similar_questions() {

	    if ( empty( $_POST['value'] ) || ( ! ap_verify_default_nonce() && ! current_user_can( 'manage_options' ) ) ) {
	        die( 'false' );
	    }

	    $keyword = sanitize_text_field( wp_unslash( $_POST['value'] ) );

	    $is_admin = (bool) $_POST['is_admin'];

	    $questions = get_posts(array(
			'post_type' => 'question',
			'showposts' => 10,
			's' => $keyword,
		));

	    if ( $questions ) {

	        $items = '<div class="ap-similar-questions-head">';
	        $items .= '<h3>'.ap_icon( 'check', true ).sprintf( __( '%d similar questions found', 'ap' ), count( $questions ) ).'</h3>';
	        $items .= '<p>'.__( 'We\'ve found similar questions that have already been asked, click to read them.' ).'</p>';
	        $items .= '</div>';

		    $items .= '<div class="ap-similar-questions">';
	        foreach ( $questions as $p ) {
	            $count = ap_count_answer_meta( $p->ID );
	            $p->post_title = ap_highlight_words( $p->post_title, $keyword );

	            if ( $is_admin ) {
	            	$items .= '<div class="ap-q-suggestion-item clearfix"><a class="select-question-button button button-primary button-small" href="'.add_query_arg( array( 'post_type' => 'answer', 'post_parent' => $p->ID ), admin_url( 'post-new.php' ) ).'">'.__( 'Select', 'ap' ).'</a><span class="question-title">'.$p->post_title.'</span><span class="acount">'.sprintf( _n( '1 Answer', '%d Answers', $count, 'ap' ), $count ).'</span></div>';
	            } else {
		            $items .= '<a class="ap-sqitem clearfix" target="_blank" href="'.get_permalink( $p->ID ).'"><span class="acount">'.sprintf( _n( '1 Answer', '%d Answers', $count, 'ap' ), $count ).'</span><span class="ap-title">'.$p->post_title.'</span></a>';
		        }
	        }

	       	$items .= '</div>';

	        $result = array( 'status' => true, 'html' => $items );
	    } else {
	        $result = array( 'status' => false, 'message' => __( 'No related questions found', 'ap' ) );
	    }

	    $this->send( $result );
	}

	/**
	 * Return comment form.
	 *
	 * @since 2.0.1
	 */
	public function load_comment_form() {

	    $result = array(
			'ap_responce' => true,
			'action' => 'load_comment_form',
		);

		$comment_id = (int) $_POST['comment_ID'];

	    if ( ap_verify_nonce( 'comment_form_nonce' ) || ap_verify_nonce( 'edit_comment_'.$comment_id ) ) {
	        $comment_args = array();
	        $content = '';
	        $commentid = '';
	        if ( isset( $_REQUEST['comment_ID'] ) ) {
	            $comment = get_comment( $comment_id );
	            $comment_post_ID = $comment->comment_post_ID;
	            $nonce = wp_create_nonce( 'comment_'.$comment->comment_ID );
	            $comment_args['label_submit'] = __( 'Update comment', 'ap' );

	            $content = $comment->comment_content;
	            $commentid = '<input type="hidden" name="comment_ID" value="'.$comment->comment_ID.'"/>';
	        } else {
	            $comment_post_ID = (int) $_REQUEST['post'];
	            $nonce = wp_create_nonce( 'comment_'.(int) $_REQUEST['post'] );
	        }

	        ob_start();
	        include ap_get_theme_location( 'comment-form.php' );
	        $html = ob_get_clean();

	        $comment_args = array(
				'id_form' => 'ap-commentform',
				'title_reply' => '',
				'logged_in_as' => '',
				'comment_field' => $html.'<input type="hidden" name="ap_form_action" value="comment_form"/><input type="hidden" name="ap_ajax_action" value="comment_form"/><input type="hidden" name="__nonce" value="'.$nonce.'"/>'.$commentid,
				'comment_notes_after' => '',
			);

	        if ( isset( $_REQUEST['comment_ID'] ) ) {
	            $comment_args['label_submit'] = __( 'Update comment', 'ap' );
	        }

			// $current_user = get_userdata( get_current_user_id() );
			global $withcomments;
	        $withcomments = true;

	        $post = new WP_Query( array( 'p' => $comment_post_ID, 'post_type' => array( 'question', 'answer' ) ) );
	        $count = get_comment_count( $comment_post_ID );
	        ob_start();
	        if ( ! ap_opt( 'show_comments_by_default' ) ) {
	            echo '<div class="ap-comment-block clearfix">';
	        }

	        echo '<div class="ap-comment-form clearfix">';
	        echo '<div class="ap-comment-inner">';
	        comment_form( $comment_args, $comment_post_ID );
	        echo '</div>';
	        echo '</div>';

	        if ( ! ap_opt( 'show_comments_by_default' ) ) {
	            while ( $post->have_posts() ) {
	                $post->the_post();
	                comments_template();
	            }

	            wp_reset_postdata();
	        }

	        if ( ! ap_opt( 'show_comments_by_default' ) ) {
	            echo '</div>';
	        }

	        $result['html'] = ob_get_clean();
	        $result['container'] = '#comments-'.$comment_post_ID;
			// $result['message'] = 'success';
			$result['view_default'] = ap_opt( 'show_comments_by_default' );
	        $result['view'] = array( 'comments_count_'.$comment_post_ID => '('.$count['approved'].')', 'comment_count_label_'.$comment_post_ID => sprintf( _n( 'One comment', '%d comments', $count['approved'], 'ap' ), $count['approved'] ) );
	    } else {
	        $result['message'] = 'no_permission';
	    }

	    $this->send( $result );
	}

	/**
	 * Ajax action for deleting comment.
	 *
	 * @since 2.0.0
	 */
	public function delete_comment() {

	    $comment_id = (int) $_POST['comment_ID'];

	    if ( isset( $_POST['comment_ID'] ) && ap_user_can_delete_comment( $comment_id ) && wp_verify_nonce( $_POST['__nonce'], 'delete_comment' ) ) {
	        $comment = get_comment( $comment_id );

	        if ( time() > (get_comment_date( 'U', (int) $_POST['comment_ID'] ) + (int) ap_opt( 'disable_delete_after' )) && ! is_super_admin() ) {
	            ap_send_json( ap_ajax_responce( array( 'message_type' => 'warning', 'message' => sprintf( __( 'This post was created %s, its locked hence you cannot delete it.', 'ap' ), ap_human_time( get_comment_date( 'U', (int) $_POST['comment_ID'] ) ) ) ) ) );

	            return;
	        }

	        do_action( 'ap_unpublish_comment', $comment );

	        $delete = wp_delete_comment( (int) $_POST['comment_ID'], true );

	        if ( $delete ) {
	            do_action( 'ap_after_deleting_comment', $comment );
	            $count = get_comment_count( $comment->comment_post_ID );
	            $this->send( array(
	            	'action' 		=> 'delete_comment',
	            	'comment_ID' 	=> (int) $_POST['comment_ID'],
	            	'message' 		=> 'comment_delete_success',
	            	'view' 			=> array(
	            			'comments_count_'.$comment->comment_post_ID => '('.$count['approved'].')',
	            			'comment_count_label_'.$comment->comment_post_ID => sprintf( _n( 'One comment', '%d comments', $count['approved'], 'ap' ), $count['approved'] ),
	            		),
	            ) );
	        }
	        $this->something_wrong();
	    }
	    $this->send( 'no_permission' );
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

		if ( ! wp_verify_nonce( $_POST['__nonce'], $action ) || ! ap_user_can_delete( $post_id ) ) {
			$this->something_wrong();
		}

		$post = get_post( $post_id );

		if ( (time() > (get_the_time( 'U', $post->ID ) + (int) ap_opt( 'disable_delete_after' ))) && ! is_super_admin() ) {

			$this->send( array(
				'message_type' => 'warning',
				'message' => sprintf( __( 'This post was created %s, its locked hence you cannot delete it.','ap' ), ap_human_time( get_the_time( 'U', $post->ID ) ) ),
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
			$count_label = sprintf( _n( '1 Answer', '%d Answers', $current_ans, 'ap' ), $current_ans );
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
			$count_label = sprintf( _n( '1 Answer', '%d Answers', $current_ans, 'ap' ), $current_ans );
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

	            ap_add_history( get_current_user_id(), $post_id, '', 'status_updated' );

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
							'submit_button' => __( 'Update', 'ap' ),
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
		} else {
			$post = get_post( $post_id );
			$featured_questions = get_option( 'featured_questions' );

			if ( ($post->post_type == 'question') ) {
				if ( ! empty( $featured_questions ) && is_array( $featured_questions ) && in_array( $post->ID, $featured_questions ) ) {

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
						'html' 			=> __( 'Set as featured', 'ap' ),
					));
				} else {
					if ( empty( $featured_questions ) || ! is_array( $featured_questions ) || ! $featured_questions ) {
						$featured_questions = array( $post->ID );
					} else {
						$featured_questions[] = $post->ID;
					}

					update_option( 'featured_questions', $featured_questions );

					$this->send(array(
						'action' 		=> 'set_featured_question',
						'message' 		=> 'set_featured_question',
						'do' 			=> array( 'updateHtml' => '#set_featured_'.$post->ID ),
						'html' 			=> __( 'Unset as featured', 'ap' ),
					));
				}
			}
		}
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

		if ( $is_following ) {
			ap_remove_follower( $current_user_id, $user_to_follow );

			$this->send( array(
				'message' 		=> 'unfollow',
				'action' 		=> 'unfollow',
				'do' 			=> array( 'updateText' => array( '#follow_'.$user_to_follow, __( 'Follow', 'ap' ) ) ),
			) );
		} else {
			ap_add_follower( $current_user_id, $user_to_follow );

			$this->send( array(
				'message' 		=> 'follow',
				'action' 		=> 'follow',
				'do' 			=> array( 'updateText' => array( '#follow_'.$user_to_follow, __( 'Unfollow', 'ap' ) ) ),
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

		die();

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
			die();
		}

		if ( ! ap_verify_default_nonce() && ! is_user_logged_in() ) {
			die();
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

		die();
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
	        ap_add_flag( $userid, $post_id );

	        $count = ap_post_flag_count( $post_id );

			// Update post meta.
			update_post_meta( $post_id, ANSPRESS_FLAG_META, $count );
	        $this->send( array(
	        	'message' => 'flagged',
	        	'action' => 'flagged',
	        	'view' => array( $post_id.'_flag_count' => $count ),
	        	'count' => $count,
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

		if ( $is_subscribed ) {
			$row = ap_remove_subscriber( $action_id, $user_id, $subscribe_type );

			if ( false !== $row ) {
				$count = ap_subscribers_count( $action_id, $subscribe_type );
				$this->send( array(
					'message' 		=> 'unsubscribed',
					'action' 		=> 'unsubscribed',
					'do' 			=> array( 'updateHtml' => '#subscribe_'.$action_id.' .text' ),
					'count' 		=> $count,
					'html' 			=> __( 'Follow', 'ap' ),
					'view' 			=> array( 'subscribe_'.$action_id => $count ),
				) );
			}
		} else {

			$row = ap_new_subscriber( $user_id, $action_id, $subscribe_type, $question_id );

			$count = ap_subscribers_count( $action_id, $subscribe_type );
			if ( false !== $row ) {
				$this->send( array(
					'message' 		=> 'subscribed',
					'action' 		=> 'subscribed',
					'do' 			=> array( 'updateHtml' => '#subscribe_'.$action_id.' .text' ),
					'count' 		=> $count,
					'html' 			=> __( 'Unfollow', 'ap' ),
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
	        $this->something_wrong();
	    }

	    if ( ! is_user_logged_in() ) {
	        $this->send( 'please_login' );
	    }

	    $post = get_post( $post_id );
	    if ( $post->post_author == get_current_user_id() ) {
	        $this->send( 'cannot_vote_own_post' );
	    }

	    $type = sanitize_text_field( $_POST['type'] );

	    $type = ($type == 'up' ? 'vote_up' : 'vote_down');

	    if ( 'question' == $post->post_type && ap_opt( 'disable_down_vote_on_question' ) && 'vote_down' == $type ) {
	        $this->send( 'voting_down_disabled' );
	    } elseif ( 'answer' === $post->post_type && ap_opt( 'disable_down_vote_on_answer' ) && 'vote_down' === $type ) {
	        $this->send( 'voting_down_disabled' );
	    }

	    $userid = get_current_user_id();

	    $is_voted = ap_is_user_voted( $post_id, 'vote', $userid );

	    if ( is_object( $is_voted ) && $is_voted->count > 0 ) {
	        // If user already voted and click that again then reverse.
			if ( $is_voted->type == $type ) {
			    ap_remove_vote( $type, $userid, $post_id, $post->post_author );
			    $counts = ap_post_votes( $post_id );

				// Update post meta.
				update_post_meta( $post_id, ANSPRESS_VOTE_META, $counts['net_vote'] );

			    do_action( 'ap_undo_vote', $post_id, $counts );

			    $action = 'undo';
			    $count = $counts['net_vote'];
			    do_action( 'ap_undo_'.$type, $post_id, $counts );

			   	$this->send( array(
			   		'action' 	=> $action,
			   		'type' 		=> $type,
			   		'count' 	=> $count,
			   		'message' 	=> 'undo_vote',
			   	) );
			} else {
			    $this->send( 'undo_vote_your_vote' );
			}
	    } else {
	        ap_add_vote( $userid, $type, $post_id, $post->post_author );

	        $counts = ap_post_votes( $post_id );

			// Update post meta.
			update_post_meta( $post_id, ANSPRESS_VOTE_META, $counts['net_vote'] );
	        do_action( 'ap_'.$type, $post_id, $counts );

	        $action = 'voted';
	        $count = $counts['net_vote'];
	       	$this->send( array( 'action' => $action, 'type' => $type, 'count' => $count, 'message' => 'voted' ) );
	    }
	}

	/**
	 * Handle ajax callback if non-logged in user try to subscribe
	 */
	public function ap_add_to_subscribe_nopriv() {
		$this->send( array( 'action' => false, 'message' => __( 'Please login for adding question to your subscribe', 'ap' ) ) );
	}

	/**
	 * Ajax callback for processing comment flag button.
	 * @since 2.4
	 */
	public function flag_comment() {

	    $comment_id = (int) $_POST['comment_id'];
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
	        	'do' 		=> array('remove_if_exists' => '#activity-'.$activity_id ),
	        ) );
	    }

	    $this->something_wrong();
	}
}
