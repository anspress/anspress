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
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
 
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
		$plugin = anspress_main::get_instance();
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
		//add_action( 'admin_head-nav-menus.php', array($this, 'ap_menu_metaboxes') );
		
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

		add_action('ap_option_fields', array($this, 'option_fields' ));

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
		
		
		add_submenu_page('anspress', 'Questions Label', 'Label', 'manage_options', 'edit-tags.php?taxonomy=question_label');

		/**
		 * ACTION: ap_admin_menu
		 * @since unknown
		 */
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
			ap_opt('ap_flush', true);
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

			if(!empty($options) && is_array($options)){
				$old_options = get_option('anspress_opt');
				
				foreach($options as $k => $opt){
					$old_options[$k] = $opt;
				}

				update_option('anspress_opt', $old_options);
				$result = array('status' => true, 'html' => '<div class="updated fade" style="display:none"><p><strong>'.__( 'AnsPress options updated successfully', 'ap' ).'</strong></p></div>');
			}
				
			
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
			ap_opt('ap_installed', true);
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
			ap_opt('ap_installed', true);
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
			ap_opt('ap_flush', true);
		}
	}

	/**
     * Option fields
     * @param  array  $settings
     * @return string
     * @since 1.0
     */
    public function option_fields($settings){
        $active = (isset($_REQUEST['option_page'])) ? $_REQUEST['option_page'] : 'general' ;
        if ($active == 'general') {
            ?>
			<div class="tab-pane" id="ap-general">		
				<table class="form-table">

					<tr valign="top">
						<th scope="row"><label for="questions_page"><?php _e('Questions Page', 'ap'); ?></label></th>
						<td>
							<?php wp_dropdown_pages( array('selected'=> $settings['questions_page_id'],'name'=> 'anspress_opt[questions_page_id]','post_type'=> 'page') ); ?>
							<p class="description"><?php _e('Questions page', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="ask_page"><?php _e('Ask Page', 'ap'); ?></label></th>
						<td>
							<?php wp_dropdown_pages( array('selected'=> $settings['ask_page_id'],'name'=> 'anspress_opt[ask_page_id]','post_type'=> 'page') ); ?>
							<p class="description"><?php _e('Ask page', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="user_page"><?php _e('User Page', 'ap'); ?></label></th>
						<td>
							<?php wp_dropdown_pages( array('selected'=> $settings['user_page_id'],'name'=> 'anspress_opt[user_page_id]','post_type'=> 'page') ); ?>
							<p class="description"><?php _e('Used to show user profil.', 'ap'); ?></p>
						</td>
					</tr>	
					<tr valign="top">
						<th scope="row"><label for="edit_page"><?php _e('Edit Page', 'ap'); ?></label></th>
						<td>
							<?php wp_dropdown_pages( array('selected'=> $settings['edit_page'],'name'=> 'anspress_opt[edit_page]','post_type'=> 'page') ); ?>
							<p class="description"><?php _e('Used to edit question and answer.', 'ap'); ?></p>
						</td>
					</tr>	

					<tr valign="top">
						<th scope="row">Author Credits</th>
						<td>
							<input type="checkbox" id="author_credits" name="anspress_opt[author_credits]" value="1" <?php checked( true, $settings['author_credits'] ); ?> />
							<label for="author_credits">Hide Author Credits</label>
						</td>
					</tr>
					
					<tr valign="top">
						<th scope="row">Disable private question</th>
						<td>
							<input type="checkbox" id="allow_private_posts" name="anspress_opt[allow_private_posts]" value="1" <?php checked( true, $settings['allow_private_posts'] ); ?> />
							<label for="allow_private_posts"><?php _e('Toggle creating private question and answer', 'ap') ?></label>
						</td>
					</tr>
				</table>
			</div>
            <?php
        }elseif ($active == 'questions') {
        	?>
        	<div class="tab-pane" id="ap-question">		
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="minimum_qtitle_length"><?php _e('Minimum words in title', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[minimum_qtitle_length]" id="minimum_qtitle_length" value="<?php echo $settings['minimum_qtitle_length'] ; ?>" />
							<p class="description"><?php _e('Minimum words for question title.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="minimum_question_length"><?php _e('Minimum words in question', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[minimum_question_length]" id="minimum_question_length" value="<?php echo $settings['minimum_question_length'] ; ?>" />
							<p class="description"><?php _e('Set minimum question word limit.', 'ap'); ?></p>
						</td>
					</tr>
				</table>
			</div>
			<?php
        }elseif ($active == 'answers') {
        	?>
        	<div class="tab-pane" id="ap-answers">		
				<table class="form-table">

					<tr valign="top">
						<th scope="row"><label for="multiple_answers"><?php _e('Multiple Answers', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" id="multiple_answers" name="anspress_opt[multiple_answers]" value="1" <?php checked( true, $settings['multiple_answers'] ); ?> />
							<label><?php _e('Allow an user to submit multiple answers on a single question', 'ap'); ?></label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="minimum_ans_length"><?php _e('Minimum words in answer', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[minimum_ans_length]" id="minimum_ans_length" value="<?php echo $settings['minimum_ans_length'] ; ?>" />
							<p class="description"><?php _e('Set minimum answer word limit.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="close_selected"><?php _e('Close after selecting answer', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" id="close_selected" name="anspress_opt[close_selected]" value="1" <?php checked( true, $settings['close_selected'] ); ?> />
							<p class="description"><?php _e('Do not allow new answer after selecting answer.', 'ap'); ?></p>
						</td>
					</tr>
				</table>
			</div>
			<?php
        }elseif ($active == 'layout') {
        	?>
        	<div class="tab-pane" id="ap-theme">		
				<table class="form-table">

					<tr valign="top">
						<th scope="row"><label for="theme"><?php _e('Theme', 'ap'); ?></label></th>
						<td>
							<select name="anspress_opt[theme]" id="theme">
								<?php 
									foreach (ap_theme_list() as $theme)
										echo '<option value="'.$theme.'">'.$theme.'</option>';
								?>									
							</select>
							<p class="description"><?php _e('Set the theme you want to use', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="avatar_size_qquestion"><?php _e('Avatar size in question page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[avatar_size_qquestion]" id="avatar_size_qquestion" value="<?php echo $settings['avatar_size_qquestion'] ; ?>" />
							<p class="description"><?php _e('User avatar size for question.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="avatar_size_qanswer"><?php _e('Avatar size in answer', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[avatar_size_qanswer]" id="avatar_size_qanswer" value="<?php echo $settings['avatar_size_qanswer'] ; ?>" />
							<p class="description"><?php _e('User avatar in question page answers.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Show title inside question', 'ap') ?></th>
						<td>
							<input type="checkbox" id="show_title_in_question" name="anspress_opt[show_title_in_question]" value="1" <?php checked( true, $settings['show_title_in_question'] ); ?> />
							<label for="show_title_in_question"><?php _e('Show title inside question, for theme layout', 'ap') ?></label>
						</td>
					</tr>
				</table>
			</div>	
			<?php
        }elseif ($active == 'user') {
        	?>
        	<div class="tab-pane" id="ap-user">
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="cover_width"><?php _e('Cover width', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[cover_width]" id="cover_width" value="<?php echo $settings['cover_width'] ; ?>" placeholder="800" />								
							<p class="description"><?php _e('Width of of the cover image.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="cover_height"><?php _e('Cover height', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[cover_height]" id="cover_height" value="<?php echo $settings['cover_height'] ; ?>" placeholder="200" />								
							<p class="description"><?php _e('Height of the cover image.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="cover_width_small"><?php _e('Small cover width', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[cover_width_small]" id="cover_width_small" value="<?php echo $settings['cover_width_small'] ; ?>" placeholder="800" />								
							<p class="description"><?php _e('Width of of the small cover image.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="cover_height_small"><?php _e('Small cover height', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[cover_height_small]" id="cover_height_small" value="<?php echo $settings['cover_height_small'] ; ?>" placeholder="200" />								
							<p class="description"><?php _e('Height of the small cover image.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="default_rank"><?php _e('Default rank', 'ap'); ?></label></th>
						<td>
							<?php
								$terms = get_terms( 'rank', array( 'hide_empty' => false, 'orderby' => 'id' ) );
								if ( !empty( $terms ) ) {
									echo '<select name="anspress_opt[default_rank]">';
									foreach ( $terms as $term ) { ?>
										<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected(  $settings['default_rank'], $term->term_id ); ?>><?php echo esc_attr( $term->name ); ?></option>
									<?php }
									echo '</select>';
								}

								/* If there are no rank terms, display a message. */
								else {
									_e( 'There are no ranks available.', 'ap' );
								}
							?>
							<p class="description"><?php _e('Assign a default rank for newly registered user', 'ap'); ?></p>
						</td>
					</tr>
				</table>
			</div>
			<?php
        }elseif ($active == 'permission') {
        	?>
        	<div class="tab-pane" id="ap-permission">
				<h3 class="ap-option-section"><?php _e('Permission', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e('Post questions', 'ap') ?></th>
						<td>
							<fieldset>
								<input type="checkbox" id="allow_anonymous" name="anspress_opt[allow_anonymous]" value="1" <?php checked( true, $settings['allow_anonymous'] ); ?> />
								<label for="allow_anonymous"><?php _e('Allow anonymous', 'ap') ?></label>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Post answers', 'ap') ?></th>
						<td>
							<fieldset>
								<input type="checkbox" id="only_admin_can_answer" name="anspress_opt[only_admin_can_answer]" value="1" <?php checked( true, $settings['only_admin_can_answer'] ); ?> />
								<label for="only_admin_can_answer"><?php _e('Only admin can answer', 'ap') ?></label>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Show answers', 'ap') ?></th>
						<td>
							<fieldset>
								<input type="checkbox" id="logged_in_can_see_ans" name="anspress_opt[logged_in_can_see_ans]" value="1" <?php checked( true, $settings['logged_in_can_see_ans'] ); ?> />
								<label for="logged_in_can_see_ans"><?php _e('Only logged in can see answers', 'ap') ?></label>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e('Show comments', 'ap') ?></th>
						<td>
							<fieldset>
								<input type="checkbox" id="logged_in_can_see_comment" name="anspress_opt[logged_in_can_see_comment]" value="1" <?php checked( true, $settings['logged_in_can_see_comment'] ); ?> />
								<label for="logged_in_can_see_comment"><?php _e('Only logged in can see comment', 'ap') ?></label>
							</fieldset>
						</td>
					</tr>
				</table>
			</div>
			<?php
        }elseif ($active == 'pages') {
        	?>
        	<div class="tab-pane" id="ap-pages">
				<h3 class="ap-option-section"><?php _e('Item per page', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="question_per_page"><?php _e('Question per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[question_per_page]" id="question_per_page" value="<?php echo $settings['question_per_page'] ; ?>" />								
							<p class="description"><?php _e('Question to show per page', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="answers_per_page"><?php _e('Answers per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[answers_per_page]" id="answers_per_page" value="<?php echo $settings['answers_per_page'] ; ?>" />								
							<p class="description"><?php _e('Answers to show per page in question page', 'ap'); ?></p>
						</td>
					</tr>					
					
					<tr valign="top">
						<th scope="row"><label for="users_per_page"><?php _e('Users per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[users_per_page]" id="users_per_page" value="<?php echo $settings['users_per_page'] ; ?>" />								
							<p class="description"><?php _e('Users to show per page on users page', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="followers_limit"><?php _e('Followers per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[followers_limit]" id="followers_limit" value="<?php echo $settings['followers_limit'] ; ?>" placeholder="10" />								
							<p class="description"><?php _e('How many followers to display on user profile?', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="following_limit"><?php _e('Following users per page', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[following_limit]" id="following_limit" value="<?php echo $settings['following_limit'] ; ?>" placeholder="10" />								
							<p class="description"><?php _e('How many following users to display on user profile?', 'ap'); ?></p>
						</td>
					</tr>
				</table>
				<h3 class="ap-option-section"><?php _e('Sorting & Ordering', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="answers_sort"><?php _e('Default sorting of answers', 'ap'); ?></label></th>
						<td>
							<select name="anspress_opt[answers_sort]" id="answers_sort">
								<option value="voted"<?php echo $settings['answers_sort']=='voted' ? ' selected="selected"' : '' ?>>Voted</option>
								<option value="oldest"<?php echo $settings['answers_sort']=='oldest' ? ' selected="selected"' : '' ?>>Oldest</option>
								<option value="newest"<?php echo $settings['answers_sort']=='newest' ? ' selected="selected"' : '' ?>>Newest</option>
							</select>
							<p class="description"><?php _e('Default active tab for answers list', 'ap'); ?></p>
						</td>
					</tr>
				</table>				
			</div>
			<?php
        }elseif ($active == 'spam') {
        	?>
        	<div class="tab-pane" id="ap-misc">	
				<h3 class="title"><?php _e('Spam', 'ap'); ?></h3>
				<p class="description"><?php _e('Default notes when flagging the posts', 'ap'); ?></p>
				<?php if(isset($settings['flag_note']) && is_array($settings['flag_note'])) : ?>
				
				<?php 
					$i = 0;
					foreach($settings['flag_note'] as $k => $flag) : 
				?>	
					<table<?php echo $i == 0 ? ' id="first-note"' : ''; ?> class="form-table flag-note-item">
						<tr valign="top">
							<th scope="row"><label><?php _e('Title', 'ap'); ?></label></th>
							<td>							
								<input type="text" class="regular-text" name="anspress_opt[flag_note][<?php echo $k;?>][title]" value="<?php echo $flag['title'];?>" placeholder="Title of the note" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label><?php _e('Description', 'ap'); ?></label></th>
							<td>							
								<textarea style="width: 500px;" name="anspress_opt[flag_note][<?php echo $k;?>][description]"><?php echo $flag['description'];?></textarea>
								
								<a class="delete-flag-note" href="#">Delete</a>
							</td>
						</tr>
					</table>
				<?php 
					$i++;
					endforeach; 
					else:
				?>				
				<table id="first-note" class="form-table flag-note-item">
					<tr valign="top">
						<th scope="row"><label><?php _e('Title', 'ap'); ?></label></th>
						<td>							
							<input type="text" class="regular-text" name="anspress_opt[flag_note][0][title]" value="" placeholder="Title of the note" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e('Description', 'ap'); ?></label></th>
						<td>							
							<textarea style="width: 500px;" name="anspress_opt[flag_note][0][description]"></textarea>
							
							<a class="delete-flag-note" href="#">Delete</a>
						</td>
					</tr>
				</table>
				<?php endif; ?>
				<a id="add-flag-note" href="#">Add more notes</a>
				<h3 class="ap-option-section"><?php _e('Moderation', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="moderate_new_question"><?php _e('New question', 'ap'); ?></label></th>
						<td>
							<select name="anspress_opt[moderate_new_question]" id="moderate_new_question">
								<option value="no_mod" <?php selected($settings['moderate_new_question'], 'no_mod') ; ?>><?php _e('No moderation', 'ap'); ?></option>
								<option value="pending" <?php selected($settings['moderate_new_question'], 'pending') ; ?>><?php _e('Hold for review', 'ap'); ?></option>
								<option value="point" <?php selected($settings['moderate_new_question'], 'point') ; ?>><?php _e('Point required', 'ap'); ?></option>
							</select>
							<p class="description"><?php _e('Hold new question for moderation. If you select "Point required" then you can must enter point below.', 'ap'); ?></p>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="mod_question_point"><?php _e('Point required for question', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" class="regular-text" name="anspress_opt[mod_question_point]" value="<?php echo $settings['mod_question_point']; ?>" />
							<p class="description"><?php _e('Point required for directly publish new question.', 'ap'); ?></p>
						</td>
					</tr>
				</table>
				<h3 class="ap-option-section"><?php _e('reCaptcha', 'ap'); ?></h3>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label for="recaptcha_public_key"><?php _e('Public Key', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[recaptcha_public_key]" id="recaptcha_public_key" value="<?php echo $settings['recaptcha_public_key'] ; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="recaptcha_private_key"><?php _e('Private Key', 'ap'); ?></label></th>
						<td>
							<input type="text" name="anspress_opt[recaptcha_private_key]" id="recaptcha_private_key" value="<?php echo $settings['recaptcha_private_key'] ; ?>" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="captcha_ask"><?php _e('Enable in ask form', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" name="anspress_opt[captcha_ask]" id="captcha_ask" value="1" <?php checked(true, $settings['captcha_ask']); ?> />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="captcha_answer"><?php _e('Enable in answer form', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" name="anspress_opt[captcha_answer]" id="captcha_answer" value="1" <?php checked(true, $settings['captcha_answer']); ?> />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="enable_captcha_skip"><?php _e('Enable reCaptcha skip based on user points', 'ap'); ?></label></th>
						<td>
							<input type="checkbox" name="anspress_opt[enable_captcha_skip]" id="enable_captcha_skip" value="1" <?php checked(true, $settings['enable_captcha_skip']); ?> />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="captcha_skip_rpoints"><?php _e('Minimum points to skip reCaptcha', 'ap'); ?></label></th>
						<td>
							<input type="number" min="1" name="anspress_opt[captcha_skip_rpoints]" id="captcha_skip_rpoints" value="<?php echo $settings['captcha_skip_rpoints'] ; ?>" />
						</td>
					</tr>
				</table>
			</div>
			<?php
        }
        
    }

}
