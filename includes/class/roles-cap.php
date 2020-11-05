<?php
/**
 * Roles and Capabilities
 *
 * @package      AnsPress
 * @subpackage   Roles and Capabilities
 * @copyright    Copyright (c) 2013, Rahul Aryan
 * @author       Rahul Aryan <rah12@live.com>
 * @license      GPL-3.0+
 * @since        0.8
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AnsPress user role helper
 */
class AP_Roles {
	/**
	 * Base user capabilities.
	 *
	 * @var array
	 */
	public $base_caps = array();

	/**
	 * Moderator level permissions.
	 *
	 * @var array
	 */
	public $mod_caps = array();

	/**
	 * Initialize the class
	 */
	public function __construct() {

		/**
		 * Base user caps.
		 *
		 * @var array
		 */
		$this->base_caps = ap_role_caps( 'participant' );

		/**
		 * Admin level caps.
		 *
		 * @var array
		 */
		$this->mod_caps = ap_role_caps( 'moderator' );

	}

	/**
	 * Add roles and cap, called on plugin activation
	 *
	 * @since 2.0.1
	 */
	public function add_roles() {
		// @codingStandardsIgnoreStart
		add_role( 'ap_moderator', __( 'AnsPress Moderator', 'anspress-question-answer' ), array(
			'read' => true,
		) );

		add_role( 'ap_participant', __( 'AnsPress Participants', 'anspress-question-answer' ), array( 'read' => true ) );
		add_role( 'ap_banned', __( 'AnsPress Banned', 'anspress-question-answer' ), array( 'read' => true ) );
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Add new capabilities
	 *
	 * @access public
	 * @since  2.0
	 * @global WP_Roles $wp_roles
	 * @return void
	 */
	public function add_capabilities() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // Override okay.
		}

		if ( is_object( $wp_roles ) ) {
			$roles = [ 'editor', 'administrator', 'contributor', 'author', 'ap_participant', 'ap_moderator', 'subscriber' ];

			foreach ( $roles as $role_name ) {

				// Add base cpas to all roles.
				foreach ( $this->base_caps as $k => $grant ) {
					$wp_roles->add_cap( $role_name, $k );
				}

				if ( in_array( $role_name, [ 'editor', 'administrator', 'ap_moderator' ], true ) ) {
					foreach ( $this->mod_caps as $k => $grant ) {
						$wp_roles->add_cap( $role_name, $k );
					}
				}
			}
		}
	}

	/**
	 * Remove an AnsPress role
	 */
	public static function remove_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles(); // override okay.
		}

		$wp_roles->remove_role( 'ap_participant' );
		$wp_roles->remove_role( 'ap_moderator' );
		$wp_roles->remove_role( 'ap_banned' );
	}
}


/**
 * Check if a user can ask a question.
 *
 * @param  integer|boolean $user_id User_id.
 * @return boolean
 * @since  2.4.6 Added new argument `$user_id`.
 * @since  4.1.0 Updated to use new option post_question_per.
 */
