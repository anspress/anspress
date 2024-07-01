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

use AnsPress\Modules\Subscriber\SubscriberModel;

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
