<?php
/**
 * AnsPresss
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

/**
 * anspress_admin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package AnsPress
 * @author  Rahul Aryan <admin@rahularyan.com>
 */
 
require_once('functions.php'); 

class anspress_admin {

	/**
	 * Instance of this class.
	 
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;
	
	// Name of the array
	protected $option_name = 'anspress_opt';

	
	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 */
	private function __construct() {
		/*
		 * Call $plugin_slug from public plugin class.
		 *
		 */
		$plugin = anspress::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		
		add_action('current_screen', array($this, 'redirect_to_install_page'));
		
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
		
		add_action('admin_init', array($this, 'register_setting'));
		
		// flush rewrite rule if option updated
		add_action('admin_init', array($this, 'init_actions'));
		add_action( 'admin_head-nav-menus.php', array($this, 'ap_menu_metaboxes') );
		
		add_action('parent_file', array($this, 'tax_menu_correction'));
		
		add_action( 'load-post.php', array($this, 'question_meta_box_class') );
		add_action( 'load-post-new.php', array($this, 'question_meta_box_class') );
		
		//add_action( 'show_user_profile', array($this, 'user_roles_fields') );
		//add_action( 'edit_user_profile', array($this, 'user_roles_fields') );
		
		//add_action( 'personal_options_update', array($this, 'save_user_roles_fields') );
		//add_action( 'edit_user_profile_update', array($this, 'save_user_roles_fields') );
		
		add_action( 'wp_ajax_ap_save_options', array($this, 'ap_save_options') );
		add_action( 'wp_ajax_ap_edit_points', array($this, 'ap_edit_points') );
		add_action( 'wp_ajax_ap_save_points', array($this, 'ap_save_points') );
		add_action( 'wp_ajax_ap_new_point_form', array($this, 'ap_new_point_form') );
		add_action( 'wp_ajax_ap_delete_point', array($this, 'ap_delete_point') );
		add_action( 'admin_menu', array($this, 'change_post_menu_label') );
		
		add_action( 'wp_ajax_ap_edit_badges', array($this, 'ap_edit_badges') );
		add_action( 'wp_ajax_ap_save_badges', array($this, 'ap_save_badges') );
		add_action( 'wp_ajax_ap_new_badge_form', array($this, 'ap_new_badge_form') );
		add_action( 'wp_ajax_ap_delete_badge', array($this, 'ap_delete_badge') );

		add_action( 'wp_ajax_ap_toggle_addon', array($this, 'ap_toggle_addon') );
		add_action( 'wp_ajax_ap_install_base_page', array($this, 'ap_install_base_page') );
		add_action( 'wp_ajax_ap_install_default_opt', array($this, 'ap_install_default_opt') );
		add_action( 'wp_ajax_ap_install_data_table', array($this, 'ap_install_data_table') );
		add_action( 'wp_ajax_ap_install_rewrite_rules', array($this, 'ap_install_rewrite_rules') );
		add_action( 'wp_ajax_ap_install_finish', array($this, 'ap_install_finish') );
		
		add_action( 'wp_ajax_ap_delete_flag', array($this, 'ap_delete_flag') );
		
		add_action( 'save_post', array($this, 'update_rewrite') );
	}

