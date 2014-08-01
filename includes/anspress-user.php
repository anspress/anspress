<?php
/**
 * AnsPress.
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */


class AP_User {

	/**
	 * Instance of this class.
	 */
	protected static $instance = null;

	 /**
     * Return an instance of this class.
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {
        
        // If the single instance hasn't been set, set it now.
        if (null == self::$instance) {
            self::$instance = new self;
        }
        
        return self::$instance;
    }
	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 */
	private function __construct() {
		add_filter( 'pre_user_query', array($this, 'follower_query') );
		//add_filter( 'pre_user_query', array($this, 'following_query') );
	}
	
	/* For modifying WP_User_Query, if passed with a var is_followers */
	public function follower_query ($query) {
		if(isset($query->query_vars['ap_followers_query'])){
			global $wpdb;
		
			$query->query_from = $query->query_from." JOIN ".$wpdb->prefix."ap_meta M ON $wpdb->users.ID = M.apmeta_userid";
			$userid = $query->query_vars['userid'];
			$query->query_where = $query->query_where." AND M.apmeta_type = 'follow' AND M.apmeta_actionid = $userid";
		}
		return $query;
	}
}


function ap_get_user_answers_list($user_id, $limit = 5, $title_limit = 50){
	$ans_args =array(
		'post_type' 		=> 'answer',
		'post_status' 		=> 'publish',
		'author' 			=> $user_id,
		'showposts' 		=> $limit,
	);
	
	$answers = get_posts($ans_args);
	
	$o = '<ul class="ap-user-answers-list">';
	foreach ($answers as $ans){
		$question = get_post($ans->post_parent);
		if(isset($question->post_title)){
			$o .= '<li class="clearfix">';
			$o .= '<div class="ap-mini-counts">';
			$o .= ap_count_ans_meta($ans->ID);
			$o .= '</div>';
			$o .= '<a class="ap-answer-title answer-hyperlink" href="'. get_permalink($ans->ID) .'">'.ap_truncate_chars($question->post_title, $title_limit).'</a>';
			$o .= '</li>';
		}
	}
	$o .= '</ul>';
	return $o;
}

/* Display the list of question of a user */
function ap_get_user_question_list($user_id, $limit = 5, $title_limit = 50){
	$q_args =array(
		'post_type' 		=> 'question',
		'post_status' 		=> 'publish',
		'author' 			=> $user_id,
		'showposts' 		=> $limit,
	);
	
	$questions = get_posts($q_args);
	
	$o = '<ul class="ap-user-answers-list">';
	foreach ($questions as $q){
		$o .= '<li class="clearfix">';
		$o .= '<div class="ap-mini-counts">';
		$o .= ap_count_ans_meta($q->ID);
		$o .= '</div>';
		$o .= '<a class="ap-answer-title answer-hyperlink" href="'. get_permalink($q->ID) .'">'.ap_truncate_chars($q->post_title, $title_limit).'</a>';
		$o .= '</li>';
	}
	$o .= '</ul>';
	return $o;
}

function ap_user_display_name($id = false){
	if(!$id)
		$id = get_the_author_meta('ID');
	
	if ($id > 0){
		$user = get_userdata($id);
		return '<span class="who"><a href="'.ap_user_link($id).'">'.$user->display_name.'</a></span>';
	}
	
	return '<span class="who">'.__('Anonymous', 'ap').'</span>';
}

function ap_user_link($userid=false, $sub = false){
	if(!$userid)
		$userid = get_the_author_meta('ID');
		
	$user = get_userdata($userid);
	return get_permalink( ap_opt('base_page') ).'user/'.$user->user_login. ($sub ? '/'.$sub : '');
}

