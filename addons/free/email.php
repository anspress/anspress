<?php
/**
 * Notifiy admin and users by email.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @copyright 2014 AnsPress.io & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   WordPress/AnsPress/Email
 *
 * Addon Name:    Email
 * Addon URI:     https://anspress.io
 * Description:   Notifiy admin and users by email.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Prevent loading of previous email extension.
 *
 * @param boolean $ret Return.
 * @param string  $ext Extension slug.
 */
function ap_prevent_loading_email_ext( $ret, $ext ) {
	if ( 'anspress-email' === $ext ) {
		return false;
	}

	return $ret;
}
add_filter( 'anspress_load_ext', 'ap_prevent_loading_email_ext', 10, 2 );

/**
 * Email handler class.
 */
class AnsPress_Email {
	public $emails = [];
	public $args = [];
	public $subject = '';
	public $message = '';
	public $header = '';

	/**
	 * Init class.
	 *
	 * @param array $args Arguments.
	 */
	public function __construct( $args = [] ) {
		$this->header();
		$this->args = $args;
	}

	/**
	 * Set email header.
	 */
	public function header() {

		if ( ! $charset = get_bloginfo( 'charset' ) ) {
			$charset = 'utf-8';
		}

		$header = 'Content-type: text/plain; charset=' . $charset . "\r\n";
		$this->header = apply_filters( 'ap_email_header', $header );
	}

	/**
	 * Add email(s) to notify.
	 *
	 * @param array|string $email Pass one or multiple emails to notify.
	 */
	public function add_email( $email ) {
		if ( is_array( ) ) {
			foreach ( $email as $e ) {
				if ( is_email( $e ) && ! in_array( $e, $this->emails, true ) ) {
					$this->emails[] = sanitize_email( $e );
				}
			}
		} else {
			if ( is_email( $email ) && ! in_array( $email, $this->emails, true ) ) {
				$this->emails[] = sanitize_email( $email );
			}
		}
	}

	/**
	 * Set subject.
	 *
	 * @param string $template Subject template.
	 */
	public function set_subject( $template ) {
		$this->subject = $this->parse_template( $content );
	}

	/**
	 * Replace tags in template.
	 *
	 * @param string $content Template.
	 * @param array  $args Args.
	 */
	public function parse_template( $content, $args ) {
		return strtr( $content, $this->args );
	}

	/**
	 * Send mail.
	 */
	public function send() {

		// Do not send if subject and message are empty.
		if ( empty( $this->subject ) || empty( $this->message ) ) {
			return;
		}

		foreach ( (array) $this->emails as $email ) {
			if ( is_email( $email ) ) {
				wp_mail( $email, $this->subject, $this->message, $this->header );
			}
		}
	}

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
		anspress()->add_action( 'ap_option_groups', __CLASS__, 'register_option', 100 );
		anspress()->add_action( 'ap_after_new_question', __CLASS__, 'ap_after_new_question' );
		anspress()->add_action( 'ap_after_new_answer', __CLASS__, 'ap_after_new_answer' );
		anspress()->add_action( 'ap_select_answer', __CLASS__, 'select_answer' );
		anspress()->add_action( 'ap_publish_comment', __CLASS__, 'new_comment' );
		anspress()->add_action( 'ap_after_update_question', __CLASS__, 'ap_after_update_question', 10, 2 );
		anspress()->add_action( 'ap_after_update_answer', __CLASS__, 'ap_after_update_answer', 10, 2 );
		anspress()->add_action( 'ap_trash_question', __CLASS__, 'ap_trash_question' );
		anspress()->add_action( 'ap_trash_answer', __CLASS__, 'ap_trash_answer' );
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
		$defaults['new_question_email_body']    = __( "Hello!\r\nA new question is posted by {asker}\r\n\r\nTitle: {question_title}\r\nDescription:\r\n{question_excerpt}\r\n\r\nLink: {question_link}", 'anspress-question-answer' );

