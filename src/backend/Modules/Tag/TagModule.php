<?php
/**
 * The Category module.
 *
 * @package AnsPress
 * @since 5.0.0
 */

namespace AnsPress\Modules\Tag;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\Plugin;

/**
 * Category module class.
 *
 * @since 5.0.0
 */
class TagModule extends AbstractModule {
	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'registerQuestionTag' ), 1 );
		add_action( 'init', array( $this, 'registerBlocks' ) );

		add_action( 'ap_admin_menu', array( $this, 'admin_tags_menu' ) );
		add_filter( 'term_link', array( $this, 'term_link_filter' ), 10, 3 );
		add_filter( 'ap_page_title', array( $this, 'page_title' ) );
		add_filter( 'ap_breadcrumbs', array( $this, 'ap_breadcrumbs' ) );
		add_action( 'ap_rewrites', array( $this, 'rewrite_rules' ), 10, 3 );
		add_filter( 'ap_current_page', array( $this, 'ap_current_page' ) );
	}

	/**
	 * Register blocks.
	 *
	 * @return void
	 */
	public function registerBlocks() {
		register_block_type( Plugin::getPathTo( 'build/frontend/tags' ) );
	}

	/**
	 * Register tag taxonomy for question cpt.
	 *
	 * @return void
	 * @since 2.0
	 */
	public function registerQuestionTag() {
		$tag_labels = array(
			'name'               => __( 'Question Tags', 'anspress-question-answer' ),
			'singular_name'      => __( 'Question Tag', 'anspress-question-answer' ),
			'all_items'          => __( 'All Tags', 'anspress-question-answer' ),
			'add_new_item'       => __( 'Add New Tag', 'anspress-question-answer' ),
			'edit_item'          => __( 'Edit Tag', 'anspress-question-answer' ),
			'new_item'           => __( 'New Tag', 'anspress-question-answer' ),
			'view_item'          => __( 'View Tag', 'anspress-question-answer' ),
			'search_items'       => __( 'Search Tag', 'anspress-question-answer' ),
			'not_found'          => __( 'Nothing Found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'Nothing found in Trash', 'anspress-question-answer' ),
			'parent_item_colon'  => '',
		);

		/**
		 * FILTER: ap_question_tag_labels
		 * Filter ic called before registering question_tag taxonomy
		 */
		$tag_labels = apply_filters( 'ap_question_tag_labels', $tag_labels );
		$tag_args   = array(
			'hierarchical' => true,
			'labels'       => $tag_labels,
			'rewrite'      => false,
			'show_in_rest' => true,
		);

		/**
		 * FILTER: ap_question_tag_args
		 * Filter ic called before registering question_tag taxonomy
		 */
		$tag_args = apply_filters( 'ap_question_tag_args', $tag_args );

		/**
		 * Now let WordPress know about our taxonomy
		 */
		register_taxonomy( 'question_tag', array( 'question' ), $tag_args );
	}

	/**
	 * Add tags menu in wp-admin.
	 */
	public function admin_tags_menu() {
		add_submenu_page( 'anspress', __( 'Question Tags', 'anspress-question-answer' ), __( 'Question Tags', 'anspress-question-answer' ), 'manage_options', 'edit-tags.php?taxonomy=question_tag' );
	}

	/**
	 * Filter tag term link.
	 *
	 * @param  string $url      Default URL of taxonomy.
	 * @param  object $term     Term array.
	 * @param  string $taxonomy Taxonomy type.
	 * @return string           New URL for term.
	 */
	public function term_link_filter( $url, $term, $taxonomy ) {
		if ( 'question_tag' === $taxonomy ) {
			if ( get_option( 'permalink_structure' ) !== '' ) {
				$opt          = get_option( 'ap_tags_path', 'tags' );
				$default_lang = '';

				// Support polylang permalink.
				if ( function_exists( 'pll_default_language' ) ) {
					$default_lang = pll_get_term_language( $term->term_id ) ? pll_get_term_language( $term->term_id ) : pll_default_language();
				}

				return user_trailingslashit( home_url( $default_lang . '/' . $opt ) . '/' . $term->slug );
			} else {
				return add_query_arg(
					array(
						'ap_page'      => 'tag',
						'question_tag' => $term->slug,
					),
					home_url()
				);
			}
		}
		return $url;
	}

	/**
	 * Tags page title.
	 *
	 * @param  string $title Title.
	 * @return string
	 */
	public function page_title( $title ) {
		if ( is_question_tags() ) {
			$title = ap_opt( 'tags_page_title' );
		} elseif ( is_question_tag() ) {
			$tag_id = sanitize_title( get_query_var( 'q_tag' ) );
			$tag    = $tag_id ? get_term_by( 'slug', $tag_id, 'question_tag' ) : get_queried_object();

			if ( $tag ) {
				$title = $tag->name;
			}
		}

		return $title;
	}

	/**
	 * Hook into AnsPress breadcrums to show tags page.
	 *
	 * @param  array $navs Breadcrumbs navs.
	 * @return array
	 */
	public function ap_breadcrumbs( $navs ) {
		if ( is_question_tag() ) {
			$tag_id       = sanitize_title( get_query_var( 'q_tag' ) );
			$tag          = $tag_id ? get_term_by( 'slug', $tag_id, 'question_tag' ) : get_queried_object();
			$navs['page'] = array(
				'title' => __( 'Tags', 'anspress-question-answer' ),
				'link'  => ap_get_link_to( 'tags' ),
				'order' => 8,
			);

			if ( $tag ) {
				$navs['tag'] = array(
					'title' => $tag->name,
					'link'  => get_term_link( $tag, 'question_tag' ), // @codingStandardsIgnoreLine.
					'order' => 8,
				);
			}
		} elseif ( is_question_tags() ) {
			$navs['page'] = array(
				'title' => __( 'Tags', 'anspress-question-answer' ),
				'link'  => ap_get_link_to( 'tags' ),
				'order' => 8,
			);
		}

		return $navs;
	}

	/**
	 * Add category pages rewrite rule.
	 *
	 * @param array  $rules AnsPress rules.
	 * @param string $slug Slug.
	 * @param int    $base_page_id AnsPress base page id.
	 * @return array
	 */
	public function rewrite_rules( $rules, $slug, $base_page_id ) {
		$base_slug = get_page_uri( ap_opt( 'tags_page' ) );
		update_option( 'ap_tags_path', $base_slug, true );
		$lang_rule    = str_replace( ap_base_page_slug() . '/', '', $slug );
		$lang_rewrite = str_replace( ap_opt( 'base_page' ), '', $base_page_id );

		$cat_rules = array(
			$lang_rule . $base_slug . '/([^/]+)/page/?([0-9]{1,})/?$' => $lang_rewrite . 'index.php?question_tag=$matches[#]&ap_paged=$matches[#]&ap_page=tag',
			$lang_rule . $base_slug . '/([^/]+)/?$' => $lang_rewrite . 'index.php?question_tag=$matches[#]&ap_page=tag',
		);

		return $cat_rules + $rules;
	}

	/**
	 * Output active tags_order in filter
	 *
	 * @param string $active Active filter.
	 * @param array  $filter Filter.
	 * @since 4.1.0
	 */
	public function filter_active_tags_order( $active, $filter ) {
		$tags_order = ap_get_current_list_filters( 'tags_order' );
		$tags_order = ! empty( $tags_order ) ? $tags_order : 'popular';

		$orders = array(
			'popular' => __( 'Popular', 'anspress-question-answer' ),
			'new'     => __( 'New', 'anspress-question-answer' ),
			'name'    => __( 'Name', 'anspress-question-answer' ),
		);

		$active = isset( $orders[ $tags_order ] ) ? $orders[ $tags_order ] : '';

		return ': <span class="ap-filter-active">' . $active . '</span>';
	}

	/**
	 * Modify current page to show tag archive.
	 *
	 * @param string $query_var Current page.
	 * @return string
	 * @since 4.1.0
	 */
	public function ap_current_page( $query_var ) {
		if ( 'tags' === $query_var && 'tag' === get_query_var( 'ap_page' ) ) {
			return 'tag';
		}

		return $query_var;
	}
}
