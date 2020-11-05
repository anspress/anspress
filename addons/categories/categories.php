<?php
/**
 * Add category support in AnsPress questions.
 *
 * @author     Rahul Aryan <rah12@live.com>
 * @copyright  2014 anspress.net & Rahul Aryan
 * @license    GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link       https://anspress.net
 * @package    AnsPress
 * @subpackage Categories Addon
 * @since      4.1.8
 */

namespace AnsPress\Addons;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Categories addon class.
 */
class Categories extends \AnsPress\Singleton {

	/**
	 * Refers to a single instance of this class.
	 *
	 * @var null|object
	 * @since 4.1.8
	 */
	public static $instance = null;

	/**
	 * Initialize the class.
	 *
	 * @since 4.0.0
	 */
	protected function __construct() {
		ap_register_page( 'category', __( 'Category', 'anspress-question-answer' ), array( $this, 'category_page' ), false );
		ap_register_page( 'categories', __( 'Categories', 'anspress-question-answer' ), array( $this, 'categories_page' ) );

		anspress()->add_action( 'init', $this, 'register_question_categories', 1 );
		anspress()->add_action( 'ap_form_addon-categories', $this, 'load_options' );
		anspress()->add_action( 'admin_enqueue_scripts', $this, 'admin_enqueue_scripts' );
		anspress()->add_action( 'ap_load_admin_assets', $this, 'ap_load_admin_assets' );
		anspress()->add_action( 'ap_admin_menu', $this, 'admin_category_menu' );
		anspress()->add_action( 'ap_display_question_metas', $this, 'ap_display_question_metas', 10, 2 );
		anspress()->add_action( 'ap_assets_js', $this, 'ap_assets_js' );
		anspress()->add_filter( 'term_link', $this, 'term_link_filter', 10, 3 );
		anspress()->add_action( 'ap_question_form_fields', $this, 'ap_question_form_fields' );
		anspress()->add_action( 'save_post_question', $this, 'after_new_question', 0, 2 );
		anspress()->add_filter( 'ap_breadcrumbs', $this, 'ap_breadcrumbs' );
		anspress()->add_action( 'terms_clauses', $this, 'terms_clauses', 10, 3 );
		anspress()->add_filter( 'ap_list_filters', $this, 'ap_list_filters' );
		anspress()->add_action( 'question_category_add_form_fields', $this, 'image_field_new' );
		anspress()->add_action( 'question_category_edit_form_fields', $this, 'image_field_edit' );
		anspress()->add_action( 'create_question_category', $this, 'save_image_field' );
		anspress()->add_action( 'edited_question_category', $this, 'save_image_field' );
		anspress()->add_action( 'ap_rewrites', $this, 'rewrite_rules', 10, 3 );
		anspress()->add_filter( 'ap_main_questions_args', $this, 'ap_main_questions_args' );
		anspress()->add_filter( 'ap_question_subscribers_action_id', $this, 'subscribers_action_id' );
		anspress()->add_filter( 'ap_ask_btn_link', $this, 'ap_ask_btn_link' );
		// anspress()->add_filter( 'ap_canonical_url', $this, 'ap_canonical_url' );
		anspress()->add_filter( 'wp_head', $this, 'category_feed' );
		anspress()->add_filter( 'manage_edit-question_category_columns', $this, 'column_header' );
		anspress()->add_filter( 'manage_question_category_custom_column', $this, 'column_content', 10, 3 );
		anspress()->add_filter( 'ap_current_page', $this, 'ap_current_page' );
		anspress()->add_action( 'posts_pre_query', $this, 'modify_query_category_archive', 9999, 2 );

		// List filtering.
		anspress()->add_action( 'ap_ajax_load_filter_category', $this, 'load_filter_category' );
		anspress()->add_filter( 'ap_list_filter_active_category', $this, 'filter_active_category', 10, 2 );

		anspress()->add_action( 'widgets_init', $this, 'widget' );
	}