		$defaults['new_answer_email_subject'] = __( 'New answer posted by {answerer}', 'anspress-question-answer' );
		$defaults['new_answer_email_body']    = __( "Hello!\r\nA new answer is posted by {answerer} on {question_title}\r\nAnswer:\r\n{answer_excerpt}\r\n\r\nLink: {answer_link}", 'anspress-question-answer' );

		$defaults['select_answer_email_subject'] = __( 'Your answer is selected as best', 'anspress-question-answer' );
		$defaults['select_answer_email_body']    = __( "Hello!\r\nYour answer on '{question_title}' is selected as best.\r\n\r\nLink: {answer_link}", 'anspress-question-answer' );

		$defaults['new_comment_email_subject'] = __( 'New comment by {commenter}', 'anspress-question-answer' );
		$defaults['new_comment_email_body']    = __( "Hello!\r\nA new comment posted on '{question_title}' by {commenter}.\r\n\r\nLink: {comment_link}", 'anspress-question-answer' );

		$defaults['edit_question_email_subject'] = __( 'A question is edited by {editor}', 'anspress-question-answer' );
		$defaults['edit_question_email_body']    = __( "Hello!\r\nQuestion '{question_title}' is edited by {editor}.\r\n\r\nLink: {question_link}", 'anspress-question-answer' );

		$defaults['edit_answer_email_subject'] = __( 'An answer is edited by {editor}', 'anspress-question-answer' );
		$defaults['edit_answer_email_body']    = __( "Hello!\r\nAnswer on '{question_title}' is edited by {editor}.\r\n\r\nLink: {question_link}", 'anspress-question-answer' );

		$defaults['trash_question_email_subject'] = __( 'A question is trashed by {user}', 'anspress-question-answer' );
		$defaults['trash_question_email_body']    = __( "Hello!\r\nQuestion '{question_title}' is trashed by {user}.\r\n", 'anspress-question-answer' );

		$defaults['trash_answer_email_subject'] = __( 'An answer is trashed by {user}', 'anspress-question-answer' );
		$defaults['trash_answer_email_body']    = __( "Hello!\r\nAnswer on '{question_title}' is trashed by {user}.\r\n", 'anspress-question-answer' );

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
				'name' => 'notify_admin_email',
				'label' => __( 'Admin email', 'anspress-question-answer' ),
				'desc' => __( 'Enter email where admin notification should be sent', 'anspress-question-answer' ),
				'type' => 'text',
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

	public static function header() {
		$header = '';
		if ( ! $charset = get_bloginfo( 'charset' ) ) {
			$charset = 'utf-8';
		}
		$header .= 'Content-type: text/plain; charset=' . $charset . "\r\n";

		return $header;
	}

	public static function replace_tags($content, $args) {
		return strtr( $content, $args );
	}

	public static function send_mail($email, $subject, $message) {
		wp_mail( $email, $subject, $message, SELF::header() );
	}

	public static function initiate_send_email() {

		SELF::$emails = array_unique( SELF::$emails );

		if ( ! empty( SELF::$emails ) && is_array( SELF::$emails ) ) {
			foreach ( SELF::$emails as $email ) {
				SELF::send_mail( $email, SELF::$subject, SELF::$message );
			}
		}
	}

