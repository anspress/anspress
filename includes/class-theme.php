<?php
/**
 * Class for anspress theme
 *
 * @package   AnsPress
 * @author    Rahul Aryan <admin@rahularyan.com>
 * @license   GPL-2.0+
 * @link      http://rahularyan.com
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Holds all hooks related to frontend layout/theme
 */
class AnsPress_Theme
{
	/**
	 * Parent class object
	 * @var object
	 */
	protected $ap;

	/**
	 * Initialize the class
	 * @param AnsPress $ap parent class.
	 */
	public function __construct($ap) {
		$this->ap = $ap;

		$this->ap->add_action( 'init', $this, 'init_actions' );
		$this->ap->add_filter( 'post_class', $this, 'question_answer_post_class' );
		$this->ap->add_filter( 'body_class', $this, 'body_class' );
		$this->ap->add_filter( 'comments_template', $this, 'comment_template' );
		$this->ap->add_action( 'after_setup_theme', $this, 'includes' );
		// $this->ap->add_filter( 'wp_title', $this, 'ap_title' , 10, 2 );
		// $this->ap->add_filter( 'wpseo_title', $this, 'wpseo_title' , 10, 2 );
		// $this->ap->add_filter( 'the_title', $this, 'the_title', 10, 2 );
		$this->ap->add_filter( 'wp_head', $this, 'feed_link', 9 );
		$this->ap->add_filter( 'wpseo_canonical', $this, 'wpseo_canonical' );
		$this->ap->add_action( 'ap_before', $this, 'ap_before_html_body' );
		$this->ap->add_action( 'wp', $this, 'remove_head_items', 10 );
		$this->ap->add_action( 'wp_head', $this, 'wp_head', 11 );
	}

	/**
	 * Function get called on init
	 */
	public function init_actions() {
		// Register anspress shortcode.
		add_shortcode( 'anspress', array( AnsPress_BasePage_Shortcode::get_instance(), 'anspress_sc' ) );

		// Register question shortcode.
		add_shortcode( 'question', array( AnsPress_Question_Shortcode::get_instance(), 'anspress_question_sc' ) );
	}

	/**
	 * AnsPress theme function as like WordPress theme function
	 * @return void
	 */
	public function includes() {
		require_once ap_get_theme_location( 'functions.php' );
	}

	/**
	 * Add answer-seleted class in post_class
	 * @param  array $classes Post class attribute.
	 * @return array
	 * @since 2.0.1
	 */
	public function question_answer_post_class($classes) {
		global $post;

		if ( 'question' == $post->post_type ) {
			if ( ap_question_best_answer_selected( $post->ID ) ) {
				$classes[] = 'answer-selected';
			}

			if ( ap_is_featured_question( $post->ID ) ) {
				$classes[] = 'featured-question';
			}

			$classes[] = 'answer-count-' . ap_count_answer_meta();
		} elseif ( 'answer' == $post->post_type ) {
			if ( ap_answer_is_best( $post->ID ) ) {
				$classes[] = 'best-answer';
			}
		}

		return $classes;
	}

	/**
	 * Add anspress classess to body
	 * @param  array $classes Body class attribute.
	 * @return array
	 * @since 2.0.1
	 */
	public function body_class($classes) {
		// Add anspress class to body.
		if ( is_anspress() ) {
			$classes[] = 'anspress';
			$classes[] = 'ap-page-' . ap_current_page();
		}

		return $classes;
	}

	/**
	 * Register AnsPress comment template
	 * @param  string $comment_template path to comment template.
	 * @return string
	 */
	public function comment_template($comment_template) {
		global $post;
		if ( $post->post_type == 'question' || $post->post_type == 'answer' ) {
			return ap_get_theme_location( 'comments.php' );
		} else {
			return $comment_template;
		}
	}

	/**
	 * Filter wp_title
	 * @param string $title WP page title.
	 * @return string
	 */
	public function ap_title($title) {
		if ( is_anspress() ) {
			remove_filter('wp_title', array(
				$this,
				'ap_title',
			));

			$new_title = ap_page_title();

			if ( strpos( $title, 'ANSPRESS_TITLE' ) !== false ) {
				$new_title = str_replace( 'ANSPRESS_TITLE', $new_title, $title );
			} else {
				$new_title = $new_title.' | ';
			}

			$new_title = apply_filters( 'ap_title', $new_title );

			return $new_title;
		}

		return $title;
	}

	/**
	 * Filter wpseo plugin title
	 * @param  string $title Page title.
	 * @return string
	 */
	public function wpseo_title($title) {
		if ( is_anspress() ) {
			remove_filter('wpseo_title', array(
				$this,
				'wpseo_title',
			));

			$new_title = ap_page_title();

			if ( strpos( $title, 'ANSPRESS_TITLE' ) !== false ) {
				$new_title = str_replace( 'ANSPRESS_TITLE', $new_title, $title ). ' | ' . get_bloginfo( 'name' );
			} else {
				$new_title = $new_title.' | '. get_bloginfo( 'name' );
			}

			$new_title = apply_filters( 'ap_wpseo_title', $new_title );

			return $new_title;
		}

		return $title;
	}

	/**
	 * Filter the_title()
	 * @param  string $title Current page/post title.
	 * @param  string $id    Post ID.
	 * @return string
	 */
	public function the_title($title, $id = null) {

		if ( ap_opt( 'base_page' ) == $id  ) {
			remove_filter('the_title', array(
				$this,
				'the_title',
			));
			return ap_page_title();
		}
		return $title;
	}

	/**
	 * Add feed link in wp_head
	 */
	public function feed_link() {
		if ( is_anspress() ) {
			echo '<link href="' . esc_url( home_url( '/feed/question-feed' ) ) . '" title="' . esc_attr__( 'Question Feed', 'anspress-question-answer' ) . '" type="application/rss+xml" rel="alternate">';
		}
	}

	/**
	 * Add default before body sidebar in AnsPress contents
	 */
	public function ap_before_html_body() {
		dynamic_sidebar( 'ap-before' );
	}

	/**
	 * Remove some unwanted things from wp_head
	 */
	public function remove_head_items($WP) {
		global $wp_query;
		if ( is_anspress() ) {
			remove_action( 'wp_head', 'rsd_link' );
			remove_action( 'wp_head', 'wlwmanifest_link' );
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
			remove_action( 'wp_head', 'rel_canonical' );
			remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'feed_links', 2 );
			$wp_query->queried_object->post_title = ap_page_title();
		}
	}

	/**
	 * Add feed and links in HEAD of the document
	 */
	public function wp_head() {
		if ( is_anspress() ) {
			$q_feed = get_post_type_archive_feed_link( 'question' );
			$a_feed = get_post_type_archive_feed_link( 'answer' );
			echo '<link rel="alternate" type="application/rss+xml" title="'.esc_attr__( 'Question feed', 'anspress-question-answer' ).'" href="'.esc_url( $q_feed ).'" />';
			echo '<link rel="alternate" type="application/rss+xml" title="'.esc_attr__( 'Answers feed', 'anspress-question-answer' ).'" href="'.esc_url( $a_feed ).'" />';
		}

		if ( is_question() && get_query_var( 'ap_page' ) != 'base' ) {
			echo '<link rel="canonical" href="'.esc_url( get_permalink( get_question_id() ) ).'">';
			echo '<link rel="shortlink" href="'.esc_url( wp_get_shortlink( get_question_id() ) ).'" />';
		}
	}

	/**
	 * Update concal link when wpseo plugin installed
	 * @return string
	 */
	public function wpseo_canonical() {
		if ( is_question() ) {
			return get_permalink( get_question_id() ); }
	}

}
