<?php
/**
 * AnsPress post types
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      https://anspress.io
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
	}

	/**
	 * Register question CPT.
	 *
	 * @since 2.0.1
	 */
	public static function register_question_cpt() {

		// Question CPT labels.
		$labels = array(
			'name'              => _x( 'Questions', 'Post Type General Name', 'anspress-question-answer' ),
			'singular_name'     => _x( 'Question', 'Post Type Singular Name', 'anspress-question-answer' ),
			'menu_name'         => __( 'Questions', 'anspress-question-answer' ),
			'parent_item_colon' => __( 'Parent Question:', 'anspress-question-answer' ),
			'all_items'         => __( 'All Questions', 'anspress-question-answer' ),
			'view_item'         => __( 'View Question', 'anspress-question-answer' ),
			'add_new_item'      => __( 'Add New Question', 'anspress-question-answer' ),
			'add_new'           => __( 'New Question', 'anspress-question-answer' ),
			'edit_item'         => __( 'Edit Question', 'anspress-question-answer' ),
			'update_item'       => __( 'Update Question', 'anspress-question-answer' ),
			'search_items'      => __( 'Search Questions', 'anspress-question-answer' ),
			'not_found'         => __( 'No question found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'No questions found in Trash', 'anspress-question-answer' ),
		);

		/**
		 * FILTER: ap_question_cpt_labels
		 * filter is called before registering question CPT
		 */
		$labels = apply_filters( 'ap_question_cpt_labels', $labels );

		// Question CPT arguments.
		$args   = array(
			'label'               => __( 'question', 'anspress-question-answer' ),
			'description'         => __( 'Question', 'anspress-question-answer' ),
			'labels'              => $labels,
			'supports'            => array(
				'title',
				'editor',
				'author',
				'comments',
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
			'query_var'           => 'apq',
		);

		/**
		 * FILTER: ap_question_cpt_args
		 * filter is called before registering question CPT
		 */
		$args = apply_filters( 'ap_question_cpt_args', $args );

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
			'parent_item_colon'  => __( 'Parent Answer:', 'anspress-question-answer' ),
			'all_items'          => __( 'All Answers', 'anspress-question-answer' ),
			'view_item'          => __( 'View Answer', 'anspress-question-answer' ),
			'add_new_item'       => __( 'Add New Answer', 'anspress-question-answer' ),
			'add_new'            => __( 'New Answer', 'anspress-question-answer' ),
			'edit_item'          => __( 'Edit Answer', 'anspress-question-answer' ),
			'update_item'        => __( 'Update Answer', 'anspress-question-answer' ),
			'search_items'       => __( 'Search Answers', 'anspress-question-answer' ),
			'not_found'          => __( 'No answer found', 'anspress-question-answer' ),
			'not_found_in_trash' => __( 'No answer found in Trash', 'anspress-question-answer' ),
		);

		/**
		 * FILTER: ap_answer_cpt_label
		 * filter is called before registering answer CPT
		 */
		$labels = apply_filters( 'ap_answer_cpt_label', $labels );

		// Answers CPT arguments.
		$args   = array(
			'label'               => __( 'answer', 'anspress-question-answer' ),
			'description'         => __( 'Answer', 'anspress-question-answer' ),
			'labels'              => $labels,
			'supports'            => array(
				'editor',
				'author',
				'comments',
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
		);

		/**
		 * FILTER: ap_answer_cpt_args
		 * filter is called before registering answer CPT
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
			$question_slug = ap_opt( 'question_page_slug' );

			if ( empty( $question_slug ) ) {
				$question_slug = 'question'; }

			if ( get_option( 'permalink_structure' ) ) {
				$link = home_url( '/' . $question_slug . '/' . $post->post_name . '/' );
			} else {
				$link = add_query_arg( array( 'apq' => false, 'question_id' => $post->ID ), ap_base_page_link() );
			}
			/**
			 * FILTER: ap_question_post_type_link
			 * Allow overriding of question post type permalink
			 */
			return apply_filters( 'ap_question_post_type_link', $link, $post );

		} elseif ( 'answer' === $post->post_type && 0 !== (int) $post->post_parent ) {
			$link = get_permalink( $post->post_parent ) . "{$post->ID}/";

			/**
			* FILTER: ap_answer_post_type_link
			* Allow overriding of answer post type permalink
			*/
			return apply_filters( 'ap_answer_post_type_link', $link, $post );
		}
		return $link;
	}

}
