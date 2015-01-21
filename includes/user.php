<?php
/**
 * AnsPress user
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-2.0+
 * @link      http://wp3.in
 * @copyright 2014 Rahul Aryan
 */

$user_pages = array();

class AnsPress_User {

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
		add_filter( 'pre_user_query', array($this, 'following_query') );
		add_action('wp_ajax_ap_cover_upload', array($this, 'cover_upload'));
		add_action('wp_ajax_ap_avatar_upload', array($this, 'avatar_upload'));
		add_action( 'after_setup_theme', array($this, 'cover_size') );
		add_action( 'ap_edit_profile_fields', array($this, 'user_fields'), 10, 2 );
		add_action( 'wp_ajax_ap_save_profile', array($this, 'ap_save_profile'));
		add_action( 'pre_user_query', array($this, 'sort_pre_user_query') );
		//add_filter('avatar_defaults', array($this, 'default_avatar'), 10);
		add_filter( 'get_avatar', array($this, 'get_avatar'), 10, 5);		
		//add_filter( 'default_avatar_select', array($this, 'default_avatar_select'));
		
		ap_register_user_page('profile', __('Profile', 'ap'), array('AnsPress_User_Page_Profile', 'output'));
		ap_register_user_page('questions', __('Questions', 'ap'), array('AnsPress_User_Page_Questions', 'output'));
		ap_register_user_page('answers', __('Answers', 'ap'), array('AnsPress_User_Page_Answers', 'output'));
		//ap_register_user_page('favorites', __('Favorites', 'ap'), array('AnsPress_User_Page_Favorites', 'output'));

