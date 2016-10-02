<?php
/**
 * All actions of AnsPress
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */
define( 'BP_AP_NOTIFIER_SLUG', 'ap_notification' );
class AnsPress_BP
{
	/**
	 * Initialize the class
	 * @since 2.0.1
	 */
	public function __construct() {

		add_action( 'bp_init', array( $this, 'bp_init' ) );
		// add_action( 'ap_enqueue', 'bp_activity_mentions_script' );
		add_action( 'bp_setup_nav',  array( $this, 'content_setup_nav' ) );
		add_post_type_support( 'question', 'buddypress-activity' );
		add_post_type_support( 'answer', 'buddypress-activity' );
		add_action( 'bp_init', array( $this, 'question_answer_tracking' ) );
		add_action( 'bp_activity_entry_meta', array( $this, 'activity_buttons' ) );
		add_filter( 'bp_activity_custom_post_type_post_action', array( $this, 'activity_action' ), 10, 2 );
		add_filter( 'bp_before_member_header_meta', array( $this, 'bp_profile_header_meta' ) );
		add_filter( 'ap_the_question_content', array( $this, 'ap_the_question_content' ) );

		add_action( 'bp_setup_globals', array( $this, 'notifier_setup_globals' ) );

		add_action( 'ap_after_new_answer', array( $this, 'add_new_answer_notification' ) );
		add_action( 'ap_publish_comment', array( $this, 'add_new_comment_notification' ) );
		//add_action( 'ap_trash_answer', array( $this, 'remove_answer_notify' ) );
		add_action( 'ap_unpublish_comment', array( $this, 'remove_comment_notify' ) );
	}

	public function bp_init() {

		add_filter( 'the_content', array( $this, 'ap_the_answer_content' ) );
	}

	public function content_setup_nav() {

		global $bp;

		if ( ! ap_opt( 'disable_reputation' ) ) {
			bp_core_new_nav_item( array(
			    'name'                  => __( 'Reputation', 'anspress-question-answer' ),
			    'slug'                  => 'reputation',
			    'screen_function'       => array( $this, 'reputation_screen_link' ),
			    'position'              => 30,// weight on menu, change it to whatever you want
			    'default_subnav_slug' => 'my-posts-subnav',

			) ); }
		bp_core_new_nav_item( array(
		    'name'                  => sprintf( __( 'Questions %s', 'anspress-question-answer' ), '<span class="count">'.count_user_posts( bp_displayed_user_id() , 'question' ).'</span>' ),
		    'slug'                  => 'questions',
		    'screen_function'       => array( $this, 'questions_screen_link' ),
		    'position'              => 40,// weight on menu, change it to whatever you want
		    'default_subnav_slug' => 'my-posts-subnav',

		) );
		bp_core_new_nav_item( array(
		    'name'                  => sprintf( __( 'Answers %s', 'anspress-question-answer' ), '<span class="count">'.count_user_posts( bp_displayed_user_id() , 'answer' ).'</span>' ),
		    'slug'                  => 'answers',
		    'screen_function'       => array( $this, 'answers_screen_link' ),
		    'position'              => 40,// weight on menu, change it to whatever you want
		    'default_subnav_slug' => 'my-posts-subnav',

		) );
	}

