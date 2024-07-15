<?php
/**
 * The Category module.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Category;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\Plugin;

/**
 * Category module class.
 *
 * @since 5.0.0
 */
class CategoryModule extends AbstractModule {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'registerQuestionCategories' ), 1 );
		add_action( 'init', array( $this, 'registerBlocks' ) );
		add_action( 'rest_api_init', array( $this, 'registerCustomTermMeta' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		add_action( 'ap_load_admin_assets', array( $this, 'ap_load_admin_assets' ) );
		add_action( 'ap_admin_menu', array( $this, 'admin_category_menu' ) );
		add_action( 'ap_enqueue', array( $this, 'ap_assets_js' ) );
		add_filter( 'term_link', array( $this, 'termLinkFilter' ), 10, 3 );
		add_action( 'admin_footer', array( $this, 'categoryScripts' ) );

		add_filter( 'ap_breadcrumbs', array( $this, 'ap_breadcrumbs' ) );
		add_action( 'question_category_add_form_fields', array( $this, 'customFieldsOnNewForm' ) );
		add_action( 'question_category_edit_form_fields', array( $this, 'customFieldsOnEditForm' ) );
		add_action( 'create_question_category', array( $this, 'save_image_field' ) );
		add_action( 'edited_question_category', array( $this, 'save_image_field' ) );
		add_action( 'ap_rewrites', array( $this, 'rewrite_rules' ), 10, 3 );
		add_filter( 'wp_head', array( $this, 'category_feed' ) );
		add_filter( 'manage_question_category_custom_column', array( $this, 'column_content' ), 10, 3 );
		add_filter( 'ap_current_page', array( $this, 'ap_current_page' ) );
	}

	/**
	 * Register category taxonomy for question cpt.
	 *
	 * @return void
	 * @since 2.0
	 */
	public function registerQuestionCategories() {

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
			'show_in_rest'       => true,
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
		register_taxonomy( 'question_category', array( 'question' ), $category_args );
	}

	/**
	 * Register blocks.
	 *
	 * @return void
	 */
	public function registerBlocks() {
		register_block_type( Plugin::getPathTo( 'build/frontend/categories' ) );
	}

	/**
	 * Register custom term meta.
	 *
	 * @return void
	 */
	public function registerCustomTermMeta() {
		register_term_meta(
			'question_category',
			'ap_category',
			array(
				'single'       => true,
				'type'         => 'object',
				'show_in_rest' => array(
					'default'          => null,
					'prepare_callback' => null,
					'schema'           => array(
						'type'       => 'object',
						'properties' => array(
							'image' => array(
								'type'       => 'object',
								'properties' => array(
									'id'  => array( 'type' => 'integer' ),
									'url' => array( 'type' => 'string' ),
								),
							),
							'color' => array( 'type' => 'string' ),
						),
						'context'    => array( 'view', 'edit' ),
					),
				),
			)
		);
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
	 * @param boolean $ret Return.
	 * @return boolean
	 */
	public function ap_load_admin_assets( $ret ) {
		$page = get_current_screen();
		if ( 'question_category' === $page->taxonomy ) {
			return true;
		}

		return $ret;
	}

	/**
	 * Add category menu in wp-admin.
	 *
	 * @since 2.0
	 * @since 4.2.0 Renamed menu from "Category".
	 */
	public function admin_category_menu() {
		add_submenu_page( 'anspress', __( 'Question Categories', 'anspress-question-answer' ), __( 'Question Categories', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_category' );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param array $js JavaScripts.
	 * @since 1.0
	 */
	public function ap_assets_js( $js ) {
		if ( ap_current_page() === 'category' ) {
			wp_enqueue_script( 'anspress-theme' );
		}
	}

	/**
	 * Filter category permalink.
	 *
	 * @param  string $url      Default taxonomy url.
	 * @param  object $term     WordPress term object.
	 * @param  string $taxonomy Current taxonomy slug.
	 * @return string
	 */
	public function termLinkFilter( $url, $term, $taxonomy ) {
		if ( 'question_category' === $taxonomy ) {
			if ( get_option( 'permalink_structure' ) !== '' ) {
				$opt          = get_option( 'ap_categories_path', 'categories' );
				$default_lang = '';

				// Support polylang permalink.
				if ( function_exists( 'pll_default_language' ) ) {
					$default_lang = pll_get_term_language( $term->term_id ) ? pll_get_term_language( $term->term_id ) : pll_default_language();
				}

				return home_url( $default_lang . '/' . $opt ) . '/' . $term->slug . '/';
			} else {
				return add_query_arg(
					array(
						'ap_page'           => 'category',
						'question_category' => $term->slug,
					),
					home_url()
				);
			}
		}

		return $url;
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
				$navs['category'] = array(
					'title' => $cats[0]->name,
					'link'  => get_term_link( $cats[0], 'question_category' ),
					'order' => 2,
				);
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
	 * Custom question category fields.
	 *
	 * @param  array $term Term.
	 * @return void
	 */
	public function customFieldsOnNewForm( $term ) {
		?>
		<div class='form-field term-image-wrap'>
			<label for='ap_image'><?php esc_attr_e( 'Image', 'anspress-question-answer' ); ?></label>
			<a href="#" id="ap-category-upload" class="button" data-action="ap_media_uplaod" data-title="<?php esc_attr_e( 'Upload image', 'anspress-question-answer' ); ?>" data-urlc="#ap_category_media_url" data-idc="#ap_category_media_id"><?php esc_attr_e( 'Upload image', 'anspress-question-answer' ); ?></a>
			<input id="ap_category_media_url" type="hidden" data-action="ap_media_value" name="ap_category_image_url" value="">
			<input id="ap_category_media_id" type="hidden" data-action="ap_media_value" name="ap_category_image_id" value="">

			<p class="description"><?php esc_attr_e( 'Category image', 'anspress-question-answer' ); ?></p>
		</div>
		<div class='form-field term-color-wrap'>
			<label for='ap_color'><?php esc_attr_e( 'Background color', 'anspress-question-answer' ); ?></label>
			<input id="ap-category-color" type="text" name="ap_color" value="">
			<p class="description"><?php esc_attr_e( 'Set background color to be used when background is not present', 'anspress-question-answer' ); ?></p>

		</div>


		<?php
	}

	/**
	 * Image field in category form.
	 *
	 * @param object $term Term.
	 */
	public function customFieldsOnEditForm( $term ) {
		$term_meta = get_term_meta( $term->term_id, 'ap_category', true );
		$term_meta = wp_parse_args(
			$term_meta,
			array(
				'image' => array(
					'id'  => '',
					'url' => '',
				),
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

					<?php if ( is_array( $term_meta ) && ! empty( $term_meta['image'] ) && ! empty( $term_meta['image']['url'] ) ) { ?>
						<a href="#" id="ap-category-upload-remove" data-action="ap_media_remove"><?php esc_attr_e( 'Remove image', 'anspress-question-answer' ); ?></a>
					<?php } ?>

					<p class='description'><?php esc_attr_e( 'Featured image for category', 'anspress-question-answer' ); ?></p>
				</td>
			</tr>
			<tr class='form-field term-name-wrap'>
				<th scope='row'>
					<label for='ap-category-color'><?php esc_attr_e( 'Background color', 'anspress-question-answer' ); ?></label>
				</th>
				<td>
					<input id="ap-category-color" type="text" name="ap_color" value="<?php echo esc_attr( $term_meta['color'] ); ?>">
					<p class="description"><?php esc_attr_e( 'Set background color to be used when background is not present', 'anspress-question-answer' ); ?></p>
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
		$color     = ap_isset_post_value( 'ap_color', '' );

		if ( current_user_can( 'manage_categories' ) ) {
			// Get options from database - if not a array create a new one.
			$term_meta = get_term_meta( $term_id, 'ap_category', true );

			if ( ! is_array( $term_meta ) ) {
				$term_meta = array( 'image' => array() );
			}

			if ( isset( $term_meta['image'] ) && ! is_array( $term_meta['image'] ) ) {
				$term_meta['image'] = array();
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
		}
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
		$lang_rule    = str_replace( ap_base_page_slug() . '/', '', $slug );
		$lang_rewrite = str_replace( ap_opt( 'base_page' ), '', $base_page_id );

		$cat_rules = array(
			$lang_rule . $base_slug . '/([^/]+)/page/?([0-9]{1,})/?$' => $lang_rewrite . 'index.php?question_category=$matches[#]&paged=$matches[#]&ap_page=category',
			$lang_rule . $base_slug . '/([^/]+)/?$' => $lang_rewrite . 'index.php?question_category=$matches[#]&ap_page=category',
		);

		return $cat_rules + $rules;
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
			echo '<link href="' . esc_url( home_url( 'feed' ) ) . '?post_type=question&question_category=' . esc_attr( $question_category->slug ) . '" title="' . esc_attr__( 'Question category feed', 'anspress-question-answer' ) . '" type="application/rss+xml" rel="alternate">';
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
	 * Add category column in admin.
	 */
	public function categoryScripts(): void {
		// Check current screen.
		$screen = get_current_screen();

		if ( 'edit-question_category' === $screen->id ) {
			?>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery('#ap-category-color').wpColorPicker();

					jQuery('[data-action="ap_media_uplaod"]').on('click', function (e) {
						e.preventDefault();
						$btn = jQuery(this);
						var image = wp.media({
							title: jQuery(this).data('title'),
							// mutiple: true if you want to upload multiple files at once
							multiple: false
						}).open().on('select', function (e) {
							// This will return the selected image from the Media Uploader, the result is an object
							var uploaded_image = image.state().get('selection').first();
							// We convert uploaded_image to a JSON object to make accessing it easier
							// Output to the console uploaded_image
							var image_url = uploaded_image.toJSON().url;
							var image_id = uploaded_image.toJSON().id;

							// Let's assign the url value to the input field
							jQuery($btn.data('urlc')).val(image_url);
							jQuery($btn.data('idc')).val(image_id);

							if (!jQuery($btn.data('urlc')).prev().is('img')) {
								jQuery($btn.data('urlc')).before('<img id="ap_category_media_preview" data-action="ap_media_value" src="' + image_url + '" />');
								jQuery($btn.data("idc")).after('<a href="#" id="ap-category-upload-remove" data-action="ap_media_remove">'+ removeImage + "</a>");
							} else {
								jQuery($btn.data('urlc')).prev().attr('src', image_url);
							}
						});
					});

					jQuery( document ).on( 'click', '[data-action="ap_media_remove"]', function (e) {
						e.preventDefault();
						jQuery('input[data-action="ap_media_value"]').val('');
						jQuery('img[data-action="ap_media_value"]').remove();
						jQuery( this ).remove();
					} );
				});
			</script>
			<?php
		}
	}
}
