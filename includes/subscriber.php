<?php
/**
 * AnsPress subscribe and subscriber related functions
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * Insert new subscriber into database.
 * @param  int    $user_id        User id.
 * @param  int    $item_id        Item id i.e. post ID, term ID etc..
 * @param  string $activity       Activity name.
 * @param  int    $question_id    Question ID.
 * @param  int    $answer_id      Answer ID.
 * @return false|integer
 * @since  unknown
 * @since  3.0.0  Added `$answer_id` args.
 */
function ap_new_subscriber( $user_id, $item_id, $activity, $question_id = 0, $answer_id = 0 ) {
	// Check if subscriber table need update.
	if ( ap_db_version_is_lower() ) {
		ap_db_subscriber_answer_id_col();
		ap_opt( 'db_version', AP_DB_VERSION );
	}

	global $wpdb;

	// Bail if user_id or item_id is 0.
	if ( 0 == $user_id || 0 == $item_id ) {
		return false;
	}

	$row = $wpdb->insert(
		$wpdb->ap_subscribers,
		array(
			'subs_user_id' 		=> $user_id,
			'subs_question_id' 	=> $question_id,
			'subs_item_id' 		=> $item_id,
			'subs_activity' 	=> $activity,
			'subs_answer_id' 	=> $answer_id,
		),
		array(
			'%d',
			'%d',
			'%d',
			'%s',
			'%d',
		)
	);

	if ( false !== $row ) {
		/**
		 * Action trigged right after inserting new subscriber in DB.
		 * @param int 		$user_id 		User ID.
		 * @param int 		$question_id 	Question ID.
		 * @param int 		$item_id 		Subscribed item ID.
		 * @param string 	$activity 		Activity type.
		 * @param int 		$answer_id 		Answer ID.
		 * @since unknown
		 * @since 3.0.0  Added `$answer_id` args.
		 */
		do_action( 'ap_new_subscriber', $user_id, $question_id, $item_id, $activity, $answer_id );
		return $wpdb->insert_id;
	}

	return $row;
}


/**
 * Remove subscriber for question or term
 * @param  integer         $item_id  	Question ID or Term ID
 * @param  integer         $user_id    	WP user ID
 * @param  string          $activity    Any sub ID
 * @param  boolean|integer $sub_id      @deprecated Type of subscriber, empty string for question
 * @param  int|false       $question_id    Question id.
 * @param  int|false       $answer_id      Answer id.
 * @return bollean|integer
 */
function ap_remove_subscriber($item_id, $user_id = false, $activity = false, $sub_id = false, $question_id = false, $answer_id = false) {
	if ( false !== $sub_id ) {
		_deprecated_argument( __FUNCTION__, '3.0', '$sub_id argument deprecated since 2.4' );
	}

	global $wpdb;

	$cols = array( 'subs_item_id' => (int) $item_id );

	$data_type = array( '%d' );

	if ( false !== $user_id ) {
		$cols['subs_user_id'] = (int) $user_id;
		$data_type[] = '%d';
	}

	if ( false !== $activity ) {
		$cols['subs_activity'] = sanitize_title_for_query( $activity );
		$data_type[] = '%s';
	}

	if ( false !== $question_id ) {
		$cols['subs_question_id'] = (int) $question_id;
		$data_type[] = '%d';
	}

	if ( false !== $answer_id ) {
		$cols['subs_answer_id'] = (int) $answer_id;
		$data_type[] = '%d';
	}

	$row = $wpdb->delete(
		$wpdb->ap_subscribers,
		$cols,
		$data_type
	);

	if ( false === $row ) {
		return false;
	}

	/**
	 * Action trigged right after removing subscriber from database.
	 * @param int 			$user_id 		User ID.
	 * @param int 			$item_id 		Item id.
	 * @param string|false 	$activity 		Activity type.
	 * @param int|false 	$question_id 	Question id.
	 * @param int|false		$answer_id 		Answer id.
	 * @since unknown
	 * @since 3.0.0 Added two arguments `$question_id` and `$answer_id`.
	 */
	do_action( 'ap_removed_subscriber', $user_id, $item_id, $activity, $question_id, $answer_id );

	return $row;
}

