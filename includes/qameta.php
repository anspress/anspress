<?php

/**
 * Insert post meta
 * @param array   $args Args.
 * @param boolean $wp_error Return WP_Error object if error.
 * @return boolean|integer qameta id on success else false.
 * @since  3.1.0
 */
function ap_insert_qameta( $args, $wp_error = false ) {
	// If post ID is not set then return.
	if ( empty( $args['post_id'] ) ) {
		return $wp_error ? new WP_Error('You must pass a post ID' ) : false;
	}

	$args = wp_unslash( wp_parse_args( $args, [
		'post_type' => get_post_type( $args['post_id'] ),
	]));

	$valid_fields = array(
		'post_id' => 'int',
		'selected' => 'int',
		'answeres' => 'int',
		'post_type' => 'str',
		'featured' => 'bool',
		'closed' => 'bool',
		'views' => 'int',
		'votes_up' => 'int',
		'votes_down' => 'int',
		'flags' => 'int',
		'subscribers' => 'int',
		'terms' => 'str',
		'activities' => 'str',
		'roles' => 'str',
		'last_updated' => 'str',
	);

	$sanitized_values = [];
	$formats = [];

	// Include and sanitize valid fields.
	foreach ( (array) $valid_fields as $field => $type ) {
		if ( ! empty( $args[ $field ] ) ) {
			$value = $args[ $field ];

			if ( in_array($field, [ 'terms', 'activities' ] ) ) {
				$value = maybe_serialize( $value );
				$formats[] = '%s';
			} elseif ( 'bool' == $type ) {
				$value = (bool) $value;
				$formats[] = '%d';
			} elseif ( 'int' == $type ) {
				$value = (int) $value;
				$formats[] = '%d';
			} else {
				$value = sanitize_text_field( $value );
				$formats[] = '%s';
			}

			$sanitized_values[ $field ] = $value;
		}
	}

	global $wpdb;

	$exists = ap_get_qameta( $args[ 'post_id' ] );
	
	if( $exists->is_new ) {
		$inserted = $wpdb->insert( $wpdb->ap_qameta, $sanitized_values, $formats );
	} else {
		$inserted = $wpdb->update( $wpdb->ap_qameta, $sanitized_values, [ 'post_id'=> $args['post_id'] ], $formats );
	}

	if ( false !== $inserted ) {
		return $wpdb->insert_id;
	}

	return $wp_error ? new WP_Error('Unable to insert AnsPress post meta' ) : false;
}

/**
 * Get a qameta by post_id
 * @param  integer $post_id Post ID.
 * @return object|false
 * @since  3.1.0
 */
function ap_get_qameta( $post_id ) {
	global $wpdb;

	$qameta = wp_cache_get( $post_id, 'ap_qameta' );

	if ( false === $qameta ) {
		$qameta = $wpdb->get_row( $wpdb->prepare( "Select * FROM $wpdb->ap_qameta WHERE post_id = %d", $post_id ), ARRAY_A );

		// If null then append is_new.
		if ( empty( $qameta ) ) {
			$qameta = [ 'is_new' => true ];
		}

		$qameta = wp_parse_args( $qameta, [
			'post_id' => $post_id,
			'selected' => false,
			'selected_id' => 0,
			'comments' => 0,
			'answers' => 0,
			'post_type' => 'question',
			'featured' => 0,
			'closed' => 0,
			'views' => 0,
			'votes_up' => 0,
			'votes_down' => 0,
			'subscribers' => 0,
			'flags' => 0,
			'terms' => '',
			'activities' => '',
			'roles' => '',
			'updated' => '',
			'is_new' => false,
		]);

		$qameta[ 'votes_net' ] = $qameta[ 'votes_up' ] + $qameta[ 'votes_down' ];
		$qameta[ 'terms' ] = maybe_unserialize( $qameta[ 'terms' ] );
		$qameta[ 'activities' ] = maybe_unserialize( $qameta[ 'activities' ] );
		$qameta = (object) $qameta;

		wp_cache_add( $post_id, $qameta, 'ap_qameta' );
	}

	return $qameta;
}

/**
 * Update count of answers in post meta.
 * @param  integer $question_id Question ID.
 * @return boolean|false
 * @since  3.1.0
 */
function ap_update_answers_count( $question_id ) {
	$current_ans = ap_count_published_answers( $question_id );
	return ap_insert_qameta( [ 'post_id' => $question_id, 'answers' => $current_ans ] );
}

/**
 * Update qameta votes count
 * @param  integer $post_id Post ID.
 * @return boolean|integer
 * @since  3.1.0
 */
function ap_update_votes_count( $post_id ) {
	$count = ap_post_votes($post_id );

	$args = array(
		'post_id' => $post_id,
		'votes_up' => $count['voted_up'],
		'votes_down' => $count['voted_down'],
	);

	ap_insert_qameta( $args );
	return $args;
}

/**
 * Set selected answer for a question
 * @param  integer $question_id Question ID.
 * @param  integer $answer_id   Answer ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_set_selected_answer( $question_id, $answer_id ) {
	ap_insert_qameta( [ 'post_id' => $question_id, 'selected_id' => $answer_id ] );
	return ap_update_answer_selected( $answer_id );
}

/**
 * Clear selected answer from a question.
 * @param  integere $question_id Question ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_unset_selected_answer( $question_id, $timestamp = true ) {
	$qameta = ap_get_qameta( $question_id );

	ap_update_answer_selected( $qameta->selected_id, false );

	$q_args = [ 'post_id' => $question_id, 'selected_id' => 0 ];

	if ( true === $timestamp ) {
		$q_args['last_updated'] = current_time( 'mysql' );
	}

	return ap_insert_qameta( $q_args );
}

/**
 * Increment view count
 * @param  integer $question_id Question ID.
 * @param  integer $answer_id   Answer ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_incriment_views_count( $post_id ) {
	$qameta = ap_get_qameta( $post_id );
	return ap_insert_qameta( [ 'post_id' => $post_id, 'views' => (int) $qameta->views + 1 ] );
}

/**
 * Updates last_active field of qameta
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_update_last_active( $post_id ) {
	return ap_insert_qameta( [ 'post_id' => $post_id, 'last_updated' => current_time( 'mysql' ) ] );
}

/**
 * Set flags count for a qameta
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_set_flag_count( $post_id, $count = 1 ) {
	return ap_insert_qameta( [ 'post_id' => $post_id, 'flags' => $count ] );
}

/**
 * Increment flags count.
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_incriment_flags_count( $post_id ) {
	$qameta = ap_get_qameta( $post_id );
	ap_insert_qameta( [ 'post_id' => $post_id, 'flags' => (int) $qameta->flags + 1 ] );

	return $qameta->flags + 1;
}

/**
 * Updates selected field of qameta
 * @param  integer $answer_id Answer ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_update_answer_selected( $answer_id, $selected = true ) {
	return ap_insert_qameta( [ 'post_id' => $answer_id, 'selected' => (bool) $selected ] );
}


/**
 * Set subscribers count for a qameta.
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_set_subscribers_count( $post_id, $count = 1 ) {
	return ap_insert_qameta( [ 'post_id' => $post_id, 'subscribers' => $count ] );
}
