<?php
/**
 * AnsPress email class.
 *
 * @package    AnsPress
 * @subpackage Email
 * @since      4.1.0
 * @author     Rahul Aryan<support@anspress.net>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @license    http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

namespace AnsPress\Addons\Email;

/**
 * The email helper class.
 *
 * @since 4.1.0
 * @since 4.1.8 Renamed class From Email to helper.
 */
class Helper {
	/**
	 * The arguments.
	 *
	 * @var array
	 * @since 4.1.0
	 */
	public $args = [];

	/**
	 * List of emails where to send notifications.
	 *
	 * @var array
	 */
	private $emails = [];

	/**
	 * The email subject.
	 *
	 * @var string
	 * @since 4.1.0
	 */
	public $subject = '';

	/**
	 * The email body.
	 *
	 * @var string
	 * @since 4.1.0
	 */
	public $body = '';

	/**
	 * The email event.
	 *
	 * @var string
	 * @since 4.1.0
	 */
	public $event = '';

	/**
	 * The email template.
	 *
	 * @var string
	 * @since 4.1.0
	 */
	public $template = '';

	/**
	 * Email template tags.
	 *
	 * @var array
	 * @since 4.1.0
	 */
	public $template_tags = [];

	/**
	 * The email header.
	 *
	 * @var array
	 */
	public $email_headers = [];

	/**
	 * Initialize email class.
	 *
	 * @param string $event Event name.
	 * @param array  $args Arguments.
	 */
	public function __construct( $event, $args = [] ) {
		$this->event = $event;

		$this->args = wp_parse_args(
			$args, array(
				'users'    => [],
				'subject'  => '',
				'tags'     => array(
					'site_name'        => get_bloginfo( 'name' ),
					'site_url'         => get_bloginfo( 'url' ),
					'site_description' => get_bloginfo( 'description' ),
				),
				'template' => [],
				'headers'  => array(
					'Content-Type: text/html; charset=utf-8',
				),
			)
		);

		$this->email_headers = $this->args['headers'];
		unset( $this->args['headers'] );

		// Add template tags.
		if ( ! empty( $this->args['tags'] ) ) {
			foreach ( $this->args['tags'] as $tag => $content ) {
				$this->add_template_tag( $tag, $content );
			}
		}
	}

	/**
	 * Add an email to the currently sending list.
	 *
	 * @param string $email Email.
	 * @return void
	 */
	public function add_email( $email ) {
		$email = trim( $email );
		if ( ! in_array( $email, $this->emails, true ) ) {
			/**
			 * Hook triggered before an email added to current sending list.
			 *
			 * @since 4.1.0
			 */
			do_action_ref_array( 'ap_before_email_to_list', [ $this ] );

			$this->emails[] = $email;
		}
	}

	/**
	 * Add user or email to current sending list.
	 *
	 * @param array $user_id User id or email.
	 * @return void
	 * @since 4.1.0
	 */
	public function add_user( $user_id ) {
		if ( ! in_array( $user_id, $this->args['users'] ) ) {
			$this->args['users'][] = $user_id;
		}
	}

	/**
	 * Add template tags.
	 *
	 * @param string $tag Template tag key.
	 * @param string $content Tag content.
	 * @return void
	 * @since 4.1.0
	 */
	public function add_template_tag( $tag, $content ) {
		$tag = '{' . sanitize_key( $tag ) . '}';

		if ( ! isset( $this->template_tags[ $tag ] ) ) {
			$this->template_tags[ $tag ] = $content;

			/**
			 * Action triggered after adding email template tag.
			 *
			 * @since 4.1.0
			 */
			do_action_ref_array( 'ap_adding_email_tag', [ $this ] );
		}
	}

	/**
	 * Add multiple template tags.
	 *
	 * @param array $tags Template tags.
	 * @return void
	 * @since 4.1.0
	 */
	public function add_template_tags( $tags ) {
		foreach ( $tags as $tag => $content ) {
			$this->add_template_tag( $tag, $content );
		}
	}

