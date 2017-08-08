<?php
/**
 * All Hooks of AnsPress
 *
 * @package	  AnsPress
 * @author		Rahul Aryan <support@anspress.io>
 * @license	  GPL-3.0+
 * @link			https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register common anspress hooks
 */
class AnsPress_Hooks {

	/**
	 * Menu class.
	 *
	 * @var string
	 */
	static $menu_class = '';
	/**
	 * Initialize the class
	 *
	 * @since 2.0.1
	 * @since 2.4.8 Removed `$ap` argument.
	 */
	public static function init() {
			anspress()->add_action( 'registered_taxonomy', __CLASS__, 'add_ap_tables' );
			anspress()->add_action( 'ap_processed_new_question', __CLASS__, 'after_new_question', 1, 2 );
			anspress()->add_action( 'ap_processed_new_answer', __CLASS__, 'after_new_answer', 1, 2 );
			anspress()->add_action( 'before_delete_post', __CLASS__, 'before_delete' );
			anspress()->add_action( 'wp_trash_post', __CLASS__, 'trash_post_action' );
			anspress()->add_action( 'untrash_post', __CLASS__, 'untrash_ans_on_question_untrash' );
			anspress()->add_action( 'comment_post', __CLASS__, 'new_comment_approve', 10, 2 );
			anspress()->add_action( 'comment_unapproved_to_approved', __CLASS__, 'comment_approve' );
			anspress()->add_action( 'comment_approved_to_unapproved', __CLASS__, 'comment_unapprove' );
			anspress()->add_action( 'trashed_comment', __CLASS__, 'comment_trash' );
			anspress()->add_action( 'delete_comment ', __CLASS__, 'comment_trash' );
			anspress()->add_action( 'edit_comment ', __CLASS__, 'edit_comment' );
			anspress()->add_action( 'ap_publish_comment', __CLASS__, 'publish_comment' );
			anspress()->add_action( 'ap_unpublish_comment', __CLASS__, 'unpublish_comment' );
			anspress()->add_action( 'wp_loaded', __CLASS__, 'flush_rules' );
			anspress()->add_action( 'safe_style_css', __CLASS__, 'safe_style_css', 11 );
			anspress()->add_action( 'save_post', __CLASS__, 'base_page_update', 10, 2 );
			anspress()->add_action( 'save_post', __CLASS__, 'question_answer_hooks', 1, 3 );
			anspress()->add_action( 'ap_vote_casted', __CLASS__, 'update_user_vote_casted_count', 10, 4 );
			anspress()->add_action( 'ap_vote_removed', __CLASS__, 'update_user_vote_casted_count' , 10, 4 );
			anspress()->add_action( 'the_post', __CLASS__, 'filter_page_title' );
			anspress()->add_action( 'ap_new_subscriber', __CLASS__, 'new_subscriber', 10, 4 );
			anspress()->add_action( 'ap_delete_subscribers', __CLASS__, 'delete_subscriber', 10, 4 );
			anspress()->add_action( 'ap_display_question_metas', __CLASS__, 'display_question_metas', 100, 2 );
			anspress()->add_action( 'widget_comments_args', __CLASS__, 'widget_comments_args' );

			anspress()->add_filter( 'posts_clauses', 'AP_QA_Query_Hooks', 'sql_filter', 1, 2 );
			anspress()->add_filter( 'posts_results', 'AP_QA_Query_Hooks', 'posts_results', 1, 2 );
			anspress()->add_filter( 'posts_pre_query', 'AP_QA_Query_Hooks', 'posts_pre_query', 1, 2 );

			// Theme	hooks.
			anspress()->add_action( 'init', 'AnsPress_Theme', 'init_actions' );
			anspress()->add_filter( 'post_class', 'AnsPress_Theme', 'question_answer_post_class' );
			anspress()->add_filter( 'body_class', 'AnsPress_Theme', 'body_class' );
			anspress()->add_action( 'after_setup_theme', 'AnsPress_Theme', 'includes_theme' );
			anspress()->add_filter( 'wpseo_title', 'AnsPress_Theme', 'wpseo_title' , 10, 2 );
			anspress()->add_filter( 'wp_head', 'AnsPress_Theme', 'feed_link', 9 );
			anspress()->add_filter( 'wpseo_canonical', 'AnsPress_Theme', 'wpseo_canonical' );
			anspress()->add_action( 'ap_before', 'AnsPress_Theme', 'ap_before_html_body' );
			anspress()->add_action( 'wp', 'AnsPress_Theme', 'remove_head_items', 10 );
			anspress()->add_action( 'wp_head', 'AnsPress_Theme', 'wp_head', 11 );
			anspress()->add_action( 'ap_after_question_content', 'AnsPress_Theme', 'question_attachments', 11 );
			anspress()->add_action( 'ap_after_answer_content', 'AnsPress_Theme', 'question_attachments', 11 );

			anspress()->add_filter( 'wp_get_nav_menu_items', __CLASS__, 'update_menu_url' );
			anspress()->add_filter( 'nav_menu_css_class', __CLASS__, 'fix_nav_current_class', 10, 2 );
			anspress()->add_filter( 'mce_buttons', __CLASS__, 'editor_buttons', 10, 2 );
			anspress()->add_filter( 'wp_insert_post_data', __CLASS__, 'wp_insert_post_data', 1000, 2 );
			anspress()->add_filter( 'ap_form_contents_filter', __CLASS__, 'sanitize_description' );
			anspress()->add_filter( 'human_time_diff', __CLASS__, 'human_time_diff' );
			anspress()->add_filter( 'comments_template_query_args', 'AnsPress_Comment_Hooks', 'comments_template_query_args' );
			anspress()->add_filter( 'get_comment_link', 'AnsPress_Comment_Hooks', 'comment_link', 10, 3 );
			anspress()->add_filter( 'template_include', 'AnsPress_Theme', 'anspress_basepage_template' );

			// Common pages hooks.
			anspress()->add_action( 'init', 'AnsPress_Common_Pages', 'register_common_pages' );

			// Register post ststus.
			anspress()->add_action( 'init', 'AnsPress_Post_Status', 'register_post_status' );

			// Rewrite rules hooks.
			anspress()->add_filter( 'query_vars', 'AnsPress_Rewrite', 'query_var' );
			anspress()->add_action( 'generate_rewrite_rules', 'AnsPress_Rewrite', 'rewrites', 1 );
			anspress()->add_filter( 'paginate_links', 'AnsPress_Rewrite', 'bp_com_paged' );
			anspress()->add_filter( 'parse_request', 'AnsPress_Rewrite', 'add_query_var' );
			anspress()->add_action( 'template_redirect', 'AnsPress_Rewrite', 'shortlink' );

			// Upload hooks.
			anspress()->add_action( 'before_delete_post', 'AnsPress_Uploader', 'before_delete_attachment' );
			anspress()->add_action( 'init', 'AnsPress_Uploader', 'create_single_schedule' );
			anspress()->add_action( 'ap_delete_temp_attachments', 'AnsPress_Uploader', 'cron_delete_temp_attachments' );

			// Vote hooks.
			anspress()->add_action( 'ap_before_delete_question', 'AnsPress_Vote', 'delete_votes' );
			anspress()->add_action( 'ap_before_delete_answer', 'AnsPress_Vote', 'delete_votes' );
			anspress()->add_action( 'ap_deleted_votes', 'AnsPress_Vote', 'ap_deleted_votes', 10, 2 );
	}