	/**
	 * Category page layout.
	 *
	 * @since 4.1.0 Use `get_queried_object()` to get current term.
	 * @since 4.1.8 Added new filter `ap_category_questions_args`.
	 */
	public function category_page() {
		$question_args = array(
			'tax_query' => array(
				array(
					'taxonomy' => 'question_category',
					'field'    => 'id',
					'terms'    => array( get_queried_object_id() ),
				),
			),
		);

		$question_category = get_queried_object();

		if ( $question_category ) {

			/**
			 * Filter category page question list query arguments.
			 *
			 * @param array $args Wp_Query arguments.
			 * @since 4.1.8
			 */
			$question_args = apply_filters( 'ap_category_questions_args', $question_args );

			anspress()->questions = ap_get_questions( $question_args );

			/**
			 * This action can be used to show custom message before category page.
			 *
			 * @param object $question_category Current question category.
			 * @since 1.4.2
			 */
			do_action( 'ap_before_category_page', $question_category );

			include ap_get_theme_location( 'addons/category/single-category.php' );
		}
	}

	/**
	 * Categories page layout
	 */
	public function categories_page() {
		global $question_categories, $ap_max_num_pages, $ap_per_page;

		$paged            = max( 1, get_query_var( 'paged' ) );
		$per_page         = ap_opt( 'categories_per_page' );
		$total_terms      = wp_count_terms(
			'question_category', [
				'hide_empty' => false,
				'parent'     => 0,
			]
		);
		$offset           = $per_page * ( $paged - 1 );
		$ap_max_num_pages = ceil( $total_terms / $per_page );

		$order = ap_opt( 'categories_page_order' ) == 'ASC' ? 'ASC' : 'DESC';

		$cat_args = array(
			'parent'     => 0,
			'number'     => $per_page,
			'offset'     => $offset,
			'hide_empty' => false,
			'orderby'    => ap_opt( 'categories_page_orderby' ),
			'order'      => $order,
		);

		/**
		 * Filter applied before getting categories.
		 *
		 * @param array $cat_args `get_terms` arguments.
		 * @since 1.0
		 */
		$cat_args = apply_filters( 'ap_categories_shortcode_args', $cat_args );

		$question_categories = get_terms( 'question_category', $cat_args );
		include ap_get_theme_location( 'addons/category/categories.php' );
	}

