<?php
/**
 * Add category support in AnsPress questions.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @copyright 2014 AnsPress.io & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   WordPress/AnsPress/Category
 *
 * Addon Name:    Category
 * Addon URI:     https://anspress.io
 * Description:   Add category support in AnsPress questions.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Category addon for AnsPress
 */
class AnsPress_Category {

	/**
	 * Initialize the class.
	 *
	 * @since 4.0
	 */
	public static function init() {
		SELF::includes();

		ap_register_page( 'category', __( 'Category', 'anspress-question-answer' ), array( __CLASS__, 'category_page' ), false );
		ap_register_page( 'categories', __( 'Categories', 'anspress-question-answer' ), array( __CLASS__, 'categories_page' ) );

		anspress()->add_action( 'init', __CLASS__, 'register_question_categories', 1 );
		anspress()->add_action( 'ap_option_groups', __CLASS__, 'load_options' );
		anspress()->add_action( 'admin_enqueue_scripts', __CLASS__, 'admin_enqueue_scripts' );
		anspress()->add_action( 'ap_load_admin_assets', __CLASS__, 'ap_load_admin_assets' );
		anspress()->add_action( 'ap_admin_menu', __CLASS__, 'admin_category_menu' );
		anspress()->add_action( 'ap_display_question_metas', __CLASS__, 'ap_display_question_metas', 10, 2 );
		anspress()->add_action( 'ap_assets_js', __CLASS__, 'ap_assets_js' );
		anspress()->add_filter( 'term_link', __CLASS__, 'term_link_filter', 10, 3 );
		anspress()->add_action( 'ap_ask_form_fields', __CLASS__, 'ask_from_category_field', 10, 2 );
		anspress()->add_action( 'ap_processed_new_question', __CLASS__, 'after_new_question', 0, 2 );
		anspress()->add_action( 'ap_processed_update_question', __CLASS__, 'after_new_question', 0, 2 );
		anspress()->add_filter( 'ap_page_title', __CLASS__, 'page_title' );
		anspress()->add_filter( 'ap_breadcrumbs', __CLASS__, 'ap_breadcrumbs' );
		anspress()->add_action( 'terms_clauses', __CLASS__, 'terms_clauses', 10, 3 );
		anspress()->add_filter( 'ap_list_filters', __CLASS__, 'ap_list_filters' );
		anspress()->add_action( 'question_category_add_form_fields', __CLASS__, 'image_field_new' );
		anspress()->add_action( 'question_category_edit_form_fields', __CLASS__, 'image_field_edit' );
		anspress()->add_action( 'create_question_category', __CLASS__, 'save_image_field' );
		anspress()->add_action( 'edited_question_category', __CLASS__, 'save_image_field' );
		anspress()->add_action( 'ap_rewrite_rules', __CLASS__, 'rewrite_rules', 10, 3 );
		anspress()->add_action( 'ap_hover_card_cat', __CLASS__, 'hover_card_category' );
		anspress()->add_filter( 'ap_main_questions_args', __CLASS__, 'ap_main_questions_args' );
		anspress()->add_filter( 'ap_question_subscribers_action_id', __CLASS__, 'subscribers_action_id' );
		anspress()->add_filter( 'ap_ask_btn_link', __CLASS__, 'ap_ask_btn_link' );
		anspress()->add_filter( 'ap_canonical_url', __CLASS__, 'ap_canonical_url' );
		anspress()->add_filter( 'wp_head', __CLASS__, 'category_feed' );
		anspress()->add_filter( 'manage_edit-question_category_columns', __CLASS__, 'column_header' );
		anspress()->add_filter( 'manage_question_category_custom_column', __CLASS__, 'column_content', 10, 3 );

		// List filtering.
		anspress()->add_action( 'ap_ajax_load_filter_category', __CLASS__, 'load_filter_category' );
		anspress()->add_filter( 'ap_list_filter_active_category', __CLASS__, 'filter_active_category', 10, 2 );
	}

	/**
	 * Include required files
	 */
	public static function includes() {
		require_once( ANSPRESS_ADDONS_DIR . '/free/category/widget.php' );
	}