	/**
	 * Add AnsPress tables in $wpdb.
	 */
	public static function add_ap_tables() {
			ap_append_table_names();
	}

	/**
	 * Things to do after creating a question
	 *
	 * @param	integer $post_id Question id.
	 * @param	object	$post Question post object.
	 * @since	1.0
	 */
	public static function after_new_question( $post_id, $post ) {

		// Add question activity meta.
		ap_update_post_activity_meta( $post_id, 'new_question', $post->post_author );

		/**
		 * ACTION: ap_after_new_question
		 * action triggered after inserting a question
		 *
		 * @since 0.9
		 */
		do_action( 'ap_after_new_question', $post_id, $post );
	}

	/**
	 * Things to do after creating an answer
	 *
	 * @param	integer $post_id answer id.
	 * @param	object	$post answer post object.
	 * @since 2.0.1
	 */
	public static function after_new_answer( $post_id, $post ) {
		// Update answer count.
		ap_update_answers_count( $post->post_parent );

		// Update activities in qameta.
		ap_update_post_activity_meta( $post_id, 'new_answer', $post->post_author );
		ap_update_post_activity_meta( $post->post_parent, 'new_answer', $post->post_author );

		/**
		 * ACTION: ap_after_new_answer
		 * action triggered after inserting an answer
		 *
		 * @since 0.9
		 */
		do_action( 'ap_after_new_answer', $post_id, $post );
	}

