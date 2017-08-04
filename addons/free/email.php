<?php
/**
 * Notify admin and users by email.
 *
 * @author     Rahul Aryan <support@anspress.io>
 * @copyright  2014 AnsPress.io & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.io
 * @package    AnsPress
 * @subpackage Email Addon
 *
 * @anspress-addon
 * Addon Name:    Email
 * Addon URI:     https://anspress.io
 * Description:   Notify admin and users by email.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Email addon for AnsPress
 */
class AnsPress_Email_Hooks {

	/**
	 * All emails to send notification.
	 *
	 * @var array
	 */
	public static $emails = array();

	/**
	 * Subject of email to send.
	 *
	 * @var string
	 */
	public static $subject;

	/**
	 * Email body.
	 *
	 * @var string
	 */
	public static $message;

	/**
	 * Initialize the class.
	 */
	public static function init() {
		SELF::ap_default_options();
		anspress()->add_filter( 'comment_notification_recipients', __CLASS__, 'default_recipients', 10, 2 );

		anspress()->add_action( 'ap_after_new_question', __CLASS__, 'question_subscription', 10, 2 );
		anspress()->add_action( 'ap_after_new_answer', __CLASS__, 'answer_subscription', 10, 2 );
		anspress()->add_action( 'ap_publish_comment', __CLASS__, 'comment_subscription' );
		anspress()->add_action( 'before_delete_post', __CLASS__, 'delete_subscriptions' );
		anspress()->add_action( 'deleted_comment', __CLASS__, 'delete_comment_subscriptions' );

		anspress()->add_action( 'ap_option_groups', __CLASS__, 'register_option', 100 );
		anspress()->add_action( 'ap_after_new_question', __CLASS__, 'ap_after_new_question' );
		anspress()->add_action( 'ap_after_new_answer', __CLASS__, 'ap_after_new_answer' );
		anspress()->add_action( 'ap_select_answer', __CLASS__, 'select_answer' );
		anspress()->add_action( 'ap_publish_comment', __CLASS__, 'new_comment' );
		anspress()->add_action( 'ap_after_update_question', __CLASS__, 'ap_after_update_question', 10, 2 );
		anspress()->add_action( 'ap_after_update_answer', __CLASS__, 'ap_after_update_answer', 10, 2 );
		anspress()->add_action( 'ap_trash_question', __CLASS__, 'ap_trash_question', 10, 2 );
		anspress()->add_action( 'ap_trash_answer', __CLASS__, 'ap_trash_answer', 10, 2 );


	}

	/**
	 * Return empty reccipients for default comment notifications.
	 *
	 * @param array   $recipients Array of recipients.
	 * @param intgere $comment_id Comment ID.
	 * @return array
	 */
	public static function default_recipients( $recipients, $comment_id ) {
		$_comment = get_comment( $comment_id );

		if ( 'anspress' === $_comment->comment_type ) {
			return [];
		}

		return $recipients;
	}

	/**
	 * Apppend default options
	 *
	 * @since   4.0.0
	 */
	public static function ap_default_options() {
		$defaults = [];
		$defaults['notify_admin_email']         = get_option( 'admin_email' );
		$defaults['plain_email']                = false;
		$defaults['notify_admin_new_question']  = true;
		$defaults['notify_admin_new_answer']    = true;
		$defaults['notify_admin_new_comment']   = true;
		$defaults['notify_admin_edit_question'] = true;
		$defaults['notify_admin_edit_answer']   = true;
		$defaults['notify_admin_trash_question'] = true;
		$defaults['notify_admin_trash_answer']  = true;

		$defaults['new_question_email_subject'] = __( 'New question posted by {asker}', 'anspress-question-answer' );
		$defaults['new_question_email_body']    = __( "Hello!\nA new question is posted by {asker}\n\nTitle: {question_title}\nDescription:\n{question_excerpt}\n\nLink: {question_link}", 'anspress-question-answer' );

		$defaults['new_answer_email_subject'] = __( 'New answer posted by {answerer}', 'anspress-question-answer' );
		$defaults['new_answer_email_body']    = __( "Hello!\nA new answer is posted by {answerer} on {question_title}\nAnswer:\n{answer_excerpt}\n\nLink: {answer_link}", 'anspress-question-answer' );

		$defaults['select_answer_email_subject'] = __( 'Your answer was selected as best', 'anspress-question-answer' );
		$defaults['select_answer_email_body']    = __( "Hello!\nYour answer on '{question_title}' was selected as best.\n\nLink: {answer_link}", 'anspress-question-answer' );

		$defaults['new_comment_email_subject'] = __( 'New comment by {commenter}', 'anspress-question-answer' );
		$defaults['new_comment_email_body']    = __( "Hello!\nA new comment posted on '{question_title}' by {commenter}.\n\nLink: {comment_link}", 'anspress-question-answer' );

		$defaults['edit_question_email_subject'] = __( 'A question is edited by {editor}', 'anspress-question-answer' );
		$defaults['edit_question_email_body']    = __( "Hello!\nQuestion '{question_title}' is edited by {editor}.\n\nLink: {question_link}", 'anspress-question-answer' );

		$defaults['edit_answer_email_subject'] = __( 'An answer is edited by {editor}', 'anspress-question-answer' );
		$defaults['edit_answer_email_body']    = __( "Hello!\nAnswer on '{question_title}' is edited by {editor}.\n\nLink: {question_link}", 'anspress-question-answer' );

		$defaults['trash_question_email_subject'] = __( 'A question is trashed by {user}', 'anspress-question-answer' );
		$defaults['trash_question_email_body']    = __( "Hello!\nQuestion '{question_title}' is trashed by {user}.\n", 'anspress-question-answer' );

		$defaults['trash_answer_email_subject'] = __( 'An answer is trashed by {user}', 'anspress-question-answer' );
		$defaults['trash_answer_email_body']    = __( "Hello!\nAnswer on '{question_title}' is trashed by {user}.\n", 'anspress-question-answer' );

		ap_add_default_options( $defaults );
	}

