<?php
/**
 * Handle all function related to voting system.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

/**
 * AnsPress vote related class.
 */
class AnsPress_Vote {

	/**
	 * Process voting button.
	 *
	 * @since 2.0.1.1
	 *
	 * @todo Add ajax tests for subscribers.
	 */
	public static function vote() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );

		if ( ! ap_verify_nonce( 'vote_' . $post_id ) ) {
				ap_ajax_json( 'something_wrong' );
		}

		$type   = 'vote_up' === ap_sanitize_unslash( 'type', 'request' ) ? 'vote_up' : 'vote_down';
		$value  = 'vote_up' === $type ? '1' : '-1';
		$userid = get_current_user_id();
		$post   = ap_get_post( $post_id );
		$thing  = ap_user_can_vote_on_post( $post_id, $type, $userid, true );

		// Check if WP_Error object and send error message code.
		if ( is_wp_error( $thing ) ) {
			ap_ajax_json(
				[
					'success'  => false,
					'snackbar' => [
						'message' => $thing->get_error_message(),
					],
				]
			);
		}

		// Check if down vote disabled.
		if ( 'question' === $post->post_type && ap_opt( 'disable_down_vote_on_question' ) && 'vote_down' === $type ) {
			ap_ajax_json( 'voting_down_disabled' );
		} elseif ( 'answer' === $post->post_type && ap_opt( 'disable_down_vote_on_answer' ) && 'vote_down' === $type ) {
			ap_ajax_json( 'voting_down_disabled' );
		}

		$is_voted = ap_get_vote( $post_id, get_current_user_id(), 'vote' );

		if ( false !== $is_voted ) {

			// If user already voted and click that again then reverse.
			if ( $is_voted->vote_value == $value ) { // loose comparison okay.
				$counts = ap_delete_post_vote( $post_id, $userid, 'vote_up' === $type );
				ap_ajax_json(
					array(
						'success'   => true,
						'action'    => 'undo',
						'vote_type' => $type,
						'snackbar'  => [
							'message' => __( 'Your vote has been removed.', 'anspress-question-answer' ),
						],
						'voteData'  => [
							'net'    => $counts['votes_net'],
							'active' => '',
							'nonce'  => wp_create_nonce( 'vote_' . $post_id ),
						],
					)
				);
			}

			// Else ask user to undor their vote first.
			ap_ajax_json(
				[
					'success'  => false,
					'snackbar' => [
						'message' => __( 'Undo your vote first.', 'anspress-question-answer' ),
					],
					'voteData' => [
						'active' => $type,
						'nonce'  => wp_create_nonce( 'vote_' . $post_id ),
					],
				]
			);
		}

		$counts = ap_add_post_vote( $post_id, $userid, 'vote_up' === $type );

		ap_ajax_json(
			array(
				'success'   => true,
				'action'    => 'voted',
				'vote_type' => $type,
				'snackbar'  => [
					'message' => __( 'Thank you for voting.', 'anspress-question-answer' ),
				],
				'voteData'  => [
					'net'    => $counts['votes_net'],
					'active' => $type,
					'nonce'  => wp_create_nonce( 'vote_' . $post_id ),
				],
			)
		);
	}

	/**
	 * Delete post votes.
	 *
	 * @param integer $post_id Post ID.
	 * @since 4.0.0
	 */
	public static function delete_votes( $post_id ) {
		$votes = ap_get_votes( [ 'vote_post_id' => $post_id ] );

		foreach ( (array) $votes as $vote ) {
			ap_delete_post_vote( $vote->vote_post_id, $vote->vote_user_id );
		}
	}

	/**
	 * Update votes count when multiple votes get deleted.
	 *
	 * @param integer $post_id Post ID.
	 * @since 4.0.0
	 */
	public static function ap_deleted_votes( $post_id, $type ) {
		if ( 'vote' === $type ) {
			ap_update_votes_count( $post_id );
		} elseif ( 'flag' === $type ) {
			ap_update_flags_count( $post_id );
		}

	}
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
 */
