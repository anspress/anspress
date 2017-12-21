<?php
/**
 * Class for anspress theme
 *
 * @package      AnsPress
 * @subpackage   Theme Hooks
 * @author       Rahul Aryan <support@anspress.io>
 * @license      GPL-3.0+
 * @link         https://anspress.io
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
		add_shortcode( 'anspress', array( AnsPress_BasePage_Shortcode::get_instance(), 'anspress_sc' ) );

		// Register question shortcode.
		add_shortcode( 'question', array( AnsPress_Question_Shortcode::get_instance(), 'anspress_question_sc' ) );
	}

	/**
	 * AnsPress theme function as like WordPress theme function.
	 *
	 * @return void
	 */
	public static function includes_theme() {
		require_once ap_get_theme_location( 'functions.php' );
	}

	/**
	 * Add answer-seleted class in post_class.
	 *
	 * @param  array $classes Post class attribute.
	 * @return array
	 * @since 2.0.1
	 */
	public static function question_answer_post_class( $classes ) {
		global $post;

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
	 * Add anspress classess to body.
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
			remove_filter( 'wp_title', [ __CLASS__, 'ap_title' ] );

			if ( is_question() ) {
				return ap_question_title_with_solved_prefix() . ' | ';
			}
		}

		return $title;
	}

	/**
	 * Filter wpseo plugin title.
	 *
	 * @param  string $title Page title.
	 * @return string
	 * @deprecated 4.1.0
	 */
	public static function wpseo_title( $title ) {
		if ( is_anspress() ) {
			remove_filter( 'wpseo_title', array(
				__CLASS__,
				'wpseo_title',
			));

			$new_title = ap_page_title();

			if ( strpos( $title, 'ANSPRESS_TITLE' ) !== false ) {
				$new_title = str_replace( 'ANSPRESS_TITLE', $new_title, $title ) . ' | ' . get_bloginfo( 'name' );
			} else {
				$new_title = $new_title . ' | ' . get_bloginfo( 'name' );
			}

			$new_title = apply_filters( 'ap_wpseo_title', $new_title );

			return $new_title;
		}

		return $title;
	}

	/**
	 * Filter the_title().
	 *
	 * @param  string $title Current page/post title.
	 * @param  string $id    Post ID.
	 * @return string
	 * @deprecated 4.1.1
	 */
	public static function the_title( $title, $id = null ) {
		_deprecated_function( __FUNCTION__, '4.1.1' );

		if ( ap_opt( 'base_page' ) == $id  ) { // WPCS: loose comparison ok.
			remove_filter( 'the_title', [ __CLASS__, 'the_title' ] );
			return ap_page_title();
		}

		return $title;
	}

	/**
	 * Add feed link in wp_head
	 *
	 * @deprecated 4.1.0
	 */
	public static function feed_link() {
		if ( is_anspress() ) {
			echo '<link href="' . esc_url( home_url( '/feed/question-feed' ) ) . '" title="' . esc_attr__( 'Question Feed', 'anspress-question-answer' ) . '" type="application/rss+xml" rel="alternate">';
		}
	}

	/**
	 * Add default before body sidebar in AnsPress contents
	 */
	public static function ap_before_html_body() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$data = wp_json_encode( array(
				'user_login'   => $current_user->data->user_login,
				'display_name' => $current_user->data->display_name,
				'user_email'   => $current_user->data->user_email,
				'avatar'       => get_avatar( $current_user->ID ),
			));
			?>
				<script type="text/javascript">
					apCurrentUser = <?php echo $data; // xss okay. ?>;
				</script>
			<?php
		}
		dynamic_sidebar( 'ap-before' );
	}

	/**
	 * Remove some unwanted things from wp_head
	 *
	 * @deprecated 4.1.0
	 */
	public static function remove_head_items() {
		if ( is_anspress() ) {
			global $wp_query;

			// Check if quesied object is set, if not then set base page object.
			if ( ! isset( $wp_query->queried_object ) ) {
				$wp_query->queried_object = ap_get_post( ap_opt( 'base_page' ) );
			}

			$wp_query->queried_object->post_title = ap_page_title();
			remove_action( 'wp_head', 'rsd_link' );
			remove_action( 'wp_head', 'wlwmanifest_link' );
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
			remove_action( 'wp_head', 'rel_canonical' );
			remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'feed_links', 2 );
		}
	}

	/**
	 * Add feed and links in HEAD of the document
	 *
	 * @since 4.1.0 Removed question sortlink override.
	 */
	public static function wp_head() {
		if ( 'base' === ap_current_page() ) {
			$q_feed = get_post_type_archive_feed_link( 'question' );
			$a_feed = get_post_type_archive_feed_link( 'answer' );
			echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr__( 'Question Feed', 'anspress-question-answer' ) . '" href="' . esc_url( $q_feed ) . '" />';
			echo '<link rel="alternate" type="application/rss+xml" title="' . esc_attr__( 'Answers Feed', 'anspress-question-answer' ) . '" href="' . esc_url( $a_feed ) . '" />';
		}
	}

	/**
	 * Update concal link when wpseo plugin installed.
	 *
	 * @return string
	 * @deprecated 4.1.0
	 */
	public static function wpseo_canonical( $url ) {
		if ( is_question() ) {
			return get_permalink( get_question_id() );
		}

		return $url;
	}

	/**
	 * Ajax callback for post actions dropdown.
	 *
	 * @since 3.0.0
	 */
	public static function post_actions() {
		$post_id = (int) ap_sanitize_unslash( 'post_id', 'r' );

		if ( ! check_ajax_referer( 'post-actions-' . $post_id, 'nonce', false ) || ! is_user_logged_in() ) {
			ap_ajax_json( 'something_wrong' );
		}

		ap_ajax_json( [ 'success' => true, 'actions' => ap_post_actions( $post_id ) ] );
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
			$templates = [ 'anspress.php', 'page.php', 'singular.php', 'index.php' ];

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
				$_term = get_queried_object();
				$term_type = str_replace( 'question_', '', $_term->taxonomy );
				array_unshift( $templates, 'anspress-' . $term_type . '.php' );
			}

			$new_template = locate_template( $templates );

			if ( '' !== $new_template ) {
				return $new_template ;
			}
		}

		return $template;
	}

	/**
	 * Filter single question content to render [anspress] shortcode.
	 *
	 * @param string $content Content.
	 * @return string
	 *
	 * @since 4.1.0
	 * @since 4.1.2 Do not replace content once question is loaded.
	 */
	public static function the_content_single_question( $content ) {
		global $ap_shortcode_loaded, $post, $question_rendered;

		if ( ! $post || true === $question_rendered ) {
			return $content;
		}

		if ( true !== $ap_shortcode_loaded && is_singular( 'question' ) ) {
			return do_shortcode( '[anspress]' );
		}

		// Check if user have permission.
		if ( in_array( $post->post_type, [ 'question', 'answer' ], true ) && ! ap_user_can_read_post( $post->ID, false, $post->post_type ) ) {
			return '<p>' . esc_attr__( 'Sorry, you do not have permission to read this post.', 'anspress-question-answer' ) . '</p>';
		}

		return $content;
	}

	/**
	 * Return comment open as false in single question page. So that
	 * theme won't render comments again.
	 *
	 * @global $question_rendered
	 * @param boolean $ret Return.
	 * @return boolean
	 *
	 * @since 4.1.0
	 */
	public static function single_question_comment_disable( $ret ) {
		global $question_rendered;

		if ( true === $question_rendered && is_singular( 'question' ) ) {
			return false;
		}

		return $ret;
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
			$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
			return wp_trim_words( $post->post_content, $excerpt_length, $excerpt_more );
		}

		return $excerpt;
	}

	/**
	 * Remove hentry class from question, answers and main pages .
	 *
	 * @param array   $post_classes Post classes.
	 * @param array   $class        An array of additional classes added to the post.
	 * @param integer $post_id      Post ID.
	 * @return array
	 * @since 4.1.0
	 */
	public static function remove_hentry_class( $post_classes, $class, $post_id ) {
		$_post = ap_get_post( $post_id );

		if ( $_post && ( in_array( $_post->post_type, [ 'answer', 'question' ], true ) || in_array( $_post->ID, ap_main_pages_id() ) ) ) {
			return array_diff( $post_classes, [ 'hentry' ] );
		}

		return $post_classes;
	}

	/**
	 * Callback for showing content below question and answer.
	 *
	 * @return void
	 * @since 4.1.2
	 */
	public static function after_question_content() {
		echo ap_post_status_badge(); // xss safe.

		$_post = ap_get_post();
		$query_db = 'answer' === $_post->post_type ? false : true;
		$activity = ap_recent_activity( null, false, $query_db );

		if ( ! empty( $activity ) ) {
			echo '<div class="ap-post-updated"><i class="apicon-clock"></i>' . $activity . '</div>';
		}
	}
}
