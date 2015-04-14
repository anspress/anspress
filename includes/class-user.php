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
        ap_register_page('users', __('Users', 'ap'), array($this, 'users_page'));

        add_filter('pre_user_query', array($this, 'follower_query'));
        add_filter('pre_user_query', array($this, 'following_query'));
        add_action('wp_ajax_ap_cover_upload', array($this, 'cover_upload'));
        add_action('wp_ajax_ap_avatar_upload', array($this, 'avatar_upload'));
        add_action('after_setup_theme', array($this, 'cover_size'));
        add_action('ap_edit_profile_fields', array($this, 'user_fields'), 10, 2);
        add_action('wp_ajax_ap_save_profile', array($this, 'ap_save_profile'));
        add_action('pre_user_query', array($this, 'sort_pre_user_query'));
        //add_filter('avatar_defaults', array($this, 'default_avatar'), 10);
        //add_filter('get_avatar', array($this, 'get_avatar'), 10, 5);
        //add_filter( 'default_avatar_select', array($this, 'default_avatar_select'));

        ap_register_user_page('profile', __('Profile', 'ap'), array('AnsPress_User_Page_Profile', 'output'));
        ap_register_user_page('questions', __('Questions', 'ap'), array('AnsPress_User_Page_Questions', 'output'));
        ap_register_user_page('answers', __('Answers', 'ap'), array('AnsPress_User_Page_Answers', 'output'));
        //ap_register_user_page('favorites', __('Favorites', 'ap'), array('AnsPress_User_Page_Favorites', 'output'));

        add_filter('ap_user_menu', array($this, 'ap_user_menu_icons'));
    }

    public function users_page(){        
        include ap_get_theme_location('users/users.php');
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

    public function upload_file()
    {
        require_once ABSPATH."wp-admin".'/includes/image.php';
        require_once ABSPATH."wp-admin".'/includes/file.php';
        require_once ABSPATH."wp-admin".'/includes/media.php';
        if ($_FILES) {
            foreach ($_FILES as $file => $array) {
                if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
                    echo "upload error : ".$_FILES[$file]['error'];
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

    public function cover_size()
    {
        add_image_size('ap_cover', ap_opt('cover_width'), ap_opt('cover_height'), array( 'top', 'center' ), true);
        add_image_size('ap_cover_small', ap_opt('cover_width_small'), ap_opt('cover_height'), array( 'top', 'center' ), true);
    }

    public function user_fields($user, $meta)
    {
        ?>
		<div class="form-groups">
			<div class="ap-fom-group-label"><?php _e('Name', 'ap');
        ?></div>
			<div class="form-group">
				<label for="username" class="ap-form-label"><?php _e('User name', 'ap') ?></label>
				<div class="no-overflow">
				<?php echo'<input type="text" name="'.$user->data->user_login.'" id="'.$user->data->user_login.'" class="form-control" placeholder="'.$user->data->user_login.'" disabled /> '?>
				</div>
			</div>
			<div class="form-group">
				<label for="first_name" class="ap-form-label"><?php _e('First name', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="first_name" id="first_name" value="<?php echo @$meta['first_name'];
        ?>" class="form-control" placeholder="<?php _e('Your first name, i.e. Rahul', 'ap');
        ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="last_name" class="ap-form-label"><?php _e('Last name', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="last_name" id="last_name" value="<?php echo @$meta['last_name'];
        ?>" class="form-control" placeholder="<?php _e('Your last name, i.e. Aryan', 'ap');
        ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="nick_name" class="ap-form-label"><?php _e('Nickname', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="nick_name" id="nick_name" value="<?php echo @$meta['nickname'];
        ?>" class="form-control" placeholder="<?php _e('Your nick name, i.e. nerdaryan', 'ap');
        ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="display_name" class="ap-form-label"><?php _e('Display Name', 'ap') ?></label>
				<div class="no-overflow">
					<select name="display_name" id="display_name"><br/>
						<?php
                        $public_display = array();
        $public_display['display_nickname'] = $user->nickname;
        $public_display['display_username'] = $user->user_login;

        if (!empty($user->first_name)) {
            $public_display['display_firstname'] = $user->first_name;
        }

        if (!empty($user->last_name)) {
            $public_display['display_lastname'] = $user->last_name;
        }

        if (!empty($user->first_name) && !empty($user->last_name)) {
            $public_display['display_firstlast'] = $user->first_name.' '.$user->last_name;
            $public_display['display_lastfirst'] = $user->last_name.' '.$user->first_name;
        }

        foreach ($public_display as $id => $item) {
            echo '<option '.selected($user->display_name, $item).'>'.$item.'</option>';
        }

        ?>
					</select>
				</div>
			</div>
		</div>
		<div class="form-groups">
			<div class="form-group">
				<label for="description" class="ap-form-label"><?php _e('About', 'ap') ?></label>
				<div class="no-overflow">
					<textarea type="text" name="description" id="description" class="form-control" placeholder="<?php _e('About you', 'ap');
        ?>"><?php echo esc_textarea(@$meta['description']);
        ?></textarea>
				</div>
			</div>
		</div>
		<div class="form-groups">
			<div class="ap-fom-group-label"><?php _e('Contact Information', 'ap');
        ?></div>
			<div class="form-group">
				<label for="url" class="ap-form-label"><?php _e('Website', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="url" id="url" value="<?php echo $user->data->user_url;
        ?>" class="form-control" placeholder="<?php _e('http://anspress.io', 'ap');
        ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="facebook" class="ap-form-label"><?php _e('Facebook', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="facebook" id="facebook" value="<?php echo @$meta['facebook'];
        ?>" class="form-control" placeholder="<?php _e('i.e. http://facebook.com/openwp', 'ap');
        ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="twitter" class="ap-form-label"><?php _e('Twitter', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="twitter" id="twitter" value="<?php echo @$meta['twitter'];
        ?>" class="form-control" placeholder="<?php _e('i.e. https://twitter.com/openwp', 'ap');
        ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="google" class="ap-form-label"><?php _e('Google+', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="google" id="twitter" value="<?php echo @$meta['google'];
        ?>" class="form-control" placeholder="<?php _e('i.e. https://plus.google.com/+OpenwpCom', 'ap');
        ?>" />
				</div>
			</div>
		</div>
		<div class="form-groups">
			<div class="ap-fom-group-label"><?php _e('Account', 'ap');
        ?></div>
			<div class="form-group">
				<label for="email" class="ap-form-label"><?php _e('Email', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="email" id="email" value="<?php echo $user->data->user_email;
        ?>" class="form-control" placeholder="<?php _e('myemail@mydomain.com', 'ap');
        ?>" disabled />
				</div>
			</div>
			<div class="form-group">
				<label for="password" class="ap-form-label"><?php _e('Password', 'ap') ?></label>
				<div class="no-overflow">
					<input type="password" name="password" id="password" value="" class="form-control" placeholder="<?php _e('Your password', 'ap');
        ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="password1" class="ap-form-label"><?php _e('Repeat password', 'ap') ?></label>
				<div class="no-overflow">
					<input type="password" name="password1" id="password1" value="" class="form-control" placeholder="<?php _e('Repeat your password.', 'ap');
        ?>" />
				</div>
			</div>
		</div>
		<?php

    }

    public function ap_save_profile()
    {
        global $current_user, $wp_roles;
        get_currentuserinfo();

        if (is_user_logged_in() && wp_verify_nonce($_POST['nonce'], 'edit_profile')) {
            $validation = ap_profile_fields_validation();

            if (isset($validation['has_error'])) {
                $result =  array(
                    'status' => 'validation_falied',
                    'message' => __('Failed to update, please check form.', 'ap'),
                    'error' => $validation,
                );
                die(json_encode($result));
            }

            $fields = ap_profile_fields_to_process();

            if (isset($fields['password'])) {
                wp_update_user(array( 'ID' => $current_user->ID, 'user_pass' => esc_attr($_POST['password']) ));
            }

            if (isset($fields['first_name'])) {
                update_user_meta($current_user->ID, 'first_name', $fields['first_name']);
            }

            if (isset($fields['last_name'])) {
                update_user_meta($current_user->ID, 'last_name', $fields['last_name']);
            }

            if (isset($fields['nick_name'])) {
                update_user_meta($current_user->ID, 'nickname', $fields['nick_name']);
            }

            if (isset($fields['display_name'])) {
                wp_update_user(array('ID' => $current_user->ID, 'display_name' => $fields['display_name']));
            }

            if (isset($fields['url'])) {
                update_user_meta($current_user->ID, 'user_url', $fields['url']);
            }

            if (isset($fields['facebook'])) {
                update_user_meta($current_user->ID, 'facebook', $fields['facebook']);
            }

            if (isset($fields['twitter'])) {
                update_user_meta($current_user->ID, 'twitter', $fields['twitter']);
            }

            if (isset($fields['description'])) {
                update_user_meta($current_user->ID, 'description', $fields['description']);
            }

            if (isset($fields['google'])) {
                update_user_meta($current_user->ID, 'google', $fields['google']);
            }

            do_action('ap_save_profile', $current_user, $fields);

            $result =  array(
                'status' => true,
                'message' => __('Successfully updated your profile.', 'ap'),
            );
        } else {
            $result =  array(
                'status' => false,
                'message' => __('Failed to save profile.', 'ap'),
            );
        }
        die(json_encode($result));
    }

    public function sort_pre_user_query($query)
    {
        if (isset($query->query_vars['ap_query']) && $query->query_vars['ap_query'] == 'sort_points') {
            global $wpdb;
            $query->query_orderby = 'ORDER BY CAST('.$wpdb->usermeta.'.meta_value as DECIMAL) DESC';
        }
    }

    public function get_avatar($avatar, $id_or_email, $size, $default, $alt)
    {
        if (!empty($id_or_email)) {
            if (is_object($id_or_email)) {
                $allowed_comment_types = apply_filters('get_avatar_comment_types', array( 'comment' ));
                if (! empty($id_or_email->comment_type) && ! in_array($id_or_email->comment_type, (array) $allowed_comment_types)) {
                    return false;
                }

                if (! empty($id_or_email->user_id)) {
                    $id = (int) $id_or_email->user_id;
                    $user = get_userdata($id);
                    if ($user) {
                        $id_or_email = $user->ID;
                    }
                }
            } elseif (is_email($id_or_email)) {
                $u = get_user_by('email', $id_or_email);
                $id_or_email = $u->ID;
            }

            $resized = ap_get_resized_avatar($id_or_email, $size);

            if ($resized) {
                return "<img alt='{$alt}' src='{$resized}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
            }

            return $avatar;
        }
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