function ap_vote_insert( $post_id, $user_id, $type = 'vote', $rec_user_id = 0, $value = '', $date = false ) {

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

	$inserted = $wpdb->insert( $wpdb->ap_votes, $args, [ '%d', '%d', '%d', '%s', '%s', '%s' ] );

	if ( false !== $inserted ) {
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

	// Vote actors.
	if ( isset( $args['vote_actor_id'] ) && ! empty( $args['vote_actor_id'] ) ) {
		if ( is_array( $args['vote_actor_id'] ) ) {
			$where .= ' AND vote_actor_id IN (' . sanitize_comma_delimited( $args['vote_actor_id'] ) . ')';
		} else {
			$where .= ' AND vote_actor_id = ' . (int) $args['vote_actor_id'];
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

	$results = $wpdb->get_results( $where ); // db call okay, unprepared sql okay.

	return $results;
}

/**
 * Get votes count.
 *
 * @param  array $args Arguments.
 *                     {
 *                      'vote_post_id' => 1,
 *                      'vote_type' => 'vote', String or Array
 *                      'vote_user_id' => 1,
 *                      'vote_date' => 'date' // Array or string
 *                     }
 * @return array|boolean
 */
function ap_count_votes( $args ) {
	global $wpdb;
	$args  = wp_parse_args( $args, [ 'group' => false ] );
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
			$where .= ' AND vote_type IN (' . sanitize_comma_delimited( $args['vote_type'], 'str' ) . ')';
		} else {
			$where .= " AND vote_type = '" . sanitize_text_field( $args['vote_type'] ) . "'";
		}
	}

	// Vote user id.
	if ( isset( $args['vote_user_id'] ) ) {

		if ( is_array( $args['vote_user_id'] ) ) {
			$where .= ' AND vote_user_id IN (' . sanitize_comma_delimited( $args['vote_user_id'] ) . ')';
		} else {
			$where .= " AND vote_user_id = '" . (int) $args['vote_user_id'] . "'";
		}
	}

	// Vote actor id.
	if ( isset( $args['vote_actor_id'] ) ) {
		if ( is_array( $args['vote_actor_id'] ) ) {
			$where .= ' AND vote_actor_id IN (' . sanitize_comma_delimited( $args['vote_actor_id'] ) . ')';
		} else {
			$where .= " AND vote_actor_id = '" . (int) $args['vote_actor_id'] . "'";
		}
	}

	// Vote value.
	if ( isset( $args['vote_value'] ) ) {
		if ( is_array( $args['vote_value'] ) ) {
			$where .= ' AND vote_value IN (' . sanitize_comma_delimited( $args['vote_value'], 'str' ) . ')';
		} else {
			$where .= " AND vote_value = '" . sanitize_text_field( $args['vote_value'] ) . "'";
		}
	}

	if ( $args['group'] ) {
		$where .= ' GROUP BY ' . esc_sql( sanitize_text_field( $args['group'] ) );
	}

	$rows = $wpdb->get_results( $where ); // db call okay, unprepared SQL okay.

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
 */
function ap_count_post_votes_by( $by, $value ) {
	$bys = [ 'post_id', 'user_id', 'actor_id' ];

	if ( ! in_array( $by, $bys, true ) ) {
		return false;
	}

	$new_counts = [
		'votes_net'  => 0,
		'votes_down' => 0,
		'votes_up'   => 0,
	];
	$args       = [
		'vote_type' => 'vote',
		'group'     => 'vote_value',
	];

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
			$type                = '-1' == $row->vote_value ? 'votes_down' : 'votes_up'; // loose comparison okay.
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
 * @param  string       $value   Vote value.
 * @return boolean|object
 * @since  4.0.0
 */
function ap_get_vote( $post_id, $user_id, $type, $value = '' ) {
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

	$vote = $wpdb->get_row( $where . $wpdb->prepare( ' AND vote_post_id = %d AND  vote_user_id = %d LIMIT 1', $post_id, $user_id ) ); // db call okay, unprepared SQL okay.

	if ( ! empty( $vote ) ) {
		return $vote;
	}

	return false;
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
 * @param  integer         $post_id Post ID.
 * @param  integer|boolean $user_id User ID.
 * @param  string|array    $type    Vote type.
 * @param  string          $value   Vote value.
 * @return boolean
 * @since  4.0.0
 */
function ap_delete_vote( $post_id, $user_id = false, $type = 'vote', $value = false ) {
	global $wpdb;

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$where = [
		'vote_post_id' => $post_id,
		'vote_user_id' => $user_id,
		'vote_type'    => $type,
	];

	if ( false !== $value ) {
		$where['vote_value'] = $value;
	}

	$row = $wpdb->delete( $wpdb->ap_votes, $where ); // db call okay, db cache okay.

	if ( false !== $row ) {
		do_action( 'ap_delete_vote', $post_id, $user_id, $type, $value );
	}

	return $row;
}

/**
 * Add post vote.
 *
 * @param  integer         $post_id Post ID.
 * @param  boolean|integer $user_id ID of user casting vote.
 * @param  string          $up_vote Is up vote.
 * @param  integer|false   $actor Id of user receiving vote.
 * @return boolean
 * @since  4.0.0
 */
function ap_add_post_vote( $post_id, $user_id = 0, $up_vote = true ) {

	if ( false === $user_id ) {
		$user_id = get_current_user_id();
	}

	$_post       = get_post( $post_id );
	$rec_user_id = $_post->post_author;

	$value = $up_vote ? '1' : '-1';
	$row   = ap_vote_insert( $post_id, $user_id, 'vote', $rec_user_id, $value );

	if ( false !== $row ) {
		// Update qameta.
		$counts    = ap_update_votes_count( $post_id );
		$vote_type = $up_vote ? 'vote_up' : 'vote_down';

		/**
			* Action ap_[vote_type]
			* Action triggred after adding a vote for a post.
			*
			* @param integer $post_id Post ID.
			* @param array   $counts All vote counts.
			*/
		do_action( 'ap_' . $vote_type, $post_id, $counts );

		return $counts;
	}

	return false;
}

/**
 * Delete post vote and update qameta votes count.
 *
 * @param  integer         $post_id    Post ID.
 * @param  boolean|integer $user_id    User ID.
 * @param  boolean|string  $up_vote    Is up vote.
 * @return boolean|integer
 */
function ap_delete_post_vote( $post_id, $user_id = false, $up_vote = null ) {
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
 * Output or return voting button.
 *
 * @param   int|object $post Post ID or object.
 * @param   bool       $echo Echo or return vote button.
 * @return  null|string
 * @since 0.1
 */
function ap_vote_btn( $post = null, $echo = true ) {
	$post = ap_get_post( $post );
	if ( ! $post || 'answer' === $post->post_type && ap_opt( 'disable_voting_on_answer' ) ) {
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

	$data = [
		'post_id' => $post->ID,
		'active'  => $type,
		'net'     => ap_get_votes_net(),
		'__nonce' => wp_create_nonce( 'vote_' . $post->ID ),
	];

	$html  = '';
	$html .= '<div id="vote_' . $post->ID . '" class="ap-vote net-vote" ap-vote=\'' . wp_json_encode( $data ) . '\'>';
	$html .= '<a class="apicon-thumb-up ap-tip vote-up' . ( $voted ? ' voted' : '' ) . ( $vote && 'vote_down' === $type ? ' disable' : '' ) . '" href="#" title="' . ( $vote && 'vote_down' === $type ? __( 'You have already voted', 'anspress-question-answer' ) : ( $voted ? __( 'Withdraw your vote', 'anspress-question-answer' ) : __( 'Up vote this post', 'anspress-question-answer' ) ) ) . '" ap="vote_up"></a>';
	$html .= '<span class="net-vote-count" data-view="ap-net-vote" itemprop="upvoteCount" ap="votes_net">' . ap_get_votes_net() . '</span>';

	if ( ( 'question' === $post->post_type && ! ap_opt( 'disable_down_vote_on_question' ) ) ||
		( 'answer' === $post->post_type && ! ap_opt( 'disable_down_vote_on_answer' ) ) ) {
		$html .= '<a data-tipposition="bottom center" class="apicon-thumb-down ap-tip vote-down' . ( $voted ? ' voted' : '' ) . ( $vote && 'vote_up' === $type ? ' disable' : '' ) . '" href="#" title="' . ( $vote && 'vote_up' === $type ? __( 'You have already voted', 'anspress-question-answer' ) : ( $voted ? __( 'Withdraw your vote', 'anspress-question-answer' ) : __( 'Down vote this post', 'anspress-question-answer' ) ) ) . '" ap="vote_down"></a>';
	}

	$html .= '</div>';

	/**
	 * Allows overriding voting button HTML upload.
	 *
	 * @param string  $html Vote button.
	 * @param WP_Post $post WordPress post object.
	 * @since 4.1.5
	 */
	$html = apply_filters( 'ap_vote_btn_html', $html, $post );

	if ( ! $echo ) {
		return $html;
	}

	echo $html; // xss okay.
}

/**
 * Pre fetch and cache all votes by given post ID.
 *
 * @param  array $ids Post IDs.
 * @since  4.0.0
 */
function ap_user_votes_pre_fetch( $ids ) {
	if ( $ids && is_user_logged_in() ) {
		$votes = ap_get_votes( [
			'vote_post_id' => (array) $ids,
			'vote_user_id' => get_current_user_id(),
			'vote_type'    => [ 'flag', 'vote' ],
		] );

		$cache_keys = [];
		foreach ( (array) $ids as $post_id ) {
			$cache_keys[ $post_id . '_' . get_current_user_id() . '_flag' ] = true;
			$cache_keys[ $post_id . '_' . get_current_user_id() . '_vote' ] = true;
		}

		foreach ( (array) $votes as $vote ) {
			unset( $cache_keys[ $vote->vote_post_id . '_' . $vote->vote_user_id . '_' . $vote->vote_type ] );
		}
	}
}

/**
 * Delete multiple post votes.
 *
 * @param integer $post_id Post id.
 * @param string  $type Vote type.
 * @return boolean
 */
function ap_delete_votes( $post_id, $type = 'vote' ) {
	global $wpdb;
	$where = [
		'vote_post_id' => $post_id,
		'vote_type'    => $type,
	];

	$rows = $wpdb->delete( $wpdb->ap_votes, $where ); // db call okay, db cache okay.

	if ( false !== $rows ) {
		do_action( 'ap_deleted_votes', $post_id, $type );
		return true;
	}

	return false;
}