	/**
	 * Category page layout
	 */
	public static function category_page() {
		global $questions, $question_category, $wp;
		$category_id = sanitize_title( get_query_var( 'q_cat' ) );

		$question_args = array(
			'tax_query' => array(
				array(
					'taxonomy' => 'question_category',
					'field' => is_numeric( $category_id ) ? 'id' : 'slug',
					'terms' => array( $category_id ),
				),
			),
		);

		$question_category = get_term_by( 'slug', $category_id, 'question_category' ); //@codingStandardsIgnoreLine.

		if ( $question_category ) {
			$questions = ap_get_questions( $question_args );

			/**
			 * This action can be used to show custom message before category page.
			 *
			 * @param object $question_category Current question category.
			 * @since 1.4.2
			 */
			do_action( 'ap_before_category_page', $question_category );

			include( ap_get_theme_location( 'addons/category/category.php' ) );
		} else {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			include ap_get_theme_location( 'not-found.php' );
		}
	}

	/**
	 * Categories page layout
	 */
	public static function categories_page() {
		global $question_categories, $ap_max_num_pages, $ap_per_page;

		$paged 							= max( 1, get_query_var( 'paged' ) );
		$per_page           = ap_opt( 'categories_per_page' );
		$total_terms        = wp_count_terms( 'question_category', [ 'hide_empty' => false, 'parent' => 0 ] );
		$offset             = $per_page * ( $paged - 1) ;
		$ap_max_num_pages   = ceil( $total_terms / $per_page );

		$order = ap_opt( 'categories_page_order' ) == 'ASC' ? 'ASC' : 'DESC';

		$cat_args = array(
			'parent'        => 0,
			'number'        => $per_page,
			'offset'        => $offset,
			'hide_empty'    => false,
			'orderby'       => ap_opt( 'categories_page_orderby' ),
			'order'         => $order,
		);

		/**
		 * FILTER: ap_categories_shortcode_args
		 * Filter applied before getting categories.
		 *
		 * @var array
		 * @since 1.0
		 */
		$cat_args = apply_filters( 'ap_categories_shortcode_args', $cat_args );
		$question_categories = get_terms( 'question_category' , $cat_args );
		include ap_get_theme_location( 'addons/category/categories.php' );
	}

	/**
	 * Register category taxonomy for question cpt.
	 *
	 * @return void
	 * @since 2.0
	 */
	public static function register_question_categories() {
		ap_add_default_options([
			'form_category_orderby'   => 'count',
			'categories_page_order'   => 'DESC',
			'categories_page_orderby' => 'count',
			'categories_page_slug'    => 'categories',
			'category_page_slug'      => 'category',
			'categories_per_page'     => 20,
			'categories_image_height' => 150,
		]);

		ap_register_menu( 'ANSPRESS_CATEGORIES_PAGE_URL', __( 'Categories', 'anspress-question-answer' ), ap_get_link_to( 'categories' ) );

		/**
		 * Labels for category taxonomy.
		 *
		 * @var array
		 */
		$categories_labels = array(
			'name' 				        => __( 'Question Categories', 'anspress-question-answer' ),
			'singular_name' 	    => _x( 'Category', 'anspress-question-answer' ),
			'all_items' 		      => __( 'All Categories', 'anspress-question-answer' ),
			'add_new_item' 		    => _x( 'Add New Category', 'anspress-question-answer' ),
			'edit_item' 		      => __( 'Edit Category', 'anspress-question-answer' ),
			'new_item' 			      => __( 'New Category', 'anspress-question-answer' ),
			'view_item' 		      => __( 'View Category', 'anspress-question-answer' ),
			'search_items' 		    => __( 'Search Category', 'anspress-question-answer' ),
			'not_found' 		      => __( 'Nothing Found', 'anspress-question-answer' ),
			'not_found_in_trash'  => __( 'Nothing found in Trash', 'anspress-question-answer' ),
			'parent_item_colon'   => '',
		);

		/**
		 * FILTER: ap_question_category_labels
		 * Filter ic called before registering question_category taxonomy
		 */
		$categories_labels = apply_filters( 'ap_question_category_labels',  $categories_labels );

		/**
		 * Arguments for category taxonomy
		 *
		 * @var array
		 * @since 2.0
		 */
		$category_args = array(
			'hierarchical' => true,
			'labels'       => $categories_labels,
			'rewrite'      => true,
		);

		/**
		 * FILTER: ap_question_category_args
		 * Filter ic called before registering question_category taxonomy
		 */
		$category_args = apply_filters( 'ap_question_category_args',  $category_args );

		/**
		 * Now let WordPress know about our taxonomy
		 */
		register_taxonomy( 'question_category', array( 'question' ), $category_args );

	}

