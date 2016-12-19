<?php
/**
 * AnsPresss admin ajax class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 */
class AnsPress_Admin_Ajax {
	/**
	 * Initialize admin ajax
	 */
	public static function init() {
		anspress()->add_action( 'wp_ajax_ap_taxo_rename', __CLASS__, 'ap_taxo_rename' );
		anspress()->add_action( 'wp_ajax_ap_delete_flag', __CLASS__, 'ap_delete_flag' );
		anspress()->add_action( 'ap_ajax_ap_clear_flag', __CLASS__, 'clear_flag' );
		anspress()->add_action( 'ap_ajax_ap_admin_vote', __CLASS__, 'ap_admin_vote' );
		anspress()->add_action( 'ap_ajax_get_all_answers', __CLASS__, 'get_all_answers' );
	}

	/**
	 * Ajax cllback for updating old taxonomy question_tags to question_tag
	 */
	public static function ap_taxo_rename() {

		if ( current_user_can( 'manage_options' ) ) {
			global $wpdb;
			$wpdb->query( "UPDATE {$wpdb->prefix}term_taxonomy SET taxonomy = 'question_tag' WHERE  taxonomy = 'question_tags'" ); // db call okay, cache ok.

			ap_opt( 'tags_taxo_renamed', 'true' );
		}

		die();
	}

	/**
	 * Delete post flag
	 */
	public static function ap_delete_flag() {
		$post_id = (int) ap_sanitize_unslash( 'id', 'p' );

		if ( ap_verify_nonce( 'flag_delete' . $post_id ) && current_user_can( 'manage_options' ) ) {
			ap_set_flag_count( $post_id, 0 );
		}

		wp_die();
	}

	/**
	 * Clear post flags.
	 *
	 * @since 2.4.6
	 */
	public static function clear_flag() {
		$args = ap_sanitize_unslash( 'args', 'p' );

		if ( current_user_can( 'manage_options' ) && ap_verify_nonce( 'clear_flag_' . $args[0] ) ) {
			ap_set_flag_count( $args[0], 0 );
			echo 0;
		}

		wp_die();
	}

	/**
	 * Handle ajax vote in wp-admin post edit screen.
	 * Cast vote as anonymous use with ID 0, so that when this vote never get
	 * rest if user vote.
	 *
	 * @since 2.5
	 */
	public static function ap_admin_vote() {
		$args = ap_sanitize_unslash( 'args', 'p' );

		if ( current_user_can( 'manage_options' ) && ap_verify_nonce( 'admin_vote' ) ) {
			$post = ap_get_post( $args[0] );

			if ( $post ) {
				$value = 'up' === $args[1] ? '1'  : '-1';
				$counts = ap_add_post_vote( $post->ID, 0, 'vote', $value );
				echo esc_attr( $counts['votes_net'] );
			}
		}
		die();
	}

	/**
	 * Ajax callback to get all answers. Used in wp-admin post edit screen to show
	 * all answers of a question.
	 *
	 * @since 4.0
	 */
	public static function get_all_answers() {
		global $answers;

		$question_id = ap_sanitize_unslash( 'question_id', 'p' );
		$answers_arr = [];
		$answers = ap_get_answers( [ 'question_id' => $question_id ] );

		if ( ap_user_can_see_answers() ) :
			while ( ap_have_answers() ) : ap_the_answer();
				global $post, $wp_post_statuses;
				$answers_arr[] = array(
					'ID'        => get_the_ID(),
					'content'   => get_the_content(),
					'avatar'    => ap_get_author_avatar( 30 ),
					'author'    => ap_user_display_name( $post->post_author ),
					'activity'  => ap_get_recent_post_activity(),
					'editLink'  => esc_url_raw( get_edit_post_link() ),
					'trashLink' => esc_url_raw( get_delete_post_link() ),
					'status'    => esc_attr( $wp_post_statuses[ $post->post_status ]->label ),
					'selected'  => ap_get_post_field( 'selected' ),
				);
			endwhile;
		endif;

		wp_send_json( $answers_arr );

		wp_die();
	}

}
