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
class AnsPress_Ajax {
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	public static function init() {
		anspress()->add_action( 'ap_ajax_suggest_similar_questions', __CLASS__, 'suggest_similar_questions' );
		anspress()->add_action( 'ap_ajax_hover_card', __CLASS__, 'hover_card' );
		anspress()->add_action( 'ap_ajax_toggle_best_answer', __CLASS__, 'toggle_best_answer' );
		anspress()->add_action( 'ap_ajax_load_tinymce', __CLASS__, 'load_tinymce' );
		anspress()->add_action( 'ap_ajax_load_comments', 'AnsPress_Comment_Hooks', 'load_comments' );
		anspress()->add_action( 'ap_ajax_edit_comment_form', 'AnsPress_Comment_Hooks', 'edit_comment_form' );
		anspress()->add_action( 'ap_ajax_new_comment', 'AnsPress_Comment_Hooks','new_comment' );
		anspress()->add_action( 'ap_ajax_edit_comment', 'AnsPress_Comment_Hooks','edit_comment' );
		anspress()->add_action( 'ap_ajax_approve_comment', 'AnsPress_Comment_Hooks','approve_comment' );
		anspress()->add_action( 'ap_ajax_delete_comment', 'AnsPress_Comment_Hooks', 'delete_comment' );
		anspress()->add_action( 'ap_ajax_get_comment', 'AnsPress_Comment_Hooks', 'get_comment' );
		anspress()->add_action( 'ap_ajax_vote', 'AnsPress_Vote', 'vote' );

		// Post actions.
		anspress()->add_action( 'ap_ajax_post_actions', 'AnsPress_Theme', 'post_actions' );
		anspress()->add_action( 'ap_ajax_action_toggle_featured', __CLASS__, 'toggle_featured' );
		anspress()->add_action( 'ap_ajax_action_close', __CLASS__, 'close_question' );
		anspress()->add_action( 'ap_ajax_action_toggle_delete_post', __CLASS__, 'toggle_delete_post' );
		anspress()->add_action( 'ap_ajax_action_delete_permanently', __CLASS__, 'permanent_delete_post' );
		anspress()->add_action( 'ap_ajax_action_status', 'AnsPress_Post_Status', 'change_post_status' );
		anspress()->add_action( 'ap_ajax_action_convert_to_post', __CLASS__, 'convert_to_post' );

		// Flag ajax callbacks.
		anspress()->add_action( 'ap_ajax_action_flag', 'AnsPress_Flag', 'action_flag' );

		// Uploader hooks.
		anspress()->add_action( 'wp_ajax_ap_image_submission', 'AnsPress_Uploader', 'image_submission' );
		anspress()->add_action( 'ap_ajax_delete_attachment', 'AnsPress_Uploader', 'delete_attachment' );

		// List filtering.
		anspress()->add_action( 'ap_ajax_load_filter_order_by', __CLASS__, 'load_filter_order_by' );

		// Subscribe
		anspress()->add_action( 'ap_ajax_subscribe', __CLASS__, 'subscribe_to_question' );

	}