	/**
	 * Register Categories options
	 */
	public static function load_options() {
		ap_register_option_section( 'addons', basename( __FILE__ ), __( 'Categories', 'anspress-question-answer' ), array(
			array(
				'name'              => 'form_category_orderby',
				'label'             => __( 'Ask form category order', 'anspress-question-answer' ),
				'description'       => __( 'Set how you want to order categories in form.', 'anspress-question-answer' ),
				'type'              => 'select',
				'options'			=> array(
					'ID' 			=> __( 'ID', 'anspress-question-answer' ),
					'name' 			=> __( 'Name', 'anspress-question-answer' ),
					'slug' 			=> __( 'Slug', 'anspress-question-answer' ),
					'count' 		=> __( 'Count', 'anspress-question-answer' ),
					'term_group' 	=> __( 'Group', 'anspress-question-answer' ),
					),
			),

			array(
				'name'              => 'categories_page_orderby',
				'label'             => __( 'Categries page order by', 'anspress-question-answer' ),
				'description'       => __( 'Set how you want to order categories in categories page.', 'anspress-question-answer' ),
				'type'              => 'select',
				'options'			=> array(
					'ID' 			=> __( 'ID', 'anspress-question-answer' ),
					'name' 			=> __( 'Name', 'anspress-question-answer' ),
					'slug' 			=> __( 'Slug', 'anspress-question-answer' ),
					'count' 		=> __( 'Count', 'anspress-question-answer' ),
					'term_group' 	=> __( 'Group', 'anspress-question-answer' ),
					),
			),

			array(
				'name'              => 'categories_page_order',
				'label'             => __( 'Categries page order', 'anspress-question-answer' ),
				'description'       => __( 'Set how you want to order categories in categories page.', 'anspress-question-answer' ),
				'type'              => 'select',
				'options'			=> array(
					'ASC' 			=> __( 'Ascending', 'anspress-question-answer' ),
					'DESC' 			=> __( 'Descending', 'anspress-question-answer' ),
				),
			),

			array(
				'name' 		=> 'categories_page_slug',
				'label' 	=> __( 'Categories page slug', 'anspress-question-answer' ),
				'desc' 		=> __( 'Slug categories page', 'anspress-question-answer' ),
				'type' 		=> 'text',
				'show_desc_tip' => false,
			),

			array(
				'name' 		=> 'category_page_slug',
				'label' 	=> __( 'Category page slug', 'anspress-question-answer' ),
				'desc' 		=> __( 'Slug for category page', 'anspress-question-answer' ),
				'type' 		=> 'text',
				'show_desc_tip' => false,
			),

			array(
				'name' 		=> 'categories_page_title',
				'label' 	=> __( 'Categories title', 'anspress-question-answer' ),
				'desc' 		=> __( 'Title of the categories page', 'anspress-question-answer' ),
				'type' 		=> 'text',
				'show_desc_tip' => false,
			),
			array(
				'name' 		=> 'categories_per_page',
				'label' 	=> __( 'Category per page', 'anspress-question-answer' ),
				'desc' 		=> __( 'Category to show per page', 'anspress-question-answer' ),
				'type' 		=> 'number',
				'show_desc_tip' => false,
			),
			array(
				'name' 		=> 'categories_image_height',
				'label' 	=> __( 'Categories image height', 'anspress-question-answer' ),
				'desc' 		=> __( 'Image height in categories page', 'anspress-question-answer' ),
				'type' 		=> 'number',
				'show_desc_tip' => false,
			),
		));
	}

