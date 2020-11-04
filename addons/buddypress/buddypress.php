<?php
/**
 * Integrate with BuddyPress profile.
 *
 * @author       Rahul Aryan <support@anspress.com>
 * @copyright    2014 anspress.net & Rahul Aryan
 * @license      GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link         https://anspress.net
 * @package      AnsPress
 * @subpackage   BuddyPress Addon
 *
 * @anspress-addon
 * Addon Name:    BuddyPress
 * Addon URI:     https://anspress.net
 * Description:   Integrate AnsPress with BuddyPress.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.net
 */

namespace AnsPress\Addons;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'BP_AP_NOTIFIER_SLUG', 'ap_notification' );

/**
 * AnsPress BuddyPress hooks.
 *
 * @since 4.1.8 Renamed from AnsPress_BP_Hooks.
 */
class BuddyPress extends \AnsPress\Singleton {

	/**
	 * Refers to a single instance of this class.
	 *
	 * @var null|object
	 * @since 4.1.8
	 */
	public static $instance = null;

	/**
	 * Initialize the class
	 *
	 * @since 2.0.1
	 */
	protected function __construct() {
		add_post_type_support( 'question', 'buddypress-activity' );
		add_post_type_support( 'answer', 'buddypress-activity' );

		anspress()->add_action( 'bp_init', $this, 'bp_init' );
		anspress()->add_action( 'ap_assets_js', $this, 'ap_assets_js' );
		// anspress()->add_action( 'ap_enqueue', 'bp_activity_mentions_script' );
		anspress()->add_action( 'bp_setup_nav', $this, 'content_setup_nav' );
		anspress()->add_action( 'bp_init', $this, 'question_answer_tracking' );
		anspress()->add_action( 'bp_activity_entry_meta', $this, 'activity_buttons' );
		anspress()->add_filter( 'bp_activity_custom_post_type_post_action', $this, 'activity_action', 10, 2 );

		anspress()->add_filter( 'ap_the_question_content', $this, 'ap_the_question_content' );
		anspress()->add_action( 'bp_notifications_get_registered_components', $this, 'registered_components' );
		anspress()->add_action( 'bp_notifications_get_notifications_for_user', $this, 'notifications_for_user', 10, 8 );

		anspress()->add_action( 'ap_after_new_answer', $this, 'new_answer_notification' );
		anspress()->add_action( 'ap_publish_comment', $this, 'new_comment_notification' );
		anspress()->add_action( 'ap_trash_question', $this, 'remove_answer_notify' );
		// anspress()->add_action( 'ap_trash_question', $this, 'remove_comment_notify' );
		anspress()->add_action( 'ap_trash_answer', $this, 'remove_answer_notify' );
		anspress()->add_action( 'ap_trash_answer', $this, 'remove_comment_notify' );
		anspress()->add_action( 'ap_unpublish_comment', $this, 'remove_comment_notify' );
		anspress()->add_action( 'before_delete_post', $this, 'remove_answer_notify' );
		// anspress()->add_action( 'before_delete_post', $this, 'remove_comment_notify' );
		anspress()->add_action( 'the_post', $this, 'mark_bp_notify_as_read' );

		anspress()->add_action( 'ap_ajax_bp_loadmore', $this, 'bp_loadmore' );
	}

