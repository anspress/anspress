<?php
/**
 * Add tags support in AnsPress questions.
 *
 * @author    Rahul Aryan <support@anspress.io>
 * @copyright 2014 AnsPress.io & Rahul Aryan
 * @license   GPL-3.0+ https://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://anspress.io
 * @package   WordPress/AnsPress/Category
 *
 * Addon Name:    Tag
 * Addon URI:     https://anspress.io
 * Description:   Add tag support in AnsPress questions.
 * Author:        Rahul Aryan
 * Author URI:    https://anspress.io
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Tags addon for AnsPress
 */
class AnsPress_Tag {

	/**
	 * Initialize the class
	 */
	public static function init() {
		SELF::includes();

		ap_register_page( 'tag', __( 'Tag', 'anspress-question-answer' ), [ __CLASS__, 'tag_page' ], false );
		ap_register_page( 'tags', __( 'Tags', 'anspress-question-answer' ), [ __CLASS__, 'tags_page' ] );

		anspress()->add_action( 'ap_option_groups', __CLASS__, 'option_fields', 20 );
		anspress()->add_action( 'widgets_init', __CLASS__, 'widget_positions' );
		anspress()->add_action( 'init', __CLASS__, 'register_question_tag', 1 );
		anspress()->add_action( 'ap_admin_menu', __CLASS__, 'admin_tags_menu' );
		anspress()->add_action( 'ap_display_question_metas', __CLASS__, 'ap_display_question_metas', 10, 2 );
		anspress()->add_action( 'ap_question_info', __CLASS__, 'ap_question_info' );
		anspress()->add_action( 'ap_assets_js', __CLASS__, 'ap_assets_js' );
		anspress()->add_action( 'ap_enqueue', __CLASS__, 'ap_localize_scripts' );
		anspress()->add_filter( 'term_link', __CLASS__, 'term_link_filter', 10, 3 );
		anspress()->add_action( 'ap_ask_form_fields', __CLASS__, 'ask_from_tag_field', 10, 2 );
		anspress()->add_action( 'ap_ask_fields_validation', __CLASS__, 'ap_ask_fields_validation' );
		anspress()->add_action( 'ap_processed_new_question', __CLASS__, 'after_new_question', 0, 2 );
		anspress()->add_action( 'ap_processed_update_question', __CLASS__, 'after_new_question', 0, 2 );
		anspress()->add_filter( 'ap_page_title', __CLASS__, 'page_title' );
		anspress()->add_filter( 'ap_breadcrumbs', __CLASS__, 'ap_breadcrumbs' );
		anspress()->add_filter( 'terms_clauses', __CLASS__, 'terms_clauses', 10, 3 );
		anspress()->add_filter( 'get_terms', __CLASS__, 'get_terms', 10, 3 );
		anspress()->add_action( 'wp_ajax_ap_tags_suggestion', __CLASS__, 'ap_tags_suggestion' );
		anspress()->add_action( 'wp_ajax_nopriv_ap_tags_suggestion', __CLASS__, 'ap_tags_suggestion' );
		anspress()->add_action( 'ap_rewrite_rules', __CLASS__, 'rewrite_rules', 10, 3 );
		anspress()->add_filter( 'ap_current_page_is', __CLASS__, 'ap_current_page_is' );
		anspress()->add_filter( 'ap_main_questions_args', __CLASS__, 'ap_main_questions_args' );

		// List filtering.
		anspress()->add_filter( 'ap_list_filters', __CLASS__, 'ap_list_filters' );
		anspress()->add_action( 'ap_ajax_load_filter_tag', __CLASS__, 'load_filter_tag' );
		anspress()->add_filter( 'ap_list_filter_active_tag', __CLASS__, 'filter_active_tag', 10, 2 );
	}

	/**
	 * Include required files
	 */
	public static function includes() {

	}

