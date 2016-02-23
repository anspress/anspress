<?php
/**
 * Roles and Capabilities
 *
 * @package     AnsPress
 * @subpackage  Classes/Roles
 * @copyright   Copyright (c) 2013, Rahul Aryan
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
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
 * Check if a user can ask a question
 * @return boolean
 */
function ap_user_can_ask() {

	if ( is_super_admin() || current_user_can( 'ap_new_question' ) ) {
		return true;
	}

	if ( ! is_user_logged_in() && ap_allow_anonymous() ) {
		return true;
	}

	return false;
}

/**
 * Check if a user can answer on a question
 * @param  integer $question_id question id.
 * @return boolean
 */
function ap_user_can_answer($question_id) {
	if ( ap_opt( 'only_admin_can_answer' ) && ! is_super_admin( ) ) {
		return false;
	}

	if ( is_super_admin() ) {
		return true;
	}

	// Filter for applying custom conditions.
	$filter = apply_filters( 'ap_user_can_answer', false );
	if ( false !== $filter ) {
		return $filter;
	}

	$question = get_post( $question_id );

	// Check if user is original poster and dont allow them to answer their own question.
	if ( ! ap_opt( 'disallow_op_to_answer' ) && $question->post_author == get_current_user_id() && is_user_logged_in() ) {
		return false;
	}

	// Bail out if question is closed.
	if ( $question->post_type == 'closed' ) {
		return false;
	}

	// Check if user already answered and if multiple answer disabled then down't allow them to answer.
	if ( current_user_can( 'ap_new_answer' ) ) {
		if ( ! ap_opt( 'multiple_answers' ) && ap_is_user_answered( $question_id, get_current_user_id() ) ) {
			return false; } else {
			return true; }
	}

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
		return false; }

	return true;
}

/**
 * Check if user can select an answer
 * @param  integer       $post_id    Answer id.
 * @param  integer|false $user_id    user id.
 * @return boolean
 */