	/**
	 * Hook on BuddyPress init.
	 */
	public function bp_init() {
		anspress()->add_filter( 'the_content', $this, 'ap_the_answer_content' );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param array $js Javacript array.
	 * @return array
	 */
	public function ap_assets_js( $js ) {

		if ( ! function_exists( 'bp_current_action' ) && ! function_exists( 'bp_current_component' ) ) {
			return $js;
		}

		if ( bp_current_component() === 'qa' ) {
			$js['main']['active']  = true;
			$js['theme']['active'] = true;
		}

		return $js;
	}

	/**
	 * BuddyPress nav hook.
	 */
	public function content_setup_nav() {
		global $bp;

		bp_core_new_nav_item(
			array(
				'name'                => __( 'Q&A', 'anspress-question-answer' ),
				'slug'                => 'qa',
				'screen_function'     => [ $this, 'ap_qa_page' ],
				'position'            => 30, // weight on menu, change it to whatever you want.
				'default_subnav_slug' => 'questions',
			)
		);

		$subnav = array(
			[
				'name' => __( 'Questions', 'anspress-question-answer' ),
				'slug' => 'questions',
			],
			[
				'name' => __( 'Answers', 'anspress-question-answer' ),
				'slug' => 'answers',
			],
		);

		$subnav = apply_filters( 'ap_bp_nav', $subnav );
		foreach ( $subnav as $nav ) {
			$this->setup_subnav( $nav['name'], $nav['slug'] );
		}
	}

	/**
	 * Setup sub nav.
	 */
	public function setup_subnav( $name, $slug ) {
		bp_core_new_subnav_item(
			array(
				'name'            => $name,
				'slug'            => $slug,
				'parent_url'      => trailingslashit( bp_displayed_user_domain() . 'qa' ),
				'parent_slug'     => 'qa',
				'screen_function' => [ $this, 'ap_qa_page' ],
				'position'        => 10,
				'user_has_access' => 'all',
			)
		);
	}

	/**
	 * AnsPress nav callback.
	 */
	public function ap_qa_page() {
		add_action( 'bp_template_content', [ $this, 'ap_qa_page_content' ] );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for QA page content.
	 */
	public function ap_qa_page_content() {
		$template = bp_current_action();

		echo '<div id="anspress" class="anspress ' . esc_attr( $template ) . '">';

		$page_cb = apply_filters( 'ap_bp_page', [ $this, 'page_' . $template ], $template );

		if ( method_exists( $page_cb[0], $page_cb[1] ) ) {
			call_user_func( $page_cb );
		} else {
			esc_attr_e( 'No AnsPress template found for rendering this page.', 'anspress-question-answer' );
		}

		echo '</div>';
	}

	/**
	 * Callback for rendering questions page.
	 */
	public function page_questions( $user_id = false, $paged = false, $only_posts = false ) {
		$args['ap_current_user_ignore'] = true;
		$args['showposts']              = 10;

		if ( false === $user_id ) {
			$user_id = bp_displayed_user_id();
		}

		$args['author'] = $user_id;

		if ( false !== $paged ) {
			$args['paged'] = $paged;
		}

		/**
		 * FILTER: ap_authors_questions_args
		 * Filter authors question list args
		 *
		 * @var array
		 */
		$args                 = apply_filters( 'ap_bp_questions_args', $args );
		anspress()->questions = new \Question_Query( $args );

		if ( false === $only_posts ) {
			echo '<div class="ap-bp-head clearfix">';
			echo '<h1>' . esc_attr__( 'Questions', 'anspress-question-answer' ) . '</h1>';
			ap_list_filters();
			echo '</div>';
			echo '<div id="ap-bp-questions">';
		}

		if ( ap_have_questions() ) {
			/* Start the Loop */
			while ( ap_have_questions() ) :
				ap_the_question();
				ap_get_template_part( 'buddypress/question-item' );
			endwhile;
		}

		if ( false === $only_posts ) {
			echo '</div>';
		}

		if ( anspress()->questions->max_num_pages > 1 && false === $only_posts ) {
			$args = wp_json_encode(
				[
					'__nonce' => wp_create_nonce( 'loadmore-questions' ),
					'type'    => 'questions',
					'current' => 1,
					'user_id' => bp_displayed_user_id(),
				]
			);
			echo '<a href="#" class="ap-bp-loadmore ap-btn" ap-loadmore="' . esc_js( $args ) . '">' . esc_attr__( 'Load more questions', 'anspress-question-answer' ) . '</a>';
		}
	}

	/**
	 * Callback for rendering questions page.
	 */
	public function page_answers( $user_id = false, $paged = false, $order_by = false, $only_posts = false ) {
		global $answers;

		$order_by                       = false === $order_by ? 'active' : $order_by;
		$args['ap_current_user_ignore'] = true;
		$args['ignore_selected_answer'] = true;
		$args['showposts']              = 10;
		$args['author']                 = bp_displayed_user_id();
		$args['ap_order_by']            = ap_sanitize_unslash( 'order_by', 'r', $order_by );

		if ( false !== $paged ) {
			$args['paged'] = $paged;
		}

		/**
		 * FILTER: ap_authors_questions_args
		 * Filter authors question list args
		 *
		 * @var array
		 */
		$args               = apply_filters( 'ap_bp_answers_args', $args );
		anspress()->answers = $answers = new \Answers_Query( $args );

		if ( false === $only_posts ) {
			echo '<div class="ap-bp-head clearfix">';
			echo '<h1>' . esc_attr__( 'Answers', 'anspress-question-answer' ) . '</h1>';
			ap_answers_tab( get_the_permalink() );
			echo '</div>';
			echo '<div id="ap-bp-answers">';
		}

		if ( ap_have_answers() ) {
			/* Start the Loop */
			while ( ap_have_answers() ) :
				ap_the_answer();
				ap_get_template_part( 'buddypress/answer-item' );
			endwhile;
		}

		if ( false === $only_posts ) {
			echo '</div>';
		}

		if ( $answers->max_num_pages > 1 && false === $only_posts ) {
			$args = wp_json_encode(
				[
					'__nonce'  => wp_create_nonce( 'loadmore-answers' ),
					'type'     => 'answers',
					'current'  => 1,
					'user_id'  => bp_displayed_user_id(),
					'order_by' => ap_sanitize_unslash( 'order_by', 'r' ),
				]
			);
			echo '<a href="#" class="ap-bp-loadmore ap-btn" ap-loadmore="' . esc_js( $args ) . '">' . esc_attr__( 'Load more answers', 'anspress-question-answer' ) . '</a>';
		}
	}

	/**
	 * Set tracking arguments for question and answer post type.
	 */
	public function question_answer_tracking() {
		// Check if the Activity component is active before using it.
		if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'activity' ) ) {
			return;
		}

		bp_activity_set_post_type_tracking_args(
			'question', array(
				'component_id'             => 'activity',
				'action_id'                => 'new_question',
				'contexts'                 => array( 'activity', 'member' ),
				'bp_activity_admin_filter' => __( 'Question', 'anspress-question-answer' ),
				'bp_activity_front_filter' => __( 'Question', 'anspress-question-answer' ),
				'bp_activity_new_post'     => __( '%1$s asked a new <a href="AP_CPT_LINK">question</a>', 'anspress-question-answer' ),
				'bp_activity_new_post_ms'  => __( '%1$s asked a new <a href="AP_CPT_LINK">question</a>, on the site %3$s', 'anspress-question-answer' ),
			)
		);

		bp_activity_set_post_type_tracking_args(
			'answer', array(
				'component_id'             => 'activity',
				'action_id'                => 'new_answer',
				'contexts'                 => array( 'activity', 'member' ),
				'bp_activity_admin_filter' => __( 'Answer', 'anspress-question-answer' ),
				'bp_activity_front_filter' => __( 'Answer', 'anspress-question-answer' ),
				'bp_activity_new_post'     => __( '%1$s <a href="AP_CPT_LINK">answered</a> a question', 'anspress-question-answer' ),
				'bp_activity_new_post_ms'  => __( '%1$s <a href="AP_CPT_LINK">answered</a> a question, on the site %3$s', 'anspress-question-answer' ),
			)
		);
	}

