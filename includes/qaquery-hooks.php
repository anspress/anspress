<?php

class AP_QA_Query_Hooks{

	public static function sql_filter($sql, $query){
		global $wpdb;

		if ( isset( $query->query['ap_query'] ) ) {
			$sql['join'] = $sql['join'] ." LEFT JOIN {$wpdb->ap_qameta} qameta ON qameta.post_id = $wpdb->posts.ID";
			$sql['fields'] = $sql['fields'] . ", qameta.* ";
		}

		return $sql;
	}

	public static function posts_results( $posts, $query ){

		foreach( (array) $posts as $k => $p ) {
			if( !in_array( $p->post_type, [ 'question', 'answer'] ) ) {
				return;
			}

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
			
			foreach ( $defaults as $pkey => $value) {
				if( !isset($p->$pkey) || empty( $p->$pkey ) ){
					$p->$pkey = $value;
				}
			}

			$p->votes_net = $p->votes_up + $p->votes_down;

			$posts[ $k ] = (object) $p;
		}

		return $posts;
	}
}