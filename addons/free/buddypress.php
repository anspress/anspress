<?php
/**
 * Integrate with BuddyPress profile.
 *
 * @author       Rahul Aryan <support@anspress.com>
 * @copyright    2014 AnsPress.io & Rahul Aryan
 * @license      GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link         https://anspress.io
 * @package      AnsPress
 * @subpackage   BuddyPress Addon
 *
 * @anspress-addon
 * Addon Name:    BuddyPress
 * Addon URI:     https://anspress.io
 * Description:   Integrate AnsPress with BuddyPress.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'BP_AP_NOTIFIER_SLUG', 'ap_notification' );

/**
 * AnsPress BuddyPress hooks.
 */
class AnsPress_BP_Hooks {

	/**
	 * Initialize the class
	 *
	 * @since 2.0.1
	 */
	public static function init() {
		add_post_type_support( 'question', 'buddypress-activity' );
		add_post_type_support( 'answer', 'buddypress-activity' );

		anspress()->add_action( 'bp_init', __CLASS__, 'bp_init' );
		anspress()->add_action( 'ap_assets_js', __CLASS__, 'ap_assets_js' );
		// anspress()->add_action( 'ap_enqueue', 'bp_activity_mentions_script' );
		anspress()->add_action( 'bp_setup_nav', __CLASS__, 'content_setup_nav' );
		anspress()->add_action( 'bp_init', __CLASS__, 'question_answer_tracking' );
		anspress()->add_action( 'bp_activity_entry_meta', __CLASS__, 'activity_buttons' );
		anspress()->add_filter( 'bp_activity_custom_post_type_post_action', __CLASS__, 'activity_action', 10, 2 );

		anspress()->add_filter( 'ap_the_question_content', __CLASS__, 'ap_the_question_content' );
		anspress()->add_action( 'bp_setup_globals', __CLASS__, 'notifier_setup_globals' );
		anspress()->add_action( 'ap_after_new_answer', __CLASS__, 'new_answer_notification' );
		anspress()->add_action( 'ap_publish_comment', __CLASS__, 'new_comment_notification' );
		anspress()->add_action( 'ap_trash_question', __CLASS__, 'remove_answer_notify' );
		//anspress()->add_action( 'ap_trash_question', __CLASS__, 'remove_comment_notify' );
		anspress()->add_action( 'ap_trash_answer', __CLASS__, 'remove_answer_notify' );
		anspress()->add_action( 'ap_trash_answer', __CLASS__, 'remove_comment_notify' );
		anspress()->add_action( 'ap_unpublish_comment', __CLASS__, 'remove_comment_notify' );
		anspress()->add_action( 'before_delete_post', __CLASS__, 'remove_answer_notify' );
		//anspress()->add_action( 'before_delete_post', __CLASS__, 'remove_comment_notify' );
		anspress()->add_action( 'the_post', __CLASS__, 'mark_bp_notify_as_read' );

		anspress()->add_action( 'ap_ajax_bp_loadmore', __CLASS__, 'bp_loadmore' );
	}