	/**
	 * Custom button for question and answer activities.
	 */
	public function activity_buttons() {
		if ( 'new_question' === bp_get_activity_type() ) {
			echo '<a class="button answer bp-secondary-action" title="' . esc_attr__( 'Answer this question', 'anspress-question-answer' ) . '" href="' . esc_url( ap_answers_link( bp_get_activity_secondary_item_id() ) ) . '">' . esc_attr__( 'Answer', 'anspress-question-answer' ) . '</a>';
		}
	}

	/**
	 * Activity action.
	 */
	public function activity_action( $action, $activity ) {
		if ( in_array( $activity->type, [ 'new_question', 'new_answer' ], true ) ) {
			return str_replace( 'AP_CPT_LINK', get_permalink( $activity->secondary_item_id ), $action );
		}

		return $action;
	}

	/**
	 * Filter question content and link metions.
	 *
	 * @param string $content Contents.
	 * @return string
	 */
	public function ap_the_question_content( $content ) {
		return bp_activity_at_name_filter( $content );
	}

	/**
	 * Filter answer content and link metions.
	 *
	 * @param string $content Contents.
	 * @return string
	 */
	public function ap_the_answer_content( $content ) {
		global $post;

		if ( ! function_exists( 'bp_activity_at_name_filter' ) ) {
			require_once WP_PLUGIN_DIR . '/buddypress/bp-activity/bp-activity-filters.php';
		}

		if ( 'answer' === $post->post_type ) {
			return bp_activity_at_name_filter( $content );
		}

		return $content;
	}

	/**
	 * Register anspress component.
	 *
	 * @return array
	 * @since 4.1.8
	 */
	public function registered_components( array $components ) {
		array_push( $components, 'anspress' );

		return $components;
	}

