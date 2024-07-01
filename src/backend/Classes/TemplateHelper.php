<?php
/**
 * Template helpers class.
 *
 * @package WordPress
 * @since 5.0.0
 */

namespace AnsPress\Classes;

use WP_REST_Request;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Template helpers class.
 */
class TemplateHelper {
	/**
	 * Get the current template type.
	 *
	 * @return string
	 */
	public static function currentTemplateType() {
		if ( is_single() ) {
			return 'single';
		} elseif ( is_page() ) {
			return 'page';
		} elseif ( is_home() ) {
			return 'home';
		} elseif ( is_front_page() ) {
			return 'front_page';
		} elseif ( is_category() ) {
			return 'category';
		} elseif ( is_tag() ) {
			return 'tag';
		} elseif ( is_author() ) {
			return 'author';
		} elseif ( is_post_type_archive() ) {
			return 'post_type_archive';
		} elseif ( is_tax() ) {
			return 'taxonomy';
		} elseif ( is_archive() ) {
			return 'archive';
		} elseif ( is_search() ) {
			return 'search';
		} elseif ( is_404() ) {
			return '404';
		} else {
			return 'index';
		}
	}

	/**
	 * Get the template hierarchy for a given type.
	 *
	 * @param string $type The type of template to get the hierarchy for.
	 * @return array
	 */
	public static function templateHierarchy( $type ) {
		global $wp_query;
		$templates = array();

		switch ( $type ) {
			case 'single':
				$post        = $wp_query->get_queried_object();
				$post_type   = get_post_type( $post );
				$templates[] = "single-{$post_type}-{$post->post_name}.php";
				$templates[] = "single-{$post_type}.php";
				$templates[] = 'single.php';
				break;

			case 'page':
				$post        = $wp_query->get_queried_object();
				$templates[] = get_page_template_slug( $post );
				if ( $post->post_name ) {
					$templates[] = "page-{$post->post_name}.php";
				}
				if ( $post->ID ) {
					$templates[] = "page-{$post->ID}.php";
				}
				$templates[] = 'page.php';
				break;

			case 'home':
				$templates[] = 'home.php';
				$templates[] = 'index.php';
				break;

			case 'front_page':
				$templates[] = 'front-page.php';
				$templates[] = 'home.php';
				$templates[] = 'index.php';
				break;

			case 'category':
				$category    = $wp_query->get_queried_object();
				$templates[] = "category-{$category->slug}.php";
				$templates[] = "category-{$category->term_id}.php";
				$templates[] = 'category.php';
				break;

			case 'tag':
				$tag         = $wp_query->get_queried_object();
				$templates[] = "tag-{$tag->slug}.php";
				$templates[] = "tag-{$tag->term_id}.php";
				$templates[] = 'tag.php';
				break;

			case 'author':
				$author      = $wp_query->get_queried_object();
				$templates[] = "author-{$author->user_nicename}.php";
				$templates[] = "author-{$author->ID}.php";
				$templates[] = 'author.php';
				break;

			case 'post_type_archive':
				$post_type   = $wp_query->get_queried_object();
				$templates[] = "archive-{$post_type->name}.php";
				$templates[] = 'archive.php';
				break;

			case 'taxonomy':
				$term        = $wp_query->get_queried_object();
				$taxonomy    = get_taxonomy( $term->taxonomy );
				$templates[] = "taxonomy-{$taxonomy->name}-{$term->slug}.php";
				$templates[] = "taxonomy-{$taxonomy->name}.php";
				$templates[] = 'taxonomy.php';
				break;

			case 'archive':
				$templates[] = 'archive.php';
				break;

			case 'search':
				$templates[] = 'search.php';
				break;

			case '404':
				$templates[] = '404.php';
				break;

			case 'index':
			default:
				$templates[] = 'index.php';
				break;
		}

		return $templates;
	}


	/**
	 * Get the current template ID.
	 *
	 * @return string
	 */
	public static function currentTemplateId(): string {
		global $_wp_current_template_id;

		return (string) $_wp_current_template_id;
	}

	/**
	 * Get attributes from the WordPress template parsed block.
	 *
	 * @param string $blockName The block name.
	 * @param string $templateId The template ID.
	 * @return array|null The attributes of the block, or null if not found.
	 */
	public static function blockTemplateAttributes( string $blockName, string $templateId ) {
		$currentType = self::currentTemplateType();
		$templates   = self::templateHierarchy( $currentType );

		$slugParts = explode( '//', $templateId );

		if ( empty( $slugParts ) || count( $slugParts ) < 2 || empty( $slugParts[1] ) ) {
			return array();
		}

		$templates = get_block_templates( array( 'slug__in' => array( $slugParts[1] ) ) );

		if ( empty( $templates ) ) {
			return array();
		}

		$blocks = parse_blocks( $templates[0]->content );

		$block = wp_get_first_block( $blocks, $blockName );

		return $block ? $block['attrs'] : array();
	}

	/**
	 * Load a REST block part.
	 *
	 * @param WP_REST_Request $req The REST request.
	 * @param string          $filePath The file path.
	 * @param array           $args The arguments.
	 * @return string The loaded block part.
	 */
	public static function loadRestBlockPart( WP_REST_Request $req, string $filePath, array $args = array() ) {
		$blockName  = $req->get_header( 'X-Anspress-Block-Name' );
		$templateId = $req->get_header( 'X-Anspress-Template-Id' );

		$attributes = self::blockTemplateAttributes( $blockName, $templateId );

		return Plugin::loadView( $filePath, array_merge( $args, array( 'attributes' => $attributes ) ), false );
	}
}