/**
 * Check if user is subscribed to question or term
 * @param  integer        $item_id 		Item id.
 * @param  integer        $activity 	Activity name.
 * @param  string|boolean $user_id 		User id.
 * @return boolean
 */
function ap_is_user_subscribed($item_id, $activity, $user_id = false) {

	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( $user_id === false ) {
		$user_id = get_current_user_id();
	}

	global $wpdb;

	$key = $item_id .'::'. $activity .'::'. $user_id;

	$cache = wp_cache_get( $key, 'ap_subscriber_count' );

	if ( false !== $cache ) {
		return $cache > 0;
	}

	$count = $wpdb->get_var( $wpdb->prepare( 'SELECT count(*) FROM '. $wpdb->ap_subscribers .' WHERE subs_item_id=%d AND subs_activity="%s" AND subs_user_id = %d', $item_id, $activity, $user_id ) );

	wp_cache_set( $key, $count, 'ap_subscriber_count' );

	return $count > 0;
}

/**
 * Return the count of subscribers for question or term
 * @param  integer $item_id 	Item id.
 * @param  string  $activity 	Type of subscription.
 * @return integer
 */
function ap_subscribers_count($item_id = false, $activity = 'q_all') {
	global $wpdb;

	$item_id = $item_id ? $item_id : get_question_id();

	$key = $item_id.'_'.$activity;

	$cache = wp_cache_get( $key, 'ap_subscriber_count' );

	if ( false !== $cache ) {
		return $cache;
	}

	$count = $wpdb->get_var( $wpdb->prepare( 'SELECT count(*) FROM '. $wpdb->ap_subscribers .' WHERE subs_item_id=%d AND subs_activity="%s"', $item_id, $activity ) );

	wp_cache_set( $key, $count, 'ap_subscriber_count' );

	if ( ! $count ) {
		return 0;
	}

	return $count;
}

/**
 * Get question subscribers count from post meta.
 * @param  intgere|object $question Question object.
 * @return integer
 */
function ap_question_subscriber_count( $question ) {
	if ( ! is_object( $question ) || ! isset( $question->post_type ) ) {
		$question = get_post( $question );
	}

	// Return if not question.
	if ( 'question' != $question->post_type ) {
		return 0;
	}

	return (int) get_post_meta( $question->ID, ANSPRESS_SUBSCRIBER_META, true );
}

/**
 * Return subscriber count in human readable format
 * @return string
 * @since 2.0.0-alpha2
 */
function ap_subscriber_count_html($post = false) {

	if ( ! $post ) {
		global $post;
	}

	$subscribed = ap_is_user_subscribed( $post->ID, 'q_all' );
	$total_subscribers = ap_subscribers_count( $post->ID );

	if ( $total_subscribers == '1' && $subscribed ) {
		return __( 'Only you are subscribed to this question.', 'anspress-question-answer' ); } elseif ($subscribed)
		return sprintf( __( 'You and <strong>%s people</strong> subscribed to this question.', 'anspress-question-answer' ), ($total_subscribers -1) );
	elseif ($total_subscribers == 0)
		return __( 'No one is subscribed to this question.', 'anspress-question-answer' );
	else {
		return sprintf( __( '<strong>%d people</strong> subscribed to this question.', 'anspress-question-answer' ), $total_subscribers ); }
}

/**
 * Return all subscribers of a question
 * @param  integer $action_id  Item id.
 * @param  string  $activity   Subscribe activity.
 * @return array
 * @since  2.1
 */
