<?php
/**
 * AnsPresss admin ajax class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      https://anspress.net
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
 */
class AnsPress_Admin_Ajax {
	/**
	 * Initialize admin ajax
	 */
	public static function init() {
		anspress()->add_action( 'wp_ajax_ap_delete_flag', __CLASS__, 'ap_delete_flag' );
		anspress()->add_action( 'ap_ajax_ap_clear_flag', __CLASS__, 'clear_flag' );
		anspress()->add_action( 'ap_ajax_ap_admin_vote', __CLASS__, 'ap_admin_vote' );
		anspress()->add_action( 'ap_ajax_get_all_answers', __CLASS__, 'get_all_answers' );
		anspress()->add_action( 'wp_ajax_ap_uninstall_data', __CLASS__, 'ap_uninstall_data' );
		anspress()->add_action( 'wp_ajax_ap_toggle_addon', __CLASS__, 'ap_toggle_addon' );
		anspress()->add_action( 'wp_ajax_ap_recount_votes', __CLASS__, 'recount_votes' );
		anspress()->add_action( 'wp_ajax_ap_recount_answers', __CLASS__, 'recount_answers' );
		anspress()->add_action( 'wp_ajax_ap_recount_flagged', __CLASS__, 'recount_flagged' );
		anspress()->add_action( 'wp_ajax_ap_recount_subscribers', __CLASS__, 'recount_subscribers' );
		anspress()->add_action( 'wp_ajax_ap_recount_reputation', __CLASS__, 'recount_reputation' );
		anspress()->add_action( 'wp_ajax_ap_recount_views', __CLASS__, 'recount_views' );
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
	 * Cast vote as guest user with ID 0, so that when this vote never get
	 * rest if user vote.
	 *
	 * @since 2.5
	 */
	public static function ap_admin_vote() {
		$args = ap_sanitize_unslash( 'args', 'p' );

		if ( current_user_can( 'manage_options' ) && ap_verify_nonce( 'admin_vote' ) ) {
			$post = ap_get_post( $args[0] );

			if ( $post ) {
				$value  = 'up' === $args[1] ? true : false;
				$counts = ap_add_post_vote( $post->ID, 0, $value );
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
		$answers     = ap_get_answers( [ 'question_id' => $question_id ] );

		while ( ap_have_answers() ) :
			ap_the_answer();
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

		$data_type  = ap_sanitize_unslash( 'data_type', 'r' );
		$valid_data = [ 'qa', 'answers', 'options', 'userdata', 'terms', 'tables' ];

		global $wpdb;

		// Only allow super admin to delete data.
		if ( is_super_admin() && in_array( $data_type, $valid_data, true ) ) {
			$done = 0;

			if ( 'qa' === $data_type ) {

				$count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type='question' OR post_type='answer'" );
				$ids   = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type='question' OR post_type='answer' LIMIT 30" );

				foreach ( (array) $ids as $id ) {
					if ( false !== wp_delete_post( $id, true ) ) {
						$done++;
					}
				}

				wp_send_json(
					[
						'done'  => (int) $done,
						'total' => (int) $count,
					]
				);
			} elseif ( 'answers' === $data_type ) {

				$count = $wpdb->get_var( "SELECT count(*) FROM $wpdb->posts WHERE post_type='answer'" );
				$ids   = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type='answer' LIMIT 30" );

				foreach ( (array) $ids as $id ) {
					if ( false !== wp_delete_post( $id, true ) ) {
						$done++;
					}
				}

				wp_send_json(
					[
						'done'  => (int) $done,
						'total' => (int) $count,
					]
				);
			} elseif ( 'userdata' === $data_type ) {

				$upload_dir = wp_upload_dir();

				// Delete avatar folder.
				wp_delete_file( $upload_dir['baseurl'] . '/ap_avatars' );

				// Remove user roles.
				AP_Roles::remove_roles();

				// Delete vote meta.
				$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => '__up_vote_casted' ], array( '%s' ) ); // @codingStandardsIgnoreLine
				$wpdb->delete( $wpdb->usermeta, [ 'meta_key' => '__down_vote_casted' ], array( '%s' ) ); // @codingStandardsIgnoreLine

				wp_send_json(
					[
						'done'  => 1,
						'total' => 0,
					]
				);
			} elseif ( 'options' === $data_type ) {

				delete_option( 'anspress_opt' );
				delete_option( 'anspress_reputation_events' );
				delete_option( 'anspress_addons' );

				wp_send_json(
					[
						'done'  => 1,
						'total' => 0,
					]
				);
			} elseif ( 'terms' === $data_type ) {

				$question_taxo = (array) get_object_taxonomies( 'question', 'names' );
				$answer_taxo   = (array) get_object_taxonomies( 'answer', 'names' );

				$taxos = $question_taxo + $answer_taxo;

				foreach ( (array) $taxos as $tax ) {
					$terms = get_terms(
						array(
							'taxonomy'   => $tax,
							'hide_empty' => false,
							'fields'     => 'ids',
						)
					);

					foreach ( (array) $terms as $t ) {
						wp_delete_term( $t, $tax );
					}
				}

				wp_send_json(
					[
						'done'  => 1,
						'total' => 0,
					]
				);
			} elseif ( 'tables' === $data_type ) {

				$tables = [ $wpdb->ap_qameta, $wpdb->ap_votes, $wpdb->ap_views, $wpdb->ap_reputations, $wpdb->ap_subscribers, $wpdb->prefix . 'ap_notifications' ];

				foreach ( $tables as $table ) {
					$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
				}

				wp_send_json(
					[
						'done'  => 1,
						'total' => 0,
					]
				);
			}
		}

		// Send empty JSON if nothing done.
		wp_send_json( [] );
	}

	/**
	 * Toggle addons.
	 */
	public static function ap_toggle_addon() {
		check_ajax_referer( 'toggle_addon', '__nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			ap_ajax_json(
				array(
					'success'  => false,
					'snackbar' => [ 'message' => __( 'Sorry, you do not have permission!', 'anspress-question-answer' ) ],
				)
			);
		}

		$addon_id = ap_sanitize_unslash( 'addon_id', 'r' );
		if ( ap_is_addon_active( $addon_id ) ) {
			ap_deactivate_addon( $addon_id );
		} else {
			ap_activate_addon( $addon_id );
		}

		// Delete page check transient.
		delete_transient( 'ap_pages_check' );

		ap_ajax_json(
			array(
				'success'  => true,
				'addon_id' => $addon_id,
				'snackbar' => [ 'message' => __( 'Successfully enabled addon. Redirecting!', 'anspress-question-answer' ) ],
				'cb'       => 'toggleAddon',
			)
		);
	}