	/**
	 * Enqueue required script
	 */
	public static function admin_enqueue_scripts() {
		if ( ! ap_load_admin_assets() ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	/**
	 * Load admin assets in categories page.
	 *
	 * @param boolean $return Return.
	 * @return boolean
	 */
	public static function ap_load_admin_assets( $return ) {
		$page = get_current_screen();
		if ( 'question_category' === $page->taxonomy ) {
			return true;
		}

		return $return;
	}

	/**
	 * Add category menu in wp-admin.
	 *
	 * @since 2.0
	 */
	public static function admin_category_menu() {
		add_submenu_page( 'anspress', __( 'Questions Category', 'anspress-question-answer' ), __( 'Category', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_category' );
	}

	/**
	 * Append meta display.
	 *
	 * @param  	array   $metas Display meta items.
	 * @param 	integer $question_id  Question id.
	 * @return 	array
	 * @since 	1.0
	 */
	public static function ap_display_question_metas( $metas, $question_id ) {
		if ( ap_post_have_terms( $question_id ) && ! is_singular( 'question' ) ) {
			$metas['categories'] = ap_question_categories_html( array( 'label' => '<i class="apicon-category"></i>' ) );
		}

		return $metas;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param array $js JavaScripts.
	 * @since 1.0
	 */
	public static function ap_assets_js( $js ) {
		if ( ap_current_page() === 'category' ) {
			$js['main']['active'] = true;
		}
		return $js;
	}

	/**
	 * Filter category permalink.
	 *
	 * @param  string $url      Default taxonomy url.
	 * @param  object $term     WordPress term object.
	 * @param  string $taxonomy Current taxonomy slug.
	 * @return string
	 */
	public static function term_link_filter( $url, $term, $taxonomy ) {
		if ( 'question_category' === $taxonomy ) {
			if ( get_option( 'permalink_structure' ) != '' ) {
				 return ap_get_link_to( array( 'ap_page' => ap_get_category_slug(), 'q_cat' => $term->slug ) );
			} else {
				return add_query_arg( array( 'ap_page' => ap_get_category_slug(), 'q_cat' => $term->term_id ), ap_base_page_link() );
			}
		}
		return $url;
	}

	/**
	 * Add category field in ask form.
	 *
	 * @param  	array 	$args 		Ask form arguments.
	 * @param  	boolean $editing 	true if is edit form.
	 * @return 	array
	 * @since 	2.0
	 */
	public static function ask_from_category_field( $args, $editing ) {
		if ( wp_count_terms( 'question_category' ) == 0 ) { // WPCS: loose comparison okay.
			return $args;
		}

		global $editing_post;

		$catgeory = ap_sanitize_unslash( 'category', 'request' );

		if ( $editing ) {
			$category = get_the_terms( $editing_post->ID, 'question_category' );
			$catgeory = $category[0]->term_id;
		}

		$args['fields'][] = array(
			'name' 		    => 'category',
			'label' 	    => __( 'Category', 'anspress-question-answer' ),
			'type'  	    => 'taxonomy_select',
			'value' 	    => ( ! empty( $catgeory ) ? $catgeory: '' ),
			'taxonomy' 	  => 'question_category',
			'orderby' 	  => ap_opt( 'form_category_orderby' ),
			'desc' 		    => __( 'Select a topic that best fits your question', 'anspress-question-answer' ),
			'order' 	    => 6,
			'sanitize'    => [ 'only_int' ],
			'validate'    => [ 'required' ],
		);

		return $args;
	}

	/**
	 * Things to do after creating a question.
	 *
	 * @param  	integer $post_id    Questions ID.
	 * @param  	object  $post       Question post object.
	 * @return 	void
	 * @since 	1.0
	 */
	public static function after_new_question( $post_id, $post ) {
		global $validate;

		if ( empty( $validate ) ) {
			return;
		}

		$fields = $validate->get_sanitized_fields();

		if ( isset( $fields['category'] ) ) {
			wp_set_post_terms( $post_id, $fields['category'], 'question_category' );
		}

	}

	/**
	 * Add category page title.
	 *
	 * @param  string $title AnsPress page title.
	 * @return string
	 */
	public static function page_title( $title ) {
		if ( is_question_categories() ) {
			$title = ap_opt( 'categories_page_title' );
		} elseif ( is_question_category() ) {
			$category_id = sanitize_title( get_query_var( 'q_cat' ) );
			$category = get_term_by( is_numeric( $category_id ) ? 'id' : 'slug', $category_id, 'question_category' ); //@codingStandardsIgnoreLine

			if ( $category ) {
				$title = $category->name;
			} else {
				$title = __( 'No matching category found', 'anspress-question-answer' );
			}
		}

		return $title;
	}

	/**
	 * Add category nav in AnsPress breadcrumbs.
	 *
	 * @param  array $navs Breadcrumbs nav array.
	 * @return array
	 */
	public static function ap_breadcrumbs( $navs ) {
		if ( is_question() && taxonomy_exists( 'question_category' ) ) {
			$cats = get_the_terms( get_question_id(), 'question_category' );

			if ( $cats ) {
				$navs['category'] = array( 'title' => $cats[0]->name, 'link' => get_term_link( $cats[0], 'question_category' ), 'order' => 2 ); //@codingStandardsIgnoreLine
			}
		} elseif ( is_question_category() ) {
			$category_id = sanitize_text_field( get_query_var( 'q_cat' ) );
			$category = get_term_by( is_numeric( $category_id ) ? 'id' : 'slug', $category_id, 'question_category' ); //@codingStandardsIgnoreLine
			$navs['page'] = array( 'title' => __( 'Categories', 'anspress-question-answer' ), 'link' => ap_get_link_to( 'categories' ), 'order' => 8 );
			$navs['category'] = array( 'title' => $category->name, 'link' => get_term_link( $category, 'question_category' ), 'order' => 8 );//@codingStandardsIgnoreLine
		} elseif ( is_question_categories() ) {
			$navs['page'] = array( 'title' => __( 'Categories', 'anspress-question-answer' ), 'link' => ap_get_link_to( 'categories' ), 'order' => 8 );
		}

		return $navs;
	}

	/**
	 * Modify term clauses.
	 *
	 * @param array $pieces MySql query parts.
	 * @param array $taxonomies Taxonomies.
	 * @param array $args Args.
	 */
	public static function terms_clauses( $pieces, $taxonomies, $args ) {

		if ( ! in_array( 'question_category', $taxonomies, true ) || ! isset( $args['ap_query'] ) || 'subscription' !== $args['ap_query'] ) {
			return $pieces;
		}

		global $wpdb;

		$pieces['join']     = $pieces['join'] . ' INNER JOIN ' . $wpdb->prefix . 'ap_meta apmeta ON t.term_id = apmeta.apmeta_actionid';
		$pieces['where']    = $pieces['where'] . " AND apmeta.apmeta_type='subscriber' AND apmeta.apmeta_param='category' AND apmeta.apmeta_userid='" . $args['user_id'] . "'";

		return $pieces;
	}

	/**
	 * Add category sorting in list filters.
	 *
	 * @param array $filters Filters.
	 * @return array
	 */
	public static function ap_list_filters( $filters ) {
		global $wp;

		if ( ! isset( $wp->query_vars['ap_categories'] ) && ! is_question_category() ) {
			$filters['category'] = array(
				'title' => __( 'Category', 'anspress-question-answer' ),
				'items' => [],
				'search' => true,
				'multiple' => true,
			);
		}

		return $filters;
	}

	/**
	 * Custom question category fields.
	 *
	 * @param  array $term Term.
	 * @return void
	 */
	public static function image_field_new( $term ) {
		?>
		<div class='form-field term-image-wrap'>
			<label for='ap_image'><?php esc_attr_e( 'Image', 'anspress-question-answer' ); ?></label>
			<a href="#" id="ap-category-upload" class="button" data-action="ap_media_uplaod" data-title="<?php esc_attr_e( 'Upload image', 'anspress-question-answer' ); ?>" data-urlc="#ap_category_media_url" data-idc="#ap_category_media_id"><?php esc_attr_e( 'Upload image', 'anspress-question-answer' ); ?></a>
			<input id="ap_category_media_url" type="hidden" name="ap_category_image_url" value="">
			<input id="ap_category_media_id" type="hidden" name="ap_category_image_id" value="">

			<p class="description"><?php esc_attr_e( 'Category image', 'anspress-question-answer' ); ?></p>
		</div>

		<div class='form-field term-image-wrap'>
			<label for='ap_icon'><?php esc_attr_e( 'Category icon class', 'anspress-question-answer' ); ?></label>
			<input id="ap_icon" type="text" name="ap_icon" value="">
			<p class="description"><?php esc_attr_e( 'Font icon class, if image not set', 'anspress-question-answer' ); ?></p>
		</div>

		<div class='form-field term-image-wrap'>
			<label for='ap-category-color'><?php esc_attr_e( 'Icon background color', 'anspress-question-answer' ); ?></label>
			<input id="ap-category-color" type="text" name="ap_color" value="">
			<p class="description"><?php esc_attr_e( 'Set background color to be used with icon', 'anspress-question-answer' ); ?></p>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('#ap-category-color').wpColorPicker();
				});
			</script>
		</div>
		<?php
	}

	/**
	 * Image field in category form.
	 *
	 * @param object $term Term.
	 */
	public static function image_field_edit( $term ) {
		$term_meta = get_term_meta( $term->term_id, 'ap_category', true );
		$term_meta = wp_parse_args( $term_meta, array(
			'image' => [ 'id' => '', 'url' => '' ],
			'icon'  => '',
			'color' => '',
		) );

		?>
			<tr class='form-field form-required term-name-wrap'>
				<th scope='row'>
					<label for='custom-field'><?php esc_attr_e( 'Image', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<a href="#" id="ap-category-upload" class="button" data-action="ap_media_uplaod" data-title="<?php esc_attr_e( 'Upload image', 'anspress-question-answer' ); ?>" data-idc="#ap_category_media_id" data-urlc="#ap_category_media_url"><?php esc_attr_e( 'Upload image', 'anspress-question-answer' ); ?></a>

					<?php if ( ! empty( $term_meta['image'] ) && ! empty( $term_meta['image']['url'] ) ) { ?>
						<img id="ap_category_media_preview" data-action="ap_media_value" src="<?php echo esc_url( $term_meta['image']['url'] ); ?>" />
					<?php } ?>

					<input id="ap_category_media_url" type="hidden" data-action="ap_media_value" name="ap_category_image_url" value="<?php echo esc_url( $term_meta['image']['url'] ); ?>">

					<input id="ap_category_media_id" type="hidden" data-action="ap_media_value" name="ap_category_image_id" value="<?php echo esc_attr( $term_meta['image']['id'] ); ?>">
					<a href="#" id="ap-category-upload-remove" data-action="ap_media_remove"><?php esc_attr_e( 'Remove image', 'anspress-question-answer' ); ?></a>

					<p class='description'><?php esc_attr_e( 'Featured image for category', 'anspress-question-answer' ); ?></p>
				</td>
			</tr>
			<tr class='form-field form-required term-name-wrap'>
				<th scope='row'>
					<label for='custom-field'><?php esc_attr_e( 'Category icon class', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<input id="ap_icon" type="text" name="ap_icon" value="<?php echo esc_attr( $term_meta['icon'] ); ?>">
					<p class="description"><?php esc_attr_e( 'Font icon class, if image not set', 'anspress-question-answer' ); ?></p>
				</td>
			</tr>
			<tr class='form-field form-required term-name-wrap'>
				<th scope='row'>
					<label for='ap-category-color'><?php esc_attr_e( 'Category icon color', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<input id="ap-category-color" type="text" name="ap_color" value="<?php echo esc_attr( $term_meta['color'] ); ?>">
					<p class="description"><?php esc_attr_e( 'Font icon class, if image not set', 'anspress-question-answer' ); ?></p>
					<script type="text/javascript">
						jQuery(document).ready(function(){
							jQuery('#ap-category-color').wpColorPicker();
						});
					</script>
				</td>
			</tr>
		<?php
	}

	/**
	 * Process and save category images.
	 *
	 * @param  integer $term_id Term id.
	 */
	public static function save_image_field( $term_id ) {

		$image_url = ap_isset_post_value( 'ap_category_image_url', false );
		$image_id = ap_isset_post_value( 'ap_category_image_id', false );
		$icon = ap_isset_post_value( 'ap_icon', false );
		$color = ap_isset_post_value( 'ap_color', false );

		if ( ( $image_url && $image_id ) || $icon || $color ) {
			// Get options from database - if not a array create a new one.
			$term_meta = get_term_meta( $term_id, 'ap_category', true );

			if ( ! is_array( $term_meta ) ) {
				$term_meta = [];
			}

			if ( ! is_array( $term_meta['image'] ) ) {
				$term_meta['image'] = [];
			}

			// Get value and save it into the database.
			$term_meta['image']['url'] = $image_url ? esc_url( $image_url ) : '';
			$term_meta['image']['id'] = $image_id ? (int) $image_id : '';

			if ( $icon ) {
				$term_meta['icon'] = sanitize_text_field( $icon );
			}

			if ( $color ) {
				$term_meta['color'] = sanitize_text_field( $color );
			}

			// Delete meta if empty.
			if ( empty( $term_meta ) ) {
				delete_term_meta( $term_id, 'ap_category' );
			}

			update_term_meta( $term_id, 'ap_category', $term_meta );
		}
	}

	/**
	 * Add category pages rewrite rule.
	 *
	 * @param  array   $rules AnsPress rules.
	 * @param  string  $slug Slug.
	 * @param  integer $base_page_id Base page ID.
	 * @return array
	 */
	public static function rewrite_rules( $rules, $slug, $base_page_id ) {
		$base = 'index.php?page_id=' . $base_page_id . '&ap_page=' ;
		$cat_rules = array(
			$slug . ap_get_categories_slug() . '/page/?([0-9]{1,})/?$' => $base . 'categories&paged=$matches[#]',
			$slug . ap_get_category_slug() . '/([^/]+)/page/?([0-9]{1,})/?$' => $base . 'category&q_cat=$matches[#]&paged=$matches[#]',
			$slug . ap_get_category_slug() . '/([^/]+)/?' => $base . 'category&q_cat=$matches[#]',
			$slug . ap_get_categories_slug() . '/?' => $base . 'categories',
		);

		return $cat_rules + $rules;
	}

	/**
	 * Output hover card for term.
	 *
	 * @param  integer $id User ID.
	 * @since  3.0.0
	 */
	public static function hover_card_category( $id ) {
		$cache = get_transient( 'ap_category_card_' . $id );

		if ( false !== $cache ) {
			ap_ajax_json( $cache );
		}

		$category = get_term( $id, 'question_category' );
		$sub_cat_count = count( get_term_children( $category->term_id, 'question_category' ) );

		$data = array(
			'template' => 'category-hover',
			'disableAutoLoad' => 'true',
			'apData' => array(
				'id' 			=> $category->term_id,
				'name' 			=> $category->name,
				'link' 			=> get_category_link( $category ), // @codingStandardsIgnoreLine.
				'image' 		=> ap_get_category_image( $category->term_id, 90 ),
				'icon' 			=> ap_get_category_icon( $category->term_id ),
				'description' 	=> $category->description,
				'question_count' 	=> sprintf( _n( '%d Question', '%d Questions', $category->count, 'anspress-question-answer' ),  $category->count ),
				'sub_category' 	=> array(
					'have' => $sub_cat_count > 0,
					'count' => sprintf( _n( '%d Sub category', '%d Sub categories', $sub_cat_count, 'anspress-question-answer' ), $sub_cat_count ),
				),
			),
		);

		/**
		 * Filter user hover card data.
		 *
		 * @param  array $data Card data.
		 * @return array
		 * @since  3.0.0
		 */
		$data = apply_filters( 'ap_category_hover_data', $data );
		set_transient( 'ap_category_card_' . $id, $data, HOUR_IN_SECONDS );
		ap_ajax_json( $data );
	}

	/**
	 * Filter main questions query args. Modify and add category args.
	 *
	 * @param  array $args Questions args.
	 * @return array
	 */
	public static function ap_main_questions_args( $args ) {
		global $questions, $wp;
		$query = $wp->query_vars;

		$categories_operator = ! empty( $wp->query_vars['ap_categories_operator'] ) ? $wp->query_vars['ap_categories_operator'] : 'IN';
		$current_filter = ap_get_current_list_filters( 'category' );

		if ( isset( $query['ap_categories'] ) && is_array( $query['ap_categories'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'question_category',
				'field'    => 'slug',
				'terms'    => $query['ap_categories'],
				'operator' => $categories_operator,
			);
		} elseif ( ! empty( $current_filter ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'question_category',
				'field'    => 'term_id',
				'terms'    => explode( ',', sanitize_comma_delimited( $current_filter ) ),
			);
		}

		return $args;
	}

	/**
	 * Subscriber action ID.
	 *
	 * @param  integer $action_id Current action ID.
	 * @return integer
	 */
	public static function subscribers_action_id( $action_id ) {
		if ( is_question_category() ) {
			global $question_category;
			$action_id = $question_category->term_id;
		}

		return $action_id;
	}

	/**
	 * Filter ask button link to append current category link.
	 *
	 * @param  string $link Ask button link.
	 * @return string
	 */
	public static function ap_ask_btn_link( $link ) {
		if ( is_question_category() ) {
			global $question_category;
			return $link . '?category=' . $question_category->term_id;
		}

		return $link;
	}

	/**
	 * Filter canonical URL when in category page.
	 *
	 * @param  string $canonical_url url.
	 * @return string
	 */
	public static function ap_canonical_url( $canonical_url ) {
		if ( is_question_category() ) {
			global $question_category;

			if ( ! $question_category ) {
				$category_id = sanitize_text_field( get_query_var( 'q_cat' ) );
				$question_category = get_term_by( is_numeric( $category_id ) ? 'id' : 'slug', $category_id, 'question_category' ); // @codingStandardsIgnoreLine.
			}

			return get_term_link( $question_category ); // @codingStandardsIgnoreLine.
		}

		return $canonical_url;
	}

	/**
	 * Category feed link in head.
	 */
	public static function category_feed() {

		if ( is_question_category() ) {
			global $question_category;

			if ( ! $question_category ) {
				$category_id = sanitize_title( get_query_var( 'q_cat' ) );
				$question_category = get_term_by( is_numeric( $category_id ) ? 'id' : 'slug', $category_id, 'question_category' );
			}

			echo '<link href="' . esc_url( home_url( 'feed' ) ) . '?post_type=question&question_category=' . esc_url( $question_category->slug ) . '" title="' . esc_attr__( 'Question category feed', 'anspress-question-answer' ) . '" type="application/rss+xml" rel="alternate">';
		}
	}

	/**
	 * Ajax callback for loading order by filter.
	 *
	 * @since 4.0.0
	 */
	public static function load_filter_category() {
		$filter = ap_sanitize_unslash( 'filter', 'r' );
		check_ajax_referer( 'filter_' . $filter, '__nonce' );

		$search = ap_sanitize_unslash( 'search', 'r', false );

		ap_ajax_json( array(
			'success'  => true,
			'items'    => ap_get_category_filter( $search ),
			'multiple' => true,
			'nonce'    => wp_create_nonce( 'filter_' . $filter ),
		));
	}

	/**
	 * Output active category in filter
	 *
	 * @since 4.0.0
	 */
	public static function filter_active_category( $active, $filter ) {
		$current_filters = ap_get_current_list_filters( 'category' );

		if ( ! empty( $current_filters ) ) {
			$args = array(
				'hierarchical'  => true,
				'hide_if_empty' => true,
				'number'        => 2,
				'include'       => $current_filters,
			);

			$terms = get_terms( 'question_category', $args );

			if ( $terms ) {
				$active_terms = [];
				foreach ( (array) $terms as $t ) {
					$active_terms[] = $t->name;
				}

				$count = count( $current_filters );
				$more_label = sprintf( __( ', %d+', 'anspress-question-answer' ), $count - 2 );

				return ': <span class="ap-filter-active">' . implode( ', ', $active_terms ) . ( $count > 2 ? $more_label : ''  ) . '</span>';
			}
		}
	}

	/**
	 * Column header.
	 *
	 * @param array $columns Category columns.
	 * @return array
	 */
	public static function column_header( $columns ) {
		$columns['icon'] = 'Icon';
		return $columns;
	}

	/**
	 * Icon column content.
	 */
	public static function column_content( $value, $column_name, $tax_id ) {
		if ( 'icon' === $column_name ) {
			ap_category_icon( $tax_id );
		}
	}
}

// Init addon.
AnsPress_Category::init();