function ap_get_subscribers( $action_id, $activity = 'q_all', $limit = 10, $user_info = '' ) {
	global $wpdb;

	$i = 1;
	if ( is_array( $activity ) && count( $activity ) > 0 ) {
		$activity_q .= ' subs_activity IN(';

		foreach ( $activity as $a ) {
			$activity_q .= '"'. sanitize_title_for_query( $a ) .'"';
			if ( $i != count( $activity ) ) {
				$activity_q .= ', ';
			}
			$i++;
		}

		$activity_q .= ') ';
	} else {
		$activity_q = ' subs_activity = "'. sanitize_title_for_query( $activity ) .'"';
	}

	$key = $action_id.'_'.(is_array($activity ) ? implode(':', $activity ) : $activity);

	$cache = wp_cache_get( $key, 'ap_subscribers' );

	if ( false !== $cache ) {
		return $cache;
	}

	if ( true === $user_info ) {
		$user_info = "JOIN {$wpdb->prefix}users on subs_user_id = ID";
	}

	$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.$wpdb->ap_subscribers." {$user_info} where subs_item_id=%d AND $activity_q LIMIT 0 , %d", $action_id, $limit ) );

	// Set individual cache for subscriber.
	if ( $results ) {
		foreach ( $results as $s ) {
			$s_key = $s->subs_item_id .'_'. $s->subs_activity .'_'. $s->subs_user_id;
			$old_cache = wp_cache_get( $s_key, 'ap_subscribers' );

			if ( false !== $old_cache ) {
				wp_cache_set( $s_key, $s, 'ap_subscribers' );
			}
		}
	}

	return $results;
}


/**
 * Output subscribe btn HTML
 * @param 	boolean|integer $action_id  Question ID or Term ID.
 * @param 	string|false    $type       Subscribe type.
 * @since 	2.0.1
 */
function ap_subscribe_btn_html($action_id = false, $type = false) {

	global $question_category, $question_tag;

	$filter = apply_filters( 'ap_subscribe_btn_action_type', array( 'action_id' => $action_id, 'type' => $type ) );

	$action_id 	= $filter['action_id'];
	$type 		= $filter['type'];

	if ( false === $action_id ) {
		$action_id = get_question_id();
	}

	$subscribe_type = 'q_all';

	if ( $type == false ) {
		$subscribe_type = apply_filters( 'ap_subscribe_btn_type', 'q_all' );
	} elseif ( $type === 'category' || $type === 'tag' ) {
		$subscribe_type = 'tax_new_q';
	}

	$subscribed = ap_is_user_subscribed( $action_id, $subscribe_type );

	$nonce = wp_create_nonce( 'subscribe_'.$action_id.'_'.$subscribe_type );

	$title = ( ! $subscribed) ? __( 'Follow', 'anspress-question-answer' ) : __( 'Unfollow', 'anspress-question-answer' );
	?>
	<div class="ap-subscribe-btn" id="<?php echo 'subscribe_'.$action_id; ?>">
		<a href="#" class="ap-btn<?php echo ($subscribed) ? ' active' :''; ?>" data-query="<?php echo 'subscribe::'. $nonce .'::'. $action_id .'::'. $subscribe_type; ?>" data-action="ajax_btn" data-cb="apSubscribeBtnCB">
            <?php echo ap_icon( 'rss', true ); ?> <span class="text"><?php echo $title ?></span>      
        </a>
        <b class="ap-btn-counter" data-view="<?php echo 'subscribe_'.$action_id; ?>"><?php echo ap_subscribers_count( $action_id, $subscribe_type ) ?></b>
    </div>

	<?php
}

/**
 * Output question or terms subscribers.
 * @param  boolean|integer $action_id   Action ID.
 * @param  string          $type                Subscription type. q_all or tax_new_q.
 * @param  integer         $avatar_size         Avatar size.
 */
function ap_question_subscribers($action_id = false, $type = '', $avatar_size = 30) {
	if ( false === $action_id  ) {
		if ( is_question() ) {
			$action_id = get_question_id();
		}
		$action_id = apply_filters( 'ap_question_subscribers_action_id', $action_id );
	}

	if ( $type == '' ) {
		$type = is_question() ? 'q_all' : 'tax_new_q' ;
	}

	$subscribers = ap_get_subscribers( $action_id, $type );

	if ( $subscribers ) {
		echo '<div class="ap-question-subscribers clearfix">';
		echo '<div class="ap-question-subscribers-inner">';
		foreach ( $subscribers as $subscriber ) {
			echo '<a href="'.ap_user_link( $subscriber->subs_user_id ).'"';
			ap_hover_card_attributes( $subscriber->subs_user_id );
			echo '>'.get_avatar( $subscriber->subs_user_id, $avatar_size ).'</a>';
		}
		echo '</div>';
		echo '</div>';
	}
}

