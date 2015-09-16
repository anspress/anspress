<?php
/**
 * All function related to AnsPress meta
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http:/wp3.in
 * @copyright 2014 Rahul Aryan
 */

/**
 * Add data to ap_meta table
 * @param  boolean         $userid    WP user id.
 * @param  null|string     $type      Meta type.
 * @param  null|string     $actionid  Action ID.
 * @param  null|string     $value     Meta value.
 * @param  null|string     $param     Meta param.
 * @param  false|timestamp $date      Meta date.
 * @return integer
 */
function ap_add_meta($userid=false, $type=null, $actionid =null, $value=null, $param = null, $date = false) {

	// Get current user id if not set.
	if ( ! $userid ) {
		$userid = get_current_user_id();
	}

	// Get current time in mysql format if not set.
	if ( ! $date ) {
		$date = current_time( 'mysql' );
	}

	global $wpdb;
	$row = $wpdb->insert(
		$wpdb->prefix . 'ap_meta',
		array(
			'apmeta_userid' 	=> $userid,
			'apmeta_type' 		=> $type,
			'apmeta_actionid' 	=> $actionid,
			'apmeta_value' 		=> maybe_serialize( $value ),
			'apmeta_param' 		=> maybe_serialize( $param ),
			'apmeta_date' 		=> $date,
		),
		array(
			'%d',
			'%s',
			'%d',
			'%s',
			'%s',
			'%s',
		)
	);

	if ( false === $row ) {
		return false;
	}

	return  $wpdb->insert_id;
}

/**
 * Update anspress meta values
 * @param  array $data
 * @param  array $where
 * @return false|integer
 */
function ap_update_meta($data, $where) {
	global $wpdb;

	$meta_key = ap_meta_key( $where );

	$update = $wpdb->update(
		$wpdb->prefix . 'ap_meta', $data, $where
	);

	if ( $update !== false ) {
		wp_cache_delete( $meta_key, 'ap_meta' ); }

	return $update;
}

/**
 * Delete ap_meta row
 * @param  false|array $where wp_db where clause
 * @param  integer     $id    if meta id is known then it can be passed
 * @return boolean
 */
function ap_delete_meta($where=false, $id=false) {
	global $wpdb;

	if ( false !== $id ) {
		$where = array( 'apmeta_id' => $id ); }

	$meta_key = ap_meta_key( $where );

	if ( is_array( $where ) ) {
		$delete = $wpdb->delete(
			$wpdb->prefix . 'ap_meta', $where
		); } else {
		return false; }

		if ( $delete ) {
			wp_cache_delete( $meta_key, 'ap_meta' ); }

		return $delete;
}

/**
 * Return meta key for caching purpose
 * @param  array $where
 * @return string
 */
function ap_meta_key($where) {
	$meta_key = '';
	if ( isset( $where['apmeta_type'] ) ) {
		$meta_key .= $where['apmeta_type']; }

	if ( isset( $where['apmeta_userid'] ) ) {
		$meta_key .= '_'.$where['apmeta_userid']; } else {
		$meta_key .= '_null'; }

		if ( isset( $where['apmeta_actionid'] ) ) {
			$meta_key .= '_'.$where['apmeta_actionid']; } else {
			$meta_key .= '_null'; }

			return $meta_key;
}

function ap_get_meta($where) {
	global $wpdb;

	$where_string = '';
	$i = 1;
	foreach ( $where as $k => $w ) {
		$where_string .= $k .' = "'.$w.'" ';

		if ( count( $where ) != $i ) {
			$where_string .= 'AND '; }

		$i++;
	}

	$query = 'SELECT * FROM '.$wpdb->prefix.'ap_meta WHERE ' .$where_string;

	$meta_key = md5( $query );

	$cache = wp_cache_get( $meta_key, 'ap_meta' );

	if ( $cache !== false ) {
		return $cache; }

	$row = $wpdb->get_row( $query, ARRAY_A );
	wp_cache_set( $meta_key, $row, 'ap_meta' );

	return $row;
}

/* get the total count by type and actionid */
/**
 * @param string $type
 */
