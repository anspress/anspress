<?php
/**
 * Holds ap_qameta table helpers.
 *
 * @since 4.0.0
 * @package AnsPress
 */

/**
 * Default qameta table fields with values.
 *
 * @return array
 */
function ap_qameta_fields() {
	return array(
		'post_id'      => '',
		'selected'     => false,
		'selected_id'  => 0,
		'comments'     => 0,
		'answers'      => 0,
		'ptype'        => 'question',
		'featured'     => 0,
		'closed'       => 0,
		'views'        => 0,
		'votes_up'     => 0,
		'votes_down'   => 0,
		'subscribers'  => 0,
		'flags'        => 0,
		'terms'        => '',
		'attach'       => '',
		'activities'   => '',
		'fields'       => '',
		'roles'        => '',
		'last_updated' => '',
		'is_new'       => false,
	);
}

/**
 * Insert post meta
 *
 * @param array   $post_id Post ID.
 * @param boolean $args Arguments.
 * @param boolean $wp_error Return wp_error on fail.
 * @return boolean|integer qameta id on success else false.
 * @since   4.0.0
 */
function ap_insert_qameta( $post_id, $args, $wp_error = false ) {

	if ( empty( $post_id ) ) {
		return $wp_error ? new WP_Error( 'Post ID is required' ) : false;
	}

	$_post  = get_post( $post_id );
	$exists = ap_get_qameta( $post_id );

	if ( ! is_object( $_post ) || ! isset( $_post->post_type ) || ! in_array( $_post->post_type, [ 'question', 'answer' ], true ) ) {
		return false;
	}

	$args = wp_unslash(
		wp_parse_args(
			$args, [
				'ptype' => $_post->post_type,
			]
		)
	);

	$sanitized_values = [];
	$formats          = [];

	// Include and sanitize valid fields.
	foreach ( (array) ap_qameta_fields() as $field => $type ) {
		if ( isset( $args[ $field ] ) ) {
			$value = $args[ $field ];

			if ( 'fields' === $field ) {
				$value     = maybe_serialize( array_merge( (array) $exists->$field, (array) $value ) );
				$formats[] = '%s';
			} elseif ( 'activities' === $field ) {
				$value     = maybe_serialize( $value );
				$formats[] = '%s';
			} elseif ( 'terms' === $field || 'attach' === $field ) {
				$value     = is_array( $value ) ? sanitize_comma_delimited( $value ) : (int) $value;
				$formats[] = '%s';
			} elseif ( in_array( $field, [ 'selected', 'featured', 'closed' ], true ) ) {
				$value     = (bool) $value;
				$formats[] = '%d';
			} elseif ( in_array( $field, [ 'selected_id', 'comments', 'answers', 'views', 'votes_up', 'votes_down', 'subscribers', 'flags' ], true ) ) {
				$value     = (int) $value;
				$formats[] = '%d';
			} else {
				$value     = sanitize_text_field( $value );
				$formats[] = '%s';
			}

			$sanitized_values[ $field ] = $value;
		}
	}

	global $wpdb;

	// Dont insert or update if not AnsPress CPT.
	// This check will also prevent inserting qameta for deleetd post.
	if ( ! isset( $exists->ptype ) || ! in_array( $exists->ptype, [ 'question', 'answer' ], true ) ) {
		return $wp_error ? new WP_Error( 'Not question or answer CPT' ) : false;
	}

	if ( $exists->is_new ) {
		$sanitized_values['post_id'] = (int) $post_id;

		if ( ! empty( $_post->post_author ) ) {
			$sanitized_values['roles'] = $_post->post_author;
		}

		$inserted = $wpdb->insert( $wpdb->ap_qameta, $sanitized_values, $formats ); // db call ok.
	} else {
		$inserted = $wpdb->update( $wpdb->ap_qameta, $sanitized_values, [ 'post_id' => $post_id ], $formats ); // db call ok.
	}

	if ( false !== $inserted ) {
		return $post_id;
	}

	return $wp_error ? new WP_Error( 'Unable to insert AnsPress qameta' ) : false;
}

