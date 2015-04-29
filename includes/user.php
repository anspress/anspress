<?php
/**
 * AnsPress user
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * Register user page
 * @param  string $page_slug  slug for links
 * @param  string $page_title Page title
 * @param  callable $func Hook to run when shortcode is found.
 * @return void
 * @since 2.0.1
 */

function ap_register_user_page($page_slug, $page_title, $func, $show_in_menu = true){
    anspress()->user_pages[$page_slug] = array('title' => $page_title, 'func' => $func, 'show_in_menu' => $show_in_menu);
}
/**
 * Count user posts by post type
 * @param  int $userid
 * @param  string $post_type
 * @return int
 * @since unknown
 */
function ap_count_user_posts_by_type($userid, $post_type = 'question')
{
    global $wpdb;

    $where = get_posts_by_author_sql($post_type, true, $userid);

    $query =  "SELECT COUNT(*) FROM $wpdb->posts $where";

    $key = md5($query);
    $cache = wp_cache_get($key, 'count');

    if ($cache === false) {
        $count = $wpdb->get_var($query);
        wp_cache_set($key, $count, 'count');
    } else {
        $count = $cache;
    }

    return apply_filters('ap_count_user_posts_by_type', $count, $userid);
}

/**
 * retrive user question counts
 * @param  int $userid
 * @return int
 * @since unknown
 */
function ap_user_question_count($userid)
{
    return ap_count_user_posts_by_type($userid, $post_type = 'question');
}

/**
 * get user answer counts
 * @param  int $userid
 * @return int
 * @since unknown
 */
function ap_user_answer_count($userid)
{
    return ap_count_user_posts_by_type($userid, $post_type = 'answer');
}

function ap_user_best_answer_count($user_id)
{
    global $wpdb;
    $query = $wpdb->prepare("SELECT count(DISTINCT pm.post_id) FROM $wpdb->postmeta pm JOIN $wpdb->posts p ON (p.ID = pm.post_id) WHERE pm.meta_key = '".ANSPRESS_BEST_META."' AND pm.meta_value = 1 AND p.post_type = 'answer' AND p.post_author = %d", $user_id);

    $key = md5($query);
    $cache = wp_cache_get($key, 'count');

    if ($cache === false) {
        $count = $wpdb->get_var($query);
        wp_cache_set($key, $count, 'count');
    } else {
        $count = $cache;
    }

    return apply_filters('ap_user_best_answer_count', $count, $user_id);
}

/**
 * For user display name
 * It can be filtered for adding cutom HTML
 * @param  mixed $args
 * @return string
 * @since 0.1
 */
function ap_user_display_name($args = array())
{
    global $post;
    $defaults = array(
        'user_id'            => get_the_author_meta('ID'),
        'html'                => false,
        'echo'                => false,
        'anonymous_label'    => __('Anonymous', 'ap'),
    );

    if (!is_array($args)) {
        $defaults['user_id'] = $args;
        $args = $defaults;
    } else {
        $args = wp_parse_args($args, $defaults);
    }

    extract($args);

    if ($user_id > 0) {
        $user = get_userdata($user_id);

        if (!$html) {
            $return = $user->display_name;
        } else {
            $return = '<span class="who"><a href="'.ap_user_link($user_id).'">'.$user->display_name.'</a></span>';
        }
    } elseif ($post->post_type == 'question' || $post->post_type == 'answer') {
        $name = get_post_meta($post->ID, 'anonymous_name', true);

        if (!$html) {
            if ($name != '') {
                $return = $name;
            } else {
                $return = $anonymous_label;
            }
        } else {
            if ($name != '') {
                $return = '<span class="who">'.$name.__(' (anonymous)', 'ap').'</span>';
            } else {
                $return = '<span class="who">'.$anonymous_label.'</span>';
            }
        }
    } else {
        if (!$html) {
            $return = $anonymous_label;
        } else {
            $return = '<span class="who">'.$anonymous_label.'</span>';
        }
    }

    /**
     * FILTER: ap_user_display_name
     * Filter can be used to alter display name
     * @var string
     * @since 2.0.1
     */
    $return = apply_filters('ap_user_display_name', $return);

    if ($echo !== false) {
        echo $return;
        return;
    }

    return $return;
}

/**
 * Link to user user pages
 * @param  int $user_id 	user id
 * @param  string $sub 		page slug
 * @return string
 * @since  unknown
 */