		add_filter('ap_user_menu', array($this, 'ap_user_menu_icons') );
	}
	
	/* For modifying WP_User_Query, if passed with a var ap_followers_query */
	public function follower_query ($query) {
		if(isset($query->query_vars['ap_followers_query'])){
			global $wpdb;
		
			$query->query_from = $query->query_from." LEFT JOIN ".$wpdb->prefix."ap_meta M ON $wpdb->users.ID = M.apmeta_userid";
			$userid = $query->query_vars['userid'];
			$query->query_where = $query->query_where." AND M.apmeta_type = 'follow' AND M.apmeta_actionid = $userid";
		}
		return $query;
	}
	
	public function following_query ($query) {
		if(isset($query->query_vars['ap_following_query'])){
			global $wpdb;
		
			$query->query_from = $query->query_from." LEFT JOIN ".$wpdb->prefix."ap_meta M ON $wpdb->users.ID = M.apmeta_actionid";
			$userid = $query->query_vars['userid'];
			$query->query_where = $query->query_where." AND M.apmeta_type = 'follow' AND M.apmeta_userid = $userid";
		}
		return $query;
	}
	
	public function upload_file(){
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
		if ($_FILES) {
			foreach ($_FILES as $file => $array) {
				if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
					echo "upload error : " . $_FILES[$file]['error'];
					die();
				}
				return  media_handle_upload( $file, 0 );
			}   
		}
	}
	
	public function cover_upload(){
		if(ap_user_can_upload_cover() && wp_verify_nonce( $_POST['nonce'], 'upload' )){
			$attach_id = $this->upload_file();
			$userid = get_current_user_id();	
			$previous_cover = get_user_meta($userid, '_ap_cover', true);
			wp_delete_attachment( $previous_cover, true );
			update_user_meta($userid, '_ap_cover', $attach_id);

			$result = array('status' => true, 'message' => __('Cover uploaded successfully.', 'ap'), 'view' => '[data-view="cover"]', 'background-image' => 'background-image:url('.ap_get_user_cover($userid).')');
			
			do_action('ap_after_cover_upload', $userid, $attach_id);
			
	  }else{
			$result = array('status' => false, 'message' => __('Unable to upload cover.', 'ap'));
	  }
	  
	  die(json_encode($result));
	}
	
	public function avatar_upload(){
		if(ap_user_can_upload_cover() && wp_verify_nonce( $_POST['nonce'], 'upload' )){
			$attach_id = $this->upload_file();
			$userid = get_current_user_id();	
			$previous_avatar = get_user_meta($userid, '_ap_avatar', true);
			wp_delete_attachment( $previous_avatar, true );
			update_user_meta($userid, '_ap_avatar', $attach_id);

			$result = array('status' => true, 'message' => __('Avatar uploaded successfully.', 'ap'), 'view' => '[data-view="avatar-main"]', 'image' => get_avatar( $userid, 105 ));
			
			do_action('ap_after_avatar_upload', $userid, $attach_id);
	  }else{
		$result = array('status' => false, 'message' => __('Unable to upload cover.', 'ap'));
	  }
	  
	  die(json_encode($result));
	}
	
	public function cover_size(){
		add_image_size( 'ap_cover', ap_opt('cover_width'), ap_opt('cover_height'), array( 'top', 'center' ), true );
		add_image_size( 'ap_cover_small', ap_opt('cover_width_small'), ap_opt('cover_height'), array( 'top', 'center' ), true );
	}
	
	public function user_fields($user, $meta){
		?>
		<div class="form-groups">
			<div class="ap-fom-group-label"><?php _e('Name', 'ap'); ?></div>
			<div class="form-group">
				<label for="username" class="ap-form-label"><?php _e('User name', 'ap') ?></label>
				<div class="no-overflow">
				<?php echo'<input type="text" name="'.$user->data->user_login.'" id="'.$user->data->user_login.'" class="form-control" placeholder="'.$user->data->user_login.'" disabled /> '?>
				</div>
			</div>
			<div class="form-group">
				<label for="first_name" class="ap-form-label"><?php _e('First name', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="first_name" id="first_name" value="<?php echo @$meta['first_name']; ?>" class="form-control" placeholder="<?php _e('Your first name, i.e. Rahul', 'ap'); ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="last_name" class="ap-form-label"><?php _e('Last name', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="last_name" id="last_name" value="<?php echo @$meta['last_name']; ?>" class="form-control" placeholder="<?php _e('Your last name, i.e. Aryan', 'ap'); ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="nick_name" class="ap-form-label"><?php _e('Nickname', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="nick_name" id="nick_name" value="<?php echo @$meta['nickname']; ?>" class="form-control" placeholder="<?php _e('Your nick name, i.e. nerdaryan', 'ap'); ?>" />
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
						 
						if ( !empty($user->first_name) )
							$public_display['display_firstname'] = $user->first_name;
						 
						if ( !empty($user->last_name) )
							$public_display['display_lastname'] = $user->last_name;
						 
						if ( !empty($user->first_name) && !empty($user->last_name) ) {
							$public_display['display_firstlast'] = $user->first_name . ' ' . $user->last_name;
							$public_display['display_lastfirst'] = $user->last_name . ' ' . $user->first_name;
						}
						 
						foreach ( $public_display as $id => $item ) {
							echo '<option '.selected( $user->display_name, $item ).'>'.$item.'</option>';
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
					<textarea type="text" name="description" id="description" class="form-control" placeholder="<?php _e('About you', 'ap'); ?>"><?php echo esc_textarea(@$meta['description']); ?></textarea>
				</div>
			</div>
		</div>
		<div class="form-groups">
			<div class="ap-fom-group-label"><?php _e('Contact Information', 'ap'); ?></div>
			<div class="form-group">
				<label for="url" class="ap-form-label"><?php _e('Website', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="url" id="url" value="<?php echo $user->data->user_url; ?>" class="form-control" placeholder="<?php _e('http://wp3.in', 'ap'); ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="facebook" class="ap-form-label"><?php _e('Facebook', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="facebook" id="facebook" value="<?php echo @$meta['facebook']; ?>" class="form-control" placeholder="<?php _e('i.e. http://facebook.com/openwp', 'ap'); ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="twitter" class="ap-form-label"><?php _e('Twitter', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="twitter" id="twitter" value="<?php echo @$meta['twitter']; ?>" class="form-control" placeholder="<?php _e('i.e. https://twitter.com/openwp', 'ap'); ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="google" class="ap-form-label"><?php _e('Google+', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="google" id="twitter" value="<?php echo @$meta['google']; ?>" class="form-control" placeholder="<?php _e('i.e. https://plus.google.com/+OpenwpCom', 'ap'); ?>" />
				</div>
			</div>
		</div>
		<div class="form-groups">
			<div class="ap-fom-group-label"><?php _e('Account', 'ap'); ?></div>
			<div class="form-group">
				<label for="email" class="ap-form-label"><?php _e('Email', 'ap') ?></label>
				<div class="no-overflow">
					<input type="text" name="email" id="email" value="<?php echo $user->data->user_email; ?>" class="form-control" placeholder="<?php _e('myemail@mydomain.com', 'ap'); ?>" disabled />
				</div>
			</div>
			<div class="form-group">
				<label for="password" class="ap-form-label"><?php _e('Password', 'ap') ?></label>
				<div class="no-overflow">
					<input type="password" name="password" id="password" value="" class="form-control" placeholder="<?php _e('Your password', 'ap'); ?>" />
				</div>
			</div>
			<div class="form-group">
				<label for="password1" class="ap-form-label"><?php _e('Repeat password', 'ap') ?></label>
				<div class="no-overflow">
					<input type="password" name="password1" id="password1" value="" class="form-control" placeholder="<?php _e('Repeat your password.', 'ap'); ?>" />
				</div>
			</div>
		</div>
		<?php
	}
	
	public function ap_save_profile(){
		global $current_user, $wp_roles;
		get_currentuserinfo();
		
		if(is_user_logged_in() && wp_verify_nonce( $_POST['nonce'], 'edit_profile' )){

			$validation = ap_profile_fields_validation();
			
			if(isset($validation['has_error'])){
				$result =  array(
					'status' => 'validation_falied',
					'message' => __('Failed to update, please check form.', 'ap'),
					'error' => $validation,
				);
				die(json_encode($result));
			}
			
			$fields = ap_profile_fields_to_process();
			
			if (isset($fields['password']))
				wp_update_user( array( 'ID' => $current_user->ID, 'user_pass' => esc_attr( $_POST['password'] ) ) );
			
			if (isset($fields['first_name']))
				update_user_meta( $current_user->ID, 'first_name', $fields['first_name']);
				
			if ( isset($fields['last_name']) )
				update_user_meta($current_user->ID, 'last_name', $fields['last_name']);
			
			if ( isset($fields['nick_name']) )
				update_user_meta($current_user->ID, 'nickname', $fields['nick_name']);
			
			if ( isset($fields['display_name']) )
				wp_update_user(array('ID' => $current_user->ID, 'display_name' => $fields['display_name']));
		
			if(isset($fields['url']))
				update_user_meta( $current_user->ID, 'user_url', $fields['url'] );
			
			if(isset($fields['facebook']))
				update_user_meta( $current_user->ID, 'facebook', $fields['facebook'] );
			
			if(isset($fields['twitter']))
				update_user_meta( $current_user->ID, 'twitter', $fields['twitter'] );
			
			if(isset($fields['description']))
				update_user_meta( $current_user->ID, 'description', $fields['description'] );
			
			if(isset($fields['google']))
				update_user_meta( $current_user->ID, 'google', $fields['google'] );
			
			do_action('ap_save_profile', $current_user, $fields);
			
			$result =  array(
				'status' => true,
				'message' => __('Successfully updated your profile.', 'ap'),
			);
		}else{
			$result =  array(
				'status' => false,
				'message' => __('Failed to save profile.', 'ap'),
			);
		}
		die(json_encode($result));
	}
	
	public function sort_pre_user_query($query){
		if(isset($query->query_vars['ap_query']) && $query->query_vars['ap_query'] == 'sort_points'){
			global $wpdb;
			$query->query_orderby = 'ORDER BY CAST('.$wpdb->usermeta.'.meta_value as DECIMAL) DESC';
		}
	}
	
	public function get_avatar($avatar, $id_or_email, $size, $default, $alt){

		if ( !empty($id_or_email) ) {
			
			if(is_object($id_or_email)){
                $allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
	                if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) )
	                        return false;
	
	                if ( ! empty( $id_or_email->user_id ) ) {
	                        $id = (int) $id_or_email->user_id;
	                        $user = get_userdata($id);
	                        if ( $user )
	                           $id_or_email = $user->ID;
	                }

			}elseif(is_email($id_or_email)){
				$u = get_user_by('email', $id_or_email);
				$id_or_email = $u->ID;
			}

			$resized = ap_get_resized_avatar($id_or_email, $size);

			if($resized)
				return "<img alt='{$alt}' src='{$resized}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
			
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
		if (isset($menus['profile']))
			$menus['profile']['class'] = ap_icon('home');

		if (isset($menus['questions']))
			$menus['questions']['class'] = ap_icon('question');

		if (isset($menus['answers']))
			$menus['answers']['class'] = ap_icon('answer'); 

		return $menus;
	}

}

/**
 * Register user page 
 * @param  string $page_slug  slug for links
 * @param  string $page_title Page title
 * @param  callable $func Hook to run when shortcode is found.    
 * @return void
 * @since 2.0.1
 */
function ap_register_user_page($page_slug, $page_title, $func){
	global $user_pages;

	if(empty($user_pages) || !is_array($user_pages))
		$user_pages = array();

	$user_pages[$page_slug] = array('title' => $page_title, 'func' =>  $func);

}

/**
 * Count user posts by post type
 * @param  int $userid 
 * @param  string $post_type
 * @return int
 * @since unknown
 */
function ap_count_user_posts_by_type( $userid, $post_type = 'question' ) {
	global $wpdb;

	$where = get_posts_by_author_sql( $post_type, true, $userid );

	$query =  "SELECT COUNT(*) FROM $wpdb->posts $where";

	$key = md5($query);	
	$cache = wp_cache_get($key, 'count');

	if($cache === false){
		$count = $wpdb->get_var( $query);
		wp_cache_set($key, $count, 'count');
	}else
		$count = $cache;

  	return apply_filters( 'ap_count_user_posts_by_type', $count, $userid );
}

/**
 * retrive user question counts
 * @param  int $userid
 * @return int
 * @since unknown
 */
function ap_user_question_count($userid){
	return ap_count_user_posts_by_type( $userid, $post_type = 'question' );
}

/**
 * get user answer counts
 * @param  int $userid
 * @return int
 * @since unknown
 */
function ap_user_answer_count($userid){
	return ap_count_user_posts_by_type( $userid, $post_type = 'answer' );
}

function ap_user_best_answer_count($user_id){
	global $wpdb;
	$query = $wpdb->prepare("SELECT count(DISTINCT pm.post_id) FROM $wpdb->postmeta pm JOIN $wpdb->posts p ON (p.ID = pm.post_id) WHERE pm.meta_key = '".ANSPRESS_BEST_META."' AND pm.meta_value = 1 AND p.post_type = 'answer' AND p.post_author = %d", $user_id);

	$key = md5($query);	
	$cache = wp_cache_get($key, 'count');

	if($cache === false){
		$count = $wpdb->get_var( $query);
		wp_cache_set($key, $count, 'count');
	}else
		$count = $cache;

  	return apply_filters( 'ap_user_best_answer_count', $count, $user_id );
}


/**
 * For user display name
 * It can be filtered for adding cutom HTML
 * @param  mixed $args
 * @return string
 * @since 0.1
 */
function ap_user_display_name($args = array()){
	global $post;
	$defaults = array(
			'user_id' 			=> get_the_author_meta('ID'),
			'html' 				=> false,
			'echo' 				=> false,
			'anonymous_label'	=> __('Anonymous', 'ap')
		);

	if(!is_array($args)){
		$defaults['user_id'] = $args;
		$args = $defaults;
	}else{
		$args = wp_parse_args( $args, $defaults );		
	}

	extract($args);

	$return = '';
	
	if ($user_id > 0){
		$user = get_userdata($user_id);
		
		if(!$html)
			$return = $user->display_name;
		else	
			$return = '<span class="who"><a href="'.ap_user_link($user_id).'">'.$user->display_name.'</a></span>';

	}elseif($post->post_type =='question' || $post->post_type =='answer' ){
		
		$name = get_post_meta($post->ID, 'anonymous_name', true);
		
		if(!$html){
			if($name != '')
				$return = $name;
			else
				$return = $anonymous_label;
		}else{
			if($name != '')
				$return = '<span class="who">'.$name.__(' (anonymous)', 'ap').'</span>';
			else
				$return = '<span class="who">'.$anonymous_label.'</span>';
		}
	}else{	
		$return = '<span class="who">'.$anonymous_label.'</span>';
	}

	/**
	 * FILTER: ap_user_display_name
	 * Filter can be used to alter display name
	 * @var string
	 * @since 2.0.1
	 */
	
	$return = apply_filters('ap_user_display_name', $return );

	if($echo)
		echo $return;
	else
		return $return;
}

/**
 * Link to user user pages
 * @param  int $user_id 	user id
 * @param  string $sub 		page slug
 * @return string
 * @since  unknown
 */
function ap_user_link($user_id = false, $sub = false){
	if(!$user_id)
		$user_id = get_the_author_meta('ID');
	
	if($user_id == 0)
		return false;
		
	$user = get_userdata($user_id);
	$base = add_query_arg(array('user' => $user->user_login), get_permalink(ap_opt('user_page_id') ));
	$args = $base;

	/* TODO: REWRITE - fix when rewrite is active */
	if(get_option('permalink_structure') != ''){		
		if($sub !== false)
			$args = add_query_arg(array('user_page' => $sub), $base);		
	
	}else{
		if($sub !== false)
			$args = add_query_arg(array('user_page' => $sub), $base);
	}
	return $args ;
}

/**
 * Output user menu
 * Extract menu from registered user pages
 * @return void
 * @since 2.0.1
 */
function ap_user_menu(){
	global $user_pages;

	$userid = ap_user_page_user_id();
	$user_page = get_query_var('user_page');
	$user_page = $user_page ? $user_page : 'profile';

	$menus = array();

	foreach($user_pages as $k => $args){
		$link 		= ap_user_link($userid, $k);
		$menus[$k] 	= array( 'title' => $args['title'], 'link' => $link);
	}
	
	/*$menus = array(
		'profile' => array( 'name' => __('Profile', 'ap'), 'link' => ap_user_link($userid), 'icon' => 'ap-icon-user'),
		'questions' => array( 'name' => __('Questions', 'ap'), 'link' => ap_user_link($userid, 'questions'), 'icon' => 'ap-icon-question'),
		'answers' => array( 'name' => __('Answers', 'ap'), 'link' => ap_user_link($userid, 'answers'), 'icon' => 'ap-icon-answer'),		
		'badges' => array( 'name' => __('Badges', 'ap'), 'link' => ap_user_link($userid, 'badges'), 'icon' => 'ap-icon-badge'),		
		'favorites' => array( 'name' => __('Favorites', 'ap'), 'link' => ap_user_link($userid, 'favorites'), 'icon' => 'ap-icon-star'),
		'followers' => array( 'name' => __('Followers', 'ap'), 'link' => ap_user_link($userid, 'followers'), 'icon' => 'ap-icon-users'),
		'following' => array( 'name' => __('Following', 'ap'), 'link' => ap_user_link($userid, 'following'), 'icon' => 'ap-icon-users'),
		'edit_profile' => array( 'name' => __('Edit Profile', 'ap'), 'link' => ap_user_link($userid, 'edit_profile'), 'icon' => 'ap-icon-pencil', 'own' => true),
		//'settings' => array( 'name' => __('Settings', 'ap'), 'link' => ap_user_link($userid, 'settings'), 'icon' => 'ap-icon-cog'),		
	);*/
	
	/**
	 * FILTER: ap_user_menu
	 * filter is applied before showing user menu
	 * @var array
	 * @since  unknown
	 */
	$menus = apply_filters('ap_user_menu', $menus);
	
	$o ='<ul class="ap-user-menu clearfix">';
	foreach($menus as $k => $m){
		//if(!((isset($m['own']) && $m['own']) && $userid != get_current_user_id()))
		$class = !empty($m['class']) ? ' '. $m['class'] : '';
			$o .= '<li'.( $user_page == $k ? ' class="active"' : '' ).'><a href="'. $m['link'] .'" class="ap-user-menu-'.$k.$class.'">'.$m['title'].'</a></li>';
	}
	$o .= '</ul>';
	
	echo $o;
}

function ap_user_page_menu(){
	if(!is_my_profile())
		return;
		
	$userid = ap_user_page_user_id();
	$user_page = get_query_var('user_page');
	$user_page = $user_page ? $user_page : 'profile';
	
	$menus = array();
	
	/* filter for overriding menu */
	$menus = apply_filters('ap_user_page_menu', $menus, $userid);
	
	if(!empty($menus)){
		$o ='<ul class="ap-user-personal-menu ap-inline-list clearfix">';
		foreach($menus as $k => $m){
			$o .= '<li'.( $user_page == $k ? ' class="active"' : '' ).'><a href="'. $m['link'] .'" class="'.$m['icon'].' ap-user-menu-'.$k.'"'.(isset($m['attributes']) ? ' '.$m['attributes'] : '' ).'>'.$m['name'].'</a></li>';
		}
		$o .= '</ul>';	
	
		echo $o;
	}
}

function ap_get_current_user_page_template(){
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
function ap_user_page(){
	global $user_pages;

	$user_id 		= ap_user_page_user_id();
	$user_page 		= ap_active_user_page();

	call_user_func($user_pages[$user_page]['func']);

}

/**
 * Get active user page
 * @return string
 * @since 2.0.1
 */
function ap_active_user_page(){
	$user_page 		= sanitize_text_field(get_query_var('user_page'));
	return  $user_page ? $user_page : 'profile';
}

/**
 * Return object of current user page
 * @since 	2.0
 */
function ap_user(){
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
	
	if($meta == 'followers')
		return @$ap_current_user_meta[AP_FOLLOWERS_META] ? $ap_current_user_meta[AP_FOLLOWERS_META] : 0;
	
	elseif($meta == 'following')
		return @$ap_current_user_meta[AP_FOLLOWING_META] ? $ap_current_user_meta[AP_FOLLOWING_META] : 0;
	
	elseif(isset($ap_current_user_meta[$meta]))
		return $ap_current_user_meta[$meta];
		
	return false;
}

function ap_user_template(){
	$userid = ap_user_page_user_id();
	$user_meta = (object)  array_map( 'ap_meta_array_map', get_user_meta($userid));
	
	if(is_ap_followers()){
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
			'offset' => $offset
		);
		
		// The Query
		$followers_query = new WP_User_Query( $args );

		$followers = $followers_query->results;
		$base = ap_user_link(ap_user_page_user_id(), 'followers') . '/%_%';
	}elseif(ap_current_user_page_is('following')){

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
			'offset' => $offset
		);

		// The Query
		$following_query = new WP_User_Query( $args );
		$following = $following_query->results;
		$base = ap_user_link(ap_user_page_user_id(), 'following') . '/%_%';
		
	}elseif(ap_current_user_page_is('questions')){
		$order = get_query_var('sort');
		$label = sanitize_text_field(get_query_var('label'));
		if(empty($order ))
			$order = 'active';//ap_opt('answers_sort');
			
		if(empty($label ))
			$label = '';
			
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		
		$question_args=array(
			'author' => ap_user_page_user_id(),
			'post_type' => 'question',
			'post_status' => 'publish',
			'showposts' => ap_opt('question_per_page'),
			'paged' => $paged
		);
		
		if($order == 'active'){				
			$question_args['orderby'] = 'meta_value';
			$question_args['meta_key'] = ANSPRESS_UPDATED_META;	
			
		}elseif($order == 'voted'){
			$question_args['orderby'] = 'meta_value_num';
			$question_args['meta_key'] = ANSPRESS_VOTE_META;
		}elseif($order == 'answers'){
			$question_args['orderby'] = 'meta_value_num';
			$question_args['meta_key'] = ANSPRESS_ANS_META;
		}elseif($order == 'unanswered'){
			$question_args['orderby'] = 'meta_value';
			$question_args['meta_key'] = ANSPRESS_ANS_META;
			$question_args['meta_value'] = '0';

		}elseif($order == 'oldest'){
			$question_args['orderby'] = 'date';
			$question_args['order'] = 'ASC';
		}
		
		if ($label != ''){
			$question_args['tax_query'] = array(
				array(
					'taxonomy' => 'question_label',
					'field' => 'slug',
					'terms' => $label
				)
			);				
		}
		
		$question_args = apply_filters('ap_user_question_args', $question_args);
		$question = new WP_Query( $question_args );
	}elseif(ap_current_user_page_is('answers')){
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

		$order = get_query_var('sort');
		if(empty($order ))
			$order = ap_opt('answers_sort');

		
		if($order == 'voted'){
			$ans_args=array(
				'author' => ap_user_page_user_id(),
				'ap_query' => 'answer_sort_voted',
				'post_type' => 'answer',
				'post_status' => 'publish',
				'showposts' => ap_opt('answers_per_page'),
				'paged' => $paged,
				'orderby' => 'meta_value_num',
				'meta_key' => ANSPRESS_VOTE_META,
				'meta_query'=>array(
					'relation' => 'OR',
					array(
						'key' => ANSPRESS_BEST_META,
						'compare' => '=',
						'value' => '1'
					),
					array(
						'key' => ANSPRESS_BEST_META,
						'compare' => 'NOT EXISTS'
					)
				)
			);
		}elseif($order == 'oldest'){
			$ans_args=array(
				'author' => ap_user_page_user_id(),
				'ap_query' => 'answer_sort_newest',
				'post_type' => 'answer',
				'post_status' => 'publish',
				'showposts' => ap_opt('answers_per_page'),
				'paged' => $paged,
				'orderby' => 'meta_value date',
				'meta_key' => ANSPRESS_BEST_META,
				'order' => 'ASC',
				'meta_query'=>array(
					'relation' => 'OR',
					array(
						'key' => ANSPRESS_BEST_META,
						'compare' => 'NOT EXISTS'
					)
				)
			);
		}else{
			$ans_args=array(
				'author' => ap_user_page_user_id(),
				'ap_query' => 'answer_sort_newest',
				'post_type' => 'answer',
				'post_status' => 'publish',
				'showposts' => ap_opt('answers_per_page'),
				'paged' 	=> $paged,			
				'orderby' 	=> 'meta_value date',
				'meta_key' => ANSPRESS_BEST_META,
				'order' 	=> 'DESC',
				'meta_query'=>array(
					'relation' => 'OR',
					array(
						'key' => ANSPRESS_BEST_META,
						'compare' => 'NOT EXISTS'
					)
				)
			);
		}
		
		$ans_args = apply_filters('ap_user_answers_args', $ans_args);
		
		$answer = new WP_Query($ans_args);	
	}elseif(ap_current_user_page_is('favorites')){
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$args=array(
				'author' => ap_user_page_user_id(),
				'ap_query' => 'user_favorites',
				'post_type' => 'question',
				'post_status' => 'publish',
				'showposts' => ap_opt('answers_per_page'),
				'paged' 	=> $paged,			
				'orderby' 	=> 'date',
				'order' 	=> 'DESC'
			);
		$args = apply_filters('ap_user_favorites_args', $args);
		
		$question = new WP_Query($args);
	}elseif(ap_current_user_page_is('messages')){
		if(ap_user_page_user_id() != get_current_user_id()){
			_e('You do not have access here', 'ap');
			return;
		}
	}elseif(ap_current_user_page_is('message')){
		if(ap_user_page_user_id() != get_current_user_id()){
			_e('You do not have access here', 'ap');
			return;
		}
		$message_id = get_query_var('message_id');
	}elseif(ap_current_user_page_is('badges')){
		$user_badges = ap_get_users_all_badges(ap_user_page_user_id());
		$count_badges = ap_user_badge_count_by_badge(ap_user_page_user_id());
	}
	
	global $user;
	global $current_user_meta;
	include ap_get_theme_location(ap_get_current_user_page_template());
	
	// Restore original Post Data	
	if(ap_current_user_page_is('questions') || ap_current_user_page_is('answers') || ap_current_user_page_is('favorites'))
	wp_reset_postdata();
}

function ap_cover_upload_form(){
	if(ap_user_can_upload_cover() && ap_user_page_user_id() == get_current_user_id()){
		?>
		<form method="post" action="#" enctype="multipart/form-data" data-action="ap_upload_form" class="">
			<div class="ap-upload-o">
				<span class="ap-tip <?php echo ap_icon('upload') ?>" title="<?php _e('Upload cover', 'ap'); ?>"></span>
				<input type="file" name="thumbnail" class="ap-upload-input" data-action="ap_upload_field">
			</div>
			<input type='hidden' value='<?php echo wp_create_nonce( 'upload' ); ?>' name='nonce' />
			<input type="hidden" name="action" id="action" value="ap_cover_upload">
		</form>
		<?php
	}
}

function ap_get_user_cover($userid, $small = false){
	if(!$small)
		$image_a =  wp_get_attachment_image_src( get_user_meta($userid, '_ap_cover', true), 'ap_cover');
	else
		$image_a =  wp_get_attachment_image_src( get_user_meta($userid, '_ap_cover', true), 'ap_cover_small');
		
	return $image_a[0];
}

function ap_user_cover_style($userid, $small = false){
	$image = ap_get_user_cover($userid);
	
	if($small){
		if($image)
			echo 'style="background-image:url('.ap_get_user_cover($userid, true).')"';
		else
			echo 'style="background-image:url('.ap_get_theme_url('images/default_cover_s.jpg').')"';
	}else{
		if($image)
			echo 'style="background-image:url('.ap_get_user_cover($userid).')"';
		else
			echo 'style="background-image:url('.ap_get_theme_url('images/default_cover.jpg').')"';
	}
}

function ap_avatar_upload_form(){
	if(ap_user_page_user_id() == get_current_user_id()){
		?>
		<form method="post" action="#" enctype="multipart/form-data" data-action="ap_upload_form" class="">
			<div class="ap-btn ap-upload-o">
				<span class="<?php echo ap_icon('upload'); ?>"></span>
				<input type="file" name="thumbnail" class="ap-upload-input" data-action="ap_upload_field">
			</div>
			<input type='hidden' value='<?php echo wp_create_nonce( 'upload' ); ?>' name='nonce' />
			<input type="hidden" name="action" id="action" value="ap_avatar_upload">
		</form>
		<?php
	}
}

function ap_edit_profile_nav(){
	$menu = array(
		'ap-about-me' => array('name' => __('About me', 'ap'), 'title' => __('Edit your "about me" section', 'ap'), 'active' => true),
		'ap-account' => array('name' => __('Account', 'ap'), 'title' => __('Edit your account information', 'ap')),
	);
	$menu =  apply_filters('ap_edit_profile_nav', $menu);
	?>
	<ul class="ap-edit-profile-nav ap-nav">
		<?php 
			foreach($menu as $k => $m)
				echo '<li><a href="#'.$k.'" data-load="ap-profile-edit-fields"'.(isset($m['active']) ? ' class="active"' : '').' title="'.$m['title'].'">'. $m['name'] .'</a></li>';
		?>
	</ul>
	<?php
}
function ap_edit_profile_form(){
	if(!is_my_profile())
		return;
		
	global $current_user_meta;
	global $user;
	?>
		<form method="POST" data-action="ap-edit-profile" action="">
			<?php do_action('ap_edit_profile_fields', $user, $current_user_meta); ?>
			<button class="btn ap-btn ap-success btn-submit-ask" type="submit"><?php _e('Save profile', 'ap'); ?></button>
			<input type="hidden" name="action" value="ap_save_profile">
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'edit_profile' ); ?>">
		</form>
	<?php
}

function ap_profile_fields_to_process(){
	$fields = array();
	
	if ( !empty($_POST['first_name'] ))
		$fields['first_name'] = esc_attr(sanitize_text_field($_POST['first_name']));
		
	if ( !empty($_POST['last_name'] ))
		$fields['last_name'] = esc_attr(sanitize_text_field($_POST['last_name']));
	
	if ( !empty($_POST['nick_name'] ))
		$fields['nick_name'] = esc_attr(sanitize_text_field($_POST['nick_name']));
	
	if ( !empty($_POST['display_name'] ))
		$fields['display_name'] = esc_attr(sanitize_text_field($_POST['display_name']));
	
	if ( !empty($_POST['url'] ))
		$fields['url'] = esc_url($_POST['url']);
	
	if ( !empty($_POST['facebook'] ))
		$fields['facebook'] = esc_url($_POST['facebook']);	
	
	if ( !empty($_POST['twitter'] ))
		$fields['twitter'] = esc_url($_POST['twitter']);	
	
	if ( !empty($_POST['google'] ))
		$fields['google'] = esc_url($_POST['google']);
	
	if ( !empty($_POST['password'] ))
		$fields['password'] = sanitize_text_field($_POST['password']);
	
	if ( !empty($_POST['description'] ))
		$fields['description'] = sanitize_text_field($_POST['description']);
	
	return $fields;
}

function ap_profile_fields_validation(){
	$error = array();
	if ( !empty($_POST['password'] ) && empty( $_POST['password1'] ) ) {
        if ( $_POST['password'] != $_POST['password1']){
			$error['has_error'] = true;
            $error['password1'] = __('The passwords you entered do not match.  Your password was not updated.', 'ap');
		}
    }
	
	$error = apply_filters('ap_profile_fields_validation', $error);
	return $error;
}

function ap_check_user_profile_complete($user_id){
	$user_meta = array_map( 'ap_meta_array_map', get_user_meta($user_id));
	
	$required = apply_filters('ap_required_user_fields', array('first_name', 'last_name', 'description'));

	if(count(array_diff(array_values($required), array_keys($user_meta))) == 0)
		return true;
	
	return false;
}

function ap_check_if_photogenic($user_id){
	$user_meta = array_map( 'ap_meta_array_map', get_user_meta($user_id));
	
	$required = apply_filters('ap_check_if_photogenic', array('_ap_cover', '_ap_avatar'));

	if(count(array_diff(array_values($required), array_keys($user_meta))) == 0)
		return true;
	
	return false;
}

function ap_get_resized_avatar($id_or_email, $size = 32, $default = false){
	$upload_dir = wp_upload_dir();
	$file_url = $upload_dir['baseurl'].'/avatar/'.$size;
	
	if($default)		
		$image_meta =  wp_get_attachment_metadata( ap_opt('default_avatar'), 'thumbnail');
	else
		$image_meta =  wp_get_attachment_metadata( get_user_meta($id_or_email, '_ap_avatar', true), 'thumbnail');
		
	if($image_meta === false || empty($image_meta))
		return false;
		
	$path =  get_attached_file(get_user_meta($id_or_email, '_ap_avatar', true));

	$orig_file_name = basename($path);
	
	$orig_dir = str_replace('/'.$orig_file_name, '', $orig_file_name);
	
	//$file = $upload_dir['basedir'].'/'.$orig_dir.'/'.$image_meta['sizes']['thumbnail']['file'];
	//$file = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $file);
	
	$avatar_dir = $upload_dir['basedir'].'/avatar/'.$size;
	$avatar_dir = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $avatar_dir);
	
	if(!file_exists($upload_dir['basedir'].'/avatar'))
		mkdir($upload_dir['basedir'].'/avatar', 0777);
		
	if(!file_exists($avatar_dir))
		mkdir($avatar_dir, 0777);

	if(!file_exists($avatar_dir.'/'.$orig_file_name)){
		$image_new = $avatar_dir.'/'. $orig_file_name;
		ap_smart_resize_image($path , null, $size , $size , false , $image_new , false , false ,100 );
	}
	
	return $file_url.'/'.$orig_file_name;
}