	/**
	 * Before deleting a question or answer.
	 *
	 * @param	integer $post_id Question or answer ID.
	 */
	public static function before_delete( $post_id ) {

		$post = ap_get_post( $post_id );
		if ( 'question' === $post->post_type ) {
			do_action( 'ap_before_delete_question', $post->ID, $post );
			$answers = get_posts( [ 'post_parent' => $post->ID, 'post_type' => 'answer' ] ); // @codingStandardsIgnoreLine

			foreach ( (array) $answers as $a ) {
				SELF::delete_answer( $a->ID, $a );
				wp_delete_post( $a->ID, true );
			}

			// Delete qameta.
			ap_delete_qameta( $post->ID );

		} elseif ( 'answer' === $post->post_type ) {
				SELF::delete_answer( $post_id, $post );
		}
	}

	/**
	 * Delete answer.
	 *
	 * @param	integer $post_id Question or answer ID.
	 * @param	object  $post Post Object.
	 */
	public static function delete_answer( $post_id, $post ) {
		do_action( 'ap_before_delete_answer', $post->ID, $post );
		ap_update_post_activity_meta( $post->post_parent, 'delete_answer', get_current_user_id() );

		if ( ap_is_selected( $post ) ) {
			ap_unset_selected_answer( $post->post_parent );
		}

		// Delete qameta.
		ap_delete_qameta( $post->ID );
	}

	/**
	 * If a question is sent to trash, then move its answers to trash as well
	 *
	 * @param	integer $post_id Post ID.
	 * @since 2.0.0
	 */
	public static function trash_post_action( $post_id ) {
		$post = ap_get_post( $post_id );

		if ( 'question' === $post->post_type ) {
			do_action( 'ap_trash_question', $post->ID, $post );

			// Save current post status so that it can be restored.
			update_post_meta( $post->ID, '_ap_last_post_status', $post->post_status );

			//@codingStandardsIgnoreStart
			$ans = get_posts( array(
				'post_type'   => 'answer',
				'post_status' => 'publish',
				'post_parent' => $post_id,
				'showposts'   => -1,
			));
			//@codingStandardsIgnoreEnd

			foreach ( (array) $ans as $p ) {
				$selcted_answer = ap_selected_answer();
				if ( $selcted_answer === $p->ID ) {
					ap_unset_selected_answer( $p->post_parent );
				}

				ap_update_post_activity_meta( $p->ID, 'delete_answer', get_current_user_id(), true );
				wp_trash_post( $p->ID );
			}
		}

		if ( 'answer' === $post->post_type ) {
			ap_update_post_activity_meta( $post->ID, 'delete_answer', get_current_user_id(), true );

			/**
			 * Triggered before trashing an answer.
			 *
			 * @param integer $post_id Answer ID.
			 * @param object $post Post object.
			 */
			do_action( 'ap_trash_answer', $post->ID, $post );

			// Save current post status so that it can be restored.
			update_post_meta( $post->ID, '_ap_last_post_status', $post->post_status );

			ap_update_answers_count( $post->post_parent );
		}
	}

	/**
	 * If questions is restored then restore its answers too.
	 *
	 * @param	integer $post_id Post ID.
	 * @since 2.0.0
	 */
	public static function untrash_ans_on_question_untrash( $post_id ) {
		$_post = ap_get_post( $post_id );

		if ( 'question' === $_post->post_type ) {
			do_action( 'ap_untrash_question', $_post->ID, $_post );
			//@codingStandardsIgnoreStart
			$ans = get_posts( array(
				'post_type'   => 'answer',
				'post_status' => 'trash',
				'post_parent' => $post_id,
				'showposts'   => -1,
			));
			//@codingStandardsIgnoreStart

			foreach ( (array) $ans as $p ) {
				//do_action( 'ap_untrash_answer', $p->ID, $p );
				wp_untrash_post( $p->ID );
			}

			ap_update_post_activity_meta( $_post->post_parent, 'restore_question', get_current_user_id() );
		}

		if ( 'answer' === $_post->post_type ) {
			$ans = ap_count_published_answers( $_post->post_parent );
			ap_update_post_activity_meta( $_post->post_parent, 'restore_answer', get_current_user_id(), true );
			do_action( 'ap_untrash_answer', $_post->ID, $_post );

			// Update answer count.
			ap_update_answers_count( $_post->post_parent, $ans + 1 );
		}
	}

