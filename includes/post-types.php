<?php
/**
 * AnsPress post types
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Custom post type.
 */
class AnsPress_PostTypes {

	/**
	 * Initialize the class
	 */
	public static function init() {
		// Register Custom Post types and taxonomy.
		anspress()->add_action( 'init', __CLASS__, 'register_question_cpt', 0 );
		anspress()->add_action( 'init', __CLASS__, 'register_answer_cpt', 0 );
		anspress()->add_action( 'post_type_link', __CLASS__, 'post_type_link', 10, 2 );
		anspress()->add_filter( 'post_type_archive_link', __CLASS__, 'post_type_archive_link', 10, 2 );
	}

	/**
	 * Return question permalink structure.
	 *
	 * @return object
	 * @since 4.1.0
	 */
	public static function question_perm_structure() {
		$question_permalink = ap_opt( 'question_page_permalink' );
		$question_slug      = ap_get_page_slug( 'question' );

		$rewrites = [];
		if ( 'question_perma_2' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question%';
		} elseif ( 'question_perma_3' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question_id%';
		} elseif ( 'question_perma_4' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question_id%/%question%';
		} elseif ( 'question_perma_5' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question%/%question_id%';
		} elseif ( 'question_perma_6' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question_id%-%question%';
		} elseif ( 'question_perma_7' === $question_permalink ) {
			$rewrites['rule'] = $question_slug . '/%question%-%question_id%';
		} else {
			$rewrites['rule'] = ap_base_page_slug() . '/' . $question_slug . '/%question%';
		}

		/**
		 * Allows filtering question permalink structure.
		 *
		 * @param array $rewrite Question permalink structure.
		 * @since 4.1.0
		 */
		return (object) apply_filters( 'ap_question_perm_structure', $rewrites );
	}

