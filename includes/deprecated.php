<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

use AnsPress\Classes\Plugin;
use AnsPress\Modules\Subscriber\SubscriberModel;
use AnsPress\Modules\Vote\VoteService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php';
}

/**
 * Removes all filters from a WordPress filter, and stashes them in the anspress()
 * global in the event they need to be restored later.
 * Copied directly from bbPress plugin.
 *
 * @global WP_filter $wp_filter
 * @global array $merged_filters
 *
 * @param string $tag Hook name.
 * @param int    $priority Hook priority.
 * @return bool
 *
 * @since 4.2.0
 * @deprecated 5.0.0
 */
function ap_remove_all_filters( $tag, $priority = false ) { // @codingStandardsIgnoreLine
	_deprecated_function( __FUNCTION__, '4.2.0' );

	return true;
}


/**
 * Insert new subscriber.
 *
 * @param  integer|false $user_id User ID.
 * @param  string        $event   Event type.
 * @param  integer       $ref_id Reference identifier id.
 * @return bool|integer
 *
 * @category haveTest
 *
 * @since  4.0.0
 * @since  4.1.5 Removed default values for arguments `$event` and `$ref_id`. Delete count cache.
 * @deprecated 5.0.0 Use AnsPress\Plugin::get(AnsPress\Modules\Subscriber\SubscriberService::class)->create() instead.
 */
function ap_new_subscriber( $user_id = false, $event = '', $ref_id = 0 ) {
	_deprecated_function(
		__FUNCTION__,
		'5.0.0',
		esc_attr__(
			'Use AnsPress\Plugin::get(AnsPress\Modules\Subscriber\SubscriberService::class)->create() instead.',
			'anspress-question-answer'
		)
	);

	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$exists = ap_get_subscriber( $user_id, $event, $ref_id );

	if ( ! $exists ) {
		$insert = $wpdb->insert( // phpcs:ignore WordPress.DB
			$wpdb->ap_subscribers,
			array(
				'subs_user_id' => $user_id,
				'subs_event'   => sanitize_title( $event ),
				'subs_ref_id'  => $ref_id,
			),
			array( '%d', '%s', '%d' )
		);

		if ( false !== $insert ) {
			_deprecated_hook(
				'ap_new_subscriber',
				'5.0.0',
				'Use `anspress/model/after_insert` instead.'
			);

			/**
			 * Hook triggered right after inserting a subscriber.
			 *
			 * @param integer $subs_id Subscription id.
			 * @param integer $user_id User id.
			 * @param string  $event   Event name.
			 * @param integer $ref_id  Reference id.
			 *
			 * @since 4.0.0
			 * @deprecated 5.0.0 Use `anspress/model/after_insert` instead.
			 */
			do_action( 'ap_new_subscriber', $wpdb->insert_id, $user_id, $event, $ref_id );

			return $wpdb->insert_id;
		}
	}

	return false;
}

/**
 * Get a subscriber.
 *
 * @param  integer|false $user_id User ID.
 * @param  string        $event   Event type.
 * @param  integer       $ref_id Reference identifier id.
 * @return null|array
 *
 * @category haveTest
 *
 * @since  4.0.0
 * @since  4.1.5 Removed default values for arguments `$event` and `$ref_id`.
 * @since  4.2.0 Fixed: warning `Required parameter $event follows optional parameter $user_id`.
 * @deprecated 5.0.0 Deprecated in favor of SubscriberModel::findMany().
 */
function ap_get_subscriber( $user_id = false, $event = '', $ref_id = '' ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'SubscriberModel::findMany()' );
	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( empty( $event ) || empty( $ref_id ) ) {
		return false;
	}

	$table = SubscriberModel::getSchema()->getTableName();

	$subscribers = SubscriberModel::findMany(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE subs_user_id = %d AND subs_ref_id = %d AND subs_event = %s LIMIT 1", // @codingStandardsIgnoreLine WordPress.DB.PreparedSQL.NotPrepared
			$user_id,
			$ref_id,
			$event
		)
	);

	if ( ! empty( $subscribers ) ) {
		return $subscribers[0];
	}

	return null;
}

/**
 * Add flag vote data to ap_votes table.
 *
 * @param integer $post_id     Post ID.
 * @param integer $user_id     User ID.
 * @return integer|boolean
 * @depreacted 5.0.0 Use `VoteService::addPostFlag()`.
 */
function ap_add_flag( $post_id, $user_id = false ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::addPostFlag()' );

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$inserted = ap_vote_insert( $post_id, $user_id, 'flag' );

	return $inserted;
}

/**
 * Count flag votes.
 *
 * @param integer $post_id Post ID.
 * @return  integer
 * @since  4.0.0
 * @deprecated 5.0.0 Use `VoteService::getPostFlagsCount()`.
 */
function ap_count_post_flags( $post_id ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::getPostFlagsCount()' );
	$rows = ap_count_votes(
		array(
			'vote_post_id' => $post_id,
			'vote_type'    => 'flag',
		)
	);

	if ( false !== $rows ) {
		return (int) $rows[0]->count;
	}

	return 0;
}

/**
 * Check if user already flagged a post.
 *
 * @param bool|integer $post Post.
 * @return bool
 * @deprecated 5.0.0 Use `VoteService::hasUserFlaggedPost()`.
 */
function ap_is_user_flagged( $post = null ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::hasUserFlaggedPost()' );
	$_post = ap_get_post( $post );

	if ( is_user_logged_in() ) {
		return ap_is_user_voted( $_post->ID, 'flag' );
	}

	return false;
}

/**
 * Flag button html.
 *
 * @param mixed $post Post.
 * @return string
 * @since 0.9
 * @deprecated 5.0.0
 */