	/**
	 * Hook on BuddyPress init.
	 */
	public static function bp_init() {
		anspress()->add_filter( 'the_content', __CLASS__, 'ap_the_answer_content' );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param array $js Javacript array.
	 * @return array
	 */
	public static function ap_assets_js( $js ) {

		if ( ! function_exists( 'bp_current_action' ) && ! function_exists( 'bp_current_component' ) ) {
			return $js;
		}

		if ( bp_current_component() === 'qa' ) {
			$js['main']['active'] = true;
			$js['theme']['active'] = true;
		}

		return $js;
	}

	/**
	 * BuddyPress nav hook.
	 */
	public static function content_setup_nav() {
		global $bp;

		bp_core_new_nav_item( array(
				'name'                  => __( 'Q&A', 'anspress-question-answer' ),
				'slug'                  => 'qa',
				'screen_function'       => [ __CLASS__, 'ap_qa_page' ],
				'position'              => 30,// weight on menu, change it to whatever you want.
				'default_subnav_slug' => 'questions',
		) );

		$subnav = array(
			[ 'name' => __( 'Questions', 'anspress-question-answer' ), 'slug' => 'questions' ],
			[ 'name' => __( 'Answers', 'anspress-question-answer' ), 'slug' => 'answers' ],
		);

		$subnav = apply_filters( 'ap_bp_nav', $subnav );
		foreach ( $subnav as $nav ) {
			SELF::setup_subnav( $nav['name'], $nav['slug'] );
		}
	}

	/**
	 * Setup sub nav.
	 */
	public static function setup_subnav( $name, $slug ) {
		bp_core_new_subnav_item( array(
			'name'            => $name,
			'slug'            => $slug,
			'parent_url'      => trailingslashit( bp_displayed_user_domain() . 'qa' ),
			'parent_slug'     => 'qa',
			'screen_function' => [ __CLASS__, 'ap_qa_page' ],
			'position'        => 10,
			'user_has_access' => 'all',
		) );
	}

	/**
	 * AnsPress nav callback.
	 */
	public static function ap_qa_page() {
		add_action( 'bp_template_content', [ __CLASS__, 'ap_qa_page_content' ] );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Callback for QA page content.
	 */
	public static function ap_qa_page_content() {
		$template = bp_current_action();

		echo '<div id="anspress" class="anspress ' . esc_attr( $template ) . '">';

		$page_cb = apply_filters( 'ap_bp_page', [ __CLASS__, 'page_' . $template ], $template );

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
	public static function page_questions( $user_id = false, $paged = false, $only_posts = false ) {
		global $questions;
		$args['ap_current_user_ignore'] = true;
		$args['showposts'] = 10;

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
		$args = apply_filters( 'ap_bp_questions_args', $args );
		anspress()->questions = $questions = new Question_Query( $args );

		if ( false === $only_posts ) {
			echo '<div class="ap-bp-head clearfix">';
			echo '<h1>' . esc_attr__( 'Questions', 'anspress-question-answer' ) . '</h1>';
			ap_list_filters();
			echo '</div>';
			echo '<div id="ap-bp-questions">';
		}

		if ( ap_have_questions() ) {
			/* Start the Loop */
			while ( ap_have_questions() ) : ap_the_question();
				ap_get_template_part( 'buddypress/question-item' );
			endwhile;
		}

		if ( $questions->max_num_pages > 1 && false === $only_posts ) {
			echo '</div>';
			$args = wp_json_encode( [ '__nonce' => wp_create_nonce( 'loadmore-questions' ), 'type' => 'questions', 'current' => 1, 'user_id' => bp_displayed_user_id() ] );
			echo '<a href="#" class="ap-bp-loadmore ap-btn" ap-loadmore="' . esc_js( $args ) . '">' . esc_attr__( 'Load more questions', 'anspress-question-answer' ) .'</a>';
		}
	}

	/**
	 * Callback for rendering questions page.
	 */
	public static function page_answers( $user_id = false, $paged = false, $order_by = false, $only_posts = false ) {
		global $answers;

		$order_by = false === $order_by ? 'active' : $order_by;
		$args['ap_current_user_ignore'] = true;
		$args['ignore_selected_answer'] = true;
		$args['showposts'] = 10;
		$args['author'] = bp_displayed_user_id();
		$args['ap_order_by'] = ap_sanitize_unslash( 'order_by', 'r', $order_by );

		if ( false !== $paged ) {
			$args['paged'] = $paged;
		}

		/**
		 * FILTER: ap_authors_questions_args
		 * Filter authors question list args
		 *
		 * @var array
		 */
		$args = apply_filters( 'ap_bp_answers_args', $args );
		anspress()->answers = $answers = new Answers_Query( $args );

		if ( false === $only_posts ) {
			echo '<div class="ap-bp-head clearfix">';
			echo '<h1>' . esc_attr__( 'Answers', 'anspress-question-answer' ) . '</h1>';
			ap_answers_tab( get_the_permalink() );
			echo '</div>';
			echo '<div id="ap-bp-answers">';
		}

		if ( ap_have_answers() ) {
			/* Start the Loop */
			while ( ap_have_answers() ) : ap_the_answer();
				ap_get_template_part( 'buddypress/answer-item' );
			endwhile;
		}

		if ( $answers->max_num_pages > 1 && false === $only_posts ) {
			echo '</div>';
			$args = wp_json_encode( [ '__nonce' => wp_create_nonce( 'loadmore-answers' ), 'type' => 'answers', 'current' => 1, 'user_id' => bp_displayed_user_id(), 'order_by' => ap_sanitize_unslash( 'order_by', 'r' ) ] );

			echo '<a href="#" class="ap-bp-loadmore ap-btn" ap-loadmore="' . esc_js( $args ) . '">' . esc_attr__( 'Load more answers', 'anspress-question-answer' ) . '</a>';
		}
	}

	/**
	 * Set tracking arguments for question and answer post type.
	 */
	public static function question_answer_tracking() {
		// Check if the Activity component is active before using it.
		if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'activity' ) ) {
			return;
		}

		bp_activity_set_post_type_tracking_args( 'question', array(
			'component_id'             => 'activity',
			'action_id'                => 'new_question',
			'contexts'                 => array( 'activity', 'member' ),
			'bp_activity_admin_filter' => __( 'Question', 'anspress-question-answer' ),
			'bp_activity_front_filter' => __( 'Question', 'anspress-question-answer' ),
			'bp_activity_new_post'     => __( '%1$s asked: <a href="AP_CPT_LINK">[Question]</a>', 'anspress-question-answer' ),
			'bp_activity_new_post_ms'  => __( '%1$s asked: <a href="AP_CPT_LINK">[Question]</a>, on the site %3$s', 'anspress-question-answer' ),
		) );

		bp_activity_set_post_type_tracking_args( 'answer', array(
			'component_id'             => 'activity',
			'action_id'                => 'new_answer',
			'contexts'                 => array( 'activity', 'member' ),
			'bp_activity_admin_filter' => __( 'Answer', 'anspress-question-answer' ),
			'bp_activity_front_filter' => __( 'Answer', 'anspress-question-answer' ),
			'bp_activity_new_post'     => __( '%1$s answered to: <a href="AP_CPT_LINK">[Answer]</a>', 'anspress-question-answer' ),
			'bp_activity_new_post_ms'  => __( '%1$s answered to: <a href="AP_CPT_LINK">[Answer]</a>, on the site %3$s', 'anspress-question-answer' ),
		) );
	}

	/**
	 * Custom button for question and answer activities.
	 */
	public static function activity_buttons() {
		if ( 'new_question' === bp_get_activity_type() ) {
			echo '<a class="button answer bp-secondary-action" title="' . esc_attr__( 'Answer this question', 'anspress-question-answer' ) . '" href="' . esc_url( ap_answers_link( bp_get_activity_secondary_item_id() ) ) . '">' . esc_attr__( 'Answer', 'anspress-question-answer' ) . '</a>';
		}
	}

	/**
	 * Custom button for question and answer activities.
	 */
	public static function activity_buttons() {
		if ( 'new_question' === bp_get_activity_type() ) {
			echo '<a class="button answer bp-secondary-action" title="' . esc_attr__( 'Answer this question', 'anspress-question-answer' ) . '" href="' . esc_url( ap_answers_link( bp_get_activity_secondary_item_id() ) ) . '">' . esc_attr__( 'Answer', 'anspress-question-answer' ) . '</a>';
		}
	}

	/**
	 * Activity action.
	 */
	public static function activity_action( $action, $activity ) {
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
	public static function ap_the_question_content( $content ) {
		return bp_activity_at_name_filter( $content );
	}

	/**
	 * Filter answer content and link metions.
	 *
	 * @param string $content Contents.
	 * @return string
	 */
	public static function ap_the_answer_content( $content ) {
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
	 * Setup AnsPress notification.
	 */
	public static function notifier_setup_globals() {
		global $bp;

		$bp->ap_notifier = new stdClass();
		$bp->ap_notifier->id = 'ap_notifier';
		$bp->ap_notifier->slug = BP_AP_NOTIFIER_SLUG;
		$bp->ap_notifier->notification_callback = array( __CLASS__, 'ap_notifier_format' );

		// Register this in the active components array.
		$bp->active_components[ $bp->ap_notifier->id ] = $bp->ap_notifier->id;

		do_action( 'notifier_setup_globals' );
	}

	/**
	 * Format notifications.
	 *
	 * @param string  $action Action name.
	 * @param integer $activity_id Activity name.
	 * @param integer $secondary_item_id Secondary item ID.
	 * @param integer $total_items Total items.
	 * @param string  $format Format.
	 */
	public static function ap_notifier_format( $action, $activity_id, $secondary_item_id, $total_items, $format = 'string' ) {
		$amount = 'single';
		$notification_link = '';
		$text = '';

		if ( strrpos( $action, 'new_answer' ) !== false ) {
		 	$answer = get_post( $activity_id );

			if ( $answer ) {
				$notification_link = get_permalink( $answer->ID );
				$title = substr( strip_tags( $answer->post_title ), 0, 35 ) . (strlen( $answer->post_title ) > 35 ? '...' : '') ;

				if ( (int) $total_items > 1 ) {
					$text = sprintf( __( '%1$d answers on - %2$s', 'anspress-question-answer' ), (int) $total_items, $title );
					$amount = 'multiple';
				} else {
					$user_fullname = bp_core_get_user_displayname( $secondary_item_id );
					$text = sprintf( __( '%1$s answered on - %2$s', 'anspress-question-answer' ), $user_fullname, $title );
				}
			}
		} elseif ( strrpos( $action, 'new_comment' ) !== false ) {
			$comment = get_comment( $activity_id );
			$post = get_post( $comment->comment_post_ID ); // WPCS: override okay.
			$notification_link  = get_permalink( $comment->comment_post_ID );
			$type = 'question' === $post->post_type ? __( 'question', 'anspress-question-answer' ) : __( 'answer', 'anspress-question-answer' );
			$amount = 'single';
			$title = substr( strip_tags( $post->post_title ), 0, 35 ) . ( strlen( $post->post_title ) > 35 ? '...' : '') ;

			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( '%1$d comments on your %3$s - %2$s', 'anspress-question-answer' ), (int) $total_items, $title, $type );
				$amount = 'multiple';
			} else {
				$user_fullname = bp_core_get_user_displayname( $secondary_item_id );
				$text = sprintf( __( '%1$s commented on your %3$s - %2$s', 'anspress-question-answer' ), $user_fullname, $title, $type );
			}
		}

		if ( 'string' === $format ) {
			$return = apply_filters( 'ap_notifier_' . $amount . '_at_mentions_notification', '<a href="' . esc_url( $notification_link ) . '">' . esc_html( $text ) . '</a>', $notification_link, (int) $total_items, $activity_id, $secondary_item_id );
		} else {
			$return = apply_filters( 'ap_notifier_' . $amount . '_at_mentions_notification', array(
				'text' => $text,
				'link' => $notification_link,
			), $notification_link, (int) $total_items, $activity_id, $secondary_item_id );
		}

		do_action( 'ap_notifier_format_notifications', $action, $activity_id, $secondary_item_id, $total_items );

		return $return;
	}

	/**
	 * New answer notifications.
	 *
	 * @param integer $post_id Post ID.
	 */
	public static function new_answer_notification( $post_id ) {
		if ( ! bp_is_active( 'notifications' ) ) {
			return;
		}

		global $bp;

		$answer = get_post( $post_id );
		$answer = get_post( $answer->post_parent );
		$subscribers = [ $answer->post_author ];

		$notification_args = array(
			'item_id'           => $answer->ID,
			'secondary_item_id' => $answer->post_author,
			'component_name'    => $bp->ap_notifier->id,
			'component_action'  => 'new_answer_'.$answer->post_parent,
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		);

		foreach ( (array) $subscribers as $s ) {
			if ( $s != $answer->post_author ) {
				$notification_args['user_id'] = $s;
				bp_notifications_add_notification( $notification_args );
			}
		}
	}

	/**
	 * New comment notification.
	 *
	 * @param object $comment Comment object.
	 */
	public static function new_comment_notification( $comment ) {
		if ( ! bp_is_active( 'notifications' ) ) {
			return;
		}

		global $bp;

		$comment = (object) $comment;
		$post = get_post( $comment->comment_post_ID );
		$subscribers = [ $post->post_author ];

		$notification_args = array(
			'item_id'           => $comment->comment_ID,
			'secondary_item_id' => $comment->user_id,
			'component_name'    => $bp->ap_notifier->id,
			'component_action'  => 'new_comment_' . $post->ID,
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
		);

		foreach ( (array) $subscribers as $s ) {
			if ( $s != $comment->user_id ) {
				$notification_args['user_id'] = $s;
				bp_notifications_add_notification( $notification_args );
			}
		}
	}

	/**
	 * Remove question notification when corresponding question get deleted.
	 *
	 * @param  integer $post_id Post ID.
	 */
	public static function remove_answer_notify( $post_id ) {
		if ( ! bp_is_active( 'notifications' ) ) {
			return;
		}

		$post = get_post( $post_id );
		bp_notifications_delete_all_notifications_by_type( $post->ID, buddypress()->ap_notifier->id, 'new_answer_' . $post->post_parent );
	}

	/**
	 * Remove answer notification when corresponding answer get deleted.
	 *
	 * @param  object $comment Comment object.
	 */
	public static function remove_comment_notify( $comment ) {
		if ( ! bp_is_active( 'notifications' ) ) {
			return;
		}

		if ( $comment->comment_ID ) {
			bp_notifications_delete_all_notifications_by_type( $comment->comment_ID, buddypress()->ap_notifier->id, 'new_comment_' . $comment->comment_post_ID );
		} else {
			$comments = get_comments( [ 'post_id' => $comment ] );
			foreach ( (array) $comments as $comment ) {
				bp_notifications_delete_all_notifications_by_type( $comment->comment_ID, buddypress()->ap_notifier->id, 'new_comment_' . $comment->comment_post_ID );
			}
		}
	}

	/**
	 * Mark notification as read when corresponding question is loaded
	 *
	 * @param mixed $post_id Post ID or Object.
	 */
	public static function mark_bp_notify_as_read( $post_id ) {

		if ( ! bp_is_active( 'notifications' ) || ! is_question( ) ) {
			return;
		}

		$user_id = get_current_user_id();

		if ( 'answer' === $post_id->post_type ) {
			bp_notifications_mark_notifications_by_item_id( $user_id, $post_id->ID, buddypress()->ap_notifier->id, 'new_answer_' . $post_id->post_parent );
		}

		if ( $post_id->comment_count >= 1 ) {
			$comments = get_comments( [ 'post_id' => $post_id->ID ] );

			foreach ( (array) $comments as $comment ) {
				bp_notifications_mark_notifications_by_item_id( $user_id, $comment->comment_ID, buddypress()->ap_notifier->id, 'new_comment_' . $post_id->ID );
			}
		}
	}

	/**
	 * Ajax callback for loading more posts.
	 */
	public static function bp_loadmore() {
		$type = ap_sanitize_unslash( 'type', 'r' );
		$order_by = ap_sanitize_unslash( 'order_by', 'r' );
		$user_id = (int) ap_sanitize_unslash( 'user_id', 'r' );
		$paged = (int) ap_sanitize_unslash( 'current', 'r' ) + 1;
		check_ajax_referer( 'loadmore-' . $type, '__nonce' );

		if ( 'questions' === $type ) {
			ob_start();
			SELF::page_questions( $user_id, $paged, true );
			$html = ob_get_clean();

			global $questions;
			$paged = $questions->max_num_pages > $paged ? $paged : false;

			ap_ajax_json(array(
				'success'  => true,
				'element'  => '#ap-bp-questions',
				'args'     => [ '__nonce' => wp_create_nonce( 'loadmore-questions' ), 'type' => 'questions', 'current' => $paged, 'user_id' => bp_displayed_user_id() ],
				'html'   	 => $html,
			));
		} elseif ( 'answers' === $type ) {
			ob_start();
			SELF::page_answers( $user_id, $paged, $order_by, true );
			$html = ob_get_clean();

			global $answers;
			$paged = $answers->max_num_pages > $paged ? $paged : false;

			ap_ajax_json(array(
				'success'  => true,
				'element'  => '#ap-bp-answers',
				'args'  => [ '__nonce' => wp_create_nonce( 'loadmore-answers' ), 'type' => 'answers', 'current' => $paged, 'user_id' => bp_displayed_user_id(), 'order_by' => $order_by ],
				'html'   	 => $html,
			));
		}
	}
}


// Include BuddyPress hooks and files.
if ( class_exists( 'BuddyPress' ) ) {
	AnsPress_BP_Hooks::init();
}