	/**
	 * Format custom notification of AnsPress.
	 *
	 * @param string $action               Component action.
	 * @param int    $item_id               Notification item ID.
	 * @param int    $secondary_item_id     Notification secondary item ID.
	 * @param int    $total_items           Number of notifications with the same action.
	 * @param string $format                Format of return. Either 'string' or 'object'.
	 * @param string $component_action_name Canonical notification action.
	 * @param string $component_name        Notification component ID.
	 * @param int    $id                    Notification ID.
	 * @return mixed
	 *
	 * @since 4.1.8
	 */
	public function notifications_for_user( $action, $item_id, $secondary_item_id, $total_items, $format = 'string', $component_action_name, $component_name, $id ) {
		if ( method_exists( $this, 'notification_' . $component_action_name ) ) {
			$method = 'notification_' . $action;
			return $this->$method( $item_id, $secondary_item_id, $total_items, $format, $id );
		}
	}

	/**
	 * Format answer notifications.
	 *
	 * @param integer $item_id           Item id.
	 * @param integer $secondary_item_id Secondary item.
	 * @param integer $total_items       Total items.
	 * @param string  $format            Notification type.
	 * @param integer $id                Notification id.
	 *
	 * @return string
	 * @since 4.1.8
	 */
	private function notification_new_answer( $item_id, $secondary_item_id, $total_items, $format, $id ) {
		$post    = get_post( $item_id );
		$link    = get_permalink( $post );
		$author  = bp_core_get_user_displayname( $secondary_item_id );

		$title   = substr( strip_tags( $post->post_title ), 0, 35 ) . ( strlen( $post->post_title ) > 35 ? '...' : '' );

		if ( 'string' === $format ) {
			if ( (int) $total_items > 1 ) {
				return '<a href="' . esc_url( $link ) . '">' . sprintf( __( '%1$d answers on your question - %2$s', 'anspress-question-answer' ), (int) $total_items, $title ) . '</a>';
			}

			return '<a href="' . esc_url( $link ) . '">' . sprintf( __( '%1$s answered on your question - %2$s', 'anspress-question-answer' ), $author, $title ) . '</a>';
		}

		return array(
			'link' => $link,
			'text' => sprintf( __( 'New answer on %s', 'anspress-question-answer' ), $title ),
		);
	}

	/**
	 * Format comments notifications.
	 *
	 * @param integer $item_id           Item id.
	 * @param integer $secondary_item_id Secondary item.
	 * @param integer $total_items       Total items.
	 * @param string  $format            Notification type.
	 * @param integer $id                Notification id.
	 *
	 * @return string
	 * @since 4.1.8
	 */
	private function notification_new_comment( $item_id, $secondary_item_id, $total_items, $format, $id ) {
		$comment = get_comment( $item_id );
		$post    = get_post( $comment->comment_post_ID );
		$link    = get_comment_link( $comment );
		$author  = get_comment_author( $comment );
		$type    = 'question' ===	$post->post_type ? __( 'question', 'anspress-question-answer' ) : __( 'answer', 'anspress-question-answer' );

		$title   = substr( strip_tags( $post->post_title ), 0, 35 ) . ( strlen( $post->post_title ) > 35 ? '...' : '' );

		if ( 'string' === $format ) {
			if ( (int) $total_items > 1 ) {
				return '<a href="' . esc_url( $link ) . '">' . sprintf( __( '%1$d comments on your %3$s - %2$s', 'anspress-question-answer' ), (int) $total_items, $title, $type ) . '</a>';
			}

			return '<a href="' . esc_url( $link ) . '">' . sprintf( __( '%1$s commented on your %3$s - %2$s', 'anspress-question-answer' ), $author, $title, $type ) . '</a>';
		}

		return array(
			'link' => $link,
			'text' => $title,
		);
	}

	/**
	 * New answer notifications.
	 *
	 * @param integer $post_id Post ID.
	 */
	public function new_answer_notification( $post_id ) {
		if ( ! bp_is_active( 'notifications' ) ) {
			return;
		}

		$post     = get_post( $post_id );
		$question = get_post( $post->post_parent );

		bp_notifications_add_notification( array(
			'user_id'           => $question->post_author,
			'item_id'           => $post->ID,
			'secondary_item_id' => $post->post_author,
			'component_name'    => 'anspress',
			'component_action'  => 'new_answer',
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		) );
	}

