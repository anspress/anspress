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
class AnsPress_Admin_Ajax
{
	/**
	 * Initialize admin ajax
	 */
	public function __construct() {
		anspress()->add_action( 'wp_ajax_ap_edit_reputation', $this, 'ap_edit_reputation' );
		anspress()->add_action( 'wp_ajax_ap_save_reputation', $this, 'ap_save_reputation' );
		anspress()->add_action( 'wp_ajax_ap_new_reputation_form', $this, 'ap_new_reputation_form' );
		anspress()->add_action( 'wp_ajax_ap_delete_reputation', $this, 'ap_delete_reputation' );
		anspress()->add_action( 'wp_ajax_ap_taxo_rename', $this, 'ap_taxo_rename' );
		anspress()->add_action( 'wp_ajax_ap_delete_flag', $this, 'ap_delete_flag' );
		anspress()->add_action( 'ap_ajax_ap_clear_flag', $this, 'clear_flag' );
		anspress()->add_action( 'ap_ajax_ap_admin_vote', $this, 'ap_admin_vote' );
	}

	/**
	 * Ajax callback for reputation edit form
	 */
	public function ap_edit_reputation() {
		if ( current_user_can( 'manage_options' ) ) {
			$id = ap_sanitize_unslash( 'id', 'request' );
			$reputation = ap_reputation_by_id( $id );

			$html = '
                <div id="ap-reputation-edit">
                    <form method="POST" data-action="ap-save-reputation">
                        <table class="form-table">
                            <tr valign="top">
								<th scope="row"><label for="title">'. __( 'Title', 'anspress-question-answer' ).'</label></th>
                                <td>
									<input id="title" type="text" name="title" value="'.$reputation['title'].'" />
                                </td>
                            </tr>
                            <tr valign="top">
								<th scope="row"><label for="description">'. __( 'Description', 'anspress-question-answer' ).'</label></th>
                                <td>
									<textarea cols="50" id="description" name="description">'.$reputation['description'].'</textarea>
                                </td>
                            </tr>
                            <tr valign="top">
								<th scope="row"><label for="reputation">'. __( 'Points', 'anspress-question-answer' ).'</label></th>
                                <td>
									<input id="reputation" type="text" name="reputation" value="'.$reputation['reputation'].'" />
                                </td>
                            </tr>
                            <tr valign="top">
								<th scope="row"><label for="event">'. __( 'Event', 'anspress-question-answer' ).'</label></th>
                                <td>
									<input type="text" name="event" value="'.$reputation['event'].'" />
                                </td>
                            </tr>
                        </table>
						<input class="button-primary" type="submit" value="'.__( 'Save Point', 'anspress-question-answer' ).'">
						<input type="hidden" name="id" value="'.$reputation['id'].'">
                        <input type="hidden" name="action" value="ap_save_reputation">
						<input type="hidden" name="nonce" value="'.wp_create_nonce( 'ap_save_reputation' ).'">
                    </form>
                </div>
			';

			$result = array( 'status' => true, 'html' => $html );
			$result = apply_filters( 'ap_edit_reputation_result', $result );
			echo json_encode( $result );
		}
		die();
	}

	/**
	 * Ajax callback for updating reputation
	 */
	public function ap_save_reputation() {

		if ( current_user_can( 'manage_options' ) ) {
			$nonce  = sanitize_text_field( $_POST['nonce'] );
			$title  = sanitize_text_field( $_POST['title'] );
			$desc   = sanitize_text_field( $_POST['description'] );
			$reputation = sanitize_text_field( $_POST['reputation'] );
			$event  = sanitize_text_field( $_POST['event'] );
			if ( wp_verify_nonce( $nonce, 'ap_save_reputation' ) ) {
				if ( isset( $_POST['id'] ) ) {
					$id     = sanitize_text_field( $_POST['id'] );
					ap_reputation_option_update( $id, $title, $desc, $reputation, $event );
				} else {
					ap_reputation_option_new( $title, $desc, $reputation, $event );
				}

				ob_start();
				AnsPress_Admin::display_reputation_page();
				$html = ob_get_clean();

				$result = array(
					'status' => true,
				'html' => $html,
				);

				echo json_encode( $result );
			}
		}

		die();
	}