	/**
	 * Get default template for an email.
	 *
	 * @return string
	 * @since 4.1.0
	 */
	public function get_default_template() {
		/**
		 * Filter for adding default email template.
		 *
		 * @since 4.1.0
		 */
		return apply_filters( "ap_email_default_template_{$this->event}", '' );
	}

	/**
	 * Prepare template for email body based on event type.
	 *
	 * @return string
	 * @since 4.1.0
	 */
	public function prepare_template() {
		$page_id = ap_opt( 'email_template_' . $this->event );
		$_post   = get_post( $page_id );

		if ( $_post ) {
			$this->template = apply_filters( 'the_content', $_post->post_content );
			$this->subject  = $_post->post_title;
		}

		// If template not found use default one.
		if ( empty( $this->template ) ) {
			$default_template = \AnsPress\Addons\Email::init()->get_default_template( $this->event );
			$this->template   = $default_template['body'];
			$this->subject    = $default_template['subject'];
		}

		$this->body    = strtr( $this->template, $this->template_tags );
		$this->subject = strtr( $this->subject, $this->template_tags );

		$main_tags = array(
			'email_title' => $this->subject,
			'email_body'  => $this->body,
			'style'       => file_get_contents( ap_get_theme_location( 'addons/email/style.css' ) ),
			'site_name'   => get_bloginfo( 'name' ),
		);

		/**
		 * This filter allows overriding `$main_tags`. Which is used in main
		 * email template.
		 *
		 * @param string $main_template Parsed template.
		 * @param object $email         Current email object.
		 * @since 4.1.0
		 * @return string
		 */
		$main_tags = apply_filters_ref_array( 'ap_email_main_tags', [ $main_tags, $this ] );

		foreach ( $main_tags as $key => $content ) {
			$this->add_template_tag( $key, $content );
		}

		$main_template = file_get_contents( ap_get_theme_location( 'addons/email/template.html' ) );
		$main_template = strtr( $main_template, $this->template_tags );

		/**
		 * Allows filtering email template after its prepared, passed be reference.
		 *
		 * @param string $main_template Parsed template.
		 * @param object $email         Current email object.
		 * @since 4.1.0
		 * @return string
		 */
		$main_template = apply_filters_ref_array( 'ap_email_template_prepared', [ $main_template, $this ] );

		return $main_template;
	}

	/**
	 * Get email ids of user. If email passed then add it to email
	 * property.
	 *
	 * @return void
	 * @since 4.1.0
	 */
	public function prepare_emails() {
		global $wpdb;

		if ( empty( $this->args['users'] ) ) {
			return;
		}

		$user_ids = [];

		foreach ( $this->args['users'] as $k => $id ) {
			if ( is_email( $id ) ) {
				$this->add_email( $id );
			} elseif ( is_numeric( $id ) ) {
				$user_ids[] = (int) $id;
			}
		}

		$ids_str = esc_sql( sanitize_comma_delimited( $user_ids ) );

		if ( empty( $ids_str ) ) {
			return;
		}

		$emails = $wpdb->get_col( "SELECT user_email FROM {$wpdb->users} WHERE ID IN ({$ids_str})" );

		foreach ( $emails as $email ) {
			$this->add_email( $email );
		}
	}

	/**
	 * Send emails.
	 *
	 * @return boolean|\WP_Error
	 */
	public function send_emails() {
		if ( defined( 'AP_DISABLE_EMAIL' ) && true === AP_DISABLE_EMAIL ) {
			return;
		}

		$body = $this->prepare_template();
		$this->prepare_emails();

		if ( empty( $this->emails ) ) {
			return new \WP_Error( 'no_emails' );
		}

		if ( ! empty( $this->emails ) ) {
			foreach ( $this->emails as $email ) {
				wp_mail( $email, $this->subject, $body, $this->email_headers );
			}
		}
	}
}