	/**
	 * Return an instance of this class.
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( 'ap-admin-css', ANSPRESS_URL.'assets/ap-admin.css');
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'jquery-form', array('jquery'), false, true );
		wp_enqueue_script( 'ap-admin-js', ANSPRESS_URL.'assets/ap-admin.js');
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	public function add_plugin_admin_menu() {
		$flagged_count = ap_flagged_posts_count();
		$flagged_count = $flagged_count->total > 0 ? $flagged_count->total : 0;
		
		$num_posts = wp_count_posts( 'question', 'readable' );
		$status = "moderate";
		$mod_count = 0;
		$count = '';
		
		if ( !empty($num_posts->$status) )
			$mod_count = $num_posts->$status;
		
		$total = $flagged_count + $mod_count;
		
		$Totalcount = '';
		if($total > 0)
			$Totalcount = ' <span class="update-plugins count"><span class="plugin-count">'.number_format_i18n($total).'</span></span>';
		
		$Flagcount = '';
		if($flagged_count > 0)
			$Flagcount = ' <span class="update-plugins count"><span class="plugin-count">'.number_format_i18n($flagged_count).'</span></span>';
		
		$Modcount =	'';	
		if($mod_count > 0)
			$Modcount = ' <span class="update-plugins count"><span class="plugin-count">'.number_format_i18n($mod_count).'</span></span>';
		
		$pos = $this->get_free_menu_position(50, 0.3);
		
		add_menu_page( 'AnsPress', 'AnsPress'.$Totalcount, 'manage_options', 'anspress', array($this, 'dashboard_page'), ANSPRESS_URL . '/assets/answer.png', $pos );
		
		add_submenu_page('anspress', __( 'All Questions', 'ap' ), __( 'All Questions', 'ap' ),	'manage_options', 'edit.php?post_type=question', '');
		
		add_submenu_page('anspress', __( 'New Question', 'ap' ), __( 'New Question', 'ap' ),	'manage_options', 'post-new.php?post_type=question', '');
		
		add_submenu_page('anspress', __( 'All Answers', 'ap' ), __( 'All Answers', 'ap' ),	'manage_options', 'edit.php?post_type=answer', '');
		
		add_submenu_page('anspress', __( 'Moderate question & answer', 'ap' ), __( 'Moderate', 'ap' ).$Modcount,	'manage_options', 'anspress_moderate', array( $this, 'display_moderate_page' ));
		
		add_submenu_page('anspress', __( 'Flagged question & answer', 'ap' ), __( 'Flagged', 'ap' ).$Flagcount,	'manage_options', 'anspress_flagged', array( $this, 'display_flagged_page' ));
		
		add_submenu_page('anspress', 'Questions Category', 'Category', 'manage_options', 'edit-tags.php?taxonomy=question_category');
		
		add_submenu_page('anspress', 'Questions Tags', 'Tags', 'manage_options', 'edit-tags.php?taxonomy=question_tags');
		
		add_submenu_page('anspress', 'Questions Label', 'Label', 'manage_options', 'edit-tags.php?taxonomy=question_label');
		
		do_action('ap_admin_menu');
		
		add_submenu_page('anspress', __( 'Points', 'ap' ), __( 'User Points', 'ap' ),	'manage_options', 'ap_points', array( $this, 'display_points_page' ));
		
		add_submenu_page('anspress', __( 'Badges', 'ap' ), __( 'User Badges', 'ap' ),	'manage_options', 'ap_badges', array( $this, 'display_badges_page' ));
		
		add_submenu_page('anspress', __( 'AnsPress Options', 'ap' ), __( 'Options', 'ap' ),	'manage_options', 'anspress_options', array( $this, 'display_plugin_admin_page' ));
		
		add_submenu_page('anspress', __( 'Addons', 'ap' ), __( 'Addons', 'ap' ),	'manage_options', 'anspress_addons', array( $this, 'display_plugin_addons_page' ));
		
		add_submenu_page('ap_install', __( 'Install', 'ap' ), __( 'Install', 'ap' ),	'manage_options', 'anspress_install', array( $this, 'display_install_page' ));
		
	}
	
	public function get_free_menu_position($start, $increment = 0.3){
        foreach ($GLOBALS['menu'] as $key => $menu) {
            $menus_positions[] = $key;
        }
 
        if (!in_array($start, $menus_positions)) return $start;
 
        /* the position is already reserved find the closet one */
        while (in_array($start, $menus_positions)) {
            $start += $increment;
        }
        return $start;
    }
	
	// highlight the proper top level menu
	public function tax_menu_correction($parent_file) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;
		if ($taxonomy == 'question_category' || $taxonomy == 'question_tags' || $taxonomy == 'question_label' || $taxonomy == 'rank' || $taxonomy == 'badge' )
			$parent_file = 'anspress';
		return $parent_file;
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}
	
	public function display_install_page() {
		include_once( 'views/install.php' );
	}
	
	public function display_plugin_addons_page() {
		include_once( 'views/addons.php' );
	}
	
	public function display_points_page() {
		include_once('points.php');
		$points_table = new AP_Points_Table();
		$points_table->prepare_items();
		?>
		<div class="wrap">        
			<div id="icon-users" class="icon32"><br/></div>
			<h2>
				<?php _e('AnsPress Points', 'ap'); ?>
				<a class="add-new-h2" href="#" data-button="ap-new-point"><?php _e('New point', 'ap'); ?></a>
			</h2>
			<div class="doante-to-anspress">
				<h3>Help us keep AnsPress open source, free and full functional without any limitations</h3>
				<a href="https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" alt="" /></a>
			</div>
			<form id="anspress-points-table" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $points_table->display() ?>
			</form>
		</div>
		<?php
	}
	
	public function display_badges_page() {
		include_once('badges.php');
		$points_table = new AP_Badges_Table();
		$points_table->prepare_items();
		?>
		<div class="wrap">        
			<div id="icon-users" class="icon32"><br/></div>
			<h2>
				<?php _e('AnsPress Badges', 'ap'); ?>
				<a class="add-new-h2" href="#" data-button="ap-new-badge"><?php _e('New badge', 'ap'); ?></a>
			</h2>
			<div class="doante-to-anspress">
				<h3>Help us keep AnsPress open source, free and full functional without any limitations</h3>
				<a href="https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" alt="" /></a>
			</div>
			<form id="anspress-badge-table" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $points_table->display() ?>
			</form>
		</div>
		<?php
	}
	
	public function dashboard_page() {
		include_once( 'views/dashboard.php' );
	}
	
	public function display_moderate_page() {
		include_once('moderate.php');
		$moderate_table = new AP_Moderate_Table();
		$moderate_table->prepare_items();
		?>
		<div class="wrap">        
			<div id="icon-users" class="icon32"><br/></div>
			<h2><?php _e('Posts waiting moderation', 'ap'); ?></h2>
			<div class="doante-to-anspress">
				<h3>Help us keep AnsPress open source, free and full functional without any limitations</h3>
				<a href="https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" alt="" /></a>
			</div>
			<form id="moderate-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $moderate_table->views() ?>
				<?php $moderate_table->advanced_filters(); ?>
				<?php $moderate_table->display() ?>
			</form>
		</div>
		<?php
	}
	
	public function display_flagged_page() {
		include_once('flagged.php');
		$moderate_table = new AP_Flagged_Table();
		$moderate_table->prepare_items();
		?>
		<div class="wrap">        
			<div id="icon-users" class="icon32"><br/></div>
			<h2><?php _e('Flagged question & answer', 'ap'); ?></h2>
			<div class="doante-to-anspress">
				<h3>Help us keep AnsPress open source, free and full functional without any limitations</h3>
				<a href="https://www.paypal.com/cgi-bin/webscr?business=rah12@live.com&cmd=_xclick&item_name=Donation%20to%20AnsPress%20development" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" alt="" /></a>
			</div>
			<form id="moderate-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $moderate_table->views() ?>
				<?php $moderate_table->advanced_filters(); ?>
				<?php $moderate_table->display() ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Add settings action link to the plugins page.
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=anspress_options' ) . '">' . __( 'Settings', 'ap' ) . '</a>'
			),
			$links
		);

	}
	//register settings
	public function register_setting(){
		register_setting( 'ap_points', 'ap_points', array($this, 'validate_options') );
	}
	public function validate_options( $input ) {
		return $input;
	}
	public function init_actions(){
		
		// flush_rules if option updated	
		if(isset($_GET['page']) && ('anspress_options' == $_GET['page']) && isset($_GET['settings-updated']) && $_GET['settings-updated']){
			$options = ap_opt();			
			$page = get_page(ap_opt('base_page'));
			$options['base_page_slug'] = $page->post_name;
			update_option( 'anspress_opt', $options);
			update_option('ap_flush', true);
		}
	}
	
	public function question_meta_box_class(){
		require_once('meta_box.php'); 
		new AP_Question_Meta_Box();
	}
	
