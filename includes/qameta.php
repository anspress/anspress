<?php

function ap_qameta_fields() {
	return array(
		'post_id' 		=> '',
		'selected' 		=> false,
		'selected_id' 	=> 0,
		'comments' 		=> 0,
		'answers' 		=> 0,
		'ptype' 		=> 'question',
		'featured' 		=> 0,
		'closed' 		=> 0,
		'views' 		=> 0,
		'votes_up' 		=> 0,
		'votes_down' 	=> 0,
		'subscribers' 	=> 0,
		'flags' 		=> 0,
		'terms' 		=> '',
		'activities' 	=> '',
		'roles' 		=> '',
		'last_updated' 	=> '',
		'is_new' 		=> false,
	);
}

/**
 * Insert post meta
 *
 * @param array   $args Args.
 * @param boolean $wp_error Return WP_Error object if error.
 * @return boolean|integer qameta id on success else false.
 * @since  3.1.0
 */

function ap_insert_qameta( $post_id, $args, $wp_error = false ) {
	$args = wp_unslash( wp_parse_args( $args, [
		'ptype' => get_post_type( $post_id ),
	]));

	$sanitized_values = [];
	$formats = [];

	// Include and sanitize valid fields.
	foreach ( (array) ap_qameta_fields() as $field => $type ) {
		if ( isset( $args[ $field ] ) ) {
			$value = $args[ $field ];

			if ( $field == 'activities' ) {
				$value = maybe_serialize( $value );
				$formats[] = '%s';
			} elseif ( $field == 'terms' ) {
				$value = is_array( $value ) ? sanitize_comma_delimited( $value ) : (int) $value;
				$formats[] = '%s';
			} elseif ( in_array( $field, ['selected', 'featured', 'closed'] ) ) {
				$value = (bool) $value;
				$formats[] = '%d';
			} elseif ( in_array($field, ['selected_id', 'comments', 'answers', 'views', 'votes_up', 'votes_down', 'subscribers', 'flags']) ) {
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

	$exists = ap_get_qameta( $post_id );
	
	if ( $exists->is_new ) {
		$sanitized_values['post_id'] = (int) $post_id;
		$inserted = $wpdb->insert( $wpdb->ap_qameta, $sanitized_values, $formats );
	} else {
		$inserted = $wpdb->update( $wpdb->ap_qameta, $sanitized_values, [ 'post_id' => $post_id ], $formats );
	}

	if ( false !== $inserted ) {
		wp_cache_delete( $post_id, 'ap_qameta' );
		return $post_id;
	}

	return $wp_error ? new WP_Error( 'Unable to insert AnsPress qameta' ) : false;
}

/**
 * Get a qameta by post_id
 *
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

		$qameta = wp_parse_args( $qameta, ap_qameta_fields() );

		$qameta['votes_net'] = $qameta['votes_up'] + $qameta['votes_down'];
		$qameta['terms'] = maybe_unserialize( $qameta['terms'] );
		$qameta['activities'] = maybe_unserialize( $qameta['activities'] );
		$qameta = (object) $qameta;

		wp_cache_add( $post_id, $qameta, 'ap_qameta' );
	}

	return $qameta;
}



/**
 * Append post object with apmeta feilds.
 *
 * @param  object $post Post Object.
 * @return object
 */
function ap_append_qameta( $post ) {
	// Convert object as array to prevent using __isset of WP_Post.
	$post_arr = (array) $post;
	if ( ! in_array( $post_arr['post_type'], [ 'question', 'answer' ] ) || isset( $post_arr['ap_qameta_wrapped'] ) ) {
		return $post;
	}

	$exist = true;
	foreach ( ap_qameta_fields() as $fields_name => $val ) {
		if ( ! isset( $post_arr[ $fields_name ] ) ) {
			$exist = false;
		}
	}

	if ( ! $exist ) {
		$defaults = ap_get_qameta( $post->ID );
		if ( ! empty( $defaults ) ) {
			foreach ( $defaults as $pkey => $value ) {
				if ( ! isset( $post_arr[ $pkey ] ) || empty( $post_arr[ $pkey ] ) ) {
					$post->$pkey = $value;
				}
			}
		}

		$post->terms = maybe_unserialize( $post->terms );
		$post->activities = maybe_unserialize( $post->activities );

		$post->votes_net = $post->votes_up - $post->votes_down;
	}

	return $post;
}

/**
 * Update count of answers in post meta.
 *
 * @param  integer $question_id Question ID.
 * @return boolean|false
 * @since  3.1.0
 */
function ap_update_answers_count( $question_id, $counts = false ) {
	if ( false === $counts ) {
		$counts = ap_count_published_answers( $question_id );
	}

	return ap_insert_qameta( $question_id, [ 'answers' => $counts, 'last_updated' => current_time( 'mysql' ) ] );
}

/**
 * Update qameta votes count
 *
 * @param  integer $post_id Post ID.
 * @return boolean|integer
 * @since  3.1.0
 */
function ap_update_votes_count( $post_id ) {
	$count = ap_count_post_votes_by( 'post_id', $post_id );
	ap_insert_qameta( $post_id, $count );
	return $count;
}

/**
 * Set selected answer for a question
 *
 * @param  integer $question_id Question ID.
 * @param  integer $answer_id   Answer ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_set_selected_answer( $question_id, $answer_id ) {
	ap_insert_qameta( $answer_id, [ 'selected' => 1, 'last_updated' => current_time( 'mysql' ) ] );
	ap_insert_qameta( $question_id, [ 'selected_id' => $answer_id, 'last_updated' => current_time( 'mysql' ) ] );
	return ap_update_answer_selected( $answer_id );
}

/**
 * Clear selected answer from a question.
 *
 * @param  integere $question_id Question ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_unset_selected_answer( $question_id ) {
	$qameta = ap_get_qameta( $question_id );

	// Clear selected column from answer qameta.
	ap_insert_qameta( $qameta->selected_id, [ 'selected' => 0, 'last_updated' => current_time( 'mysql' ) ] );
	return ap_insert_qameta( $question_id, [ 'selected_id' => '', 'last_updated' => current_time( 'mysql' ) ] );
}

/**
 * Update views count of qameta.
 *
 * @param  integer       $question_id Question ID.
 * @param  integer|false $views   Passing view will replace existing value else increment existing.
 * @return integer
 * @since  3.1.0
 */
function ap_update_views_count( $post_id, $views = false ) {
	if ( false === $views ) {
		$qameta = ap_get_qameta( $post_id );
		$views = (int) $qameta->views + 1;
	}

	ap_insert_qameta( $post_id, [ 'views' => $views ] );
	return $views;
}

/**
 * Updates last_active field of qameta
 *
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_update_last_active( $post_id ) {
	var_dump( [ 'last_updated' => current_time( 'mysql' ) ] );
	return ap_insert_qameta( $post_id, [ 'last_updated' => current_time( 'mysql' ) ] );
}

/**
 * Set flags count for a qameta
 *
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_set_flag_count( $post_id, $count = 1 ) {
	return ap_insert_qameta( $post_id, [ 'flags' => $count ] );
}

/**
 * Increment flags count.
 *
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_update_flags_count( $post_id ) {
	$count = ap_count_flag_vote( 'flag', $post_id );
	ap_insert_qameta( $post_id, [ 'flags' => $count ] );

	return $count;
}

/**
 * Updates selected field of qameta
 *
 * @param  integer $answer_id Answer ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_update_answer_selected( $answer_id, $selected = true ) {
	return ap_insert_qameta( $answer_id, [ 'selected' => (bool) $selected ] );
}


/**
 * Set subscribers count for a qameta.
 *
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_set_subscribers_count( $post_id, $count = 1 ) {
	return ap_insert_qameta( $post_id, [ 'subscribers' => $count ] );
}


/**
 * Updates terms of qameta.
 *
 * @param  integer $question_id Question ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_update_qameta_terms( $question_id ) {
	$taxonomies = get_taxonomies( '', 'names' );
	$terms = wp_get_object_terms( $question_id, $taxonomies );

	$term_ids = [];

	foreach ( (array) $terms as $term ) {
		$term_ids[] = $term->term_id;
	}

	if ( ! empty( $term_ids ) ) {
		ap_insert_qameta( $question_id, [ 'terms' => $term_ids ] );
	}

	return $term_ids;
}

/**
 * Set a question as a featured.
 * @param  integer $post_id Question ID.
 * @return boolean
 */
function ap_set_featured_question( $post_id ) {
	return ap_insert_qameta( $post_id, [ 'featured' => 1 ] );
}

/**
 * Unset a question as a featured.
 * @param  integer $post_id Question ID.
 * @return boolean
 */
function ap_unset_featured_question( $post_id ) {
	return ap_insert_qameta( $post_id, [ 'featured' => 0 ] );
}