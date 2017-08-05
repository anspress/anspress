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
		anspress()->add_action( 'wp_ajax_ap_uninstall_data', __CLASS__, 'ap_uninstall_data' );
		anspress()->add_action( 'wp_ajax_ap_toggle_addons', __CLASS__, 'ap_toggle_addons' );
		anspress()->add_action( 'wp_ajax_ap_migrator_4x', __CLASS__, 'ap_migrator_4x' );
		anspress()->add_action( 'wp_ajax_anspress_recount', __CLASS__, 'anspress_recount' );
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
		$post_id = ap_sanitize_unslash( 'post_id', 'p' );

		if ( current_user_can( 'manage_options' ) && ap_verify_nonce( 'clear_flag_' . $post_id ) ) {
			ap_delete_flags( $post_id, 'flag' );
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

		while ( ap_have_answers() ) : ap_the_answer();
			global $post, $wp_post_statuses;
			if ( ap_user_can_view_post() ) :
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
			endif;
		endwhile;

		wp_send_json( $answers_arr );

		wp_die();
	}

	/**
	 * Uninstall actions.
	 *
	 * @since 4.0.0
	 */
	public static function ap_uninstall_data() {
		check_ajax_referer( 'ap_uninstall_data', '__nonce' );

		$data_type = ap_sanitize_unslash( 'data_type', 'r' );
		$valid_data = [ 'qa', 'answers', 'options', 'userdata', 'terms', 'tables' ];

		global $wpdb;

		// Only allow super admin to delete data.
		if ( is_super_admin( ) && in_array( $data_type, $valid_data, true ) ) {
			$done = 0;

			if ( 'qa' === $data_type ) {

				$count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type='question' OR post_type='answer'" );
				$ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type='question' OR post_type='answer' LIMIT 30" );

				foreach ( (array) $ids as $id ) {
					if ( false !== wp_delete_post( $id, true ) ) {
						$done++;
					}
				}

				wp_send_json( [ 'done' => (int) $done, 'total' => (int) $count ] );
			} elseif ( 'answers' === $data_type ) {

				$count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type='answer'" );
				$ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type='answer' LIMIT 30" );

				foreach ( (array) $ids as $id ) {
					if ( false !== wp_delete_post( $id, true ) ) {
						$done++;
					}
				}

				wp_send_json( [ 'done' => (int) $done, 'total' => (int) $count ] );
			} elseif ( 'userdata' === $data_type ) {

				$upload_dir = wp_upload_dir();

				// Delete avatar folder.
				wp_delete_file( $upload_dir['baseurl'] . '/ap_avatars' );

				// Remove user roles.
				AP_Roles::remove_roles();

				// Delete vote meta.
				$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => '__up_vote_casted' ], array( '%s' ) ); // @codingStandardsIgnoreLine
				$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => '__down_vote_casted' ], array( '%s' ) ); // @codingStandardsIgnoreLine

				wp_send_json( [ 'done' => 1, 'total' => 0 ] );
			} elseif ( 'options' === $data_type ) {

				delete_option( 'anspress_opt' );
				delete_option( 'anspress_reputation_events' );
				delete_option( 'anspress_addons' );

				wp_send_json( [ 'done' => 1, 'total' => 0 ] );
			} elseif ( 'terms' === $data_type ) {

				$question_taxo = (array) get_object_taxonomies( 'question', 'names' );
				$answer_taxo = (array) get_object_taxonomies( 'answer', 'names' );

				$taxos = $question_taxo + $answer_taxo;

				foreach ( (array) $taxos as $tax ) {
					$terms = get_terms( array(
						'taxonomy' 		=> $tax,
						'hide_empty' 	=> false,
						'fields' 			=> 'ids',
					) );

					foreach ( (array) $terms as $t ) {
						wp_delete_term( $t, $tax );
					}
				}

				wp_send_json( [ 'done' => 1, 'total' => 0 ] );
			} elseif ( 'tables' === $data_type ) {

				$tables = [ $wpdb->ap_qameta, $wpdb->ap_votes, $wpdb->ap_views, $wpdb->ap_reputations, $wpdb->ap_subscribers, $wpdb->prefix . 'ap_notifications' ];

				foreach ( $tables as $table ) {
					$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
				}

				wp_send_json( [ 'done' => 1, 'total' => 0 ] );
			}
		}

		// Send empty JSON if nothing done.
		wp_send_json( [] );
	}

	/**
	 * Toggle addons.
	 */
	public static function ap_toggle_addons() {
		check_ajax_referer( 'ap-toggle-addons', '__nonce' );

		if ( ! is_super_admin( ) ) {
			wp_die( '' );
		}

		$_REQUEST['option_page'] = 'addons';
		$previous_addons = get_option( 'anspress_addons', [] );
		$new_addons = array_flip( ap_isset_post_value( 'addon', [] ) );

		if ( empty( $new_addons ) ) {
			update_option( 'anspress_addons', [] );
		}

		$addons = $previous_addons + $new_addons;

		foreach ( (array) $addons as $file => $status ) {
			if ( ! isset( $new_addons[ $file ] ) ) {
				ap_deactivate_addon( $file );
			} else {
				ap_activate_addon( $file );
			}
		}

		wp_die( );
	}


	public static function ap_migrator_4x() {
		check_ajax_referer( 'ap_migration', '__nonce' );

		if ( is_super_admin() ) {
			require_once( ANSPRESS_DIR . 'admin/update.php' );
			new AP_Update_Helper();
		}

		wp_die();
	}

	/**
	 * Ajax callback for performing recount actions.
	 *
	 * @since 4.0.5
	 * @return void
	 */
	public static function anspress_recount() {
		check_ajax_referer( 'recount', '__nonce' );

		// Check if user have permission to do this action.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json( [
				'error' => true,
			] );
		}

		$sub_action = ap_sanitize_unslash( 'sub_action', 'r', '' );
		$current = (int) ap_sanitize_unslash( 'current', 'r', 0 );
		$offset = 50 * $current;

		$method_name = 'recount_' . $sub_action;

		if ( ! empty( $sub_action ) && method_exists( __CLASS__, $method_name ) ) {
			self::$method_name( $current, $offset );
		}

		wp_send_json( [
			'error' => true,
		] );
	}

	/**
	 * Recount all votes
	 *
	 * @param integer $current Current index.
	 * @param integer $offset Current offset.
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_votes( $current, $offset ) {
		global $wpdb;

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type IN ('question', 'answer') LIMIT {$offset},50" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			ap_update_votes_count( $id );
		}

		$action = 'continue';

		if ( count( $ids ) < 50 ) {
			$action = 'success';
		}

		wp_send_json( [
			'action'    => $action,
			'total'     => $total_found,
			'processed' => count( $ids ),
		] );
	}

	/**
	 * Recount answers of questions.
	 *
	 * @param integer $current Current index.
	 * @param integer $offset Current offset.
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_answers( $current, $offset ) {
		global $wpdb;

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type = 'question' LIMIT {$offset},50" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			ap_update_answers_count( $id, false, false );
		}

		$action = 'continue';

		if ( count( $ids ) < 50 ) {
			$action = 'success';
		}

		wp_send_json( [
			'action'    => $action,
			'total'     => $total_found,
			'processed' => count( $ids ),
		] );
	}

	/**
	 * Recount flags of posts.
	 *
	 * @param integer $current Current index.
	 * @param integer $offset Current offset.
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_flags( $current, $offset ) {
		global $wpdb;

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type IN ('question', 'answer') LIMIT {$offset},50" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			ap_update_flags_count( $id );
		}

		$action = 'continue';

		if ( count( $ids ) < 50 ) {
			$action = 'success';
		}

		wp_send_json( [
			'action'    => $action,
			'total'     => $total_found,
			'processed' => count( $ids ),
		] );
	}

	/**
	 * Recount question subscribers.
	 *
	 * @param integer $current Current index.
	 * @param integer $offset Current offset.
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_subscribers( $current, $offset ) {
		global $wpdb;

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type = 'question' LIMIT {$offset},50" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			ap_update_subscribers_count( $id );
		}

		$action = 'continue';

		if ( count( $ids ) < 50 ) {
			$action = 'success';
		}

		wp_send_json( [
			'action'    => $action,
			'total'     => $total_found,
			'processed' => count( $ids ),
		] );
	}

	/**
	 * Recount users reputation.
	 *
	 * @param integer $current Current index.
	 * @param integer $offset Current offset.
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_reputation( $current, $offset ) {
		global $wpdb;

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->users} LIMIT {$offset},50" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			ap_update_user_reputation_meta( $id );
		}

		$action = 'continue';

		if ( count( $ids ) < 50 ) {
			$action = 'success';
		}

		wp_send_json( [
			'action'    => $action,
			'total'     => $total_found,
			'processed' => $offset + count( $ids ),
		] );
	}

	/**
	 * Recount reputation for answering a question.
	 *
	 * @param  integer $user_id User id.
	 * @param  string  $event   Event type.
	 * @return boolean Returns true on success.
	 */
	public static function recount_answer_reputation( $user_id, $event ) {
		global $wpdb;

		$post_ids = $wpdb->get_col(
			$wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_author = %d AND post_type = 'answer' AND post_status IN ('publish', 'private_post')", $user_id )
		);

		// Return if no answers found.
		if ( empty( $post_ids ) ) {
			return true;
		}

		$events = ap_search_array( ap_get_reputation_events(), 'parent', 'answer' );
		PC::debug( $events );

		foreach ( (array) $post_ids as $id ) {
				$exists = ap_get_reputation( $e, $id, $user_id );

				if ( ! $exists ) {

				}
		}
	}

	/**
	 * Recount question views.
	 *
	 * @param integer $current Current index.
	 * @param integer $offset Current offset.
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_views( $current, $offset ) {
		global $wpdb;
		$args = wp_parse_args( ap_isset_post_value( 'args' ), array(
			'fake_views' => false,
			'min_views'  => 100,
			'max_views'  => 200,
		));

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type = 'question' LIMIT {$offset},50" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			$table_views = (int) ap_get_views( $id );
			$qameta_views = (int) ap_get_post_field( 'views', $id );

			if ( $qameta_views < $table_views ) {
				$views = $table_views + $qameta_views;
			} else {
				$views = $qameta_views;
			}

			if ( $args['fake_views'] ) {
				$views = $views + ap_rand( $args['min_views'], $args['max_views'], 0.5 );
			}

			ap_update_views_count( $id, $views );
		}

		$action = 'continue';

		if ( count( $ids ) < 50 ) {
			$action = 'success';
		}

		wp_send_json( [
			'action'    => $action,
			'total'     => $total_found,
			'processed' => count( $ids ),
		] );
	}

}