	/**
	 * Show similar questions while asking a question.
	 *
	 * @since 2.0.1
	 */
	public static function suggest_similar_questions() {
		// Die if question suggestion is disabled.
		if ( ap_disable_question_suggestion( ) ) {
			wp_die( 'false' );
		}

		$keyword = ap_sanitize_unslash( 'value', 'request' );
		if ( empty( $keyword ) || ( ! ap_verify_default_nonce() && ! current_user_can( 'manage_options' ) ) ) {
				wp_die( 'false' );
		}

		$keyword = ap_sanitize_unslash( 'value', 'request' );
		$is_admin = (bool) ap_isset_post_value( 'is_admin', false );
		$questions = get_posts( array( // @codingStandardsIgnoreLine
			'post_type' => 'question',
			'showposts' => 10,
			's'         => $keyword,
		));

		if ( $questions ) {
				$items = '<div class="ap-similar-questions-head">';
				$items .= '<h3><i class="apicon-check"></i>' . sprintf( __( '%d similar questions found', 'anspress-question-answer' ), count( $questions ) ) . '</h3>';
				$items .= '<p>' . __( 'We\'ve found similar questions that have already been asked, click to read them.', 'anspress-question-answer' ) . '</p>';
				$items .= '</div>';

			$items .= '<div class="ap-similar-questions">';

			foreach ( (array) $questions as $p ) {
				$count = ap_get_answers_count( $p->ID );
				$p->post_title = ap_highlight_words( $p->post_title, $keyword );

				if ( $is_admin ) {
					$items .= '<div class="ap-q-suggestion-item clearfix"><a class="select-question-button button button-primary button-small" href="' . add_query_arg( array( 'post_type' => 'answer', 'post_parent' => $p->ID ), admin_url( 'post-new.php' ) ) . '">' . __( 'Select', 'anspress-question-answer' ) . '</a><span class="question-title">' . $p->post_title . '</span><span class="acount">' . sprintf( _n( '%d Answer', '%d Answers', $count, 'anspress-question-answer' ), $count ) . '</span></div>';
				} else {
					$items .= '<a class="ap-sqitem clearfix" target="_blank" href="' . get_permalink( $p->ID ) . '"><span class="acount">' . sprintf( _n( '%d Answer', '%d Answers', $count, 'anspress-question-answer' ), $count ) . '</span><span class="ap-title">' . $p->post_title . '</span></a>';
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
	 *
	 * @since 2.0.0
	 */
	public static function toggle_best_answer() {
		$answer_id = (int) ap_sanitize_unslash( 'answer_id', 'r' );

		if ( ! is_user_logged_in() || ! check_ajax_referer( 'select-answer-' . $answer_id, 'nonce', false ) ) {
			ap_ajax_json( 'something_wrong' );
		}

		$_post = ap_get_post( $answer_id );

		// Unselect best answer if already selected.
		if ( ap_have_answer_selected( $_post->post_parent ) ) {
			ap_unselect_answer( $answer_id );
			ap_ajax_json( array(
				'success'    => true,
				'action'     => 'unselected',
				'snackbar' 	 => [ 'message' => __( 'Best answer is unselected for your question.', 'anspress-question-answer' ) ],
				'label'      => __( 'Select', 'anspress-question-answer' ),
			) );
		}

		// Do not allow answer to be selected as best if status is moderate.
		if ( 'moderate' === $_post->post_status || 'trash' === $_post->post_status || 'private' === $_post->post_status ) {
			ap_ajax_json( [
				'success'  => false,
				'snackbar' => [ 'message' => __( 'This answer cannot be selected as best, update status to select as best answer.', 'anspress-question-answer' ) ],
			] );
		}

		// Add question activity meta.
		ap_update_post_activity_meta( $_post->post_parent, 'answer_selected', get_current_user_id() );

		// Add answer activity meta.
		ap_update_post_activity_meta( $_post->ID, 'best_answer', get_current_user_id() );

		/**
		 * Trigger right after selecting an answer.
		 *
		 * @param object $_post Post.
		 */
		do_action( 'ap_select_answer', $_post );

		// Update question qameta.
		ap_set_selected_answer( $_post->post_parent, $_post->ID );

		// Close question if enabled in option.
		if ( ap_opt( 'close_selected' ) ) {
			ap_insert_qameta( $_post->post_parent, [ 'closed' => 1 ] );
		}

		ap_ajax_json( array(
			'success'  => true,
			'action'   => 'selected',
			'snackbar' => [ 'message' => __( 'Best answer is selected for your question.', 'anspress-question-answer' ) ],
			'label'    => __( 'Unselect', 'anspress-question-answer' ),
		) );
	}

	/**
	 * Process ajax trash posts callback.
	 */
	public static function toggle_delete_post() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );

		$failed_response = array(
			'success'  => false,
			'snackbar' => [ 'message' => __( 'Unable to trash this post', 'anspress-question-answer' ) ],
		);

		if ( ! ap_verify_nonce( 'trash_post_' . $post_id ) ) {
			ap_ajax_json( $failed_response );
		}

		$post = ap_get_post( $post_id );

		$post_type = 'question' === $post->post_type ? __( 'Question', 'anspress-question-answer' ) : __( 'Answer', 'anspress-question-answer' );

		if ( 'trash' === $post->post_status ) {

			if ( ! ap_user_can_restore( $post ) ) {
				ap_ajax_json( $failed_response );
			}

			wp_untrash_post( $post->ID );

			ap_ajax_json( array(
				'success'      => true,
				'action' 		   => [ 'active' => false, 'label' => __( 'Delete', 'anspress-question-answer' ), 'title' => __( 'Delete this post (can be restored again)', 'anspress-question-answer' ) ],
				'snackbar' 		 => [ 'message' => sprintf( __( '%s is restored', 'anspress-question-answer' ), $post_type ) ],
				'newStatus'    => 'publish',
				'postMessage' => ap_get_post_status_message( $post_id ),
			) );
		}

		if ( ! ap_user_can_delete_post( $post_id ) ) {
			ap_ajax_json( $failed_response );
		}

		// Delete lock feature.
		// Do not allow post to be trashed if defined time elapsed.
		if ( (time() > (get_the_time( 'U', $post->ID ) + (int) ap_opt( 'disable_delete_after' ))) && ! is_super_admin() ) {
			ap_ajax_json( array(
				'success'  => false,
				'snackbar' => [ 'message' => sprintf( __( 'This post was created %s, hence you cannot trash it','anspress-question-answer' ), ap_human_time( get_the_time( 'U', $post->ID ) ) ) ],
			) );
		}

		wp_trash_post( $post_id );

		ap_ajax_json( array(
			'success'      => true,
			'action' 		   => [ 'active' => true, 'label' => __( 'Undelete', 'anspress-question-answer' ), 'title' => __( 'Restore this post', 'anspress-question-answer' ) ],
			'snackbar' 		 => [ 'message' => sprintf( __( '%s is trashed', 'anspress-question-answer' ), $post_type ) ],
			'newStatus'    => 'trash',
			'postMessage' => ap_get_post_status_message( $post_id ),
		) );
	}

	/**
	 * Handle Ajax callback for permanent delete of post.
	 */
	public static function permanent_delete_post() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );

		if ( ! ap_verify_nonce( 'delete_post_' . $post_id ) || ! ap_user_can_permanent_delete( $post_id ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Sorry, unable to delete post', 'anspress-question-answer' ) ],
			) );
		}

