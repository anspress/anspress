<?php
/**
 * All Hooks of AnsPress
 *
 * @package   AnsPress
 * @author      Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link            https://anspress.net
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
			anspress()->add_action( 'untrash_post', __CLASS__, 'untrash_posts' );
			anspress()->add_action( 'comment_post', __CLASS__, 'new_comment_approve', 10, 2 );
			anspress()->add_action( 'comment_unapproved_to_approved', __CLASS__, 'comment_approve' );
			anspress()->add_action( 'comment_approved_to_unapproved', __CLASS__, 'comment_unapprove' );
			anspress()->add_action( 'trashed_comment', __CLASS__, 'comment_trash' );
			anspress()->add_action( 'delete_comment', __CLASS__, 'comment_trash' );
			anspress()->add_action( 'edit_comment', __CLASS__, 'edit_comment' );
			anspress()->add_action( 'ap_publish_comment', __CLASS__, 'publish_comment' );
			anspress()->add_action( 'ap_unpublish_comment', __CLASS__, 'unpublish_comment' );
			anspress()->add_action( 'wp_loaded', __CLASS__, 'flush_rules' );
			anspress()->add_action( 'safe_style_css', __CLASS__, 'safe_style_css', 11 );
			anspress()->add_action( 'save_post', __CLASS__, 'base_page_update', 10, 2 );
			anspress()->add_action( 'save_post_question', __CLASS__, 'save_question_hooks', 1, 3 );
			anspress()->add_action( 'save_post_answer', __CLASS__, 'save_answer_hooks', 1, 3 );
			anspress()->add_action( 'transition_post_status', __CLASS__, 'transition_post_status', 10, 3 );
			anspress()->add_action( 'ap_vote_casted', __CLASS__, 'update_user_vote_casted_count', 10, 4 );
			anspress()->add_action( 'ap_vote_removed', __CLASS__, 'update_user_vote_casted_count', 10, 4 );
			anspress()->add_action( 'ap_display_question_metas', __CLASS__, 'display_question_metas', 100, 2 );
			anspress()->add_action( 'widget_comments_args', __CLASS__, 'widget_comments_args' );

			anspress()->add_filter( 'posts_clauses', 'AP_QA_Query_Hooks', 'sql_filter', 1, 2 );
			anspress()->add_filter( 'posts_results', 'AP_QA_Query_Hooks', 'posts_results', 1, 2 );
			anspress()->add_filter( 'posts_pre_query', 'AP_QA_Query_Hooks', 'modify_main_posts', 999999, 2 );
			anspress()->add_filter( 'pre_get_posts', 'AP_QA_Query_Hooks', 'pre_get_posts' );

			// Theme hooks.
			anspress()->add_action( 'init', 'AnsPress_Theme', 'init_actions' );
			anspress()->add_filter( 'template_include', 'AnsPress_Theme', 'template_include' );
			anspress()->add_filter( 'ap_template_include', 'AnsPress_Theme', 'template_include_theme_compat' );
			anspress()->add_filter( 'post_class', 'AnsPress_Theme', 'question_answer_post_class' );
			anspress()->add_filter( 'body_class', 'AnsPress_Theme', 'body_class' );
			anspress()->add_action( 'after_setup_theme', 'AnsPress_Theme', 'includes_theme' );
			anspress()->add_filter( 'wp_title', 'AnsPress_Theme', 'ap_title', 0 );
			anspress()->add_action( 'ap_before', 'AnsPress_Theme', 'ap_before_html_body' );
			anspress()->add_action( 'wp_head', 'AnsPress_Theme', 'wp_head', 11 );
			anspress()->add_action( 'ap_after_question_content', 'AnsPress_Theme', 'question_attachments', 11 );
			anspress()->add_action( 'ap_after_answer_content', 'AnsPress_Theme', 'question_attachments', 11 );
			anspress()->add_filter( 'nav_menu_css_class', __CLASS__, 'fix_nav_current_class', 10, 2 );
			//anspress()->add_filter( 'mce_external_languages', __CLASS__, 'mce_plugins_languages' );
			anspress()->add_filter( 'wp_insert_post_data', __CLASS__, 'wp_insert_post_data', 1000, 2 );
			anspress()->add_filter( 'ap_form_contents_filter', __CLASS__, 'sanitize_description' );

			anspress()->add_filter( 'template_include', 'AnsPress_Theme', 'anspress_basepage_template', 9999 );
			anspress()->add_filter( 'get_the_excerpt', 'AnsPress_Theme', 'get_the_excerpt', 9999, 2 );
			anspress()->add_filter( 'post_class', 'AnsPress_Theme', 'remove_hentry_class', 10, 3 );
			anspress()->add_action( 'ap_after_question_content', 'AnsPress_Theme', 'after_question_content' );
			anspress()->add_filter( 'ap_after_answer_content', 'AnsPress_Theme', 'after_question_content' );

			anspress()->add_filter( 'the_comments', 'AnsPress_Comment_Hooks', 'the_comments' );
			//anspress()->add_filter( 'comments_template_query_args', 'AnsPress_Comment_Hooks', 'comments_template_query_args' );
			anspress()->add_filter( 'get_comment_link', 'AnsPress_Comment_Hooks', 'comment_link', 10, 3 );
			anspress()->add_filter( 'preprocess_comment', 'AnsPress_Comment_Hooks', 'preprocess_comment' );
			anspress()->add_filter( 'comments_template', 'AnsPress_Comment_Hooks', 'comments_template' );

			// Common pages hooks.
			anspress()->add_action( 'init', 'AnsPress_Common_Pages', 'register_common_pages' );

			// Register post status.
			anspress()->add_action( 'init', 'AnsPress_Post_Status', 'register_post_status' );

			// Rewrite rules hooks.
			anspress()->add_filter( 'request', 'AnsPress_Rewrite', 'alter_the_query' );
			anspress()->add_filter( 'query_vars', 'AnsPress_Rewrite', 'query_var' );
			anspress()->add_action( 'generate_rewrite_rules', 'AnsPress_Rewrite', 'rewrites', 1 );
			anspress()->add_filter( 'paginate_links', 'AnsPress_Rewrite', 'bp_com_paged' );
			anspress()->add_filter( 'parse_request', 'AnsPress_Rewrite', 'add_query_var' );
			anspress()->add_action( 'template_redirect', 'AnsPress_Rewrite', 'shortlink' );

			// Upload hooks.
			anspress()->add_action( 'deleted_post', 'AnsPress_Uploader', 'deleted_attachment' );
			anspress()->add_action( 'init', 'AnsPress_Uploader', 'create_single_schedule' );
			anspress()->add_action( 'ap_delete_temp_attachments', 'AnsPress_Uploader', 'cron_delete_temp_attachments' );
			anspress()->add_action( 'intermediate_image_sizes_advanced', 'AnsPress_Uploader', 'image_sizes_advanced' );

			// Vote hooks.
			anspress()->add_action( 'ap_before_delete_question', 'AnsPress_Vote', 'delete_votes' );
			anspress()->add_action( 'ap_before_delete_answer', 'AnsPress_Vote', 'delete_votes' );
			anspress()->add_action( 'ap_deleted_votes', 'AnsPress_Vote', 'ap_deleted_votes', 10, 2 );

			// Form hooks.
			anspress()->add_action( 'ap_form_question', 'AP_Form_Hooks', 'question_form', 11 );
			anspress()->add_action( 'ap_form_answer', 'AP_Form_Hooks', 'answer_form', 11 );
			anspress()->add_action( 'ap_form_comment', 'AP_Form_Hooks', 'comment_form', 11 );
			anspress()->add_action( 'ap_form_image_upload', 'AP_Form_Hooks', 'image_upload_form', 11 );

			// Subscriptions
			anspress()->add_action( 'ap_after_new_question', __CLASS__, 'question_subscription', 10, 2 );
			anspress()->add_action( 'ap_after_new_answer', __CLASS__, 'answer_subscription', 10, 2 );
			anspress()->add_action( 'ap_new_subscriber', __CLASS__, 'new_subscriber', 10, 4 );
			anspress()->add_action( 'ap_delete_subscribers', __CLASS__, 'delete_subscribers', 10, 2 );
			anspress()->add_action( 'ap_delete_subscriber', __CLASS__, 'delete_subscriber', 10, 3 );
			anspress()->add_action( 'before_delete_post', __CLASS__, 'delete_subscriptions' );
			anspress()->add_action( 'ap_publish_comment', __CLASS__, 'comment_subscription' );
			anspress()->add_action( 'deleted_comment', __CLASS__, 'delete_comment_subscriptions', 10, 2 );
			anspress()->add_action( 'get_comments_number', __CLASS__, 'get_comments_number', 11, 2 );
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
	 * @param   integer $post_id Question id.
	 * @param   object  $post Question post object.
	 * @since   1.0
	 * @since   4.1.2 Removed @see ap_update_post_activity_meta().
	 */
	public static function after_new_question( $post_id, $post ) {

		/**
		 * Action triggered after inserting a question
		 *
		 * @since 0.9
		 */
		do_action( 'ap_after_new_question', $post_id, $post );
	}

	/**
	 * Things to do after creating an answer
	 *
	 * @param   integer $post_id answer id.
	 * @param   object  $post answer post object.
	 * @since 2.0.1
	 * @since 4.1.2  Removed @see ap_update_post_activity_meta().
	 * @since 4.1.11 Removed @see ap_update_answers_count().
	 */
	public static function after_new_answer( $post_id, $post ) {
		// Update answer count.
		ap_update_answers_count( $post->post_parent );

		/**
		 * Action triggered after inserting an answer
		 *
		 * @since 0.9
		 */
		do_action( 'ap_after_new_answer', $post_id, $post );
	}

	/**
	 * This callback handles pre delete question actions.
	 *
	 * Before deleting a question we have to make sure that all answers
	 * and metas are cleared. Some hooks in answer may require question data
	 * so its better to delete all answers before deleting question.
	 *
	 * @param   integer $post_id Question or answer ID.
	 * @since unknown
	 * @since 4.1.6 Delete cache for `ap_is_answered`.
	 * @since 4.1.8 Delete uploaded images and `anspress-images` meta.
	 */
	public static function before_delete( $post_id ) {
		$post = ap_get_post( $post_id );

		if ( ! ap_is_cpt( $post ) ) {
			return;
		}

		// Get anspress uploads.
		$images = get_post_meta( $post_id, 'anspress-image' );
		if ( ! empty( $images ) ) {

			// Delete all uploaded images.
			foreach ( $images as $img ) {
				$uploads = wp_upload_dir();
				$file    = $uploads['basedir'] . "/anspress-uploads/$img";

				if ( file_exists( $file ) ) {
					unlink( $file );
				}
			}
		}

		if ( 'question' === $post->post_type ) {

			/**
			 * Action triggered before deleting a question form database.
			 *
			 * At this point question are not actually deleted from database hence
			 * it will be easy to perform actions which uses mysql queries.
			 *
			 * @param integer $post_id Question id.
			 * @param WP_Post $post    Question object.
			 * @since unknown
			 */
			do_action( 'ap_before_delete_question', $post->ID, $post );

			$answers = get_posts( [ 'post_parent' => $post->ID, 'post_type' => 'answer' ] ); // @codingStandardsIgnoreLine

			foreach ( (array) $answers as $a ) {
				self::delete_answer( $a->ID, $a );
				wp_delete_post( $a->ID, true );
			}

			// Delete qameta.
			ap_delete_qameta( $post->ID );

		} elseif ( 'answer' === $post->post_type ) {
			self::delete_answer( $post_id, $post );
		}
	}

	/**
	 * Delete answer.
	 *
	 * @param   integer $post_id Question or answer ID.
	 * @param   object  $post Post Object.
	 * @since unknown
	 * @since 4.1.2 Removed @see ap_update_post_activity_meta().
	 */
	public static function delete_answer( $post_id, $post ) {
		do_action( 'ap_before_delete_answer', $post->ID, $post );

		if ( ap_is_selected( $post ) ) {
			ap_unset_selected_answer( $post->post_parent );
		}

		// Delete qameta.
		ap_delete_qameta( $post->ID );
	}

	/**
	 * If a question is sent to trash, then move its answers to trash as well
	 *
	 * @param   integer $post_id Post ID.
	 * @since 2.0.0
	 * @since 4.1.2 Removed @see ap_update_post_activity_meta().
	 * @since 4.1.6 Delete cache for `ap_is_answered`.
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

				wp_trash_post( $p->ID );
			}
		}

		if ( 'answer' === $post->post_type ) {

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
	 * @param   integer $post_id Post ID.
	 * @since 2.0.0
	 * @since 4.1.2 Removed @see ap_update_post_activity_meta().
	 * @since 4.1.11 Renamed method from `untrash_ans_on_question_untrash` to `untrash_posts`.
	 */
	public static function untrash_posts( $post_id ) {
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
		}

		if ( 'answer' === $_post->post_type ) {
			$ans = ap_count_published_answers( $_post->post_parent );
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
	 *
	 * @since unknown
	 * @since 4.1.0 Do not check post_type, instead comment type.
	 */
	public static function new_comment_approve( $comment_id, $approved ) {
		if ( 1 === $approved ) {
			$comment = get_comment( $comment_id );

			if ( 'anspress' === $comment->comment_type ) {
				/**
				 * Action is triggered when a anspress comment is published.
				 *
				 * @param object $comment Comment object.
				 * @since unknown
				 */
				do_action( 'ap_publish_comment', $comment );
			}
		}
	}

	/**
	 * Used to create an action when comment get approved.
	 *
	 * @param	array|object $comment Comment object.
	 *
	 * @since unknown
	 * @since 4.1.0 Do not check post_type, instead comment type.
	 */
	public static function comment_approve( $comment ) {
		if ( 'anspress' === $comment->comment_type ) {
			/** This action is documented in includes/hooks.php */
			do_action( 'ap_publish_comment', $comment );
		}
	}

	/**
	 * Used to create an action when comment get unapproved.
	 *
	 * @param	array|object $comment Comment object.
	 * @since unknown
	 * @since 4.1.0 Do not check post_type, instead comment type.
	 */
	public static function comment_unapprove( $comment ) {
		if ( 'anspress' === $comment->comment_type ) {
			/**
			 * Action is triggered when a anspress comment is unpublished.
			 *
			 * @param object $comment Comment object.
			 * @since unknown
			 */
			do_action( 'ap_unpublish_comment', $comment );
		}
	}

	/**
	 * Used to create an action when comment get trashed.
	 *
	 * @param	integer $comment_id Comment ID.
	 * @since unknown
	 * @since 4.1.0 Do not check post_type, instead comment type.
	 */
	public static function comment_trash( $comment_id ) {
		$comment = get_comment( $comment_id );

		if ( 'anspress' === $comment->comment_type ) {
			/** This action is documented in includes/hooks.php */
			do_action( 'ap_unpublish_comment', $comment );
		}
	}

	/**
	 * Actions to run after posting a comment
	 *
	 * @param	object|array $comment Comment object.
	 * @since unknown
	 * @since 4.1.2 Log to activity table on new comment. Removed @see ap_update_post_activity_meta().
	 */
	public static function publish_comment( $comment ) {
		$comment = (object) $comment;

		$post = ap_get_post( $comment->comment_post_ID );

		if ( ! in_array( $post->post_type, [ 'question', 'answer' ], true ) ) {
			return false;
		}

		$count = get_comment_count( $comment->comment_post_ID );
		ap_insert_qameta( $comment->comment_post_ID, array(
			'fields'       => [ 'unapproved_comments' => $count['awaiting_moderation'] ],
			'last_updated' => current_time( 'mysql' ),
		) );


		// Log to activity table.
		ap_activity_add( array(
			'q_id'    => 'answer' === $post->post_type ? $post->post_parent: $post->ID,
			'action'  => 'new_c',
			'a_id'    => 'answer' === $post->post_type ? $post->ID: 0,
			'c_id'    => $comment->comment_ID,
			'user_id' => $comment->user_id,
		) );
	}

	/**
	 * Actions to run after unpublishing a comment.
	 *
	 * @param	object|array $comment Comment object.
	 * @since 4.1.2 Removed @see ap_update_post_activity_meta().
	 */
	public static function unpublish_comment( $comment ) {
		$comment = (object) $comment;
		$count = get_comment_count( $comment->comment_post_ID );
		ap_insert_qameta( $comment->comment_post_ID, [ 'fields' => [ 'unapproved_comments' => $count['awaiting_moderation'] ] ] );
	}

	/**
	 * Edit comment hook callback.
	 *
	 * @since unknown
	 * @since 4.1.2 Removed @see ap_update_post_activity_meta().
	 */
	public static function edit_comment( $comment_id ) {
		$comment = get_comment( $comment_id );
		$post = ap_get_post( $comment->comment_post_ID );

		if ( ! ap_is_cpt( $post ) ) {
			return;
		}

		$q_id = 'answer' === $post->post_type ? $post->post_parent : $post->ID;
		$a_id = 'answer' === $post->post_type ? $post->ID : 0;

		// Insert activity.
		ap_activity_add( array(
			'q_id'   => $q_id,
			'a_id'   => $a_id,
			'action' => 'edit_c',
      'c_id'   => $comment_id,
		) );
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
			flush_rewrite_rules( true );
			ap_opt( 'ap_flush', 'false' );
		}
	}

	/**
	 * Add translations for AnsPress's tinymce plugins.
	 *
	 * @param array $translations Translations for external TinyMCE plugins.
	 * @since 4.1.5
	 */
	public static function mce_plugins_languages( $translations ) {
		$translations['anspress'] = ANSPRESS_DIR . 'includes/mce-languages.php';
		return $translations;
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
		return $contents;
	}

	/**
	 * Allowed CSS attributes for post_content
	 *
	 * @param	array $attr Allowed CSS attributes.
	 * @return array
	 * @since 4.1.11 Fixed wrong variable name.
	 */
	public static function safe_style_css( $attr ) {
		global $ap_kses_check; // Check if wp_kses is called by AnsPress.

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
	 * @since 4.1.0   Update respective page slug in options.
	 */
	public static function base_page_update( $post_id, $post ) {
		if ( wp_is_post_revision( $post ) ) {
			return;
		}

		$main_pages = array_keys( ap_main_pages() );
		$page_ids = [];

		foreach ( $main_pages as $slug ) {
			$page_ids[ ap_opt( $slug ) ] = $slug;
		}

		if ( in_array( $post_id, array_keys( $page_ids ) ) ) {
			$current_opt = $page_ids[ $post_id ];

			ap_opt( $current_opt, $post_id );
			ap_opt( $current_opt . '_id', $post->post_name );

			ap_opt( 'ap_flush', 'true' );
		}
	}

	/**
	 * Trigger posts hooks right after saving question.
	 *
	 * @param	integer $post_id Post ID.
	 * @param	object	$post		Post Object
	 * @param	boolean $updated Is updating post
	 * @since 4.1.0
	 * @since 4.1.2 Do not process if form not submitted. Insert updated to activity table.
	 * @since 4.1.8 Add `ap_delete_images_not_in_content`.
	 */
	public static function save_question_hooks( $post_id, $post, $updated ) {
		if ( wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) {
			return;
		}

		if ( $updated ) {
			// Deleted unused images from meta.
			ap_delete_images_not_in_content( $post_id );
		}

		$form = anspress()->get_form( 'question' );
		$values = $form->get_values();

		$qameta = array(
			'last_updated' => current_time( 'mysql' ),
			'answers'      => ap_count_published_answers( $post_id ),
		);

		// Check if anonymous post and have name.
		if ( $form->is_submitted() && ! is_user_logged_in() && ap_allow_anonymous() && ! empty( $values['anonymous_name']['value'] ) ) {
			$qameta['fields'] = array(
				'anonymous_name' => $values['anonymous_name']['value'],
			);
		}

		/**
		 * Modify qameta args which will be inserted after inserting
		 * or updating question.
		 *
		 * @param array   $qameta  Qameta arguments.
		 * @param object  $post    Post object.
		 * @param boolean $updated Is updated.
		 * @since 4.1.0
		 */
		$qameta = apply_filters( 'ap_insert_question_qameta', $qameta, $post, $updated );
		ap_insert_qameta( $post_id, $qameta );

		if ( $updated ) {
			/**
			 * Action triggered right after updating question.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 * @since 4.1.0 Removed `$post->post_type` variable.
			 */
			do_action( 'ap_processed_update_question' , $post_id, $post );

		} else {
			/**
			 * Action triggered right after inserting new question.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 * @since 4.1.0 Removed `$post->post_type` variable.
			 */
			do_action( 'ap_processed_new_question', $post_id, $post );
		}

		// Update qameta terms.
		ap_update_qameta_terms( $post_id );
	}

	/**
	 * Trigger posts hooks right after saving answer.
	 *
	 * @param	integer $post_id Post ID.
	 * @param	object	$post		Post Object
	 * @param	boolean $updated Is updating post
	 * @since 4.1.0
	 * @since 4.1.2 Do not process if form not submitted. Insert updated to activity table.
	 * @since 4.1.8 Add `ap_delete_images_not_in_content`.
	 */
	public static function save_answer_hooks( $post_id, $post, $updated ) {
		if ( wp_is_post_autosave( $post ) || wp_is_post_revision( $post ) ) {
			return;
		}

		if ( $updated ) {
			// Deleted unused images from meta.
			ap_delete_images_not_in_content( $post_id );
		}

		$form = anspress()->get_form( 'answer' );

		$values = $form->get_values();
		$activity_type = ! empty( $values['post_id']['value'] ) ? 'edit_answer' : 'new_answer';

		// Update parent question's answer count.
		ap_update_answers_count( $post->post_parent );

		$qameta = array(
			'last_updated' => current_time( 'mysql' ),
			'activities'   => array(
				'type'    => $activity_type,
				'user_id' => $post->post_author,
				'date'    => current_time( 'mysql' ),
			),
		);

		// Check if anonymous post and have name.
		if ( $form->is_submitted() && ! is_user_logged_in() && ap_allow_anonymous() && ! empty( $values['anonymous_name']['value'] ) ) {
			$qameta['fields'] = array(
				'anonymous_name' => $values['anonymous_name']['value'],
			);
		}

		/**
		 * Modify qameta args which will be inserted after inserting
		 * or updating answer.
		 *
		 * @param array   $qameta  Qameta arguments.
		 * @param object  $post    Post object.
		 * @param boolean $updated Is updated.
		 * @since 4.1.0
		 */
		$qameta = apply_filters( 'ap_insert_answer_qameta', $qameta, $post, $updated );
		ap_insert_qameta( $post_id, $qameta );

		if ( $updated ) {
			/**
			 * Action triggered right after updating answer.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 * @since 4.1.0 Removed `$post->post_type` variable.
			 */
			do_action( 'ap_processed_update_answer' , $post_id, $post );

		} else {
			/**
			 * Action triggered right after inserting new answer.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 * @since 4.1.0 Removed `$post->post_type` variable.
			 */
			do_action( 'ap_processed_new_answer', $post_id, $post );
		}

		// Update qameta terms.
		ap_update_qameta_terms( $post_id );
	}

	/**
	 * Trigger activity update hook on question and answer status transition.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       WordPress post object.
	 * @return void
	 * @since 4.1.2
	 */
	public static function transition_post_status( $new_status, $old_status, $post ) {
		if ( 'new' === $old_status || ! in_array( $post->post_type, [ 'answer', 'question' ], true ) ) {
			return;
		}

		$question_id = 'answer' === $post->post_type ? $post->post_parent : $post->ID;
		$answer_id   = 'answer' === $post->post_type ? $post->ID : 0;

		// Log to db.
		ap_activity_add( array(
			'q_id'   => $question_id,
			'a_id'   => $answer_id,
			'action' => 'status_' . $new_status,
		) );
	}

	/**
	 * Update user meta of vote
	 *
	 * @param	integer $userid					  User ID who is voting.
	 * @param	string	$type						  Vote type.
	 * @param	integer $actionid				  Post ID.
	 * @param	integer $receiving_userid User who is receiving vote.
	 */
	public static function update_user_vote_casted_count( $userid, $type, $actionid, $receiving_userid ) {
		$voted = ap_count_post_votes_by( 'user_id', $userid );
		// Update total casted vote of user.
		update_user_meta( $userid, '__up_vote_casted', $voted->votes_up );
		update_user_meta( $userid, '__down_vote_casted', $voted->votes_down );
	}

	/**
	 * Update qameta subscribers count on adding new subscriber.
	 *
	 * @param integer $rows Number of rows deleted.
	 * @param string  $where Where clause.
	 */
	public static function delete_subscriber( $ref_id, $user_id, $event ) {
		// Remove ids from event.
		$esc_event = ap_esc_subscriber_event( $event );

		if ( in_array( $esc_event, [ 'question', 'answer', 'comment' ], true ) ) {
			ap_update_subscribers_count( $ref_id );
		}
	}

	public static function display_question_metas( $metas, $question_id ) {
		if ( is_user_logged_in() && is_question() && ap_is_addon_active( 'email.php' ) ) {
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
	 *
	 * @deprecated 4.1.13
	 */
	public static function human_time_diff( $since ) {
		$replace = array(
			'min'   => __( 'minute', 'anspress-question-answer' ),
			'mins'  => __( 'minutes', 'anspress-question-answer' ),
			'hour'  => __( 'hour', 'anspress-question-answer' ),
			'hours' => __( 'hours', 'anspress-question-answer' ),
			'day'   => __( 'day', 'anspress-question-answer' ),
			'days'  => __( 'days', 'anspress-question-answer' ),
			'week'  => __( 'week', 'anspress-question-answer' ),
			'weeks' => __( 'weeks', 'anspress-question-answer' ),
			'year'  => __( 'year', 'anspress-question-answer' ),
			'years' => __( 'years', 'anspress-question-answer' ),
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

	/**
	 * Subscribe OP to his own question.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post post objct.
	 *
	 * @category haveTest
	 *
	 * @since unknown Introduced
	 * @since 4.1.5 Moved from addons/free/email.php
	 */
	public static function question_subscription( $post_id, $_post ) {
		if ( $_post->post_author > 0 ) {
			ap_new_subscriber( $_post->post_author, 'question', $_post->ID );
		}
	}

	/**
	 * Subscribe author to their answer. Answer id is stored in event name.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post   Post object.
	 *
	 * @category haveTest
	 *
	 * @since unknown Introduced
	 * @since 4.1.5 Moved from addons/free/email.php
	 */
	public static function answer_subscription( $post_id, $_post ) {
		if ( $_post->post_author > 0 ) {
			ap_new_subscriber( $_post->post_author, 'answer_' . $post_id, $_post->post_parent );
		}
	}

	/**
	 * Update qameta subscribers count on adding new subscriber.
	 *
	 * @param integer $subscriber_id id of new subscriber added.
	 * @param integer $user_id id of user.
	 * @param string  $event Subscribe event.
	 * @param integer $ref_id Reference id.
	 *
	 * @category haveTest
	 *
	 * @since unknown
	 * @since 4.1.5 Update answer subscribers count.
	 */
	public static function new_subscriber( $subscribe_id, $user_id, $event, $ref_id ) {
		// Remove ids from event.
		$esc_event = ap_esc_subscriber_event( $event );

		if ( in_array( $esc_event, [ 'question', 'answer', 'comment' ], true ) ) {
			ap_update_subscribers_count( $ref_id );
		}

		// Update answer subscribers count.
		if ( 'answer' === $esc_event ) {
			$event_id = ap_esc_subscriber_event_id( $event );
			ap_update_subscribers_count( $event_id );
		}
	}

	/**
	 * Update qameta subscribers count before deleting subscribers.
	 *
	 * @param string $rows  Number of rows deleted.
	 * @param string $where Where clause.
	 *
	 * @category haveTest
	 *
	 * @since 4.1.5
	 */
	public static function delete_subscribers( $rows, $where ) {
		if ( ! isset( $where['subs_ref_id'] ) || ! isset( $where['subs_event'] ) ) {
			return;
		}

		// Remove ids from event.
		$esc_event = ap_esc_subscriber_event( $where['subs_event'] );

		if ( in_array( $esc_event, [ 'question', 'answer', 'comment' ], true ) ) {
			ap_update_subscribers_count( $where['subs_ref_id'] );
		}
	}

	/**
	 * Delete subscriptions.
	 *
	 * @param integer $postid Post ID.
	 *
	 * @since unknown Introduced
	 * @since 4.1.5 Moved from addons/free/email.php
	 */
	public static function delete_subscriptions( $postid ) {
		$_post = get_post( $postid );

		if ( 'question' === $_post->post_type ) {
			// Delete question subscriptions.
			ap_delete_subscribers( array(
				'subs_event'  => 'question',
				'subs_ref_id' => $postid,
			) );
		}

		if ( 'answer' === $_post->post_type ) {
			// Delete question subscriptions.
			ap_delete_subscribers( array(
				'subs_event'  => 'answer_' . $_post->post_parent,
			) );
		}
	}

	/**
	 * Add comment subscriber.
	 *
	 * If question than subscription event will be `question_{$question_id}` and ref id will contain
	 * comment id. If answer than subscription event will be `answer_{$answer_id}` and ref_id
	 * will contain comment ID.
	 *
	 * @param object $comment Comment object.
	 * @since unknown Introduced
	 * @since 4.1.5 Moved from addons/free/email.php
	 * @since 4.1.8 Changed event.
	 */
	public static function comment_subscription( $comment ) {
		if ( $comment->user_id > 0 ) {
			$_post = ap_get_post( $comment->comment_post_ID );
			$type = $_post->post_type . '_' . $_post->ID;
			ap_new_subscriber( $comment->user_id, $type, $comment->comment_ID );
		}
	}

	/**
	 * Delete comment subscriptions right before deleting comment.
	 *
	 * @param integer $comment_id Comment ID.
	 * @param integer $_comment   Comment object.
	 *
	 * @since unknown Introduced
	 * @since 4.1.5 Moved from addons/free/email.php
	 * @since 4.1.8 Changed event.
	 */
	public static function delete_comment_subscriptions( $comment_id, $_comment ) {
		$_post = get_post( $_comment->comment_post_ID );

		if ( in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
			$type = $_post->post_type . '_' . $_post->ID;
			$row = ap_delete_subscribers( array(
				'subs_event'  => $type,
				'subs_ref_id' => $_comment->comment_ID,
			) );
		}
	}

	/**
	 * Include anspress comments count.
	 * This fixes no comments visible while using DIVI.
	 *
	 * @param integer $count   Comments count
	 * @param integer $post_id Post ID.
	 * @return integer
	 *
	 * @since 4.1.13
	 */
	public static function get_comments_number( $count, $post_id ) {
		global $post_type;

		if ( $post_type == 'question' || ( defined( 'DOING_AJAX' ) && true === DOING_AJAX && 'ap_form_comment' === ap_isset_post_value( 'action' ) ) ) {
			$get_comments     = get_comments( array(
				'post_id' => $post_id,
				'status'  => 'approve'
			) );

			$types = separate_comments( $get_comments );
			if( ! empty( $types['anspress'] ) ) {
				$count = count( $types['anspress'] );
			}
		}

		return $count;
	}
}