function ap_user_link($user_id = false, $sub = false)
{

    if (!$user_id) {
        $user_id = get_the_author_meta('ID');
    }

    if($user_id <1)
        return '#';

    $is_enabled = apply_filters('ap_user_profile_active', true);

    if(function_exists('bp_core_get_userlink') && !$is_enabled)
        return bp_core_get_userlink($user_id, false, true);
    elseif(!$is_enabled)
        return get_author_posts_url($user_id);
    
    if ($user_id == 0)
        return false;

    $user = get_userdata($user_id);

    if($sub === false){
        $sub = array('ap_page' => 'user', 'user' => $user->user_login);
    }
    elseif(is_array($sub)){
       $sub['ap_page']  = 'user';
       $sub['user']     = $user->user_login;
    }
    elseif(!is_array($sub)){
        $sub = array('ap_page' => 'user', 'user' => $user->user_login, 'user_page' => $sub);
    }

    return apply_filters('ap_user_link', ap_get_link_to($sub), $user_id);
}

/**
 * Output user menu
 * Extract menu from registered user pages
 * @return void
 * @since 2.0.1
 */
function ap_user_menu()
{
    $user_pages = anspress()->user_pages;

    $userid             = ap_get_displayed_user_id();
    $active_user_page   = get_query_var('user_page');
    $active_user_page   = $active_user_page ? $active_user_page : 'profile';

    $menus = array();

    foreach ($user_pages as $k => $args) {
        $link        = ap_user_link($userid, $k);
        $menus[$k]    = array( 'slug' => $k, 'title' => $args['title'], 'link' => $link, 'order' => 10);
    }

    /**
     * FILTER: ap_user_menu
     * filter is applied before showing user menu
     * @var array
     * @since  unknown
     */
    $menus = apply_filters('ap_user_menu', $menus);

    $menus = ap_sort_array_by_order($menus);

    if (!empty($menus) && is_array($menus)) {
        $o = '<ul id="ap-user-menu" class="ap-user-menu clearfix">';
        foreach ($menus as $m) {            
            //if(!((isset($m['own']) && $m['own']) && $userid != get_current_user_id()))
            $class = !empty($m['class']) ? ' '.$m['class'] : '';
            $o .= '<li'.($active_user_page == $m['slug'] ? ' class="active"' : '').'><a href="'.$m['link'].'" class="ap-user-menu-'.$m['slug'].$class.'">'.$m['title'].'</a></li>';
        }
        $o .= '</ul>';
        echo $o;
    }
}

function ap_user_page_menu()
{
    if (!is_my_profile()) {
        return;
    }

    $userid = ap_get_displayed_user_id();
    $user_page = get_query_var('user_page');
    $user_page = $user_page ? $user_page : 'profile';

    $menus = array();

    /* filter for overriding menu */
    $menus = apply_filters('ap_user_page_menu', $menus, $userid);

    if (!empty($menus)) {
        $o = '<ul class="ap-user-personal-menu ap-inline-list clearfix">';
        foreach ($menus as $k => $m) {
            $o .= '<li'.($user_page == $k ? ' class="active"' : '').'><a href="'.$m['link'].'" class="'.$m['icon'].' ap-user-menu-'.$k.'"'.(isset($m['attributes']) ? ' '.$m['attributes'] : '').'>'.$m['name'].'</a></li>';
        }
        $o .= '</ul>';

        echo $o;
    }
}

/**
 * @return string
 */
function ap_get_current_user_page_template()
{
    $user_page = get_query_var('user_page');
    $user_page = $user_page ? $user_page : 'profile';

    $template = 'user-'.$user_page.'.php';

    return apply_filters('ap_get_current_user_page_template', $template);
}

/**
 * Output user page
 * @return void
 * @since  2.0
 */
function ap_user_page()
{
    $user_pages     = anspress()->user_pages;
    $user_id        = ap_get_displayed_user_id();
    $user_page      = ap_active_user_page();
    $callback       = $user_pages[$user_page]['func'];

    if($user_id > 0 && ((is_array($callback) && method_exists($callback[0], $callback[1])) || (!is_array($callback) && function_exists($callback)) ) )
        call_user_func($callback);
    else
        echo '<div class="ap-page-template-404">'.__('Page not found or registered.', 'ap').'</div>';
}

/**
 * Get active user page
 * @return string
 * @since 2.0.1
 */
function ap_active_user_page()
{
    $user_page        = sanitize_text_field(get_query_var('user_page'));
    return  $user_page ? $user_page : 'profile';
}

/**
 * Return meta of active user in user page
 * @param  string $meta key of meta
 * @return string
 * @since 	unknown
 */
function ap_get_current_user_meta($meta)
{
    global $ap_current_user_meta;

    if ($meta == 'followers') {
        return @$ap_current_user_meta[AP_FOLLOWERS_META] ? $ap_current_user_meta[AP_FOLLOWERS_META] : 0;
    } elseif ($meta == 'following') {
        return @$ap_current_user_meta[AP_FOLLOWING_META] ? $ap_current_user_meta[AP_FOLLOWING_META] : 0;
    } elseif (isset($ap_current_user_meta[$meta])) {
        return $ap_current_user_meta[$meta];
    }

    return false;
}

