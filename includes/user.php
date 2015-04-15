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
function ap_register_user_page($page_slug, $page_title, $func)
{
    ap_append_to_global_var('user_pages', $page_slug, array('title' => $page_title, 'func' =>  $func));
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
        $return = '<span class="who">'.$anonymous_label.'</span>';
    }

    /**
     * FILTER: ap_user_display_name
     * Filter can be used to alter display name
     * @var string
     * @since 2.0.1
     */
    $return = apply_filters('ap_user_display_name', $return);

    if ($echo) {
        echo $return;
    } else {
        return $return;
    }
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

    $is_enabled = apply_filters('ap_user_profile_active', true);

    if(function_exists('bp_core_get_userlink') && !$is_enabled)
        return bp_core_get_userlink($user_id, false, true);
    elseif(!$is_enabled)
        return get_author_posts_url($user_id);
    
    if ($user_id == 0)
        return false;

    $user = get_userdata($user_id);

    return apply_filters('ap_user_link', ap_get_link_to(array('ap_page' => 'users', 'user' => $user->user_login)), $user_id);
}

/**
 * Output user menu
 * Extract menu from registered user pages
 * @return void
 * @since 2.0.1
 */
function ap_user_menu()
{
    global $user_pages;

    $userid = ap_user_page_user_id();
    $user_page = get_query_var('user_page');
    $user_page = $user_page ? $user_page : 'profile';

    $menus = array();

    foreach ($user_pages as $k => $args) {
        $link        = ap_user_link($userid, $k);
        $menus[$k]    = array( 'title' => $args['title'], 'link' => $link, 'order' => 10);
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
        $o = '<ul class="ap-user-menu clearfix">';
        foreach ($menus as $k => $m) {
            //if(!((isset($m['own']) && $m['own']) && $userid != get_current_user_id()))
            $class = !empty($m['class']) ? ' '.$m['class'] : '';
            $o .= '<li'.($user_page == $k ? ' class="active"' : '').'><a href="'.$m['link'].'" class="ap-user-menu-'.$k.$class.'">'.$m['title'].'</a></li>';
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

    $userid = ap_user_page_user_id();
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
    global $user_pages;

    $user_id        = ap_user_page_user_id();
    $user_page        = ap_active_user_page();

    call_user_func($user_pages[$user_page]['func']);
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
 * Return object of current user page
 * @since 	2.0
 */
function ap_user()
{
    global $ap_user;

    return $ap_user;
}

/**
 * return $User->data of active user in user page
 * @return 	object
 * @since 	2.0
 */
function ap_user_data()
{
    global $ap_user_data;

    return $ap_user_data;
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

function ap_user_template()
{
    $userid = ap_user_page_user_id();
    $user_meta = (object) array_map('ap_meta_array_map', get_user_meta($userid));

    if (is_ap_followers()) {
        $total_followers = ap_get_current_user_meta('followers');

        // how many users to show per page
        $users_per_page = ap_opt('followers_limit');

        // grab the current page number and set to 1 if no page number is set
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        // calculate the total number of pages.
        $total_pages = 1;
        $offset = $users_per_page * ($paged - 1);
        $total_pages = ceil($total_followers / $users_per_page);

        $args = array(
            'ap_followers_query' => true,
            'number' => $users_per_page,
            'userid' => ap_user_page_user_id(),
            'offset' => $offset,
        );

        // The Query
        $followers_query = new WP_User_Query($args);

        $followers = $followers_query->results;
        $base = ap_user_link(ap_user_page_user_id(), 'followers').'/%_%';
    } elseif (ap_current_user_page_is('following')) {
        $total_following = ap_get_current_user_meta('following');

        // how many users to show per page
        $users_per_page = ap_opt('following_limit');

        // grab the current page number and set to 1 if no page number is set
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        // calculate the total number of pages.
        $total_pages = 1;
        $offset = $users_per_page * ($paged - 1);
        $total_pages = ceil($total_following / $users_per_page);

        $args = array(
            'ap_following_query' => true,
            'number' => $users_per_page,
            'userid' => ap_user_page_user_id(),
            'offset' => $offset,
        );

        // The Query
        $following_query = new WP_User_Query($args);
        $following = $following_query->results;
        $base = ap_user_link(ap_user_page_user_id(), 'following').'/%_%';
    } elseif (ap_current_user_page_is('questions')) {
        $order = get_query_var('sort');
        $label = sanitize_text_field(get_query_var('label'));
        if (empty($order)) {
            $order = 'active';
        }//ap_opt('answers_sort');

        if (empty($label)) {
            $label = '';
        }

        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $question_args = array(
            'author' => ap_user_page_user_id(),
            'post_type' => 'question',
            'post_status' => 'publish',
            'showposts' => ap_opt('question_per_page'),
            'paged' => $paged,
        );

        if ($order == 'active') {
            $question_args['orderby'] = 'meta_value';
            $question_args['meta_key'] = ANSPRESS_UPDATED_META;
        } elseif ($order == 'voted') {
            $question_args['orderby'] = 'meta_value_num';
            $question_args['meta_key'] = ANSPRESS_VOTE_META;
        } elseif ($order == 'answers') {
            $question_args['orderby'] = 'meta_value_num';
            $question_args['meta_key'] = ANSPRESS_ANS_META;
        } elseif ($order == 'unanswered') {
            $question_args['orderby'] = 'meta_value';
            $question_args['meta_key'] = ANSPRESS_ANS_META;
            $question_args['meta_value'] = '0';
        } elseif ($order == 'oldest') {
            $question_args['orderby'] = 'date';
            $question_args['order'] = 'ASC';
        }

        if ($label != '') {
            $question_args['tax_query'] = array(
                array(
                    'taxonomy' => 'question_label',
                    'field' => 'slug',
                    'terms' => $label,
                ),
            );
        }

        $question_args = apply_filters('ap_user_question_args', $question_args);
        $question = new WP_Query($question_args);
    } elseif (ap_current_user_page_is('answers')) {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

        $order = get_query_var('sort');
        if (empty($order)) {
            $order = ap_opt('answers_sort');
        }

        if ($order == 'voted') {
            $ans_args = array(
                'author' => ap_user_page_user_id(),
                'ap_query' => 'answer_sort_voted',
                'post_type' => 'answer',
                'post_status' => 'publish',
                'showposts' => ap_opt('answers_per_page'),
                'paged' => $paged,
                'orderby' => 'meta_value_num',
                'meta_key' => ANSPRESS_VOTE_META,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => ANSPRESS_BEST_META,
                        'compare' => '=',
                        'value' => '1',
                    ),
                    array(
                        'key' => ANSPRESS_BEST_META,
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            );
        } elseif ($order == 'oldest') {
            $ans_args = array(
                'author' => ap_user_page_user_id(),
                'ap_query' => 'answer_sort_newest',
                'post_type' => 'answer',
                'post_status' => 'publish',
                'showposts' => ap_opt('answers_per_page'),
                'paged' => $paged,
                'orderby' => 'meta_value date',
                'meta_key' => ANSPRESS_BEST_META,
                'order' => 'ASC',
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => ANSPRESS_BEST_META,
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            );
        } else {
            $ans_args = array(
                'author' => ap_user_page_user_id(),
                'ap_query' => 'answer_sort_newest',
                'post_type' => 'answer',
                'post_status' => 'publish',
                'showposts' => ap_opt('answers_per_page'),
                'paged'    => $paged,
                'orderby'    => 'meta_value date',
                'meta_key' => ANSPRESS_BEST_META,
                'order'    => 'DESC',
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key' => ANSPRESS_BEST_META,
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            );
        }

        $ans_args = apply_filters('ap_user_answers_args', $ans_args);

        $answer = new WP_Query($ans_args);
    } elseif (ap_current_user_page_is('favorites')) {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
                'author' => ap_user_page_user_id(),
                'ap_query' => 'user_favorites',
                'post_type' => 'question',
                'post_status' => 'publish',
                'showposts' => ap_opt('answers_per_page'),
                'paged'    => $paged,
                'orderby'    => 'date',
                'order'    => 'DESC',
            );
        $args = apply_filters('ap_user_favorites_args', $args);

        $question = new WP_Query($args);
    } elseif (ap_current_user_page_is('messages')) {
        if (ap_user_page_user_id() != get_current_user_id()) {
            _e('You do not have access here', 'ap');

            return;
        }
    } elseif (ap_current_user_page_is('message')) {
        if (ap_user_page_user_id() != get_current_user_id()) {
            _e('You do not have access here', 'ap');

            return;
        }
        $message_id = get_query_var('message_id');
    } elseif (ap_current_user_page_is('badges')) {
        $user_badges = ap_get_users_all_badges(ap_user_page_user_id());
        $count_badges = ap_user_badge_count_by_badge(ap_user_page_user_id());
    }

    global $user;
    global $current_user_meta;
    include ap_get_theme_location(ap_get_current_user_page_template());

    // Restore original Post Data
    if (ap_current_user_page_is('questions') || ap_current_user_page_is('answers') || ap_current_user_page_is('favorites')) {
        wp_reset_postdata();
    }
}

function ap_cover_upload_form()
{
    if (ap_user_can_upload_cover() && ap_user_page_user_id() == get_current_user_id()) {
        ?>
		<form method="post" action="#" enctype="multipart/form-data" data-action="ap_upload_form" class="">
			<div class="ap-upload-o">
				<span class="ap-tip <?php echo ap_icon('upload') ?>" title="<?php _e('Upload cover', 'ap');
        ?>"></span>
				<input type="file" name="thumbnail" class="ap-upload-input" data-action="ap_upload_field">
			</div>
			<input type='hidden' value='<?php echo wp_create_nonce('upload');
        ?>' name='nonce' />
			<input type="hidden" name="action" id="action" value="ap_cover_upload">
		</form>
		<?php

    }
}

function ap_get_user_cover($userid, $small = false)
{
    if (!$small) {
        $image_a =  wp_get_attachment_image_src(get_user_meta($userid, '_ap_cover', true), 'ap_cover');
    } else {
        $image_a =  wp_get_attachment_image_src(get_user_meta($userid, '_ap_cover', true), 'ap_cover_small');
    }

    return $image_a[0];
}

function ap_user_cover_style($userid, $small = false)
{
    $image = ap_get_user_cover($userid);

    if ($small) {
        if ($image) {
            echo 'style="background-image:url('.ap_get_user_cover($userid, true).')"';
        } else {
            echo 'style="background-image:url('.ap_get_theme_url('images/default_cover_s.jpg').')"';
        }
    } else {
        if ($image) {
            echo 'style="background-image:url('.ap_get_user_cover($userid).')"';
        } else {
            echo 'style="background-image:url('.ap_get_theme_url('images/default_cover.jpg').')"';
        }
    }
}

function ap_avatar_upload_form()
{
    if (ap_user_page_user_id() == get_current_user_id()) {
        ?>
		<form method="post" action="#" enctype="multipart/form-data" data-action="ap_upload_form" class="">
			<div class="ap-btn ap-upload-o">
				<span class="<?php echo ap_icon('upload');
        ?>"></span>
				<input type="file" name="thumbnail" class="ap-upload-input" data-action="ap_upload_field">
			</div>
			<input type='hidden' value='<?php echo wp_create_nonce('upload');
        ?>' name='nonce' />
			<input type="hidden" name="action" id="action" value="ap_avatar_upload">
		</form>
		<?php

    }
}

function ap_edit_profile_nav()
{
    $menu = array(
        'ap-about-me' => array('name' => __('About me', 'ap'), 'title' => __('Edit your "about me" section', 'ap'), 'active' => true),
        'ap-account' => array('name' => __('Account', 'ap'), 'title' => __('Edit your account information', 'ap')),
    );
    $menu =  apply_filters('ap_edit_profile_nav', $menu);
    ?>
	<ul class="ap-edit-profile-nav ap-nav">
		<?php
            foreach ($menu as $k => $m) {
                echo '<li><a href="#'.$k.'" data-load="ap-profile-edit-fields"'.(isset($m['active']) ? ' class="active"' : '').' title="'.$m['title'].'">'.$m['name'].'</a></li>';
            }
    ?>
	</ul>
	<?php

}
function ap_edit_profile_form()
{
    if (!is_my_profile()) {
        return;
    }

    global $current_user_meta;
    global $user;
    ?>
		<form method="POST" data-action="ap-edit-profile" action="">
			<?php do_action('ap_edit_profile_fields', $user, $current_user_meta);
    ?>
			<button class="btn ap-btn ap-success btn-submit-ask" type="submit"><?php _e('Save profile', 'ap');
    ?></button>
			<input type="hidden" name="action" value="ap_save_profile">
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce('edit_profile');
    ?>">
		</form>
	<?php

}

function ap_profile_fields_to_process()
{
    $fields = array();

    if (!empty($_POST['first_name'])) {
        $fields['first_name'] = esc_attr(sanitize_text_field($_POST['first_name']));
    }

    if (!empty($_POST['last_name'])) {
        $fields['last_name'] = esc_attr(sanitize_text_field($_POST['last_name']));
    }

    if (!empty($_POST['nick_name'])) {
        $fields['nick_name'] = esc_attr(sanitize_text_field($_POST['nick_name']));
    }

    if (!empty($_POST['display_name'])) {
        $fields['display_name'] = esc_attr(sanitize_text_field($_POST['display_name']));
    }

    if (!empty($_POST['url'])) {
        $fields['url'] = esc_url($_POST['url']);
    }

    if (!empty($_POST['facebook'])) {
        $fields['facebook'] = esc_url($_POST['facebook']);
    }

    if (!empty($_POST['twitter'])) {
        $fields['twitter'] = esc_url($_POST['twitter']);
    }

    if (!empty($_POST['google'])) {
        $fields['google'] = esc_url($_POST['google']);
    }

    if (!empty($_POST['password'])) {
        $fields['password'] = sanitize_text_field($_POST['password']);
    }

    if (!empty($_POST['description'])) {
        $fields['description'] = sanitize_text_field($_POST['description']);
    }

    return $fields;
}

function ap_profile_fields_validation()
{
    $error = array();
    if (!empty($_POST['password']) && empty($_POST['password1'])) {
        if ($_POST['password'] != $_POST['password1']) {
            $error['has_error'] = true;
            $error['password1'] = __('The passwords you entered do not match.  Your password was not updated.', 'ap');
        }
    }

    $error = apply_filters('ap_profile_fields_validation', $error);

    return $error;
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

function ap_get_resized_avatar($id_or_email, $size = 32, $default = false)
{
    $upload_dir = wp_upload_dir();
    $file_url = $upload_dir['baseurl'].'/avatar/'.$size;

    if ($default) {
        $image_meta =  wp_get_attachment_metadata(ap_opt('default_avatar'), 'thumbnail');
    } else {
        $image_meta =  wp_get_attachment_metadata(get_user_meta($id_or_email, '_ap_avatar', true), 'thumbnail');
    }

    if ($image_meta === false || empty($image_meta)) {
        return false;
    }

    $path =  get_attached_file(get_user_meta($id_or_email, '_ap_avatar', true));

    $orig_file_name = basename($path);

    $orig_dir = str_replace('/'.$orig_file_name, '', $orig_file_name);

    //$file = $upload_dir['basedir'].'/'.$orig_dir.'/'.$image_meta['sizes']['thumbnail']['file'];
    //$file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $file);

    $avatar_dir = $upload_dir['basedir'].'/avatar/'.$size;
    $avatar_dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $avatar_dir);

    if (!file_exists($upload_dir['basedir'].'/avatar')) {
        mkdir($upload_dir['basedir'].'/avatar', 0777);
    }

    if (!file_exists($avatar_dir)) {
        mkdir($avatar_dir, 0777);
    }

    if (!file_exists($avatar_dir.'/'.$orig_file_name)) {
        $image_new = $avatar_dir.'/'.$orig_file_name;
        ap_smart_resize_image($path, null, $size, $size, false, $image_new, false, false, 100);
    }

    return $file_url.'/'.$orig_file_name;
}

function ap_user_profile_meta($echo = true)
{
    $user_id        = ap_user_page_user_id();
    $ap_user        = ap_user();
    $ap_user_data    = ap_user_data();

    $metas = array();

    $metas['display_name'] = '<span class="ap-user-name">'.$ap_user_data->display_name.'</span>';
    $metas['url'] = '<span class="ap-user-url '.ap_icon('link').'">'.ap_get_current_user_meta('user_url').'</span>';

    /**
     * FILTER: ap_user_profile_meta
     * Can be used to alter user profile meta
     * @var string
     */
    $metas = apply_filters('ap_user_profile_meta', $metas);

    $output = '';

    if (!empty($metas) && is_array($metas) && count($metas) > 0) {
        $output .= '<div class="ap-user-profile-meta">';
        foreach ($metas as $meta) {
            $output .= $meta.' ';
        }
        $output .= '</div>';
    }

    if ($echo) {
        echo $output;
    } else {
        return $output;
    }
}

function ap_profile_user_stats_counts($echo = true)
{
    $user_id        = ap_user_page_user_id();
    $ap_user        = ap_user();
    $ap_user_data    = ap_user_data();

    $metas = array();
    $metas['reputation'] = '<a href="'.ap_user_link($user_id, 'reputation').'"><b data-view="ap-reputation">'.ap_get_reputation($user_id, true).'</b><span>'.__('Points', 'ap').'</span></a>';
    $metas['followers'] = '<a href="'.ap_user_link($user_id, 'followers').'"><b data-view="ap-followers">'.ap_get_current_user_meta('followers').'</b><span>'.__('Followers', 'ap').'</span></a>';
    $metas['following'] = '<a href="'.ap_user_link($user_id, 'following').'"><b data-view="ap-following">'.ap_get_current_user_meta('following').'</b><span>'.__('Following', 'ap').'</span></a>';

    /**
     * FILTER: ap_profile_user_stats_counts
     * Can be used to alter user profile stats counts
     * @var string
     */
    $metas = apply_filters('ap_profile_user_stats_counts', $metas);

    $output = '';

    if (!empty($metas) && is_array($metas) && count($metas) > 0) {
        $output .= '<ul class="ap-user-counts ap-ul-inline clearfix">';
        foreach ($metas as $meta) {
            $output .= '<li>'.$meta.'</li>';
        }
        $output .= '</ul>';
    }

    if ($echo) {
        echo $output;
    } else {
        return $output;
    }
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