		$post = ap_get_post( $post_id );

		if ( 'question' === $post->post_type ) {
			/**
			 * Triggered right before deleting question.
			 *
			 * @param  integer $post_id question ID.
			 */
			do_action( 'ap_wp_trash_question', $post_id );
		} else {
			/**
			 * Triggered right before deleting answer.
			 *
			 * @param  integer $post_id answer ID.
			 */
			do_action( 'ap_wp_trash_answer', $post_id );
		}

		wp_delete_post( $post_id, true );

		if ( 'question' === $post->post_type ) {
			ap_ajax_json( array(
				'success'  => true,
				'redirect' => ap_base_page_link(),
				'snackbar' => [ 'message' => __( 'Question is deleted permanently', 'anspress-question-answer' ) ],
			) );
		}

		$current_ans = ap_count_published_answers( $post->post_parent );
		$count_label = sprintf( _n( '%d Answer', '%d Answers', $current_ans, 'anspress-question-answer' ), $current_ans );

		ap_ajax_json( array(
			'success'      => true,
			'snackbar'     => [ 'message' => __( 'Answer is deleted permanently', 'anspress-question-answer' ) ],
			'deletePost'   => $post_id,
			'answersCount' => [ 'text' => $count_label, 'number' => $current_ans ],
		) );
	}

	/**
	 * Handle toggle featured question ajax callback
	 */
	public static function toggle_featured() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );

		if ( ! ap_user_can_toggle_featured() || ! ap_verify_nonce( 'set_featured_' . $post_id ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Sorry, you cannot toggle a featured question', 'anspress-question-answer' ) ],
			) );
		}

		$post = ap_get_post( $post_id );

		// Do nothing if post type is not question.
		if ( 'question' !== $post->post_type ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Only question can be set as featured', 'anspress-question-answer' ) ],
			) );
		}

		// Check if current question ID is in featured question array.
		if ( ap_is_featured_question( $post ) ) {
			ap_unset_featured_question( $post->ID );
			ap_ajax_json( array(
				'success'   => true,
				'action' 		=> [ 'active' => false, 'title' => __( 'Mark this question as featured', 'anspress-question-answer' ), 'label' => __( 'Feature', 'anspress-question-answer' ) ],
				'snackbar'  => [ 'message' => __( 'Question is unmarked as featured.', 'anspress-question-answer' ) ],
			));
		}

		ap_set_featured_question( $post->ID );
		ap_ajax_json( array(
			'success'  => true,
			'action'   => [ 'active' => true, 'title' => __( 'Unmark this question as featured', 'anspress-question-answer' ), 'label' => __( 'Unfeature', 'anspress-question-answer' ) ],
			'snackbar' => [ 'message' => __( 'Question is marked as featured.', 'anspress-question-answer' ) ],
		));
	}

	/**
	 * Handle Ajax callback for user hover card
	 */
	public static function hover_card() {
		if ( ap_opt( 'disable_hover_card' ) ) {
			wp_die();
		}

		$id = (int) ap_sanitize_unslash( 'id', 'p' );
		$type = ap_sanitize_unslash( 'type', 'request', 'user' );

		if ( ! ap_verify_default_nonce() ) {
			wp_die();
		}

		/**
		 * AP Hover card actions.
		 *
		 * @param integer $id ID.
		 */
		do_action( 'ap_hover_card_' . $type, $id );
		wp_die();
	}

	/**
	 * Close question callback.
	 */
	public static function close_question() {
		$post_id = ap_sanitize_unslash( 'post_id', 'p' );

		// Check permission and nonce.
		if ( ! is_user_logged_in() || ! check_ajax_referer( 'close_' . $post_id, 'nonce', false ) || ! ap_user_can_close_question() ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'You cannot close a question', 'anspress-question-answer' ) ],
			));
		}

		$_post = ap_get_post( $post_id );
		$toggle = ap_toggle_close_question( $post_id );
		$close_label = $_post->closed ? __( 'Close', 'anspress-question-answer' ) :  __( 'Open', 'anspress-question-answer' );
		$close_title = $_post->closed ? __( 'Close this question for new answer.', 'anspress-question-answer' ) : __( 'Open this question for new answers', 'anspress-question-answer' );

		$message = 1 === $toggle ? __( 'Question closed', 'anspress-question-answer' ) : __( 'Question is opened', 'anspress-question-answer' );

		$results = array(
			'success'     => true,
			'action'      => [ 'label' => $close_label, 'title' => $close_title ],
			'snackbar'    => [ 'message' => $message ],
			'postMessage' => ap_get_post_status_message( $post_id ),
		);

		ap_ajax_json( $results );
	}

	/**
	 * Send JSON response and terminate.
	 *
	 * @param array|string $result Ajax response.
	 */
	public static function send( $result ) {
		ap_send_json( ap_ajax_responce( $result ) );
	}

	/**
	 * Load tinyMCE assets using ajax.
	 *
	 * @since 3.0.0
	 */
	public static function load_tinymce() {
		$settings = ap_tinymce_editor_settings( 'answer' );

		if ( false !== $settings['tinymce'] ) {
			$settings['tinymce'] = array(
				'content_css'      => ap_get_theme_url( 'css/editor.css' ),
				'wp_autoresize_on' => true,
			);
		}

		if ( ap_user_can_upload( ) ) {
			ap_upload_js_init();
			wp_enqueue_script( 'ap-upload', ANSPRESS_URL . 'assets/js/upload.js', [ 'plupload' ] );
		}

		echo '<div class="ap-editor">';
	    wp_editor( '', 'description', $settings );
	    echo '</div>';
	    \_WP_Editors::enqueue_scripts();
	    ob_start();
		print_footer_scripts();
		$scripts = ob_get_clean();
		echo str_replace( 'jquery-core,jquery-migrate,', '', $scripts ); // xss okay.
		\_WP_Editors::editor_js();
	    wp_die();
	}

	/**
	 * Ajax callback for converting a question into a post.
	 *
	 * @since 3.0.0
	 */
	public static function convert_to_post() {
		$post_id = ap_sanitize_unslash( 'post_id', 'r' );

		if ( ! ap_verify_nonce( 'convert-post-' . $post_id ) || ! ( is_super_admin( ) || current_user_can( 'manage_options' ) ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Sorry, you are not allowed to convert this question to post', 'anspress-question-answer' ) ],
			) );
		}

		$row = set_post_type( $post_id, 'post' );

		// After success trash all answers.
		if ( $row ) {
			global $wpdb;

			// Get IDs of all answer.
			$answer_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d and post_type = 'answer' ", (int) $post_id ) ); // db call okay, cache ok.

			foreach ( (array) $answer_ids as $id ) {
				wp_delete_post( $id );
			}

			ap_ajax_json( array(
				'success' => true,
				'snackbar' => [ 'message' => sprintf( __( ' Question "%s" is converted to post and its answers are trashed', 'anspress-question-answer' ), get_the_title( $post_id ) ) ],
				'redirect' => get_the_permalink( $post_id ),
			) );
		}
	}

	/**
	 * Ajax callback for loading order by filter.
	 *
	 * @since 4.0.0
	 */
	public static function load_filter_order_by() {
		$filter = ap_sanitize_unslash( 'filter', 'r' );
		check_ajax_referer( 'filter_' . $filter, '__nonce' );

		ap_ajax_json( array(
			'success'  => true,
			'multiple' => false,
			'items'    => ap_get_questions_orderby(),
		));
	}

	public static function subscribe_to_question() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'r' );

		if ( ! is_user_logged_in() ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'You must be logged in to subscribe to a question', 'anspress-question-answer' ) ],
			) );
		}

		$_post = ap_get_post( $post_id );

		if ( 'question' === $_post->post_type && ! ap_verify_nonce( 'subscribe_' . $post_id ) ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Sorry, unable to subscribe', 'anspress-question-answer' ) ],
			) );
		}

		// Check if already subscribed, toggle if subscribed.
		$exists = ap_get_subscriber( false, 'question', $post_id );

		if ( $exists ) {
			ap_delete_subscribers( 'question', $post_id, get_current_user_id() );
			ap_ajax_json( array(
				'success'  => true,
				'snackbar' => [ 'message' => __( 'Successfully unsubscribed from question', 'anspress-question-answer' ) ],
				'count'    => ap_get_post_field( 'subscribers', $post_id ),
				'label'    => __( 'Subscribe', 'anspress-question-answer' ),
			) );
		}

		// Insert subscriber.
		$insert = ap_new_subscriber( false, 'question', $post_id );

		if ( false === $insert ) {
			ap_ajax_json( array(
				'success' => false,
				'snackbar' => [ 'message' => __( 'Sorry, unable to subscribe', 'anspress-question-answer' ) ],
			) );
		}

		ap_ajax_json( array(
			'success'  => true,
			'snackbar' => [ 'message' => __( 'Successfully subscribed to question', 'anspress-question-answer' ) ],
			'count'    => ap_get_post_field( 'subscribers', $post_id ),
			'label'    => __( 'Unsubscribe', 'anspress-question-answer' ),
		) );
	}
}