	/**
	 * Used to create an action when comment publishes.
	 *
	 * @param	integer			 $comment_id Comment ID.
	 * @param	integer|false $approved	 1 if comment is approved else false.
	 */
	public static function new_comment_approve( $comment_id, $approved ) {
		if ( 1 === $approved ) {
			$comment = get_comment( $comment_id );

			$post = ap_get_post( $comment->comment_post_ID );

			if( ! in_array( $post->post_type, [ 'answer', 'question' ], true ) ) {
				return;
			}

			do_action( 'ap_publish_comment', $comment );
		}
	}

	/**
	 * Used to create an action when comment get approved.
	 *
	 * @param	array|object $comment Comment object.
	 */
	public static function comment_approve( $comment ) {
		$post = ap_get_post( $comment->comment_post_ID );

		if( ! in_array( $post->post_type, [ 'answer', 'question' ], true ) ) {
			return;
		}

		do_action( 'ap_publish_comment', $comment );
	}

	/**
	 * Used to create an action when comment get unapproved.
	 *
	 * @param	array|object $comment Comment object.
	 */
	public static function comment_unapprove( $comment ) {
		$post = ap_get_post( $comment->comment_post_ID );

		if( ! in_array( $post->post_type, [ 'answer', 'question' ], true ) ) {
			return;
		}

		do_action( 'ap_unpublish_comment', $comment );
	}

	/**
	 * Used to create an action when comment get trashed.
	 *
	 * @param	integer $comment_id Comment ID.
	 */
	public static function comment_trash( $comment_id ) {
		$comment = get_comment( $comment_id );
		$post = ap_get_post( $comment->comment_post_ID );

		if( ! in_array( $post->post_type, [ 'answer', 'question' ], true ) ) {
			return;
		}
		do_action( 'ap_unpublish_comment', $comment );
	}

	/**
	 * Actions to run after posting a comment
	 *
	 * @param	object|array $comment Comment object.
	 */
	public static function publish_comment( $comment ) {
		$comment = (object) $comment;

		$post = ap_get_post( $comment->comment_post_ID );

		if ( ! in_array( $post->post_type, [ 'question', 'answer' ], true ) ) {
			return false;
		}

		if ( $post->post_type == 'question' ) {
			ap_update_post_activity_meta( $comment->comment_post_ID, 'new_comment', $comment->user_id );
		} elseif ( $post->post_type == 'answer' ) {
			ap_update_post_activity_meta( $comment->comment_post_ID, 'new_comment_answer', $comment->user_id, true );
		}

		$count = get_comment_count( $comment->comment_post_ID );
		ap_insert_qameta( $comment->comment_post_ID, [ 'fields' => [ 'unapproved_comments' => $count['awaiting_moderation'] ] ] );
	}

	/**
	 * Actions to run after unpublishing a comment.
	 *
	 * @param	object|array $comment Comment object.
	 */
	public static function unpublish_comment( $comment ) {
		$comment = (object) $comment;
		ap_update_post_activity_meta( $comment->comment_post_ID, 'delete_comment', get_current_user_id(), true );

		$count = get_comment_count( $comment->comment_post_ID );
		ap_insert_qameta( $comment->comment_post_ID, [ 'fields' => [ 'unapproved_comments' => $count['awaiting_moderation'] ] ] );
	}

	/**
	 * Edit comment hook callback.
	 */
	public function edit_comment( $comment_id ) {
		$comment = get_comment( $comment_id );
		$post = ap_get_post( $comment->comment_post_ID );

		if ( ! ('question' == $post->post_type || 'answer' == $post->post_type) ) {
			return;
		}

		if ( $post->post_type == 'question' ) {
			ap_update_post_activity_meta( $comment->comment_post_ID, 'edit_comment', get_current_user_id() );
		} else {
			ap_update_post_activity_meta( $comment->comment_post_ID, 'edit_comment_answer', get_current_user_id(), true );
		}
	}

