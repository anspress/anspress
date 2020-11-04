<?php
/**
 * Addon for user notifications.
 *
 * @author     Rahul Aryan <rah12@live.com>
 * @copyright  2017 anspress.net & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.net
 * @package    AnsPress
 * @subpackage Notifications Addon
 * @since      4.1.8
 */

namespace AnsPress\Addons;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Require functions.
require_once ANSPRESS_ADDONS_DIR . '/notifications/functions.php';
require_once ANSPRESS_ADDONS_DIR . '/notifications/query.php';

/**
 * AnsPress notifications hooks.
 *
 * @package AnsPress
 * @author  Rahul Aryan <rah12@live.com>
 * @since   4.0.0
 */
class Notifications extends \AnsPress\Singleton {

	/**
	 * Instance of this class.
	 *
	 * @var     object
	 * @since 4.1.8
	 */
	protected static $instance = null;

	/**
	 * Initialize the class.
	 *
	 * @since 4.1.8
	 */
	protected function __construct() {
		ap_add_default_options(
			[
				'user_page_title_notifications' => __( 'Notifications', 'anspress-question-answer' ),
				'user_page_slug_notifications'  => 'notifications',
			]
		);

		anspress()->add_filter( 'ap_form_addon-notifications', $this, 'load_options' );

		// Activate AnsPress notifications only if buddypress not active.
		if ( ! ap_is_addon_active( 'buddypress.php' ) ) {
			ap_register_page( 'notifications', __( 'Notifications', 'anspress-question-answer' ), '', true, true );
			anspress()->add_filter( 'ap_menu_object', $this, 'ap_menu_object' );
			anspress()->add_action( 'ap_notification_verbs', $this, 'register_verbs' );
			anspress()->add_action( 'ap_user_pages', $this, 'ap_user_pages' );
			anspress()->add_action( 'ap_after_new_answer', $this, 'new_answer', 10, 2 );
			anspress()->add_action( 'ap_trash_question', $this, 'trash_question', 10, 2 );
			anspress()->add_action( 'ap_before_delete_question', $this, 'trash_question', 10, 2 );
			anspress()->add_action( 'ap_trash_answer', $this, 'trash_answer', 10, 2 );
			anspress()->add_action( 'ap_before_delete_answer', $this, 'trash_answer', 10, 2 );
			anspress()->add_action( 'ap_untrash_answer', $this, 'new_answer', 10, 2 );
			anspress()->add_action( 'ap_select_answer', $this, 'select_answer' );
			anspress()->add_action( 'ap_unselect_answer', $this, 'unselect_answer' );
			anspress()->add_action( 'ap_publish_comment', $this, 'new_comment' );
			anspress()->add_action( 'ap_unpublish_comment', $this, 'delete_comment' );
			anspress()->add_action( 'ap_vote_up', $this, 'vote_up' );
			anspress()->add_action( 'ap_vote_down', $this, 'vote_down' );
			anspress()->add_action( 'ap_undo_vote_up', $this, 'undo_vote_up' );
			anspress()->add_action( 'ap_undo_vote_down', $this, 'undo_vote_down' );
			anspress()->add_action( 'ap_insert_reputation', $this, 'insert_reputation', 10, 4 );
			anspress()->add_action( 'ap_delete_reputation', $this, 'delete_reputation', 10, 3 );
			anspress()->add_action( 'ap_ajax_mark_notifications_seen', $this, 'mark_notifications_seen' );
			anspress()->add_action( 'ap_ajax_load_more_notifications', $this, 'load_more_notifications' );
			anspress()->add_action( 'ap_ajax_get_notifications', $this, 'get_notifications' );
		}
	}

