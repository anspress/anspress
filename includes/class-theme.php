<?php
/**
 * Class for anspress theme
 *
 * @package      AnsPress
 * @subpackage   Theme Hooks
 * @author       Rahul Aryan <rah12@live.com>
 * @license      GPL-3.0+
 * @link         https://anspress.net
 * @copyright    2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Holds all hooks related to frontend layout/theme
 */
class AnsPress_Theme {
	/**
	 * Function get called on init
	 */
	public static function init_actions() {
		// Register anspress shortcode.
		// add_shortcode( 'anspress', array( AnsPress_BasePage_Shortcode::get_instance(), 'anspress_sc' ) );

		// Register question shortcode.
		// add_shortcode( 'question', array( AnsPress_Question_Shortcode::get_instance(), 'anspress_question_sc' ) );
	}

	/**
	 * Add answer-seleted class in post_class.
	 *
	 * @param  array $classes Post class attribute.
	 * @return array
	 * @since 2.0.1
	 * @since 4.1.8 Fixes #426: Undefined property `post_type`.
	 */
	public static function question_answer_post_class( $classes ) {
		global $post;

		if ( ! $post ) {
			return $classes;
		}

		if ( 'question' === $post->post_type ) {
			if ( ap_have_answer_selected( $post->ID ) ) {
				$classes[] = 'answer-selected';
			}

			if ( ap_is_featured_question( $post->ID ) ) {
				$classes[] = 'featured-question';
			}

			$classes[] = 'answer-count-' . ap_get_answers_count();
		} elseif ( 'answer' === $post->post_type ) {
			if ( ap_is_selected( $post->ID ) ) {
				$classes[] = 'best-answer';
			}

			if ( ! ap_user_can_read_answer( $post ) ) {
				$classes[] = 'no-permission';
			}
		}

		return $classes;
	}

	/**
	 * Add anspress classes to body.
	 *
	 * @param  array $classes Body class attribute.
	 * @return array
	 * @since 2.0.1
	 */
	public static function body_class( $classes ) {
		// Add anspress class to body.
		if ( is_anspress() ) {
			$classes[] = 'anspress-content';
			$classes[] = 'ap-page-' . ap_current_page();
		}

		return $classes;
	}

	/**
	 * Filter wp_title.
	 *
	 * @param string $title WP page title.
	 * @return string
	 * @since 4.1.1 Do not override title of all pages except single question.
	 */
	public static function ap_title( $title ) {
		if ( is_anspress() ) {
			remove_filter( 'wp_title', array( __CLASS__, 'ap_title' ) );

			if ( is_question() ) {
				return ap_question_title_with_solved_prefix() . ' | ';
			}
		}

		return $title;
	}

	/**
	 * Filter document_title_parts.
	 *
	 * @param  array $title The document title parts.
	 * @return array
	 * @since 4.4.0
	 */
	public static function ap_title_parts( $title ) {
		if ( is_anspress() ) {
			remove_filter( 'document_title_parts', array( __CLASS__, 'ap_title_parts' ) );

			if ( is_question() ) {
				$title['title'] = ap_question_title_with_solved_prefix();
			}
		}

		return $title;
	}

	/**
	 * Add feed and links in HEAD of the document
	 *
	 * @since 4.1.0 Removed question sortlink override.
	 */
	public static function wp_head() {
		if ( ap_current_page( 'base' ) ) {
			$q_feed = get_post_type_archive_feed_link( 'question' );
			$a_feed = get_post_type_archive_feed_link( 'answer' );
			echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr__( 'Question Feed', 'anspress-question-answer' ) . '" href="' . esc_url( $q_feed ) . '" />';
			echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr__( 'Answers Feed', 'anspress-question-answer' ) . '" href="' . esc_url( $a_feed ) . '" />';
		}
	}

	/**
	 * Shows lists of attachments of a question
	 */
	public static function question_attachments() {
		if ( ap_have_attach() ) {
			include ap_get_theme_location( 'attachments.php' );
		}
	}

	/**
	 * Check if anspress.php file exists in theme. If exists
	 * then load this template for AnsPress.
	 *
	 * @param  string $template Template.
	 * @return string
	 * @since  3.0.0
	 * @since  4.1.0 Give priority to page templates and then anspress.php and lastly fallback to page.php.
	 * @since  4.1.1 Load single question template if exists.
	 */
	public static function anspress_basepage_template( $template ) {
		if ( is_anspress() ) {
			$templates = array( 'anspress.php', 'page.php', 'singular.php', 'index.php' );

			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				$templates = array();
			}

			if ( is_page() ) {
				$_post = get_queried_object();

				array_unshift( $templates, 'page-' . $_post->ID . '.php' );
				array_unshift( $templates, 'page-' . $_post->post_name . '.php' );

				$page_template = get_post_meta( $_post->ID, '_wp_page_template', true );

				if ( ! empty( $page_template ) && 'default' !== $page_template ) {
					array_unshift( $templates, $page_template );
				}
			} elseif ( is_single() ) {
				$_post = get_queried_object();

				array_unshift( $templates, 'single-' . $_post->ID . '.php' );
				array_unshift( $templates, 'single-' . $_post->post_name . '.php' );
				array_unshift( $templates, 'single-' . $_post->post_type . '.php' );
			} elseif ( is_tax() ) {
				$_term     = get_queried_object();
				$term_type = str_replace( 'question_', '', $_term->taxonomy );
				array_unshift( $templates, 'anspress-' . $term_type . '.php' );
			}

			$new_template = locate_template( $templates );

			if ( '' !== $new_template ) {
				return $new_template;
			}
		}

		return $template;
	}

	/**
	 * Generate question excerpt if there is not any already.
	 *
	 * @param string      $excerpt Default excerpt.
	 * @param object|null $post    WP_Post object.
	 * @return string
	 * @since 4.1.0
	 */
	public static function get_the_excerpt( $excerpt, $post = null ) {
		$post = get_post( $post );

		if ( 'question' === $post->post_type ) {
			if ( get_query_var( 'answer_id' ) ) {
				$post = ap_get_post( get_query_var( 'answer_id' ) );
			}

			// Check if excerpt exists.
			if ( ! empty( $post->post_excerpt ) ) {
				return $post->post_excerpt;
			}

			$excerpt_length = apply_filters( 'excerpt_length', 55 );
			$excerpt_more   = apply_filters( 'excerpt_more', ' [&hellip;]' );
			return wp_trim_words( $post->post_content, $excerpt_length, $excerpt_more );
		}

		return $excerpt;
	}

	/**
	 * Remove hentry class from question, answers and main pages .
	 *
	 * @param array   $post_classes Post classes.
	 * @param array   $class_name        An array of additional classes added to the post.
	 * @param integer $post_id      Post ID.
	 * @return array
	 * @since 4.1.0
	 */
	public static function remove_hentry_class( $post_classes, $class_name, $post_id ) {
		$_post = ap_get_post( $post_id );

		if ( $_post && ( in_array( $_post->post_type, array( 'answer', 'question' ), true ) || in_array( $_post->ID, ap_main_pages_id() ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			return array_diff( $post_classes, array( 'hentry' ) );
		}

		return $post_classes;
	}
}