	/**
	 * Sanitize form value
	 * @param  string $name Field value.
	 * @return string
	 */
	public static function value($name) {
		$settings = ap_opt();
		if ( isset( $settings[ $name ] ) ) {
			return str_replace( '//', '', $settings[ $name ] );
		}

		return '';
	}


	/**
	 * Register options
	 */
	public static function register_option() {
		ap_register_option_group( 'email', __( 'Email', 'anspress-question-answer' ) );

		ap_register_option_section( 'email', 'admin_notify', __( 'Notify admin(s)', 'anspress-question-answer' ) , array(
			array(
				'name'          => 'notify_admin_email',
				'label'         => __( 'Admin email', 'anspress-question-answer' ),
				'desc'          => __( 'Enter email where admin notification should be sent', 'anspress-question-answer' ),
				'type'          => 'text',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'notify_admin_new_question',
				'label' => __( 'New question', 'anspress-question-answer' ),
				'desc' => __( 'Send email to admin for every new question.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'notify_admin_new_answer',
				'label' => __( 'New answer', 'anspress-question-answer' ),
				'desc' => __( 'Send email to admin for every new answer.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'notify_admin_new_comment',
				'label' => __( 'New comment', 'anspress-question-answer' ),
				'desc' => __( 'Send email to admin for every new comment.', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'notify_admin_edit_question',
				'label' => __( 'Edit question', 'anspress-question-answer' ),
				'desc' => __( 'Send email to admin when question is edited', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'notify_admin_edit_answer',
				'label' => __( 'Edit answer', 'anspress-question-answer' ),
				'desc' => __( 'Send email to admin when answer is edited', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'notify_admin_trash_question',
				'label' => __( 'Delete question', 'anspress-question-answer' ),
				'desc' => __( 'Send email to admin when question is trashed', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
			array(
				'name' => 'notify_admin_trash_answer',
				'label' => __( 'Delete answer', 'anspress-question-answer' ),
				'desc' => __( 'Send email to admin when asnwer is trashed', 'anspress-question-answer' ),
				'type' => 'checkbox',
				'show_desc_tip' => false,
			),
		));

		ap_register_option_section( 'email', 'email_templates', __( 'Templates', 'anspress-question-answer' ) , array(
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'New question', 'anspress-question-answer' ) . '</span>',
			),
			array(
				'name' => 'new_question_email_subject',
				'label' => __( 'Subject', 'anspress-question-answer' ),
				'type' => 'text',
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'new_question_email_body',
				'label' => __( 'Body', 'anspress-question-answer' ),
				'type' => 'textarea',
				'attr' => 'style="width:100%;min-height:200px"',
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'New Answer', 'anspress-question-answer' ) . '</span>',
			),
			array(
				'name' => 'new_answer_email_subject',
				'label' => __( 'Subject', 'anspress-question-answer' ),
				'type' => 'text',
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'new_answer_email_body',
				'label' => __( 'Body', 'anspress-question-answer' ),
				'type' => 'textarea',
				'attr' => 'style="width:100%;min-height:200px"',
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Select Answer', 'anspress-question-answer' ) . '</span>',
			),
			array(
				'name' => 'select_answer_email_subject',
				'label' => __( 'Subject', 'anspress-question-answer' ),
				'type' => 'text',
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'select_answer_email_body',
				'label' => __( 'Body', 'anspress-question-answer' ),
				'type' => 'textarea',
				'attr' => 'style="width:100%;min-height:200px"',
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'New comment', 'anspress-question-answer' ) . '</span>',
			),
			array(
				'name' => 'new_comment_email_subject',
				'label' => __( 'Subject', 'anspress-question-answer' ),
				'type' => 'text',
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'new_comment_email_body',
				'label' => __( 'Body', 'anspress-question-answer' ),
				'type' => 'textarea',
				'attr' => 'style="width:100%;min-height:200px"',
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Edit question', 'anspress-question-answer' ) . '</span>',
			),
			array(
				'name' => 'edit_question_email_subject',
				'label' => __( 'Subject', 'anspress-question-answer' ),
				'type' => 'text',
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'edit_question_email_body',
				'label' => __( 'Body', 'anspress-question-answer' ),
				'type' => 'textarea',
				'attr' => 'style="width:100%;min-height:200px"',
			),
			array(
				'name' => '__sep',
				'type' => 'custom',
				'html' => '<span class="ap-form-separator">' . __( 'Edit answer', 'anspress-question-answer' ) . '</span>',
			),
			array(
				'name' => 'edit_answer_email_subject',
				'label' => __( 'Subject', 'anspress-question-answer' ),
				'type' => 'text',
				'attr' => 'style="width:80%"',
			),
			array(
				'name' => 'edit_answer_email_body',
				'label' => __( 'Body', 'anspress-question-answer' ),
				'type' => 'textarea',
				'attr' => 'style="width:100%;min-height:200px"',
			),
		));
	}

	/**
	 * Email header.
	 */
	public static function header() {
		$header = '';

		if ( ! $charset = get_bloginfo( 'charset' ) ) {
			$charset = 'utf-8';
		}

		$header .= 'Content-type: text/plain; charset=' . $charset . "\r\n";

		return $header;
	}

	/**
	 * Replace tags in email template.
	 *
	 * @param string $content Template content.
	 * @param array  $args Arguments.
	 * @return string
	 */
	public static function replace_tags( $content, $args ) {
		return strtr( $content, $args );
	}

	/**
	 * Send email to users.
	 *
	 * @param string $email Email id to send.
	 * @param string $subject Email subject.
	 * @param string $message Email body.
	 */
	public static function send_mail( $email, $subject, $message ) {
		if ( defined( 'AP_DISABLE_EMAIL' ) && true === AP_DISABLE_EMAIL ) {
			return;
		}

		wp_mail( $email, $subject, $message, SELF::header() );
	}

	/**
	 * Add email to object.
	 *
	 * @param string|array $email Email of array of emails.
	 */
	public static function add_email( $email ) {

		if ( is_array( $email ) ) {
			foreach ( $email as $e ) {
				if ( is_email( $email ) && ! in_array( $e, SELF::$emails, true ) ) {
					SELF::$emails[] = $email;
				}
			}
		} elseif ( is_email( $email ) && ! in_array( $email, SELF::$emails, true ) ) {
			SELF::$emails[] = $email;
		}
	}

	/**
	 * Check if class has emails.
	 */
	public static function have_emails() {
		return count( SELF::$emails ) > 0;
	}

	/**
	 * Sends email to array of email ids.
	 */
	public static function initiate_send_email() {
		SELF::$emails = array_unique( SELF::$emails );

		SELF::$emails = apply_filters( 'ap_emails_to_notify', SELF::$emails );

		foreach ( (array) SELF::$emails as $email ) {
			SELF::send_mail( $email, SELF::$subject, SELF::$message );
		}
	}

	/**
	 * Send email to admin when new question is created.
	 *
	 * @param  integer $question_id Question ID.
	 * @since 1.0
	 */
	public static function ap_after_new_question( $question_id ) {
		if ( ap_opt( 'notify_admin_new_question' ) ) {

			$current_user = wp_get_current_user();
			$question = ap_get_post( $question_id );

			// Don't bother if current user is admin.
			if ( ap_opt( 'notify_admin_email' ) == $current_user->user_email ) { // WPCS: loose comparison okay.
				return;
			}

			$tags = array(
				'{asker}'             => ap_user_display_name( $question->post_author ),
				'{question_title}'    => $question->post_title,
				'{question_link}'     => get_permalink( $question->ID ),
				'{question_content}'  => $question->post_content,
				'{question_excerpt}'  => ap_truncate_chars( strip_tags( $question->post_content ), 100 ),
			);

			/**
			 * Allow adding custom email tags for new question notification.
			 *
			 * @param array  $tags     Default email tags.
			 * @param object $question Question object.
			 * @since unknown
			 * @since 4.1.0 Added new argument `$question`.
			 */
			$tags = apply_filters( 'ap_new_question_email_tags', $tags, $question );

			SELF::$subject = SELF::replace_tags( ap_opt( 'new_question_email_subject' ), $tags );
			SELF::$message = SELF::replace_tags( ap_opt( 'new_question_email_body' ), $tags );
			SELF::$emails[] = ap_opt( 'notify_admin_email' );
			SELF::initiate_send_email();
		}
	}

	/**
	 * Send email after new answer.
	 *
	 * @param integer $answer_id Answer ID.
	 */
	public static function ap_after_new_answer( $answer_id ) {
		$current_user = wp_get_current_user();
		$answer = ap_get_post( $answer_id );

		if ( ap_opt( 'notify_admin_new_answer' ) && ap_opt( 'notify_admin_email' ) !== $current_user->user_email ) {
			SELF::add_email( ap_opt( 'notify_admin_email' ) );
		}

		if ( 'private_post' !== $answer->post_status && 'moderate' !== $answer->post_status ) {
			$subscribers = ap_get_subscribers( 'question', $answer->post_parent );

			foreach ( (array) $subscribers as $s ) {
				if ( ap_user_can_view_post( $answer ) && $s->user_email !== $current_user->user_email ) {
					SELF::add_email( $s->user_email );
				}
			}
		}

		// Check if have emails before proceeding.
		if ( ! SELF::have_emails() ) {
			return;
		}

		$args = array(
			'{answerer}'        => ap_user_display_name( $answer->post_author ),
			'{question_title}'  => $answer->post_title,
			'{answer_link}'     => get_permalink( $answer->ID ),
			'{answer_content}'  => $answer->post_content,
			'{answer_excerpt}'  => ap_truncate_chars( strip_tags( $answer->post_content ), 100 ),
		);

		$args = apply_filters( 'ap_new_answer_email_tags', $args );

		SELF::$subject = SELF::replace_tags( ap_opt( 'new_answer_email_subject' ), $args );
		SELF::$message = SELF::replace_tags( ap_opt( 'new_answer_email_body' ), $args );

		SELF::initiate_send_email();
	}

	/**
	 * Notify answer author that his answer is selected as best.
	 *
	 * @param  object $_post Selected answer object.
	 */
	public static function select_answer( $_post ) {
		if ( get_current_user_id() === $_post->post_author ) {
			return;
		}

		$args = array(
			'{answerer}'        => ap_user_display_name( $_post->post_author ),
			'{question_title}'  => $_post->post_title,
			'{answer_link}'     => get_permalink( $_post->ID ),
			'{answer_content}'  => $_post->post_content,
			'{answer_excerpt}'  => ap_truncate_chars( strip_tags( $_post->post_content ), 100 ),
		);

		$args = apply_filters( 'ap_select_answer_email_tags', $args );

		$subject = SELF::replace_tags( ap_opt( 'select_answer_email_subject' ), $args );

		$message = SELF::replace_tags( ap_opt( 'select_answer_email_body' ), $args );
		SELF::send_mail( get_the_author_meta( 'email', $_post->post_author ), $subject, $message );
	}

	/**
	 * Notify admin on new comment and is not approved.
	 *
	 * @param object $comment Comment object.
	 */
	public static function new_comment( $comment ) {

		$current_user = wp_get_current_user();
		$post = ap_get_post( $comment->comment_post_ID );

		$subscribers = ap_get_subscribers( 'comment_' . $comment->comment_post_ID );
		$post_author  = get_user_by( 'id', $post->post_author );

		if ( ap_opt( 'notify_admin_new_comment' ) && ap_opt( 'notify_admin_email' ) !== $current_user->user_email ) {
			SELF::add_email( ap_opt( 'notify_admin_email' ) );
		}

		if ( $subscribers && ap_in_array_r( $post_author->data->user_email, $subscribers ) &&
			$post_author->data->user_email !== $current_user->user_email ) {
			SELF::add_email( $post_author->data->user_email );
		}

		foreach ( (array) $subscribers as $s ) {
			if ( ap_user_can_view_post( $post ) && $s->user_email !== $current_user->user_email ) {
				SELF::add_email( $s->user_email );
			}
		}

		// Check if have emails before proceeding.
		if ( ! SELF::have_emails() ) {
			return;
		}

		$args = array(
			'{commenter}'         => ap_user_display_name( $comment->user_id ),
			'{question_title}'    => $post->post_title,
			'{comment_link}'      => get_comment_link( $comment ),
			'{comment_content}'   => $comment->comment_content,
		);

		$args = apply_filters( 'ap_new_comment_email_tags', $args );
		SELF::$subject = SELF::replace_tags( ap_opt( 'new_comment_email_subject' ), $args );
		SELF::$message = SELF::replace_tags( ap_opt( 'new_comment_email_body' ), $args );

		SELF::initiate_send_email();
	}

	/**
	 * Notify after question get updated.
	 *
	 * @param object $question Question object.
	 * @param string $event Type of update event.
	 */
	public static function ap_after_update_question( $question, $event ) {
		if ( 'edited' !== $event ) {
			return;
		}

		$current_user = wp_get_current_user();

		// Notify admin if current user is not admin itself.
		if ( ap_opt( 'notify_admin_email' ) !== $current_user->user_email && ap_opt( 'notify_admin_edit_question' ) ) {
			SELF::add_email( ap_opt( 'notify_admin_email' ) );
		}

		$subscribers = ap_get_subscribers( 'question', $question->ID );
		$post_author  = get_user_by( 'id', $question->post_author );

		if ( $subscribers && ! ap_in_array_r( $post_author->data->user_email, $subscribers ) &&
			$post_author->data->user_email !== $current_user->user_email ) {
			SELF::add_email( $post_author->data->user_email );
		}

		foreach ( (array) $subscribers as $s ) {
			if ( ap_user_can_view_post( $question ) && ! empty( $s->user_email ) &&
				$s->user_email !== $current_user->user_email ) {
				SELF::add_email( $s->user_email );
			}
		}

		// Check if have emails before proceeding.
		if ( ! SELF::have_emails() ) {
			return;
		}

		$args = array(
			'{asker}'             => ap_user_display_name( $question->post_author ),
			'{editor}'            => ap_user_display_name( get_current_user_id() ),
			'{question_title}'    => $question->post_title,
			'{question_link}'     => get_permalink( $question->ID ),
			'{question_content}'  => $question->post_content,
			'{question_excerpt}'  => ap_truncate_chars( strip_tags( $question->post_content ), 100 ),
		);

		$args = apply_filters( 'ap_edit_question_email_tags', $args );
		SELF::$subject = SELF::replace_tags( ap_opt( 'edit_question_email_subject' ), $args );
		SELF::$message = SELF::replace_tags( ap_opt( 'edit_question_email_body' ), $args );
		SELF::initiate_send_email();
	}

	/**
	 * Notify users after answer gets updated.
	 *
	 * @param object $answer Answer object.
	 * @param string $event Event type.
	 */
	public static function ap_after_update_answer( $answer, $event ) {
		if ( 'edited' !== $event ) {
			return;
		}

		$answer = ap_get_post( $answer );
		$current_user = wp_get_current_user();

		if ( ap_opt( 'notify_admin_email' ) !== $current_user->user_email &&
			ap_opt( 'notify_admin_edit_answer' ) ) {
			SELF::add_email( ap_opt( 'notify_admin_email' ) );
		}

		$a_subscribers = (array) ap_get_subscribers( 'answer_' . $answer->post_parent );
		$q_subscribers = (array) ap_get_subscribers( 'question', $answer->post_parent );
		$subscribers = array_merge( $a_subscribers, $q_subscribers );
		$post_author  = get_user_by( 'id', $answer->post_author );

		if ( ! ap_in_array_r( $post_author->data->user_email, $subscribers ) &&
			$current_user->user_email !== $post_author->data->user_email ) {
			SELF::add_email( $post_author->data->user_email );
		}

		foreach ( (array) $subscribers as $s ) {
			if ( ap_user_can_view_post( $answer ) && ! empty( $s->user_email ) &&
				$s->user_email !== $current_user->user_email ) {
				SELF::add_email( $s->user_email );
			}
		}

		// Check if have emails before proceeding.
		if ( ! SELF::have_emails() ) {
			return;
		}

		$args = array(
			'{answerer}'          => ap_user_display_name( $answer->post_author ),
			'{editor}'            => ap_user_display_name( get_current_user_id() ),
			'{question_title}'    => $answer->post_title,
			'{question_link}'     => get_permalink( $answer->post_parent ),
			'{answer_content}'    => $answer->post_content,
		);

		$args = apply_filters( 'ap_edit_answer_email_tags', $args );
		SELF::$subject = SELF::replace_tags( ap_opt( 'edit_answer_email_subject' ), $args );
		SELF::$message = SELF::replace_tags( ap_opt( 'edit_answer_email_body' ), $args );
		SELF::initiate_send_email();
	}

	/**
	 * Notify admin on trashing a question.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post Post object.
	 */
	public static function ap_trash_question( $post_id, $_post ) {
		if ( ! ap_opt( 'notify_admin_trash_question' ) ) {
			return;
		}

		$current_user = wp_get_current_user();

		// Don't bother if current user is admin.
		if ( ap_opt( 'notify_admin_email' ) === $current_user->user_email ) {
			return;
		}

		$args = array(
			'{user}'              => ap_user_display_name( get_current_user_id() ),
			'{question_title}'    => $_post->post_title,
			'{question_link}'     => get_permalink( $_post->ID ),
		);

		$args = apply_filters( 'ap_trash_question_email_tags', $args );
		$subject = SELF::replace_tags( ap_opt( 'trash_question_email_subject' ), $args );
		$message = SELF::replace_tags( ap_opt( 'trash_question_email_body' ), $args );

		SELF::send_mail( ap_opt( 'notify_admin_email' ), $subject, $message );
	}

	/**
	 * Notify admin on trashing a answer.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post Post object.
	 */
	public static function ap_trash_answer( $post_id, $_post ) {

		if ( ! ap_opt( 'notify_admin_trash_answer' ) ) {
			return;
		}

		$current_user = wp_get_current_user();

		// Don't bother if current user is admin.
		if ( ap_opt( 'notify_admin_email' ) === $current_user->user_email ) {
			return;
		}

		$args = array(
			'{user}'              => ap_user_display_name( get_current_user_id() ),
			'{question_title}'    => $_post->post_title,
			'{question_link}'     => get_permalink( $_post->post_parent ),
		);

		$args = apply_filters( 'ap_trash_answer_email_tags', $args );
		$subject = SELF::replace_tags( ap_opt( 'trash_answer_email_subject' ), $args );
		$message = SELF::replace_tags( ap_opt( 'trash_answer_email_body' ), $args );

		// Sends email.
		SELF::send_mail( ap_opt( 'notify_admin_email' ), $subject, $message );
	}

	/**
	 * Subscribe OP to his own question.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post post objct.
	 */
	public static function question_subscription( $post_id, $_post ) {
		if ( $_post->post_author > 0 ) {
			ap_new_subscriber( $_post->post_author, 'question', $_post->ID );
		}
	}

	/**
	 * Subscribe to answer.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post post objct.
	 */
	public static function answer_subscription( $post_id, $_post ) {
		if ( $_post->post_author > 0 ) {
			ap_new_subscriber( $_post->post_author, 'answer_' . $_post->post_parent, $_post->ID );
		}
	}

	/**
	 * Add comment subscriber.
	 *
	 * @param object $comment Comment object.
	 */
	public static function comment_subscription( $comment ) {
		if ( $comment->user_id > 0 ) {
			ap_new_subscriber( $comment->user_id, 'comment_' . $comment->comment_post_ID, $comment->comment_ID );
		}
	}

	/**
	 * Delete subscriptions.
	 *
	 * @param integer $postid Post ID.
	 */
	public static function delete_subscriptions( $postid ) {
		$_post = get_post( $postid );

		if ( 'question' === $_post->post_type ) {
			// Delete question subscriptions.
			ap_delete_subscribers( 'question', $postid );
		}

		if ( 'answer' === $_post->post_type ) {
			// Delete question subscriptions.
			ap_delete_subscribers( 'answer_' . $_post->post_parent );
		}
	}

	/**
	 * Delete comment subscriptions right before deleting comment.
	 *
	 * @param integer $comment_id Comment ID.
	 */
	public static function delete_comment_subscriptions( $comment_id ) {
		$_comment = get_comment( $comment_id );
		$_post = get_post( $_comment->comment_post_ID );

		if ( in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
			ap_delete_subscribers( 'comment_' . $_post->ID );
		}
	}
}

// Init addon.
AnsPress_Email_Hooks::init();
