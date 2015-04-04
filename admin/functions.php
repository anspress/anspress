<?php
/**
 * Common AnsPress admin functions
 *
 * @link http://wp3.in
 * @since unknown
 *
 * @package AnsPress
 */



// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}


/**
 * Return the total numbers of post
 * @param  string         $post_type
 * @param  boolean|string $meta_type
 * @return array
 * @since  2.0.0-alpha2
 */
function ap_total_posts_count($post_type = 'question', $meta_type =  false)
{

	global $wpdb;

	$type = "";
	
	if('question' == $post_type)
		$type = "p.post_type = 'question'";
	elseif('answer' == $post_type)
		$type = "p.post_type = 'answer'";
	else
		$type = "(p.post_type = 'question' OR p.post_type = 'answer')";

	$meta = "";
	$join = "";
	
	if($meta_type){
		$meta = "AND m.apmeta_type='$meta_type'";
		$join = "INNER JOIN ".$wpdb->prefix."ap_meta m ON p.ID = m.apmeta_actionid";
	}

	$where = "WHERE $type $meta";
	
	$where = apply_filters( 'ap_total_posts_count', $where );
	
	$query = "SELECT count(*) as count, p.post_status FROM $wpdb->posts p $join $where GROUP BY p.post_status";
	
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

/**
 * Return number of flagged posts
 * @return object
 * @since unknown
 */
function ap_flagged_posts_count(){
	return ap_total_posts_count('both', 'flag');
}


/**
 * Register anspress option tab and fields
 * @param  string 	$group_slug  	slug for links
 * @param  string 	$group_title 	Page title
 * @param  array 	$fields 		fields array.    
 * @return void
 * @since 2.0.0-alpha2
 */
function ap_register_option_group($group_slug, $group_title, $fields){
	$fields = apply_filters( 'ap_option_group_'.$group_slug, $fields );
	ap_append_to_global_var('ap_option_tabs', $group_slug , array('title' => $group_title, 'fields' =>  $fields));
}

/**
 * Output option tab nav
 * @return void
 * @since 2.0.0-alpha2
 */
function ap_options_nav(){
	global $ap_option_tabs;
	$active = (isset($_REQUEST['option_page'])) ? $_REQUEST['option_page'] : 'general' ;

	$menus = array();


	foreach($ap_option_tabs as $k => $args){
		$link 		= admin_url( "admin.php?page=anspress_options&option_page={$k}");
		$menus[$k] 	= array( 'title' => $args['title'], 'link' => $link);
	}

	/**
	 * FILTER: ap_option_tab_nav
	 * filter is applied before showing option tab navigation
	 * @var array
	 * @since  2.0.0-alpha2
	 */
	$menus = apply_filters('ap_option_tab_nav', $menus);
	

	$o ='<ul id="ap_opt_nav" class="nav nav-tabs">';
	foreach($menus as $k => $m){
		$class = !empty($m['class']) ? ' '. $m['class'] : '';
			$o .= '<li'.( $active == $k ? ' class="active"' : '' ).'><a href="'. $m['link'] .'" class="ap-user-menu-'.$k.$class.'">'.$m['title'].'</a></li>';
	}
	$o .= '</ul>';

	echo $o;
}

/**
 * Display fields group options. Uses AnsPress_Form to renders fields.
 * @return void
 * @since 2.0.0-alpha2
 */
function ap_option_group_fields(){
	global $ap_option_tabs;
	$active = (isset($_REQUEST['option_page'])) ? sanitize_text_field($_REQUEST['option_page']) : 'general' ;

	if(empty($ap_option_tabs) && is_array($ap_option_tabs))
		return;

	$fields =  $ap_option_tabs[$active]['fields'];

	$args = array(
        'name'              => 'options_form',
        'is_ajaxified'      => false,
        'submit_button'     => __('Save options', 'ap'),
        'nonce_name'        => 'nonce_option_form',
        'fields'            => $fields,
    );

	$form = new AnsPress_Form($args);

    echo $form->get_form();
}