/**
 * Display user meta
 * @param  	boolean 	$html    	for html output
 * @param  	int 		$user_id 	User id, if empty then post author witll be user
 * @param 	boolen 		$echo 
 * @return 	string
 */
function ap_user_display_meta($html = false, $user_id = false, $echo = false){

	if(!$user_id)
		$user_id = get_the_author_meta('ID');

	$metas = array();

	$metas['display_name'] = '<span class="ap-user-meta ap-user-meta-display_name">'. ap_user_display_name(array('html' => true)) .'</span>';

	/**
	 * FILTER: ap_user_display_meta_array
	 * Can be used to alter user display meta
	 * @var array
	 */
	$metas = apply_filters('ap_user_display_meta_array', $metas );



	$output = '';
	
	if(!empty($metas) && is_array($metas) && count($metas) > 0){
		$output .= '<div class="ap-user-meta">';
		foreach($metas as $meta){
			$output .= $meta. ' ';
		}
		$output .= '</div>';
	}

	if($echo)
		echo $output;
	else
		return $output;
}

function ap_user_profile_meta($echo = true)
{

	$user_id 		= ap_user_page_user_id();
	$ap_user 		= ap_user();
	$ap_user_data 	= ap_user_data();

	$metas = array();

	$metas['display_name'] = '<span class="ap-user-name">'.$ap_user_data->display_name.'</span>';
	$metas['url'] = '<span class="ap-user-url '.ap_icon('link').'">'.ap_get_current_user_meta('user_url').'</span>';

	/**
	 * FILTER: ap_user_profile_meta
	 * Can be used to alter user profile meta
	 * @var string
	 */
	$metas = apply_filters('ap_user_profile_meta', $metas );



	$output = '';
	
	if(!empty($metas) && is_array($metas) && count($metas) > 0){
		$output .= '<div class="ap-user-profile-meta">';
		foreach($metas as $meta){
			$output .= $meta. ' ';
		}
		$output .= '</div>';
	}

	if($echo)
		echo $output;
	else
		return $output;
}