	/**
	 * Ajax callback for 'ap_recount_votes` which recounting votes of posts.
	 *
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_votes() {
		if ( ! ap_verify_nonce( 'recount_votes' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$paged  = (int) ap_sanitize_unslash( 'paged', 'r', 0 );
		$offset = absint( $paged * 100 );

		global $wpdb;

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type IN ('question', 'answer') LIMIT {$offset},100" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			ap_update_votes_count( $id );
		}

		$done   = $offset + count( $ids );
		$remain = $total_found - ( $offset + count( $ids ) );

		$json = array(
			'success' => true,
			'total'   => $total_found,
			'remain'  => $remain,
			'el'      => '.ap-recount-votes',
			'msg'     => sprintf( __( '%d done out of %d' ), $done, $total_found ),
		);

		if ( $remain > 0 ) {
			$json['q'] = array(
				'action'  => 'ap_recount_votes',
				'__nonce' => wp_create_nonce( 'recount_votes' ),
				'paged'   => $paged + 1,
			);
		}

		ap_send_json( $json );
	}

	/**
	 * Ajax callback for 'ap_recount_answers` which recounting answers of questions.
	 *
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_answers() {
		if ( ! ap_verify_nonce( 'recount_answers' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$paged  = (int) ap_sanitize_unslash( 'paged', 'r', 0 );
		$offset = absint( $paged * 100 );

		global $wpdb;

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type = 'question' LIMIT {$offset},100" ); // @codingStandardsIgnoreLine.
		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			ap_update_answers_count( $id, false, false );
		}

		$done   = $offset + count( $ids );
		$remain = $total_found - ( $offset + count( $ids ) );

		$json = array(
			'success' => true,
			'total'   => $total_found,
			'remain'  => $remain,
			'el'      => '.ap-recount-answers',
			'msg'     => sprintf( __( '%d done out of %d' ), $done, $total_found ),
		);

		if ( $remain > 0 ) {
			$json['q'] = array(
				'action'  => 'ap_recount_answers',
				'__nonce' => wp_create_nonce( 'recount_answers' ),
				'paged'   => $paged + 1,
			);
		}

		ap_send_json( $json );
	}

	/**
	 * Recount flags of posts.
	 *
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_flagged() {
		if ( ! ap_verify_nonce( 'recount_flagged' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		global $wpdb;

		$paged  = (int) ap_sanitize_unslash( 'paged', 'r', 0 );
		$offset = absint( $paged * 100 );

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type IN ('question', 'answer') LIMIT {$offset},100" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			ap_update_flags_count( $id );
		}

		$done   = $offset + count( $ids );
		$remain = $total_found - ( $offset + count( $ids ) );

		$json = array(
			'success' => true,
			'total'   => $total_found,
			'remain'  => $remain,
			'el'      => '.ap-recount-flagged',
			'msg'     => sprintf( __( '%d done out of %d' ), $done, $total_found ),
		);

		if ( $remain > 0 ) {
			$json['q'] = array(
				'action'  => 'ap_recount_flagged',
				'__nonce' => wp_create_nonce( 'recount_flagged' ),
				'paged'   => $paged + 1,
			);
		}

		ap_send_json( $json );
	}

	/**
	 * Recount question subscribers.
	 *
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_subscribers() {
		if ( ! ap_verify_nonce( 'recount_subscribers' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		global $wpdb;

		$paged  = (int) ap_sanitize_unslash( 'paged', 'r', 0 );
		$offset = absint( $paged * 100 );

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type = 'question' LIMIT {$offset},100" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			ap_update_subscribers_count( $id );
		}

		$done   = $offset + count( $ids );
		$remain = $total_found - ( $offset + count( $ids ) );

		$json = array(
			'success' => true,
			'total'   => $total_found,
			'remain'  => $remain,
			'el'      => '.ap-recount-subscribers',
			'msg'     => sprintf( __( '%d done out of %d' ), $done, $total_found ),
		);

		if ( $remain > 0 ) {
			$json['q'] = array(
				'action'  => 'ap_recount_subscribers',
				'__nonce' => wp_create_nonce( 'recount_subscribers' ),
				'paged'   => $paged + 1,
			);
		}

		ap_send_json( $json );
	}

	/**
	 * Recount users reputation.
	 *
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_reputation() {
		if ( ! ap_verify_nonce( 'recount_reputation' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		global $wpdb;

		$paged  = (int) ap_sanitize_unslash( 'paged', 'r', 0 );
		$offset = absint( $paged * 100 );

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->users} LIMIT {$offset},100" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			ap_update_user_reputation_meta( $id );
		}

		$done   = $offset + count( $ids );
		$remain = $total_found - ( $offset + count( $ids ) );

		$json = array(
			'success' => true,
			'total'   => $total_found,
			'remain'  => $remain,
			'el'      => '.ap-recount-reputation',
			'msg'     => sprintf( __( '%d done out of %d' ), $done, $total_found ),
		);

		if ( $remain > 0 ) {
			$json['q'] = array(
				'action'  => 'ap_recount_reputation',
				'__nonce' => wp_create_nonce( 'recount_reputation' ),
				'paged'   => $paged + 1,
			);
		}

		ap_send_json( $json );
	}

	/**
	 * Recount question views.
	 *
	 * @return void
	 * @since 4.0.5
	 */
	public static function recount_views() {
		if ( ! ap_verify_nonce( 'recount_views' ) || ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		global $wpdb;

		$args = wp_parse_args( ap_sanitize_unslash( 'args', 'r', '' ), array(
			'fake_views' => false,
			'min_views'  => 100,
			'max_views'  => 200,
		) );

		$paged  = (int) ap_sanitize_unslash( 'paged', 'r', 0 );
		$offset = absint( $paged * 100 );

		$ids = $wpdb->get_col( "SELECT SQL_CALC_FOUND_ROWS ID FROM {$wpdb->posts} WHERE post_type = 'question' LIMIT {$offset},100" ); // @codingStandardsIgnoreLine.

		$total_found = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // DB call okay, Db cache okay.

		foreach ( (array) $ids as $id ) {
			$table_views  = (int) ap_get_views( $id );
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

		$done   = $offset + count( $ids );
		$remain = $total_found - ( $offset + count( $ids ) );

		$json = array(
			'success' => true,
			'total'   => $total_found,
			'remain'  => $remain,
			'el'      => '.ap-recount-views',
			'msg'     => sprintf( __( '%d done out of %d' ), $done, $total_found ),
		);

		if ( $remain > 0 ) {
			$json['q'] = array(
				'action'  => 'ap_recount_views',
				'__nonce' => wp_create_nonce( 'recount_views' ),
				'paged'   => $paged + 1,
			);
		}

		ap_send_json( $json );
	}
}