	/**
	 * Update AnsPress pages URL dynimacally
	 *
	 * @param	array $items Menu item.
	 * @return array
	 */
	public static function update_menu_url( $items ) {
		// If this is admin then we dont want to update url.
		if ( is_admin() ) {
			return $items;
		}

		$pages = anspress()->pages;

		foreach ( (array) $items as $key => $item ) {

			if ( 'anspress-links' === $item->type ) {
				if ( isset( $pages[ $item->object ]['private'] ) && $pages[ $item->object ]['private'] && ! is_user_logged_in() ) {
					unset( $items[ $key ] );
				} else {
					if ( 'base' === $item->object ) {
						$item->url = ap_get_link_to( '/' );
					} else {
						$item->url = apply_filters( 'ap_menu_link', ap_get_link_to( ap_get_page_slug( $item->object ) ), $item );
					}
				}
			}
		}

		return apply_filters( 'ap_menu_items', $items );
	}

	/**
	 * Add current-menu-item class in AnsPress pages
	 *
	 * @param	array	$class Menu class.
	 * @param	object $item Current menu item.
	 * @return array menu item.
	 * @since	2.1
	 */
	public static function fix_nav_current_class( $class, $item ) {
		// Return if empty or `$item` is not object.
		if ( empty( $item ) || ! is_object( $item ) ) {
			return $class;
		}

		if ( ap_current_page() === $item->object ) {
			$class[] = 'current-menu-item';
		}

		return $class;
	}

	/**
	 * Check if flushing rewrite rule is needed
	 *
	 * @return void
	 */
	public static function flush_rules() {
			if ( ap_opt( 'ap_flush' ) != 'false' ) {
					flush_rewrite_rules();
					ap_opt( 'ap_flush', 'false' );
			}
	}

	/**
	 * Configure which button will appear in wp_editor
	 *
	 * @param	array	$buttons	 Button names.
	 * @param	string $editor_id Editor ID.
	 * @return array
	 */
	public static function editor_buttons( $buttons, $editor_id ) {
		if ( is_anspress() || ap_is_ajax() ) {
			return array( 'bold', 'italic', 'underline', 'strikethrough', 'bullist', 'numlist', 'link', 'unlink', 'blockquote', 'pre' );
		}

		return $buttons;
	}

	/**
	 * Filter post so that anonymous author should not be replaced
	 * by current user approving post.
	 *
	 * @param	array $data post data.
	 * @param	array $args Post arguments.
	 * @return array
	 * @since 2.2
	 * @since 4.1.0 Fixed: `post_author` get replaced if `anonymous_name` is empty.
	 *
	 * @global object $post Global post object.
	 */
	public static function wp_insert_post_data( $data, $args ) {
		global $post;

		if ( in_array( $args['post_type'], [ 'question', 'answer' ], true ) ) {
			$fields = ap_get_post_field( 'fields', $args['ID'] );

			if ( ( is_object( $post ) && '0' === $post->post_author ) || ( !empty( $fields ) && !empty( $fields['anonymous_name'] ) ) ) {
				$data['post_author'] = '0';
			}
		}

		return $data;
	}

	/**
	 * Sanitize post description
	 *
	 * @param	string $contents Post content.
	 * @return string					 Return sanitized post content.
	 */
	public static function sanitize_description( $contents ) {
		$contents = ap_trim_traling_space( $contents );
		$contents = ap_replace_square_bracket( $contents );

		return $contents;
	}

	/**
	 * Allowed CSS attributes for post_content
	 *
	 * @param	array $attr Allowed CSS attributes.
	 * @return array
	 */
	public static function safe_style_css( $attr ) {
		global $ap_kses_checkc; // Check if wp_kses is called by AnsPress.

		if ( isset( $ap_kses_check ) && $ap_kses_check ) {
				$attr = array( 'text-decoration', 'text-align' );
		}
		return $attr;
	}

	/**
	 * Flush rewrite rule if base page is updated.
	 *
	 * @param	integer $post_id Base page ID.
	 * @param	object	$post		Post object.
	 */
	public static function base_page_update( $post_id, $post ) {

		if ( wp_is_post_revision( $post ) ) {
			return;
		}

		if ( ap_opt( 'base_page' ) == $post_id ) {
			ap_opt( 'ap_flush', 'true' );
		}
	}

