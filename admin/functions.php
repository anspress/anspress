<?php


function ap_flagged_posts_count(){

	global $wpdb;

	$where = "WHERE (p.post_type = 'question' OR p.post_type = 'answer') AND m.apmeta_type='flag'";
	
	$where = apply_filters( 'ap_moderate_count_where', $where );
	
	$query = "SELECT count(*) as count, p.post_status FROM $wpdb->posts p INNER JOIN ".$wpdb->prefix."ap_meta m ON p.ID = m.apmeta_actionid $where GROUP BY p.post_status";
	
	$cache_key = md5( $query );

	$count = wp_cache_get( $cache_key, 'counts');
	
	if ( false !== $count )
		return $count;
		
	$count = $wpdb->get_results( $query, ARRAY_A);
	
	$counts = array();
	foreach ( get_post_stati() as $state )
		$counts[$state] = 0;		
	$counts['total'] = 0;
	foreach ( (array) $count as $row ){
		$counts[$row['post_status']] = $row['count'];
		$counts['total'] += $row['count'];
	}	
	wp_cache_set( $cache_key, (object)$counts, 'counts' );

	return (object)$counts;
}