	/**
	 * Send email to admin when new question is created
	 * @param  integer $question_id
	 * @since 1.0
	 */
	public static function ap_after_new_question($question_id) {
		if ( ap_opt( 'notify_admin_new_question' ) ) {

			$current_user = wp_get_current_user();

			$question = get_post( $question_id );

			// don't bother if current user is admin
			if ( ap_opt( 'notify_admin_email' ) == $current_user->user_email ) {
				return;
			}

			$args = array(
				'{asker}'             => ap_user_display_name( $question->post_author ),
				'{question_title}'    => $question->post_title,
				'{question_link}'     => get_permalink( $question->ID ),
				'{question_content}'  => $question->post_content,
				'{question_excerpt}'  => ap_truncate_chars( strip_tags( $question->post_content ), 100 ),
			);

			$args = apply_filters( 'ap_new_question_email_tags', $args );

			SELF::$subject = SELF::replace_tags( ap_opt( 'new_question_email_subject' ), $args );

			SELF::$message = SELF::replace_tags( ap_opt( 'new_question_email_body' ), $args );

			SELF::$emails[] = ap_opt( 'notify_admin_email' );

			/*
			if ( ($answer->post_status != 'private_post' || $answer->post_status != 'moderate') ) {
                $users = ap_get_subscribers( $question_id, 'q_all', 100 );

                if ( $users ) {
                    foreach ( $users as $user ) {
                        // Dont send email to poster
                        if ( $user->user_email != $current_user->user_email ) {
                            SELF::$emails[] = $user->user_email; }
                    }
                }
			}*/
			SELF::initiate_send_email();
		}
	}

