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
		add_action('admin_init', array($this, 'flush_rules'));
		add_action( 'admin_head-nav-menus.php', array($this, 'ap_menu_metaboxes') );


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
		wp_enqueue_script( 'ap-admin-js', ANSPRESS_URL.'assets/ap-admin.js');
	}

	public static function default_options(){
		$page = get_page(ap_opt('base_page'));
		return array(
			'base_page' 		=> get_option('ap_base_page_created'),
			'base_page_slug' 	=> $page->post_name,
			'theme' 			=> 'default',
			'author_credits' 	=> false,
			'clear_databse' 	=> false,
			'multiple_answers' 	=> false,
			'question_points' 	=> 10,
			'answer_points' 	=> 20,
			'comment_points' 	=> 5,
			'up_vote_points' 	=> 3,
			'down_vote_points' 	=> -1,
			'flag_note' => array(0 => array('title' => 'it is spam', 'description' => 'This question is effectively an advertisement with no disclosure. It is not useful or relevant, but promotional.')),			
			'tags_per_page' 			=> '20',
		);
	}
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	public function add_plugin_admin_menu() {

		/*
		 * Add a settings page for this plugin to the Settings menu.
		 *
		 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
		 *
		 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
		*/
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'AnsPress Options', 'ap' ),
			__( 'AnsPress Options', 'ap' ),
			'manage_options',
			'anspress_options',
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Add settings action link to the plugins page.
	 */
	public function add_action_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=anspress_options' ) . '">' . __( 'Settings', 'ap' ) . '</a>'
			),
			$links
		);

	}
	//register settings
	public function register_setting(){
		// Register settings and call sanitation functions
		register_setting( 'anspress_options', 'anspress_opt', array($this, 'validate_options') );
	}
	public function validate_options( $input ) {
		return $input;
	}
	public function flush_rules(){
		// flush_rules if option updated	
		if(isset($_GET['page']) && ('anspress_options' == $_GET['page']) && isset($_GET['settings-updated']) && $_GET['settings-updated']){
			$options = ap_opt();			
			$page = get_page(ap_opt('base_page'));
			$options['base_page_slug'] = $page->post_name;
			update_option( 'anspress_opt', $options);
			flush_rewrite_rules();
		}
	}
	
public function ap_menu_metaboxes()
	{
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
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" class="menu-item-checkbox" data-url="<?php echo get_permalink( $base_page ); ?>" data-title="<?php _e('AnsPress', 'ap'); ?>"> <?php _e('AnsPress', 'ap'); ?>
					</label>
				</li>
				<li>
					<label class="menu-item-title">
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" class="menu-item-checkbox" data-url="<?php echo get_permalink( $base_page ); ?>&ap_page=ask" data-title="<?php _e('Ask', 'ap'); ?>"> <?php _e('Ask', 'ap'); ?>
					</label>
				</li>				
				<li>
					<label class="menu-item-title">
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" class="menu-item-checkbox" data-url="<?php echo get_permalink( $base_page ); ?>&ap_page=categories" data-title="<?php _e('Categories', 'ap'); ?>"> <?php _e('Categories', 'ap'); ?>
					</label>
				</li>
				<li>
					<label class="menu-item-title">
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder; ?>][menu-item-url]" class="menu-item-checkbox" data-url="<?php echo get_permalink( $base_page ); ?>&ap_page=tags" data-title="<?php _e('Tags', 'ap'); ?>"> <?php _e('Tags', 'ap'); ?>						
					</label>
				</li>
				<li>
					<label class="menu-item-title">
						<input type="radio" value="" name="menu-item[<?php echo $_nav_menu_placeholder ; ?>][menu-item-url]" class="menu-item-checkbox" data-url="<?php echo get_permalink( $base_page ); ?>&ap_page=users" data-title="<?php _e('Users', 'ap'); ?>"> <?php _e('Users', 'ap'); ?>
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
	


}