	/**
	 * New comment notification.
	 *
	 * @param object $comment Comment object.
	 */
	public function new_comment_notification( $comment ) {
		if ( ! bp_is_active( 'notifications' ) ) {
			return;
		}

		global $bp;

		$comment = (object) $comment;
		$post    = get_post( $comment->comment_post_ID );

		bp_notifications_add_notification( array(
			'user_id'          => $post->post_author,
			'item_id'          => $comment->comment_ID,
			'component_name'   => 'anspress',
			'component_action' => 'new_comment',
			'date_notified'    => bp_core_current_time(),
			'is_new'           => 1,
		) );
	}

	/**
	 * Remove question notification when corresponding question get deleted.
	 *
	 * @param  integer $post_id Post ID.
	 */
	public function remove_answer_notify( $post_id ) {
		if ( ! bp_is_active( 'notifications' ) ) {
			return;
		}

		$post = get_post( $post_id );
		bp_notifications_delete_all_notifications_by_type( $post->ID, 'anspress', 'new_answer' );
	}

	/**
	 * Remove answer notification when corresponding answer get deleted.
	 *
	 * @param  object $comment Comment object.
	 */
	public function remove_comment_notify( $comment ) {
		if ( ! bp_is_active( 'notifications' ) ) {
			return;
		}

		if ( $comment->comment_ID ) {
			bp_notifications_delete_all_notifications_by_type( $comment->comment_ID, 'anspress', 'new_comment' );
		} else {
			$comments = get_comments( [ 'post_id' => $comment ] );
			foreach ( (array) $comments as $comment ) {
				bp_notifications_delete_all_notifications_by_type( $comment->comment_ID, 'anspress', 'new_comment' );
			}
		}
	}

	/**
	 * Mark notification as read when corresponding question is loaded
	 *
	 * @param mixed $post_id Post ID or Object.
	 */
	public function mark_bp_notify_as_read( $post_id ) {

		if ( ! bp_is_active( 'notifications' ) || ! is_question() ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( 'answer' === $post_id->post_type ) {
			bp_notifications_mark_notifications_by_item_id( $user_id, $post_id->ID, 'anspress', 'new_answer' );
		}

		if ( $post_id->comment_count >= 1 ) {
			$comments = get_comments( [ 'post_id' => $post_id->ID ] );

			foreach ( (array) $comments as $comment ) {
				bp_notifications_mark_notifications_by_item_id( $user_id, $comment->comment_ID, 'anspress', 'new_comment' );
			}
		}
	}

	/**
	 * Ajax callback for loading more posts.
	 */
	public function bp_loadmore() {
		$type     = ap_sanitize_unslash( 'type', 'r' );
		$order_by = ap_sanitize_unslash( 'order_by', 'r' );
		$user_id  = (int) ap_sanitize_unslash( 'user_id', 'r' );
		$paged    = (int) ap_sanitize_unslash( 'current', 'r' ) + 1;
		check_ajax_referer( 'loadmore-' . $type, '__nonce' );

		if ( 'questions' === $type ) {
			ob_start();
			$this->page_questions( $user_id, $paged, true );
			$html = ob_get_clean();

			$paged = anspress()->questions->max_num_pages > $paged ? $paged : false;

			ap_ajax_json(
				array(
					'success' => true,
					'element' => '#ap-bp-questions',
					'args'    => [
						'__nonce' => wp_create_nonce( 'loadmore-questions' ),
						'type'    => 'questions',
						'current' => $paged,
						'user_id' => bp_displayed_user_id(),
					],
					'html'    => $html,
				)
			);
		} elseif ( 'answers' === $type ) {
			ob_start();
			$this->page_answers( $user_id, $paged, $order_by, true );
			$html = ob_get_clean();

			global $answers;
			$paged = $answers->max_num_pages > $paged ? $paged : false;

			ap_ajax_json(
				array(
					'success' => true,
					'element' => '#ap-bp-answers',
					'args'    => [
						'__nonce'  => wp_create_nonce( 'loadmore-answers' ),
						'type'     => 'answers',
						'current'  => $paged,
						'user_id'  => bp_displayed_user_id(),
						'order_by' => $order_by,
					],
					'html'    => $html,
				)
			);
		}
	}

}

// Include BuddyPress hooks and files.
if ( class_exists( '\BuddyPress' ) ) {
	\AnsPress\Addons\BuddyPress::init();
}