public function ap_menu_metaboxes(){
		/* $anspress_menu = array(
			'id' => 'add-anspress',
			'title' => 'AnsPress',
			'callback' => 'wp_nav_menu_item_link_meta_box',
			'args' => null		
		);
		$GLOBALS['wp_meta_boxes']['nav-menus']['side']['default']['add-anspress'] = $anspress_menu;
		var_dump ( $GLOBALS['wp_meta_boxes']['nav-menus']['side']['default']['add-custom-links']); */
		add_meta_box( 'add-anspress', __( 'AnsPress' ), array($this, 'wp_nav_menu_item_anspress_meta_box'), 'nav-menus', 'side', 'default' );
			//and $GLOBALS['wp_meta_boxes']['nav-menus'] = array ();
	}
	
	public function wp_nav_menu_item_anspress_meta_box(){
		global $_nav_menu_placeholder, $nav_menu_selected_id;

		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;
		$base_page = ap_opt('base_page');
		
		?>
		<div class="aplinks" id="aplinks">
			<input type="hidden" value="custom" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-type]" />
			<ul>
				<li>
					<label class="menu-item-title">
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" class="menu-item-checkbox" data-url="ANSPRESS_BASE_PAGE_URL" data-title="<?php _e('AnsPress', 'ap'); ?>"> <?php _e('AnsPress', 'ap'); ?>
					</label>
				</li>
				<li>
					<label class="menu-item-title">
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" class="menu-item-checkbox" data-url="ANSPRESS_ASK_PAGE_URL" data-title="<?php _e('Ask', 'ap'); ?>"> <?php _e('Ask', 'ap'); ?>
					</label>
				</li>				
				<li>
					<label class="menu-item-title">
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" class="menu-item-checkbox" data-url="ANSPRESS_CATEGORIES_PAGE_URL" data-title="<?php _e('Categories', 'ap'); ?>"> <?php _e('Categories', 'ap'); ?>
					</label>
				</li>
				<li>
					<label class="menu-item-title">
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" class="menu-item-checkbox" data-url="ANSPRESS_TAGS_PAGE_URL" data-title="<?php _e('Tags', 'ap'); ?>"> <?php _e('Tags', 'ap'); ?>						
					</label>
				</li>
				<li>
					<label class="menu-item-title">
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder ; ?>][menu-item-url]" class="menu-item-checkbox" data-url="ANSPRESS_USERS_PAGE_URL" data-title="<?php _e('Users', 'ap'); ?>"> <?php _e('Users', 'ap'); ?>
					</label>
				</li>
				<li>
					<label class="menu-item-title">
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder ; ?>][menu-item-url]" class="menu-item-checkbox" data-url="ANSPRESS_USER_PROFILE_URL" data-title="<?php _e('My profile', 'ap'); ?>"> <?php _e('My profile', 'ap'); ?>
					</label>
				</li>
			</ul>

			<p class="button-controls">
				<span class="add-to-menu">
					<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e('Add to Menu'); ?>" name="add-custom-menu-item" id="submit-aplinks" />
					<span class="spinner"></span>
				</span>
			</p>

		</div><!-- /.customlinkdiv -->
		<?php
	}
	

	public function user_roles_fields( $user ) { 
	?>

		<h3><?php _e('AnsPress Options', 'ap'); ?></h3>

		<table class="form-table">
			<tr>
				<th><label for="ap_role"><?php _e('AnsPress Role', 'ap'); ?></label></th>
				<td>
					<select type="text" name="ap_role" id="ap_role">
					<?php

						foreach(ap_roles() as $k => $role)
							echo '<option value="'.$k.'"'.(get_the_author_meta( 'ap_role', $user->ID ) == $k ? ' selected="selected"' : '').'>'.$role.'</option>';
					?>
					</select><br />
					<span class="description"><?php _e('Role and permission for AnsPress', 'ap'); ?></span>
				</td>
			</tr>
		</table>
	<?php }
	
	public function save_user_roles_fields( $user_id ) {
		update_usermeta( $user_id, 'ap_role', sanitize_text_field($_POST['ap_role']) );
	}
	
	public function ap_save_options(){
		if(current_user_can('manage_options')){
			flush_rewrite_rules();
			$options = $_POST['anspress_opt'];
			update_option('anspress_opt', $options);
				
			$result = array('status' => true, 'html' => '<div class="updated fade" style="display:none"><p><strong>'.__( 'AnsPress options updated successfully', 'ap' ).'</strong></p></div>');
		}
		die(json_encode( $result ));
	}
	
	public function ap_edit_points(){
		if(current_user_can('manage_options')){
			$id = sanitize_text_field($_POST['id']);
			$point = ap_point_by_id($id);
			
			$html = '
				<div id="ap-point-edit">
					<form method="POST" data-action="ap-save-point">
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><label for="title">'. __('Title', 'ap').'</label></th>
								<td>
									<input id="title" type="text" name="title" value="'.$point['title'].'" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="description">'. __('Description', 'ap').'</label></th>
								<td>
									<textarea cols="50" id="description" name="description">'.$point['description'].'</textarea>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="points">'. __('Points', 'ap').'</label></th>
								<td>
									<input id="points" type="text" name="points" value="'.$point['points'].'" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="event">'. __('Event', 'ap').'</label></th>
								<td>
									<input type="text" name="event" value="'.$point['event'].'" />
								</td>
							</tr>
						</table>
						<input class="button-primary" type="submit" value="'.__('Save Point', 'ap').'">
						<input type="hidden" name="id" value="'.$point['id'].'">
						<input type="hidden" name="action" value="ap_save_points">
						<input type="hidden" name="nonce" value="'.wp_create_nonce('ap_save_point').'">
					</form>
				</div>
			';
			
			$result = array('status' => true, 'html' => $html);
			$result = apply_filters('ap_edit_points_result', $result);
			echo json_encode( $result );
		}
		die();
	}
	
	public function ap_save_points(){
		if(current_user_can('manage_options')){
			$nonce 	= sanitize_text_field($_POST['nonce']);
			$title 	= sanitize_text_field($_POST['title']);
			$desc 	= sanitize_text_field($_POST['description']);
			$points = sanitize_text_field($_POST['points']);
			$event 	= sanitize_text_field($_POST['event']);
			if(wp_verify_nonce($nonce, 'ap_save_point')){
				if(isset($_POST['id'])){
					$id 	= sanitize_text_field($_POST['id']);				
					ap_point_option_update($id, $title, $desc, $points, $event);
				}else{
					ap_point_option_new($title, $desc, $points, $event);
				}
				
				ob_start();
				$this->display_points_page();
				$html = ob_get_clean();
				
				$result =  array(
					'status' => true, 'html' => $html
				);
				
				echo json_encode( $result );
			}
		}
		
		die();
	}	
	public function ap_new_point_form(){
		if(current_user_can('manage_options')){
			$html = '
				<div id="ap-point-edit">
					<form method="POST" data-action="ap-save-point">
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><label for="title">'. __('Title', 'ap').'</label></th>
								<td>
									<input id="title" type="text" name="title" value="" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="description">'. __('Description', 'ap').'</label></th>
								<td>
									<textarea cols="50" id="description" name="description"></textarea>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="points">'. __('Points', 'ap').'</label></th>
								<td>
									<input id="points" type="text" name="points" value="" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="event">'. __('Event', 'ap').'</label></th>
								<td>
									<input type="text" name="event" value="" />
								</td>
							</tr>
						</table>
						<input class="button-primary" type="submit" value="'.__('Save Point', 'ap').'">
						<input type="hidden" name="action" value="ap_save_points">
						<input type="hidden" name="nonce" value="'.wp_create_nonce('ap_save_point').'">
					</form>
				</div>
			';
			
			$result = array('status' => true, 'html' => $html);
			$result = apply_filters('ap_new_point_form_result', $result);
			echo json_encode( $result );
		}
		die();
	}
	
	public function ap_delete_point(){
		if(current_user_can('manage_options')){
			$args = explode('-', sanitize_text_field($_POST['args']));
			if(wp_verify_nonce($args[1], 'delete_point')){
				ap_point_option_delete($args[0]);
				$result = array('status' => true);
				$result = apply_filters('ap_delete_point_form_result', $result);
				echo json_encode( $result );
			}
		}
		
		die();
	}
	
	public function ap_edit_badges(){
		if(current_user_can('manage_options')){
			$id = sanitize_text_field($_POST['id']);
			$badge = ap_badge_by_id($id);
			
			$badges_opt = '';
			foreach(ap_badge_types() as $k => $b){
				$badges_opt .= "<option value='{$k}' ".selected($k, $badge['type'], false).">{$b}</option>";
			}
			
			$html = '
				<div id="ap-badge-edit">
					<form method="POST" data-action="ap-save-badge">
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><label for="title">'. __('Title', 'ap').'</label></th>
								<td>
									<input id="title" type="text" name="title" value="'.$badge['title'].'" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="description">'. __('Description', 'ap').'</label></th>
								<td>
									<textarea cols="50" id="description" name="description">'.$badge['description'].'</textarea>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="type">'. __('Type', 'ap').'</label></th>
								<td>
									<select id="type" name="type">'.$badges_opt.'</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="min_points">'. __('Min. Points', 'ap').'</label></th>
								<td>
									<input id="min_points" type="text" name="min_points" value="'.$badge['min_points'].'" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="event">'. __('Event', 'ap').'</label></th>
								<td>
									<input type="text" name="event" value="'.$badge['event'].'" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="multiple">'. __('Multiple', 'ap').'</label></th>
								<td>
									<input type="checkbox" name="multiple" '.checked($badge['multiple'], 1, false).'value="1" />
								</td>
							</tr>
						</table>
						<input class="button-primary" type="submit" value="'.__('Save badge', 'ap').'">
						<input type="hidden" name="id" value="'.$badge['id'].'">
						<input type="hidden" name="action" value="ap_save_badges">
						<input type="hidden" name="nonce" value="'.wp_create_nonce('ap_save_badge').'">
					</form>
				</div>
			';
			
			$result = array('status' => true, 'html' => $html);
			$result = apply_filters('ap_edit_badge_result', $result);
			echo json_encode( $result );
		}
		die();
	}
	public function ap_save_badges(){
		if(current_user_can('manage_options')){
			$nonce 		= sanitize_text_field($_POST['nonce']);
			$title 		= sanitize_text_field($_POST['title']);
			$desc 		= sanitize_text_field($_POST['description']);
			$type 		= sanitize_text_field($_POST['type']);
			$min_points = sanitize_text_field($_POST['min_points']);
			$event 		= sanitize_text_field($_POST['event']);
			$multiple 	= (int)$_POST['multiple'];
			if(wp_verify_nonce($nonce, 'ap_save_badge')){
				if(isset($_POST['id'])){
					$id 	= sanitize_text_field($_POST['id']);				
					ap_badge_option_update($id, $title, $desc, $type, $min_points, $event, $multiple);
				}else{
					ap_badge_option_new($id, $title, $desc, $type, $min_points, $event, $multiple);
				}
				
				ob_start();
				$this->display_badges_page();
				$html = ob_get_clean();
				
				$result =  array(
					'status' => true, 'html' => $html
				);
				
				echo json_encode( $result );
			}
		}
		die();
	}
	
	public function ap_new_badge_form(){
		if(current_user_can('manage_options')){
			
			$badges_opt = '';
			foreach(ap_badge_types() as $k => $b){
				$badges_opt .= "<option value='{$k}'>{$b}</option>";
			}
			
			$html = '
				<div id="ap-badge-edit">
					<form method="POST" data-action="ap-save-badge">
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><label for="title">'. __('Title', 'ap').'</label></th>
								<td>
									<input id="title" type="text" name="title" value="" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="description">'. __('Description', 'ap').'</label></th>
								<td>
									<textarea cols="50" id="description" name="description"></textarea>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="type">'. __('Type', 'ap').'</label></th>
								<td>
									<select id="type" name="type">'.$badges_opt.'</select>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="min_points">'. __('Min. Points', 'ap').'</label></th>
								<td>
									<input id="min_points" type="text" name="min_points" value="" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="event">'. __('Event', 'ap').'</label></th>
								<td>
									<input type="text" name="event" value="" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="multiple">'. __('Multiple', 'ap').'</label></th>
								<td>
									<input type="checkbox" name="multiple" value="1" />
								</td>
							</tr>
						</table>
						<input class="button-primary" type="submit" value="'.__('Save badge', 'ap').'">
						<input type="hidden" name="action" value="ap_save_badges">
						<input type="hidden" name="nonce" value="'.wp_create_nonce('ap_save_badge').'">
					</form>
				</div>
			';
			
			$result = array('status' => true, 'html' => $html);
			$result = apply_filters('ap_new_badge_result', $result);
			echo json_encode( $result );
		}
		die();
	}
		
	public function ap_delete_badge(){
		if(current_user_can('manage_options')){
			$args = explode('-', sanitize_text_field($_POST['args']));
			if(wp_verify_nonce($args[1], 'delete_badge')){
				ap_badge_option_delete($args[0]);
				$result = array('status' => true);
				$result = apply_filters('ap_delete_badge_form_result', $result);
				echo json_encode( $result );
			}
		}
		
		die();
	}
	
	public function change_post_menu_label() {
		global $menu;
		global $submenu;
		$submenu['anspress'][0][0] = 'AnsPress';
	}
	
	public function ap_toggle_addon(){
		if(current_user_can('manage_options')){
			$args = explode('-', sanitize_text_field($_POST['args']));
			if(wp_verify_nonce($args[1], 'toggle_addon')){
				$option = get_option('ap_addons');

				if(isset($option[$args[0]]) && $option[$args[0]]){
					$active = $option[$args[0]];
					if($active)
						$option[$args[0]] = false;
					
					$result = array('status' => 'deactivate', 'html' => '<a data-action="ap-toggle-addon" data-args="'.$args[0].'-'.wp_create_nonce('toggle_addon').'-activate'.'" href="#" class="button button-primary activate">'.__('Activate', 'ap').'</a>', 'message' => '<div id="ap-message" class="updated fade"><p><strong>'.sprintf(__( '%s disabled successfully.', 'ap' ), $args[0]).'</strong></p></div>');
				}else{
					$option[$args[0]] = true;
					$result = array('status' => 'activate', 'html' => '<a data-action="ap-toggle-addon" data-args="'.$args[0].'-'.wp_create_nonce('toggle_addon').'-deactivate'.'" href="#" class="button button-primary activate">'.__('Deactivate', 'ap').'</a>', 'message' => '<div id="ap-message" class="updated fade"><p><strong>'.sprintf(__( '%s activated successfully.', 'ap' ), $args[0]).'</strong></p></div>');
				}
				
				update_option('ap_addons', $option);
			}
		}
		die(json_encode($result));
	}
	
	public function redirect_to_install_page(){
		$screen = get_current_screen();

		/* Check current admin page. */
		if(isset($_GET['escape_install']) && wp_verify_nonce($_GET['nonce'], 'anspress_install')){
			update_option('ap_installed', true);
		}elseif($screen->id != 'admin_page_anspress_install' && !get_option('ap_installed')){
			wp_redirect(admin_url('/admin.php?page=anspress_install'));
			exit;
		}
	}
	
	public function ap_install_base_page(){
		if(wp_verify_nonce($_POST['args'], 'anspress_install') && current_user_can('manage_options')){
			// Update post 37
			  $basepage = array(
				  'ID'           => intval($_POST['base_page']),
				  'post_content' => '[anspress]',
				  'post_title' => '[anspress]'
			  );

			// Update the post into the database
			  wp_update_post( $basepage );
			  flush_rewrite_rules();
			  
			  
		}
		die(true);
	}
	public function ap_install_data_table(){
		if(wp_verify_nonce($_POST['args'], 'anspress_install') && current_user_can('manage_options')){
			
		}
		die(true);
	}
	public function ap_install_default_opt(){
		if(wp_verify_nonce($_POST['args'], 'anspress_install') && current_user_can('manage_options')){
			ap_opt('default_rank', (int)$_POST['rank']);
			ap_opt('default_label', (int)$_POST['label']);
		}
		die(true);
	}
	public function ap_install_rewrite_rules(){
		if(wp_verify_nonce($_POST['args'], 'anspress_install') && current_user_can('manage_options')){
			flush_rewrite_rules();
		}
		die(true);
	}
	public function ap_install_finish(){
		if(wp_verify_nonce($_POST['args'], 'anspress_install') && current_user_can('manage_options')){
			update_option('ap_installed', true);
		}
		die(admin_url('/admin.php?page=anspress_options'));
	}
	
	public function ap_delete_flag(){
		$id = (int)sanitize_text_field($_POST['flag_id']);
		if(wp_verify_nonce($_POST['nonce'], 'flag_delete'.$id) && current_user_can('manage_options')){
			return ap_delete_meta(false, $id);
		}
		die();
	}
	
	public function update_rewrite($post_id){
		if(ap_opt('base_page') == $post_id){
			$post = get_post($post_id);
			ap_opt('base_page_slug', $post->post_name);
			update_option('ap_flush', true);
		}
	}

}
