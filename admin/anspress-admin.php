<?php
/**
 * AnsPresss admin class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once 'license.php';

/**
 * This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package AnsPress
 * @author  Rahul Aryan <rah12@live.com>
 */
class AnsPress_Admin {

	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * AnsPress option key.
	 *
	 * @var string
	 */
	protected $option_name = 'anspress_opt';


	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 */
	public static function init() {
		self::includes();

		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . 'anspress-question-answer.php' );
		anspress()->add_filter( 'plugin_action_links_' . $plugin_basename, __CLASS__, 'add_action_links' );
		anspress()->add_action( 'save_post', __CLASS__, 'ans_parent_post', 10, 2 );
		anspress()->add_action( 'trashed_post', __CLASS__, 'trashed_post', 10, 2 );
		anspress()->add_action( 'admin_enqueue_scripts', __CLASS__, 'enqueue_admin_styles' );
		anspress()->add_action( 'admin_enqueue_scripts', __CLASS__, 'enqueue_admin_scripts' );
		anspress()->add_action( 'admin_menu', __CLASS__, 'add_plugin_admin_menu' );
		anspress()->add_action( 'parent_file', __CLASS__, 'fix_active_admin_menu', 1000 );
		anspress()->add_action( 'admin_init', __CLASS__, 'init_actions' );
		anspress()->add_action( 'parent_file', __CLASS__, 'tax_menu_correction' );
		anspress()->add_action( 'load-post.php', __CLASS__, 'question_meta_box_class' );
		anspress()->add_action( 'load-post-new.php', __CLASS__, 'question_meta_box_class' );
		anspress()->add_action( 'admin_menu', __CLASS__, 'change_post_menu_label' );
		anspress()->add_filter( 'wp_insert_post_data', __CLASS__, 'post_data_check', 99 );
		anspress()->add_action( 'admin_head-nav-menus.php', __CLASS__, 'ap_menu_metaboxes' );
		anspress()->add_filter( 'posts_clauses', __CLASS__, 'join_by_author_name', 10, 2 );
		anspress()->add_action( 'get_pages', __CLASS__, 'get_pages', 10, 2 );
		anspress()->add_action( 'wp_insert_post_data', __CLASS__, 'modify_answer_title', 10, 2 );
		anspress()->add_action( 'admin_footer-post.php', __CLASS__, 'append_post_status_list' );
		anspress()->add_action( 'admin_post_anspress_update_db', __CLASS__, 'update_db' );
		anspress()->add_action( 'admin_post_anspress_create_base_page', __CLASS__, 'anspress_create_base_page' );
		anspress()->add_action( 'admin_notices', __CLASS__, 'anspress_notice' );
		anspress()->add_action( 'ap_register_options', __CLASS__, 'register_options' );
		anspress()->add_action( 'ap_after_field_markup', __CLASS__, 'page_select_field_opt' );
		anspress()->add_action( 'admin_action_ap_addon_options', __CLASS__, 'ap_addon_options' );
		anspress()->add_action( 'admin_action_ap_save_addon_options', __CLASS__, 'save_addon_options' );
		anspress()->add_action( 'admin_footer', __CLASS__, 'admin_footer' );
	}

	/**
	 * Include files required in wp-admin
	 */
	public static function includes() {
		require_once 'functions.php';

		new AP_license();
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 */
	public static function enqueue_admin_styles() {
		if ( ! ap_load_admin_assets() ) {
			return;
		}

		wp_enqueue_style( 'ap-admin-css', ANSPRESS_URL . 'assets/ap-admin.css', [], AP_VERSION );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'anspress-fonts', ap_get_theme_url( 'css/fonts.css' ), [], AP_VERSION );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 */
	public static function enqueue_admin_scripts() {
		$page = get_current_screen();

		if ( ! ap_load_admin_assets() ) {
			return;
		}

		wp_enqueue_script( 'anspress-main', ANSPRESS_URL . 'assets/js/min/main.min.js', [ 'jquery', 'jquery-form', 'backbone', 'underscore' ], AP_VERSION );

		wp_enqueue_script( 'anspress-admin-js', ANSPRESS_URL . 'assets/js/min/ap-admin.min.js', [ 'anspress-main' ], AP_VERSION, true );

		?>
			<script type="text/javascript">
				currentQuestionID = '<?php the_ID(); ?>';
				apTemplateUrl = '<?php echo ap_get_theme_url( 'js-template', false, false ); ?>';
				aplang = {};
				apShowComments  = false;
			</script>
		<?php

		if ( 'post' === $page->base && 'question' === $page->post_type ) {
			wp_enqueue_script( 'ap-admin-app-js', ANSPRESS_URL . 'assets/js/min/admin-app.min.js', [], AP_VERSION, true );
		}

		wp_enqueue_script( 'postbox' );
	}

	/**
	 * Menu counter
	 *
	 * @return array
	 * @since 2.4.6
	 */
	public static function menu_counts() {
		$flagged   = ap_total_flagged_count();
		$q_flagged = $flagged['questions'];
		$a_flagged = $flagged['answers'];

		$question_count = wp_count_posts( 'question', 'readable' );
		$answer_count   = wp_count_posts( 'answer', 'readable' );

		$types          = array(
			'question' => ( ! empty( $question_count->moderate ) ? $question_count->moderate : 0 ) + $q_flagged->total,
			'answer'   => ( ! empty( $answer_count->moderate ) ? $answer_count->moderate : 0 ) + $a_flagged->total,
			'flagged'  => $q_flagged->total + $a_flagged->total,
		);

		$types['total'] = array_sum( $types );
		$types_html     = array();

		foreach ( (array) $types as $k => $count ) {
			if ( $count > 0 ) {
				$types_html[ $k ] = ' <span class="update-plugins count ap-menu-counts"><span class="plugin-count">' . number_format_i18n( $count ) . '</span></span>';
			} else {
				$types_html[ $k ] = '';
			}
		}

		return $types_html;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	public static function add_plugin_admin_menu() {
		if ( ! current_user_can( 'delete_pages' ) ) {
			return;
		}

		global $submenu;

		$counts = self::menu_counts();
		$pos    = self::get_free_menu_position( 42.9 );

		add_menu_page( 'AnsPress', 'AnsPress' . $counts['total'], 'delete_pages', 'anspress', array( __CLASS__, 'dashboard_page' ), ANSPRESS_URL . '/assets/answer.png', $pos );

		add_submenu_page( 'anspress', __( 'All Questions', 'anspress-question-answer' ), __( 'All Questions', 'anspress-question-answer' ) . $counts['question'], 'delete_pages', 'edit.php?post_type=question', '' );

		add_submenu_page( 'anspress', __( 'New Question', 'anspress-question-answer' ), __( 'New Question', 'anspress-question-answer' ), 'delete_pages', 'post-new.php?post_type=question', '' );

		add_submenu_page( 'anspress', __( 'All Answers', 'anspress-question-answer' ), __( 'All Answers', 'anspress-question-answer' ) . $counts['answer'], 'delete_pages', 'edit.php?post_type=answer', '' );

		add_submenu_page( 'anspress', __( 'New Answer', 'anspress-question-answer' ), __( 'New Answer', 'anspress-question-answer' ), 'delete_pages', 'ap_select_question', array( __CLASS__, 'display_select_question' ) );

		/**
		 * Action hook for adding custom menu in wp-admin.
		 *
		 * @since unknown
		 */
		do_action( 'ap_admin_menu' );

		add_submenu_page( 'anspress', __( 'AnsPress Options', 'anspress-question-answer' ), __( 'Options', 'anspress-question-answer' ), 'manage_options', 'anspress_options', array( __CLASS__, 'display_plugin_options_page' ) );

		add_submenu_page( 'anspress', __( 'AnsPress Add-ons', 'anspress-question-answer' ), __( 'Add-ons', 'anspress-question-answer' ), 'manage_options', 'anspress_addons', array( __CLASS__, 'display_plugin_addons_page' ) );

		$submenu['anspress'][500] = array( 'Theme & Extensions', 'manage_options', 'https://anspress.net/themes/' );

		add_submenu_page( 'anspress-hidden', __( 'About AnsPress', 'anspress-question-answer' ), __( 'About AnsPress', 'anspress-question-answer' ), 'manage_options', 'anspress_about', array( __CLASS__, 'display_plugin_about_page' ) );

	}

	public static function fix_active_admin_menu( $parent_file ) {
		global $submenu_file, $current_screen, $plugin_page;

		// Set correct active/current menu and submenu in the WordPress Admin menu for the "example_cpt" Add-New/Edit/List
		if ( in_array( $current_screen->post_type, [ 'question', 'answer' ], true )  ) {
			$submenu_file = 'edit.php?post_type=' . $current_screen->post_type;
			if ( $current_screen->action == 'add' ) {
				$submenu_file = 'post-new.php?post_type=' . $current_screen->post_type;
			}
			$parent_file  = 'anspress';
		}

		return $parent_file;
	}

	/**
	 * Get free unused menu position. This function helps prevent other plugin
	 * menu conflict when assigned to same position.
	 *
	 * @param integer $start          position.
	 * @param double  $increment     position.
	 */
	public static function get_free_menu_position( $start, $increment = 0.99 ) {
		$menus_positions = array_keys( $GLOBALS['menu'] );

		if ( ! in_array( $start, $menus_positions, true ) ) {
			return $start;
		}

		// This position is already reserved find the closet one.
		while ( in_array( $start, $menus_positions, true ) ) {
			$start += $increment;
		}
		return $start;
	}

	/**
	 * Highlight the proper top level menu.
	 *
	 * @param   string $parent_file parent menu item.
	 * @return  string
	 */
	public static function tax_menu_correction( $parent_file ) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;

		if ( 'question_category' === $taxonomy || 'question_tag' === $taxonomy || 'question_label' === $taxonomy || 'rank' === $taxonomy || 'badge' === $taxonomy ) {
			$parent_file = 'anspress';
		}
		return $parent_file;
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public static function display_plugin_options_page() {
		include_once 'views/options.php';
	}

	/**
	 * Load extensions page layout
	 */
	public static function display_plugin_addons_page() {
		include_once 'views/addons.php';
	}

	/**
	 * Load about page layout
	 */
	public static function display_plugin_about_page() {
		include_once 'views/about.php';
	}

	/**
	 * Load dashboard page layout.
	 *
	 * @since 2.4
	 */
	public static function dashboard_page() {
		include_once 'views/dashboard.php';
	}

	/**
	 * Control the output of question selection.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public static function display_select_question() {
		include_once 'views/select_question.php';
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @param string $links Pugin action links.
	 */
	public static function add_action_links( $links ) {
		return array_merge(
			$links,
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=anspress_options' ) . '">' . __( 'Settings', 'anspress-question-answer' ) . '</a>',
				'about'    => '<a href="' . admin_url( 'admin.php?page=anspress_about' ) . '">' . __( 'About', 'anspress-question-answer' ) . '</a>',
			)
		);
	}

	/**
	 * Hook to run on init
	 */
	public static function init_actions() {
		$GLOBALS['wp']->add_query_var( 'post_parent' );

		// Flush_rules if option updated.
		if ( isset( $_GET['page'] ) && ('anspress_options' === $_GET['page']) && isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) { // @codingStandardsIgnoreLine.
			$options                   = ap_opt();
			$page                      = get_page( ap_opt( 'base_page' ) );
			$options['base_page_slug'] = $page->post_name;
			update_option( 'anspress_opt', $options );
			ap_opt( 'ap_flush', 'true' );
		}

		// If creating a new question then first set a question ID.
		global $typenow;
		global $pagenow;

		if ( in_array( $pagenow, array( 'post-new.php' ), true ) &&
				'answer' === $typenow &&
				! isset( $_GET['post_parent'] ) // @codingStandardsIgnoreLine.
			) {
			wp_safe_redirect( admin_url( 'admin.php?page=ap_select_question' ) );
			exit;
		}

		add_filter( 'pre_get_posts', array( __CLASS__, 'serach_qa_by_userid' ) );
	}

	/**
	 * Question meta box.
	 */
	public static function question_meta_box_class() {
		require_once 'meta-box.php';
		new AP_Question_Meta_Box();
	}

	/**
	 * Save anspress user roles.
	 *
	 * @param integer $user_id User ID.
	 */
	public static function save_user_roles_fields( $user_id ) {
		update_usermeta( $user_id, 'ap_role', ap_sanitize_unslash( 'ap_role', 'p' ) );
	}

	/**
	 * Change post menu label.
	 */
	public static function change_post_menu_label() {
		global $menu;
		global $submenu;
		$submenu['anspress'][0][0] = 'AnsPress';
	}

	/**
	 * Set answer CPT post parent when saving.
	 *
	 * @param  integer $post_id Post ID.
	 * @param  object  $post Post Object.
	 * @since 2.0.0
	 */
	public static function ans_parent_post( $post_id, $post ) {
		global $pagenow;

		if ( ! in_array( $pagenow, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		if ( 'answer' === $post->post_type ) {
			$parent_q = (int) ap_sanitize_unslash( 'post_parent', 'p' );
			if ( empty( $parent_q ) ) {
				return;
			} else {
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $parent_q ), array( 'ID' => $post->ID ) ); // db call ok, cache ok.
			}
		}
	}

	/**
	 * Delete page check transient after AnsPress pages are deleted.
	 *
	 * @param integer $post_id Page ID.
	 * @return void
	 * @since 4.1.0
	 */
	public static function trashed_post( $post_id ) {
		$_post = get_post( $post_id );

		if ( 'page' === $_post->post_type ) {
			$pages_slug = [ 'base_page', 'ask_page' ];
			$page_ids   = [];
			$opt        = ap_opt();

			foreach ( $pages_slug as $slug ) {
				$page_ids[] = $opt[ $slug ];
			}

			if ( in_array( $_post->ID, $page_ids, true ) ) {
				delete_transient( 'ap_pages_check' );
			}
		}
	}

	/**
	 * [Not documented]
	 *
	 * @param array $data Post data array.
	 * @return array
	 */
	public static function post_data_check( $data ) {
		global $pagenow;

		if ( 'post.php' === $pagenow && 'answer' === $data['post_type'] ) {
			$parent_q = ap_sanitize_unslash( 'ap_q', 'p' );

			$parent_q = ! empty( 'parent_q' ) ? $parent_q : $data['post_parent'];

			if ( ! empty( $parent_q ) ) {
				add_filter( 'redirect_post_location', [ __CLASS__, 'custom_post_location' ], 99 );
				return;
			}
		}

		return $data;
	}

	/**
	 * Redirect to custom post location for error message.
	 *
	 * @param String $location redirect url.
	 * @return string
	 */
	public static function custom_post_location( $location ) {
		remove_filter( 'redirect_post_location', __FUNCTION__, 99 );
		$location = add_query_arg( 'message', 99, $location );

		return $location;
	}

	/**
	 * Hook menu meta box.
	 *
	 * @return void
	 * @since unknown
	 */
	public static function ap_menu_metaboxes() {
		add_meta_box( 'anspress-menu-mb', 'AnsPress', [ __CLASS__, 'render_menu' ], 'nav-menus', 'side', 'high' );
	}

	/**
	 * Shows AnsPress menu meta box in WP menu editor.
	 *
	 * @return void
	 * @since unknown
	 */
	public static function render_menu( $object, $args ) {
		global $nav_menu_selected_id;
		$menu_items = ap_menu_obejct();

		$walker       = new Walker_Nav_Menu_Checklist( false );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);
		?>

		<div id="anspress-div">
			<div id="tabs-panel-anspress-all" class="tabs-panel tabs-panel-active">
			<ul id="anspress-checklist-pop" class="categorychecklist form-no-clear" >
				<?php
					echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $menu_items ), 0, (object) array( 'walker' => $walker ) );
				?>
			</ul>

			<p class="button-controls">
				<span class="list-controls">
					<a href="
					<?php
						echo esc_url(
							add_query_arg(
								array(
									'anspress-all' => 'all',
									'selectall'    => 1,
								),
								remove_query_arg( $removed_args )
							)
						);
					?>
					#anspress-menu-mb" class="select-all"><?php _e( 'Select All', 'anspress-question-answer' ); ?></a>
				</span>

				<span class="add-to-menu">
					<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-anspress-menu-item" id="submit-anspress-div" />
					<span class="spinner"></span>
				</span>
			</p>
		</div>
	<?php
	}

	/**
	 * Add author args in query.
	 *
	 * @param  object $query WP_Query object.
	 */
	public static function serach_qa_by_userid( $query ) {
		$screen = get_current_screen();

		if ( isset( $query->query_vars['s'], $screen->id, $screen->post_type ) &&
			( 'edit-question' === $screen->id && 'question' === $screen->post_type || 'edit-answer' === $screen->id && 'answer' === $screen->post_type ) &&
			$query->is_main_query() ) {

			$search_q = ap_parse_search_string( get_search_query() );

			// Set author args.
			if ( ! empty( $search_q['author_id'] ) && is_array( $search_q['author_id'] ) ) {

				$user_ids = '';

				foreach ( (array) $search_q['author_id'] as $id ) {
					$user_ids .= (int) $id . ',';
				}

				set_query_var( 'author', rtrim( $user_ids, ',' ) );

			} elseif ( ! empty( $search_q['author_name'] ) && is_array( $search_q['author_name'] ) ) {

				$author_names = array();

				foreach ( (array) $search_q['author_name'] as $id ) {
					$author_names[] = sanitize_title_for_query( $id );
				}
				set_query_var( 'ap_author_name', $author_names );
			}

			set_query_var( 's', $search_q['q'] );
		}
	}

	/**
	 * Filter comment clauses, join meta where key is _ap_flag
	 * As pre_get_comments custom meta query not working so we are adding JOIN statement
	 *
	 * @param  array $clauses WordPress comment clauses.
	 * @return array
	 */
	public static function filter_comments_query( $clauses ) {
		global $wpdb;
		$clauses['join'] = "JOIN $wpdb->commentmeta ON $wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id AND meta_key = '_ap_flag'";

		return $clauses;
	}

	/**
	 * Join users table in post table for searching posts by on user_login.
	 *
	 * @param  array  $pieces Wp_Query mysql clauses.
	 * @param  object $query  Parent class.
	 * @return array
	 * @since 2.4
	 */
	public static function join_by_author_name( $pieces, $query ) {

		if ( isset( $query->query_vars['ap_author_name'] ) && is_array( $query->query_vars['ap_author_name'] ) && count( $query->query_vars['ap_author_name'] ) > 0 ) {

			global $wpdb;
			$authors        = $query->query_vars['ap_author_name'];
			$authors        = implode( "','", array_map( 'sanitize_title_for_query', array_unique( (array) $authors ) ) );
			$authors        = "'" . rtrim( $authors, ",'" ) . "'";
			$pieces['join'] = " JOIN $wpdb->users users ON users.ID = $wpdb->posts.post_author AND users.user_login IN ($authors)"; // @codingStandardsIgnoreLine.
		}

		return $pieces;
	}

	/**
	 * Remove AnsPress base page from front page page select input.
	 *
	 * @param array $pages Page array.
	 * @param array $r Arguments.
	 * @return array
	 */
	public static function get_pages( $pages, $r ) {
		if ( isset( $r['name'] ) && 'page_on_front' === $r['name'] ) {
			foreach ( (array) $pages as $k => $page ) {
				if ( ap_opt( 'base_page' ) == $page->ID ) { // loose comparison okay.
					unset( $pages[ $k ] );
				}
			}
		}

		return $pages;
	}

	/**
	 * Modify answer title before saving, in wp-admin.
	 *
	 * @param  array $data    Raw post data.
	 * @return array
	 */
	public static function modify_answer_title( $data ) {
		if ( 'answer' === $data['post_type'] ) {
			$data['post_title'] = get_the_title( $data['post_parent'] );
		}

		return $data;
	}

	/**
	 * Add AnsPress post status to post edit select box.
	 */
	public static function append_post_status_list() {
		 global $post;
		 $complete = '';
		 $label    = '';

		if ( in_array( $post->post_type, [ 'question', 'answer' ], true ) ) {
			if ( 'moderate' === $post->post_status ) {
				 $complete = ' selected=\'selected\'';
				 $label    = '<span id=\'post-status-display\'>' . esc_attr__( 'Moderate', 'anspress-question-answer' ) . '</span>';
			} elseif ( 'private_post' === $post->post_status ) {
				 $complete = ' selected=\'selected\'';
				 $label    = '<span id=\'post-status-display\'>' . esc_attr__( 'Private Post', 'anspress-question-answer' ) . '</span>';
			}

			// @codingStandardsIgnoreStart
			echo '<script>
				jQuery(document).ready(function(){
					jQuery("select#post_status").append("<option value=\'moderate\' ' . $complete . '>' . esc_attr__( 'Moderate', 'anspress-question-answer' ) . '</option>");
					jQuery("select#post_status").append("<option value=\'private_post\' ' . $complete . '>' . esc_attr__( 'Private Post', 'anspress-question-answer' ) . '</option>");
					jQuery(".misc-pub-section label").append("' . $label . '");
				});
			</script>';
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Show AnsPress notices.
	 */
	public static function anspress_notice() {
		$page             = get_current_screen();
		$anspress_updates = get_option( 'anspress_updates', [] );
		$have_updates     = empty( $anspress_updates ) || in_array( false, $anspress_updates, true );

		$messages = array(
			'db'            => [
				'type'    => 'error',
				'message' => __( 'AnsPress database is not updated.', 'anspress-question-answer' ),
				'button'  => ' <a class="button" href="' . admin_url( 'admin-post.php?action=anspress_update_db' ) . '">' . __( 'Update now', 'anspress-question-answer' ) . '</a>',
				'show'    => ( get_option( 'anspress_db_version' ) != AP_DB_VERSION ),
			],
			'missing_pages' => [
				'type'    => 'error',
				'message' => __( 'One or more AnsPress page(s) does not exists.', 'anspress-question-answer' ),
				'button'  => ' <a href="' . admin_url( 'admin-post.php?action=anspress_create_base_page' ) . '">' . __( 'Set automatically', 'anspress-question-answer' ) . '</a> ' . __( 'Or', 'anspress-question-answer' ) . ' <a href="' . admin_url( 'admin.php?page=anspress_options' ) . '">' . __( 'Set set by yourself', 'anspress-question-answer' ) . '</a>',
				'show'    => ( ! self::check_pages_exists() ),
			],
		);

		foreach ( $messages as $msg ) {
			if ( $msg['show'] ) {
				$class = 'ap-notice notice notice-' . $msg['type'];
				printf(
					'<div class="%1$s %4$s"><p>%2$s%3$s</p></div>',
					esc_attr( $class ),
					esc_html( $msg['message'] ),
					$msg['button'],
					'apicon-anspress-icon'
				);
			}
		}
	}

	/**
	 * Check if AnsPress pages are exists.
	 *
	 * @return boolean
	 * @since 4.1.0
	 */
	private static function check_pages_exists() {
		$cache = get_transient( 'ap_pages_check' );

		if ( false === $cache ) {
			$opt        = ap_opt();
			$pages_slug = array_keys( ap_main_pages() );

			$pages_in = [];
			foreach ( $pages_slug as $slug ) {
				$pages_in[] = $opt[ $slug ];
			}

			$args = array(
				'include'     => $pages_in,
				'post_type'   => 'page',
				'post_status' => 'publish',
			);

			$pages = get_posts( $args );

			if ( count( $pages ) < count( $pages_slug ) ) {
				$cache = '0';
				set_transient( 'ap_pages_check', '0', HOUR_IN_SECONDS );
			} else {
				set_transient( 'ap_pages_check', '1', HOUR_IN_SECONDS );
				$cache = '1';
			}
		}

		return '0' === $cache ? false : true;
	}

	/**
	 * Updates AnsPress DB tables.
	 */
	public static function update_db() {
		if ( current_user_can( 'manage_options' ) ) {
			require_once ANSPRESS_DIR . '/activate.php';

			$activate = \AP_Activate::get_instance();
			$activate->insert_tables();
			update_option( 'anspress_db_version', AP_DB_VERSION );
		}

		wp_redirect( admin_url( 'admin.php?page=anspress_options' ) );
	}

	/**
	 * Create a page and set it as base page.
	 */
	public static function anspress_create_base_page() {
		if ( current_user_can( 'manage_options' ) ) {
			ap_create_base_page();
			flush_rewrite_rules();
			delete_transient( 'ap_pages_check' );
		}

		wp_redirect( admin_url( 'admin.php?page=anspress_options' ) );
	}

	/**
	 * Register all AnsPress options.
	 *
	 * @return void
	 * @since 4.1.0
	 */
	public static function register_options() {
		add_filter( 'ap_form_options_general_pages', [ __CLASS__, 'options_general_pages' ] );
		add_filter( 'ap_form_options_general_permalinks', [ __CLASS__, 'options_general_permalinks' ] );
		add_filter( 'ap_form_options_general_layout', [ __CLASS__, 'options_general_layout' ] );
		add_filter( 'ap_form_options_postscomments', [ __CLASS__, 'options_postscomments' ] );
		add_filter( 'ap_form_options_uac_reading', [ __CLASS__, 'options_uac_reading' ] );
		add_filter( 'ap_form_options_uac_posting', [ __CLASS__, 'options_uac_posting' ] );
		add_filter( 'ap_form_options_uac_other', [ __CLASS__, 'options_uac_other' ] );
		add_filter( 'ap_form_options_user_activity', [ __CLASS__, 'options_user_activity' ] );
	}

	/**
	 * Register AnsPress general pages options.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function options_general_pages() {
		$opt  = ap_opt();
		$form = array(
			'submit_label' => __( 'Save Pages', 'anspress-question-answer' ),
			'fields'       => array(
				'author_credits' => array(
					'label' => __( 'Hide author credits', 'anspress-question-answer' ),
					'desc'  => __( 'Hide link to AnsPress project site.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'order' => 0,
					'value' => $opt['author_credits'],
				),
				'sep-warning'    => array(
					'html' => '<div class="ap-uninstall-warning">' . __( 'If you have created main pages manually then make sure to have [anspress] shortcode in all pages.', 'anspress-question-answer' ) . '</div>',
				),
			),
		);

		foreach ( ap_main_pages() as $slug => $args ) {
			$form['fields'][ $slug ] = array(
				'label'      => $args['label'],
				'desc'       => $args['desc'],
				'type'       => 'select',
				'options'    => 'posts',
				'posts_args' => array(
					'post_type' => 'page',
					'showposts' => -1,
				),
				'value'      => $opt[ $slug ],
				'sanitize'   => 'absint',
			);
		}

		/**
		 * Filter to override pages options form.
		 *
		 * @param array $form Form arguments.
		 * @since 4.1.0
		 */
		return apply_filters( 'ap_options_form_pages', $form );
	}

	/**
	 * Register permalinks options.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function options_general_permalinks() {
		$opt = ap_opt();

		$form = array(
			'submit_label' => __( 'Save Permalinks', 'anspress-question-answer' ),
			'fields'       => array(
				'question_page_slug'      => array(
					'label'    => __( 'Question slug', 'anspress-question-answer' ),
					'desc'     => __( 'Slug for single question page.', 'anspress-question-answer' ),
					'value'    => $opt['question_page_slug'],
					'validate' => 'required',
				),
				'question_page_permalink' => array(
					'label'    => __( 'Question permalink', 'anspress-question-answer' ),
					'desc'     => __( 'Select single question permalink structure.', 'anspress-question-answer' ),
					'type'     => 'radio',
					'options'  => [
						'question_perma_1' => home_url( '/' . ap_base_page_slug() ) . '/<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/question-name/',
						'question_perma_2' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/question-name/',
						'question_perma_3' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/213/',
						'question_perma_4' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/213/question-name/',
						'question_perma_5' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/question-name/213/',
						'question_perma_6' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/213-question-name/',
						'question_perma_7' => home_url( '/' ) . '<b class="ap-base-slug">' . ap_opt( 'question_page_slug' ) . '</b>/question-name-213/',
					],
					'value'    => $opt['question_page_permalink'],
					'validate' => 'required',
				),
				'base_page_title'         => array(
					'label'    => __( 'Base page title', 'anspress-question-answer' ),
					'desc'     => __( 'Main questions list page title', 'anspress-question-answer' ),
					'value'    => $opt['base_page_title'],
					'validate' => 'required',
				),
				'search_page_title'       => array(
					'label'    => __( 'Search page title', 'anspress-question-answer' ),
					'desc'     => __( 'Title of the search page', 'anspress-question-answer' ),
					'value'    => $opt['search_page_title'],
					'validate' => 'required',
				),
				'author_page_title'       => array(
					'label'    => __( 'Author page title', 'anspress-question-answer' ),
					'desc'     => __( 'Title of the author page', 'anspress-question-answer' ),
					'value'    => $opt['author_page_title'],
					'validate' => 'required',
				),
			),
		);

		/**
		 * Filter to override permalinks options form.
		 *
		 * @param array $form Form arguments.
		 * @since 4.1.0
		 */
		return apply_filters( 'ap_options_form_permalinks', $form );
	}

	/**
	 * Register AnsPress general layout options.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function options_general_layout() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'load_assets_in_anspress_only' => array(
					'name'  => '',
					'label' => __( 'Load assets in AnsPress page only?', 'anspress-question-answer' ),
					'desc'  => __( 'Check this to load AnsPress JS and CSS on the AnsPress page only. Be careful, this might break layout.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['load_assets_in_anspress_only'],
				),
				'avatar_size_list'             => array(
					'label'   => __( 'List avatar size', 'anspress-question-answer' ),
					'desc'    => __( 'User avatar size for questions list.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['avatar_size_list'],
				),
				'avatar_size_qquestion'        => array(
					'label'   => __( 'Question avatar size', 'anspress-question-answer' ),
					'desc'    => __( 'User avatar size for question.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['avatar_size_qquestion'],
				),
				'avatar_size_qanswer'          => array(
					'label'   => __( 'Answer avatar size', 'anspress-question-answer' ),
					'desc'    => __( 'User avatar size for answer.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['avatar_size_qanswer'],
				),
				'avatar_size_qcomment'         => array(
					'label'   => __( 'Comment avatar size', 'anspress-question-answer' ),
					'desc'    => __( 'User avatar size for comments.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['avatar_size_qcomment'],
				),
				'question_per_page'            => array(
					'label'   => __( 'Questions per page', 'anspress-question-answer' ),
					'desc'    => __( 'Questions to show per page.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['question_per_page'],
				),
				'answers_per_page'             => array(
					'label'   => __( 'Answers per page', 'anspress-question-answer' ),
					'desc'    => __( 'Answers to show per page.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['answers_per_page'],
				),
			),
		);

		/**
		 * Filter to override layout options form.
		 *
		 * @param array $form Form arguments.
		 * @since 4.1.0
		 */
		return apply_filters( 'ap_options_form_layout', $form );
	}

	/**
	 * Register UAC reading options.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function options_uac_reading() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'read_question_per' => array(
					'label'   => __( 'Who can read question?', 'anspress-question-answer' ),
					'desc'    => __( 'Set who can view or read a question.', 'anspress-question-answer' ),
					'type'    => 'select',
					'value'   => $opt['read_question_per'],
					'options' => array(
						'anyone'    => __( 'Anyone, including non-loggedin', 'anspress-question-answer' ),
						'logged_in' => __( 'Only logged in', 'anspress-question-answer' ),
						'have_cap'  => __( 'Only user having ap_read_question capability', 'anspress-question-answer' ),
					),
				),
				'read_answer_per'   => array(
					'label'   => __( 'Who can read answers?', 'anspress-question-answer' ),
					'desc'    => __( 'Set who can view or read a answer.', 'anspress-question-answer' ),
					'type'    => 'select',
					'value'   => $opt['read_answer_per'],
					'options' => array(
						'anyone'    => __( 'Anyone, including non-loggedin', 'anspress-question-answer' ),
						'logged_in' => __( 'Only logged in', 'anspress-question-answer' ),
						'have_cap'  => __( 'Only user having ap_read_answer capability', 'anspress-question-answer' ),
					),
				),
				'read_comment_per'  => array(
					'label'   => __( 'Who can read comment?', 'anspress-question-answer' ),
					'desc'    => __( 'Set who can view or read a comment.', 'anspress-question-answer' ),
					'type'    => 'select',
					'value'   => $opt['read_comment_per'],
					'options' => array(
						'anyone'    => __( 'Anyone, including non-loggedin', 'anspress-question-answer' ),
						'logged_in' => __( 'Only logged in', 'anspress-question-answer' ),
						'have_cap'  => __( 'Only user having ap_read_comment capability', 'anspress-question-answer' ),
					),
				),
			),
		);

		return $form;
	}

	/**
	 * Register AnsPress user access control options.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function options_uac_posting() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'post_question_per'     => array(
					'label'   => __( 'Who can post question?', 'anspress-question-answer' ),
					'desc'    => __( 'Set who can submit a question from frontend.', 'anspress-question-answer' ),
					'type'    => 'select',
					'value'   => $opt['post_question_per'],
					'options' => array(
						'anyone'    => __( 'Anyone, including non-loggedin', 'anspress-question-answer' ),
						'logged_in' => __( 'Only logged in', 'anspress-question-answer' ),
						'have_cap'  => __( 'Only user having ap_new_question capability', 'anspress-question-answer' ),
					),
				),
				'post_answer_per'       => array(
					'label'   => __( 'Who can post answer?', 'anspress-question-answer' ),
					'desc'    => __( 'Set who can submit an answer from frontend.', 'anspress-question-answer' ),
					'type'    => 'select',
					'value'   => $opt['post_answer_per'],
					'options' => array(
						'anyone'    => __( 'Anyone, including non-loggedin', 'anspress-question-answer' ),
						'logged_in' => __( 'Only logged in', 'anspress-question-answer' ),
						'have_cap'  => __( 'Only user having ap_new_answer capability', 'anspress-question-answer' ),
					),
				),
				'create_account'      => array(
					'label' => __( 'Create account for non-registered', 'anspress-question-answer' ),
					'desc'  => __( 'Allow non-registered users to create account by entering their email in question. After submitting post a confirmation email will be sent to the user.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['create_account'],
				),
				'multiple_answers'      => array(
					'label' => __( 'Multiple answers', 'anspress-question-answer' ),
					'desc'  => __( 'Allow users to submit multiple answer per question.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['multiple_answers'],
				),
				'disallow_op_to_answer' => array(
					'label' => __( 'OP can answer?', 'anspress-question-answer' ),
					'desc'  => __( 'OP: Original poster/asker. Enabling this option will prevent users to post an answer on their question.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['disallow_op_to_answer'],
				),
				'post_comment_per'      => array(
					'label'   => __( 'Who can post comment?', 'anspress-question-answer' ),
					'desc'    => __( 'Set who can submit a comment from frontend.', 'anspress-question-answer' ),
					'type'    => 'select',
					'value'   => $opt['post_comment_per'],
					'options' => array(
						'anyone'    => __( 'Anyone, including non-loggedin', 'anspress-question-answer' ),
						'logged_in' => __( 'Only logged in', 'anspress-question-answer' ),
						'have_cap'  => __( 'Only user having ap_new_comment capability', 'anspress-question-answer' ),
					),
				),
				'new_question_status'   => array(
					'label'   => __( 'Status of new question', 'anspress-question-answer' ),
					'desc'    => __( 'Default status of new question.', 'anspress-question-answer' ),
					'type'    => 'select',
					'options' => array(
						'publish'  => __( 'Publish', 'anspress-question-answer' ),
						'moderate' => __( 'Moderate', 'anspress-question-answer' ),
					),
					'value'   => $opt['new_question_status'],
				),
				'edit_question_status'  => array(
					'label'   => __( 'Status of edited question', 'anspress-question-answer' ),
					'desc'    => __( 'Default status of edited question.', 'anspress-question-answer' ),
					'type'    => 'select',
					'options' => array(
						'publish'  => __( 'Publish', 'anspress-question-answer' ),
						'moderate' => __( 'Moderate', 'anspress-question-answer' ),
					),
					'value'   => $opt['edit_question_status'],
				),
				'new_answer_status'     => array(
					'label'   => __( 'Status of new answer', 'anspress-question-answer' ),
					'desc'    => __( 'Default status of new answer.', 'anspress-question-answer' ),
					'type'    => 'select',
					'options' => array(
						'publish'  => __( 'Publish', 'anspress-question-answer' ),
						'moderate' => __( 'Moderate', 'anspress-question-answer' ),
					),
					'value'   => $opt['new_answer_status'],
				),
				'edit_answer_status'    => array(
					'label'   => __( 'Status of edited answer', 'anspress-question-answer' ),
					'desc'    => __( 'Default status of edited answer.', 'anspress-question-answer' ),
					'type'    => 'select',
					'options' => array(
						'publish'  => __( 'Publish', 'anspress-question-answer' ),
						'moderate' => __( 'Moderate', 'anspress-question-answer' ),
					),
					'value'   => $opt['edit_answer_status'],
				),
				'anonymous_post_status' => array(
					'label'   => __( 'Status of non-loggedin post', 'anspress-question-answer' ),
					'desc'    => __( 'Default status of question or answer submitted by non-loggedin user.', 'anspress-question-answer' ),
					'type'    => 'select',
					'options' => array(
						'publish'  => __( 'Publish', 'anspress-question-answer' ),
						'moderate' => __( 'Moderate', 'anspress-question-answer' ),
					),
					'value'   => $opt['anonymous_post_status'],
				),
			),
		);

		/**
		 * Filter to override UAC options form.
		 *
		 * @param array $form Form arguments.
		 * @since 4.1.0
		 */
		return apply_filters( 'ap_options_form_uac', $form );
	}

	/**
	 * Register other UAC options.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function options_uac_other() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'allow_upload'        => array(
					'label' => __( 'Allow image upload', 'anspress-question-answer' ),
					'desc'  => __( 'Allow logged-in users to upload image.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['allow_upload'],
				),
				'uploads_per_post'    => array(
					'label' => __( 'Max uploads per post', 'anspress-question-answer' ),
					'desc'  => __( 'Set numbers of media user can upload for each post.', 'anspress-question-answer' ),
					'value' => $opt['uploads_per_post'],
				),
				'max_upload_size'     => array(
					'label' => __( 'Max upload size', 'anspress-question-answer' ),
					'desc'  => __( 'Set maximum upload size.', 'anspress-question-answer' ),
					'value' => $opt['max_upload_size'],
				),
				'allow_private_posts' => array(
					'label' => __( 'Allow private posts', 'anspress-question-answer' ),
					'desc'  => __( 'Allows users to create private question and answer. Private Q&A are only visible to admin and moderators.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['allow_private_posts'],
				),
				'multiple_answers'    => array(
					'label' => __( 'Multiple Answers', 'anspress-question-answer' ),
					'desc'  => __( 'Allows users to post multiple answers on a question.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['multiple_answers'],
				),
			),
		);

		return $form;
	}

	/**
	 * Register AnsPress QA options.
	 *
	 * @return array
	 * @since 4.1.0
	 */
	public static function options_postscomments() {
		$opt = ap_opt();

		$form = array(
			'fields' => array(
				'comment_number'                => array(
					'label'   => __( 'Numbers of comments to show', 'anspress-question-answer' ),
					'desc'    => __( 'Numbers of comments to load in each query?', 'anspress-question-answer' ),
					'value'   => $opt['comment_number'],
					'subtype' => 'number',
				),
				'duplicate_check'               => array(
					'label' => __( 'Check duplicate', 'anspress-question-answer' ),
					'desc'  => __( 'Check for duplicate posts before posting', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['duplicate_check'],
				),
				'disable_q_suggestion'          => array(
					'label' => __( 'Disable question suggestion', 'anspress-question-answer' ),
					'desc'  => __( 'Checking this will disable question suggestion in ask form', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['disable_q_suggestion'],
				),
				'default_date_format'           => array(
					'label' => __( 'Show default date format', 'anspress-question-answer' ),
					'desc'  => __( 'Instead of showing time passed i.e. 1 Hour ago, show default format date.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['default_date_format'],
				),
				'show_solved_prefix'            => array(
					'label'    => __( 'Show solved prefix', 'anspress-question-answer' ),
					'desc'     => __( 'If an answer is selected for question then [solved] prefix will be added in title.', 'anspress-question-answer' ),
					'type'     => 'checkbox',
					'value'    => $opt['show_solved_prefix'],
					'validate' => 'required',
				),
				'question_order_by'             => array(
					'label'   => __( 'Default question order', 'anspress-question-answer' ),
					'desc'    => __( 'Order question list by default using selected', 'anspress-question-answer' ),
					'type'    => 'select',
					'options' => array(
						'voted'  => __( 'Voted', 'anspress-question-answer' ),
						'active' => __( 'Active', 'anspress-question-answer' ),
						'newest' => __( 'Newest', 'anspress-question-answer' ),
						'oldest' => __( 'Oldest', 'anspress-question-answer' ),
					),
					'value'   => $opt['question_order_by'],
				),
				'keep_stop_words'               => array(
					'label' => __( 'Keep stop words in question slug', 'anspress-question-answer' ),
					'desc'  => __( 'AnsPress will not strip stop words in question slug.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['keep_stop_words'],
				),
				'minimum_qtitle_length'         => array(
					'label'   => __( 'Minimum title length', 'anspress-question-answer' ),
					'desc'    => __( 'Set minimum letters for a question title.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['minimum_qtitle_length'],
				),
				'minimum_question_length'       => array(
					'label'   => __( 'Minimum question content', 'anspress-question-answer' ),
					'desc'    => __( 'Set minimum letters for a question contents.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['minimum_question_length'],
				),
				'question_text_editor'          => array(
					'label' => __( 'Question editor?', 'anspress-question-answer' ),
					'desc'  => __( 'Quick tags editor', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['question_text_editor'],
				),
				'answer_text_editor'            => array(
					'label' => __( 'Answer editor?', 'anspress-question-answer' ),
					'desc'  => __( 'Quick tags editor', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['answer_text_editor'],
				),
				'disable_comments_on_question'  => array(
					'label' => __( 'Disable comments', 'anspress-question-answer' ),
					'desc'  => __( 'Disable comments on questions.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['disable_comments_on_question'],
				),
				'disable_voting_on_question'    => array(
					'label' => __( 'Disable voting', 'anspress-question-answer' ),
					'desc'  => __( 'Disable voting on questions.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['disable_voting_on_question'],
				),
				'disable_down_vote_on_question' => array(
					'label' => __( 'Disable down voting', 'anspress-question-answer' ),
					'desc'  => __( 'Disable down voting on questions.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['disable_down_vote_on_question'],
				),
				'close_selected'                => array(
					'label' => __( 'Close question after selecting answer', 'anspress-question-answer' ),
					'desc'  => __( 'If enabled this will prevent user to submit answer on solved question.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['close_selected'],
				),
				'answers_sort'                  => array(
					'label'   => __( 'Default answers order', 'anspress-question-answer' ),
					'desc'    => __( 'Order answers by by default using selected', 'anspress-question-answer' ),
					'type'    => 'select',
					'options' => array(
						'voted'  => __( 'Voted', 'anspress-question-answer' ),
						'active' => __( 'Active', 'anspress-question-answer' ),
						'newest' => __( 'Newest', 'anspress-question-answer' ),
						'oldest' => __( 'Oldest', 'anspress-question-answer' ),
					),
					'value'   => $opt['answers_sort'],
				),
				'minimum_ans_length'            => array(
					'label'   => __( 'Minimum answer content', 'anspress-question-answer' ),
					'desc'    => __( 'Set minimum letters for a answer contents.', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['minimum_ans_length'],
				),
				'disable_comments_on_answer'    => array(
					'label' => __( 'Disable comments', 'anspress-question-answer' ),
					'desc'  => __( 'Disable comments on answer.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['disable_comments_on_answer'],
				),
				'disable_voting_on_answer'      => array(
					'label' => __( 'Disable voting', 'anspress-question-answer' ),
					'desc'  => __( 'Disable voting on answers.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['disable_voting_on_answer'],
				),
				'disable_down_vote_on_answer'   => array(
					'label' => __( 'Disable down voting', 'anspress-question-answer' ),
					'desc'  => __( 'Disable down voting on answers.', 'anspress-question-answer' ),
					'type'  => 'checkbox',
					'value' => $opt['disable_down_vote_on_answer'],
				),
			),
		);

		/**
		 * Filter to override post and comments options form.
		 *
		 * @param array $form Form arguments.
		 * @since 4.1.0
		 */
		return apply_filters( 'ap_options_form_postscomments', $form );
	}

	/**
	 * Register AnsPress user's activity options.
	 *
	 * @return array
	 * @since 4.1.8
	 */
	public static function options_user_activity() {
		global $wp_roles;
		$opt = ap_opt();

		$roles = [];
		foreach ( $wp_roles->roles as $key => $role ) {
			$roles[ $key ] = $role['name'];
		}

		$form = array(
			'fields' => array(
				'activity_exclude_roles' => array(
					'label'   => __( 'Select the roles to exclude in activity feed.', 'anspress-question-answer' ),
					'desc'    => __( 'Selected role\'s activities will be excluded in site activity feed.', 'anspress-question-answer' ),
					'type'    => 'checkbox',
					'value'   => $opt['activity_exclude_roles'],
					'options' => $roles,
				),
			),
		);

		/**
		 * Filter to user's activity option form.
		 *
		 * @param array $form Form arguments.
		 * @since 4.1.8
		 */
		return apply_filters( 'ap_options_form_user_activity', $form );
	}

	/**
	 * Add link to view, edit and create right next to page select field.
	 *
	 * @param object $field Field object.
	 * @return void
	 */
	public static function page_select_field_opt( $field ) {
		$page_slugs = array_keys( ap_main_pages() );

		// Return if not the field we are looking for.
		if ( ! in_array( $field->original_name, $page_slugs, true ) ) {
			return;
		}

		$field->add_html( '&nbsp;&nbsp;&nbsp;<a href="' . esc_url( get_permalink( $field->value() ) ) . '">' . __( 'View page', 'anspress-question-answer' ) . '</a>&nbsp;&nbsp;&nbsp;' );
		$field->add_html( '<a href="' . esc_url( get_edit_post_link( $field->value() ) ) . '">' . __( 'Edit page', 'anspress-question-answer' ) . '</a>' );
	}

	/**
	 * Load addons options form in a thickbox.
	 *
	 * @return void
	 */
	public static function ap_addon_options() {
		// Bail if no permission.
		if ( ! current_user_can( 'manage_options' ) ) {
			exit;
		}

		define( 'IFRAME_REQUEST', true );
		iframe_header();

		wp_enqueue_style( 'anspress-main', ap_get_theme_url( 'css/main.css' ), [], AP_VERSION );
		wp_enqueue_style( 'ap-admin-css', ANSPRESS_URL . 'assets/ap-admin.css', [], AP_VERSION );
		wp_enqueue_style( 'anspress-fonts', ap_get_theme_url( 'css/fonts.css' ), [], AP_VERSION );
		?>
			<script type="text/javascript">
				currentQuestionID = '<?php the_ID(); ?>';
				apTemplateUrl = '<?php echo ap_get_theme_url( 'js-template', false, false ); ?>';
				aplang = {};
				apShowComments  = false;
			</script>
		<?php
		wp_enqueue_script( 'selectize', ANSPRESS_URL . 'assets/js/min/selectize.min.js', [ 'jquery' ], AP_VERSION );
		wp_enqueue_script( 'anspress-main', ANSPRESS_URL . 'assets/js/min/main.min.js', [ 'jquery', 'jquery-form', 'backbone', 'underscore' ], AP_VERSION );
		wp_enqueue_script( 'anspress-admin-js', ANSPRESS_URL . 'assets/js/min/ap-admin.min.js', [ 'anspress-main' ], AP_VERSION, true );

		$addon     = ap_get_addon( ap_sanitize_unslash( 'addon', 'r' ) );
		$from_args = array(
			'form_action' => admin_url( 'admin.php?action=ap_save_addon_options&active_addon=' . $addon['id'] ),
			'ajax_submit' => false,
		);

		/**
		 * Filter AnsPress add-on options form.
		 *
		 * @param array $form_args Array for form arguments.
		 * @since 4.1.0
		 */
		$form_args = apply_filters( 'ap_addon_form_args', $from_args );

		$form_name = str_replace( '.php', '', $addon['id'] );
		$form_name = str_replace( '/', '_', $form_name );

		echo '<div class="ap-thickboxw">';
		// Show updated notice.
		if ( ap_isset_post_value( 'updated' ) === '1' ) {
			echo '<div class="notice notice-success is-dismissible">';
			echo '<p>' . esc_html__( 'Addon options updated successfully!', 'anspress-question-answer' ) . '</p>';
			echo '</div>';
		}

		if ( anspress()->form_exists( 'addon-' . $form_name ) ) {
			anspress()->get_form( 'addon-' . $form_name )->generate( $form_args );
		} else {
			echo '<p class="ap-form-nofields">' . sprintf( esc_attr__( 'There is no option registered by this addon. Custom options can be registered by using hook: %s', 'anspress-question-answer' ), 'ap_form_addon-' . $form_name ) . '</p>';
		}
		echo '</div>';

		iframe_footer();
		exit;
	}

	/**
	 * Saves addons options and redirect back to addon form.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	public static function save_addon_options() {
		// Bail if no permission.
		if ( ! current_user_can( 'manage_options' ) ) {
			exit;
		}

		$form_name  = ap_sanitize_unslash( 'ap_form_name', 'r' );
		$addon_name = ap_sanitize_unslash( 'active_addon', 'r' );

		$addon   = ap_get_active_addons( $addon_name );
		$updated = false;

		// Process submit form.
		if ( ! empty( $form_name ) && anspress()->get_form( $form_name )->is_submitted() ) {
			$form   = anspress()->get_form( $form_name );
			$values = $form->get_values();

			if ( ! $form->have_errors() ) {
				$options = get_option( 'anspress_opt', [] );

				foreach ( $values as $key => $opt ) {
					$options[ $key ] = $opt['value'];
				}

				update_option( 'anspress_opt', $options );
				wp_cache_delete( 'anspress_opt', 'ap' );
				wp_cache_delete( 'anspress_opt', 'ap' );

				$updated = true;

				// Flush rewrite rules.
				ap_opt( 'ap_flush', 'true' );
			}
		}

		wp_redirect( admin_url( 'admin.php?action=ap_addon_options&addon=' . $addon_name . '&updated=' . $updated ) );
		exit;
	}

	/**
	 * Output custom script and styles in admin_footer.
	 *
	 * @return void
	 * @since 4.1.8
	 */
	public static function admin_footer() {
		?>
			<style>
				#adminmenu .anspress-license-count{
					background: #0073aa;
				}
			</style>
		<?php
	}

}
