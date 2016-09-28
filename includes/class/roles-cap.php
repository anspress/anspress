<?php
/**
 * Roles and Capabilities
 *
 * @package     AnsPress
 * @subpackage  Classes/Roles
 * @copyright   Copyright (c) 2013, Rahul Aryan
 * @license     https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @since       0.8
 */

/**
 * AnsPress user role helper
 */
class AP_Roles
{
	/**
	 * Base user capabilities
	 * @var array
	 */
	public $base_caps = array();

	/**
	 * Moderator level permissions
	 * @var array
	 */
	public $mod_caps = array();

	/**
	 * Initialize the class
	 */
	public function __construct() {

		/**
		 * Base user caps
		 * @var array
		 */
		$this->base_caps = ap_role_caps('participant' );

		/**
		 * Admin level caps
		 * @var array
		 */
		$this->mod_caps = ap_role_caps('moderator' );

	}

	/**
	 * Add roles and cap, called on plugin activation
	 *
	 * @since 2.0.1
	 */
	public function add_roles() {

		add_role( 'ap_moderator', __( 'AnsPress Moderator', 'anspress-question-answer' ), array(
			'read' => true,
		) );

		add_role( 'ap_participant', __( 'AnsPress Participants', 'anspress-question-answer' ), array( 'read' => true ) );
		add_role( 'ap_banned', __( 'AnsPress Banned', 'anspress-question-answer' ), array( 'read' => true ) );
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

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}
		if ( is_object( $wp_roles ) ) {

			$roles = array( 'editor', 'administrator', 'contributor', 'author', 'ap_participant', 'ap_moderator', 'subscriber' );

			foreach ( $roles as $role_name ) {

				// Add base cpas to all roles.
				foreach ( $this->base_caps as $k => $grant ) {
					$wp_roles->add_cap( $role_name, $k );
				}

				if ( 'editor' == $role_name || 'administrator' == $role_name || 'ap_moderator' == $role_name ) {
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
	public function remove_roles() {
		global $wp_roles;

		if ( class_exists( 'WP_Roles' ) ) {
			if ( ! isset( $wp_roles ) ) {
				$wp_roles = new WP_Roles();
			}
		}
		$wp_roles->remove_role( 'ap_participant' );
		$wp_roles->remove_role( 'ap_moderator' );
		$wp_roles->remove_role( 'ap_banned' );

	}
}


/**
 * Check if a user can ask a question.
 * @param  integer|boolean $user_id User_id.
 * @return boolean
 * @since  2.4.6 Added new argument `$user_id`.
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
	 * @param  boolean|string 	$filter 	Apply this filter, empty string by default.
	 * @param  integer 			$user_id 	User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_ask', '', $user_id );
	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	if ( user_can( $user_id, 'ap_new_question' ) || ( ! is_user_logged_in() && ap_allow_anonymous()) ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can answer on a question
 * @param  integer|object  $question_id    Question id or object.
 * @param  boolean|integer $user_id        User ID.
 * @return boolean
 * @since  2.4.6 Added new argument `$user_id`.
 */
function ap_user_can_answer( $question_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	$question = get_post( $question_id );

	/**
	 * Allow overriding of ap_user_can_answer.
	 * @param boolean|string $filter 		Apply this filter, default is empty string.
	 * @param integer 		 $question_id 	Question ID.
	 * @param integer 		 $user_id 		User ID.
	 * @since 2.4.6 Added 2 new arguments `$question_id` and `$user_id`.
	 */
	$filter = apply_filters( 'ap_user_can_answer', '', $question->ID, $user_id );
	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Return if user cannot read question.
	if ( ! ap_allow_anonymous() && ! ap_user_can_read_question($question_id, $user_id ) ) {
		return false;
	}

	// Check if only admin is allowed to answer.
	if ( ap_opt( 'only_admin_can_answer' ) && ! is_super_admin( $user_id ) ) {
		return false;
	}

	// Do not allow to answer if best answer is selected.
	if ( ap_opt('close_selected' ) && ap_question_best_answer_selected( $question->ID ) ) {
		return false;
	}

	// Bail out if question is closed.
	if ( $question->post_status == 'closed' ) {
		return false;
	}

	// Check if user is original poster and dont allow them to answer their own question.
	if ( ! ap_opt( 'disallow_op_to_answer' ) && $question->post_author == $user_id ) {
		return false;
	}

	// Check if user already answered and if multiple answer disabled then down't allow them to answer.
	if ( user_can( $user_id, 'ap_new_answer' ) ) {
		if ( ! ap_opt( 'multiple_answers' ) && ap_is_user_answered( $question_id, $user_id ) ) {
			return false;
		} else {
			return true;
		}
	}

	// Check if anonymous asnwer is allowed and if yes then return true.
	if ( ap_allow_anonymous() && ! is_user_logged_in() ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can answer on a question
 * @return boolean
 */
function ap_user_can_see_answers() {
	if ( is_super_admin() ) {
		return true; }

	if ( ap_opt( 'logged_in_can_see_ans' ) && ! is_user_logged_in() ) {
		return false;
	}

	return true;
}

/**
 * Check if user can select an answer
 * @param  integer       $post_id    Answer id.
 * @param  integer|false $user_id    user id.
 * @return boolean
 */
function ap_user_can_select_answer($post_id, $user_id = false) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	$answer 	= get_post( $post_id );

	// If not answer then return false.
	if ( 'answer' != $answer->post_type ) {
		return false;
	}

	$question 	= get_post( $answer->post_parent );

	if ( $user_id == $question->post_author ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can edit answer on a question
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

	$answer = get_post( $post_id );

	/**
	 * Filter to hijack ap_user_can_edit_answer. This filter will be applied if filter
	 * returns a boolean value. To baypass return an empty string.
	 * @param string|boolean 	$filter 		Apply this filter.
	 * @param integer 			$question_id 	Question ID.
	 * @param integer 			$user_id 		User ID.
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

	if ( $user_id == $answer->post_author && user_can( $user_id, 'ap_edit_answer' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can edit a question.
 * @param  boolean|integer $post_id Question ID.
 * @param  boolean|integer $user_id User ID.
 * @return boolean
 * @since  2.4.7 Added new argument `$user_id`.
 * @since  2.4.7 Added new filter `ap_user_can_edit_question`.
 */
function ap_user_can_edit_question( $post_id = false, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_edit_others_question' ) ) {
		return true;
	}

	if ( false !== $post_id ) {
		$question = get_post( $post_id );
	} else {
		global $post;
		$question = $post;
	}

	/**
	 * Filter to hijack ap_user_can_edit_question. This filter will be applied if filter
	 * returns a boolean value. To baypass return an empty string.
	 * @param string|boolean 	$filter 		Apply this filter.
	 * @param integer 			$question_id 	Question ID.
	 * @param integer 			$user_id 		User ID.
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

	if ( $user_id == $question->post_author && user_can( $user_id, 'ap_edit_question' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can change post label.
 * @return boolean
 */
function ap_user_can_change_label() {
	if ( is_super_admin() || current_user_can( 'ap_change_label' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can comment on AnsPress posts
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
	 * @param  boolean|string 	$apply_filter 	Apply current filter, empty string by default.
	 * @param  integer|object 	$post_id 		Post ID or object.
	 * @param  integer 			$user_id 		User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_comment', '', $post_id, $user_id );
	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	$post_o = get_post( $post_id );

	// Do not allow to comment if post is moderate.
	if ( 'moderate' === $post_o->post_status ) {
		return false;
	}

	// Don't allow user to comment if they don't have permission to read post.
	if ( ! ap_user_can_read_post( $post_id, $user_id ) ) {
		return false;
	}

	if ( user_can( $user_id, 'ap_new_comment' ) || ap_opt( 'anonymous_comment' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can edit comment
 * @param  integer $comment_id     Comment ID.
 * @return boolean
 * @since 2.4.6 Added an `$user_id`. Also check if user can read post.
 * @since 2.4.6 Added filter ap_user_can_edit_comment.
 */
function ap_user_can_edit_comment( $comment_id,  $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin() || current_user_can( 'ap_mod_comment' ) ) {
		return true;
	}

	$comment = get_comment( $comment_id );

	/**
	 * Filter to hijack ap_user_can_edit_comment.
	 * @param  boolean|string 	$apply_filter 	Apply current filter, empty string by default.
	 * @param  integer|object 	$post_id 		Post ID or object.
	 * @param  integer 			$user_id 		User ID.
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
	if ( '0' == $comment->comment_approved ) {
		return false;
	}

	// Don't allow user to comment if they don't have permission to read post.
	if ( ! ap_user_can_read_post( $comment->comment_post_ID, $user_id ) ) {
		return false;
	}

	if ( user_can( $user_id, 'ap_edit_comment' ) && $user_id == $comment->user_id ) {
		return true;
	}

	return false;
}

/**
 * Check if user can delete comment
 * @param  integer $comment_id Comment_ID.
 * @return boolean
 */
function ap_user_can_delete_comment($comment_id, $user_id = false) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin($user_id ) || user_can( $user_id, 'ap_delete_others_comment' ) ) {
		return true;
	}

	if ( user_can( $user_id, 'ap_delete_comment' ) && $user_id == get_comment( $comment_id )->user_id ) {
		return true;
	}

	return false;
}

/**
 * Check if user can delete AnsPress posts.
 * @param  integer         $post_id    Question or answer ID.
 * @param  integer|boolean $post_id    User ID.
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

	$post_o = get_post( $post_id );
	$type = $post_o->post_type;

	/**
	 * Filter to hijack ap_user_can_delete_post.
	 * @param  boolean|string 	$apply_filter 	Apply current filter, empty string by default.
	 * @param  integer|object 	$post_id 		Post ID or object.
	 * @param  integer 			$user_id 		User ID.
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

	if ( $user_id == $post_o->post_author && user_can( $user_id, 'ap_delete_'.$type ) ) {
		return true;
	} elseif ( user_can( $user_id, 'ap_delete_others_'.$type ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can delete a question.
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
 * @return boolean
 */
function ap_user_can_permanent_delete() {
	if ( is_super_admin() ) {
		return true;
	}

	return false;
}

/**
 * Check if user can restore question or answer.
 * @return boolean
 * @since  3.0.0
 */
function ap_user_can_restore( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Bail if super.
	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	if ( user_can( $user_id, 'ap_restore_posts' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user have permission to view post
 * @param  int $post_id post ID.
 * @param  int $user_id user ID.
 * @return boolean
 * @since  2.0.1
 */
function ap_user_can_view_private_post( $post_id, $user_id = false ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_view_private' ) ) {
		return true;
	}

	$post_o = get_post( $post_id );

	if ( $post_o->post_author == $user_id ) {
		return true;
	}

	// Also allow question author to see all private answers.
	if ( 'answer' == $post_o->post_type ) {
		$question = get_post( $post_o->post_parent );

		if ( $question->post_author == $user_id ) {
			return true;
		}
	}

	return false;
}

/**
 * Check if user can view a moderate post
 * @param  integer $post_id Question ID.
 * @param  integer $user_id User ID.
 * @return boolean
 */
function ap_user_can_view_moderate_post( $post_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Return if user is anonymous.
	if ( empty( $user_id ) ) {
		return false;
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_view_moderate' ) ) {
		return true;
	}

	$post_o = get_post( $post_id );

	if ( $post_o->post_author == $user_id ) {
		return true;
	}

	return false;
}

/**
 * Check if user can view a future post.
 * @param  integer $post_id Post ID.
 * @param  integer $user_id User ID.
 * @return boolean
 */
function ap_user_can_view_future_post( $post_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	// Return if user is anonymous.
	if ( empty( $user_id ) ) {
		return false;
	}

	if ( is_super_admin( $user_id ) || user_can( $user_id, 'ap_view_future' ) ) {
		return true;
	}

	$post_o = get_post( $post_id );

	if ( $post_o->post_author == $user_id ) {
		return true;
	}

	return false;
}

/**
 * Check if user can view post
 * @param  integer $post_id Question or answer ID.
 * @return boolean
 */
function ap_user_can_view_post($post_id = false, $user_id = false) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	$post_o = get_post( $post_id );

	if ( 'private_post' == $post_o->post_status && ap_user_can_view_private_post( $post_o->ID, $user_id ) ) {
		return true;
	}

	if ( 'moderate' == $post_o->post_status && ap_user_can_view_moderate_post( $post_o->ID, $user_id ) ) {
		return true;
	}

	if ( 'future' == $post_o->post_status && ap_user_can_view_future_post( $post_o->ID, $user_id ) ) {
		return true;
	}

	if ( 'publish' == $post_o->post_status || 'closed' == $post_o->post_status ) {
		return true;
	}

	return false;
}

/**
 * Check if anonymous posting is allowed
 * @return boolean
 */
function ap_allow_anonymous() {
	return (bool) ap_opt( 'allow_anonymous' );
}

/**
 * Check if current user can change post status i.e. private_post, moderate, closed
 * @param  integer|object  $post_id    Question or Answer id.
 * @param  integer|boolean $user_id    User id.
 * @return boolean
 * @since  2.1
 * @since  2.4.7 Added new filter `ap_user_can_change_status`.
 * @since  2.4.7 Added new argument `$user_id`.
 **/
function ap_user_can_change_status( $post_id, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( user_can( $user_id, 'ap_change_status_other' ) || is_super_admin( $user_id ) ) {
		return true;
	}

	$post_o = get_post( $post_id );

	/**
	 * Filter to hijack ap_user_can_change_status.
	 * @param  boolean|string 	$apply_filter 	Apply current filter, empty string by default.
	 * @param  integer 			$post_id 		Post ID.
	 * @param  integer 			$user_id 		User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_change_status', '', $post_o->ID, $user_id );
	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	if ( user_can( $user_id, 'ap_change_status' ) && ($post_o->post_author > 0 && $post_o->post_author == $user_id ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can change post status to closed
 * @return boolean
 */
function ap_user_can_change_status_to_closed() {
	if ( is_super_admin() || current_user_can( 'ap_change_status_other' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can change post status to moderate
 * @return boolean
 */
function ap_user_can_change_status_to_moderate() {
	if ( is_super_admin() || current_user_can( 'ap_change_status_other' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can upload an image
 * @return boolean
 */
function ap_user_can_upload_image() {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( is_super_admin() ) {
		return true;
	}

	if ( ap_opt( 'allow_upload_image' ) ) {
		return true;
	}

	return false;
}


/**
 * Check if user can delete an attachment.
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

	$attachment = get_post( $attacment_id );

	if ( ! $attachment ) {
		return false;
	}

	// Check if attachment post author matches `$user_id`.
	if ( $user_id == $attachment->post_author ) {
		return true;
	}

	return false;
}

/**
 * Check if user can upload an avatar
 * @since 2.4
 */
function ap_user_can_upload_avatar() {
	// Return false if profile is not active.
	if ( ! ap_is_profile_active() ) {
		return false;
	}

	if ( is_super_admin() || current_user_can( 'ap_upload_avatar' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can upload a cover image
 * @since 2.4
 */
function ap_user_can_upload_cover() {
	// Return false if profile is not active.
	if ( ! ap_is_profile_active() ) {
		return false;
	}

	if ( is_super_admin() || current_user_can( 'ap_upload_cover' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can edit their profile
 */
function ap_user_can_edit_profile() {
	// Return false if profile is not active.
	if ( ! ap_is_profile_active() ) {
		return false;
	}

	if ( is_super_admin() || current_user_can( 'ap_edit_profile' ) ) {
		return true;
	}

	return false;
}

function ap_show_captcha_to_user( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( apply_filters( 'ap_show_captcha', false, $user_id ) ) {
		return false;
	}

	if ( ap_opt( 'recaptcha_site_key' ) != '' && ap_opt( 'enable_recaptcha' ) ) {
		return true;
	}

	return false;
}

/**
 * Get AnsPress role capabilities by role key.
 * @param  string $role Role key.
 * @return array|false
 * @since 2.4.6
 */
function ap_role_caps( $role ) {
	$roles = array(
		'participant' => array(
			'ap_read_question'         	=> true,
			'ap_read_answer'			=> true,
			'ap_new_question'			=> true,
			'ap_new_answer'				=> true,
			'ap_new_comment'			=> true,
			'ap_edit_question'			=> true,
			'ap_edit_answer'			=> true,
			'ap_edit_comment'			=> true,
			'ap_delete_question'		=> true,
			'ap_delete_answer'			=> true,
			'ap_delete_comment'			=> true,
			'ap_vote_up'				=> true,
			'ap_vote_down'				=> true,
			'ap_vote_flag'				=> true,
			'ap_vote_close'				=> true,
			'ap_upload_cover'			=> true,
			'ap_change_status'			=> true,
			'ap_upload_avatar'			=> true,
			'ap_edit_profile'			=> true,
		),
		'moderator' => array(
			'ap_edit_others_question'	=> true,
			'ap_edit_others_answer'		=> true,
			'ap_edit_others_comment'	=> true,
			'ap_delete_others_question'	=> true,
			'ap_delete_others_answer'	=> true,
			'ap_delete_others_comment'	=> true,
			'ap_view_private'			=> true,
			'ap_view_moderate'			=> true,
			'ap_change_status_other'	=> true,
			'ap_approve_comment'		=> true,
			'ap_no_moderation'			=> true,
			'ap_restore_posts'			=> true,
		),
	);

	$roles = apply_filters( 'ap_role_caps', $roles );

	if ( isset( $roles[ $role ] ) ) {
		return $roles[ $role ];
	}

	return false;
}

/**
 * Check if a user can read post
 * @param  integer|object  $post_id 	Post ID.
 * @param  boolean|integer $user_id     User ID.
 * @return boolean
 * @since  2.4.6
 */
function ap_user_can_read_post( $post_id, $user_id = false, $post_type = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$post_o = get_post( $post_id );

	if ( false === $post_type ) {
		$post_type = $post_o->post_type;
	}

	// If not question or answer then return true.
	if ( ! in_array($post_type, array( 'question', 'answer' ) ) ) {
		return true;
	}

	/**
	 * Allow overriding of ap_user_can_read_post.
	 * @param  boolean|string  	$apply_filter Default is empty string.
	 * @param  integer  		$post_id  	  Question ID.
	 * @param  integer  		$user_id  	  User ID.
	 * @param  string   		$post_type    Post type.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_read_post', '', $post_o->ID, $user_id, $post_type );
	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	// Also return true if user have capability to edit others question.
	if ( user_can( $user_id, 'ap_edit_others_'.$post_type ) ) {
		return true;
	}

	// Do not allow to read trash post.
	if ( 'trash' === $post_o->post_status ) {
		return false;
	}

	// If Answer, check if user can read parent question.
	if ( 'answer' == $post_type ) {
		$answer = get_post( $post_o->post_parent );
		if ( 'private_post' == $answer->post_status && ! ap_user_can_view_private_post( $answer->ID, $user_id ) ) {
			return false;
		} elseif ( 'moderate' == $answer->post_status && ! ap_user_can_view_moderate_post( $answer->ID, $user_id ) ) {
			return false;
		}
	}

	if ( 'private_post' == $post_o->post_status && ! ap_user_can_view_private_post( $post_id, $user_id ) ) {
		return false;
	} elseif ( 'moderate' == $post_o->post_status && ! ap_user_can_view_moderate_post( $post_id, $user_id ) ) {
		return false;
	}

	if ( ! ap_opt('only_logged_in' ) && 'question' == $post_type ) {
		return true;
	}

	if ( ! ap_opt('logged_in_can_see_ans' ) && 'answer' == $post_type ) {
		return true;
	}

	if ( ap_opt('only_logged_in' ) && is_user_logged_in() && 'question' == $post_type ) {
		return true;
	}

	if ( ap_opt('logged_in_can_see_ans' ) && is_user_logged_in() && 'answer' == $post_type ) {
		return true;
	}

	// Finally return false. And break the heart :p.
	return false;
}

/**
 * Check if a user can read question
 * @param  integer|object  $question_id   Question ID.
 * @param  boolean|integer $user_id     User ID.
 * @return boolean
 * @uses   ap_user_can_read_post
 * @since  2.4.6
 */
function ap_user_can_read_question( $question_id, $user_id = false ) {
	return ap_user_can_read_post( $question_id, $user_id, 'question' );
}

/**
 * Check if a user can read answer
 * @param  integer|object  $answer_id   Answer ID.
 * @param  boolean|integer $user_id     User ID.
 * @return boolean
 * @uses   ap_user_can_read_post
 * @since  2.4.6
 */
function ap_user_can_read_answer( $answer_id, $user_id = false ) {
	return ap_user_can_read_post( $answer_id, $user_id, 'answer' );
}

/**
 * Check if user is allowed to cast a vote on post.
 * @param  integer|object  $post_id 	Post ID or Object.
 * @param  string          $type    	Vote type. vote_up or vote_down.
 * @param  boolean|integer $user_id 	User ID.
 * @param  boolean		   $wp_error 	Return WP_Error object.
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

	$type = $type == 'vote_up' ? 'vote_up' : 'vote_down';

	$post_o = get_post( $post_id );

	/**
	 * Filter to hijack ap_user_can_vote_on_post.
	 * @param  boolean|string 	$apply_filter 	Apply current filter, empty string by default.
	 * @param  integer|object 	$post_id 		Post ID or object.
	 * @param  string 		 	$type 			Vote type, vote_up or vote_down.
	 * @param  integer 			$user_id 		User ID.
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
	if ( $post_o->post_author == $user_id ) {
		if ( $wp_error ) {
			return new WP_Error('cannot_vote_own_post', __('Voting on own\'s post is not allowed.', 'anspress-question-answer' ) );
		}
		return false;
	}

	// Check if user can read question/answer, if not then they are not allowed to vote.
	if ( ! ap_user_can_read_post( $post_id, $user_id ) ) {
		if ( $wp_error ) {
			return new WP_Error('you_cannot_vote_on_restricted', __( 'Voting on restricted posts are not allowed.', 'anspress-question-answer' ) );
		}
		return false;
	}

	if ( user_can( $user_id, 'ap_'.$type ) ) {
		return true;
	}

	if ( $wp_error ) {
		return new WP_Error('no_permission', __('You do not have permission to vote.', 'anspress-question-answer' ) );
	}

	return false;
}

/**
 * Check if user can delete comment
 * @param  integer|boolean $user_id User ID.
 * @return boolean
 */
function ap_user_can_approve_comment( $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	/**
	 * Filter to hijack ap_user_can_approve_comment.
	 * @param  boolean|string 	$apply_filter 	Apply current filter, empty string by default.
	 * @param  integer 			$user_id 		User ID.
	 * @return boolean
	 * @since  3.0.0
	 */
	$filter = apply_filters( 'ap_user_can_approve_comment', '', $user_id );
	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	if ( is_super_admin($user_id ) || user_can( $user_id, 'ap_approve_comment' ) ) {
		return true;
	}

	return false;
}