function ap_check_user_profile_complete($user_id)
{
    $user_meta = array_map('ap_meta_array_map', get_user_meta($user_id));

    $required = apply_filters('ap_required_user_fields', array('first_name', 'last_name', 'description'));

    if (count(array_diff(array_values($required), array_keys($user_meta))) == 0) {
        return true;
    }

    return false;
}

function ap_check_if_photogenic($user_id)
{
    $user_meta = array_map('ap_meta_array_map', get_user_meta($user_id));

    $required = apply_filters('ap_check_if_photogenic', array('_ap_cover', '_ap_avatar'));

    if (count(array_diff(array_values($required), array_keys($user_meta))) == 0) {
        return true;
    }

    return false;
}

/**
 * Display users page tab
 * @return void
 */
function ap_users_tab(){
    $active = isset($_GET['ap_sort']) ? $_GET['ap_sort'] : 'reputation';
    
    $link = '?ap_sort=';

    
    ?>
    <ul class="ap-questions-tab ap-ul-inline clearfix" role="tablist">
        <?php if(!ap_opt('disable_reputation')): ?>
            <li class="<?php echo $active == 'reputation' ? ' active' : ''; ?>"><a href="<?php echo $link.'reputation'; ?>"><?php _e('Reputation', 'ap'); ?></a></li>
        <?php endif; ?>
        <li class="<?php echo $active == 'newest' ? ' active' : ''; ?>"><a href="<?php echo $link.'newest'; ?>"><?php _e('Newest', 'ap'); ?></a></li>
        <?php 
            /**
             * ACTION: ap_users_tab
             * Used to hook into users page tab
             * @since 2.1.0
             */
            do_action('ap_users_tab', $active); 
        ?>
    </ul>
    <?php
}

/**
 * echo ID of currently displaying user
 * @return integer WordPress user ID
 * @since 2.1
 */
function ap_displayed_user_id(){
    echo ap_get_displayed_user_id();
}
    /**
     * Return ID of currently displaying user
     * @return integer WordPress user ID
     * @since 2.1
     */
    function ap_get_displayed_user_id(){
        $user_id =  (int)get_query_var('ap_user_id');
        
        if($user_id > 0)
            return $user_id;

        return 0;
    }


/**
 * Retrive image url
 * @param  integer $size
 * @param  boolean $default
 * @return string
 * @since  0.0.1
 */
function ap_get_avatar_src($user_id, $size = 'thumbnail', $default = false) {
    if ($default) {
        $image      = wp_get_attachment_image_src(ap_opt('default_avatar') , 'thumbnail');
    } 
    else {
        $image      = wp_get_attachment_image_src(get_user_meta($user_id, '__ap_avatar', true) , array(
            $size,
            $size
            ));
    }
    
    if ($image === false || !is_array($image) || empty($image[0])) {
        return false;
    }
    
    return $image[0];
}

/**
 * User's top posts tab
 * @return void
 * @since 2.1
 */
function ap_user_top_posts_tab(){
    $active = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'answers';
    
    $link = '?tab=';
    ?>
    <?php printf(__('Top %s', 'ap'), $active); ?>
    <ul id="ap-user-posts-tab" class="ap-flat-tab ap-ul-inline clearfix" role="tablist">
        <li class="<?php echo $active == 'answers' ? ' active' : ''; ?>"><a href="<?php echo $link.'answers'; ?>"><?php _e('Answers', 'ap'); ?></a></li>
        <li class="<?php echo $active == 'questions' ? ' active' : ''; ?>"><a href="<?php echo $link.'questions'; ?>"><?php _e('Questions', 'ap'); ?></a></li>
        <?php 
            /**
             * ACTION: ap_users_tab
             * Used to hook into users page tab
             * @since 2.1.0
             */
            do_action('ap_users_tab', $active); 
        ?>
    </ul>
    <?php
}

/**
 * Display user meta
 * @param   boolean         $html  for html output
 * @param   false|integer   $user_id  User id, if empty then post author witll be user
 * @param   boolen          $echo
 * @return  string
 */
function ap_user_display_meta($html = false, $user_id = false, $echo = false)
{
    if (false === $user_id) 
        $user_id = get_the_author_meta('ID');   

    $metas = array();

    $metas['display_name'] = '<span class="ap-user-meta ap-user-meta-display_name">'. ap_user_display_name(array('html' => true)) .'</span>';

    /**
     * FILTER: ap_user_display_meta_array
     * Can be used to alter user display meta
     * @var array
     */
    $metas = apply_filters('ap_user_display_meta_array', $metas, $user_id);

    $output = '';

    if (!empty($metas) && is_array($metas) && count($metas) > 0) {
        $output .= '<span class="ap-user-meta">';
        foreach ($metas as $meta) {
            $output .= $meta.' ';
        }
        $output .= '</span>';
    }

    if ($echo) {
        echo $output;
    } else {
        return $output;
    }
}