function ap_meta_total_count($type, $actionid=false, $userid = false, $group = false, $value = false) {
	global $wpdb;

	$where_query = '';
	$group_query = '';

	if ( $actionid ) {
		$where_query .= "apmeta_actionid = $actionid"; }

	if ( $userid ) {
		$where_query .= " apmeta_userid = $userid"; }

	if ( $group ) {
		$group_query .= 'GROUP BY '.$group; }

	if ( $value ) {
		$where_query .= " apmeta_value = '$value'"; }

	$query = 'SELECT IFNULL(count(*), 0) FROM ' .$wpdb->prefix ."ap_meta where apmeta_type = '$type' and $where_query $group_query";

	$key = md5( $query );

	$cache = wp_cache_get( $key, 'count' );

	if ( $cache !== false ) {
		return $cache; }

	$count = $wpdb->get_var( $query );

	wp_cache_set( $key, $count, 'count' );

	return $count;
}

/**
 * @param string $type
 */
function ap_meta_user_done($type, $userid = false, $actionid, $param = false, $value = false) {
	global $wpdb;

	$where = '';

	/* check if type contains OR */
	if ( strpos( $type, '||' ) !== false ) {
		$or = explode( '||', $type );
		$i = 1;
		foreach ( $or as $o ) {
			$where .= "apmeta_type = '".trim( $o )."' ";
			if ( $i != count( $or ) ) {
				$where .= ' OR '; }

			$i++;
		}
	} else {
		$where .= "apmeta_type = '$type'";
	}

	if ( $userid !== false ) {
		$where .= $wpdb->prepare( 'and apmeta_userid = %d', $userid ); }

	$query = $wpdb->prepare( 'SELECT IFNULL(count(*), 0) FROM ' .$wpdb->prefix .'ap_meta where '.$where.' and apmeta_actionid = %d ', $actionid );

	if ( $value ) {
		$query = $query. $wpdb->prepare( 'and apmeta_value = "%s"', $value ); }

	if ( $param ) {
		$query = $query. $wpdb->prepare( 'and apmeta_param = "%s"', $param ); }

	$key = md5( $query );

	$user_done = wp_cache_get( $key, 'counts' );

	if ( $user_done !== false ) {
		return $user_done; }

	$user_done = $wpdb->get_var( $query );

	wp_cache_set( $key, $user_done, 'counts' );

	return $user_done;
}

function ap_get_all_meta($args =false, $limit=10, $query = false) {
	global $wpdb;

	$where_string = '';
	$group_string = '';
	$order_string = '';

	if ( isset( $args['where'] ) ) {
		foreach ( $args['where'] as $k => $a ) {
			$compare = isset( $a['compare'] ) ? $a['compare'] : '=';
			$relation = isset( $a['relation'] ) ? $a['relation'] : 'AND';

			$where_string .= $relation. ' ';

			if ( is_array( $a['value'] ) ) {
				$val = "('".implode( "', '", $a['value'] )."')"; } else {
				$val = "'".$a['value']."'"; }

				$where_string .= $k .' '.$compare.' '.$val.' ';
		}
	}

	if ( isset( $args['group'] ) ) {
		$i = 1;
		foreach ( $args['group'] as $k => $a ) {
			$relation = isset( $a['relation'] ) ? $a['relation'] : 'AND';

			if ( $i != 1 ) {
				$group_string .= $relation. ' '; }

			$group_string .= $k .' ';
			$i++;
		}
		$group_string = 'GROUP BY '.$group_string;
	}

	if ( isset( $args['orderby'] ) ) {
		$i = 1;
		foreach ( $args['orderby'] as $k => $a ) {
			$order = isset( $a['order'] ) ? $a['order'] : 'ASC';

			$order_string .= $k .' '.$order;

			if ( $i != count( $args['orderby'] ) ) {
				$order_string .= ', '; }

			$i++;
		}
		$order_string = 'ORDER BY '.$order_string;
	}

	if ( ! $query ) {
		$query = 'SELECT *, UNIX_TIMESTAMP(apmeta_date) as unix_date FROM ' .$wpdb->prefix ."ap_meta where  1=1 $where_string $group_string $order_string LIMIT $limit"; }

	$query = apply_filters( 'ap_pre_get_all_meta_query', $query, $args );

	$key = md5( $query );
	$cache = wp_cache_get( $key, 'object' );

	if ( $cache !== false ) {
		return $cache; }

	$result = $wpdb->get_results( $query );
	wp_cache_set( $key, $result, 'object' );

	return $result;
}
