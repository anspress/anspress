<?php
/**
 * AnsPresss admin class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
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

	/**
	 * Slug of the plugin screen.
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * AnsPress option key
	 * @var string
	 */
	protected $option_name = 'anspress_opt';


	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 */
	private function __construct() {
		$this->includes();

		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . 'anspress-question-answer.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
		add_action( 'save_post', array( $this, 'ans_parent_post' ), 10, 2 );
		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_setting' ) );
		add_action( 'admin_init', array( $this, 'init_actions' ) );
		add_action( 'parent_file', array( $this, 'tax_menu_correction' ) );
		add_action( 'load-post.php', array( $this, 'question_meta_box_class' ) );
		add_action( 'load-post-new.php', array( $this, 'question_meta_box_class' ) );
		add_action( 'admin_menu', array( $this, 'change_post_menu_label' ) );
		add_action( 'edit_form_after_title', array( $this, 'edit_form_after_title' ) );
		add_filter( 'wp_insert_post_data', array( $this, 'post_data_check' ), 99 );
		add_filter( 'post_updated_messages', array( $this, 'post_custom_message' ) );
		add_action( 'admin_head-nav-menus.php', array( $this, 'ap_menu_metaboxes' ) );
		add_action( 'admin_notices', array( $this, 'taxonomy_rename' ) );
		add_filter( 'posts_clauses', array( $this, 'join_by_author_name' ), 10, 2 );
		add_filter( 'manage_edit-comments_columns', array( $this, 'comment_flag_column' ) );
		add_filter( 'manage_comments_custom_column', array( $this, 'comment_flag_column_data' ), 10, 2 );
		add_filter( 'comment_status_links', array( $this, 'comment_flag_view' ) );
		add_action( 'current_screen', array( $this, 'comments_flag_query' ), 10, 2 );
		add_action( 'get_pages', array( $this, 'get_pages' ), 10, 2 );
		add_action( 'wp_insert_post_data', array( $this, 'modify_answer_title' ), 10, 2 );
		add_action( 'admin_action_ap_update_helper', array( $this, 'update_helper' ) );

		// Query filters.
		anspress()->add_action('admin_footer-post.php', 'AnsPress_Query_Filter', 'append_post_status_list' );
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
	 * Include files required in wp-admin
	 */
	public function includes() {
		require_once( 'functions.php' );
		require_once( 'options-fields.php' );
		require_once( 'license.php' );
		require_once( 'class-list-table-hooks.php' );
		new AP_license();
		new AP_List_Table_Hooks();
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 */
	public function enqueue_admin_styles() {
		if( !ap_load_admin_assets() ){
			return;
		}
		wp_enqueue_style( 'ap-admin-css', ANSPRESS_URL.'assets/ap-admin.css' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'ap-fonts', ap_get_theme_url( 'fonts/style.css' ), array(), AP_VERSION );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 */
	public function enqueue_admin_scripts() {
		if( !ap_load_admin_assets() ){
			return;
		}

		$page = get_current_screen();

		$dir = ap_env_dev() ? 'js' : 'min';
		$min = ap_env_dev() ? '' : '.min';

		wp_enqueue_script( 'jquery-form', array( 'jquery' ), false, true );
		wp_enqueue_script( 'ap-initial.js', ap_get_theme_url( 'js/initial.min.js' ), 'jquery', AP_VERSION );
		wp_enqueue_script( 'ap-functions-js', ANSPRESS_URL.'assets/'.$dir.'/ap-functions'.$min.'.js', 'jquery', AP_VERSION );

		if( 'toplevel_page_anspress' == $page->base ){
			wp_enqueue_script( 'ap-chart-js', '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js' );
		}
		
		wp_enqueue_script( 'ap-admin-js', ANSPRESS_URL.'assets/'.$dir.'/ap-admin'.$min.'.js' , array( 'wp-color-picker' ) );
		wp_enqueue_script( 'postbox' );
	}

	/**
	 * Menu counter
	 * @return array
	 * @since 2.4.6
	 */
	public function menu_counts() {
		$q_flagged_count = ap_total_posts_count( 'question', 'flag' );
		$a_flagged_count = ap_total_posts_count( 'answer', 'flag' );
		$question_count = wp_count_posts( 'question', 'readable' );
		$answer_count = wp_count_posts( 'answer', 'readable' );

		$types = array(
			'question' 	=> ( ! empty( $question_count->moderate ) ? $question_count->moderate : 0 ) + $q_flagged_count->total,
			'answer' 	=> ( ! empty( $answer_count->moderate ) ? $answer_count->moderate : 0 ) + $a_flagged_count->total,
			'flagged' 	=> $q_flagged_count->total + $a_flagged_count->total,
		);

		$types['total'] = array_sum( $types );

		$types_html = array();
		foreach ( $types as $k => $count ) {
			if ( $count > 0 ) {
				$types_html[ $k ] = ' <span class="update-plugins count"><span class="plugin-count">'.number_format_i18n( $count ).'</span></span>';
			} else {
				$types_html[ $k ] = '';
			}
		}

		return $types_html;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	public function add_plugin_admin_menu() {
		if ( ! current_user_can( 'delete_pages' ) ) {
			return;
		}

		global $submenu;

		$counts = $this->menu_counts();

		$pos = $this->get_free_menu_position( 42.9 );

		add_menu_page( 'AnsPress', 'AnsPress'.$counts['total'], 'delete_pages', 'anspress', array( $this, 'dashboard_page' ), ANSPRESS_URL . '/assets/answer.png', $pos );

		add_submenu_page( 'anspress', __( 'All Questions', 'anspress-question-answer' ), __( 'All Questions', 'anspress-question-answer' ).$counts['question'], 'delete_pages', 'edit.php?post_type=question', '' );

		add_submenu_page( 'anspress', __( 'New Question', 'anspress-question-answer' ), __( 'New Question', 'anspress-question-answer' ), 'delete_pages', 'post-new.php?post_type=question', '' );

		add_submenu_page( 'anspress', __( 'All Answers', 'anspress-question-answer' ), __( 'All Answers', 'anspress-question-answer' ).$counts['answer'], 'delete_pages', 'edit.php?post_type=answer', '' );

		add_submenu_page( 'anspress', __( 'Reputation', 'anspress-question-answer' ), __( 'Reputation', 'anspress-question-answer' ), 'manage_options', 'anspress_reputation', array( $this, 'display_reputation_page' ) );

		add_submenu_page( 'ap_select_question', __( 'Select question', 'anspress-question-answer' ), __( 'Select question', 'anspress-question-answer' ), 'delete_pages', 'ap_select_question', array( $this, 'display_select_question' ) );

		/**
		 * ACTION: ap_admin_menu
		 * @since unknown
		 */
		do_action( 'ap_admin_menu' );

		add_submenu_page( 'anspress', __( 'AnsPress Options', 'anspress-question-answer' ), __( 'Options', 'anspress-question-answer' ), 'manage_options', 'anspress_options', array( $this, 'display_plugin_admin_page' ) );

		$submenu['anspress'][500] = array( 'Theme & Extensions', 'manage_options' , 'https://anspress.io/themes/' );

		add_submenu_page( 'anspress', __( 'About AnsPress', 'anspress-question-answer' ), __( 'About AnsPress', 'anspress-question-answer' ), 'manage_options', 'anspress_about', array( $this, 'display_plugin_about_page' ) );

	}

	/**
	 * Get free unused menu position. This function helps prevent other plugin
	 * menu conflict when assigned to same position.
	 * @param integer $start          position.
	 * @param double  $increment     position.
	 */
	public function get_free_menu_position($start, $increment = 0.99) {
		$menus_positions = array_keys( $GLOBALS['menu'] );

		if ( ! in_array( $start, $menus_positions ) ) {
			return $start;
		}

		// This position is already reserved find the closet one.
		while ( in_array( $start, $menus_positions ) ) {
			$start += $increment;
		}
		return $start;
	}

	/**
	 * Highlight the proper top level menu
	 * @param  	string $parent_file	parent menu item.
	 * @return 	string
	 */
	public function tax_menu_correction($parent_file) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;

		if ( 'question_category' == $taxonomy || 'question_tags' == $taxonomy || 'question_label' == $taxonomy || 'rank' == $taxonomy || 'badge' == $taxonomy ) {
			$parent_file = 'anspress';
		}
		return $parent_file;
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Load extensions page layout
	 */
	public function display_plugin_addons_page() {
		include_once( 'views/addons.php' );
	}

	/**
	 * Load about page layout
	 */
	public function display_plugin_about_page() {
		include_once( 'views/about.php' );
	}

	/**
	 * Load reputation page layout
	 */
	public static function display_reputation_page() {
		include_once( 'reputation.php' );
		$reputation_table = @new AnsPress_Reputation_Table();
		$reputation_table->prepare_items();
		include( 'views/reputation.php' );
	}

	/**
	 * Load dashboard page layout
	 * @since 2.4
	 */
	public function dashboard_page() {
		include_once( 'views/dashboard.php' );
	}

	/**
	 * Control the ouput of question selection
	 * @return void
	 * @since 2.0.0-alpha2
	 */
	public function display_select_question() {
		include_once( 'views/select_question.php' );
	}

	/**
	 * Add settings action link to the plugins page
	 * @param string $links Pugin action links.
	 */
	public function add_action_links($links) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=anspress_options' ) . '">' . __( 'Settings', 'anspress-question-answer' ) . '</a>',
			),
			$links
		);

	}

	/**
	 * register reputation settings
	 */
	public function register_setting() {
		register_setting( 'ap_reputation', 'ap_reputation', array( $this, 'validate_options' ) );
	}

	/**
	 * Validate reuptation setting
	 * @param  string $input Reputation.
	 * @return string
	 */
	public function validate_options($input) {
		return $input;
	}

	/**
	 * Hook to run on init
	 */
	public function init_actions() {
		$GLOBALS['wp']->add_query_var( 'post_parent' );

		// Flush_rules if option updated.
		if ( isset( $_GET['page'] ) && ('anspress_options' == $_GET['page']) && isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
			$options = ap_opt();
			$page = get_page( ap_opt( 'base_page' ) );
			$options['base_page_slug'] = $page->post_name;
			update_option( 'anspress_opt', $options );
			ap_opt( 'ap_flush', 'true' );
		}

		// If creating a new question then first set a question ID.
		global $typenow;
		global $pagenow;

		if ( in_array( $pagenow, array( 'post-new.php' ) ) && $typenow == 'answer' && ! isset( $_GET['post_parent'] ) ) {
			wp_redirect( admin_url( 'admin.php?page=ap_select_question' ) );
		}

		add_filter( 'pre_get_posts', array( $this, 'serach_qa_by_userid' ) );

	}

	public function question_meta_box_class() {
		require_once( 'meta_box.php' );
		new AP_Question_Meta_Box();
	}

	public function save_user_roles_fields($user_id) {

		update_usermeta( $user_id, 'ap_role', sanitize_text_field( $_POST['ap_role'] ) );
	}



	public function change_post_menu_label() {

		global $menu;
		global $submenu;
		$submenu['anspress'][0][0] = 'AnsPress';
	}

	/**
	 * Show question detail above new answer
	 * @return void
	 * @since 2.0
	 */
	public function edit_form_after_title() {

		global $typenow, $pagenow, $post;

		if ( in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) && $post->post_type == 'answer' && ( (isset( $_GET['action'] ) && $_GET['action'] == 'edit') ) ) {
			$post_parent = isset( $_GET['action'] ) ? $post->post_parent : (int) $_GET['post_parent'];

			echo '<div class="ap-selected-question">';
			if ( ! isset( $post_parent ) ) {
				echo '<p class="no-q-selected">'.__( 'This question is orphan, no question is selected for this answer', 'anspress-question-answer' ).'</p>';
			} else {
				$q = get_post( $post_parent );
				$answer = get_post_meta( $q->ID, ANSPRESS_ANS_META, true );
				echo '<a class="ap-q-title" href="'. get_permalink( $q->post_id ) .'">'. $q->post_title .'</a>';
				echo '<div class="ap-q-meta"><span class="ap-a-count">'.sprintf( _n( '1 Answer', '%d Answers', $answer, 'anspress-question-answer' ), $answer ).'</span><span class="ap-edit-link">| <a href="'.get_edit_post_link( $q->ID ).'">'. __( 'Edit question', 'anspress-question-answer' ).'</a></span></div>';
				echo '<div class="ap-q-content">'. $q->post_content .'</div><input type="hidden" name="post_parent" value="'.$post_parent.'" />';
			}
			echo '</div>';
		}
	}

	/**
	 * Set answer CPT post parent when saving
	 * @param  integer $post_id
	 * @param  object  $post
	 * @return void
	 * @since 2.0.0-alpha2
	 */
	public function ans_parent_post($post_id, $post) {

		global $pagenow;

		if ( ! in_array( $pagenow, array( 'post.php', 'post-new.php' ) ) ) {
			return $post->ID;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		if ( $post->post_type == 'answer' ) {
			$parent_q = (int) $_GET['post_parent'];
			if ( ! isset( $parent_q ) || $parent_q == '0' || $parent_q == '' ) {
				return $post->ID;
			} else {
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $parent_q ), array( 'ID' => $post->ID ) );
			}
		}
	}

	public function post_data_check($data) {

		global $pagenow;
		if ( $pagenow == 'post.php' && $data['post_type'] == 'answer' ) {
			$parent_q = isset( $_REQUEST['ap_q'] ) ? $_REQUEST['ap_q'] : $data['post_parent'];
			if ( ! isset( $parent_q ) || $parent_q == '0' || $parent_q == '' ) {
				add_filter('redirect_post_location', array(
					$this,
					'custom_post_location',
				), 99);
				return;
			}
		}

		return $data;
	}

	public function post_custom_message($messages) {

		global $post;

		if ( $post->post_type == 'answer' && isset( $_REQUEST['message'] ) && $_REQUEST['message'] == 99 ) {
			add_action('admin_notices', array(
				$this,
				'ans_notice',
			));
		}

		return $messages;
	}

	/**
	 * Hook menu meta box
	 * @return void
	 * @since unknown
	 */
	public function ap_menu_metaboxes() {
		add_meta_box( 'add-anspress', __( 'AnsPress Pages', 'anspress-question-answer' ), array( $this, 'wp_nav_menu_item_anspress_meta_box' ), 'nav-menus', 'side', 'high' );
	}

	/**
	 * Shows AnsPress menu meta box in WP menu editor
	 * @return void
	 * @since unknown
	 */
	public function wp_nav_menu_item_anspress_meta_box() {
		global $_nav_menu_placeholder, $nav_menu_selected_id;

		$_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;

		echo '<div class="aplinks" id="aplinks">';
		echo '<input type="hidden" value="custom" name="menu-item['.$_nav_menu_placeholder.'][menu-item-type]" />';
		echo '<ul>';

		$ap_pages = anspress()->pages;

		$ap_pages['profile']       = array( 'title' => __( 'User profile', 'anspress-question-answer' ), 'show_in_menu' => true );
		$ap_pages['notification']  = array( 'title' => __( 'User notification', 'anspress-question-answer' ), 'show_in_menu' => true );

		foreach ( $ap_pages as $k => $args ) {
			if ( $args['show_in_menu'] ) {
				echo '<li>';
				echo '<label class="menu-item-title">';
				echo '<input type="radio" value="" name="menu-item['.$_nav_menu_placeholder.'][menu-item-url]" class="menu-item-checkbox" data-url="'. strtoupper( 'ANSPRESS_PAGE_URL_'.$k ) .'" data-title="'.$args['title'].'"> '.$args['title'].'</label>';
				echo '</li>';
			}
		}

		echo '</ul><p class="button-controls">
                    <span class="add-to-menu">
						<input type="submit"'.wp_nav_menu_disabled_check( $nav_menu_selected_id ).' class="button-secondary submit-add-to-menu right" value="'.__( 'Add to Menu', 'anspress-question-answer' ).'" name="add-custom-menu-item" id="submit-aplinks" />
                        <span class="spinner"></span>
                    </span>
				</p>';
		echo '</div>';

	}

	public function taxonomy_rename() {
		global $pagenow;

		if ( get_option( 'ap_update_helper' ) ) {
			?>
                <div class="update-nag">
			        <h3><?php printf(__('AnsPress update is not complete yet! click %shere%s to continue.','anspress-question-answer' ), '<a href="'.admin_url( 'admin.php?action=ap_update_helper&__nonce'.wp_create_nonce( 'ap_update_help' ) ).'">', '</a>' ); ?></h3>
                </div>
		    <?php
		}
		if ( ap_opt( 'tags_taxo_renamed' ) == 'true' || ! taxonomy_exists( 'question_tag' ) ) {
			return;
		}

		if ( 'edit-tags.php' != $pagenow ) {
			return;
		}
		?>
        <div class="error">
	        <p><strong><?php printf( __( 'Is your existing question tags are not appearing ? click here to fix it %s', 'anspress-question-answer' ), '<a class="ap-rename-taxo" href="#">'.__( 'Fix question tags', 'anspress-question-answer' ).'</a>' ); ?></strong></p>
	        <p><?php printf( __( 'Hide message %s', 'anspress-question-answer' ), '<a class="ap-rename-taxo" href="#">'.__( 'dismiss', 'anspress-question-answer' ).'</a>' ); ?></p>
        </div>
	    <?php
	}

	/**
	 * Add author args in query
	 * @param  object $query WP_Query object.
	 */
	public function serach_qa_by_userid($query) {
		$screen = get_current_screen();

		if ( isset( $query->query_vars['s'], $screen->id, $screen->post_type ) && ($screen->id == 'edit-question' && $screen->post_type == 'question' || $screen->id == 'edit-answer' && $screen->post_type == 'answer' ) && $query->is_main_query() ) {

			$search_q = ap_parse_search_string( get_search_query( ) );

			// Set author args.
			if ( ! empty( $search_q['author_id'] ) && is_array( $search_q['author_id'] ) ) {

				$user_ids = '';

				foreach ( $search_q['author_id'] as $id ) {
					$user_ids .= (int) $id.','; }

				set_query_var( 'author', rtrim( $user_ids, ',' ) );

			} elseif ( ! empty( $search_q['author_name'] ) && is_array( $search_q['author_name'] ) ) {

				$author_names = array();

				foreach ( $search_q['author_name'] as $id ) {
					$author_names[] = sanitize_title_for_query( $id );
				}

				set_query_var( 'ap_author_name', $author_names );

			}

			set_query_var( 's', $search_q['q'] );

		}
	}

	/**
	 * Adds flags column in comment table
	 * @param array $columns Comments table columns.
	 * @since 2.4
	 */
	public function comment_flag_column($columns) {
		$columns['comment_flag'] = __( 'Flag', 'anspress-question-answer' );
		return $columns;
	}

	/**
	 * Show comment_flag data in comment table
	 * @param  string  $column         name of the comment table column.
	 * @param  integer $comment_ID     Current comment ID.
	 * @return void
	 */
	public function comment_flag_column_data($column, $comment_ID) {
		if ( 'comment_flag' == $column ) {
			$count = get_comment_meta( $comment_ID, ANSPRESS_FLAG_META, true );

			if ( $count ) {
				echo '<span class="ap-comment-col-flag">';
				echo $count;
				echo '</span>';
			}
		}
	}

	/**
	 * Add flag view link in comment table
	 * @param  array $views view items array.
	 * @return array
	 */
	public function comment_flag_view( $views ) {
		$views['flagged'] = '<a href="edit-comments.php?show_flagged=true"'.(isset( $_GET['show_flagged'] ) ? ' class="current"' : '').'>'.__( 'Flagged','anspress-question-answer' ).'</a>';
		return $views;
	}

	/**
	 * Delay hooking our clauses filter to ensure it's only applied when needed.
	 * @param string $screen Current screen.
	 */
	public function comments_flag_query( $screen ) {
	    if ( $screen->id != 'edit-comments' ) {
	        return;
	    }

	    // Check if our Query Var is defined.
	    if ( isset( $_GET['show_flagged'] ) ) {
	        add_action( 'comments_clauses', array( $this, 'filter_comments_query' ) );
	    }
	}

	/**
	 * Filter comment clauses, join meta where key is _ap_flag
	 * As pre_get_comments custom meta query not working so we are adding JOIN statement
	 * @param  array $clauses WordPress comment clauses.
	 * @return array
	 */
	public function filter_comments_query( $clauses ) {
		global $wpdb;
		$clauses['join'] = "JOIN $wpdb->commentmeta ON $wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id AND meta_key = '_ap_flag'";

		return $clauses;
	}

	/**
	 * Join users table in post table for searching posts by on user_login
	 * @param  array  $pieces Wp_Query mysql clauses.
	 * @param  object $query  Parent class.
	 * @return array
	 * @since 2.4
	 */
	public function join_by_author_name($pieces, $query) {
		if ( isset( $query->query_vars['ap_author_name'] ) && is_array( $query->query_vars['ap_author_name'] ) && count( $query->query_vars['ap_author_name'] ) > 0 ) {

			global $wpdb;
			$authors = $query->query_vars['ap_author_name'];
			$authors = implode( "','", array_map( 'sanitize_title_for_query', array_unique( (array) $authors ) ) );
			$authors = "'". rtrim( $authors, ",'" )."'";
			$pieces['join'] = " JOIN $wpdb->users users ON users.ID = $wpdb->posts.post_author AND users.user_login IN ($authors)";
		}

		return $pieces;
	}

	public function get_pages($pages, $r) {
		if ( isset( $r['name'] ) && 'page_on_front' == $r['name'] ) {
			if ( $pages ) {
				foreach ( $pages as $k => $page ) {
					if ( $page->ID == ap_opt( 'base_page' ) ) {
						unset( $pages[$k] );
					}
				}
			}
		}

		return $pages;
	}

	/**
	 * Modify answer title before saving, in wp-admin
	 * @param  array $data    Raw post data.
	 * @param  array $postarr Post array.
	 * @return array
	 */
	public function modify_answer_title($data) {
		if ( $data['post_type'] == 'answer' ) {
			$data['post_title'] = get_the_title( $data['post_parent'] );
		}
		return $data;
	}

	public function update_helper() {
		require_once(ANSPRESS_DIR.'admin/update.php' );

		$ap_update_helper = new AP_Update_Helper;

		// Move subscribers.
		if ( get_option( 'ap_subscribers_moved', false ) ) {
			$ap_update_helper->move_subscribers();
		}
		delete_option( 'ap_update_helper' );
		wp_redirect( 'admin.php?page=anspress' );
		wp_die();
	}
}
