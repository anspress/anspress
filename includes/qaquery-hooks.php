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
			} else {
				$sql['orderby'] = "COALESCE(qameta.last_updated, {$wpdb->posts}.post_modified) ASC, " . $sql['orderby'];
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

	public static function posts_results( $posts, $query ) {

		foreach ( (array) $posts as $k => $p ) {
			if ( in_array( $p->post_type, [ 'question', 'answer' ] ) ) {
				$defaults = array(
					'post_id' => $p->ID,
					'selected' => false,
					'selected_id' => 0,
					'comments' => 0,
					'answers' => 0,
					'ptype' => 'question',
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
				);

				foreach ( $defaults as $pkey => $value ) {
					if ( ! isset($p->$pkey ) || empty( $p->$pkey ) ) {
						$p->$pkey = $value;
					}
				}

				$p->votes_net = $p->votes_up - $p->votes_down;
				$posts[ $k ] = (object) $p;
			}
		}

		return $posts;
	}
}
