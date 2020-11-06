<?php
/**
 * Question and answer query filters.
 *
 * @package AnsPress
 * @since 4.0.0
 */

/**
 * Query hooks
 */
class AP_QA_Query_Hooks {

	/**
	 * Alter WP_Query mysql query for question and answers.
	 *
	 * @param  array  $sql      Sql query.
	 * @param  Object $wp_query Instance.
	 * @return array
	 * @since unknown
	 * @since 4.1.7  Fixed: Session answers are included in wrong question.
	 * @since 4.1.8  Fixed: Sorting issue with best answer.
	 * @since 4.1.13 Do not include session posts to question query.
	 */
	public static function sql_filter( $sql, $wp_query ) {
		global $wpdb;

		// Do not filter if wp-admin.
		if ( is_admin() ) {
			return $sql;
		}

		if ( isset( $wp_query->query['ap_query'] ) ) {
			$sql['join']   = $sql['join'] . " LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = {$wpdb->posts}.ID";
			$sql['fields'] = $sql['fields'] . ', qameta.*, qameta.votes_up - qameta.votes_down AS votes_net';
			$post_status   = '';
			$query_status  = $wp_query->query['post_status'];

			if ( isset( $wp_query->query['ap_current_user_ignore'] ) && false === $wp_query->query['ap_current_user_ignore'] ) {
				// Build the post_status mysql query.
				if ( ! empty( $query_status ) ) {
					if ( is_array( $query_status ) ) {
						$i = 1;

						foreach ( get_post_stati() as $status ) {

							if ( in_array( $status, $wp_query->query['post_status'], true ) ) {
								$post_status .= $wpdb->posts . ".post_status = '" . $status . "'";

								if ( count( $query_status ) != $i ) {
									$post_status .= ' OR ';
								} else {
									$post_status .= ')';
								}
								$i++;
							}
						}
					} else {
						$post_status .= $wpdb->posts . ".post_status = '" . $query_status . "' ";
					}
				}

				// Replace post_status query.
				if ( is_user_logged_in() && false !== ( $pos = strpos( $sql['where'], $post_status ) ) ) {
					$pos          = $pos + strlen( $post_status );
					$author_query = $wpdb->prepare( " OR ( {$wpdb->posts}.post_author = %d AND {$wpdb->posts}.post_status IN ('private_post') ) ", get_current_user_id() );
					$sql['where'] = substr_replace( $sql['where'], $author_query, $pos, 0 );
				}
			}

			// Hack to fix WP_Query for fetching anonymous author posts.
			if ( isset( $wp_query->query['author'] ) && 0 === $wp_query->query['author'] ) {
				$sql['where'] = $sql['where'] . $wpdb->prepare( " AND {$wpdb->posts}.post_author = %d", $wp_query->query['author'] );
			}

			$ap_order_by  = isset( $wp_query->query['ap_order_by'] ) ? $wp_query->query['ap_order_by'] : 'active';
			$answer_query = isset( $wp_query->query['ap_answers_query'] );

			if ( 'answers' === $ap_order_by && ! $answer_query ) {
				$sql['orderby'] = 'IFNULL(qameta.answers, 0) DESC, ' . $sql['orderby'];
			} elseif ( 'views' === $ap_order_by && ! $answer_query ) {
				$sql['orderby'] = 'IFNULL(qameta.views, 0) DESC, ' . $sql['orderby'];
			} elseif ( 'unanswered' === $ap_order_by && ! $answer_query ) {
				$sql['orderby'] = 'IFNULL(qameta.answers, 0) ASC,' . $sql['orderby'];
			} elseif ( 'voted' === $ap_order_by ) {
				$sql['orderby'] = 'CASE WHEN IFNULL(votes_net, 0) >= 0 THEN 1 ELSE 2 END ASC, ABS(votes_net) DESC, ' . $sql['orderby'];
			} elseif ( 'unsolved' === $ap_order_by && ! $answer_query ) {
				$sql['orderby'] = "if( qameta.selected_id = '' or qameta.selected_id is null, 1, 0 ) DESC," . $sql['orderby'];
			} elseif ( 'oldest' === $ap_order_by ) {
				$sql['orderby'] = "{$wpdb->posts}.post_date ASC";
			} elseif ( 'newest' === $ap_order_by ) {
				$sql['orderby'] = "{$wpdb->posts}.post_date DESC";
			} else {
				$sql['orderby'] = 'qameta.last_updated DESC ';
			}

			// Keep featured posts on top.
			if ( ! $answer_query ) {
				$sql['orderby'] = 'CASE WHEN IFNULL(qameta.featured, 0) =1 THEN 1 ELSE 2 END ASC, ' . $sql['orderby'];
			}

			// Keep best answer to top.
			if ( $answer_query && ! $wp_query->query['ignore_selected_answer'] ) {
				$sql['orderby'] = 'case when qameta.selected = 1 then 1 else 2 end, ' . $sql['orderby'];
			}

			// Allow filtering sql query.
			$sql = apply_filters( 'ap_qa_sql', $sql );

			$wp_query->count_request = $sql;
		}

		return $sql;
	}