function ap_user_can_select_answer($post_id, $user_id = false) {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( is_super_admin() ) {
		return true;
	}

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
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
 * @param  integer $post_id Answer id.
 * @return boolean
 */
function ap_user_can_edit_ans($post_id) {
	if ( current_user_can( 'ap_edit_others_answer' ) || is_super_admin() ) {
		return true;
	}

	if ( ! is_user_logged_in() ) {
		return false;
	}

	$answer = get_post( $post_id );

	if ( get_current_user_id() == $answer->post_author && current_user_can( 'ap_edit_answer' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can edit a question
 * @param  false|integer $post_id Question ID.
 * @return boolean
 */
function ap_user_can_edit_question($post_id = false) {
	if ( is_super_admin() || current_user_can( 'ap_edit_others_question' ) ) {
		return true;
	}

	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( false !== $post_id ) {
		$question = get_post( $post_id );
	} else {
		global $post;
		$question = $post;
	}

	if ( get_current_user_id() == $question->post_author && current_user_can( 'ap_edit_question' ) ) {
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
 * @return boolean
 */
function ap_user_can_comment() {
	if ( is_super_admin() || current_user_can( 'ap_new_comment' ) || ap_opt( 'anonymous_comment' ) ) {
		return true;
	}

	return false;
}

/**
 * Check if user can edit comment
 * @param  integer $comment_id     Comment ID.
 * @return boolean
 */
function ap_user_can_edit_comment($comment_id) {
	if ( is_super_admin() || current_user_can( 'ap_mod_comment' ) ) {
		return true;
	}

	if ( current_user_can( 'ap_edit_comment' ) && get_current_user_id() == get_comment( $comment_id )->user_id ) {
		return true;
	}

	return false;
}

/**
 * Check if user can delete comment
 * @param  integer $comment_id Comment_ID.
 * @return boolean
 */
function ap_user_can_delete_comment($comment_id) {
	if ( is_super_admin() || current_user_can( 'ap_mod_comment' ) ) {
		return true;
	}

	if ( current_user_can( 'ap_delete_comment' ) && get_current_user_id() == get_comment( $comment_id )->user_id ) {
		return true;
	}

	return false;
}

/**
 * Check if user can delete AnsPress posts
 * @param  integer $post_id Question or answer ID.
 * @return boolean
 */
function ap_user_can_delete($post_id) {
	if ( is_super_admin() ) {
		return true;
	}

	$post_o = get_post( $post_id );

	if ( get_current_user_id() == $post_o->post_author ) {
		if ( ($post_o->post_type == 'question' && current_user_can( 'ap_delete_question' )) || ($post_o->post_type == 'answer' && current_user_can( 'ap_delete_answer' ) ) ) {
			return true;
		}
	} else {
		if ( $post_o->post_type == 'question' && current_user_can( 'ap_delete_others_question' ) ) {
			return true;
		} elseif ( $post_o->post_type == 'answer' && current_user_can( 'ap_delete_others_answer' ) ) {
			return true;
		}
	}

	return false;
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
 * Check if user can view post
 * @param  integer $post_id Question or answer ID.
 * @return boolean
 */
function ap_user_can_view_post($post_id = false) {
	if ( is_super_admin() ) {
		return true;
	}

	$post_o = get_post( $post_id );

	if ( 'private_post' == $post_o->post_status && ap_user_can_view_private_post( $post_0->ID ) ) {
		return true;
	}

	if ( 'moderate' == $post_o->post_status && ap_user_can_view_moderate_post( $post_0->ID ) ) {
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
 * @param  integer $post_id Question id.
 * @return boolean
 * @since 2.1
 **/
function ap_user_can_change_status($post_id) {

	if ( ! is_user_logged_in() ) {
		return false; }

	if ( current_user_can( 'ap_change_status_other' ) || is_super_admin() ) {
		return true;
	}

	$post_o = get_post( $post_id );

	if ( current_user_can( 'ap_change_status' ) && ($post_o->post_author > 0 && $post_o->post_author == get_current_user_id() ) ) {
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

	if ( is_super_admin() || ap_opt( 'allow_upload_image' ) ) {
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
			'ap_hide_question'			=> true,
			'ap_hide_answer'			=> true,
			'ap_delete_question'		=> true,
			'ap_delete_answer'			=> true,
			'ap_delete_comment'			=> true,
			'ap_vote_up'				=> true,
			'ap_vote_down'				=> true,
			'ap_vote_flag'				=> true,
			'ap_vote_close'				=> true,
			'ap_upload_cover'			=> true,
			'ap_message'				=> true,
			'ap_new_tag'				=> true,
			'ap_change_status'			=> true,
			'ap_upload_avatar'			=> true,
			'ap_edit_profile'			=> true,
		),
		'moderator' => array(
			'ap_edit_others_question'	=> true,
			'ap_edit_others_answer'		=> true,
			'ap_edit_others_comment'	=> true,
			'ap_hide_others_question'	=> true,
			'ap_hide_others_answer'		=> true,
			'ap_hide_others_comment'	=> true,
			'ap_delete_others_question'	=> true,
			'ap_delete_others_answer'	=> true,
			'ap_delete_others_comment'	=> true,
			'ap_change_label'			=> true,
			'ap_view_private'			=> true,
			'ap_view_moderate'			=> true,
			'ap_change_status_other'	=> true,
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
		return false;
	}

	/**
	 * Allow overriding of ap_user_can_read_post.
	 * @param  boolean  $apply_filter Default is false.
	 * @param  integer  $post_id  	  Question ID.
	 * @param  integer  $user_id  	  User ID.
	 * @param  string   $post_type    Post type.
	 * @return boolean
	 * @since  2.4.6
	 */
	if ( apply_filters( 'ap_user_can_read_post', false, $post_id, $user_id, $post_type ) ) {
		return true;
	}

	// Check if user have capability to read question/answer.
	// And then check post status based access.
	if ( user_can( $user_id, 'ap_read_'.$post_type ) ) {
		if ( 'private_post' == $post_o->post_status && ap_user_can_view_private_post( $post_id, $user_id ) ) {
			return true;
		} elseif ( 'moderate' == $post_o->post_status && ap_user_can_view_moderate_post( $post_id, $user_id ) ) {
			return true;
		} elseif ( 'publish' == $post_o->post_status || 'closed' == $post_o->post_status ) {
			return true;
		}
	}

	// Also return true if user have capability to edit others question.
	if ( user_can( $user_id, 'ap_edit_others_'.$post_type ) ) {
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
	 * @param  boolean 			$apply_filter 	Apply current filter, false by default.
	 * @param  integer|object 	$post_id 		Post ID or object.
	 * @param  string 		 	$type 			Vote type, vote_up or vote_down.
	 * @param  integer 			$user_id 		User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	if ( apply_filters( 'ap_user_can_vote_on_post', false, $post_id, $type, $user_id ) ) {
		return true;
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
		return new WP_Error('no_permission', __('Its look like you do not have permission to vote.', 'anspress-question-answer' ) );
	}

	return false;
}
