<?php

class AP_QA_Query_Hooks{

	public static function sql_filter($sql, $args) {
		global $wpdb;

		if ( isset( $args->query['ap_query'] ) ) {
			$sql['join'] = $sql['join'] ." LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = $wpdb->posts.ID";
			$sql['fields'] = $sql['fields'] . ', qameta.*, qameta.votes_up - qameta.votes_down AS votes_net';

			$ap_sortby = isset( $args->query['ap_sortby'] ) ? $args->query['ap_sortby'] : 'active';
			$answer_query = isset( $args->query['ap_answers_query'] );

			if ( 'answers' == $ap_sortby && ! $answer_query ) {
				$sql['orderby'] = 'IFNULL(qameta.answers, 0) DESC, ' . $sql['orderby'];
			} elseif ( 'views' == $ap_sortby && ! $answer_query ) {
				$sql['orderby'] = 'IFNULL(qameta.views, 0) DESC, ' . $sql['orderby'];
			} elseif ( 'unanswered' == $ap_sortby && ! $answer_query ) {
				$sql['orderby'] = 'IFNULL(qameta.answers, 0) ASC,' . $sql['orderby'];
			} elseif ( 'voted' == $ap_sortby ) {
				$sql['orderby'] = 'CASE WHEN IFNULL(votes_net, 0) >= 0 THEN 1 ELSE 2 END ASC, ABS(votes_net) DESC, ' . $sql['orderby'];
			} elseif ( 'unsolved' == $ap_sortby && ! $answer_query ) {
				$sql['orderby'] = "if( qameta.selected_id = '' or qameta.selected_id is null, 1, 0 ) ASC," . $sql['orderby'];
			} elseif ( 'oldest' == $ap_sortby ) {
				$sql['orderby'] = "{$wpdb->posts}.post_date ASC";
			} elseif ( 'newest' == $ap_sortby ) {
				$sql['orderby'] = "{$wpdb->posts}.post_date DESC";
			} else {
				$sql['orderby'] = "COALESCE(qameta.last_updated, {$wpdb->posts}.post_modified) DESC, " . $sql['orderby'];
			}

			// Keep featured posts on top.
			if ( ! $answer_query ) {
				$sql['orderby'] = 'IFNULL(qameta.featured, 0) DESC, ' . $sql['orderby'];
			}

			// Keep best answer to top.
			if ( $answer_query ) {
				$sql['orderby'] = 'qameta.selected <> 1 , ' . $sql['orderby'];
			}
		}

		return $sql;
	}

	public static function posts_results( $posts, $instance ) {
		foreach ( (array) $posts as $k => $p ) {
			// Convert object as array to prevent using __isset of WP_Post.
			$p_arr = (array) $p;

			if ( in_array( $p_arr['post_type'], [ 'question', 'answer' ] ) ) {
				foreach ( ap_qameta_fields() as $fields_name => $val ) {
					if ( ! isset( $p_arr[ $fields_name ] ) || empty( $p_arr[ $fields_name ] ) ) {
						$p->$fields_name = $val;
					}

					// Serialize terms and activities.
					$p->terms = maybe_unserialize( $p->terms );
					$p->activities = maybe_unserialize( $p->activities );

					$p->ap_qameta_wrapped = true;
					$p->votes_net = $p->votes_up - $p->votes_down;
					$posts[ $k ] = $p;
				}
			}
		}
		return $posts;
	}
}