     /**
	 * Filter question activity strings
	 * to display post title
	 */

        function question_include_post_type_title( $action, $activity ) {
            if ( empty( $activity->id ) ) {
                return $action;
            }
            if ( 'new_question' != $activity->type ) {
                return $action;
            }
            preg_match_all( '/<a.*?>([^>]*)<\/a>/', $action, $matches );
            if ( empty( $matches[1][1] ) || '[Question]' != $matches[1][1] ) {
                return $action;
            }
            $post_type_title = bp_activity_get_meta( $activity->id, 'post_title' );
            if ( empty( $post_type_title ) ) {
                switch_to_blog( $activity->item_id );
                $post_type_title = get_post_field( 'post_title', $activity->secondary_item_id );
                // We have a title save it in activity meta to avoid switching blogs too much
                if ( ! empty( $post_type_title ) ) {
                    bp_activity_update_meta( $activity->id, 'post_title', $post_type_title );
                }
                restore_current_blog();
            }
            return str_replace( $matches[1][1], esc_html( $post_type_title ), $action );
        }
        add_filter( 'bp_activity_custom_post_type_post_action', 'question_include_post_type_title', 120, 2 );
    
     /**
	 * Filter answer activity strings
	 * to display post title
	 */

        function answer_include_post_type_title( $action, $activity ) {
            if ( empty( $activity->id ) ) {
                return $action;
            }
            if ( 'new_answer' != $activity->type ) {
                return $action;
            }
            preg_match_all( '/<a.*?>([^>]*)<\/a>/', $action, $matches );
            if ( empty( $matches[1][1] ) || '[Answer]' != $matches[1][1] ) {
                return $action;
            }
            $post_type_title = bp_activity_get_meta( $activity->id, 'post_title' );
            if ( empty( $post_type_title ) ) {
                switch_to_blog( $activity->item_id );
                $post_type_title = get_post_field( 'post_title', $activity->secondary_item_id );
                // We have a title save it in activity meta to avoid switching blogs too much
                if ( ! empty( $post_type_title ) ) {
                    bp_activity_update_meta( $activity->id, 'post_title', $post_type_title );
                }
                restore_current_blog();
            }
            return str_replace( $matches[1][1], esc_html( $post_type_title ), $action );
        }
        add_filter( 'bp_activity_custom_post_type_post_action', 'answer_include_post_type_title', 121, 2 );