function ap_user_menu(){
	$userid = ap_get_user_page_user();
	$user_page = get_query_var('user_page');
	$user_page = $user_page ? $user_page : 'profile';
	
	$menus = array(
		'profile' => array( 'name' => __('Profile', 'ap'), 'link' => ap_user_link($userid), 'icon' => 'ap-icon-user'),
		'questions' => array( 'name' => __('Questions', 'ap'), 'link' => ap_user_link($userid, 'questions'), 'icon' => 'ap-icon-question'),
		'answers' => array( 'name' => __('Answers', 'ap'), 'link' => ap_user_link($userid, 'answers'), 'icon' => 'ap-icon-answer'),
		'activity' => array( 'name' => __('Activity', 'ap'), 'link' => ap_user_link($userid, 'activity'), 'icon' => 'ap-icon-history'),
		'favorites' => array( 'name' => __('Favorites', 'ap'), 'link' => ap_user_link($userid, 'favorites'), 'icon' => 'ap-icon-star'),
		'followers' => array( 'name' => __('Followers', 'ap'), 'link' => ap_user_link($userid, 'followers'), 'icon' => 'ap-icon-users'),
		'following' => array( 'name' => __('Following', 'ap'), 'link' => ap_user_link($userid, 'following'), 'icon' => 'ap-icon-users'),
	);
	
	/* filter for overriding menu */
	$menus = apply_filters('ap_user_menu', $menus);
	
	$o ='<ul class="ap-user-menu clearfix">';
	foreach($menus as $k => $m){
		$o .= '<li'.( $user_page == $k ? ' class="active"' : '' ).'><a href="'. $m['link'] .'" class="'.$m['icon'].' ap-user-menu-'.$k.'">'.$m['name'].'</a></li>';
	}
	$o .= '</ul>';
	
	echo $o;
}

function ap_user_personal_menu(){
	$userid = ap_get_user_page_user();
	$user_page = get_query_var('user_page');
	$user_page = $user_page ? $user_page : 'profile';
	
	$menus = array(
		'edit_profile' => array( 'name' => __('Edit Profile', 'ap'), 'link' => ap_user_link($userid, 'edit_profile'), 'icon' => 'ap-icon-pencil'),
		'settings' => array( 'name' => __('Settings', 'ap'), 'link' => ap_user_link($userid, 'settings'), 'icon' => 'ap-icon-cog'),
		'messages' => array( 'name' => __('Messages', 'ap'), 'link' => ap_user_link($userid, 'messages'), 'icon' => 'ap-icon-mail'),
	);
	
	/* filter for overriding menu */
	$menus = apply_filters('ap_user_personal_menu', $menus);
	
	$o ='<ul class="ap-user-personal-menu ap-inline-list clearfix">';
	foreach($menus as $k => $m){
		$o .= '<li'.( $user_page == $k ? ' class="active"' : '' ).'><a href="'. $m['link'] .'" class="'.$m['icon'].' ap-user-menu-'.$k.'">'.$m['name'].'</a></li>';
	}
	$o .= '</ul>';
	
	echo $o;
}

function ap_get_current_user_page_template(){
	
	if(is_anspress()){
		$user_page = get_query_var('user_page');
		$user_page = $user_page ? $user_page : 'profile';
		
		$template = 'user-'.$user_page.'.php';
				
		return apply_filters('ap_get_current_user_page_template', $template);
	}
	return 'content-none.php';
}

function ap_user_template(){
	$userid = ap_get_user_page_user();
	$user_meta = (object)  array_map( function( $a ){ return $a[0]; }, get_user_meta($userid));
	
	if(is_ap_followers()){
		$followers_query = ap_followers_query();
		$followers = $followers_query->results;
	}
	
	include ap_get_theme_location(ap_get_current_user_page_template());
}

function ap_get_current_user_meta($meta){
	global $current_user_meta;
	
	if($meta == 'followers')
		return @$current_user_meta[AP_FOLLOWERS_META] ? $current_user_meta[AP_FOLLOWERS_META] : 0;
	
	elseif($meta == 'following')
		return @$current_user_meta[AP_FOLLOWING_META] ? $current_user_meta[AP_FOLLOWING_META] : 0;
	
	elseif(isset($current_user_meta[$meta]))
		return $current_user_meta[$meta];
		
	return false;
}

function ap_followers_query(){
	$args = array(
		'ap_followers_query' => true,
		'number' => 10,
		'userid' => ap_get_user_page_user()
	);

	// The Query
	$followers = new WP_User_Query( $args );

	return $followers;
	
}