	public static function ap_after_new_answer( $answer_id ) {
			$current_user = wp_get_current_user();
			$answer = ap_get_post( $answer_id );

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

			SELF::$emails = array();

			if ( ap_opt( 'notify_admin_new_answer' ) && $current_user->user_email !== ap_opt( 'notify_admin_email' ) ) {
				SELF::$emails[] = ap_opt( 'notify_admin_email' );
			}

			if ( $answer->post_status !== 'private_post' && $answer->post_status !== 'moderate' ) {
				/*$subscribers = ap_get_subscribers( $answer->post_parent, 'q_all', 100, true );
				if ( $subscribers ) {
					foreach ( $subscribers as $s ) {
						if ( $s->user_email != $current_user->user_email ) {
							SELF::$emails[] = $s->user_email;
						}
					}
				}*/
			}

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
	 * Notify admin on new comment and is not approved
	 * @param  object $comment Comment id
	 */
	public static function new_comment($comment) {

		$current_user = wp_get_current_user();

		$post = get_post( $comment->comment_post_ID );

		$post_id = $post->ID;

		$args = array(
			'{commenter}'         => ap_user_display_name( $comment->user_id ),
			'{question_title}'    => $post->post_title,
			'{comment_link}'      => get_comment_link( $comment ),
			'{comment_content}'   => $comment->comment_content,
		);

		$args = apply_filters( 'ap_new_comment_email_tags', $args );

		SELF::$subject = SELF::replace_tags( ap_opt( 'new_comment_email_subject' ), $args );

		SELF::$message = SELF::replace_tags( ap_opt( 'new_comment_email_body' ), $args );

		SELF::$emails = array();

		$subscribe_type = $post->post_type == 'answer' ? 'a_all' : 'q_post';

		//$subscribers = ap_get_subscribers( $post_id, $subscribe_type, 100, true );
		$subscribers = [];

		$post_author  = get_user_by( 'id', $post->post_author );

		if ( ! ap_in_array_r( $post_author->data->user_email, $subscribers ) ) {
			$subscribers[] = (object) array( 'user_email' => $post_author->data->user_email, 'ID' => $post_author->ID, 'display_name' => $post_author->data->display_name );
		}

		if ( $subscribers ) {
			foreach ( $subscribers as $s ) {
				if ( $s->user_email != $current_user->user_email ) {
					SELF::$emails[] = $s->user_email;
				}
			}
		}

		SELF::initiate_send_email();
	}

	public static function ap_after_update_question( $question, $event ) {
		if ( 'edited' !== $event ) {
			return;
		}

		$question = ap_get_post( $question );
		$current_user = wp_get_current_user();
		SELF::$emails = array();

		if ( ap_opt( 'notify_admin_email' ) !== $current_user->user_email && ap_opt( 'notify_admin_edit_question' ) ) {
			SELF::$emails[] = ap_opt( 'notify_admin_email' );
		}

		//$subscribers = ap_get_subscribers( $question_id, array( 'q_post', 'q_all' ), 100, true );
		$subscribers = [];

		$post_author  = get_user_by( 'id', $question->post_author );

		if ( ! ap_in_array_r( $post_author->data->user_email, $subscribers ) ) {
			$subscribers[] = (object) array( 'user_email' => $post_author->data->user_email, 'ID' => $post_author->ID, 'display_name' => $post_author->data->display_name );
		}

		if ( $subscribers ) {
			foreach ( $subscribers as $s ) {
				if ( ! empty( $s->user_email ) && $s->user_email !== $current_user->user_email ) {
					SELF::$emails[] = $s->user_email;
				}
			}
		}

		if ( ! is_array( SELF::$emails ) || empty( SELF::$emails ) ) {
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

	public static function ap_after_update_answer( $answer, $event ) {
		if ( 'edited' !== $event ) {
			return;
		}

		if ( ! ap_opt( 'notify_admin_edit_answer' ) ) {
			return;
		}

		$answer = ap_get_post( $answer );
		$current_user = wp_get_current_user();
		SELF::$emails = array();

		if ( ap_opt( 'notify_admin_email' ) !== $current_user->user_email && ap_opt( 'notify_admin_edit_answer' ) ) {
			SELF::$emails[] = ap_opt( 'notify_admin_email' );
		}

		//$subscribers = ap_get_subscribers( $answer_id, 'a_all', 100, true );
		$subscribers = [];

		$post_author  = get_user_by( 'id', $answer->post_author );

		if ( ! ap_in_array_r( $post_author->data->user_email, $subscribers ) ) {
			$subscribers[] = (object) array( 'user_email' => $post_author->data->user_email, 'ID' => $post_author->ID, 'display_name' => $post_author->data->display_name );
		}

		if ( $subscribers ) {
			foreach ( $subscribers as $s ) {
				if ( ! empty($s->user_email ) && $s->user_email != $current_user->user_email ) {
					SELF::$emails[] = $s->user_email;
				}
			}
		}

		if ( ! is_array( SELF::$emails ) || empty( SELF::$emails ) ) {
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

	public static function ap_trash_question($post) {

		if ( ! ap_opt( 'notify_admin_trash_question' ) ) {
			return;
		}

		$current_user = wp_get_current_user();

		// don't bother if current user is admin
		if ( ap_opt( 'notify_admin_email' ) == $current_user->user_email ) {
			return; }

		$args = array(
			'{user}'              => ap_user_display_name( get_current_user_id() ),
			'{question_title}'    => $post->post_title,
			'{question_link}'     => get_permalink( $post->ID ),
		);

		$args = apply_filters( 'ap_trash_question_email_tags', $args );

		$subject = SELF::replace_tags( ap_opt( 'trash_question_email_subject' ), $args );

		$message = SELF::replace_tags( ap_opt( 'trash_question_email_body' ), $args );

		// sends email
		SELF::send_mail( ap_opt( 'notify_admin_email' ), $subject, $message );
	}

	public static function ap_trash_answer($post) {

		if ( ! ap_opt( 'notify_admin_trash_answer' ) ) {
			return; }

		$current_user = wp_get_current_user();

		// don't bother if current user is admin
		if ( ap_opt( 'notify_admin_email' ) == $current_user->user_email ) {
			return;
		}

		$args = array(
			'{user}'              => ap_user_display_name( get_current_user_id() ),
			'{question_title}'    => $post->post_title,
			'{question_link}'     => get_permalink( $post->post_parent ),
		);

		$args = apply_filters( 'ap_trash_answer_email_tags', $args );

		$subject = SELF::replace_tags( ap_opt( 'trash_answer_email_subject' ), $args );

		$message = SELF::replace_tags( ap_opt( 'trash_answer_email_body' ), $args );

		// Sends email.
		SELF::send_mail( ap_opt( 'notify_admin_email' ), $subject, $message );
	}
}

// Init addon.
AnsPress_Email_Hooks::init();
