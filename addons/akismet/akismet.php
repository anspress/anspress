<?php
/**
 * Akismet check.
 *
 * An AnsPress add-on to check for spam in posts before publishing.
 *
 * @author     Rahul Aryan <rah12@live.com>
 * @copyright  2014 anspress.net & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.net
 * @package    AnsPress
 * @subpackage Akismet check Addon
 * @since      4.1.11
 *
 * @anspress-addon
 * Addon Name:    Akismet check
 * Addon URI:     https://anspress.net
 * Description:   Checks for spam in posts.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.net
 */

namespace Anspress\Addons;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Akismet addon class.
 *
 * @since 4.1.11
 */
class Akismet extends \AnsPress\Singleton {

	/**
	 * Refers to a single instance of this class.
	 *
	 * @var null|object
	 * @since 4.1.8
	 */
	public static $instance = null;

	/**
	 * Initialize the class.
	 */
	protected function __construct() {
		// Return if akisment is not enabled.
		if ( ! class_exists( 'Akismet' ) ) {
			return;
		}

		ap_add_default_options( array(
			'spam_post_action' => 'moderate',
		));

		anspress()->add_action( 'ap_form_addon-akismet', $this, 'option_form' );
		anspress()->add_action( 'ap_after_question_form_processed', $this, 'new_question_answer' );
		anspress()->add_action( 'ap_after_answer_form_processed', $this, 'new_question_answer' );
		anspress()->add_action( 'admin_action_ap_mark_spam', $this, 'submit_spam' );
		anspress()->add_action( 'post_row_actions', $this, 'row_actions', 10, 2 );
	}

	/**
	 * Register options of Avatar addon.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function option_form() {
		$opt = ap_opt();

		$form = array(
			'submit_label' => __( 'Save add-on options', 'anspress-question-answer' ),
			'fields'       => array(
				'spam_post_action'        => array(
					'label'   => __( 'What to do when post is a spam?', 'anspress-question-answer' ),
					'type'    => 'select',
					'options' => array(
						'moderate' => __( 'Change status to moderate', 'anspress-question-answer' ),
						'trash'    => __( 'Trash the post', 'anspress-question-answer' ),
					),
					'value'   => $opt['spam_post_action'],
				),
			),
		);

		return $form;
	}

	/**
	 * Check post for spam, if spam then hold it for moderation.
	 *
	 * @param  integer $post_id Post id.
	 */
	private function api_request( $post_id, $submit = false ) {
		$post = ap_get_post( $post_id );
		$comment_type = 'question' === $post->post_type ? 'forum-post' : 'replay';

		// Set default arguments to pass.
		$defaults = array(
			'blog'                 => home_url( '/' ),
			'user_ip'              => get_post_meta( $post->ID, 'create_ip', true ),
			'user_agent'           => $_SERVER['HTTP_USER_AGENT'],
			'referrer'             => $_SERVER['HTTP_REFERER'],
			'permalink'            => get_permalink( $post->ID ),
			'comment_type'         => $comment_type,
			'comment_author'       => get_the_author_meta( 'nicename', $post->post_author ),
			'comment_author_email' => get_the_author_meta( 'user_email', $post->post_author ),
			'comment_author_url'   => get_the_author_meta( 'url', $post->post_author ),
			'comment_content'      => $post->post_title . "\n\n" . $post->post_content,
		);

		$akismet_ua = sprintf( 'WordPress/%s | AnsPress/%s', $GLOBALS['wp_version'], AP_VERSION );
		$akismet_ua = apply_filters( 'akismet_ua', $akismet_ua );
		$api_key    = \Akismet::get_api_key();
		$host       = \Akismet::API_HOST;

		if ( ! empty( $api_key ) ) {
			$host = $api_key . '.' . $host;
		}

		$http_host = $host;
		$http_args = array(
			'body'        => $defaults,
			'headers'     => array(
				'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option( 'blog_charset' ),
				'Host'         => $host,
				'User-Agent'   => $akismet_ua,
			),
			'httpversion' => '1.0',
			'timeout'     => 15,
		);

		$akismet_url = $http_akismet_url = "http://{$http_host}/1.1/";

		if ( false === $submit ) {
			$akismet_url .= 'comment-check';
		} else {
			$akismet_url .= 'submit-spam';
		}

		$akismet_url = set_url_scheme( $akismet_url, 'https' );
		$response    = wp_remote_post( $akismet_url, $http_args );

		\Akismet::log( compact( 'akismet_url', 'http_args', 'response' ) );

		if ( is_wp_error( $response ) ) {
			// Intermittent connection problems may cause the first HTTPS
			// request to fail and subsequent HTTP requests to succeed randomly.
			// Retry the HTTPS request once before disabling SSL for a time.
			$response = wp_remote_post( $akismet_url, $http_args );

			\Akismet::log( compact( 'akismet_url', 'http_args', 'response' ) );

			if ( is_wp_error( $response ) ) {
				// Try the request again without SSL.
				$response = wp_remote_post( $http_akismet_url, $http_args );
				\Akismet::log( compact( 'http_akismet_url', 'http_args', 'response' ) );
			}
		}

		if ( is_wp_error( $response ) ) {
			return array( '', '' );
		}

		// Lastly if true mark it as spam.
		if ( 'true' === $response['body'] || 'Thanks for making the web a better place.' === $response['body'] ) {
			$this->spam_post_action( $post_id );
			update_post_meta( $post_id, '__ap_spam', current_time( 'timestamp' ) );
		}

	}

	/**
	 * Action to do when post is marked as a spam.
	 *
	 * @param integer $post_id Post id.
	 * @return void
	 */
	public function spam_post_action( $post_id ) {
		$opt = ap_opt( 'spam_post_action' );

		wp_update_post( array(
			'ID'          => $post_id,
			'post_status' => $opt,
		) );
	}

	/**
	 * Check for spam in content after submission from frontend.
	 *
	 * @param integer $post_id Post id.
	 * @return void
	 */
	public function new_question_answer( $post_id ) {
		$_post = ap_get_post( $post_id );

		// Return if already a spam or user is admin.
		if ( 'moderate' === $_post->post_status || user_can( $_post->post_author, 'manage_options' ) ) {
			return;
		}

		$this->api_request( $post_id );
	}

	/**
	 * Submit spam to akismet.
	 *
	 * @return void
	 */
	public function submit_spam() {

		// Check role.
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( ap_sanitize_unslash( 'nonce', 'r' ), 'send_spam' ) ) {
			die( esc_attr__( 'Cheating', 'anspress-question-answer' ) );
		}

		$post_id = ap_sanitize_unslash( 'post_id', 'r' );
		$this->api_request( $post_id, true );

		// Redirect.
		wp_redirect( admin_url( 'edit.php?post_type=question' ) );
		die();
	}

	/**
	 * Add post row action to mark a post as a spam.
	 *
	 * @param array    $actions List of actions.
	 * @param \WP_Post $post    Post object.
	 * @return void
	 */
	public function row_actions( $actions, $post ) {
		if ( ! ap_is_cpt( $post ) || 'moderate' === $post->post_status ) {
			return $actions;
		}

		$nonce = wp_create_nonce( 'send_spam' );

		$actions['report_spam'] = '<a href="' . admin_url( 'admin.php?action=ap_mark_spam&post_id=' . $post->ID . '&nonce=' . $nonce ) . '" aria-label="' . __( 'Mark this post as a spam', 'anspress-question-answer' ) . '">' . __( 'Mark as spam', 'anspress-question-answer' ) . '</a>';


		return $actions;
	}
}

// Init class.
Akismet::init();