	public function reputation_screen_link() {
	    add_action( 'bp_template_title', array( $this, 'reputation_screen_title' ) );
	    add_action( 'bp_template_content', array( $this, 'reputation_screen_content' ) );
	    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	public function reputation_screen_title() {
	    _e( 'Reputation', 'anspress-question-answer' );
	}

	public function reputation_screen_content() {
		global $wpdb;
		$user_id = bp_displayed_user_id();

		$reputation = ap_get_all_reputation( $user_id );
		echo '<div id="anspress">';
	    ap_get_template_part( 'user/reputation' );
	    echo '</div>';
	}

	public function questions_screen_link() {
	    add_action( 'bp_template_title', array( $this, 'questions_screen_title' ) );
	    add_action( 'bp_template_content', array( $this, 'questions_screen_content' ) );
	    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	public function questions_screen_title() {
	    _e( 'Questions', 'anspress-question-answer' );
	}

	public function questions_screen_content() {
		global $questions;

		$questions 		 = new Question_Query( array( 'author' => bp_displayed_user_id() ) );
		echo '<div id="anspress">';
	    ap_get_template_part( 'buddypress/user-questions' );
	    echo '</div>';
	    wp_reset_postdata();
	}

	public function answers_screen_link() {
	    add_action( 'bp_template_title', array( $this, 'answers_screen_title' ) );
	    add_action( 'bp_template_content', array( $this, 'answers_screen_content' ) );
	    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	public function answers_screen_title() {
	    _e( 'Answers', 'anspress-question-answer' );
	}

	public function answers_screen_content() {
		global $answers;

		$answers 		 = new Answers_Query( array( 'author' => bp_displayed_user_id() ) );
		echo '<div id="anspress">';
	    include ap_get_theme_location( 'buddypress/user-answers.php' );
	    echo '</div>';
	    wp_reset_postdata();
	}

	public function question_answer_tracking() {
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
			'bp_activity_new_post'     => __( '%1$s asked a new <a href="AP_CPT_LINK">question</a>', 'anspress-question-answer' ),
			'bp_activity_new_post_ms'  => __( '%1$s asked a new <a href="AP_CPT_LINK">question</a>, on the site %3$s', 'anspress-question-answer' ),
	    ) );

	    bp_activity_set_post_type_tracking_args( 'answer', array(
	        'component_id'             => 'activity',
	        'action_id'                => 'new_answer',
	        'contexts'                 => array( 'activity', 'member' ),
	        'bp_activity_admin_filter' => __( 'Answer', 'anspress-question-answer' ),
			'bp_activity_front_filter' => __( 'Answer', 'anspress-question-answer' ),
			'bp_activity_new_post'     => __( '%1$s <a href="AP_CPT_LINK">answered</a> a question', 'anspress-question-answer' ),
			'bp_activity_new_post_ms'  => __( '%1$s <a href="AP_CPT_LINK">answered</a> a question, on the site %3$s', 'anspress-question-answer' ),
	    ) );
	}

	public function activity_buttons() {

		if ( 'new_question' == bp_get_activity_type() ) {
			echo '<a class="button answer bp-secondary-action" title="'.__( 'Answer this question', 'anspress-question-answer' ).'" href="'.ap_answers_link( bp_get_activity_secondary_item_id() ).'">'.__( 'Answer', 'anspress-question-answer' ).'</a>'; }
	}

	public function activity_action($action, $activity) {

		if ( $activity->type == 'new_question' || $activity->type == 'new_answer' ) {
			return str_replace( 'AP_CPT_LINK', get_permalink( $activity->secondary_item_id ), $action ); }

		return $action;
	}

	public function bp_profile_header_meta() {
		if ( ap_opt( 'disable_reputation' ) ) {
			return; }

		echo '<span class="ap-user-meta ap-user-meta-reputation">'. sprintf( __( '%s Reputation', 'anspress-question-answer' ), ap_get_reputation( bp_displayed_user_id(), true ) ) .'</span>';
	}

	/**
	 * Filter question content and link metions
	 * @return string
	 */
	public function ap_the_question_content($content) {
		return bp_activity_at_name_filter( $content );
	}

	public function ap_the_answer_content($content) {
		global $post;

		if ( ! function_exists( 'bp_activity_at_name_filter' ) ) {
			require_once WP_PLUGIN_DIR.'/buddypress/bp-activity/bp-activity-filters.php'; }

		if ( $post->post_type == 'answer' ) {
			return bp_activity_at_name_filter( $content ); }

		return $content;
	}
	public function notifier_setup_globals() {
	    global $bp;
	    $bp->ap_notifier = new stdClass();
	    $bp->ap_notifier->id = 'ap_notifier';// I asume others are not going to use this is
	    $bp->ap_notifier->slug = BP_AP_NOTIFIER_SLUG;
	    $bp->ap_notifier->notification_callback = array( $this, 'ap_notifier_format_notifications' );// show the notification
	    /* Register this in the active components array */
	    $bp->active_components[$bp->ap_notifier->id] = $bp->ap_notifier->id;

	    do_action( 'ap_notifier_setup_globals' );
	}

	public function ap_notifier_format_notifications( $action, $activity_id, $secondary_item_id, $total_items, $format = 'string' ) {

	   	$amount = 'single';

	   	if ( strrpos( $action, 'new_answer' ) !== false ) {
	   		$answer = get_post( $activity_id );

	   		if ( $answer ) {
				$notification_link  = get_permalink( $answer->ID );

				$title = substr( strip_tags( $answer->post_title ), 0, 35 ). (strlen( $answer->post_title ) > 35 ? '...' : '') ;

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
			$post  = get_post( $comment->comment_post_ID );
			$notification_link  = get_permalink( $comment->comment_post_ID );

			$type = $post->post_type == 'question' ? __( 'question', 'anspress-question-answer' ) : __( 'answer', 'anspress-question-answer' );
			$amount = 'single';

			$title = substr( strip_tags( $post->post_title ), 0, 35 ). (strlen( $post->post_title ) > 35 ? '...' : '') ;

			if ( (int) $total_items > 1 ) {
				$text = sprintf( __( '%1$d comments on your %3$s - %2$s', 'anspress-question-answer' ), (int) $total_items, $title, $type );
				$amount = 'multiple';
			} else {
				$user_fullname = bp_core_get_user_displayname( $secondary_item_id );
				$text = sprintf( __( '%1$s commented on your %3$s - %2$s', 'anspress-question-answer' ), $user_fullname, $title, $type );
			}
		}

		if ( 'string' == $format ) {

			$return = apply_filters( 'ap_notifier_' . $amount . '_at_mentions_notification', '<a href="' . esc_url( $notification_link ) . '">' . esc_html( $text ) . '</a>', $notification_link, (int) $total_items, $activity_id, $secondary_item_id );
		} else {

			$return = apply_filters( 'ap_notifier_' . $amount . '_at_mentions_notification', array(
				'text' => @$text,
				'link' => @$notification_link,
			), @$notification_link, (int) $total_items, $activity_id, $secondary_item_id );
		}

		do_action( 'ap_notifier_format_notifications', $action, $activity_id, $secondary_item_id, $total_items );

		return $return;
	}

	public function add_new_answer_notification( $post_id ) {
	    if ( bp_is_active( 'notifications' ) ) {
	    	global $bp;
	    	$answer = get_post( $post_id );

	    	$participants = ap_get_parti( $answer->post_parent );

	    	$notification_args = array(
	            'item_id'           => $answer->ID,
	            'secondary_item_id' => $answer->post_author,
	            'component_name'    => $bp->ap_notifier->id,
	            'component_action'  => 'new_answer_'.$answer->post_parent,
	            'date_notified'     => bp_core_current_time(),
	            'is_new'            => 1,
	    	);

	    	if ( ! empty( $participants ) && is_array( $participants ) ) {
				foreach ( $participants as $p ) {
					if ( $p->apmeta_userid != $answer->post_author ) {
						$notification_args['user_id'] = $p->apmeta_userid;
						bp_notifications_add_notification( $notification_args );
					}
				}
			}
	    }
	}

	public function add_new_comment_notification( $comment ) {
		$comment = (object) $comment;
	    if ( bp_is_active( 'notifications' ) ) {
	    	global $bp;
	    	$post = get_post( $comment->comment_post_ID );

	    	if ( $post->post_type == 'answer' ) {
	    		$participants = ap_get_parti( false, false, $comment->comment_post_ID ); }

	    	if ( $post->post_type == 'question' ) {
	    		$participants = ap_get_parti( $comment->comment_post_ID ); }

	    	$notification_args = array(
	            'item_id'           => $comment->comment_ID,
	            'secondary_item_id' => $comment->user_id,
	            'component_name'    => $bp->ap_notifier->id,
	            'component_action'  => 'new_comment_'.$post->ID,
	            'date_notified'     => bp_core_current_time(),
	            'is_new'            => 1,
	    	);

	    	if ( ! empty( $participants ) && is_array( $participants ) ) {
				foreach ( $participants as $p ) {
					if ( $p->apmeta_userid != $comment->user_id ) {
						$notification_args['user_id'] = $p->apmeta_userid;
						bp_notifications_add_notification( $notification_args );
					}
				}
			}
	    }
	}

	/**
	 * Remove question notification when corresponding question get deleted
	 * @param  integer $post_id
	 * @return void
	 */
	public function remove_question_notify($post_id) {

		if ( bp_is_active( 'notifications' ) ) {
			bp_notifications_delete_all_notifications_by_type( $post_id, buddypress()->ap_notifier->id, 'new_answer_'.$post_id ); }

	}

	/**
	 * Remove answer notification when corresponding answer get deleted
	 * @param  object $comment
	 * @return void
	 */
	public function remove_comment_notify($comment) {

		if ( bp_is_active( 'notifications' ) ) {
			bp_notifications_delete_all_notifications_by_type( $comment->comment_post_ID, buddypress()->ap_notifier->id, 'new_comment_'.$comment->comment_post_ID ); }

	}

}