	/**
	 * Register notification addon options.
	 *
	 * @return array
	 * @since 4.1.8
	 */
	public function load_options() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'user_page_title_notifications' => array(
					'label' => __( 'Notifications page title', 'anspress-question-answer' ),
					'desc'  => __( 'Custom title for user profile notifications page', 'anspress-question-answer' ),
					'value' => $opt['user_page_title_notifications'],
				),
				'user_page_slug_notifications'  => array(
					'label' => __( 'Notifications page slug', 'anspress-question-answer' ),
					'desc'  => __( 'Custom slug for user profile notifications page', 'anspress-question-answer' ),
					'value' => $opt['user_page_slug_notifications'],
				),
			),
		);

		return $form;
	}

	/**
	 * Filter notification menu title.
	 *
	 * @param  object $items Menu item object.
	 * @return array
	 */
	public function ap_menu_object( $items ) {
		foreach ( $items as $k => $i ) {
			if ( isset( $i->object ) && 'notifications' === $i->object ) {
				$items[ $k ]->url  = '#apNotifications';
				$items[ $k ]->type = 'custom';
			}
		}

		return $items;
	}

	public function register_verbs() {
		ap_register_notification_verb(
			'new_answer', array(
				'label' => __( 'posted an answer on your question', 'anspress-question-answer' ),
			)
		);

		ap_register_notification_verb(
			'new_comment', array(
				'ref_type' => 'comment',
				'label'    => __( 'commented on your %cpt%', 'anspress-question-answer' ),
			)
		);

		ap_register_notification_verb(
			'vote_up', array(
				'ref_type' => 'post',
				'label'    => __( 'up voted your %cpt%', 'anspress-question-answer' ),
			)
		);

		ap_register_notification_verb(
			'vote_down', array(
				'ref_type'   => 'post',
				'hide_actor' => true,
				'icon'       => 'apicon-thumb-down',
				'label'      => __( 'down voted your %cpt%', 'anspress-question-answer' ),
			)
		);

		ap_register_notification_verb(
			'best_answer', array(
				'ref_type' => 'post',
				'label'    => __( 'selected your answer', 'anspress-question-answer' ),
			)
		);

		ap_register_notification_verb(
			'new_points', array(
				'ref_type' => 'reputation',
				'label'    => __( 'You have earned %points% points', 'anspress-question-answer' ),
			)
		);

		ap_register_notification_verb(
			'lost_points', array(
				'ref_type' => 'reputation',
				'label'    => __( 'You lose %points% points', 'anspress-question-answer' ),
			)
		);
	}

	/**
	 * Adds reputations tab in AnsPress authors page.
	 */
	public function ap_user_pages() {
		anspress()->user_pages[] = array(
			'slug'    => 'notifications',
			'label'   => __( 'Notifications', 'anspress-question-answer' ),
			'count'   => ap_count_unseen_notifications(),
			'icon'    => 'apicon-globe',
			'cb'      => [ $this, 'notification_page' ],
			'private' => true,
		);
	}

	/**
	 * Display reputation tab content in AnsPress author page.
	 */
	public function notification_page() {
		$user_id = ap_current_user_id();
		$seen    = ap_sanitize_unslash( 'seen', 'r', 'all' );

		if ( get_current_user_id() === $user_id ) {
			$seen          = 'all' === $seen ? null : (int) $seen;
			$notifications = new \AnsPress\Notifications(
				[
					'user_id' => $user_id,
					'seen'    => $seen,
				]
			);

			do_action( 'ap_before_notification_page', $notifications );

			include ap_get_theme_location( 'addons/notification/index.php' );
		} else {
			_e( 'You do not have permission to view this page', 'anspress-question-answer' ); // xss okay.
		}
	}

	/**
	 * Remove all notifications related to question when its get deleted.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post Post object.
	 */
	public function trash_question( $post_id, $_post ) {
		ap_delete_notifications(
			array(
				'parent'   => $post_id,
				'ref_type' => [ 'answer', 'vote_up', 'vote_down', 'post' ],
			)
		);
	}

	/**
	 * Add notification for new answer.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post Post object.
	 */
	public function new_answer( $post_id, $_post ) {
		$_question = get_post( $_post->post_parent );
		ap_insert_notification(
			array(
				'user_id'  => $_question->post_author,
				'actor'    => $_post->post_author,
				'parent'   => $_post->post_parent,
				'ref_id'   => $_post->ID,
				'ref_type' => 'answer',
				'verb'     => 'new_answer',
			)
		);
	}

	/**
	 * Remove all notifications related to answer when its get deleted.
	 *
	 * @param integer $post_id Post ID.
	 * @param object  $_post Post object.
	 */
	public function trash_answer( $post_id, $_post ) {
		ap_delete_notifications(
			array(
				'ref_id'   => $post_id,
				'ref_type' => [ 'answer', 'vote_up', 'vote_down', 'post' ],
			)
		);
	}

	/**
	 * Notify user when their answer is selected.
	 *
	 * @param object $_post Post object.
	 */
	public function select_answer( $_post ) {
		// Award select answer points to question author only.
		if ( get_current_user_id() !== $_post->post_author ) {
			ap_insert_notification(
				array(
					'user_id'  => $_post->post_author,
					'actor'    => get_current_user_id(),
					'parent'   => $_post->post_parent,
					'ref_id'   => $_post->ID,
					'ref_type' => 'answer',
					'verb'     => 'best_answer',
				)
			);
		}
	}

	/**
	 * Remove notification when users answer get unselected.
	 *
	 * @param object $_post Post object.
	 */
	public function unselect_answer( $_post ) {
		ap_delete_notifications(
			array(
				'parent'   => $_post->post_parent,
				'ref_type' => 'answer',
				'verb'     => 'best_answer',
			)
		);
	}

	/**
	 * Notify user on new comment.
	 *
	 * @param  object $comment WordPress comment object.
	 */
	public function new_comment( $comment ) {
		$_post = get_post( $comment->comment_post_ID );

		if ( get_current_user_id() !== $_post->post_author ) {
			ap_insert_notification(
				array(
					'user_id'  => $_post->post_author,
					'actor'    => $comment->user_id,
					'parent'   => $comment->comment_post_ID,
					'ref_id'   => $comment->comment_ID,
					'ref_type' => 'comment',
					'verb'     => 'new_comment',
				)
			);
		}
	}

	/**
	 * Remove notification on deleting comment.
	 *
	 * @param  object $comment Comment object.
	 */
	public function delete_comment( $comment ) {
		ap_delete_notifications(
			array(
				'actor'    => $comment->user_id,
				'parent'   => $comment->comment_post_ID,
				'ref_type' => 'comment',
			)
		);
	}

	/**
	 * Award reputation when user recive an up vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public function vote_up( $post_id ) {
		$_post = get_post( $post_id );

		if ( get_current_user_id() !== $_post->post_author ) {
			ap_insert_notification(
				array(
					'user_id'  => $_post->post_author,
					'actor'    => get_current_user_id(),
					'parent'   => $_post->ID,
					'ref_id'   => $_post->ID,
					'ref_type' => $_post->post_type,
					'verb'     => 'vote_up',
				)
			);
		}
	}

	/**
	 * Notify when user recive an down vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public function vote_down( $post_id ) {
		$_post = get_post( $post_id );

		if ( get_current_user_id() !== $_post->post_author ) {
			ap_insert_notification(
				array(
					'user_id'  => $_post->post_author,
					'actor'    => get_current_user_id(),
					'parent'   => $_post->ID,
					'ref_id'   => $_post->ID,
					'ref_type' => $_post->post_type,
					'verb'     => 'vote_down',
				)
			);
		}
	}

	/**
	 * Notify when user recive an up vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public function undo_vote_up( $post_id ) {
		ap_delete_notifications(
			array(
				'ref_id' => $post_id,
				'actor'  => get_current_user_id(),
				'verb'   => 'vote_up',
			)
		);
	}

	/**
	 * Notify when user recive an down vote.
	 *
	 * @param integer $post_id Post ID.
	 */
	public function undo_vote_down( $post_id ) {
		ap_delete_notifications(
			array(
				'ref_id' => $post_id,
				'actor'  => get_current_user_id(),
				'verb'   => 'vote_down',
			)
		);
	}

	/**
	 * Notify user on new reputation.
	 *
	 * @param integer $reputation_id Reputation id.
	 * @param integer $user_id User id.
	 * @param string  $event Reputation event.
	 * @param integer $ref_id Reputation reference id.
	 */
	public function insert_reputation( $reputation_id, $user_id, $event, $ref_id ) {
		ap_insert_notification(
			array(
				'user_id'  => $user_id,
				'ref_id'   => $reputation_id,
				'ref_type' => 'reputation',
				'verb'     => ap_get_reputation_event_points( $event ) > 0 ? 'new_points' : 'lost_points',
			)
		);
	}

	/**
	 * Notify user on new reputation.
	 *
	 * @param integer|false $deleted NUmbers of rows deleted.
	 * @param integer       $user_id User id.
	 * @param string        $event Reputation event.
	 */
	public function delete_reputation( $deleted, $user_id, $event ) {
		ap_delete_notifications(
			array(
				'ref_type' => 'reputation',
				'user_id'  => $user_id,
			)
		);
	}

	/**
	 * Ajax callback for marking all notification of current user
	 * as seen.
	 */
	public function mark_notifications_seen() {
		if ( ! is_user_logged_in() || ! ap_verify_nonce( 'mark_notifications_seen' ) ) {
			ap_ajax_json(
				array(
					'success'  => false,
					'snackbar' => [ 'message' => __( 'There was a problem processing your request', 'anspress-question-answer' ) ],
				)
			);
		}

		// Mark all notifications as seen.
		ap_set_notifications_as_seen( get_current_user_id() );

		ap_ajax_json(
			array(
				'success'  => true,
				'btn'      => [ 'hide' => true ],
				'snackbar' => [ 'message' => __( 'Successfully updated all notifications', 'anspress-question-answer' ) ],
				'cb'       => 'notificationAllRead',
			)
		);

		wp_die();
	}

	/**
	 * Ajax callback for loading more notifications.
	 */
	public function load_more_notifications() {
		check_admin_referer( 'load_more_notifications', '__nonce' );

		$user_id = ap_sanitize_unslash( 'user_id', 'r' );
		$paged   = ap_sanitize_unslash( 'current', 'r', 1 ) + 1;

		ob_start();
		$notifications = new \AnsPress\Notifications(
			[
				'user_id' => $user_id,
				'paged'   => $paged,
			]
		);

		while ( $notifications->have() ) :
			$notifications->the_notification();
			$notifications->item_template();
		endwhile;

		$html = ob_get_clean();

		$paged = $notifications->total_pages > $paged ? $paged : 0;

		ap_ajax_json(
			array(
				'success' => true,
				'args'    => [
					'ap_ajax_action' => 'load_more_notifications',
					'__nonce'        => wp_create_nonce( 'load_more_notifications' ),
					'current'        => (int) $paged,
					'user_id'        => $user_id,
				],
				'html'    => $html,
				'element' => '.ap-noti',
			)
		);
	}

	/**
	 * Ajax callback for loading user notifications dropdown.
	 */
	public function get_notifications() {
		if ( ! is_user_logged_in() ) {
			wp_die();
		}

		$notifications = new \AnsPress\Notifications( [ 'user_id' => get_current_user_id() ] );

		$items = [];
		while ( $notifications->have() ) :
			$notifications->the_notification();
			$items[] = array(
				'ID'         => $notifications->object->noti_id,
				'verb'       => $notifications->object->noti_verb,
				'verb_label' => $notifications->get_verb(),
				'icon'       => $notifications->get_icon(),
				'avatar'     => $notifications->actor_avatar(),
				'hide_actor' => $notifications->hide_actor(),
				'actor'      => $notifications->get_actor(),
				'ref_title'  => $notifications->get_ref_title(),
				'ref_type'   => $notifications->object->noti_ref_type,
				'points'     => $notifications->get_reputation_points(),
				'date'       => ap_human_time( $notifications->get_date(), false ),
				'permalink'  => $notifications->get_permalink(),
				'seen'       => $notifications->object->noti_seen,
			);
		endwhile;

		ap_ajax_json(
			array(
				'success'       => true,
				'notifications' => $items,
				'total'         => ap_count_unseen_notifications(),
				'mark_args'     => array(
					'ap_ajax_action' => 'mark_notifications_seen',
					'__nonce'        => wp_create_nonce( 'mark_notifications_seen' ),
				),
			)
		);
	}

}

/**
 * Insert table when addon is activated.
 */
function ap_notification_addon_activation() {
	global $wpdb;
	$charset_collate = ! empty( $wpdb->charset ) ? 'DEFAULT CHARACTER SET ' . $wpdb->charset : '';

	$table = 'CREATE TABLE `' . $wpdb->prefix . 'ap_notifications` (
			`noti_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`noti_user_id` bigint(20) NOT NULL,
			`noti_actor` bigint(20) NOT NULL,
			`noti_parent` bigint(20) NOT NULL,
			`noti_ref_id` bigint(20) NOT NULL,
			`noti_ref_type` varchar(100) NOT NULL,
			`noti_verb` varchar(100) NOT NULL,
			`noti_date` timestamp NULL DEFAULT NULL,
			`noti_seen` tinyint(1) NOT NULL DEFAULT 0,
			PRIMARY KEY (`noti_id`)
		)' . $charset_collate . ';';

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $table );
}
ap_addon_activation_hook( basename( __FILE__ ), __NAMESPACE__ . '\ap_notification_addon_activation' );

// Init class.
Notifications::init();
