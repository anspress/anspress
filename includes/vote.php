<?php
/**
 * Handle all function related to voting system.
 *
 * @link https://anspress.io
 */

/**
 * AnsPress vote related class.
 */
class AnsPress_Vote {

	/**
	 * Process voting button.
	 *
	 * @since 2.0.1.1
	 */
	public static function vote() {
	    $post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );

	    if ( ! ap_verify_nonce( 'vote_' . $post_id ) ) {
	        ap_ajax_json( 'something_wrong' );
	    }

	    $type = ap_sanitize_unslash( 'type', 'request' );
	    $value = $type == 'up' ? '1' : '-1';
	    $type = $type == 'up' ? 'vote_up' : 'vote_down';

	    $userid = get_current_user_id();
	    $post = ap_get_post( $post_id );
	    $thing = ap_user_can_vote_on_post( $post_id, $type, $userid, true );

	    // Check if WP_Error object and send error message code.
	    if ( is_wp_error( $thing ) ) {
	        ap_ajax_json( $thing->get_error_code() );
	    }

	    if ( 'question' == $post->post_type && ap_opt( 'disable_down_vote_on_question' ) && 'vote_down' == $type ) {
	        ap_ajax_json( 'voting_down_disabled' );
	    } elseif ( 'answer' === $post->post_type && ap_opt( 'disable_down_vote_on_answer' ) && 'vote_down' === $type ) {
	        ap_ajax_json( 'voting_down_disabled' );
	    }

	    $is_voted = ap_get_vote( $post_id, get_current_user_id(), 'vote' );

	    if ( false !== $is_voted ) {
	        // If user already voted and click that again then reverse.
			if ( $is_voted->vote_value == $value ) {
				$counts = ap_delete_post_vote( $post_id, $userid, 'vote' );
			    do_action( 'ap_undo_vote', $post_id, $counts );
			    do_action( 'ap_undo_' . $type, $post_id, $counts );

			   	ap_ajax_json( array(
			   		'action' 	=> 'undo',
			   		'type' 		=> $type,
			   		'count' 	=> $counts['votes_net'],
			   		'message' 	=> 'undo_vote',
			   	) );
			}

			// Else ask user to undor their vote first.
			ap_ajax_json( 'undo_vote_your_vote' );
	    }

		$counts = ap_add_post_vote( $post_id, $userid, 'vote', $value );
		// Update post meta.
		do_action( 'ap_' . $type, $post_id, $counts );
	   	ap_ajax_json( array(
	   		'action' => 'voted',
	   		'type' => $type,
	   		'count' => $counts['votes_net'],
	   		'message' => 'voted',
	   	) );
	}
}

/**
 * Insert vote in ap_votes table.
 *
 * @param  integer $post_id Post ID.
 * @param  array   $args    Arguments.
 * @return boolean
 * @since  4.0.0
 */
