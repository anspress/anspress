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
$user_pages = array();

class AnsPress_User
{

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     */
    public function __construct()
    {
        add_action('init', array($this, 'init_actions'));
        add_filter('pre_user_query', array($this, 'follower_query'));
        add_filter('pre_user_query', array($this, 'following_query'));
        add_filter('pre_user_query', array($this, 'user_sort_by_reputation'));
        add_action('wp_ajax_ap_cover_upload', array($this, 'cover_upload'));
        add_action('wp_ajax_ap_avatar_upload', array($this, 'avatar_upload'));
        add_filter('get_avatar', array($this, 'get_avatar'), 10, 5);
        //add_filter('ap_user_menu', array($this, 'ap_user_menu_icons'));
    }

    public function init_actions()
    {
        // Register AnsPress pages
        ap_register_page('users', __('Users', 'ap'), array($this, 'users_page'));
        ap_register_page('user', __('User', 'ap'), array($this, 'user_page'), false);

        // Register user pages
        ap_register_user_page('activity', __('Activity', 'ap'), array($this, 'activity_page'));
        ap_register_user_page('profile', __('Profile', 'ap'), array($this, 'profile_page'));
        ap_register_user_page('questions', __('Questions', 'ap'), array($this, 'questions_page'));
        ap_register_user_page('answers', __('Answers', 'ap'), array($this, 'answers_page'));
    }

    public function users_page(){
        if(ap_opt('enable_users_directory'))
            include ap_get_theme_location('users/users.php');
        else
            _e('User directory is disabled.', 'ap');
    }

    public function user_page(){
        if(ap_has_users(array('ID' => ap_get_displayed_user_id() ) )){
            include ap_get_theme_location('user/user.php');
        }else{
            _e('No user found', 'ap');
        }
    }

    /**
     * Output for activity page
     * @since 2.1
     */
    public function activity_page(){        
        include ap_get_theme_location('user/activity.php');
    }

    /**
     * Output for profile page
     * @since 2.1
     */
    public function profile_page(){        
        include ap_get_theme_location('user/profile.php');
    }

    /**
     * Output for user questions page
     * @since 2.1
     */
    public function questions_page(){
        ap_get_questions(array('author' => ap_get_displayed_user_id()));
        include ap_get_theme_location('user/user-questions.php');
        wp_reset_postdata();
    }

    /**
     * Output for user answers page
     * @since 2.0.1
     */
    public function answers_page(){
        ap_get_answers(array('author' => ap_get_displayed_user_id()));
        include ap_get_theme_location('user/user-answers.php');
        wp_reset_postdata();
    }

    /* For modifying WP_User_Query, if passed with a var ap_followers_query */
    public function follower_query($query)
    {
        if (isset($query->query_vars['ap_followers_query'])) {
            global $wpdb;

            $query->query_from = $query->query_from." LEFT JOIN ".$wpdb->prefix."ap_meta M ON $wpdb->users.ID = M.apmeta_userid";
            $userid = $query->query_vars['userid'];
            $query->query_where = $query->query_where." AND M.apmeta_type = 'follow' AND M.apmeta_actionid = $userid";
        }

        return $query;
    }

    public function following_query($query)
    {
        if (isset($query->query_vars['ap_following_query'])) {
            global $wpdb;

            $query->query_from = $query->query_from." LEFT JOIN ".$wpdb->prefix."ap_meta M ON $wpdb->users.ID = M.apmeta_actionid";
            $userid = $query->query_vars['userid'];
            $query->query_where = $query->query_where." AND M.apmeta_type = 'follow' AND M.apmeta_userid = $userid";
        }

        return $query;
    }

    public function user_sort_by_reputation($query)
    {
        if (isset($query->query_vars['ap_query']) && $query->query_vars['ap_query'] == 'user_sort_by_reputation') {
            global $wpdb;            
            $query->query_orderby = 'ORDER BY cast(mt1.meta_value AS DECIMAL) DESC';
        }

        return $query;
    }