	/**
	 * Add qameta fields to post and prefetch metas and users.
	 *
	 * @param  array  $posts Post array.
	 * @param  object $instance QP_Query instance.
	 * @return array
	 * @since 3.0.0
	 * @since 4.1.0 Fixed: qameta fields are not appending properly.
	 */
	public static function posts_results( $posts, $instance ) {
		global $question_rendered;

		foreach ( (array) $posts as $k => $p ) {
			if ( in_array( $p->post_type, [ 'question', 'answer' ], true ) ) {
				// Convert object as array to prevent using __isset of WP_Post.
				$p_arr = (array) $p;

				// Check if ptype exists which is a qameta feild.
				if ( ! empty( $p_arr['ptype'] ) ) {
					$qameta = ap_qameta_fields();
				} else {
					$qameta = ap_get_qameta( $p->ID );
				}

				foreach ( (array) $qameta as $fields_name => $val ) {
					if ( ! isset( $p_arr[ $fields_name ] ) || empty( $p_arr[ $fields_name ] ) ) {
						$p->$fields_name = $val;
					}
				}

				// Serialize fields and activities.
				$p->activities = maybe_unserialize( $p->activities );
				$p->fields     = maybe_unserialize( $p->fields );

				$p->ap_qameta_wrapped = true;
				$p->votes_net         = $p->votes_up - $p->votes_down;

				// Unset if user cannot read.
				if ( ! ap_user_can_read_post( $p, false, $p->post_type ) ) {
					if ( $instance->is_single() && $instance->is_main_query() ) {
						$posts[ $k ] = self::imaginary_post( $p );
					} else {
						unset( $posts[ $k ] );
					}
				} else {
					$posts[ $k ] = $p;
				}
			}
		} // End foreach().

		if ( isset( $instance->query['ap_question_query'] ) || isset( $instance->query['ap_answers_query'] ) ) {
			$instance->pre_fetch();
		}

		return $posts;
	}

	/**
	 * An imaginary post.
	 *
	 * @return object
	 */
	public static function imaginary_post( $p ) {
		$_post = array(
			'ID'           => 0,
			'post_title'   => __( 'No permission', 'anspress-question-answer' ),
			'post_content' => __( 'You do not have permission to read this question.', 'anspress-question-answer' ),
			'post_status'  => $p->post_status,
			'post_type'    => 'question',
		);

		return (object) $_post;
	}

	/**
	 * Modify main query.
	 *
	 * @param array  $posts  Array of post object.
	 * @param object $query Wp_Query object.
	 * @return void|array
	 * @since 4.1.0
	 */
	public static function modify_main_posts( $posts, $query ) {
		if ( ! is_admin() && $query->is_main_query() && $query->is_search() && 'question' === get_query_var( 'post_type' ) ) {
			$query->found_posts   = 1;
			$query->max_num_pages = 1;
			$posts                = [ get_post( ap_opt( 'base_page' ) ) ];
		}

		return $posts;
	}

	/**
	 * Include all post status in single question so that we can show custom messages.
	 *
	 * @param WP_Query $query Query loop.
	 * @return void
	 * @since 4.1.4
	 * @since 4.1.5 Include future questions as well.
	 */
	public static function pre_get_posts( $query ) {
		if ( $query->is_single() && $query->is_main_query() && 'question' === get_query_var( 'post_type' ) ) {
			$query->set( 'post_status', [ 'publish', 'trash', 'moderate', 'private_post', 'future', 'ap_spam' ] );
		}
	}
}
