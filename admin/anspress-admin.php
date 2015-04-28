<?php
/**
 * AnsPresss admin class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 */
class AnsPress_Admin
{

	/**
	 * Instance of this class.	 
	 * @var      object
	 */
	protected static $instance = null;

	protected $plugin_slug = 'anspress';

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
		
		$this->includes();
		new AnsPress_Options_Page;

		add_action( 'save_post', array($this, 'ans_parent_post'), 10, 2 ); 

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
		
		add_action('admin_init', array($this, 'register_setting'));		
		// flush rewrite rule if option updated
		add_action('admin_init', array($this, 'init_actions'));		
		add_action('parent_file', array($this, 'tax_menu_correction'));		
		add_action( 'load-post.php', array($this, 'question_meta_box_class') );
		add_action( 'load-post-new.php', array($this, 'question_meta_box_class') );		
		add_action( 'wp_ajax_ap_edit_reputation', array($this, 'ap_edit_reputation') );
		add_action( 'wp_ajax_ap_save_reputation', array($this, 'ap_save_reputation') );
		add_action( 'wp_ajax_ap_new_reputation_form', array($this, 'ap_new_reputation_form') );
		add_action( 'wp_ajax_ap_delete_reputation', array($this, 'ap_delete_reputation') );
		add_action( 'admin_menu', array($this, 'change_post_menu_label') );		
		add_action( 'wp_ajax_ap_edit_badges', array($this, 'ap_edit_badges') );
		add_action( 'wp_ajax_ap_save_badges', array($this, 'ap_save_badges') );
		add_action( 'wp_ajax_ap_new_badge_form', array($this, 'ap_new_badge_form') );
		add_action( 'wp_ajax_ap_taxo_rename', array($this, 'ap_taxo_rename') );
		add_action( 'wp_ajax_ap_delete_flag', array($this, 'ap_delete_flag') );		
		add_action( 'edit_form_after_title', array($this, 'edit_form_after_title') );		       
        add_filter('wp_insert_post_data', array($this, 'post_data_check'), 99);
        add_filter('post_updated_messages', array($this,'post_custom_message'));
        add_action( 'admin_head-nav-menus.php', array($this, 'ap_menu_metaboxes') );
        add_action( 'admin_notices', array($this, 'taxonomy_rename') );
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