function ap_flag_btn_args( $post = null ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	if ( ! is_user_logged_in() ) {
		return;
	}

	$_post   = ap_get_post( $post );
	$flagged = ap_is_user_flagged( $_post );

	if ( ! $flagged ) {
		$title = sprintf(
			/* Translators: %s Question or Answer post type label for flagging question or answer. */
			__( 'Flag this %s', 'anspress-question-answer' ),
			( 'question' === $_post->post_type ) ? esc_html__( 'question', 'anspress-question-answer' ) : esc_html__( 'answer', 'anspress-question-answer' )
		);
	} else {
		$title = sprintf(
			/* Translators: %s Question or Answer post type label for already flagged question or answer. */
			__( 'You have flagged this %s', 'anspress-question-answer' ),
			( 'question' === $_post->post_type ) ? esc_html__( 'question', 'anspress-question-answer' ) : esc_html__( 'answer', 'anspress-question-answer' )
		);
	}

	$actions['close'] = array(
		'cb'     => 'flag',
		'icon'   => 'apicon-check',
		'query'  => array(
			'__nonce' => wp_create_nonce( 'flag_' . $_post->ID ),
			'post_id' => $_post->ID,
		),
		'label'  => __( 'Flag', 'anspress-question-answer' ),
		'title'  => $title,
		'count'  => $_post->flags,
		'active' => $flagged,
	);

	return $actions['close'];
}

/**
 * Delete multiple posts flags.
 *
 * @param integer $post_id Post id.
 * @return boolean
 * @deprecated 5.0.0 Use `VoteService::removePostFlag()`.
 */
function ap_delete_flags( $post_id ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::removePostFlag()' );
	return ap_delete_votes( $post_id, 'flag' );
}

/**
 * Update total flagged question and answer count.
 *
 * @since 4.0.0
 * @deprecated 5.0.0 Use `VoteService::recountAndUpdateTotalFlagged()`.
 */
function ap_update_total_flags_count() {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::recountAndUpdateTotalFlagged()' );

	$opt                      = get_option( 'anspress_global', array() );
	$opt['flagged_questions'] = ap_total_posts_count( 'question', 'flag' );
	$opt['flagged_answers']   = ap_total_posts_count( 'answer', 'flag' );

	update_option( 'anspress_global', $opt );
}

/**
 * Return total flagged post count.
 *
 * @return array
 * @since 4.0.0
 * @deprecated 5.0.0 Use `VoteService::getTotalFlaggedPost()`.
 */
function ap_total_flagged_count() {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService::getTotalFlaggedPost()' );

	$opt['flagged_questions'] = ap_total_posts_count( 'question', 'flag' );
	$updated                  = true;

	$opt['flagged_answers'] = ap_total_posts_count( 'answer', 'flag' );
	$updated                = true;

	return array(
		'questions' => $opt['flagged_questions'],
		'answers'   => $opt['flagged_answers'],
	);
}

/**
 * Increment flags count.
 *
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 * @deprecated 5.0.0
 */
function ap_update_flags_count( $post_id ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );
	$count = ap_count_post_flags( $post_id );
	ap_insert_qameta( $post_id, array( 'flags' => $count ) );

	return $count;
}

/**
 * Load comment form button.
 *
 * @param   mixed $_post Post ID or object.
 * @return  string
 * @since   0.1
 * @since   4.1.0 Added @see ap_user_can_read_comments() check.
 * @since   4.1.2 Hide comments button if comments are already showing.
 * @deprecated 5.0.0
 */
function ap_comment_btn_html( $_post = null ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );
	if ( ! ap_user_can_read_comments( $_post ) ) {
		return;
	}

	$_post = ap_get_post( $_post );

	if ( 'question' === $_post->post_type && ap_opt( 'disable_comments_on_question' ) ) {
		return;
	}

	if ( 'answer' === $_post->post_type && ap_opt( 'disable_comments_on_answer' ) ) {
		return;
	}

	$comment_count = get_comments_number( $_post->ID );
	$args          = wp_json_encode(
		array(
			'post_id' => $_post->ID,
			'__nonce' => wp_create_nonce( 'comment_form_nonce' ),
		)
	);

	$unapproved = '';

	if ( ap_user_can_approve_comment() ) {
		$unapproved_count = ! empty( $_post->fields['unapproved_comments'] ) ? (int) $_post->fields['unapproved_comments'] : 0;
		$unapproved       = '<b class="unapproved' . ( $unapproved_count > 0 ? ' have' : '' ) . '" ap-un-commentscount title="' . esc_attr__( 'Comments awaiting moderation', 'anspress-question-answer' ) . '">' . $unapproved_count . '</b>';
	}

	$output = ap_new_comment_btn( $_post->ID, false );

	return $output;
}

/**
 * Comment actions args.
 *
 * @param object|integer $comment Comment object.
 * @return array
 * @since 4.0.0
 * @deprecated 5.0.0
 */
function ap_comment_actions( $comment ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	$comment = get_comment( $comment );
	$actions = array();

	if ( ap_user_can_edit_comment( $comment->comment_ID ) ) {
		$actions[] = array(
			'title' => __( 'Edit this Comment', 'anspress-question-answer' ),
			'label' => __( 'Edit', 'anspress-question-answer' ),
			'href'  => '#',
			'query' => array(
				'action'     => 'comment_modal',
				'__nonce'    => wp_create_nonce( 'edit_comment_' . $comment->comment_ID ),
				'comment_id' => $comment->comment_ID,
			),
		);
	}

	if ( ap_user_can_delete_comment( $comment->comment_ID ) ) {
		$actions[] = array(
			'title' => __( 'Delete this Comment', 'anspress-question-answer' ),
			'label' => __( 'Delete', 'anspress-question-answer' ),
			'href'  => '#',
			'query' => array(
				'__nonce'        => wp_create_nonce( 'delete_comment_' . $comment->comment_ID ),
				'ap_ajax_action' => 'delete_comment',
				'comment_id'     => $comment->comment_ID,
			),
		);
	}

	/**
	 * For filtering comment action buttons.
	 *
	 * @param array $actions Comment actions.
	 * @since   2.0.0
	 * @deprecated 5.0.0
	 */
	return apply_filters( 'ap_comment_actions', $actions );
}