/**
 * Delete qameta row.
 *
 * @param  integer $post_id Post ID.
 * @return integer|false
 */
function ap_delete_qameta( $post_id ) {
	global $wpdb;
	return $wpdb->delete( $wpdb->ap_qameta, [ 'post_id' => $post_id ], [ '%d' ] ); // db call ok, db cache ok.
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

	$qameta = $wpdb->get_row( $wpdb->prepare( "SELECT qm.*, p.post_type as ptype FROM {$wpdb->posts} p LEFT JOIN {$wpdb->ap_qameta} qm ON qm.post_id = p.ID WHERE p.ID = %d", $post_id ), ARRAY_A ); // db call ok.

	// If null then append is_new.
	if ( empty( $qameta['post_id'] ) ) {
		$qameta = [ 'is_new' => true ];
	}

	$qameta = wp_parse_args( $qameta, ap_qameta_fields() );

	$qameta['votes_net']  = $qameta['votes_up'] + $qameta['votes_down'];
	$qameta['activities'] = maybe_unserialize( $qameta['activities'] );

	if ( empty( $qameta['activities'] ) ) {
		$qameta['activities'] = [];
	}

	$qameta['fields'] = maybe_unserialize( $qameta['fields'] );
	$qameta           = (object) $qameta;

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
	if ( ! in_array( $post_arr['post_type'], [ 'question', 'answer' ], true ) || isset( $post_arr['ap_qameta_wrapped'] ) ) {
		return $post;
	}

	$exist = true;
	foreach ( ap_qameta_fields() as $fields_name => $val ) {
		if ( ! isset( $post_arr[ $fields_name ] ) ) {
			$exist = false;
			break;
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

		$post->terms      = maybe_unserialize( $post->terms );
		$post->activities = maybe_unserialize( $post->activities );

		$post->votes_net = $post->votes_up - $post->votes_down;
	}

	return $post;
}

/**
 * Update count of answers in post meta.
 *
 * @param  integer $question_id Question ID.
 * @param  integer $counts Custom count value to update.
 * @return boolean|false
 * @since  3.1.0
 */
function ap_update_answers_count( $question_id, $counts = false, $update_time = true ) {
	if ( false === $counts ) {
		$counts = ap_count_published_answers( $question_id );
	}

	$args = [ 'answers' => $counts ];

	if ( $update_time ) {
		$args['last_updated'] = current_time( 'mysql' );
	}

	return ap_insert_qameta( $question_id, $args );
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
 * @since  4.1.2 Insert activity to log.
 * @since  4.1.8 Close question after selecting an answer.
 */
function ap_set_selected_answer( $question_id, $answer_id ) {
	// Log to activity table.
	ap_activity_add(
		array(
			'q_id'   => $question_id,
			'a_id'   => $answer_id,
			'action' => 'selected',
		)
	);

	ap_insert_qameta(
		$answer_id, [
			'selected'     => 1,
			'last_updated' => current_time( 'mysql' ),
		]
	);

	$q_args = array(
		'selected_id'  => $answer_id,
		'last_updated' => current_time( 'mysql' ),
	);

	// Close question if enabled in option.
	if ( ap_opt( 'close_selected' ) ) {
		$q_args['closed'] = 1;
	}

	ap_insert_qameta( $question_id, $q_args );
	$ret = ap_update_answer_selected( $answer_id );

	$_post = ap_get_post( $answer_id );

	/**
	 * Trigger right after selecting an answer.
	 *
	 * @param WP_Post $_post       WordPress post object.
	 * @param object  $answer_id   Answer ID.
	 *
	 * @since 4.1.8 Moved from ajax-hooks.php.
	 */
	do_action( 'ap_select_answer', $_post, $question_id );

	return $ret;
}

/**
 * Clear selected answer from a question.
 *
 * @param  integer $question_id Question ID.
 * @return integer|false
 * @since  3.1.0
 * @since  4.1.2 Insert activity to `ap_activity` table.
 * @since  4.1.8 Reopen question after unselecting.
 */
function ap_unset_selected_answer( $question_id ) {
	$qameta = ap_get_qameta( $question_id );

	// Log to activity table.
	ap_activity_add( array(
		'q_id'   => $question_id,
		'a_id'   => $qameta->selected_id,
		'action' => 'unselected',
	) );

	// Clear selected column from answer qameta.
	ap_insert_qameta(
		$qameta->selected_id, [
			'selected'     => 0,
			'last_updated' => current_time( 'mysql' ),
		]
	);

	$ret = ap_insert_qameta( $question_id, array(
		'selected_id'  => '',
		'last_updated' => current_time( 'mysql' ),
		'closed'       => 0,
	));

	$_post = ap_get_post( $qameta->selected_id );

	/**
	 * Action triggered after an answer is unselected as best.
	 *
	 * @param WP_Post $_post       Answer post object.
	 * @param WP_Post $question_id Question id.
	 *
	 * @since unknown
	 * @since 4.1.8 Moved from ajax-hooks.php.
	 */
	do_action( 'ap_unselect_answer', $_post, $question_id );

	return $ret;
}

/**
 * Update views count of qameta.
 *
 * @param  integer|false $post_id Question ID.
 * @param  integer|false $views   Passing view will replace existing value else increment existing.
 * @return integer
 * @since  3.1.0
 */
function ap_update_views_count( $post_id, $views = false ) {
	if ( false === $views ) {
		$qameta = ap_get_qameta( $post_id );
		$views  = (int) $qameta->views + 1;
	}

	ap_insert_qameta( $post_id, [ 'views' => $views ] );
	return $views;
}

/**
 * Updates last_active field of qameta.
 *
 * @param  integer $post_id Post ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_update_last_active( $post_id ) {
	return ap_insert_qameta( $post_id, [ 'last_updated' => current_time( 'mysql' ) ] );
}

/**
 * Set flags count for a qameta
 *
 * @param  integer $post_id Post ID.
 * @param  integer $count   Custom count.
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
	$count = ap_count_post_flags( $post_id );
	ap_insert_qameta( $post_id, [ 'flags' => $count ] );

	return $count;
}

/**
 * Updates selected field of qameta.
 *
 * @param  integer $answer_id Answer ID.
 * @param  boolean $selected Is selected.
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
 * @param  integer $count Custom count to update.
 * @return integer|false
 * @since  3.1.0
 */
function ap_update_subscribers_count( $post_id, $count = false ) {
	if ( false === $count ) {
		$count = ap_subscribers_count( 'question', $post_id );
	}

	ap_insert_qameta( $post_id, [ 'subscribers' => $count ] );

	return $count;
}


/**
 * Updates terms of qameta.
 *
 * @param  integer $question_id Question ID.
 * @return integer|false
 * @since  3.1.0
 */
function ap_update_qameta_terms( $question_id ) {
	$terms = [];

	if ( taxonomy_exists( 'question_category' ) ) {
		$categories = get_the_terms( $question_id, 'question_category' );

		if ( $categories ) {
			$terms = $terms + $categories;
		}
	}

	if ( taxonomy_exists( 'question_tag' ) ) {
		$tags = get_the_terms( $question_id, 'question_tag' );

		if ( $tags ) {
			$terms = $terms + $tags;
		}
	}

	if ( taxonomy_exists( 'question_label' ) ) {
		$labels = get_the_terms( $question_id, 'question_label' );

		if ( $labels ) {
			$terms = $terms + $labels;
		}
	}

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
 *
 * @param  integer $post_id Question ID.
 * @return boolean
 */
function ap_set_featured_question( $post_id ) {
	return ap_insert_qameta( $post_id, [ 'featured' => 1 ] );
}

/**
 * Unset a question as a featured.
 *
 * @param  integer $post_id Question ID.
 * @return boolean
 */
function ap_unset_featured_question( $post_id ) {
	return ap_insert_qameta( $post_id, [ 'featured' => 0 ] );
}

/**
 * Update post attachment IDs.
 *
 * @param  integer $post_id Post ID.
 * @return array
 */
function ap_update_post_attach_ids( $post_id ) {
	global $wpdb;

	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} where post_type = 'attachment' AND post_parent = %d", $post_id ) );

	$insert = ap_insert_qameta( (int) $post_id, [ 'attach' => $ids ] );
	return $ids;
}

/**
 * Update activities of a qameta.
 *
 * @param  integer $post_id    Post ID.
 * @param  array   $activities Activities.
 * @return boolean|integer
 */
function ap_update_post_activities( $post_id, $activities = array() ) {
	return ap_insert_qameta(
		$post_id, [
			'activities'   => $activities,
			'last_updated' => current_time( 'mysql' ),
		]
	);
}

/**
 * Update post activity meta.
 *
 * @param  object|integer $post                 Question or answer.
 * @param  string         $type                 Activity type.
 * @param  integer        $user_id              ID of user doing activity.
 * @param  boolean        $append_to_question   Append activity to question.
 * @param  boolean|string $date                 Activity date in mysql timestamp format.
 * @return boolean
 * @since  2.4.7
 * @deprecated 4.1.2  Use @see ap_activity_add(). Activities are inserted in `ap_activity` table.
 */
function ap_update_post_activity_meta( $post, $type, $user_id, $append_to_question = false, $date = false ) {
	if ( empty( $post ) ) {
		return false;
	}

	if ( false === $date ) {
		$date = current_time( 'mysql' );
	}

	$post_o   = ap_get_post( $post );
	$meta_val = compact( 'type', 'user_id', 'date' );

	// Append to question activity meta. So that it can shown in question list.
	if ( 'answer' === $post_o->post_type && $append_to_question ) {
		$_post         = ap_get_post( $post_o->post_parent );
		$meta          = $_post->activities;
		$meta['child'] = $meta_val;
		ap_update_post_activities( $post_o->post_parent, $meta );
	}

	return ap_update_post_activities( $post_o->ID, $meta_val );
}

/**
 * Toggle closed
 *
 * @param  integer $post_id Question ID.
 * @return boolean
 */
function ap_toggle_close_question( $post_id ) {
	$qameta = ap_get_qameta( $post_id );
	$toggle = $qameta->closed ? 0 : 1;
	ap_insert_qameta( $post_id, [ 'closed' => $toggle ] );

	return $toggle;
}

/**
 * Get a specific post field.
 *
 * @param  string $field Post field name.
 * @param  mixed  $_post Post ID, Object or null.
 * @return mixed
 * @since  4.0.0
 * @since  4.1.5 Serialize field value if column is `fields`.
 */
function ap_get_post_field( $field, $_post = null ) {
	$_post = ap_get_post( $_post );

	if ( isset( $_post->$field ) ) {
		// Serialize if fields column.
		if ( 'fields' === $field ) {
			return maybe_unserialize( $_post->$field );
		}

		return $_post->$field;
	}

	return '';
}

/**
 * Echo specific post field.
 *
 * @param  string              $field Post field name.
 * @param  object|integer|null $_post Post ID, Object or null.
 */
function ap_post_field( $field = null, $_post = null ) {
	echo ap_get_post_field( $field, $_post ); // xss ok.
}

/**
 * Update unpublished posts counts of a user.
 *
 * @param integer|null $user_id User ID to update.
 * @return void
 * @since 4.1.13
 */
function ap_update_user_unpublished_count( $user_id = null ) {
	global $wpdb;

	if ( null === $user_id ) {
		$user_id = get_current_user_id();
	}

	$counts = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(*) as count, post_type FROM $wpdb->posts WHERE post_status IN ('draft', 'moderate', 'pending', 'trash') AND post_author = %d AND post_type IN ('question', 'answer') GROUP BY post_type", $user_id ) );

	if ( ! empty( $counts ) ) {
		$counts = wp_list_pluck( $counts, 'count', 'post_type' );
	}

	$q_count = ! empty( $counts['question'] ) ? (int) $counts['question'] : 0;
	$a_count = ! empty( $counts['answer'] ) ? (int) $counts['answer'] : 0;

	// Update questions count.
	update_user_meta( $user_id, '__ap_unpublished_questions', $q_count );

	// Update answers count.
	update_user_meta( $user_id, '__ap_unpublished_answers', $a_count );
}