	/**
	 * Tag page layout.
	 */
	public static function tag_page() {
		global $questions, $question_tag;
		$tag_id = sanitize_title( get_query_var( 'q_tag' ) );

		$question_args = array(
			'paged'      => max( 1, get_query_var( 'paged' ) ),
			'tax_query'  => array(
				array(
					'taxonomy' => 'question_tag',
					'field'    => 'slug',
					'terms'    => array( $tag_id ),
				),
			),
		);

		$question_args = apply_filters( 'ap_tag_question_query_args', $question_args );

		$question_tag = get_term_by( 'slug', $tag_id, 'question_tag' ); // @codingStandardsIgnoreLine.

		if ( $question_tag ) {
			$questions = ap_get_questions( $question_args );
			include( ap_get_theme_location( 'addons/tag/tag.php' ) );
		} else {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			include ap_get_theme_location( 'not-found.php' );
		}

	}

	/**
	 * Tags page layout
	 */
	public static function tags_page() {

		global $question_tags, $ap_max_num_pages, $ap_per_page, $tags_rows_found;
		$paged 				= max( 1, get_query_var( 'paged' ) );
		$per_page     = (int) ap_opt( 'tags_per_page' );
		$per_page     = 0 === $per_page ? 1 : $per_page;
		$offset       = $per_page * ( $paged - 1);

		$tag_args = array(
			'ap_tags_query' => 'num_rows',
			'parent'        => 0,
			'number'        => $per_page,
			'offset'        => $offset,
			'hide_empty'    => false,
			'order'         => 'DESC',
		);

		$ap_sort = ap_isset_post_value( 'ap_sort', 'count' );

		if ( 'new' === $ap_sort ) {
			$tag_args['orderby'] = 'id';
			$tag_args['order']      = 'ASC';
		} elseif ( 'name' === $ap_sort ) {
			$tag_args['orderby']    = 'name';
			$tag_args['order']      = 'ASC';
		} else {
			$tag_args['orderby'] = 'count';
		}

		if ( isset( $_GET['ap_s'] ) ) {
			$tag_args['search'] = sanitize_text_field( $_GET['ap_s'] );
		}

		/**
		 * FILTER: ap_tags_shortcode_args
		 * Filter applied before getting categories.
		 *
		 * @var array
		 */
		$tag_args = apply_filters( 'ap_tags_shortcode_args', $tag_args );

		$question_tags 		= get_terms( 'question_tag' , $tag_args );
		$total_terms        = (int) wp_count_terms( 'question_tag', [ 'hide_empty' => false, 'parent' => 0 ] );
		$ap_max_num_pages   = ceil( $total_terms / $per_page );

		include ap_get_theme_location( 'addons/tag/tags.php' );
	}

	/**
	 * Register widget position.
	 */
	public static function widget_positions() {
		register_sidebar( array(
			'name'          => __( '(AnsPress) Tags', 'anspress-question-answer' ),
			'id'            => 'ap-tags',
			'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
			'after_widget'  => '</div>',
			'description'   => __( 'Widgets in this area will be shown in anspress tags page.', 'anspress-question-answer' ),
			'before_title'  => '<h3 class="ap-widget-title">',
			'after_title'   => '</h3>',
		) );
	}

	/**
	 * Register tag taxonomy for question cpt.
	 *
	 * @return void
	 * @since 2.0
	 */
	public static function register_question_tag() {
		ap_add_default_options([
			'max_tags'        => 5,
			'min_tags'        => 1,
			'tags_page_title' => __( 'Tags', 'anspress-question-answer' ),
			'tags_per_page'   => 20,
			'tags_page_slug'  => 'tags',
			'tag_page_slug'   => 'tag',
		]);

		$tag_labels = array(
			'name' 				        => __( 'Question Tags', 'anspress-question-answer' ),
			'singular_name' 	    => _x( 'Tag', 'anspress-question-answer' ),
			'all_items' 		      => __( 'All Tags', 'anspress-question-answer' ),
			'add_new_item' 		    => _x( 'Add New Tag', 'anspress-question-answer' ),
			'edit_item' 		      => __( 'Edit Tag', 'anspress-question-answer' ),
			'new_item' 			      => __( 'New Tag', 'anspress-question-answer' ),
			'view_item' 		      => __( 'View Tag', 'anspress-question-answer' ),
			'search_items' 		    => __( 'Search Tag', 'anspress-question-answer' ),
			'not_found' 		      => __( 'Nothing Found', 'anspress-question-answer' ),
			'not_found_in_trash'  => __( 'Nothing found in Trash', 'anspress-question-answer' ),
			'parent_item_colon'   => '',
		);

		/**
		 * FILTER: ap_question_tag_labels
		 * Filter ic called before registering question_tag taxonomy
		 */
		$tag_labels = apply_filters( 'ap_question_tag_labels',  $tag_labels );
		$tag_args = array(
			'hierarchical' => true,
			'labels'       => $tag_labels,
			'rewrite'      => false,
		);

		/**
		 * FILTER: ap_question_tag_args
		 * Filter ic called before registering question_tag taxonomy
		 */
		$tag_args = apply_filters( 'ap_question_tag_args',  $tag_args );

		/**
		 * Now let WordPress know about our taxonomy
		 */
		register_taxonomy( 'question_tag', array( 'question' ), $tag_args );
	}