	/**
	 * Register category taxonomy for question cpt.
	 *
	 * @return void
	 * @since 2.0
	 */
	public function register_question_categories() {
		ap_add_default_options(
			[
				'form_category_orderby'   => 'count',
				'categories_page_order'   => 'DESC',
				'categories_page_orderby' => 'count',
				'category_page_slug'      => 'category',
				'categories_per_page'     => 20,
				'categories_image_height' => 150,
			]
		);

		/**
		 * Labels for category taxonomy.
		 *
		 * @var array
		 */
		$categories_labels = array(
			'name'               => __( 'Question Categories', 'anspress-question-answer' ),
			'singular_name'      => __( 'Category', 'anspress-question-answer' ),
			'all_items'          => __( 'All Categories', 'anspress-question-answer' ),
			'add_new_item'       => __( 'Add New Category', 'anspress-question-answer' ),
			'edit_item'          => __( 'Edit Category', 'anspress-question-answer' ),
			'new_item'           => __( 'New Category', 'anspress-question-answer' ),
			'view_item'          => __( 'View Category', 'anspress-question-answer' ),
			'search_items'       => __( 'Search Category', 'anspress-question-answer' ),
			'not_found'          => __( 'Nothing Found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'anspress-question-answer' ),
			'parent_item_colon'  => '',
		);

		/**
		 * FILTER: ap_question_category_labels
		 * Filter ic called before registering question_category taxonomy
		 */
		$categories_labels = apply_filters( 'ap_question_category_labels', $categories_labels );

		/**
		 * Arguments for category taxonomy
		 *
		 * @var array
		 * @since 2.0
		 */
		$category_args = array(
			'hierarchical'       => true,
			'labels'             => $categories_labels,
			'rewrite'            => false,
			'publicly_queryable' => true,
		);

		/**
		 * Filter is called before registering question_category taxonomy.
		 *
		 * @param array $category_args Category arguments.
		 */
		$category_args = apply_filters( 'ap_question_category_args', $category_args );

		/**
		 * Now let WordPress know about our taxonomy
		 */
		register_taxonomy( 'question_category', [ 'question' ], $category_args );
	}

	/**
	 * Register Categories options
	 */
	public function load_options() {
		$opt  = ap_opt();
		$form = array(
			'fields' => array(
				'form_category_orderby'   => array(
					'label'       => __( 'Ask form category order', 'anspress-question-answer' ),
					'description' => __( 'Set how you want to order categories in form.', 'anspress-question-answer' ),
					'type'        => 'select',
					'options'     => array(
						'ID'         => __( 'ID', 'anspress-question-answer' ),
						'name'       => __( 'Name', 'anspress-question-answer' ),
						'slug'       => __( 'Slug', 'anspress-question-answer' ),
						'count'      => __( 'Count', 'anspress-question-answer' ),
						'term_group' => __( 'Group', 'anspress-question-answer' ),
					),
					'value'       => $opt['form_category_orderby'],
				),
				'categories_page_orderby' => array(
					'label'       => __( 'Categries page order by', 'anspress-question-answer' ),
					'description' => __( 'Set how you want to order categories in categories page.', 'anspress-question-answer' ),
					'type'        => 'select',
					'options'     => array(
						'ID'         => __( 'ID', 'anspress-question-answer' ),
						'name'       => __( 'Name', 'anspress-question-answer' ),
						'slug'       => __( 'Slug', 'anspress-question-answer' ),
						'count'      => __( 'Count', 'anspress-question-answer' ),
						'term_group' => __( 'Group', 'anspress-question-answer' ),
					),
					'value'       => $opt['categories_page_orderby'],
				),
				'categories_page_order'   => array(
					'label'       => __( 'Categries page order', 'anspress-question-answer' ),
					'description' => __( 'Set how you want to order categories in categories page.', 'anspress-question-answer' ),
					'type'        => 'select',
					'options'     => array(
						'ASC'  => __( 'Ascending', 'anspress-question-answer' ),
						'DESC' => __( 'Descending', 'anspress-question-answer' ),
					),
					'value'       => $opt['categories_page_order'],
				),
				'category_page_slug'      => array(
					'label' => __( 'Category page slug', 'anspress-question-answer' ),
					'desc'  => __( 'Slug for category page', 'anspress-question-answer' ),
					'value' => $opt['category_page_slug'],
				),
				'categories_per_page'     => array(
					'label'   => __( 'Category per page', 'anspress-question-answer' ),
					'desc'    => __( 'Category to show per page', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['categories_per_page'],
				),
				'categories_image_height' => array(
					'label'   => __( 'Categories image height', 'anspress-question-answer' ),
					'desc'    => __( 'Image height in categories page', 'anspress-question-answer' ),
					'subtype' => 'number',
					'value'   => $opt['categories_image_height'],
				),
			),
		);

		return $form;
	}

	/**
	 * Enqueue required script
	 */
	public function admin_enqueue_scripts() {
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
	public function ap_load_admin_assets( $return ) {
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
	public function admin_category_menu() {
		add_submenu_page( 'anspress', __( 'Questions Category', 'anspress-question-answer' ), __( 'Category', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_category' );
	}

	/**
	 * Append meta display.
	 *
	 * @param   array   $metas Display meta items.
	 * @param   integer $question_id  Question id.
	 * @return  array
	 * @since   1.0
	 */
	public function ap_display_question_metas( $metas, $question_id ) {
		if ( ap_post_have_terms( $question_id ) ) {
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
	public function ap_assets_js( $js ) {
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
	public function term_link_filter( $url, $term, $taxonomy ) {
		if ( 'question_category' === $taxonomy ) {
			if ( get_option( 'permalink_structure' ) != '' ) {
				$opt = get_option( 'ap_categories_path', 'categories' );
				return home_url( $opt ) . '/' . $term->slug . '/';
			} else {
				return add_query_arg(
					[
						'ap_page'           => 'category',
						'question_category' => $term->slug,
					], home_url()
				);
			}
		}

		return $url;
	}

	/**
	 * Add category field in ask form.
	 *
	 * @param   array $form Ask form arguments.
	 * @return  array
	 * @since   4.1.0
	 */
	public function ap_question_form_fields( $form ) {
		if ( wp_count_terms( 'question_category' ) == 0 ) { // WPCS: loose comparison okay.
			return $form;
		}

		$editing_id  = ap_sanitize_unslash( 'id', 'r' );
		$category_id = ap_sanitize_unslash( 'category', 'r' );

		$form['fields']['category'] = array(
			'label'    => __( 'Category', 'anspress-question-answer' ),
			'desc'     => __( 'Select a topic that best fits your question.', 'anspress-question-answer' ),
			'type'     => 'select',
			'options'  => 'terms',
			'order'    => 2,
			'validate' => 'required,not_zero',
		);

		// Add value when editing post.
		if ( ! empty( $editing_id ) ) {
			$categories = get_the_terms( $editing_id, 'question_category' );

			if ( $categories ) {
				$form['fields']['category']['value'] = $categories[0]->term_id;
			}
		} elseif ( ! empty( $category_id ) ) {
			$form['fields']['category']['value'] = (int) $category_id;
		}

		return $form;
	}

	/**
	 * Things to do after creating a question.
	 *
	 * @param   integer $post_id    Questions ID.
	 * @param   object  $post       Question post object.
	 * @return  void
	 * @since   1.0
	 */
	public function after_new_question( $post_id, $post ) {
		$values = anspress()->get_form( 'question' )->get_values();

		if ( isset( $values['category']['value'] ) ) {
			wp_set_post_terms( $post_id, $values['category']['value'], 'question_category' );
		}
	}

	/**
	 * Add category nav in AnsPress breadcrumbs.
	 *
	 * @param  array $navs Breadcrumbs nav array.
	 * @return array
	 */
	public function ap_breadcrumbs( $navs ) {
		if ( is_question() && taxonomy_exists( 'question_category' ) ) {
			$cats = get_the_terms( get_question_id(), 'question_category' );

			if ( $cats ) {
				$navs['category'] = array( 'title' => $cats[0]->name, 'link' => get_term_link( $cats[0], 'question_category' ), 'order' => 2 ); //@codingStandardsIgnoreLine
			}
		} elseif ( is_question_category() ) {
			$category     = get_queried_object();
			$navs['page'] = array(
				'title' => __( 'Categories', 'anspress-question-answer' ),
				'link'  => ap_get_link_to( 'categories' ),
				'order' => 8,
			);

			$navs['category'] = array(
				'title' => $category->name,
				'link'  => get_term_link( $category, 'question_category' ),
				'order' => 8,
			);
		} elseif ( is_question_categories() ) {
			$navs['page'] = array(
				'title' => __( 'Categories', 'anspress-question-answer' ),
				'link'  => ap_get_link_to( 'categories' ),
				'order' => 8,
			);
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
	public function terms_clauses( $pieces, $taxonomies, $args ) {

		if ( ! in_array( 'question_category', $taxonomies, true ) || ! isset( $args['ap_query'] ) || 'subscription' !== $args['ap_query'] ) {
			return $pieces;
		}

		global $wpdb;

		$pieces['join']  = $pieces['join'] . ' INNER JOIN ' . $wpdb->prefix . 'ap_meta apmeta ON t.term_id = apmeta.apmeta_actionid';
		$pieces['where'] = $pieces['where'] . " AND apmeta.apmeta_type='subscriber' AND apmeta.apmeta_param='category' AND apmeta.apmeta_userid='" . $args['user_id'] . "'";

		return $pieces;
	}

	/**
	 * Add category sorting in list filters.
	 *
	 * @param array $filters Filters.
	 * @return array
	 */
	public function ap_list_filters( $filters ) {
		global $wp;

		if ( ! isset( $wp->query_vars['ap_categories'] ) && ! is_question_category() ) {
			$filters['category'] = array(
				'title'    => __( 'Category', 'anspress-question-answer' ),
				'items'    => [],
				'search'   => true,
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
	public function image_field_new( $term ) {
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
	public function image_field_edit( $term ) {
		$term_meta = get_term_meta( $term->term_id, 'ap_category', true );
		$term_meta = wp_parse_args(
			$term_meta, array(
				'image' => [
					'id'  => '',
					'url' => '',
				],
				'icon'  => '',
				'color' => '',
			)
		);

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
			<tr class='form-field term-name-wrap'>
				<th scope='row'>
					<label for='custom-field'><?php esc_attr_e( 'Category icon class', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<input id="ap_icon" type="text" name="ap_icon" value="<?php echo esc_attr( $term_meta['icon'] ); ?>">
					<p class="description"><?php esc_attr_e( 'Font icon class, if image not set', 'anspress-question-answer' ); ?></p>
				</td>
			</tr>
			<tr class='form-field term-name-wrap'>
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
	public function save_image_field( $term_id ) {

		$image_url = ap_isset_post_value( 'ap_category_image_url', '' );
		$image_id  = ap_isset_post_value( 'ap_category_image_id', '' );
		$icon      = ap_isset_post_value( 'ap_icon', '' );
		$color     = ap_isset_post_value( 'ap_color', '' );

		if ( current_user_can( 'manage_categories' ) ) {
			// Get options from database - if not a array create a new one.
			$term_meta = get_term_meta( $term_id, 'ap_category', true );

			if ( ! is_array( $term_meta ) ) {
				$term_meta = [ 'image' => [] ];
			}

			if ( ! is_array( $term_meta['image'] ) ) {
				$term_meta['image'] = [];
			}

			// Image url.
			if ( ! empty( $image_url ) ) {
				$term_meta['image']['url'] = esc_url( $image_url );
			} else {
				unset( $term_meta['image']['url'] );
			}

			// Image id.
			if ( ! empty( $image_id ) ) {
				$term_meta['image']['id'] = (int) $image_id;
			} else {
				unset( $term_meta['image']['id'] );
			}

			// Category icon.
			if ( ! empty( $icon ) ) {
				$term_meta['icon'] = sanitize_text_field( $icon );
			} else {
				unset( $term_meta['icon'] );
			}

			// Category color.
			if ( ! empty( $color ) ) {
				$term_meta['color'] = sanitize_text_field( $color );
			} else {
				unset( $term_meta['color'] );
			}

			if ( empty( $term_meta['image'] ) ) {
				unset( $term_meta['image'] );
			}

			// Delete meta if empty.
			if ( empty( $term_meta ) ) {
				delete_term_meta( $term_id, 'ap_category' );
			} else {
				update_term_meta( $term_id, 'ap_category', $term_meta );
			}
		} // End if().
	}

	/**
	 * Add category pages rewrite rule.
	 *
	 * @param  array   $rules AnsPress rules.
	 * @param  string  $slug Slug.
	 * @param  integer $base_page_id Base page ID.
	 * @return array
	 * @since unknown
	 * @since 4.1.6 Fixed: category pagination.
	 */
	public function rewrite_rules( $rules, $slug, $base_page_id ) {
		$base_slug = get_page_uri( ap_opt( 'categories_page' ) );
		update_option( 'ap_categories_path', $base_slug, true );

		$cat_rules = array(
			$base_slug . '/([^/]+)/page/?([0-9]{1,})/?$' => 'index.php?question_category=$matches[#]&paged=$matches[#]&ap_page=category',
			$base_slug . '/([^/]+)/?$'                   => 'index.php?question_category=$matches[#]&ap_page=category',
		);

		return $cat_rules + $rules;
	}

	/**
	 * Filter main questions query args. Modify and add category args.
	 *
	 * @param  array $args Questions args.
	 * @return array
	 */
	public function ap_main_questions_args( $args ) {
		global $wp;
		$query = $wp->query_vars;

		$categories_operator = ! empty( $wp->query_vars['ap_categories_operator'] ) ? $wp->query_vars['ap_categories_operator'] : 'IN';
		$current_filter      = ap_get_current_list_filters( 'category' );

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
	public function subscribers_action_id( $action_id ) {
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
	public function ap_ask_btn_link( $link ) {
		if ( is_question_category() ) {
			$question_category = get_queried_object();
			return $link . '?category=' . $question_category->term_id;
		}

		return $link;
	}

	/**
	 * Filter canonical URL when in category page.
	 *
	 * @param  string $canonical_url url.
	 * @return string
	 * @deprecated 4.1.1
	 */
	public function ap_canonical_url( $canonical_url ) {
		if ( is_question_category() ) {
			global $question_category;

			if ( ! $question_category ) {
				$question_category = get_queried_object();
			}

			return get_term_link( $question_category ); // @codingStandardsIgnoreLine.
		}

		return $canonical_url;
	}

	/**
	 * Category feed link in head.
	 */
	public function category_feed() {

		if ( is_question_category() ) {
			$question_category = get_queried_object();
			echo '<link href="' . esc_url( home_url( 'feed' ) ) . '?post_type=question&question_category=' . esc_url( $question_category->slug ) . '" title="' . esc_attr__( 'Question category feed', 'anspress-question-answer' ) . '" type="application/rss+xml" rel="alternate">';
		}
	}

	/**
	 * Ajax callback for loading order by filter.
	 *
	 * @since 4.0.0
	 */
	public function load_filter_category() {
		$filter = ap_sanitize_unslash( 'filter', 'r' );
		check_ajax_referer( 'filter_' . $filter, '__nonce' );

		$search = (string) ap_sanitize_unslash( 'search', 'r', false );
		ap_ajax_json(
			array(
				'success'  => true,
				'items'    => ap_get_category_filter( $search ),
				'multiple' => true,
				'nonce'    => wp_create_nonce( 'filter_' . $filter ),
			)
		);
	}

	/**
	 * Output active category in filter
	 *
	 * @since 4.0.0
	 */
	public function filter_active_category( $active, $filter ) {
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

				$count      = count( $current_filters );
				$more_label = sprintf( __( ', %d+', 'anspress-question-answer' ), $count - 2 );

				return ': <span class="ap-filter-active">' . implode( ', ', $active_terms ) . ( $count > 2 ? $more_label : '' ) . '</span>';
			}
		}
	}

	/**
	 * Column header.
	 *
	 * @param array $columns Category columns.
	 * @return array
	 */
	public function column_header( $columns ) {
		$columns['icon'] = 'Icon';
		return $columns;
	}

	/**
	 * Icon column content.
	 */
	public function column_content( $value, $column_name, $tax_id ) {
		if ( 'icon' === $column_name ) {
			ap_category_icon( $tax_id );
		}
	}

	/**
	 * Modify current page to show category archive.
	 *
	 * @param string $query_var Current page.
	 * @return string
	 * @since 4.1.0
	 */
	public function ap_current_page( $query_var ) {
		if ( 'categories' === $query_var && 'category' === get_query_var( 'ap_page' ) ) {
			return 'category';
		}

		return $query_var;
	}

	/**
	 * Modify main query.
	 *
	 * @param array  $posts  Array of post object.
	 * @param object $query Wp_Query object.
	 * @return void|array
	 * @since 4.1.0
	 */
	public function modify_query_category_archive( $posts, $query ) {
		if ( $query->is_main_query() && $query->is_tax( 'question_category' ) && 'category' === get_query_var( 'ap_page' ) ) {
			$query->found_posts   = 1;
			$query->max_num_pages = 1;
			$page                 = get_page( ap_opt( 'categories_page' ) );
			$page->post_title     = get_queried_object()->name;
			$posts                = [ $page ];
		}

		return $posts;
	}

	/**
	 * Include required files.
	 *
	 * @since 4.1.8
	 */
	public function widget() {
		require_once ANSPRESS_ADDONS_DIR . '/categories/widget.php';
		register_widget( 'Anspress\Widgets\Categories' );
	}
}

// Init addon.
Categories::init();
