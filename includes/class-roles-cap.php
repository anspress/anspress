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
		$this->base_caps = array(
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
		);

		/**
		 * Admin level caps
		 * @var array
		 */
		$this->mod_caps = array(
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
		);

	}

	/**
	 * Add roles and cap, called on plugin activation
	 *
	 * @since 2.0.1
	 */
	public function add_roles() {

		add_role( 'ap_moderator', __( 'AnsPress Moderator', 'ap' ), array(
			'read' => true,
		) );

		add_role( 'ap_participant', __( 'AnsPress Participants', 'ap' ), array( 'read' => true ) );
		add_role( 'ap_banned', __( 'AnsPress Banned', 'ap' ), array( 'read' => true ) );
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
 * @return boolean
 * @since 2.0.1
 */
function ap_user_can_view_private_post($post_id) {
	if ( is_super_admin() || current_user_can( 'ap_view_private' ) ) {
		return true;
	}

	$post_o = get_post( $post_id );

	if ( $post_o->post_author == get_current_user_id() ) {
		return true;
	}

	// Also allow question author to see all private answers.
	if ( 'answer' == $post_o->post_type ) {
		$question = get_post( $post_o->post_parent );

		if ( $question->post_author == get_current_user_id() ) {
			return true;
		}
	}

	return false;
}

/**
 * Check if user can view a moderate post
 * @param  integer $question_id Question ID.
 * @return boolean
 */
function ap_user_can_view_moderate_post($question_id) {
	if ( is_super_admin() || current_user_can( 'ap_view_moderate' ) ) {
		return true;
	}

	$post_o = get_post( $question_id );

	if ( $post_o->post_author == get_current_user_id() ) {
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

	if ( false !== $post_id ) {
		$post_id = get_the_ID();
	}

	$post_o = get_post( $post_id );

	if ( 'private_post' == $post_o->post_status && ap_user_can_view_private_post( $post_id ) ) {
		return true;
	}

	if ( 'moderate' == $post_o->post_status && ap_user_can_view_moderate_post( $post_id ) ) {
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

