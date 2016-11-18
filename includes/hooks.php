<?php
/**
 * All Hooks of AnsPress
 *
 * @package	 AnsPress
 * @author		Rahul Aryan <support@anspress.io>
 * @license	 GPL-2.0+
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
			anspress()->add_action( 'ap_processed_update_question', __CLASS__, 'ap_after_update_question', 1, 2 );
			anspress()->add_action( 'ap_processed_update_answer', __CLASS__, 'ap_after_update_answer', 1, 2 );
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
			anspress()->add_action( 'the_post', __CLASS__, 'ap_append_vote_count' );

			anspress()->add_filter( 'posts_clauses', 'AP_QA_Query_Hooks', 'sql_filter', 1, 2 );
			anspress()->add_filter( 'posts_results', 'AP_QA_Query_Hooks', 'posts_results', 1, 2 );

			// Theme	hooks.
			anspress()->add_action( 'init', 'AnsPress_Theme', 'init_actions' );
			anspress()->add_filter( 'post_class', 'AnsPress_Theme', 'question_answer_post_class' );
			anspress()->add_filter( 'body_class', 'AnsPress_Theme', 'body_class' );
			anspress()->add_filter( 'comments_template', 'AnsPress_Theme', 'comment_template' );
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
			anspress()->add_filter( 'wp_insert_post_data', __CLASS__, 'wp_insert_post_data', 10, 2 );
			anspress()->add_filter( 'ap_form_contents_filter', __CLASS__, 'sanitize_description' );
			anspress()->add_filter( 'human_time_diff', __CLASS__, 'human_time_diff' );
			anspress()->add_filter( 'comments_template_query_args', 'AnsPress_Comment_Hooks', 'comments_template_query_args' );
			anspress()->add_filter( 'template_include', 'AnsPress_Theme', 'anspress_basepage_template' );
			anspress()->add_filter( 'avatar_defaults' , 'AnsPress_Theme', 'default_avatar' );
			anspress()->add_filter( 'pre_get_avatar_data', 'AnsPress_Theme', 'get_avatar', 1000, 3 );

			// Common pages hooks.
			anspress()->add_action( 'init', 'AnsPress_Common_Pages', 'register_common_pages' );

			// Register post ststus.
			anspress()->add_action( 'init', 'AnsPress_Post_Status', 'register_post_status' );

			// Rewrite rules hooks.
			anspress()->add_filter( 'query_vars', 'AnsPress_Rewrite', 'query_var' );
			anspress()->add_action( 'generate_rewrite_rules', 'AnsPress_Rewrite', 'rewrites', 1 );
			anspress()->add_filter( 'paginate_links', 'AnsPress_Rewrite', 'bp_com_paged' );
			anspress()->add_filter( 'parse_request', 'AnsPress_Rewrite', 'add_query_var' );

			// Upload hooks.
			anspress()->add_action( 'deleted_post', 'AnsPress_Uploader', 'after_delete_attachment' );
			anspress()->add_action( 'init', 'AnsPress_Uploader', 'create_single_schedule' );
			anspress()->add_action( 'ap_delete_temp_attachments', 'AnsPress_Uploader', 'cron_delete_temp_attachments' );
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

		// Update qameta terms.
		ap_update_qameta_terms( $post_id );
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
	 * Things to do after updating question
	 *
	 * @param	integer $post_id Question ID.
	 * @param	object	$post		Question post object.
	 */
	public static function ap_after_update_question( $post_id, $post ) {
		/**
		 * ACTION: ap_after_new_answer
		 * action triggered after inserting an answer
		 *
		 * @since 0.9
		 */
		do_action( 'ap_after_update_question', $post_id, $post );
		// Update qameta terms.
		ap_update_qameta_terms( $post_id );
	}

	/**
	 * Things to do after updating an answer
	 *
	 * @param	integer $post_id	Answer ID.
	 * @param	object	$post		 Answer post object.
	 */
	public static function ap_after_update_answer( $post_id, $post ) {
		// Update answer count.
		ap_update_answers_count( $post->post_parent );

		/**
		 * ACTION: ap_processed_update_answer
		 * action triggered after inserting an answer
		 *
		 * @since 0.9
		 */
		do_action( 'ap_after_update_answer', $post_id, $post );
	}

	/**
	 * Before deleting a question or answer.
	 *
	 * @param	integer $post_id Question or answer ID.
	 */
	public static function before_delete( $post_id ) {
		$post = ap_get_post( $post_id );
		if ( 'question' === $post->post_type ) {
			do_action( 'ap_before_delete_question', $post->ID );
			//@codingStandardsIgnoreStart
			$answers = get_posts( [ 'post_parent' => $post->ID, 'post_type' => 'answer' ] );
			//@codingStandardsIgnoreEnd

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
		do_action( 'ap_before_delete_answer', $post->ID );
		ap_update_post_activity_meta( $post->post_parent, 'delete_answer', $post->post_author );

		$selcted_answer = ap_selected_answer( $post );
		if ( $post->ID === $selcted_answer ) {
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
		$post = ap_get_post( $post_id );

		if ( 'question' === $post->post_type ) {
			do_action( 'ap_untrash_question', $post->ID );
			//@codingStandardsIgnoreStart
			$ans = get_posts( array(
				'post_type'   => 'answer',
				'post_status' => 'trash',
				'post_parent' => $post_id,
				'showposts'   => -1,
			));
			//@codingStandardsIgnoreStart

			foreach ( (array) $ans as $p ) {
				do_action( 'ap_untrash_answer', $p->ID, $p );
				wp_untrash_post( $p->ID );
			}
			ap_update_post_activity_meta( $p->post_parent, 'restore_question', get_current_user_id() );
		}

		if ( 'answer' === $post->post_type ) {
			$ans = ap_count_published_answers( $post->post_parent );
			ap_update_post_activity_meta( $post->post_parent, 'restore_answer', get_current_user_id(), true );
			do_action( 'ap_untrash_answer', $post->ID, $ans );
			// Update answer count.
			ap_update_answers_count( $post->post_parent, $ans + 1 );
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

		if ( $post->post_type == 'question' ) {
			ap_update_post_activity_meta( $comment->comment_post_ID, 'new_comment', $comment->user_id );
		} elseif ( $post->post_type == 'answer' ) {
			ap_update_post_activity_meta( $comment->comment_post_ID, 'new_comment_answer', $comment->user_id, true );
		}
	}

	/**
	 * Actions to run after unpublishing a comment.
	 *
	 * @param	object|array $comment Comment object.
	 */
	public static function unpublish_comment( $comment ) {
		$comment = (object) $comment;
		ap_update_post_activity_meta( $comment->comment_post_ID, 'delete_comment', $comment->user_id, true );
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
	 * Build anspress page url constants
	 *
	 * @since 2.4
	 */
	public static function page_urls( $pages ) {
		$page_url = array();
		foreach ( (array) $pages as $slug => $args ) {
					$page_url[ $slug ] = 'ANSPRESS_PAGE_URL_' . strtoupper( $slug );
			}
			return $page_url;
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

			/**
			 * Define default AnsPress pages
			 * So that default pages should work properly after
			 * Changing categories page slug.
		 *
			 * @var array
			 */

			$default_pages	= array(
				'ask' 		     => array(),
				'question' 	   => array()
			);

			/**
			 * Modify default pages of AnsPress
		 *
			 * @param	array $default_pages Default pages of AnsPress.
			 * @return array
			 */
			$pages = array_merge( anspress()->pages, apply_filters( 'ap_default_pages', $default_pages ) );

			$page_url = SELF::page_urls( $pages );

		foreach ( (array) $items as $key => $item ) {
			$slug = array_search( str_replace( array( 'http://', 'https://' ), '', $item->url ), $page_url );

			if ( false !== $slug ) {
				if ( isset( $pages[ $slug ]['logged_in'] ) && $pages[ $slug ]['logged_in'] && ! is_user_logged_in() ) {
					unset( $items[ $key ] );
				}

				$item->url = ap_get_link_to( $slug );
				$item->classes[] = 'anspress-page-link';
				$item->classes[] = 'anspress-page-' . $slug;

				if ( get_query_var( 'ap_page' ) == $slug ) {
					$item->classes[] = 'anspress-active-menu-link';
				}
			}
		}

			return $items;
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
		SELF::$menu_class = $class;
			$pages = anspress()->pages;

			// Return if empty or `$item` is not object.
			if ( empty( $item ) || ! is_object( $item ) ) {
				return SELF::$menu_class;
			}

		foreach ( (array) $pages as $args ) {
			SELF::add_proper_menu_classes( $item );
		}

			return SELF::$menu_class;
	}

	/**
	 * Add proper class for AnsPress menu items.
	 *
	 * @since 3.0.0
	 */
	public static function add_proper_menu_classes( $item ) {
		// Return if not anspress menu.
		if ( ! in_array( 'anspress-page-link', SELF::$menu_class ) ) {
			return;
		}

		if ( ap_get_link_to( get_query_var( 'ap_page' ) ) != $item->url ) {
			$pos = array_search( 'current-menu-item', SELF::$menu_class );
			unset( SELF::$menu_class[ $pos ] );
		}

		// Return if already have ap-dropdown.
		if ( in_array( 'ap-dropdown', SELF::$menu_class ) ) {
			return;
		}

		// Add ap-dropdown and ap-userdp-menu class if profile dropdown.
		if ( in_array( 'anspress-page-profile', SELF::$menu_class ) ) {
			SELF::$menu_class[] = 'ap-dropdown';
			SELF::$menu_class[] = 'ap-userdp-menu';
		}
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
	 *
	 * @param	array $data post data.
	 * @param	array $args Post arguments.
	 * @return array
	 * @since 2.2
	 */
	public static function wp_insert_post_data( $data, $args ) {
			if ( 'question' == $args['post_type'] || 'answer' == $args['post_type'] ) {
					if ( '0' == $args['post_author'] ) {
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
			/**
			 * Action triggered right after updating question/answer.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 */
			do_action( 'ap_processed_update_' . $post->post_type, $post_id, $post );
		} else {
			/**
			 * Action triggered right after inserting new question/answer.
			 *
			 * @param integer $post_id Inserted post ID.
			 * @param object	$post		Inserted post object.
			 * @since 0.9
			 */
			do_action( 'ap_processed_new_' . $post->post_type, $post_id, $post );
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

		// Update total received vote of user.
		//update_user_meta( $receiving_userid, '__up_vote_received', ap_count_vote( false, 'vote_up', false, $receiving_userid ) );
		//update_user_meta( $receiving_userid, '__down_vote_received', ap_count_vote( false, 'vote_down', false, $receiving_userid ) );
	}

	/**
	 * Append variable to post Object.
	 *
	 * @param Object $post Post object.
	 */
	public static function ap_append_vote_count( $post ) {
			if ( ap_opt( 'base_page' ) == $post->ID && ! is_admin() ) {
				$post->post_title = ap_page_title();
			}
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
}
