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

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
	 * @deprecated 5.0.0 Use VoteController instead.
	 */
	public static function vote() {
		_deprecated_function( __METHOD__, '5.0.0', 'VoteController' );

		$post_id = (int) ap_sanitize_unslash( 'post_id', 'request' );

		if ( ! anspress_verify_nonce( 'vote_' . $post_id ) ) {
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
				array(
					'success'  => false,
					'snackbar' => array(
						'message' => $thing->get_error_message(),
					),
				)
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
			if ( $is_voted->vote_value == $value ) { // phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
				$counts = ap_delete_post_vote( $post_id, $userid, 'vote_up' === $type );
				ap_ajax_json(
					array(
						'success'   => true,
						'action'    => 'undo',
						'vote_type' => $type,
						'snackbar'  => array(
							'message' => __( 'Your vote has been removed.', 'anspress-question-answer' ),
						),
						'voteData'  => array(
							'net'    => $counts['votes_net'],
							'active' => '',
							'nonce'  => wp_create_nonce( 'vote_' . $post_id ),
						),
					)
				);
			}

			// Else ask user to undor their vote first.
			ap_ajax_json(
				array(
					'success'  => false,
					'snackbar' => array(
						'message' => __( 'Undo your vote first.', 'anspress-question-answer' ),
					),
					'voteData' => array(
						'active' => $type,
						'nonce'  => wp_create_nonce( 'vote_' . $post_id ),
					),
				)
			);
		}

		$counts = ap_add_post_vote( $post_id, $userid, 'vote_up' === $type );

		ap_ajax_json(
			array(
				'success'   => true,
				'action'    => 'voted',
				'vote_type' => $type,
				'snackbar'  => array(
					'message' => __( 'Thank you for voting.', 'anspress-question-answer' ),
				),
				'voteData'  => array(
					'net'    => $counts['votes_net'],
					'active' => $type,
					'nonce'  => wp_create_nonce( 'vote_' . $post_id ),
				),
			)
		);
	}

	/**
	 * Delete post votes.
	 *
	 * @param integer $post_id Post ID.
	 * @since 4.0.0
	 * @deprecated 5.0.0 Use VoteController instead.
	 */
	public static function delete_votes( $post_id ) {
		_deprecated_function( __METHOD__, '5.0.0', 'VoteController' );

		$votes = ap_get_votes( array( 'vote_post_id' => $post_id ) );

		foreach ( (array) $votes as $vote ) {
			ap_delete_post_vote( $vote->vote_post_id, $vote->vote_user_id );
		}
	}

	/**
	 * Update votes count when multiple votes get deleted.
	 *
	 * @param integer $post_id Post ID.
	 * @param string  $type    Vote type.
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
 */
function ap_count_votes( $args ) {
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
 */
function ap_count_post_votes_by( $by, $value ) {
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
 */
function ap_get_vote( $post_id, $user_id, $type, $value = '' ) {
	global $wpdb;
	$where = "SELECT * FROM {$wpdb->ap_votes} WHERE 1=1 ";

	if ( ! empty( $type ) ) {
		if ( is_array( $type ) ) {
			$vote_type_in = sanitize_comma_delimited( $type, 'str' );

			if ( ! empty( $vote_type_in ) ) {
				$where .= ' AND vote_type IN (' . $vote_type_in . ')';
			}
		} else {
			$where .= $wpdb->prepare( ' AND vote_type = %s', $type );
		}
	}

	if ( ! empty( $value ) ) {
		if ( is_array( $value ) ) {
			$value_in = sanitize_comma_delimited( $value, 'str' );

			if ( ! empty( $value_in ) ) {
				$where .= ' AND vote_value IN (' . $value_in . ')';
			}
		} else {
			$where .= $wpdb->prepare( ' AND vote_value = %s', $value );
		}
	}

	$query = $where . $wpdb->prepare( ' AND vote_post_id = %d AND  vote_user_id = %d LIMIT 1', $post_id, $user_id );

	$vote = $wpdb->get_row( $query ); // phpcs:ignore WordPress.DB

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
 * Add post vote.
 *
 * @param  integer         $post_id Post ID.
 * @param  boolean|integer $user_id ID of user casting vote.
 * @param  string          $up_vote Is up vote.
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

	if ( ( 'question' === $post->post_type && ! ap_opt( 'disable_down_vote_on_question' ) ) ||
	( 'answer' === $post->post_type && ! ap_opt( 'disable_down_vote_on_answer' ) ) ) {
		$html .= '<a data-tipposition="bottom center" class="apicon-thumb-down ap-tip vote-down' .
			( $voted ? ' voted' : '' ) .
			( $vote && 'vote_up' === $type ? ' disable' : '' ) .
			'" href="#" title="' .
			( $vote && 'vote_up' === $type ? __( 'You have already voted', 'anspress-question-answer' ) :
			( $voted ? __( 'Withdraw your vote', 'anspress-question-answer' ) : $downvote_message ) ) .
			'" ap="vote_down"></a>';
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

	if ( ! $output ) {
		return $html;
	}

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Pre fetch and cache all votes by given post ID.
 *
 * @param  array $ids Post IDs.
 * @since  4.0.0
 * @deprecated 5.0.0
 */
function ap_user_votes_pre_fetch( $ids ) {
	_deprecated_function( __FUNCTION__, '5.0.0' );

	if ( $ids && is_user_logged_in() ) {
		$votes = ap_get_votes(
			array(
				'vote_post_id' => (array) $ids,
				'vote_user_id' => get_current_user_id(),
				'vote_type'    => array( 'flag', 'vote' ),
			)
		);

		$cache_keys = array();
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