function ap_user_can_ask( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	/**
	 * Filter to hijack ap_user_can_ask function.
	 *
	 * @param  boolean|string   $filter     Apply this filter, empty string by default.
	 * @param  integer          $user_id    User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_ask', '', $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	$option = ap_opt( 'post_question_per' );
	if ( 'have_cap' === $option && is_user_logged_in() && user_can( $user_id, 'ap_new_question' ) ) {
		return true;
	} elseif ( 'logged_in' === $option && is_user_logged_in() ) {
		return true;
	} elseif ( 'anyone' === $option ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can answer on a question.
 *
 * @param  mixed           $question_id    Question id or object.
 * @param  boolean|integer $user_id        User ID.
 * @return boolean
 * @since  2.4.6 Added new argument `$user_id`.
 * @since  4.1.0 Check if `$question_id` argument is a valid question CPT ID. Updated to use new option post_answer_per. Also removed checking of option only_admin_can_answer. Fixed: anonymous cannot answer if allow op to answer option is unchecked.
 */
function ap_user_can_answer( $question_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$question = ap_get_post( $question_id );

	// Return false if not a question.
	if ( ! $question || 'question' !== $question->post_type ) {
		return false;
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	/**
	 * Allow overriding of ap_user_can_answer.
	 *
	 * @param boolean|string $filter        Apply this filter, default is empty string.
	 * @param integer          $question_id     Question ID.
	 * @param integer          $user_id         User ID.
	 * @since 2.4.6          Added 2 new arguments `$question_id` and `$user_id`.
	 */
	$filter = apply_filters( 'ap_user_can_answer', '', $question->ID, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Return if user cannot read question.
	if ( ! ap_user_can_read_question( $question_id, $user_id ) ) {
		return false;
	}

	// Do not allow to answer if best answer is selected.
	if ( ap_opt( 'close_selected' ) && ap_have_answer_selected( $question->ID ) ) {
		return false;
	}

	// Bail out if question is closed.
	if ( is_post_closed( $question ) ) {
		return false;
	}

	// Check if user is original poster and dont allow them to answer their own question.
	if ( is_user_logged_in() && ! ap_opt( 'disallow_op_to_answer' ) && ! empty( $question->post_author ) && $question->post_author == $user_id ) { // loose comparison ok.
		return false;
	}

	// Check if user already answered and if multiple answer disabled then don't allow them to answer.
	if ( is_user_logged_in() && ! ap_opt( 'multiple_answers' ) && ap_is_user_answered( $question->ID, $user_id ) ) {
		return false;
	}

	$option = ap_opt( 'post_answer_per' );
	if ( 'have_cap' === $option && is_user_logged_in() && user_can( $user_id, 'ap_new_answer' ) ) {
		return true;
	} elseif ( 'logged_in' === $option && is_user_logged_in() ) {
		return true;
	} elseif ( 'anyone' === $option ) {
		return true;
	}

	return false;
}

/**
 * Check if user can select an answer.
 *
 * @param  mixed         $_post    Post.
 * @param  integer|false $user_id    user id.
 * @return boolean
 * @since unknown
 * @since 4.1.6 Allow moderators to toggle best answer.
 */
function ap_user_can_select_answer( $_post = null, $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	/**
	 * Filter to hijack ap_user_can_select_answer. This filter will be applied if filter
	 * returns a boolean value. To baypass return an empty string.
	 *
	 * @param string|boolean    $filter         Apply this filter.
	 * @param mixed   $_post Question ID.
	 * @param integer $user_id User ID.
	 */
	$filter = apply_filters( 'ap_user_can_select_answer', '', $_post, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	$answer = ap_get_post( $_post );

	// If not answer then return false.
	if ( 'answer' !== $answer->post_type ) {
		return false;
	}

	$question = ap_get_post( $answer->post_parent );

	if ( is_user_logged_in() && (string) $user_id === $question->post_author ) {
		return true;
	}

	// Allow moderators to toggle best answer.
	if ( user_can( $user_id, 'ap_toggle_best_answer' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can edit answer on a question.
 *
 * @param  mixed           $post    Post.
 * @param  boolean|integer $user_id User id.
 * @return boolean
 * @since  4.0.0
 */
function ap_user_can_edit_post( $post = null, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$_post = ap_get_post( $post );

	if ( ! in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
		return false;
	}

	if ( user_can( $user_id, 'ap_edit_others_' . $_post->post_type ) || is_super_admin( $user_id ) ) {
		return true;
	}

	/**
	 * Filter to hijack ap_user_can_edit_post. This filter will be applied if filter
	 * returns a boolean value. To baypass return an empty string.
	 *
	 * @param string|boolean    $filter         Apply this filter.
	 * @param integer           $question_id    Question ID.
	 * @param integer           $user_id        User ID.
	 */
	$filter = apply_filters( 'ap_user_can_edit_post', '', $_post->ID, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Do not allow to edit if moderate.
	if ( 'moderate' === $_post->post_status ) {
		return false;
	}

	if ( ! ap_user_can_read_post( $_post, $user_id ) ) {
		return false;
	}

	if ( $user_id == $_post->post_author && user_can( $user_id, 'ap_edit_' . $_post->post_type ) ) { // loose comparison ok.
		return true;
	}

	return false;
}

/**
 * Check if a user can edit answer on a question.
 *
 * @param  integer         $post_id Answer id.
 * @param  boolean|integer $user_id User id.
 * @return boolean
 * @since  2.4.7 Renamed function from `ap_user_can_edit_ans` to `ap_user_can_edit_answer`.
 */
function ap_user_can_edit_answer( $post_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( user_can( $user_id, 'ap_edit_others_answer' ) || is_super_admin( $user_id ) ) {
		return true;
	}

	$answer = ap_get_post( $post_id );

	/**
	 * Filter to hijack ap_user_can_edit_answer. This filter will be applied if filter
	 * returns a boolean value. To baypass return an empty string.
	 *
	 * @param string|boolean    $filter         Apply this filter.
	 * @param integer           $question_id    Question ID.
	 * @param integer           $user_id        User ID.
	 */
	$filter = apply_filters( 'ap_user_can_edit_answer', '', $answer->ID, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Do not allow to edit if moderate.
	if ( 'moderate' === $answer->post_status ) {
		return false;
	}

	// No point to let user edit answer if they cannot read.
	if ( ! ap_user_can_read_answer( $answer->ID, $user_id ) ) {
		return false;
	}

	if ( $user_id == $answer->post_author && user_can( $user_id, 'ap_edit_answer' ) ) { // loose comparison ok.
		return true;
	}

	return false;
}

/**
 * Check if user can edit a question.
 *
 * @param  boolean|integer $post_id Question ID.
 * @param  boolean|integer $user_id User ID.
 * @return boolean
 * @since  2.4.7 Added new argument `$user_id`.
 * @since  2.4.7 Added new filter `ap_user_can_edit_question`.
 * @since  4.1.5 Check if valid post type.
 * @since  4.1.8 Fixed: user is not able to edit their own question.
 */
function ap_user_can_edit_question( $post_id = false, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_edit_others_question' ) ) {
		return true;
	}

	if ( false !== $post_id ) {
		$question = ap_get_post( $post_id );
	} else {
		global $post;
		$question = $post;
	}

	// Check post_type.
	if ( ! $question || 'question' !== $question->post_type ) {
		return false;
	}

	/**
	 * Filter to hijack ap_user_can_edit_question. This filter will be applied if filter
	 * returns a boolean value. To baypass return an empty string.
	 *
	 * @param string|boolean    $filter         Apply this filter.
	 * @param integer           $question_id    Question ID.
	 * @param integer           $user_id        User ID.
	 */
	$filter = apply_filters( 'ap_user_can_edit_question', '', $question->ID, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Do not allow to edit if moderate.
	if ( 'moderate' === $question->post_status ) {
		return false;
	}

	if ( ! ap_user_can_read_question( $question->ID, $user_id ) ) {
		return false;
	}

	if ( $user_id == $question->post_author && user_can( $user_id, 'ap_edit_question' ) ) { // loose comparison ok.
		return true;
	}

	return false;
}

/**
 * Check if user can change post label.
 *
 * @return boolean
 */
function ap_user_can_change_label() {
	if ( is_super_admin() || current_user_can( 'ap_change_label' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can comment on AnsPress posts.
 *
 * @param boolean|integer $post_id Post ID.
 * @param boolean|integer $user_id User ID.
 * @return boolean
 * @since 2.4.6 Added two arguments `$post_id` and `$user_id`. Also check if user can read post.
 * @since 2.4.6 Added filter ap_user_can_comment.
 */
function ap_user_can_comment( $post_id = false, $user_id = false ) {
	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	/**
	 * Filter to hijack ap_user_can_comment.
	 *
	 * @param  boolean|string   $apply_filter   Apply current filter, empty string by default.
	 * @param  integer|object   $post_id        Post ID or object.
	 * @param  integer          $user_id        User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_comment', '', $post_id, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	$post_o = ap_get_post( $post_id );

	// Do not allow to comment if post is moderate.
	if ( 'moderate' === $post_o->post_status ) {
		return false;
	}

	// Don't allow user to comment if they don't have permission to read post.
	if ( ! ap_user_can_read_post( $post_id, $user_id ) ) {
		return false;
	}

	$option = ap_opt( 'post_comment_per' );
	if ( 'have_cap' === $option && user_can( $user_id, 'ap_new_comment' ) ) {
		return true;
	} elseif ( 'logged_in' === $option && is_user_logged_in() ) {
		return true;
	} elseif ( 'anyone' === $option ) {
		return true;
	}

	return false;
}

/**
 * Check if user can edit comment.
 *
 * @param  integer       $comment_id Comment ID.
 * @param  integer|false $user_id User ID.
 * @return boolean
 * @since 2.4.6 Added an `$user_id`. Also check if user can read post.
 * @since 2.4.6 Added filter ap_user_can_edit_comment.
 */
function ap_user_can_edit_comment( $comment_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin() || current_user_can( 'ap_mod_comment' ) ) {
		return true;
	}

	$comment = get_comment( $comment_id );

	/**
	 * Filter to hijack ap_user_can_edit_comment.
	 *
	 * @param  boolean|string   $apply_filter   Apply current filter, empty string by default.
	 * @param  integer|object   $post_id        Post ID or object.
	 * @param  integer          $user_id        User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_edit_comment', '', $comment_id, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Do not allow to edit if not approved.
	if ( '0' == $comment->comment_approved ) { // loose comparison ok.
		return false;
	}

	// Don't allow user to comment if they don't have permission to read post.
	if ( ! ap_user_can_read_post( $comment->comment_post_ID, $user_id ) ) {
		return false;
	}

	if ( user_can( $user_id, 'ap_edit_comment' ) && $user_id == $comment->user_id ) { // loose comparison ok.
		return true;
	}

	return false;
}

/**
 * Check if user can delete comment.
 *
 * @param  integer       $comment_id Comment_ID.
 * @param  integer|false $user_id User ID.
 * @return boolean
 */
function ap_user_can_delete_comment( $comment_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_delete_others_comment' ) ) {
		return true;
	}

	if ( user_can( $user_id, 'ap_delete_comment' ) && get_comment( $comment_id )->user_id == $user_id ) { // loose comparison ok.
		return true;
	}

	return false;
}

/**
 * Check if user can delete AnsPress posts.
 *
 * @param  mixed         $post_id    Question or answer ID.
 * @param  integer|false $user_id    User ID.
 * @return boolean
 * @since  2.4.7 Renamed function name from `ap_user_can_delete`.
 * @since  2.4.7 Added filter `ap_user_can_delete_post`.
 */
function ap_user_can_delete_post( $post_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	$post_o = ap_get_post( $post_id );
	$type   = $post_o->post_type;

	// Return if not question or answer post type.
	if ( ! in_array( $post_o->post_type, [ 'question', 'answer' ], true ) ) {
		return false;
	}

	/**
	 * Filter to hijack ap_user_can_delete_post.
	 *
	 * @param  boolean|string   $apply_filter   Apply current filter, empty string by default.
	 * @param  integer|object   $post_id        Post ID or object.
	 * @param  integer          $user_id        User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_delete_post', '', $post_o->ID, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// User must not able to delete post if they cannot read post.
	if ( ! ap_user_can_read_post( $post_id, $user_id ) ) {
		return false;
	}

	if ( $user_id == $post_o->post_author && user_can( $user_id, 'ap_delete_' . $type ) ) { // loose comparison ok.
		return true;
	} elseif ( user_can( $user_id, 'ap_delete_others_' . $type ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can delete a question.
 *
 * @param  object|integer $question   Question ID or object.
 * @param  boolean        $user_id    User ID.
 * @return boolean
 * @since  2.4.7
 * @uses   ap_user_can_delete_post
 */
function ap_user_can_delete_question( $question, $user_id = false ) {
	return ap_user_can_delete_post( $question, $user_id );
}

/**
 * Check if user can delete a answer.
 *
 * @param  object|integer $answer   Answer ID or object.
 * @param  boolean        $user_id  User ID.
 * @return boolean
 * @since  2.4.7
 * @uses   ap_user_can_delete_post
 */
function ap_user_can_delete_answer( $answer, $user_id = false ) {
	return ap_user_can_delete_post( $answer, $user_id );
}

/**
 * Check if user can permanently delete a AnsPress posts
 *
 * @return boolean
 */
function ap_user_can_permanent_delete( $post = null, $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$_post = ap_get_post( $post );

	// Return false if not question or answer.
	if ( ! in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
		return false;
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_delete_post_permanent' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can restore question or answer.
 *
 * @param  boolean|integer $user_id  User ID.
 * @return boolean
 * @since  3.0.0
 */
function ap_user_can_restore( $_post = null, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Bail if super.
	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	$_post = is_object( $_post ) ? $_post : ap_get_post( $_post );

	if ( user_can( $user_id, 'ap_restore_posts' ) || (int) $_post->post_author === $user_id ) {
		return true;
	}

	return false;
}

/**
 * Check if user have permission to view post.
 *
 * @param  mixed         $_post Post.
 * @param  integer|false $user_id user ID.
 * @return boolean
 * @since  2.0.1
 */
function ap_user_can_view_private_post( $_post = null, $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_view_private' ) ) {
		return true;
	}

	$post_o = is_object( $_post ) ? $_post : ap_get_post( $_post );

	if ( ! $post_o || 0 == $user_id ) {
		return false;
	}

	if ( $post_o->post_author == $user_id ) { // loose comparison ok.
		return true;
	}

	// Also allow question author to see all private answers.
	if ( 'answer' === $post_o->post_type ) {
		$question = ap_get_post( $post_o->post_parent );

		if ( $question->post_author == $user_id ) { // loose comparison ok.
			return true;
		}
	}

	return false;
}

/**
 * Check if user can view a moderate post.
 *
 * @param  integer $post_id Question ID.
 * @param  integer $user_id User ID.
 * @return boolean
 * @since  unknown
 * @since  4.1.5 Let user view if post is in their session. This is useful for guest users.
 */
function ap_user_can_view_moderate_post( $post_id = null, $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_view_moderate' ) ) {
		return true;
	}

	$post_o = ap_get_post( $post_id );

	// Bail if not answer or question.
	if ( ! $post_o || ! ap_is_cpt( $post_o ) ) {
		return false;
	}

	if ( is_user_logged_in() && $post_o->post_author == $user_id ) { // loose comparison ok.
		return true;
	}

	if ( ! is_user_logged_in() && '0' === $post_o->post_author && anspress()->session->post_in_session( $post_o ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can view a future post.
 *
 * @param  integer $post_id Post ID.
 * @param  integer $user_id User ID.
 * @return boolean
 * @since  2.4.6
 * @since  4.1.5 Allow first argument to be `null`.
 * @since  4.1.5 Let user view if post is in their session. This is useful for anonymous users.
 */
function ap_user_can_view_future_post( $post_id = null, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_view_future' ) ) {
		return true;
	}

	$_post = ap_get_post( $post_id );

	// Bail if not answer or question.
	if ( ! $_post || ! ap_is_cpt( $_post ) ) {
		return false;
	}

	if ( is_user_logged_in() && $_post && $_post->post_author == $user_id ) { // loose comparison ok.
		return true;
	}

	$session_type  = 'answer' === $_post->post_type ? 'answers' : 'questions';
	$session_posts = anspress()->session->get( $session_type );

	if ( is_array( $session_posts ) && ! is_user_logged_in() && '0' === $_post->post_author && in_array( $_post->ID, $session_posts ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can view post
 *
 * @param  integer|false $post_id Question or answer ID.
 * @param  integer|false $user_id User ID.
 * @return boolean
 */
function ap_user_can_view_post( $post_id = false, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	$post_o = is_object( $post_id ) ? $post_id : ap_get_post( $post_id );

	if ( 'private_post' === $post_o->post_status && ap_user_can_view_private_post( $post_o->ID, $user_id ) ) {
		return true;
	}

	if ( 'moderate' === $post_o->post_status && ap_user_can_view_moderate_post( $post_o->ID, $user_id ) ) {
		return true;
	}

	if ( 'future' === $post_o->post_status && ap_user_can_view_future_post( $post_o->ID, $user_id ) ) {
		return true;
	}

	if ( 'publish' === $post_o->post_status ) {
		return true;
	}

	return false;
}

/**
 * Check if anonymous question posting is allowed.
 *
 * @return boolean
 * @since unknown
 * @since 4.1.0 Updated to use new option post_question_per.
 */
function ap_allow_anonymous() {
	return 'anyone' === ap_opt( 'post_question_per' );
}

/**
 * Check if current user can change post status i.e. private_post, moderate, closed.
 *
 * @param  integer|object  $post_id    Question or Answer id.
 * @param  integer|boolean $user_id    User id.
 * @return boolean
 * @since  2.1
 * @since  2.4.7 Added new filter `ap_user_can_change_status`.
 * @since  2.4.7 Added new argument `$user_id`.
 * @since  4.1.0 Do not allow post author to change their own post status regardless of moderator role.
 **/
function ap_user_can_change_status( $post_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	$post_o = ap_get_post( $post_id );

	if ( ! in_array( $post_o->post_type, [ 'question', 'answer' ], true ) ) {
		return false;
	}

	/**
	 * Filter to hijack ap_user_can_change_status.
	 *
	 * @param  boolean|string   $apply_filter   Apply current filter, empty string by default.
	 * @param  integer          $post_id        Post ID.
	 * @param  integer          $user_id        User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_change_status', '', $post_o->ID, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Do not allow post author to change status if current status is moderate,
	// regardless of moderator user role.
	if ( 'moderate' === $post_o->post_status && $post_o->post_author == $user_id ) {
		return false;
	}

	if ( user_can( $user_id, 'ap_change_status_other' ) ) {
		return true;
	}

	if ( user_can( $user_id, 'ap_change_status' ) &&
	 ( $post_o->post_author > 0 && $post_o->post_author == $user_id ) ) { // loose comparison ok.
		return true;
	}

	return false;
}

/**
 * Check if user can change post status to closed.
 *
 * @param integer $user_id User ID.
 * @return boolean
 */
function ap_user_can_close_question( $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_close_question' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can change post status to moderate.
 *
 * @return boolean
 */
function ap_user_can_change_status_to_moderate() {
	if ( is_super_admin() || current_user_can( 'ap_change_status_other' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can upload an image.
 *
 * @return boolean
 */
function ap_user_can_upload() {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( is_super_admin() ) {
		return true;
	}

	if ( ap_opt( 'allow_upload' ) ) {
		return true;
	}

	return false;
}


/**
 * Check if user can delete an attachment.
 *
 * @param  integer         $attacment_id Attachment ID.
 * @param  boolean|integer $user_id      User ID.
 * @return boolean
 * @since  3.0.0
 */
function ap_user_can_delete_attachment( $attacment_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	$attachment = ap_get_post( $attacment_id );

	if ( ! $attachment ) {
		return false;
	}

	// Check if attachment post author matches `$user_id`.
	if ( $user_id == $attachment->post_author ) { // loose comparison ok.
		return true;
	}

	return false;
}

/**
 * Check if user can by pass a captcha.
 *
 * @param integer|false $user_id User ID.
 *
 * @since 4.1.8 Exclude defined user roles.
 */
function ap_show_captcha_to_user( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Return false if super admin or ap_moderator.
	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_moderator' ) ) {
		return false;
	}

	if ( apply_filters( 'ap_show_captcha', false, $user_id ) ) {
		return false;
	}

	$current_user = wp_get_current_user();
	$opt          = array_keys( ap_opt( 'recaptcha_exclude_roles' ) );
	$intersect    = array_intersect( $current_user->roles, $opt );

	if ( ! empty( $intersect ) && count( $intersect ) > 0 ) {
		return false;
	}

	if ( ap_opt( 'recaptcha_site_key' ) !== '' ) {
		return true;
	}

	return false;
}

/**
 * Get AnsPress role capabilities by role key.
 *
 * @param  string $role Role key.
 * @return array|false
 * @since 2.4.6
 */
function ap_role_caps( $role ) {
	$roles = array(
		'participant' => array(
			'ap_read_question'   => true,
			'ap_read_answer'     => true,
			'ap_read_comment'    => true,
			'ap_new_question'    => true,
			'ap_new_answer'      => true,
			'ap_new_comment'     => true,
			'ap_edit_question'   => true,
			'ap_edit_answer'     => true,
			'ap_edit_comment'    => true,
			'ap_delete_question' => true,
			'ap_delete_answer'   => true,
			'ap_delete_comment'  => true,
			'ap_vote_up'         => true,
			'ap_vote_down'       => true,
			'ap_vote_flag'       => true,
			'ap_vote_close'      => true,
			'ap_upload_cover'    => true,
			'ap_change_status'   => true,
		),
		'moderator'   => array(
			'ap_edit_others_question'   => true,
			'ap_edit_others_answer'     => true,
			'ap_edit_others_comment'    => true,
			'ap_delete_others_question' => true,
			'ap_delete_others_answer'   => true,
			'ap_delete_others_comment'  => true,
			'ap_delete_post_permanent'  => true,
			'ap_view_private'           => true,
			'ap_view_moderate'          => true,
			'ap_change_status_other'    => true,
			'ap_approve_comment'        => true,
			'ap_no_moderation'          => true,
			'ap_restore_posts'          => true,
			'ap_toggle_featured'        => true,
			'ap_toggle_best_answer'     => true,
		),
	);

	$roles = apply_filters( 'ap_role_caps', $roles );

	if ( isset( $roles[ $role ] ) ) {
		return $roles[ $role ];
	}

	return false;
}

/**
 * Check if a user can read post.
 *
 * @param  integer|object  $_post   Post ID.
 * @param  boolean|integer $user_id   User ID.
 * @param  string|integer  $post_type Post type.
 * @return boolean
 * @since  2.4.6
 * @since  4.1.0 Check for options `read_question_per` and `read_answer_per`.
 */
function ap_user_can_read_post( $_post = null, $user_id = false, $post_type = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$post_o    = ap_get_post( $_post );
	$post_type = $post_o->post_type;

	if ( ! $post_o ) {
		return false;
	}

	// If not question or answer then return true.
	if ( ! in_array( $post_type, [ 'question', 'answer' ], true ) ) {
		return true;
	}

	/**
	 * Allow overriding of ap_user_can_read_post.
	 *
	 * @param  boolean|string   $apply_filter Default is empty string.
	 * @param  integer          $post_id      Question ID.
	 * @param  integer          $user_id      User ID.
	 * @param  string           $post_type    Post type.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_read_post', '', $post_o->ID, $user_id, $post_type );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Show if post in user's session.
	if ( ! is_user_logged_in() && '0' === $post_o->post_author && anspress()->session->post_in_session( $post_o ) ) {
		return true;
	}

	// Also return true if user have capability to edit others question.
	if ( user_can( $user_id, 'ap_edit_others_' . $post_type ) ) {
		return true;
	}

	// Do not allow to read trash post.
	if ( 'trash' === $post_o->post_status ) {
		return false;
	}

	// If Answer, check if user can read parent question.
	if ( 'answer' === $post_type ) {
		$answer = ap_get_post( $post_o->post_parent );
		if ( 'private_post' === $answer->post_status && ! ap_user_can_view_private_post( $answer->ID, $user_id ) ) {
			return false;
		} elseif ( 'moderate' === $answer->post_status && ! ap_user_can_view_moderate_post( $answer->ID, $user_id ) ) {
			return false;
		}
	}

	if ( 'private_post' === $post_o->post_status && ! ap_user_can_view_private_post( $post_o->ID, $user_id ) ) {
		return false;
	} elseif ( 'moderate' === $post_o->post_status && ! ap_user_can_view_moderate_post( $post_o->ID, $user_id ) ) {
		return false;
	}

	$option = ap_opt( 'read_' . $post_type . '_per' );

	if ( 'have_cap' === $option && is_user_logged_in() && user_can( $user_id, 'ap_read_' . $post_type ) ) {
		return true;
	} elseif ( 'logged_in' === $option && is_user_logged_in() ) {
		return true;
	} elseif ( 'anyone' === $option ) {
		return true;
	}

	// Finally return false. And break the heart :p.
	return false;
}

/**
 * Check if a user can read question.
 *
 * @param  mixed           $question_id   Question ID.
 * @param  boolean|integer $user_id     User ID.
 * @return boolean
 * @uses   ap_user_can_read_post
 * @since  2.4.6
 */
function ap_user_can_read_question( $question_id, $user_id = false ) {
	return ap_user_can_read_post( $question_id, $user_id, 'question' );
}

/**
 * Check if a user can read answer.
 *
 * @param  integer|object  $answer_id   Answer ID.
 * @param  boolean|integer $user_id     User ID.
 * @return boolean
 * @uses   ap_user_can_read_post
 * @since  2.4.6
 */
function ap_user_can_read_answer( $post = null, $user_id = false ) {
	return ap_user_can_read_post( $post, $user_id, 'answer' );
}

/**
 * Check if user is allowed to cast a vote on post.
 *
 * @param  integer|object  $post_id     Post ID or Object.
 * @param  string          $type        Vote type. vote_up or vote_down.
 * @param  boolean|integer $user_id     User ID.
 * @param  boolean         $wp_error    Return WP_Error object.
 * @return boolean
 * @since  2.4.6
 */
function ap_user_can_vote_on_post( $post_id, $type, $user_id = false, $wp_error = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Return true if super admin.
	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	$type   = 'vote_up' === $type ? 'vote_up' : 'vote_down';
	$post_o = ap_get_post( $post_id );

	/**
	 * Filter to hijack ap_user_can_vote_on_post.
	 *
	 * @param  boolean|string   $apply_filter   Apply current filter, empty string by default.
	 * @param  integer|object   $post_id        Post ID or object.
	 * @param  string           $type           Vote type, vote_up or vote_down.
	 * @param  integer          $user_id        User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_vote_on_post', '', $post_o->ID, $type, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Do not allow post author to vote on self posts.
	if ( $post_o->post_author == $user_id ) { // loose comparison okay.
		if ( $wp_error ) {
			return new WP_Error( 'cannot_vote_own_post', __( 'Voting on own post is not allowed', 'anspress-question-answer' ) );
		}
		return false;
	}

	// Check if user can read question/answer, if not then they are not allowed to vote.
	if ( ! ap_user_can_read_post( $post_id, $user_id ) ) {
		if ( $wp_error ) {
			return new WP_Error( 'you_cannot_vote_on_restricted', __( 'Voting on restricted posts are not allowed.', 'anspress-question-answer' ) );
		}
		return false;
	}

	if ( user_can( $user_id, 'ap_' . $type ) ) {
		return true;
	}

	if ( $wp_error ) {
		return new WP_Error( 'no_permission', __( 'You do not have permission to vote.', 'anspress-question-answer' ) );
	}

	return false;
}

/**
 * Check if user can delete comment.
 *
 * @param  integer|boolean $user_id User ID.
 * @return boolean
 */
function ap_user_can_approve_comment( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	/**
	 * Filter to hijack ap_user_can_approve_comment.
	 *
	 * @param  boolean|string   $apply_filter   Apply current filter, empty string by default.
	 * @param  integer          $user_id        User ID.
	 * @return boolean
	 * @since  3.0.0
	 */
	$filter = apply_filters( 'ap_user_can_approve_comment', '', $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_approve_comment' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can toggle featured question.
 *
 * @param  integer|boolean $user_id User ID.
 * @return boolean
 */
function ap_user_can_toggle_featured( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	/**
	 * Filter to hijack ap_user_can_toggle_featured.
	 *
	 * @param  boolean|string   $apply_filter   Apply current filter, empty string by default.
	 * @param  integer          $user_id        User ID.
	 * @return boolean
	 * @since  3.0.0
	 */
	$filter = apply_filters( 'ap_user_can_toggle_featured', '', $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_toggle_featured' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can read a comment.
 *
 * @param  integer|object  $_comment    Comment id or object.
 * @param  boolean|integer $user_id   User ID.
 * @return boolean
 * @since  4.1.0
 */
function ap_user_can_read_comment( $_comment = false, $user_id = false ) {
	$_comment = get_comment( $_comment );

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	/**
	 * Filter to hijack ap_user_can_read_comment.
	 *
	 * @param  boolean|string   $apply_filter   Apply current filter, empty string by default.
	 * @param  integer|object   $_comment         Comment object.
	 * @param  integer              $user_id            User ID.
	 * @since  4.1.0
	 */
	$filter = apply_filters( 'ap_user_can_read_comment', '', $_comment, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// If user cannot read post then return from here.
	if ( ! ap_user_can_read_post( $_comment->comment_post_ID, $user_id ) ) {
		return false;
	}

	if ( '1' != $_comment->comment_approved && ! ap_user_can_approve_comment( $user_id ) ) {
		return false;
	}

	$option = ap_opt( 'read_comment_per' );
	if ( 'have_cap' === $option && is_user_logged_in() && user_can( $user_id, 'ap_read_comment' ) ) {
		return true;
	} elseif ( 'logged_in' === $option && is_user_logged_in() ) {
		return true;
	} elseif ( 'anyone' === $option ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can read a comments.
 *
 * @param  mixed           $_post     Post ID or object.
 * @param  boolean|integer $user_id   User ID.
 * @return boolean
 * @since  4.1.0
 */
function ap_user_can_read_comments( $_post = null, $user_id = false ) {
	$_post = ap_get_post( $_post );

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	/**
	 * Filter to hijack ap_user_can_read_comments.
	 *
	 * @param  boolean|string   $apply_filter   Apply current filter, empty string by default.
	 * @param  object               $_post            Post ID or object.
	 * @param  integer              $user_id            User ID.
	 * @since  4.1.0
	 */
	$filter = apply_filters( 'ap_user_can_read_comments', '', $_post, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// If user cannot read post then return from here.
	if ( ! ap_user_can_read_post( $_post, $user_id ) ) {
		return false;
	}

	$option = ap_opt( 'read_comment_per' );

	if( 'have_cap' === $option && get_user_by( 'ID', $user_id )->has_cap( 'ap_read_comment' ) ) {
	    return true;
	}
	else if ( 'logged_in' === $option && is_user_logged_in() ) {
	    return true;
	} elseif ( 'anyone' === $option ) {
		return true;
	}

	return false;
}