	/**
	 * Ajax callback for new reputation form
	 */
	public function ap_new_reputation_form() {

		if ( current_user_can( 'manage_options' ) ) {
			$html = '
                <div id="ap-reputation-edit">
                    <form method="POST" data-action="ap-save-reputation">
                        <table class="form-table">
                            <tr valign="top">
								<th scope="row"><label for="title">'. __( 'Title', 'anspress-question-answer' ).'</label></th>
                                <td>
                                    <input id="title" type="text" name="title" value="" />
                                </td>
                            </tr>
                            <tr valign="top">
								<th scope="row"><label for="description">'. __( 'Description', 'anspress-question-answer' ).'</label></th>
                                <td>
                                    <textarea cols="50" id="description" name="description"></textarea>
                                </td>
                            </tr>
                            <tr valign="top">
								<th scope="row"><label for="reputation">'. __( 'Points', 'anspress-question-answer' ).'</label></th>
                                <td>
                                    <input id="reputation" type="text" name="reputation" value="" />
                                </td>
                            </tr>
                            <tr valign="top">
								<th scope="row"><label for="event">'. __( 'Event', 'anspress-question-answer' ).'</label></th>
                                <td>
                                    <input type="text" name="event" value="" />
                                </td>
                            </tr>
                        </table>
						<input class="button-primary" type="submit" value="'.__( 'Save Point', 'anspress-question-answer' ).'">
                        <input type="hidden" name="action" value="ap_save_reputation">
						<input type="hidden" name="nonce" value="'.wp_create_nonce( 'ap_save_reputation' ).'">
                    </form>
                </div>
			';

			$result = array( 'status' => true, 'html' => $html );
			$result = apply_filters( 'ap_new_reputation_form_result', $result );
			echo json_encode( $result );
		}
		die();
	}

	/**
	 * Ajax callback for delting reputation
	 */
	public function ap_delete_reputation() {

		if ( current_user_can( 'manage_options' ) ) {
			$args = explode( '-', sanitize_text_field( $_POST['args'] ) );
			if ( wp_verify_nonce( $args[1], 'delete_reputation' ) ) {
				ap_reputation_option_delete( $args[0] );
				$result = array( 'status' => true );
				$result = apply_filters( 'ap_delete_reputation_form_result', $result );
				echo json_encode( $result );
			}
		}

		die();
	}

	/**
	 * Ajax cllback for updating old taxonomy question_tags to question_tag
	 */
	public function ap_taxo_rename() {

		if ( current_user_can( 'manage_options' ) ) {
			global $wpdb;

			$wpdb->query( 'UPDATE '.$wpdb->prefix."term_taxonomy SET taxonomy = 'question_tag' WHERE  taxonomy = 'question_tags'" );

			ap_opt( 'tags_taxo_renamed', 'true' );
		}

		die();
	}

	/**
	 * Delete post flag
	 */
	public function ap_delete_flag() {

		$id = (int) sanitize_text_field( $_POST['id'] );
		if ( wp_verify_nonce( $_POST['__nonce'], 'flag_delete'.$id ) && current_user_can( 'manage_options' ) ) {
			return ap_delete_meta( false, $id );
		}
		die();
	}

	/**
	 * Clear post flags.
	 * @since 2.4.6
	 */
	public function clear_flag() {
		$args = $_POST['args'];
		if ( current_user_can( 'manage_options' ) && wp_verify_nonce( $_POST['__nonce'], 'clear_flag_'. $args[0] ) ) {
			ap_delete_all_post_flags( $args[0] );
			delete_post_meta( $args[0], ANSPRESS_FLAG_META );
			die( _e('0' ) );
		}
		die();
	}

	/**
	 * Handle ajax vote in wp-admin post edit screen.
	 * Cast vote as anonymous use with ID 0, so that when this vote never get
	 * rest if user vote.
	 * @since 2.5
	 */
	public function ap_admin_vote() {
		$args = $_POST['args'];

		if ( current_user_can( 'manage_options' ) && wp_verify_nonce( $_POST['__nonce'], 'admin_vote' ) ) {
			$post = get_post( $args[0] );

			if ( $post ) {
				$count = ( $args[1] == 'up' ? (1)  : ( -1 ) );
				$count = ap_add_post_vote( 0, 'vote_up', $post->ID, 0, $count );
				echo $count['net_vote'];
			}
		}
		die();
	}

}