	/**
	 * Add tags menu in wp-admin.
	 */
	public static function admin_tags_menu() {
		add_submenu_page( 'anspress', __( 'Question Tags', 'anspress-question-answer' ), __( 'Tags', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_tag' );
	}

	/**
	 * Register option fields.
	 */
	public static function option_fields() {
		ap_register_option_section( 'addons', 'tags', __( 'Tag', 'anspress-question-answer' ), array(
			array(
				'name'              => 'tags_per_page',
				'label'             => __( 'Tags to show', 'anspress-question-answer' ),
				'description'       => __( 'Numbers of tags to show in tags page.', 'anspress-question-answer' ),
				'type'              => 'number',
			),
			array(
				'name'              => 'max_tags',
				'label'             => __( 'Maximum tags', 'anspress-question-answer' ),
				'description'       => __( 'Maximum numbers of tags that user can add when asking.', 'anspress-question-answer' ),
				'type'              => 'number',
			),
			array(
				'name'              => 'min_tags',
				'label'             => __( 'Minimum tags', 'anspress-question-answer' ),
				'description'       => __( 'minimum numbers of tags that user must add when asking.', 'anspress-question-answer' ),
				'type'              => 'number',
			),
			array(
				'name' 		=> 'tags_page_title',
				'label' 	=> __( 'Tags page title', 'anspress-question-answer' ),
				'desc' 		=> __( 'Title for tags page', 'anspress-question-answer' ),
				'type' 		=> 'text',
				'show_desc_tip' => false,
			),
			array(
				'name' 		=> 'tags_page_slug',
				'label' 	=> __( 'Tags page slug', 'anspress-question-answer' ),
				'desc' 		=> __( 'Slug tags page', 'anspress-question-answer' ),
				'type' 		=> 'text',
				'show_desc_tip' => false,
			),

			array(
				'name' 		=> 'tag_page_slug',
				'label' 	=> __( 'Tag page slug', 'anspress-question-answer' ),
				'desc' 		=> __( 'Slug for tag page', 'anspress-question-answer' ),
				'type' 		=> 'text',
				'show_desc_tip' => false,
			),
		));
	}


	/**
	 * Append meta display.
	 *
	 * @param  array $metas Display metas.
	 * @param  array $question_id Post ID.
	 * @return array
	 * @since 2.0
	 */
	public static function ap_display_question_metas( $metas, $question_id ) {
		if ( ap_post_have_terms( $question_id, 'question_tag' ) && ! is_singular( 'question' ) ) {
			$metas['tags'] = ap_question_tags_html( array( 'label' => '<i class="apicon-tag"></i>', 'show' => 1 ) ); }

		return $metas;
	}

	/**
	 * Hook tags after post.
	 *
	 * @param   object $post Post object.
	 * @return  string
	 * @since   1.0
	 */
	public static function ap_question_info( $post ) {

		if ( ap_question_have_tags() ) {
			echo '<div class="widget"><span class="ap-widget-title">' . esc_attr__( 'Tags', 'anspress-question-answer' ) . '</span>';
			echo '<div class="ap-post-tags clearfix">' . ap_question_tags_html( [ 'list' => true, 'label' => '' ] ) . '</div></div>'; // WPCS: xss okay.
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param array $js Javacript array.
	 * @return array
	 */
	public static function ap_assets_js( $js ) {
		$js['tags'] = [ 'dep' => [ 'anspress-main' ], 'footer' => true ];

		if ( is_ask() ) {
			$js['tags']['active'] = true;
		}

		return $js;
	}

	/**
	 * Add translated strings to the javascript files
	 */
	public static function ap_localize_scripts() {
		$l10n_data = array(
			'deleteTag'            => __( 'Delete Tag', 'anspress-question-answer' ),
			'addTag'               => __( 'Add Tag', 'anspress-question-answer' ),
			'tagAdded'             => __( 'added to the tags list.', 'anspress-question-answer' ),
			'tagRemoved'           => __( 'removed from the tags list.', 'anspress-question-answer' ),
			'suggestionsAvailable' => __( 'Suggestions are available. Use the up and down arrow keys to read it.', 'anspress-question-answer' ),
		);

		wp_localize_script(
			'anspress-tags',
			'apTagsTranslation',
			$l10n_data
		);
	}

	/**
	 * Filter tag term link.
	 *
	 * @param  string $url      Default URL of taxonomy.
	 * @param  array  $term     Term array.
	 * @param  string $taxonomy Taxonomy type.
	 * @return string           New URL for term.
	 */
	public static function term_link_filter( $url, $term, $taxonomy ) {
		if ( 'question_tag' === $taxonomy ) {
			if ( get_option( 'permalink_structure' ) !== '' ) {
				return ap_get_link_to( array( 'ap_page' => ap_get_tag_slug(), 'q_tag' => $term->slug ) );
			} else {
				return ap_get_link_to( array( 'ap_page' => ap_get_tag_slug(), 'q_tag' => $term->term_id ) );
			}
		}
		return $url;
	}

	/**
	 * Add tag field in ask form.
	 *
	 * @param  array   $args Arguments.
	 * @param  boolean $editing Is editing form.
	 */
	public static function ask_from_tag_field( $args, $editing ) {
		global $editing_post;
		$tag_val = $editing ? get_the_terms( $editing_post->ID, 'question_tag' ) : ap_sanitize_unslash( 'tags', 'r', [] ) ;

		ob_start();
		?>
			<div class="ap-field-tags ap-form-fields">
				<label class="ap-form-label" for="tags"><?php esc_attr_e( 'Tags', 'anspress-question-answer' ); ?></label>
				<div data-role="ap-tagsinput" class="ap-tags-input">
					<div id="ap-tags-add">
						<input id="tags" class="ap-tags-field ap-form-control" placeholder="<?php esc_attr_e( 'Type and hit enter', 'anspress-question-answer' ); ?>" autocomplete="off" />
						<ul id="ap-tags-suggestion">
						</ul>
					</div>

					<ul id="ap-tags-holder" aria-describedby="ap-tags-list-title">
						<?php foreach ( (array) $tag_val as $tag ) { ?>
							<?php if ( ! empty( $tag->slug ) ) { ?>
								<li class="ap-tagssugg-item">
									<button role="button" class="ap-tag-remove"><span class="sr-only"></span> <span class="ap-tag-item-value"><?php echo esc_attr( $tag->slug ); ?></span><i class="apicon-x"></i></button>
									<input type="hidden" name="tags[]" value="<?php echo esc_attr( $tag->slug ); ?>" />
								</li>
							<?php } ?>
						<?php } ?>
					</ul>
				</div>
			</div>
		<?php

		$tag_field = ob_get_clean();
		$args['fields'][] = array(
			'name' 		=> 'tag',
			'label' 	=> __( 'Tags', 'anspress-question-answer' ),
			'type'  	=> 'custom',
			'taxonomy' 	=> 'question_tag',
			'desc' 		=> __( 'Slowly type for suggestions', 'anspress-question-answer' ),
			'order' 	=> 11,
			'html' 		=> $tag_field,
		);

		return $args;
	}

	/**
	 * Add tag in validation field.
	 *
	 * @param  array $args Form arguments.
	 * @return array
	 */
	public static function ap_ask_fields_validation( $args ) {
		$args['tags'] = array(
			'sanitize' => array( 'sanitize_tags' ),
			'validate' => array( 'comma_separted_count' => ap_opt( 'min_tags' ) ),
		);

		return $args;
	}

	/**
	 * Things to do after creating a question.
	 *
	 * @param  integer $post_id Post ID.
	 * @param  object  $post Post object.
	 * @since 1.0
	 */
	public static function after_new_question( $post_id, $post ) {
		global $validate;

		if ( empty( $validate ) ) {
			return;
		}

		$fields = $validate->get_sanitized_fields();
		if ( isset( $fields['tags'] ) ) {
			$tags = explode( ',', $fields['tags'] );
			wp_set_object_terms( $post_id, $tags, 'question_tag' );
		}
	}

	/**
	 * Tags page title.
	 *
	 * @param  string $title Title.
	 * @return string
	 */
	public static function page_title( $title ) {
		if ( is_question_tags() ) {
			$title = ap_opt( 'tags_page_title' );
		} elseif ( is_question_tag() ) {
			$tag_id = sanitize_title( get_query_var( 'q_tag' ) );
			$tag = get_term_by( 'slug', $tag_id, 'question_tag' ); // @codingStandardsIgnoreLine.
			$title = $tag->name;
		}

		return $title;
	}

	/**
	 * Hook into AnsPress breadcrums to show tags page.
	 *
	 * @param  array $navs Breadcrumbs navs.
	 * @return array
	 */
	public static function ap_breadcrumbs( $navs ) {

		if ( is_question_tag() ) {
			$tag_id = sanitize_title( get_query_var( 'q_tag' ) );
			$tag = get_term_by( 'slug', $tag_id, 'question_tag' ); // @codingStandardsIgnoreLine.
			$navs['page'] = array( 'title' => __( 'Tags', 'anspress-question-answer' ), 'link' => ap_get_link_to( 'tags' ), 'order' => 8 );

			if ( $tag ) {
				$navs['tag'] = array(
					'title' => $tag->name,
					'link'  => get_term_link( $tag, 'question_tag' ), // @codingStandardsIgnoreLine.
					'order' => 8,
				);
			}
		} elseif ( is_question_tags() ) {
			$navs['page'] = array( 'title' => __( 'Tags', 'anspress-question-answer' ), 'link' => ap_get_link_to( 'tags' ), 'order' => 8 );
		}

		return $navs;
	}

	/**
	 * Modify terms mysql quries.
	 *
	 * @param array $query Query parameters.
	 * @param array $taxonomies Available taxonomies.
	 * @param array $args Arguments.
	 * @return array
	 */
	public static function terms_clauses( $query, $taxonomies, $args ) {
		if ( isset( $args['ap_tags_query'] ) && 'num_rows' === $args['ap_tags_query'] ) {
			$query['fields'] = 'SQL_CALC_FOUND_ROWS ' . $query['fields'];
		}

		if ( in_array( 'question_tag', $taxonomies, true ) && isset( $args['ap_query'] ) && 'tags_subscription' === $args['ap_query'] ) {
			global $wpdb;

			$query['join']     = $query['join'].' INNER JOIN ' . $wpdb->prefix . 'ap_meta apmeta ON t.term_id = apmeta.apmeta_actionid';
			$query['where']    = $query['where'] . " AND apmeta.apmeta_type='subscriber' AND apmeta.apmeta_param='tag' AND apmeta.apmeta_userid='" . $args['user_id'] . "'";
		}

		return $query;
	}

	public static function get_terms( $terms, $taxonomies, $args ) {
		if ( isset( $args['ap_tags_query'] ) && $args['ap_tags_query'] == 'num_rows' ) {
			global $tags_rows_found,  $wpdb;

			$tags_rows_found = $wpdb->get_var( apply_filters( 'ap_get_terms_found_rows', 'SELECT FOUND_ROWS()', $terms, $taxonomies, $args ) );
			// wp_cache_set( SELF::cache_key.'_count', SELF::total_count, 'anspress-question-answer' );
		}
		return $terms;
	}

	/**
	 * Handle tags suggestion on question form
	 */
	public static function ap_tags_suggestion() {
		$keyword = ap_sanitize_unslash( 'q', 'r' );

		$tags = get_terms( 'question_tag', array(
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => false,
			'search'     => $keyword,
			'number'     => 8,
		));

		if ( $tags ) {
			$items = array();
			foreach ( $tags as $k => $t ) {
				$items [ $k ] = $t->slug;
			}

			$result = array( 'status' => true, 'items' => $items );
			wp_send_json( $result );
		}

		wp_send_json( array( 'status' => false ) );
	}

	/**
	 * Add category pages rewrite rule.
	 *
	 * @param  array $rules AnsPress rules.
	 * @return array
	 */
	public static function rewrite_rules( $rules, $slug, $base_page_id ) {
		$tags_rules = array();
		$base = 'index.php?page_id=' . $base_page_id . '&ap_page=';
		$tags_rules[ $slug . ap_get_tag_slug() . '/([^/]+)/page/?([0-9]{1,})/?' ] = $base . 'tag&q_tag=$matches[#]&paged=$matches[#]';
		$tags_rules[ $slug . ap_get_tags_slug() . '/([^/]+)/page/?([0-9]{1,})/?' ] = $base . 'tags&q_tag=$matches[#]&paged=$matches[#]';

		$tags_rules[ $slug . ap_get_tag_slug() . '/([^/]+)/?' ] = $base . 'tag&q_tag=$matches[#]';
		$tags_rules[ $slug . ap_get_tags_slug() . '/?' ] = $base . 'tags';

		return $tags_rules + $rules;
	}

	/**
	 * Override ap_current_page_is function to check if tags or tag page.
	 *
	 * @param  string $page Current page slug.
	 * @return string
	 */
	public static function ap_current_page_is( $page ) {
		if ( is_question_tags() ) {
			$page = 'tags';
		} elseif ( is_question_tag() ) {
			$page = 'tag';
		}

		return $page;
	}
	/**
	 * Filter main questions query args. Modify and add label args.
	 *
	 * @param  array $args Questions args.
	 * @return array
	 */
	public static function ap_main_questions_args( $args ) {
		global $questions, $wp;
		$query = $wp->query_vars;

		$current_filter = ap_get_current_list_filters( 'tag' );
		$tags_operator = ! empty( $wp->query_vars['ap_tags_operator'] ) ? $wp->query_vars['ap_tags_operator'] : 'IN';

		if ( isset( $query['ap_tags'] ) && is_array( $query['ap_tags'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'question_tag',
				'field'    => 'slug',
				'terms'    => $query['ap_tags'],
				'operator' => $tags_operator,
			);
		} elseif ( ! empty( $current_filter ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'question_tag',
				'field'    => 'term_id',
				'terms'    => $current_filter,
			);
		}

		return $args;
	}

	/**
	 * Add tags sorting in list filters
	 *
	 * @return array
	 */
	public static function ap_list_filters( $filters ) {
		global $wp;

		if ( ! isset( $wp->query_vars['ap_tags'] ) ) {
			$filters['tag'] = array(
				'title' => __( 'Tag', 'anspress-question-answer' ),
				'search' => true,
				'multiple' => true,
			);
		}

		return $filters;
	}

	/**
	 * Ajax callback for loading order by filter.
	 *
	 * @since 4.0.0
	 */
	public static function load_filter_tag() {
		$filter = ap_sanitize_unslash( 'filter', 'r' );
		check_ajax_referer( 'filter_' . $filter, '__nonce' );
		$search = ap_sanitize_unslash( 'search', 'r', false );

		ap_ajax_json( array(
			'success'  => true,
			'items'    => ap_get_tag_filter( $search ),
			'multiple' => true,
			'nonce'    => wp_create_nonce( 'filter_' . $filter ),
		));
	}

	/**
	 * Output active tag in filter
	 *
	 * @since 4.0.0
	 */
	public static function filter_active_tag( $active, $filter ) {
		$current_filters = ap_get_current_list_filters( 'tag' );

		if ( ! empty( $current_filters ) ) {
			$args = array(
				'hierarchical'  => true,
				'hide_if_empty' => true,
				'number'        => 2,
				'include'       => $current_filters,
			);

			$terms = get_terms( 'question_tag', $args );

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
}


// Init addons.
AnsPress_Tag::init();