    public function upload_file()
    {
        require_once ABSPATH."wp-admin".'/includes/image.php';
        require_once ABSPATH."wp-admin".'/includes/file.php';
        require_once ABSPATH."wp-admin".'/includes/media.php';
        if ($_FILES) {
            foreach ($_FILES as $file => $array) {
                if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
                    echo "upload error : ".sanitize_text_field($_FILES[$file]['error']);
                    die();
                }

                return  media_handle_upload($file, 0);
            }
        }
    }

    public function cover_upload()
    {
        if (ap_user_can_upload_cover() && wp_verify_nonce($_POST['nonce'], 'upload')) {
            $attach_id = $this->upload_file();
            $userid = get_current_user_id();
            $previous_cover = get_user_meta($userid, '_ap_cover', true);
            wp_delete_attachment($previous_cover, true);
            update_user_meta($userid, '_ap_cover', $attach_id);

            $result = array('status' => true, 'message' => __('Cover uploaded successfully.', 'ap'), 'view' => '[data-view="cover"]', 'background-image' => 'background-image:url('.ap_get_user_cover($userid).')');

            do_action('ap_after_cover_upload', $userid, $attach_id);
        } else {
            $result = array('status' => false, 'message' => __('Unable to upload cover.', 'ap'));
        }

        die(json_encode($result));
    }

    public function avatar_upload()
    {
        if (ap_user_can_upload_cover() && wp_verify_nonce($_POST['nonce'], 'upload')) {
            $attach_id = $this->upload_file();
            $userid = get_current_user_id();
            $previous_avatar = get_user_meta($userid, '_ap_avatar', true);
            wp_delete_attachment($previous_avatar, true);
            update_user_meta($userid, '_ap_avatar', $attach_id);

            $result = array('status' => true, 'message' => __('Avatar uploaded successfully.', 'ap'), 'view' => '[data-view="avatar-main"]', 'image' => get_avatar($userid, 105));

            do_action('ap_after_avatar_upload', $userid, $attach_id);
        } else {
            $result = array('status' => false, 'message' => __('Unable to upload cover.', 'ap'));
        }

        die(json_encode($result));
    }

    /**
     * Override get_avatar
     * @param  string $avatar
     * @param  integar|string $id_or_email
     * @param  string $size
     * @param  string $default
     * @param  string $alt
     * @return string
     */
    public function get_avatar($avatar, $id_or_email, $size, $default, $alt) {

        if (!empty($id_or_email)) {

            if (is_object($id_or_email)) {
                $allowed_comment_types = apply_filters('get_avatar_comment_types', array(
                    'comment'
                    ));

                if (!empty($id_or_email->comment_type) && !in_array($id_or_email->comment_type, (array)$allowed_comment_types)) {
                    return $avatar;
                }

                if (!empty($id_or_email->user_id)) {
                    $id          = (int)$id_or_email->user_id;
                    $user        = get_userdata($id);
                    if ($user) {
                        $id_or_email = $user->ID;
                    }
                }else{
                    $id_or_email = 0;
                }
            } 
            elseif (is_email($id_or_email)) {
                $u           = get_user_by('email', $id_or_email);
                $id_or_email = $u->ID;
            }

            
            $resized     = ap_get_avatar_src($id_or_email, $size);

            if ($resized) {
                return "<img data-cont='avatar_{$id_or_email}' alt='{$alt}' src='{$resized}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
            } 
        }

        $display_name = ap_user_display_name(array('user_id' => $id_or_email));

        return '<img data-cont="avatar_' . $id_or_email . '" alt="' . $alt . '" data-name="' . $display_name . '" data-height="' . $size . '" data-width="' . $size . '" data-char-count="1" class="ap-dynamic-avatar"/>';
    }

    /**
     * Set icon class for user menus
     * @param  arrat $menus
     * @return array
     * @since 2.0.1
     */
    public function ap_user_menu_icons($menus)
    {
        if (isset($menus['profile'])) {
            $menus['profile']['class'] = ap_icon('home');
        }

        if (isset($menus['questions'])) {
            $menus['questions']['class'] = ap_icon('question');
        }

        if (isset($menus['answers'])) {
            $menus['answers']['class'] = ap_icon('answer');
        }

        return $menus;
    }
}