function ap_profile_user_stats_counts($echo = true){
	$user_id 		= ap_user_page_user_id();
	$ap_user 		= ap_user();
	$ap_user_data 	= ap_user_data();

	$metas = array();
	$metas['points'] = '<a href="'.ap_user_link($user_id, 'points').'"><b data-view="ap-points">'.ap_get_points($user_id, true).'</b><span>'.__('Points', 'ap').'</span></a>';
	$metas['followers'] = '<a href="'.ap_user_link($user_id, 'followers').'"><b data-view="ap-followers">'.ap_get_current_user_meta('followers').'</b><span>'.__('Followers', 'ap').'</span></a>';
	$metas['following'] = '<a href="'.ap_user_link($user_id, 'following').'"><b data-view="ap-following">'.ap_get_current_user_meta('following').'</b><span>'.__('Following', 'ap').'</span></a>';

	/**
	 * FILTER: ap_profile_user_stats_counts
	 * Can be used to alter user profile stats counts
	 * @var string
	 */
	$metas = apply_filters('ap_profile_user_stats_counts', $metas );

	$output = '';
	
	if(!empty($metas) && is_array($metas) && count($metas) > 0){
		$output .= '<ul class="ap-user-counts ap-ul-inline clearfix">';
		foreach($metas as $meta){
			$output .= '<li>'. $meta. '</li>';
		}
		$output .= '</ul>';
	}

	if($echo)
		echo $output;
	else
		return $output;
}