	/**
	 * Register question CPT.
	 *
	 * @since 2.0.1
	 */
	public static function register_question_cpt() {

		add_rewrite_tag( '%question_id%', '([0-9]+)', 'post_type=question&p=' );
		add_rewrite_tag( '%question%', '([^/]+)' );

		// Question CPT labels.
		$labels = array(
			'name'               => _x( 'Questions', 'Post Type General Name', 'anspress-question-answer' ),
			'singular_name'      => _x( 'Question', 'Post Type Singular Name', 'anspress-question-answer' ),
			'menu_name'          => __( 'Questions', 'anspress-question-answer' ),
			'parent_item_colon'  => __( 'Parent question:', 'anspress-question-answer' ),
			'all_items'          => __( 'All questions', 'anspress-question-answer' ),
			'view_item'          => __( 'View question', 'anspress-question-answer' ),
			'add_new_item'       => __( 'Add new question', 'anspress-question-answer' ),
			'add_new'            => __( 'New question', 'anspress-question-answer' ),
			'edit_item'          => __( 'Edit question', 'anspress-question-answer' ),
			'update_item'        => __( 'Update question', 'anspress-question-answer' ),
			'search_items'       => __( 'Search questions', 'anspress-question-answer' ),
			'not_found'          => __( 'No question found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'No questions found in trash', 'anspress-question-answer' ),
		);

		/**
		 * Override default question CPT labels.
		 *
		 * @param array $labels Default question labels.
		 */
		$labels = apply_filters( 'ap_question_cpt_labels', $labels );

		// Question CPT arguments.
		$args = array(
			'label'               => __( 'question', 'anspress-question-answer' ),
			'description'         => __( 'Question', 'anspress-question-answer' ),
			'labels'              => $labels,
			'supports'            => array(
				'title',
				'editor',
				'author',
				'comments',
				'excerpt',
				'trackbacks',
				'revisions',
				'custom-fields',
				'buddypress-activity',
			),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => true,
			'menu_icon'           => ANSPRESS_URL . '/assets/question.png',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'rewrite'             => false,
			'query_var'           => 'question',
			'delete_with_user'    => true,
		);

		/**
		 * Filter default question CPT arguments.
		 *
		 * @param array $args CPT arguments.
		 */
		$args = apply_filters( 'ap_question_cpt_args', $args );

		// Call it before registering cpt.
		AnsPress_Rewrite::rewrite_rules();

		// Register CPT question.
		register_post_type( 'question', $args );
	}

	/**
	 * Register answer custom post type.
	 *
	 * @since  2.0
	 */
	public static function register_answer_cpt() {
		// Answer CPT labels.
		$labels = array(
			'name'               => _x( 'Answers', 'Post Type General Name', 'anspress-question-answer' ),
			'singular_name'      => _x( 'Answer', 'Post Type Singular Name', 'anspress-question-answer' ),
			'menu_name'          => __( 'Answers', 'anspress-question-answer' ),
			'parent_item_colon'  => __( 'Parent answer:', 'anspress-question-answer' ),
			'all_items'          => __( 'All answers', 'anspress-question-answer' ),
			'view_item'          => __( 'View answer', 'anspress-question-answer' ),
			'add_new_item'       => __( 'Add new answer', 'anspress-question-answer' ),
			'add_new'            => __( 'New answer', 'anspress-question-answer' ),
			'edit_item'          => __( 'Edit answer', 'anspress-question-answer' ),
			'update_item'        => __( 'Update answer', 'anspress-question-answer' ),
			'search_items'       => __( 'Search answers', 'anspress-question-answer' ),
			'not_found'          => __( 'No answer found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'No answer found in trash', 'anspress-question-answer' ),
		);

		/**
		 * Filter default answer labels.
		 *
		 * @param array $labels Default answer labels.
		 */
		$labels = apply_filters( 'ap_answer_cpt_label', $labels );

		// Answers CPT arguments.
		$args = array(
			'label'               => __( 'answer', 'anspress-question-answer' ),
			'description'         => __( 'Answer', 'anspress-question-answer' ),
			'labels'              => $labels,
			'supports'            => array(
				'editor',
				'author',
				'comments',
				'excerpt',
				'revisions',
				'custom-fields',
			),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'menu_icon'           => ANSPRESS_URL . '/assets/answer.png',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
			'rewrite'             => false,
			'query_var'           => 'answer',
		);

		/**
		 * Filter default answer arguments.
		 *
		 * @param array $args Arguments.
		 */
		$args = apply_filters( 'ap_answer_cpt_args', $args );

		// Register CPT answer.
		register_post_type( 'answer', $args );
	}

	/**
	 * Alter question and answer CPT permalink.
	 *
	 * @param  string $link Link.
	 * @param  object $post Post object.
	 * @return string
	 * @since 2.0.0
	 */
	public static function post_type_link( $link, $post ) {
		if ( 'question' === $post->post_type ) {
			$question_slug = ap_opt( 'question_page_permalink' );

			if ( empty( $question_slug ) ) {
				$question_slug = 'question_perma_1';
			}

			$default_lang = '';

			// Support polylang permalink.
			if ( function_exists( 'pll_default_language' ) ) {
				$default_lang = pll_default_language();
			}

			if ( get_option( 'permalink_structure' ) ) {
				$structure = self::question_perm_structure();
				$rule      = str_replace( '%question_id%', $post->ID, $structure->rule );
				$rule      = str_replace( '%question%', $post->post_name, $rule );
				$link      = home_url( $default_lang . '/' . $rule . '/' );
			} else {
				$link = add_query_arg( array( 'question' => $post->ID ), ap_base_page_link() );
			}

			/**
			 * Allow overriding of question post type permalink
			 *
			 * @param string $link Question link.
			 * @param object $post Post object.
			 */
			return apply_filters( 'ap_question_post_type_link', $link, $post );

		} elseif ( 'answer' === $post->post_type && 0 !== (int) $post->post_parent ) {
			$link = get_permalink( $post->post_parent ) . "answer/{$post->ID}/";

			/**
			 * Allow overriding of answer post type permalink.
			 *
			 * @param string $link Question link.
			 * @param object $post Post object.
			 */
			return apply_filters( 'ap_answer_post_type_link', $link, $post );
		} // End if().

		return $link;
	}

	/**
	 * Filters the post type archive permalink.
	 *
	 * @param string $link      The post type archive permalink.
	 * @param string $post_type Post type name.
	 * @since 4.1.0
	 */
	public static function post_type_archive_link( $link, $post_type ) {
		if ( 'question' === $post_type ) {
			return get_permalink( ap_opt( 'base_page' ) );
		}

		return $link;
	}

}