	public function includes()
	{
		require_once('functions.php'); 
		require_once('options-page.php'); 
		require_once('extensions.php');
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
		global $typenow, $pagenow;

		if (in_array( $pagenow, array( 'admin.php' ) ) &&  (isset($_GET['page']) && $_GET['page'] == 'anspress') )
			wp_enqueue_script('masonry');

		wp_enqueue_script( 'jquery-form', array('jquery'), false, true );
		wp_enqueue_script( 'ap-admin-js', ANSPRESS_URL.'assets/ap-admin.js');
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	public function add_plugin_admin_menu() {

		if(!current_user_can('delete_pages'))
			return;
		
		$flagged_count = ap_flagged_posts_count();
		$flagged_count = $flagged_count->total > 0 ? $flagged_count->total : 0;
		
		$num_posts = wp_count_posts( 'question', 'readable' );
		$status = "moderate";
		$mod_count = 0;
		
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
		
		$pos = $this->get_free_menu_position(42.9);

		add_menu_page( 'AnsPress', 'AnsPress'.$Totalcount, 'delete_pages', 'anspress', array($this, 'dashboard_page'), ANSPRESS_URL . '/assets/answer.png', $pos );
		
		add_submenu_page('anspress', __( 'All Questions', 'ap' ), __( 'All Questions', 'ap' ),	'delete_pages', 'edit.php?post_type=question', '');
		
		add_submenu_page('anspress', __( 'New Question', 'ap' ), __( 'New Question', 'ap' ),	'delete_pages', 'post-new.php?post_type=question', '');
		
		add_submenu_page('anspress', __( 'All Answers', 'ap' ), __( 'All Answers', 'ap' ),	'delete_pages', 'edit.php?post_type=answer', '');
		
		add_submenu_page('anspress', __( 'Moderate question & answer', 'ap' ), __( 'Moderate', 'ap' ).$Modcount,	'manage_options', 'anspress_moderate', array( $this, 'display_moderate_page' ));
		
		add_submenu_page('anspress', __( 'Flagged question & answer', 'ap' ), __( 'Flagged', 'ap' ).$Flagcount,	'delete_pages', 'anspress_flagged', array( $this, 'display_flagged_page' ));		
		
		add_submenu_page('anspress', __( 'Reputation', 'ap' ), __( 'Reputation', 'ap' ),	'manage_options', 'anspress_reputation', array( $this, 'display_reputation_page' ));

		add_submenu_page('anspress', __( 'AnsPress Options', 'ap' ), __( 'Options', 'ap' ),	'manage_options', 'anspress_options', array( $this, 'display_plugin_admin_page' ));
		
		//add_submenu_page('anspress', __( 'Extensions', 'ap' ), __( 'Extensions', 'ap' ),	'manage_options', 'anspress_ext', array( $this, 'display_plugin_addons_page' ));

		 add_submenu_page('ap_post_flag', __( 'Post flag', 'ap' ), __( 'Post flag', 'ap' ), 'delete_pages', 'ap_post_flag', array( $this, 'display_post_flag' ));
		 add_submenu_page('ap_select_question', __( 'Select question', 'ap' ), __( 'Select question', 'ap' ), 'delete_pages', 'ap_select_question', array( $this, 'display_select_question' ));

		/**
		 * ACTION: ap_admin_menu
		 * @since unknown
		 */
		do_action('ap_admin_menu');		
		
	}
	
	/**
	 * @param integer $start
	 */
	public function get_free_menu_position($start, $increment = 0.99){
		$menus_positions = array();
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
	
	public function display_plugin_addons_page() {
		include_once( 'views/addons.php' );
	}
	
	public function display_reputation_page() {
		include_once('reputation.php');
		$reputation_table = new AnsPress_Reputation_Table();
		$reputation_table->prepare_items();
		?>
		<div class="wrap">        
			<div id="apicon-users" class="icon32"><br/></div>
			<h2>
				<?php _e('AnsPress Points', 'ap'); ?>
				<a class="add-new-h2" href="#" data-button="ap-new-reputation"><?php _e('New reputation', 'ap'); ?></a>
			</h2>
			<form id="anspress-reputation-table" method="get">
				<input type="hidden" name="page" value="<?php echo sanitize_text_field($_REQUEST['page']); ?>" />
				<?php $reputation_table->display() ?>
			</form>
		</div>
		<?php
	}
	
	public function display_badges_page() {
		include_once('badges.php');
		$badge_table = new AP_Badges_Table();
		$badge_table->prepare_items();
		?>
		<div class="wrap">        
			<div id="apicon-users" class="icon32"><br/></div>
			<h2>
				<?php _e('AnsPress Badges', 'ap'); ?>
				<a class="add-new-h2" href="#" data-button="ap-new-badge"><?php _e('New badge', 'ap'); ?></a>
			</h2>
			<?php do_action('ap_after_admin_page_title') ?>
			<form id="anspress-badge-table" method="get">
				<input type="hidden" name="page" value="<?php echo sanitize_text_field($_REQUEST['page']) ?>" />
				<?php $badge_table->display() ?>
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
			<div id="apicon-users" class="icon32"><br/></div>
			<h2><?php _e('Posts waiting moderation', 'ap'); ?></h2>
			<?php do_action('ap_after_admin_page_title') ?>
			<form id="moderate-filter" method="get">
				<input type="hidden" name="page" value="<?php echo sanitize_text_field($_REQUEST['page']); ?>" />
				<?php $moderate_table->views() ?>
				<?php $moderate_table->advanced_filters(); ?>
				<?php $moderate_table->display() ?>
			</form>
		</div>
		<?php
	}
	
	public function display_flagged_page() {
		include_once('flagged.php');
		$flagged_table = new AP_Flagged_Table();
		$flagged_table->prepare_items();
		?>
		<div class="wrap">        
			<div id="apicon-users" class="icon32"><br/></div>
			<h2><?php _e('Flagged question & answer', 'ap'); ?></h2>
			<?php do_action('ap_after_admin_page_title') ?>
			<form id="flagged-filter" method="get">
				<input type="hidden" name="page" value="<?php echo sanitize_text_field($_REQUEST['page']); ?>" />
				<?php $flagged_table->views() ?>
				<?php $flagged_table->advanced_filters(); ?>
				<?php $flagged_table->display() ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Control the output of post flag page
	 * @return void
	 * @since 2.0.0-alpha2
	 */
	public function display_post_flag() {
		include_once('views/post_flag.php');
	}

	/**
	 * Control the ouput of question selection
	 * @return void
	 * @since 2.0.0-alpha2
	 */
	public function display_select_question() {
		include_once('views/select_question.php');
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
		register_setting( 'ap_reputation', 'ap_reputation', array($this, 'validate_options') );
	}
	public function validate_options( $input ) {
		return $input;
	}
	public function init_actions(){

		$GLOBALS['wp']->add_query_var( 'post_parent' );
		
		// flush_rules if option updated	
		if(isset($_GET['page']) && ('anspress_options' == $_GET['page']) && isset($_GET['settings-updated']) && $_GET['settings-updated']){
			$options = ap_opt();			
			$page = get_page(ap_opt('base_page'));
			$options['base_page_slug'] = $page->post_name;
			update_option( 'anspress_opt', $options);
			ap_opt('ap_flush', 'true');
		}

		// If creating a new question then first set a question ID

		global $typenow;

		global $pagenow;

		if (in_array( $pagenow, array( 'post-new.php' ) ) && $typenow == 'answer' && !isset($_GET['post_parent'])){
		   wp_redirect( admin_url( 'admin.php?page=ap_select_question' ) );
		}
	}
	
	public function question_meta_box_class(){
		require_once('meta_box.php'); 
		new AP_Question_Meta_Box();
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
	
	public function ap_edit_reputation(){
		if(current_user_can('manage_options')){
			$id = sanitize_text_field($_POST['id']);
			$reputation = ap_reputation_by_id($id);
			
			$html = '
				<div id="ap-reputation-edit">
					<form method="POST" data-action="ap-save-reputation">
						<table class="form-table">
							<tr valign="top">
								<th scope="row"><label for="title">'. __('Title', 'ap').'</label></th>
								<td>
									<input id="title" type="text" name="title" value="'.$reputation['title'].'" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="description">'. __('Description', 'ap').'</label></th>
								<td>
									<textarea cols="50" id="description" name="description">'.$reputation['description'].'</textarea>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="reputation">'. __('Points', 'ap').'</label></th>
								<td>
									<input id="reputation" type="text" name="reputation" value="'.$reputation['reputation'].'" />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="event">'. __('Event', 'ap').'</label></th>
								<td>
									<input type="text" name="event" value="'.$reputation['event'].'" />
								</td>
							</tr>
						</table>
						<input class="button-primary" type="submit" value="'.__('Save Point', 'ap').'">
						<input type="hidden" name="id" value="'.$reputation['id'].'">
						<input type="hidden" name="action" value="ap_save_reputation">
						<input type="hidden" name="nonce" value="'.wp_create_nonce('ap_save_reputation').'">
					</form>
				</div>
			';
			
			$result = array('status' => true, 'html' => $html);
			$result = apply_filters('ap_edit_reputation_result', $result);
			echo json_encode( $result );
		}
		die();
	}
	
	public function ap_save_reputation(){
		if(current_user_can('manage_options')){
			$nonce 	= sanitize_text_field($_POST['nonce']);
			$title 	= sanitize_text_field($_POST['title']);
			$desc 	= sanitize_text_field($_POST['description']);
			$reputation = sanitize_text_field($_POST['reputation']);
			$event 	= sanitize_text_field($_POST['event']);
			if(wp_verify_nonce($nonce, 'ap_save_reputation')){
				if(isset($_POST['id'])){
					$id 	= sanitize_text_field($_POST['id']);				
					ap_reputation_option_update($id, $title, $desc, $reputation, $event);
				}else{
					ap_reputation_option_new($title, $desc, $reputation, $event);
				}
				
				ob_start();
				$this->display_reputation_page();
				$html = ob_get_clean();
				
				$result =  array(
					'status' => true, 'html' => $html
				);
				
				echo json_encode( $result );
			}
		}
		
		die();
	}	
	public function ap_new_reputation_form(){
		if(current_user_can('manage_options')){
			$html = '
				<div id="ap-reputation-edit">
					<form method="POST" data-action="ap-save-reputation">
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
								<th scope="row"><label for="reputation">'. __('Points', 'ap').'</label></th>
								<td>
									<input id="reputation" type="text" name="reputation" value="" />
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
						<input type="hidden" name="action" value="ap_save_reputation">
						<input type="hidden" name="nonce" value="'.wp_create_nonce('ap_save_reputation').'">
					</form>
				</div>
			';
			
			$result = array('status' => true, 'html' => $html);
			$result = apply_filters('ap_new_reputation_form_result', $result);
			echo json_encode( $result );
		}
		die();
	}
	
	public function ap_delete_reputation(){
		if(current_user_can('manage_options')){
			$args = explode('-', sanitize_text_field($_POST['args']));
			if(wp_verify_nonce($args[1], 'delete_reputation')){
				ap_reputation_option_delete($args[0]);
				$result = array('status' => true);
				$result = apply_filters('ap_delete_reputation_form_result', $result);
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
								<th scope="row"><label for="min_reputation">'. __('Min. Points', 'ap').'</label></th>
								<td>
									<input id="min_reputation" type="text" name="min_reputation" value="'.$badge['min_reputation'].'" />
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
			$id 		= (int)sanitize_text_field($_POST['id']);
			$title 		= sanitize_text_field($_POST['title']);
			$desc 		= sanitize_text_field($_POST['description']);
			$type 		= sanitize_text_field($_POST['type']);
			$min_reputation = sanitize_text_field($_POST['min_reputation']);
			$event 		= sanitize_text_field($_POST['event']);
			$multiple 	= (int)$_POST['multiple'];
			if(wp_verify_nonce($nonce, 'ap_save_badge')){
				if(isset($_POST['id'])){
					$id 	= sanitize_text_field($_POST['id']);				
					ap_badge_option_update($id, $title, $desc, $type, $min_reputation, $event, $multiple);
				}else{
					ap_badge_option_new($id, $title, $desc, $type, $min_reputation, $event, $multiple);
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
								<th scope="row"><label for="min_reputation">'. __('Min. Points', 'ap').'</label></th>
								<td>
									<input id="min_reputation" type="text" name="min_reputation" value="" />
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
		
	public function ap_taxo_rename(){

		if(current_user_can('manage_options')){
			global $wpdb;

			$wpdb->query("UPDATE ".$wpdb->prefix."term_taxonomy SET taxonomy = 'question_tag' WHERE  taxonomy = 'question_tags'");

			ap_opt('tags_taxo_renamed', 'true');
		}
		
		die();
	}
	
	public function change_post_menu_label() {
		global $menu;
		global $submenu;
		$submenu['anspress'][0][0] = 'AnsPress';
	}
	
	
	
	public function ap_delete_flag(){
		$id = (int)sanitize_text_field($_POST['id']);
		if(wp_verify_nonce($_POST['__nonce'], 'flag_delete'.$id) && current_user_can('manage_options')){
			return ap_delete_meta(false, $id);
		}
		die();
	}

	/**
	 * Show question detail above new answer
	 * @return void
	 * @since 2.0
	 */
	public function edit_form_after_title()
	{
		global $typenow, $pagenow, $post;

		if (in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) && $post->post_type == 'answer' && ( (isset($_GET['action']) && $_GET['action'] == 'edit') )){
			
			$post_parent = isset($_GET['action']) ? $post->post_parent : (int)$_GET['post_parent'];
			
			echo '<div class="ap-selected-question">';
				if(!isset($post_parent)){
					echo '<p class="no-q-selected">'.__('This question is orphan, no question is selected for this answer').'</p>';
				}else{
					$q = get_post($post_parent);
					$answer = get_post_meta( $q->ID, ANSPRESS_ANS_META, true );
					echo '<a class="ap-q-title" href="'. get_permalink($q->post_id) .'">'. $q->post_title .'</a>';
					echo '<div class="ap-q-meta"><span class="ap-a-count">'.sprintf(_n('1 Answer', '%d Answer', $answer, 'ap'), $answer).'</span><span class="ap-edit-link">| <a href="'.get_edit_post_link($q->ID).'">'. __('Edit question', 'ap').'</a></span></div>';
					echo '<div class="ap-q-content">'. $q->post_content .'</div><input type="hidden" name="post_parent" value="'.$post_parent.'" />';
				}
			echo '</div>';
		}
	}

	/**
	 * Set answer CPT post parent when saving
	 * @param  integer $post_id
	 * @param  object $post 
	 * @return void
	 * @since 2.0.0-alpha2
	 */
	public function ans_parent_post( $post_id, $post ) {

		global $pagenow;

		if (!in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) )
		   return $post->ID;

		if ( !current_user_can( 'edit_post', $post->ID ))
			return $post->ID;
		
		if ( $post->post_type == 'answer' ) {
			$parent_q = (int) $_GET['post_parent'];
			if( !isset( $parent_q ) || $parent_q == '0' || $parent_q =='' ){
				return $post->ID;
			}else{
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $parent_q ), array( 'ID' => $post->ID ) );
			}
			
		}
	}

	public function post_data_check($data)
    {
        global $pagenow;
        if ($pagenow == 'post.php' && $data['post_type'] == 'answer') {
            $parent_q = isset($_REQUEST['ap_q']) ? $_REQUEST['ap_q'] : $data['post_parent'];
            if (!isset($parent_q) || $parent_q == '0' || $parent_q == '') {
                add_filter('redirect_post_location', array(
                    $this,
                    'custom_post_location'
                ), 99);
                return;
            }
        }
        
        return $data;
    }

    public function post_custom_message($messages)
    {
        global $post;
        
        if ($post->post_type == 'answer' && isset($_REQUEST['message']) && $_REQUEST['message'] == 99)
            add_action('admin_notices', array(
                $this,
                'ans_notice'
            ));
        
        return $messages;
    }

    /**
     * Hook menu meta box
     * @return void
     * @since unknown
     */
    public function ap_menu_metaboxes(){
		add_meta_box( 'add-anspress', __( 'AnsPress Pages' ), array($this, 'wp_nav_menu_item_anspress_meta_box'), 'nav-menus', 'side', 'high' );
	}

	/**
	 * Shows AnsPress menu meta box in WP menu editor
	 * @return void
	 * @since unknown
	 */
	public function wp_nav_menu_item_anspress_meta_box(){
		global $_nav_menu_placeholder, $nav_menu_selected_id;

		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;

		$pages = anspress()->pages;

		echo '<div class="aplinks" id="aplinks">';
		echo '<input type="hidden" value="custom" name="menu-item['.$_nav_menu_placeholder.'][menu-item-type]" />';
		echo '<ul>';
		foreach($pages as $k => $args){
			if($args['show_in_menu']){
				echo '<li>';
				echo '<label class="menu-item-title">';
				echo '<input type="radio" value="" name="menu-item['.$_nav_menu_placeholder.'][menu-item-url]" class="menu-item-checkbox" data-url="'. strtoupper ( 'ANSPRESS_PAGE_URL_'.$k) .'" data-title="'.$args['title'].'"> '.$args['title'].'</label>';
				echo '</li>';
			}
		}
		echo '</ul><p class="button-controls">
					<span class="add-to-menu">
						<input type="submit"'.wp_nav_menu_disabled_check( $nav_menu_selected_id ).' class="button-secondary submit-add-to-menu right" value="'.__('Add to Menu', 'ap').'" name="add-custom-menu-item" id="submit-aplinks" />
						<span class="spinner"></span>
					</span>
				</p>';
		echo '</div>';

	}

	public function taxonomy_rename()
	{

		global $pagenow;

		if(ap_opt('tags_taxo_renamed') == 'true' || !taxonomy_exists('question_tag'))
			return;

		if('edit-tags.php' != $pagenow)
			return;
		?>
	    <div class="error">
	        <p><strong><?php printf(__( 'Is your existing question tags are not appearing ? click here to fix it %s', 'ap' ), '<a class="ap-rename-taxo" href="#">'.__('Fix question tags', 'ap').'</a>'); ?></strong></p>
	        <p><?php printf(__( 'Hide message %s', 'ap' ), '<a class="ap-rename-taxo" href="#">'.__('dismiss', 'ap').'</a>'); ?></p>
	    </div>
	    <?php
	}
}