/**
 * Subscribe a user for a question.
 */
function ap_subscribe_question( $posta, $user_id = false ) {
	if ( ! is_object( $posta ) || ! isset( $posta->post_type ) ) {
		$posta = get_post( $posta );
	}

	// Return if not question.
	if ( 'question' != $posta->post_type ) {
		return false;
	}

	if ( false === $user_id ) {
		$user_id = $posta->post_author;
	}

	if ( ! ap_is_user_subscribed( $posta->ID, 'q_all', $user_id ) ) {
		ap_new_subscriber( $user_id, $posta->ID, 'q_all', $posta->ID );
	}
}

/**
 * Return all subscribers id
 * @param  integer      $item_id Item id.
 * @param  string|array $activity Activity type.
 * @return array   Ids of subscribed user.
 */
function ap_subscriber_ids( $item_id =false, $activity = 'q_all', $question_id = 0 ) {
	global $wpdb;

	if ( is_array( $activity ) ) {
		$activity_k = implode( '::', $activity );
	} else {
		$activity_k = $activity;
	}

	$key = $item_id . '::' . $activity_k .'::'. $question_id;

	$activity_q = '';

	$cache = wp_cache_get( $key, 'ap_subscribers_ids' );

	if ( false !== $cache ) {
		return $cache;
	}

	$item = '';

	if ( false !== $item_id ) {
		$item = $wpdb->prepare( 'subs_item_id = %d AND', $item_id );
	}

	$question = '';
	if ( 0 != $question_id ) {
		$question = $wpdb->prepare( 'AND subs_question_id=%d', $question_id );
	}

	$i = 1;
	if ( is_array( $activity ) && count( $activity ) > 0 ) {
		$activity_q .= ' subs_activity IN(';

		foreach ( $activity as $a ) {
			$activity_q .= '"'. sanitize_title_for_query( $a ) .'"';
			if ( $i != count( $activity ) ) {
				$activity_q .= ', ';
			}
			$i++;
		}

		$activity_q .= ') ';
	} else {
		$activity_q = ' subs_activity = "'. sanitize_title_for_query( $activity ) .'"';
	}

	$results = $wpdb->get_col( 'SELECT subs_user_id FROM '.$wpdb->ap_subscribers.' WHERE '.$item.' '. $activity_q .' '. $question .' GROUP BY subs_user_id' );

	wp_cache_set( $key, $results, 'ap_subscribers_ids' );
	return $results;
}

/**
 * Remove current user id from subscribers id
 * @param  array $subscribers Subscribers user_id.
 * @return array
 */
function ap_unset_current_user_from_subscribers($subscribers) {
	// Remove current user from subscribers.
	if ( ! empty( $subscribers ) && ($key = array_search( get_current_user_id(), $subscribers )) !== false ) {
	    unset( $subscribers[$key] );
	}

	return $subscribers;
}

/**
 * Add comment subscriber in database.
 * @param  object|int $comment Comment object or ID.
 * @param  bool|int   $user_id User ID.
 * @return bool|int
 * @since  3.0.0
 */
function ap_add_comment_subscriber( $comment, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$comment = get_comment( $comment );
	$post = get_post( $comment->comment_post_ID );
	$question_id = $post->post_type == 'question' ? $post->ID : $post->post_parent;
	$answer_id = $post->post_type == 'answer' ? $post->ID : 0;
	return ap_new_subscriber( $user_id, $post->ID, 'comment', $question_id, $answer_id );
}

/**
 * Remove comment subscriber from database.
 * @param  object|int $comment Comment object or ID.
 * @param  bool|int   $user_id User ID.
 * @return bool|int
 * @since  3.0.0
 */
function ap_remove_comment_subscriber( $comment, $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$comment = get_comment( $comment );
	$post = get_post( $comment->comment_post_ID );
	$question_id = $post->post_type == 'question' ? $post->ID : $post->post_parent;
	$answer_id = $post->post_type == 'answer' ? $post->ID : 0;
	return ap_remove_subscriber( $post->ID, $user_id, 'comment', false, $question_id, $answer_id );
}