/**
 * Output comment wrapper.
 *
 * @param mixed $_post Post ID or object.
 * @param array $args  Arguments.
 * @param array $single Is on single page? Default is `false`.
 *
 * @return void
 * @since 2.1
 * @since 4.1.0 Added two args `$_post` and `$args` and using WP_Comment_Query.
 * @since 4.1.1 Check if valid post and post type before loading comments.
 * @since 4.1.2 Introduced new argument `$single`.
 * @deprecated 5.0.0
 */
function ap_the_comments( $_post = null, $args = array(), $single = false ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	// If comment number is 0 then dont show on single question.
	if ( $single && ap_opt( 'comment_number' ) < 1 ) {
		return;
	}

	global $comment;

	$_post = ap_get_post( $_post );

	// Check if valid post.
	if ( ! $_post || ! in_array( $_post->post_type, array( 'question', 'answer' ), true ) ) {
		echo '<div class="ap-comment-no-perm">' . esc_attr__( 'Not a valid post ID.', 'anspress-question-answer' ) . '</div>';
		return;
	}

	if ( ! ap_user_can_read_comments( $_post ) ) {
		echo '<div class="ap-comment-no-perm">' . esc_attr__( 'Sorry, you do not have permission to read comments.', 'anspress-question-answer' ) . '</div>';

		return;
	}

	if ( 'question' === $_post->post_type && ap_opt( 'disable_comments_on_question' ) ) {
		return;
	}

	if ( 'answer' === $_post->post_type && ap_opt( 'disable_comments_on_answer' ) ) {
		return;
	}

	if ( 0 == get_comments_number( $_post->ID ) ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
		if ( ! $single ) {
			echo '<div class="ap-comment-no-perm">' . esc_attr__( 'No comments found.', 'anspress-question-answer' ) . '</div>';
		}
		return;
	}

	$user_id = get_current_user_id();
	$paged   = (int) max( 1, ap_isset_post_value( 'paged', 1 ) );

	$default = array(
		'post_id'       => $_post->ID,
		'order'         => 'ASC',
		'status'        => 'approve',
		'number'        => $single ? ap_opt( 'comment_number' ) : 99,
		'show_more'     => true,
		'no_found_rows' => false,
	);

	// Always include current user comments.
	if ( ! empty( $user_id ) && $user_id > 0 ) {
		$default['include_unapproved'] = array( $user_id );
	}

	if ( ap_user_can_approve_comment() ) {
		$default['status'] = 'all';
	}

	$args = wp_parse_args( $args, $default );
	if ( $paged > 1 ) {
		$args['offset'] = ap_opt( 'comment_number' );
	}

	$query = new WP_Comment_Query( $args );
	if ( 0 == $query->found_comments && ! $single ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
		echo '<div class="ap-comment-no-perm">' . esc_attr__( 'No comments found.', 'anspress-question-answer' ) . '</div>';
		return;
	}

	foreach ( $query->comments as $c ) {
		$comment = $c; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		ap_get_template_part( 'comment' );
	}

	echo '<div class="ap-comments-footer">';
	if ( $query->max_num_pages > 1 && $single ) {
		echo '<a class="ap-view-comments" href="#/comments/' . (int) $_post->ID . '/all">' .
		// translators: %s is total comments found.
		esc_attr( sprintf( __( 'Show %s more comments', 'anspress-question-answer' ), $query->found_comments - ap_opt( 'comment_number' ) ) ) . '</a>';
	}

	echo '</div>';
}

/**
 * A wrapper function for @see ap_the_comments() for using in
 * post templates.
 *
 * @return void
 * @since 4.1.2
 * @deprecated 5.0.0
 */
