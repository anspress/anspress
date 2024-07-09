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

	/**
	 * Get the current page URL.
	 *
	 * @return string
	 */
	public static function currentPageUrl(): string {
		global $wp;
		return home_url( add_query_arg( array(), $wp->request ) );
	}

	/**
	 * Get the current page URL with query arguments.
	 *
	 * @return array
	 */
	public static function questionFilterOptions(): array {
		$options = array(
			array(
				'key'     => 'all',
				'value'   => __( 'All', 'anspress-question-answer' ),
				'default' => true,
			),
			array(
				'key'   => 'open',
				'value' => __( 'Open', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'closed',
				'value' => __( 'Closed', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'solved',
				'value' => __( 'Solved', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'unsolved',
				'value' => __( 'Unsolved', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'featured',
				'value' => __( 'Featured', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'unanswered',
				'value' => __( 'Unanswered', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'moderate',
				'value' => __( 'Moderate', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'private_post',
				'value' => __( 'Private', 'anspress-question-answer' ),
			),
		);

		$options = apply_filters( 'anspress/questions/filter_options', $options );

		return $options;
	}

	/**
	 * Get the current page URL with query arguments.
	 *
	 * @return array
	 */
	public static function questionOrderByOptions(): array {
		$options = array(
			array(
				'key'     => 'active',
				'value'   => __( 'Last active', 'anspress-question-answer' ),
				'default' => true,
			),
			array(
				'key'   => 'date',
				'value' => __( 'Date', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'votes',
				'value' => __( 'Votes count', 'anspress-question-answer' ),
			),

			array(
				'key'   => 'answers',
				'value' => __( 'Answers count', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'views',
				'value' => __( 'Views count', 'anspress-question-answer' ),
			),
		);

		$options = apply_filters( 'anspress/questions/orderby_options', $options );

		return $options;
	}

	/**
	 * Get the current page URL with query arguments.
	 *
	 * @return array
	 */
	public static function questionOrderOptions(): array {
		$options = array(
			array(
				'key'     => 'desc',
				'value'   => __( 'Descending', 'anspress-question-answer' ),
				'default' => true,
			),
			array(
				'key'   => 'asc',
				'value' => __( 'Ascending', 'anspress-question-answer' ),
			),
		);

		$options = apply_filters( 'anspress/questions/order_options', $options );

		return $options;
	}

	/**
	 * Sanitize and set default options.
	 *
	 * @param array  $args The arguments.
	 * @param string $optionKey The option key.
	 * @param array  $options The options.
	 * @return array
	 */
	private static function argsSanitizeAndSetDefaultOptions( $args, $optionKey, $options ) {
		if ( ! empty( $args[ $optionKey ] ) && is_array( $args[ $optionKey ] ) ) {
			$validKeys          = array_column( $options, 'key' );
			$args[ $optionKey ] = array_intersect( $args[ $optionKey ], $validKeys );
		} else {
			$defaultOption      = array_filter(
				$options,
				function ( $option ) {
					return ! empty( $option['default'] );
				}
			);
			$args[ $optionKey ] = ! empty( $defaultOption ) && ! empty( $defaultOption['key'] ) ? array( $defaultOption['key'] ) : array();
		}
		return $args;
	}

	/**
	 * Get the current questions query arguments.
	 *
	 * @return array
	 */
	public static function currentQuestionsQueryArgs() {
		$currentArgs = isset( $_GET ) ? sanitize_post( wp_unslash( $_GET ) ) : array(); // @codingStandardsIgnoreLine

		$args = wp_array_slice_assoc(
			$currentArgs,
			array(
				'keywords',
				'args:filter',
				'args:orderby',
				'args:order',
				'args:categories',
				'args:tags',
			)
		);

		$orderByOptions = self::questionOrderByOptions();
		$args           = self::argsSanitizeAndSetDefaultOptions( $args, 'args:orderby', $orderByOptions );

		$orderOptions = self::questionOrderOptions();
		$args         = self::argsSanitizeAndSetDefaultOptions( $args, 'args:order', $orderOptions );

		$filterOptions = self::questionFilterOptions();
		$args          = self::argsSanitizeAndSetDefaultOptions( $args, 'args:filter', $filterOptions );

		// Sanitize keywords.
		if ( ! empty( $args['keywords'] ) ) {
			$args['keywords'] = sanitize_text_field( $args['keywords'] );
		}

		/**
		 * Filter the current questions query arguments.
		 *
		 * @param array $args The current questions query arguments.
		 * @return array The filtered current questions query arguments.
		 */
		$args = apply_filters( 'anspress/questions/query_args', $args );

		return $args;
	}

	/**
	 * Get the current questions query selected terms.
	 *
	 * @param string $taxonomy The taxonomy.
	 * @param array  $currentArgs The current arguments.
	 * @return array
	 */
	public static function currentQuestionQuerySelectedTerms( string $taxonomy, ?array $currentArgs = array() ) {
		if ( empty( $currentArgs ) ) {
			return array();
		}

		$selectedTerms = array();

		$selectedTerms = array_map( 'intval', $currentArgs );

		// Filter empty terms.
		$selectedTerms = array_filter( $selectedTerms );

		if ( empty( $selectedTerms ) ) {
			return array();
		}

		// Get term objects.
		$selectedTerms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'include'    => $selectedTerms,
				'hide_empty' => false,
			)
		);

		return $selectedTerms;
	}

	/**
	 * Display question terms.
	 *
	 * @param array  $terms Terms to display.
	 * @param string $label Label to display.
	 *
	 * @return void
	 */
	public static function displayQuestionTerms( array $terms, string $label ) {
		$tagsCount = count( $terms );

		if ( $terms && $tagsCount > 0 ) {
			echo '<span class="wp-block-anspress-questions-item-tags">';
			echo esc_html( $label );
			$i = 1;
			foreach ( $terms as $t ) {
				if ( $i > 2 ) {
					break;
				}
				echo '<a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a>';
				++$i;
			}
			if ( $tagsCount > 2 ) {
				echo '<a href="' . esc_url( get_the_permalink() ) . '">';
				// translators: %s is number of tags.
				printf( esc_attr__( '%s+', 'anspress-question-answer' ), esc_attr( number_format_i18n( $tagsCount - 2 ) ) );
				echo '</a>';
			}
			echo '</span>';
		}
	}
}
