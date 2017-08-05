<?php
/**
 * AnsPresss admin class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( 'license.php' );

/**
 * This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
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
		anspress()->add_action( 'admin_enqueue_scripts', __CLASS__, 'enqueue_admin_styles' );
		anspress()->add_action( 'admin_enqueue_scripts', __CLASS__, 'enqueue_admin_scripts' );
		anspress()->add_action( 'admin_menu', __CLASS__, 'add_plugin_admin_menu' );
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
		anspress()->add_action( 'admin_post_anspress_options', __CLASS__, 'process_option_form' );
		anspress()->add_action( 'admin_post_anspress_update_db', __CLASS__, 'update_db' );
		anspress()->add_action( 'admin_post_anspress_create_base_page', __CLASS__, 'anspress_create_base_page' );
		anspress()->add_action( 'admin_notices', __CLASS__, 'anspress_notice' );
	}

	/**
	 * Include files required in wp-admin
	 */
	public static function includes() {
		require_once( 'functions.php' );
		require_once( 'options-fields.php' );

		new AP_license();
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 */
	public static function enqueue_admin_styles() {
		if ( ! ap_load_admin_assets() ) {
			return;
		}

		$dir = ap_env_dev() ? 'css' : 'css/min';
		$min = ap_env_dev() ? '' : '.min';

		wp_enqueue_style( 'ap-admin-css', ANSPRESS_URL . 'assets/ap-admin.css' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'anspress-fonts', ap_get_theme_url( 'css/fonts.css' ), array(), AP_VERSION );
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 */
	public static function enqueue_admin_scripts() {
		$page = get_current_screen();

		$dir = ap_env_dev() ? 'js' : 'js/min';
		$min = ap_env_dev() ? '' : '.min';

		if ( ! ap_load_admin_assets() ) {
			return;
		}

		wp_enqueue_script( 'anspress-common', ANSPRESS_URL . 'assets/' . $dir . '/common' . $min . '.js', [ 'jquery', 'jquery-form', 'backbone', 'underscore' ], AP_VERSION );

		if ( 'toplevel_page_anspress' === $page->base ) {
			wp_enqueue_script( 'ap-chart-js', '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.1.4/Chart.min.js' );
		}

		wp_enqueue_script( 'anspress-admin-js', ANSPRESS_URL . 'assets/' . $dir . '/ap-admin' . $min . '.js' , [ 'anspress-common' ], AP_VERSION, true );

		?>
			<script type="text/javascript">
				currentQuestionID = '<?php the_ID(); ?>';
			</script>
		<?php

		if ( 'post' === $page->base && 'question' === $page->post_type ) {
			wp_enqueue_script( 'ap-admin-app-js', ANSPRESS_URL . 'assets/' . $dir . '/admin-app' . $min . '.js' , [], AP_VERSION, true );
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
		$flagged = ap_total_flagged_count();

		$q_flagged = $flagged['questions'];
		$a_flagged = $flagged['answers'];

		$question_count = wp_count_posts( 'question', 'readable' );
		$answer_count = wp_count_posts( 'answer', 'readable' );
		$types = array(
			'question' 	=> ( ! empty( $question_count->moderate ) ? $question_count->moderate : 0 ) + $q_flagged->total,
			'answer' 	=> ( ! empty( $answer_count->moderate ) ? $answer_count->moderate : 0 ) + $a_flagged->total,
			'flagged' 	=> $q_flagged->total + $a_flagged->total,
		);

		$types['total'] = array_sum( $types );
		$types_html = array();

		foreach ( (array) $types as $k => $count ) {
			if ( $count > 0 ) {
				$types_html[ $k ] = ' <span class="update-plugins count"><span class="plugin-count">' . number_format_i18n( $count ) . '</span></span>';
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
		$pos = self::get_free_menu_position( 42.9 );

		add_menu_page( 'AnsPress', 'AnsPress' . $counts['total'], 'delete_pages', 'anspress', array( __CLASS__, 'dashboard_page' ), ANSPRESS_URL . '/assets/answer.png', $pos );

		add_submenu_page( 'anspress', __( 'All Questions', 'anspress-question-answer' ), __( 'All Questions', 'anspress-question-answer' ) . $counts['question'], 'delete_pages', 'edit.php?post_type=question', '' );

		add_submenu_page( 'anspress', __( 'New Question', 'anspress-question-answer' ), __( 'New Question', 'anspress-question-answer' ), 'delete_pages', 'post-new.php?post_type=question', '' );

		add_submenu_page( 'anspress', __( 'All Answers', 'anspress-question-answer' ), __( 'All Answers', 'anspress-question-answer' ) . $counts['answer'], 'delete_pages', 'edit.php?post_type=answer', '' );

		add_submenu_page( 'ap_select_question', __( 'Select question', 'anspress-question-answer' ), __( 'Select question', 'anspress-question-answer' ), 'delete_pages', 'ap_select_question', array( __CLASS__, 'display_select_question' ) );

		/**
		 * ACTION: ap_admin_menu
		 *
		 * @since unknown
		 */
		do_action( 'ap_admin_menu' );

		add_submenu_page( 'anspress', __( 'AnsPress Options', 'anspress-question-answer' ), __( 'Options & Add-ons', 'anspress-question-answer' ), 'manage_options', 'anspress_options', array( __CLASS__, 'display_plugin_admin_page' ) );

		$submenu['anspress'][500] = array( 'Theme & Extensions', 'manage_options' , 'https://anspress.io/themes/' );

		add_submenu_page( 'anspress-hidden', __( 'About AnsPress', 'anspress-question-answer' ), __( 'About AnsPress', 'anspress-question-answer' ), 'manage_options', 'anspress_about', array( __CLASS__, 'display_plugin_about_page' ) );

		add_submenu_page( 'anspress-hidden', __( 'Upgrade AnsPress', 'anspress-question-answer' ), __( 'Upgrade AnsPress', 'anspress-question-answer' ), 'manage_options', 'anspress_upgrade', array( __CLASS__, 'upgrade_page' ) );

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
	 * @param  	string $parent_file	parent menu item.
	 * @return 	string
	 */
	public static function tax_menu_correction( $parent_file ) {
		global $current_screen;
		$taxonomy = $current_screen->taxonomy;

		if ( 'question_category' === $taxonomy || 'question_tags' === $taxonomy || 'question_label' === $taxonomy || 'rank' === $taxonomy || 'badge' === $taxonomy ) {
			$parent_file = 'anspress';
		}
		return $parent_file;
	}

	/**
	 * Render the settings page for this plugin.
	 */
	public static function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}

	/**
	 * Load extensions page layout
	 */
	public static function display_plugin_addons_page() {
		include_once( 'views/addons.php' );
	}

	/**
	 * Load about page layout
	 */
	public static function display_plugin_about_page() {
		include_once( 'views/about.php' );
	}

	/**
	 * Load dashboard page layout.
	 *
	 * @since 2.4
	 */
	public static function dashboard_page() {
		include_once( 'views/dashboard.php' );
	}

	/**
	 * Load dashboard page layout.
	 *
	 * @since 2.4
	 */
	public static function upgrade_page() {
		include_once( 'views/upgrade.php' );
	}

	/**
	 * Control the ouput of question selection.
	 *
	 * @return void
	 * @since 2.0.0
	 */
	public static function display_select_question() {
		include_once( 'views/select_question.php' );
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
				'about' => '<a href="' . admin_url( 'admin.php?page=anspress_about' ) . '">' . __( 'About', 'anspress-question-answer' ) . '</a>',
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
			$options = ap_opt();
			$page = get_page( ap_opt( 'base_page' ) );
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
		require_once( 'meta-box.php' );
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
			return $post->ID;
		}

		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $post->ID;
		}

		if ( 'answer' === $post->post_type ) {
			$parent_q = (int) ap_sanitize_unslash( 'post_parent', 'p' );
			if ( empty( $parent_q ) ) {
				return $post->ID;
			} else {
				global $wpdb;
				$wpdb->update( $wpdb->posts, array( 'post_parent' => $parent_q ), array( 'ID' => $post->ID ) ); // db call ok, cache ok.
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
	 * Redirect to cusotm post location for error message.
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
		$db_fields = false;

		/*if ( false ) {
			$db_fields = array( 'parent' => 'parent', 'id' => 'post_parent' );
		}*/

		$walker = new Walker_Nav_Menu_Checklist( $db_fields );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		); ?>

		<div id="anspress-div">
			<div id="tabs-panel-anspress-all" class="tabs-panel tabs-panel-active">
			<ul id="anspress-checklist-pop" class="categorychecklist form-no-clear" >
				<?php
					echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $menu_items ), 0, (object) array( 'walker' => $walker ) );
				?>
			</ul>

			<p class="button-controls">
				<span class="list-controls">
					<a href="<?php
						echo esc_url(add_query_arg(
							array(
								'anspress-all' => 'all',
								'selectall'     => 1,
							),
							remove_query_arg( $removed_args )
						));
					?>#anspress-menu-mb" class="select-all"><?php _e( 'Select All', 'anspress-question-answer' ); ?></a>
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

			$search_q = ap_parse_search_string( get_search_query( ) );

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
			$authors = $query->query_vars['ap_author_name'];
			$authors = implode( "','", array_map( 'sanitize_title_for_query', array_unique( (array) $authors ) ) );
			$authors = "'" . rtrim( $authors, ",'" ) . "'";
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
		 $label = '';

		if ( in_array( $post->post_type, [ 'question', 'answer' ], true ) ) {
			if ( 'moderate' === $post->post_status ) {
				 $complete = ' selected=\'selected\'';
				 $label = '<span id=\'post-status-display\'>' . esc_attr__( 'Moderate', 'anspress-question-answer' ) . '</span>';
			} elseif ( 'private_post' === $post->post_status ) {
				 $complete = ' selected=\'selected\'';
				 $label = '<span id=\'post-status-display\'>' . esc_attr__( 'Private Post', 'anspress-question-answer' ) . '</span>';
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
	 * Process AnsPress option form.
	 *
	 * @since 4.0.0
	 */
	public static function process_option_form() {
		$redirect = admin_url( 'admin.php?page=anspress_options' );

		if ( ap_isset_post_value( '__nonce' ) && ap_verify_nonce( 'nonce_option_form' ) && current_user_can( 'manage_options' ) ) {

			$settings = get_option( 'anspress_opt', array() );
			$groups   = ap_get_option_groups();
			$active   = ap_sanitize_unslash( 'fields_group', 'request' );
			$ap_active_section = ap_isset_post_value( 'ap_active_section', '' );

			// If active is set.
			if ( '' !== $active && '' !== $ap_active_section ) {

				$default_opt = ap_default_options();

				$i = 0;
				// Check $_POST value against fields.
				foreach ( (array) $groups[ $active ]['sections'] as $section_slug => $section ) {
					if ( $section_slug === $ap_active_section ) {
						foreach ( (array) $section['fields'] as $k => $f ) {

							if ( ! isset( $f['name'] ) ) {
								continue;
							}

							if ( isset( $f['type'] ) && 'textarea' === $f['type'] ) {
								$value = esc_textarea( wp_unslash( ap_isset_post_value( $f['name'], '' ) ) );
							} else {
								$value = ap_sanitize_unslash( $f['name'], 'request' );
							}

							// If reset then get value from default option.
							if ( ap_sanitize_unslash( 'reset', 'p' ) ) {
								$value = $default_opt[ $f['name'] ];
							}

							// Set checkbox field value as 0 when empty.
							if ( isset( $f['type'] ) && 'checkbox' === $f['type'] && empty( $value ) ) {
								$value = '0';
							}

							if ( isset( $value ) ) {
								$settings[ $f['name'] ] = $value;
							} else {
								unset( $settings[ $f['name'] ] );
							}
						}
					}
					$i++;
				}

				update_option( 'anspress_opt', $settings );
				wp_cache_delete( 'anspress_opt', 'ap' );
			}

			flush_rewrite_rules();
			$redirect = admin_url( 'admin.php?page=anspress_options&updated=true&option_page='. $active );
		}

		wp_safe_redirect( $redirect );
	}

	/**
	 * Show AnsPress notices.
	 */
	public static function anspress_notice() {
		$page = get_current_screen();
		$anspress_updates = get_option( 'anspress_updates', [] );
		$have_updates = empty( $anspress_updates ) || in_array( false, $anspress_updates, true );

		$messages = array(
			'db' => [
				'type'    => 'error',
				'message' => __( 'AnsPress database is not updated.', 'anspress-question-answer' ),
				'button'  => ' <a class="button" href="' . admin_url( 'admin-post.php?action=anspress_update_db' ) . '">' . __( 'Update now', 'anspress-question-answer' ) . '</a>',
				'show'    => ( get_option( 'anspress_db_version' ) != AP_DB_VERSION )
			],
			'upgrade' => [
				'type'    => 'error',
				'message' => __( 'You must continue to upgrade AnsPress data.', 'anspress-question-answer' ),
				'button'  => ' <a class="button" href="' . admin_url( 'admin.php?page=anspress_upgrade' ) . '">' . __( 'Upgrade now', 'anspress-question-answer' ) . '</a>',
				'show'    => ( get_option( 'ap_update_helper', false ) && 'admin_page_anspress_upgrade' !== $page->base && $have_updates ),
			],
			'upgrade' => [
				'type'    => 'error',
				'message' => __( 'AnsPress base page does not exists. AnsPress require a base page to work properly.', 'anspress-question-answer' ),
				'button'  => ' <a href="' . admin_url( 'admin-post.php?action=anspress_create_base_page' ) . '">' . __( 'Set automatically', 'anspress-question-answer' ) . '</a> ' . __( 'Or', 'anspress-question-answer' ) . ' <a href="' . admin_url( 'admin.php?page=anspress_options' ) . '">' . __( 'Set existing page as base page', 'anspress-question-answer' ) . '</a>',
				'show'    => ( ! ap_get_post( ap_opt( 'base_page' ) ) ),
			],
		);

		foreach ( $messages as $msg ) {
			if ( $msg['show'] ) {
				$class = 'notice notice-' . $msg['type'];
				printf( '<div class="%1$s"><p>%2$s%3$s</p></div>', esc_attr( $class ), esc_html( $msg['message'] ), $msg['button'] );
			}
		}
	}

	/**
	 * Updates AnsPress DB tables.
	 */
	public static function update_db() {
		if ( current_user_can( 'manage_options' ) ) {
			$activate = AP_Activate::get_instance();
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
		}

		wp_redirect( admin_url( 'admin.php?page=anspress_options' ) );
	}
}