function ap_post_comments() {
	_deprecated_function( __FUNCTION__, '5.0.0' );
	echo '<apcomments id="comments-' . esc_attr( get_the_ID() ) . '" class="have-comments">';
	ap_the_comments( null, array(), true );
	echo '</apcomments>';

	// New comment button.
	echo ap_comment_btn_html( get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Return or print new comment button.
 *
 * @param integer $post_id Post id.
 * @param boolean $output  Return or echo. Default is echo.
 * @return string|void
 * @since 4.1.8
 * @deprecated 5.0.0
 */
function ap_new_comment_btn( $post_id, $output = true ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	if ( ap_user_can_comment( $post_id ) ) {
		$html = '';

		$btn_args = wp_json_encode(
			array(
				'action'  => 'comment_modal',
				'post_id' => $post_id,
				'__nonce' => wp_create_nonce( 'new_comment_' . $post_id ),
			)
		);

		$html .= '<a href="#" class="ap-btn-newcomment" aponce="false" apajaxbtn apquery="' . esc_js( $btn_args ) . '">';
		$html .= esc_attr__( 'Add a Comment', 'anspress-question-answer' );
		$html .= '</a>';

		if ( false === $output ) {
			return $html;
		}

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

/**
 * Generate comment form.
 *
 * @param  false|integer $post_id  Question or answer id.
 * @param  false|object  $_comment Comment id or object.
 * @return void
 *
 * @since 4.1.0
 * @since 4.1.5 Don't use ap_ajax.
 * @deprecated 5.0.0
 */
function ap_comment_form( $post_id = false, $_comment = false ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	if ( ! ap_user_can_comment( $post_id ) ) {
		return;
	}

	$args = array(
		'hidden_fields' => array(
			array(
				'name'  => 'post_id',
				'value' => $post_id,
			),
			array(
				'name'  => 'action',
				'value' => 'ap_form_comment',
			),
		),
	);

	$form = anspress()->get_form( 'comment' );

	// Add value when editing post.
	if ( false !== $_comment && ! empty( $_comment ) ) {
		$_comment = get_comment( $_comment );
		$values   = array();

		$args['hidden_fields'][] = array(
			'name'  => 'comment_id',
			'value' => $_comment->comment_ID,
		);

		$values['content'] = $_comment->comment_content;

		if ( empty( $_comment->user_id ) ) {
			$values['author'] = $_comment->comment_author;
			$values['email']  = $_comment->comment_author_email;
			$values['url']    = $_comment->comment_author_url;
		}

		$form->set_values( $values );
	}

	$form->generate( $args );
}

/**
 * Return description of a post status.
 *
 * @param  boolean|integer $post_id Post ID.
 * @deprecated 5.0.0
 */
function ap_post_status_badge( $post_id = false ) {
	$ret = '<postmessage>';
	$msg = ap_get_post_status_message( $post_id );

	if ( ! empty( $msg ) ) {
		$ret .= $msg;
	}

	$ret .= '</postmessage>';

	return $ret;
}

/**
 * Check if user can comment on AnsPress posts.
 *
 * @param boolean|integer $post_id Post ID.
 * @param boolean|integer $user_id User ID.
 * @return boolean
 * @since 2.4.6 Added two arguments `$post_id` and `$user_id`. Also check if user can read post.
 * @since 2.4.6 Added filter ap_user_can_comment.
 * @deprecated 5.0.0
 */
function ap_user_can_comment( $post_id = false, $user_id = false ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	if ( false === $post_id ) {
		$post_id = get_the_ID();
	}

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( is_super_admin( $user_id ) ) {
		return true;
	}

	/**
	 * Filter to hijack ap_user_can_comment.
	 *
	 * @param  boolean|string   $apply_filter   Apply current filter, empty string by default.
	 * @param  integer|object   $post_id        Post ID or object.
	 * @param  integer          $user_id        User ID.
	 * @return boolean
	 * @since  2.4.6
	 */
	$filter = apply_filters( 'ap_user_can_comment', '', $post_id, $user_id );

	if ( true === $filter ) {
		return true;
	} elseif ( false === $filter ) {
		return false;
	}

	$post_o = ap_get_post( $post_id );

	// Do not allow to comment if post is moderate.
	if ( 'moderate' === $post_o->post_status ) {
		return false;
	}

	// Don't allow user to comment if they don't have permission to read post.
	if ( ! ap_user_can_read_post( $post_id, $user_id ) ) {
		return false;
	}

	$option = ap_opt( 'post_comment_per' );
	if ( 'have_cap' === $option && user_can( $user_id, 'ap_new_comment' ) ) {
		return true;
	} elseif ( 'logged_in' === $option && is_user_logged_in() ) {
		return true;
	} elseif ( 'anyone' === $option ) {
		return true;
	}

	return false;
}

/**
 * Get a subscribers count of a reference by specific event or without it.
 *
 * @param  string  $event   Event type.
 * @param  integer $ref_id  Reference identifier id.
 * @return null|array
 *
 * @category haveTest
 *
 * @since  4.0.0
 * @since  4.1.5 When `$event` is empty and `$ref_id` is 0 then get total subscribers of site.
 * @deprecated 5.0.0
 */
function ap_subscribers_count( $event = '', $ref_id = 0 ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	global $wpdb;

	$ref_query = '';

	if ( $ref_id > 0 ) {
		$ref_query = $wpdb->prepare( ' AND subs_ref_id = %d', $ref_id );
	}

	$event_query = '';

	if ( ! empty( $event ) ) {
		$event_query = $wpdb->prepare( ' AND subs_event = %s', $event );
	}

	$results = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->ap_subscribers} WHERE 1=1 {$event_query} {$ref_query}" ); //phpcs:ignore WordPress.DB

	return $results;
}

/**
 * Get subscribers. Total subscribers count will be returned
 * if no argument is passed.
 *
 * @param  array $where {
 *          Where clauses.
 *
 *          @type string  $subs_event   Event type.
 *          @type integer $subs_ref_id  Reference id.
 *          @type integer $subs_user_id User id.
 * }
 * @param  null  $event  Deprecated.
 * @param  null  $ref_id Deprecated.
 *
 * @return null|array
 *
 * @category haveTest
 *
 * @since  4.0.0
 * @since  4.1.5 Deprecated arguments `$event` and `$ref_id`. Added new argument `$where`.
 * @deprecated 5.0.0
 */
function ap_get_subscribers( $where = array(), $event = null, $ref_id = null ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	if ( null !== $event || null !== $ref_id ) {
		_deprecated_argument( __FUNCTION__, '4.1.5', esc_attr__( 'All 2 arguments $event and $ref_id are deprecated.', 'anspress-question-answer' ) );
	}

	global $wpdb;

	$where = wp_parse_args(
		$where,
		array(
			'subs_event'   => '',
			'subs_ref_id'  => '',
			'subs_user_id' => '',
		)
	);

	$where = wp_array_slice_assoc( $where, array( 'subs_event', 'subs_ref_id', 'subs_user_id' ) );

	// Return if where clauses are empty.
	if ( empty( $where ) ) {
		return;
	}

	$query = '';

	if ( isset( $where['subs_ref_id'] ) && $where['subs_ref_id'] > 0 ) {
		$query .= $wpdb->prepare( ' AND s.subs_ref_id = %d', $where['subs_ref_id'] );
	}

	if ( ! empty( $where['subs_event'] ) ) {
		$query .= $wpdb->prepare( ' AND s.subs_event = %s', $where['subs_event'] );
	}

	if ( ! empty( $where['subs_user_id'] ) ) {
		$query .= $wpdb->prepare( ' AND s.subs_user_id = %s', $where['subs_user_id'] );
	}

	$results = $wpdb->get_results( "SELECT * FROM {$wpdb->ap_subscribers} s LEFT JOIN {$wpdb->users} u ON u.ID = s.subs_user_id WHERE 1=1 {$query}" ); // phpcs:ignore WordPress.DB

	return $results;
}

/**
 * Delete subscribers by event, ref_id and user_id.
 *
 * This is not a recommended function to delete subscriber as this
 * function does not properly handles hooks. Instead use @see ap_delete_subscriber().
 *
 * @param array   $where {
 *          Where clauses.
 *
 *          @type string  $subs_event   Event type.
 *          @type integer $subs_ref_id  Reference id.
 *          @type integer $subs_user_id User id.
 * }
 * @param string  $event   Deprecated.
 * @param integer $ref_id  Deprecated.
 * @param integer $user_id Deprecated.
 *
 * @return bool|integer|null
 *
 * @category haveTest
 *
 * @since 4.0.0 Introduced
 * @since 4.1.5 Deprecated arguments `$event`, `$ref_id` and `$user_id`. Added new arguments `$where`.
 * @deprecated 5.0.0
 */
function ap_delete_subscribers( $where, $event = null, $ref_id = null, $user_id = null ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	if ( null !== $event || null !== $ref_id || null !== $user_id ) {
		_deprecated_argument( __FUNCTION__, '4.1.5', esc_attr__( 'All 3 arguments $event, $ref_id and $user_id are deprecated.', 'anspress-question-answer' ) );
	}

	global $wpdb;

	$where = wp_array_slice_assoc( $where, array( 'subs_event', 'subs_ref_id', 'subs_user_id' ) );

	// Return if where clauses are empty.
	if ( empty( $where ) ) {
		return;
	}

	/**
	 * Action triggered right after deleting subscribers.
	 *
	 * @param string  $where   $where {
	 *          Where clauses.
	 *
	 *          @type string  $subs_event   Event type.
	 *          @type integer $subs_ref_id  Reference id.
	 *          @type integer $subs_user_id User id.
	 * }
	 *
	 * @category haveTest
	 *
	 * @since 4.1.5
	 */
	do_action( 'ap_before_delete_subscribers', $where );

	$rows = $wpdb->delete( $wpdb->ap_subscribers, $where ); // phpcs:ignore WordPress.DB

	if ( false !== $rows ) {
		$ref_id = isset( $where['subs_ref_id'] ) ? $where['subs_ref_id'] : 0;
		$event  = isset( $where['subs_event'] ) ? $where['subs_event'] : '';

		/**
		 * Action triggered right after deleting subscribers.
		 *
		 * @param integer $rows    Number of rows deleted.
		 * @param string  $where   $where {
		 *          Where clauses.
		 *
		 *          @type string  $subs_event   Event type.
		 *          @type integer $subs_ref_id  Reference id.
		 *          @type integer $subs_user_id User id.
		 * }
		 *
		 * @since 4.0.0
		 */
		do_action( 'ap_delete_subscribers', $rows, $where );
	}

	return $rows;
}

/**
 * Delete a single subscriber.
 *
 * This is a preferred function for deleting a subscriber. Avoid using
 * function @see ap_delete_subscribers().
 *
 * @param integer $ref_id  Reference id.
 * @param integer $user_id User id.
 * @param string  $event   Event type.
 *
 * @return boolean Return true on success.
 *
 * @category haveTest
 *
 * @since 4.1.5
 * @deprecated 5.0.0
 */
function ap_delete_subscriber( $ref_id, $user_id, $event ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	global $wpdb;

	$rows = $wpdb->delete( // phpcs:ignore WordPress.DB
		$wpdb->ap_subscribers,
		array(
			'subs_ref_id'  => $ref_id,
			'subs_user_id' => $user_id,
			'subs_event'   => $event,
		),
		array( '%d', '%d', '%s' )
	);

	if ( false !== $rows ) {
		/**
		 * Action triggered right after deleting a single subscriber.
		 *
		 * @param integer $ref_id  Reference id.
		 * @param integer $user_id User id.
		 * @param string  $event   Event type.
		 */
		do_action( 'ap_delete_subscriber', $ref_id, $user_id, $event );

		return true;
	}

	return false;
}

/**
 * Output question subscribe button.
 *
 * @param object|integer|false $_post Post object or ID.
 * @param boolean              $output Echo or return.
 * @return string|null
 * @since 4.0.0
 * @deprecated 5.0.0
 */
function ap_subscribe_btn( $_post = false, $output = true ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );
	$_post = ap_get_post( $_post );

	$args        = wp_json_encode(
		array(
			'__nonce' => wp_create_nonce( 'subscribe_' . $_post->ID ),
			'id'      => $_post->ID,
		)
	);
	$subscribers = (int) ap_get_post_field( 'subscribers', $_post );
	$subscribed  = ap_is_user_subscriber( 'question', $_post->ID );
	$label       = $subscribed ? __( 'Unsubscribe', 'anspress-question-answer' ) : __( 'Subscribe', 'anspress-question-answer' );

	$html = '<a href="#" class="ap-btn ap-btn-subscribe ap-btn-small ' . ( $subscribed ? 'active' : '' ) . '" apsubscribe apquery="' . esc_js( $args ) . '"><span class="apsubscribers-title">' . esc_html( $label ) . '</span><span class="apsubscribers-count">' . esc_html( $subscribers ) . '</span></a>';

	if ( ! $output ) {
		return $html;
	}

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Check if user is subscribed to a reference event.
 *
 * @param string  $event Event type.
 * @param integer $ref_id Reference id.
 * @param integer $user_id User ID.
 * @return bool
 *
 * @since 4.0.0
 * @deprecated 5.0.0
 */
function ap_is_user_subscriber( $event, $ref_id, $user_id = false ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$exists = ap_get_subscriber( $user_id, $event, $ref_id );

	if ( $exists ) {
		return true;
	}

	return false;
}

/**
 * Return escaped subscriber event name. It basically removes
 * id suffixed in event name and only name.
 *
 * @param string $event Event name.
 * @return string
 * @since 4.1.5
 * @deprecated 5.0.0
 */
function ap_esc_subscriber_event( $event ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );
	return false !== strpos( $event, '_' ) ? substr( $event, 0, strpos( $event, '_' ) ) : $event;
}

/**
 * Parse subscriber event name to get event id.
 *
 * @param string $event Event name. i.e. `answer_2334`.
 * @return integer
 * @since 4.1.5
 * @deprecated 5.0.0
 */
function ap_esc_subscriber_event_id( $event ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );
	return (int) substr( $event, strpos( $event, '_' ) + 1 );
}

/**
 * Output or return voting button.
 *
 * @todo Add output sanitization.
 * @param   int|object $post   Post ID or object.
 * @param   bool       $output Echo or return vote button.
 * @return  null|string
 * @since 0.1
 * @deprecated 5.0.0
 */
function ap_vote_btn( $post = null, $output = true ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	$post = ap_get_post( $post );
	if ( ! $post || ( 'answer' === $post->post_type && ap_opt( 'disable_voting_on_answer' ) ) ) {
		return;
	}

	if ( 'question' === $post->post_type && ap_opt( 'disable_voting_on_question' ) ) {
		return;
	}

	$vote  = is_user_logged_in() ? ap_get_vote( $post->ID, get_current_user_id(), 'vote' ) : false;
	$voted = $vote ? true : false;

	if ( $vote && '1' === $vote->vote_value ) {
		$type = 'vote_up';
	} elseif ( $vote && '-1' === $vote->vote_value ) {
		$type = 'vote_down';
	} else {
		$type = '';
	}

	$data = array(
		'post_id' => $post->ID,
		'active'  => $type,
		'net'     => ap_get_votes_net(),
		'__nonce' => wp_create_nonce( 'vote_' . $post->ID ),
	);

	$upvote_message = sprintf(
		/* Translators: %s Question or Answer post type label for up voting the question or answer. */
		__( 'Up vote this %s', 'anspress-question-answer' ),
		( 'question' === $post->post_type ) ? esc_html__( 'question', 'anspress-question-answer' ) : esc_html__( 'answer', 'anspress-question-answer' )
	);
	$downvote_message = sprintf(
		/* Translators: %s Question or Answer post type label for down voting the question or answer. */
		__( 'Down vote this %s', 'anspress-question-answer' ),
		( 'question' === $post->post_type ) ? esc_html__( 'question', 'anspress-question-answer' ) : esc_html__( 'answer', 'anspress-question-answer' )
	);

	$html = '';

	$html .= '<div id="vote_' . $post->ID . '" class="ap-vote net-vote" ap-vote="' .
		esc_js( wp_json_encode( $data ) ) . '">' .
		'<a class="apicon-thumb-up ap-tip vote-up' . ( $voted ? ' voted' : '' ) .
		( $vote && 'vote_down' === $type ? ' disable' : '' ) . '" href="#" title="' .
		( $vote && 'vote_down' === $type ?
			__( 'You have already voted', 'anspress-question-answer' ) :
			( $voted ? __( 'Withdraw your vote', 'anspress-question-answer' ) : $upvote_message ) ) .
		'" ap="vote_up"></a>' .
		'<span class="net-vote-count" data-view="ap-net-vote" itemprop="upvoteCount" ap="votes_net">' .
		ap_get_votes_net() . '</span>';

	$html .= '</div>';

	/**
	 * Allows overriding voting button HTML upload.
	 *
	 * @param string  $html Vote button.
	 * @param WP_Post $post WordPress post object.
	 * @since 4.1.5
	 */
	$html = apply_filters( 'ap_vote_btn_html', $html, $post );

	if ( ! $output ) {
		return $html;
	}

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Delete post vote and update qameta votes count.
 *
 * @param  integer         $post_id    Post ID.
 * @param  boolean|integer $user_id    User ID.
 * @param  boolean|string  $up_vote    Is up vote.
 * @return boolean|integer
 * @deprecated 5.0.0
 */
function ap_delete_post_vote( $post_id, $user_id = false, $up_vote = null ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );
	$value = false;

	if ( null !== $up_vote ) {
		$value = $up_vote ? '1' : '-1';
	}

	$type = $up_vote ? 'vote_up' : 'vote_down';
	$rows = ap_delete_vote( $post_id, $user_id, 'vote', $value );

	if ( false !== $rows ) {
		$counts = ap_update_votes_count( $post_id );
		do_action( 'ap_undo_vote', $post_id, $counts );
		do_action( 'ap_undo_' . $type, $post_id, $counts );

		return $counts;
	}
	return false;
}

/**
 * Insert vote in ap_votes table.
 *
 * @param  integer       $post_id Post ID.
 * @param  integer       $user_id ID of user casting voting.
 * @param  string        $type Type of vote.
 * @param  integer|false $rec_user_id Id of user receiving vote.
 * @param  string        $value Value of vote.
 * @param  string|false  $date Date of vote, default is current time.
 * @return boolean
 * @since  4.0.0
 * @deprecated 5.0.0 Use VoteService instead.
 */
function ap_vote_insert( $post_id, $user_id, $type = 'vote', $rec_user_id = 0, $value = '', $date = false ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService' );

	if ( false === $date ) {
		$date = current_time( 'mysql' );
	}

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;
	$args = array(
		'vote_post_id'  => $post_id,
		'vote_user_id'  => $user_id,
		'vote_rec_user' => $rec_user_id,
		'vote_type'     => $type,
		'vote_value'    => $value,
		'vote_date'     => $date,
	);

	$inserted = $wpdb->insert( $wpdb->ap_votes, $args, array( '%d', '%d', '%d', '%s', '%s', '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

	if ( $inserted ) {
		/**
		 * Action triggered after inserting a vote.
		 *
		 * @param array $args Vote arguments.
		 * @since  4.0.0
		 */
		do_action( 'ap_insert_vote', $args );

		return true;
	}

	return false;
}

/**
 * Add post vote.
 *
 * @param  integer         $post_id Post ID.
 * @param  boolean|integer $user_id ID of user casting vote.
 * @param  string          $up_vote Is up vote.
 * @return boolean
 * @since  4.0.0
 * @deprecated 5.0.0 Use VoteService instead.
 */
function ap_add_post_vote( $post_id, $user_id = 0, $up_vote = true ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	Plugin::get( VoteService::class )->addPostVote( $post_id, $up_vote, $user_id );

	$counts = ap_update_votes_count( $post_id );

	return $counts;
}

/**
 * Delete multiple post votes.
 *
 * @param integer $post_id Post id.
 * @param string  $type Vote type.
 * @return boolean
 * @deprecated 5.0.0 Use VoteService instead.
 */
function ap_delete_votes( $post_id, $type = 'vote' ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );
	global $wpdb;
	$where = array(
		'vote_post_id' => $post_id,
		'vote_type'    => $type,
	);

	$rows = $wpdb->delete( $wpdb->ap_votes, $where ); // phpcs:ignore WordPress.DB

	if ( false !== $rows ) {
		do_action( 'ap_deleted_votes', $post_id, $type );
		return true;
	}

	return false;
}

/**
 * Delete vote from database.
 *
 * @param  integer         $post_id Post ID.
 * @param  integer|boolean $user_id User ID.
 * @param  string|array    $type    Vote type.
 * @param  string          $value   Vote value.
 * @return boolean
 * @since  4.0.0
 * @deprecated 5.0.0 Use VoteService instead.
 */
function ap_delete_vote( $post_id, $user_id = false, $type = 'vote', $value = false ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService' );

	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$where = array(
		'vote_post_id' => $post_id,
		'vote_user_id' => $user_id,
		'vote_type'    => $type,
	);

	if ( false !== $value ) {
		$where['vote_value'] = $value;
	}

	$row = $wpdb->delete( $wpdb->ap_votes, $where ); // phpcs:ignore WordPress.DB

	if ( false !== $row ) {
		do_action( 'ap_delete_vote', $post_id, $user_id, $type, $value );
	}

	return $row;
}


/**
 * Check if user vote on a post.
 *
 * @param  integer      $post_id Post ID.
 * @param  string|array $type    Vote type.
 * @param  integer      $user_id User ID.
 * @return boolean
 * @since  4.0.0
 * @uses   ap_get_vote
 * @deprecated 5.0.0 Use VoteService instead.
 */
function ap_is_user_voted( $post_id, $type = 'vote', $user_id = false ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService' );
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( false === ap_get_vote( $post_id, $user_id, $type ) ) {
		return false;
	}

	return true;
}

/**
 * Return votes.
 *
 * @param  array|integer $args Arguments or vote_post_id.
 * @return array|boolean
 * @since  4.0.0
 * @deprecated 5.0.0 Use VoteModel instead.
 */
function ap_get_votes( $args = array() ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteModel' );

	if ( ! is_array( $args ) ) {
		$args = array( 'vote_post_id' => (int) $args );
	}

	global $wpdb;
	$where = "SELECT * FROM {$wpdb->ap_votes} WHERE 1=1";

	// Single or multiple posts.
	if ( isset( $args['vote_post_id'] ) && ! empty( $args['vote_post_id'] ) ) {
		if ( is_array( $args['vote_post_id'] ) ) {
			$in_str = sanitize_comma_delimited( $args['vote_post_id'] );

			if ( ! empty( $in_str ) ) {
				$where .= ' AND vote_post_id IN (' . $in_str . ')';
			}
		} else {
			$where .= ' AND vote_post_id = ' . (int) $args['vote_post_id'];
		}
	}

	// Single or multiple users.
	if ( isset( $args['vote_user_id'] ) && ! empty( $args['vote_user_id'] ) ) {
		if ( is_array( $args['vote_user_id'] ) ) {
			$in_str = sanitize_comma_delimited( $args['vote_user_id'] );
			if ( ! empty( $in_str ) ) {
				$where .= ' AND vote_user_id IN (' . $in_str . ')';
			}
		} else {
			$where .= ' AND vote_user_id = ' . (int) $args['vote_user_id'];
		}
	}

	// Vote actors.
	if ( isset( $args['vote_actor_id'] ) && ! empty( $args['vote_actor_id'] ) ) {
		if ( is_array( $args['vote_actor_id'] ) ) {
			$in_str = sanitize_comma_delimited( $args['vote_actor_id'] );

			if ( ! empty( $in_str ) ) {
				$where .= ' AND vote_actor_id IN (' . $in_str . ')';
			}
		} else {
			$where .= ' AND vote_actor_id = ' . (int) $args['vote_actor_id'];
		}
	}

	// Single or multiple vote types.
	if ( isset( $args['vote_type'] ) && ! empty( $args['vote_type'] ) ) {
		if ( is_array( $args['vote_type'] ) ) {
			$vote_type_in = sanitize_comma_delimited( $args['vote_type'], 'str' );

			if ( ! empty( $vote_type_in ) ) {
				$where .= ' AND vote_type IN (' . $vote_type_in . ')';
			}
		} else {
			$where .= $wpdb->prepare( ' AND vote_type = %s', $args['vote_type'] );
		}
	}

	$results = $wpdb->get_results( $where ); // phpcs:ignore WordPress.DB

	return ! is_array( $results ) ? array() : $results;
}

/**
 * Get votes count.
 *
 * @param  array $args {
 *              Arguments.
 *
 *              @type int $vote_post_id Post id.
 *              @type string|array $vote_type Vote type.
 *              @type int $vote_user_id User id,
 *              @type string $vote_date Vote date.
 *        }
 * @return array|boolean
 * @deprecated 5.0.0 Use VoteController instead.
 */
function ap_count_votes( $args ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteController' );
	global $wpdb;
	$args  = wp_parse_args( $args, array( 'group' => false ) );
	$where = 'SELECT count(*) as count';

	if ( $args['group'] ) {
		$where .= ', ' . esc_sql( sanitize_text_field( $args['group'] ) );
	}

	$where .= " FROM {$wpdb->ap_votes} WHERE 1=1 ";

	if ( isset( $args['vote_post_id'] ) ) {
		$where .= 'AND vote_post_id = ' . (int) $args['vote_post_id'];
	}

	if ( isset( $args['vote_type'] ) ) {
		if ( is_array( $args['vote_type'] ) ) {
			$in_str = sanitize_comma_delimited( $args['vote_type'], 'str' );

			if ( ! empty( $in_str ) ) {
				$where .= ' AND vote_type IN (' . $in_str . ')';
			}
		} else {
			$where .= $wpdb->prepare( ' AND vote_type = %s', $args['vote_type'] );
		}
	}

	// Vote user id.
	if ( isset( $args['vote_user_id'] ) ) {
		if ( is_array( $args['vote_user_id'] ) ) {
			$vote_user_in = sanitize_comma_delimited( $args['vote_user_id'] );
			if ( ! empty( $vote_user_in ) ) {
				$where .= ' AND vote_user_id IN (' . $vote_user_in . ')';
			}
		} else {
			$where .= " AND vote_user_id = '" . (int) $args['vote_user_id'] . "'";
		}
	}

	// Vote actor id.
	if ( isset( $args['vote_actor_id'] ) ) {
		if ( is_array( $args['vote_actor_id'] ) ) {
			$vote_actor_id_in = sanitize_comma_delimited( $args['vote_actor_id'] );
			if ( ! empty( $vote_actor_id_in ) ) {
				$where .= ' AND vote_actor_id IN (' . $vote_actor_id_in . ')';
			}
		} else {
			$where .= $wpdb->prepare( ' AND vote_actor_id = %d', $args['vote_actor_id'] );
		}
	}

	// Vote value.
	if ( isset( $args['vote_value'] ) ) {
		if ( is_array( $args['vote_value'] ) ) {
			$vote_value_in = sanitize_comma_delimited( $args['vote_value'], 'str' );
			if ( ! empty( $vote_value_in ) ) {
				$where .= ' AND vote_value IN (' . $vote_value_in . ')';
			}
		} else {
			$where .= $wpdb->prepare( ' AND vote_value = %s', $args['vote_value'] );
		}
	}

	if ( $args['group'] ) {
		$where .= ' GROUP BY ' . esc_sql( sanitize_text_field( $args['group'] ) );
	}

	$rows = $wpdb->get_results( $where ); // phpcs:ignore WordPress.DB

	if ( false !== $rows ) {
		return $rows;
	}

	return false;
}

/**
 * Count votes of a post and property format.
 *
 * @param  string $by By.
 * @param  string $value Value.
 * @return array
 * @since  4.0.0
 * @uses   ap_count_votes
 * @deprecated 5.0.0 Use VoteService instead.
 */
function ap_count_post_votes_by( $by, $value ) {
	_deprecated_function( __FUNCTION__, '5.0.0', 'VoteService' );

	$bys = array( 'post_id', 'user_id', 'actor_id' );

	if ( ! in_array( $by, $bys, true ) ) {
		return false;
	}

	$new_counts = array(
		'votes_net'  => 0,
		'votes_down' => 0,
		'votes_up'   => 0,
	);
	$args       = array(
		'vote_type' => 'vote',
		'group'     => 'vote_value',
	);

	if ( 'post_id' === $by ) {
		$args['vote_post_id'] = $value;
	} elseif ( 'user_id' === $by ) {
		$args['vote_user_id'] = $value;
	} elseif ( 'actor_id' === $by ) {
		$args['vote_actor_id'] = $value;
	}

	$rows = ap_count_votes( $args );

	if ( false !== $rows ) {
		foreach ( (array) $rows as $row ) {
			if ( is_object( $row ) && isset( $row->count ) ) {
				$type                = '-1' == $row->vote_value ? 'votes_down' : 'votes_up'; // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				$new_counts[ $type ] = (int) $row->count;
			}
		}
		$new_counts['votes_net'] = $new_counts['votes_up'] - $new_counts['votes_down'];
	}

	return $new_counts;
}

/**
 * Get a single vote from database.
 *
 * @param  integer      $post_id Post ID.
 * @param  integer      $user_id User ID.
 * @param  string|array $type    Vote type.
 * @param  string       $value   Vote value.
 * @return boolean|object
 * @since  4.0.0
 * @deprecated 5.0.0
 */
function ap_get_vote( $post_id, $user_id, $type, $value = '' ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );
	return false;
}