function ap_vote_insert( $post_id, $user_id, $type = 'vote', $value = '', $date = false ) {
	if ( false === $date ) {
		$date = current_time( 'mysql' );
	}
	global $wpdb;
	$args = array(
		'vote_post_id' 	=> $post_id,
		'vote_user_id' 	=> $user_id,
		'vote_type' 	=> $type,
		'vote_value' 	=> $value,
		'vote_date' 	=> $date,
	);
	$inserted = $wpdb->insert( $wpdb->ap_votes, $args, [ '%d', '%d', '%s', '%s', '%s' ] );
	if ( false !== $inserted ) {
		/**
		 * Action triggred after inserting a vote.
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
 * Return votes.
 *
 * @param  array|integer $args Arguments or vote_post_id.
 * @return array|boolean
 * @since  4.0.0
 */
function ap_get_votes( $args = array() ) {
	if ( ! is_array( $args ) ) {
		$args = [ 'vote_post_id' => $args ];
	}
	global $wpdb;
	$where = "SELECT * FROM {$wpdb->ap_votes} WHERE 1=1";
	// Single or multiple posts.
	if ( isset( $args['vote_post_id'] ) && ! empty( $args['vote_post_id'] ) ) {
		if ( is_array( $args['vote_post_id'] ) ) {
			$where .= ' AND vote_post_id IN (' . sanitize_comma_delimited( $args['vote_post_id'], 'str' ) . ')';
		} else {
			$where .= ' AND vote_post_id = ' . (int) $args['vote_post_id'];
		}
	}
	// Single or multiple users.
	if ( isset( $args['vote_user_id'] ) && ! empty( $args['vote_user_id'] ) ) {
		if ( is_array( $args['vote_user_id'] ) ) {
			$where .= ' AND vote_user_id IN (' . sanitize_comma_delimited( $args['vote_user_id'] ) . ')';
		} else {
			$where .= ' AND vote_user_id = ' . (int) $args['vote_user_id'];
		}
	}
	// Single or multiple vote types.
	if ( isset( $args['vote_type'] ) && ! empty( $args['vote_type'] ) ) {
		if ( is_array( $args['vote_type'] ) ) {
			$where .= ' AND vote_type IN (' . sanitize_comma_delimited( $args['vote_type'], 'str' ) . ')';
		} else {
			$where .= ' AND vote_type = ' . sanitize_text_field( $args['vote_type'] );
		}
	}

	$key = md5( $where );
	$cache = wp_cache_get( $key, 'ap_votes_queries' );
	if ( false !== $cache ) {
		return $cache;
	}
	$results = $wpdb->get_results( $where );
	if ( false !== $results ) {
		wp_cache_set( $key, $results, 'ap_votes_queries' );
		// Also cache each vote individually.
		foreach ( (array) $results as $vote ) {
			$key = $vote->vote_post_id . '_' . $vote->vote_user_id . '_' . $vote->vote_type;
			wp_cache_set( $key, $vote, 'ap_votes' );
		}
	}
	return $results;
}

/**
 * Get votes count.
 *
 * @param  array $args Arguments
 *                     {
 *                     	'vote_post_id' => 1,
 *                     	'vote_type' => 'vote', String or Array
 *                     	'vote_use_id' => 1,
 *                     	'vote_date' => 'date' // Array or string
 *                     }
 * @return array|boolean
 */
function ap_count_votes( $args ) {
	global $wpdb;
	$where = "SELECT count(*) as count, vote_value as type FROM {$wpdb->ap_votes} WHERE 1=1 ";
	if ( isset( $args['vote_post_id'] ) ) {
		$where .= 'AND vote_post_id = ' . (int) $args['vote_post_id'];
	}

	if ( isset( $args['vote_type'] ) ) {
		if ( is_array( $args['vote_type'] ) ) {
			$where .= ' AND vote_type IN (' . sanitize_comma_delimited( $args['vote_type'], 'str' ) . ')';
		} else {
			$where .= " AND vote_type = '" . sanitize_text_field( $args['vote_type'] ) . "'";
		}
	}

	if ( isset( $args['vote_user_id'] ) ) {
		if ( is_array( $args['vote_user_id'] ) ) {
			$where .= ' AND vote_user_id IN (' . sanitize_comma_delimited( $args['vote_user_id'] ) . ')';
		} else {
			$where .= " AND vote_user_id = '" . (int) $args['vote_user_id'] . "'";
		}
	}

	if ( isset( $args['vote_value'] ) ) {
		if ( is_array( $args['vote_value'] ) ) {
			$where .= ' AND vote_value IN (' . sanitize_comma_delimited( $args['vote_value'], 'str' ) . ')';
		} else {
			$where .= " AND vote_value = '" . sanitize_text_field( $args['vote_value'] ) . "'";
		}
	}
	$where .= ' GROUP BY type';
	$cache_key = md5( $where );
	$cache = wp_cache_get( $cache_key, 'ap_vote_counts' );
	if ( false !== $cache ) {
		return $cache;
	}
	$rows = $wpdb->get_results( $where );
	wp_cache_set( $cache_key, $rows, 'ap_vote_counts' );
	if ( false !== $rows ) {
		return $rows;
	}
	return false;
}

/**
 * Count votes of a post and propery format.
 *
 * @param  integer $post_id Post ID.
 * @return array
 * @since  4.0.0
 * @uses   ap_count_votes
 */
function ap_count_post_votes_by( $by, $value ) {
	$bys = [ 'post_id', 'user_id' ];
	if ( ! in_array( $by, $bys ) ) {
		return false;
	}
	$new_counts = [ 'votes_net' => 0, 'votes_down' => 0, 'votes_up' => 0 ];
	$args = [ 'vote_type' => 'vote' ];
	if ( 'post_id' == $by ) {
		$args['vote_post_id'] = $value;
	} elseif ( 'user_id' == $by ) {
		$args['vote_user_id'] = $value;
	}
	$rows = ap_count_votes( $args );
	if ( false !== $rows ) {
		foreach ( (array) $rows as $row ) {
			$type = $row->type == '-1' ? 'votes_down' : 'votes_up';
			$new_counts[ $type ] = (int) $row->count;
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
 * @return boolena|object
 * @since  4.0.0
 */
function ap_get_vote( $post_id, $user_id, $type, $value = '' ) {
	$cache_key = $post_id . '_' . $user_id . '_' . $type;
	$cache = wp_cache_get( $cache_key, 'ap_votes' );
	if ( false !== $cache ) {
		return $cache;
	}

	global $wpdb;
	$where = "SELECT * FROM {$wpdb->ap_votes} WHERE 1=1 ";
	if ( ! empty( $type ) ) {
		if ( is_array( $type ) ) {
			$where .= ' AND vote_type IN (' . sanitize_comma_delimited( $type, 'str' ) . ')';
		} else {
			$where .= " AND vote_type = '" . sanitize_text_field( $type ) . "'";
		}
	}
	if ( ! empty( $value ) ) {
		if ( is_array( $value ) ) {
			$where .= ' AND vote_value IN (' . sanitize_comma_delimited( $value, 'str' ) . ')';
		} else {
			$where .= " AND vote_value = '" . sanitize_text_field( $value ) . "'";
		}
	}
	$vote = $wpdb->get_row( $where . $wpdb->prepare( ' AND vote_post_id = %d AND  vote_user_id = %d LIMIT 1', $post_id, $user_id ) );
	wp_cache_set( $cache_key, $vote, 'ap_votes' );

	if ( ! empty( $vote ) ) {
		return $vote;
	}
	return false;
}

/**
 * Check if user vote on a post.
 *
 * @param  integer      $post_id Post ID.
 * @param  integer      $user_id User ID.
 * @param  string|array $type    Vote type.
 * @return boolean
 * @since  4.0.0
 * @uses   ap_get_vote
 */
function ap_is_user_voted( $post_id, $type = 'vote', $user_id = false ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( false === ap_get_vote( $post_id, $user_id, $type ) ) {
		return false;
	}
	return true;
}

/**
 * Delete vote from database.
 *
 * @param  integer      $post_id Post ID.
 * @param  integer      $user_id User ID.
 * @param  string|array $type    Vote type.
 * @return boolean
 * @since  4.0.0
 */
function ap_delete_vote( $post_id, $user_id = false, $type = 'vote', $value = false ) {
	global $wpdb;
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$where = [ 'vote_post_id' => $post_id, 'vote_user_id' => $user_id, 'vote_type' => $type ];
	if ( false !== $value ) {
		$where['vote_value'] = $value;
	}
	return $wpdb->delete( $wpdb->ap_votes, $where );
}

/**
 * Add post vote.
 *
 * @param  integer         $post_id Post ID.
 * @param  boolean|integer $user_id ID of user casting vote.
 * @param  string          $type    Vote type.
 * @return boolean
 * @since  4.0.0
 */
function ap_add_post_vote( $post_id, $user_id = false, $type = 'vote', $value = '1' ) {
	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}
	$row = ap_vote_insert( $post_id, $user_id, $type, $value );
	if ( false !== $row ) {
		return ap_update_votes_count( $post_id );
	}
	return false;
}

/**
 * Delete post vote and update qameta votes count.
 *
 * @param  integer         $post_id    Post ID.
 * @param  boolean|integer $user_id    User ID.
 * @param  string          $type       Vote type.
 * @param  boolean|string  $value      Vote value.
 * @return boolean|integer
 */
function ap_delete_post_vote( $post_id, $user_id = false, $type = 'vote', $value = false ) {
	$rows = ap_delete_vote( $post_id, $user_id, $type, $value );
	if ( false !== $rows ) {
		return ap_update_votes_count( $post_id );
	}
	return false;
}

/**
 * Output or return voting button.
 *
 * @param 	int|object $post Post ID or object.
 * @param 	bool       $echo Echo or return vote button.
 * @return 	null|string
 * @since 0.1
 */
function ap_vote_btn( $post = null, $echo = true ) {
	$post = ap_get_post( $post );
	if ( 'answer' == $post->post_type && ap_opt( 'disable_voting_on_answer' ) ) {
		return;
	}

	if ( 'question' == $post->post_type && ap_opt( 'disable_voting_on_question' ) ) {
		return;
	}
	$nonce = wp_create_nonce( 'vote_' . $post->ID );
	$vote = ap_get_vote( $post->ID, get_current_user_id(), 'vote' );
	$voted = $vote ? true : false;
	$type = $vote && $vote->vote_value == '1' ? 'vote_up' : 'vote_down';

	$html = '';
	$html .= '<div data-id="' . $post->ID . '" class="ap-vote net-vote" data-action="vote">';
	$html .= '<a class="' . ap_icon( 'vote_up' ) . ' ap-tip vote-up' . ($voted ? ' voted' : '') . ($vote && $type == 'vote_down' ? ' disable' : '') . '" data-query="ap_ajax_action=vote&type=up&post_id=' . $post->ID . '&__nonce=' . $nonce . '" href="#" title="' . __( 'Up vote this post', 'anspress-question-answer' ) . '"></a>';

	$html .= '<span class="net-vote-count" data-view="ap-net-vote" itemprop="upvoteCount">' . ap_get_votes_net() . '</span>';

	if ( ('question' == $post->post_type && ! ap_opt( 'disable_down_vote_on_question' )) ||
		('answer' == $post->post_type && ! ap_opt( 'disable_down_vote_on_answer' )) ) {
		$html .= '<a data-tipposition="bottom center" class="' . ap_icon( 'vote_down' ) . ' ap-tip vote-down' . ($voted ? ' voted' : '') . ($vote && $type == 'vote_up' ? ' disable' : '') . '" data-query="ap_ajax_action=vote&type=down&post_id=' . $post->ID . '&__nonce=' . $nonce . '" href="#" title="' . __( 'Down vote this post', 'anspress-question-answer' ) . '"></a>';
	}

	$html .= '</div>';

	if ( $echo ) {
		echo $html;
	} else {
		return $html;
	}
}