	/**
	 * Trigger AnsPress posts hooks right after inserting question/answer
	 *
	 * @param	integer $post_id Post ID.
	 * @param	object	$post		Post Object
	 * @param	boolean $updated Is updating post
	 * @since 3.0.3
	 */
	public static function question_answer_hooks( $post_id, $post, $updated ) {
		if ( wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) {
			return;
		}

		// check if post type is question or answer.
		if ( ! in_array( $post->post_type, [ 'question', 'answer' ] ) ) {
			return;
		}

		if ( $updated ) {
			if ( 'answer' === $post->post_type ) {
				// Update answer count.
				ap_update_answers_count( $post->post_parent );
			}

			/**
			 * Action triggered right after updating question/answer.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 */
			do_action( "ap_processed_update_{$post->post_type}", $post_id, $post );

		} else {
			/**
			 * Action triggered right after inserting new question/answer.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 */
			do_action( "ap_processed_new_{$post->post_type}", $post_id, $post );
		}

		if ( 'question' === $post->post_type ) {
			// Update qameta terms.
			ap_update_qameta_terms( $post_id );
		}
	}

	/**
	 * Update user meta of vote
	 *
	 * @param	integer $userid					 User ID who is voting.
	 * @param	string	$type						 Vote type.
	 * @param	integer $actionid				 Post ID.
	 * @param	integer $receiving_userid User who is receiving vote.
	 */
	public static function update_user_vote_casted_count( $userid, $type, $actionid, $receiving_userid ) {
		$voted = ap_count_post_votes_by( 'user_id', $userid );
		// Update total casted vote of user.
		update_user_meta( $userid, '__up_vote_casted', $voted->votes_up );
		update_user_meta( $userid, '__down_vote_casted', $voted->votes_down );
	}

	/**
	 * Append variable to post Object.
	 *
	 * @param Object $post Post object.
	 */
	public static function filter_page_title( $post ) {
		if ( ap_opt( 'base_page' ) == $post->ID && ! is_admin() ) {
			$post->post_title = ap_page_title();
		}
	}

	/**
	 * Update qameta subscribers count on adding new subscriber.
	 *
	 * @param integer $subscriber_id id of new subscriber added.
	 * @param integer $user_id id of user.
	 * @param string  $event Subscribe event.
	 * @param integer $ref_id Reference id.
	 */
	public static function new_subscriber( $subscribe_id, $user_id, $event, $ref_id ) {
		ap_update_subscribers_count( $ref_id );
	}

	/**
	 * Update qameta subscribers count on adding new subscriber.
	 *
	 * @param integer $rows Number of rows deleted.
	 * @param string  $event Subscribe event.
	 * @param integer $ref_id Reference id.
	 * @param integer $user_id Id of user.
	 */
	public static function delete_subscriber( $rows, $event, $ref_id, $user_id ) {
		ap_update_subscribers_count( $ref_id );
	}

	public static function display_question_metas( $metas, $question_id ) {
		if ( is_user_logged_in() && is_question() && ap_is_addon_active( 'free/email.php' ) ) {
			$metas['subscribe'] = ap_subscribe_btn( false, false );
		}

		return $metas;
	}

	/**
	 * Make human_time_diff strings translatable.
	 *
	 * @param	string $since Time since.
	 * @return string
	 * @since	2.4.8
	 */
	public static function human_time_diff( $since ) {
		$replace = array(
			'min'			  => __( 'minute', 'anspress-question-answer' ),
			'mins'		  => __( 'minutes', 'anspress-question-answer' ),
			'hour'		  => __( 'hour', 'anspress-question-answer' ),
			'hours' 	  => __( 'hours', 'anspress-question-answer' ),
			'day'	 	    => __( 'day', 'anspress-question-answer' ),
			'days'		  => __( 'days', 'anspress-question-answer' ),
			'week'		  => __( 'week', 'anspress-question-answer' ),
			'weeks'		  => __( 'weeks', 'anspress-question-answer' ),
			'year'		  => __( 'year', 'anspress-question-answer' ),
			'years'		  => __( 'years', 'anspress-question-answer' ),
		);

		return strtr( $since, $replace );
	}

	/**
	 * Filter recent comments widget args.
	 * Exclude AnsPress comments from recent commenst widget.
	 *
	 * @param array $args Comments arguments.
	 * @return array
	 */
	public static function widget_comments_args( $args ) {
		$args['type__not_in'] = [ 'anspress' ];
		return $args;
	}
}
