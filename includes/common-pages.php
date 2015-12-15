<?php
/**
 * Class for base page
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

/**
 * Handle output of all default pages of AnsPress
 */
class AnsPress_Common_Pages
{
	/**
	 * Parent class object
	 * @var object
	 */
	protected $ap;

	/**
	 * Initialize the class
	 * @param object $ap Parent class object.
	 */
	public function __construct($ap) {
		$this->ap = $ap;
		$this->ap->add_action( 'init', $this, 'register_common_pages' );
	}

	/**
	 * Register all pages of AnsPress
	 */
	public function register_common_pages() {
		ap_register_page( 'base', ap_opt( 'base_page_title' ), array( $this, 'base_page' ) );
		ap_register_page( ap_opt( 'question_page_slug' ), __( 'Question', 'anspress-question-answer' ), array( $this, 'question_page' ), false );
		ap_register_page( ap_opt( 'ask_page_slug' ), __( 'Ask', 'anspress-question-answer' ), array( $this, 'ask_page' ) );
		ap_register_page( 'edit', __( 'Edit', 'anspress-question-answer' ), array( $this, 'edit_page' ), false );
		ap_register_page( 'search', __( 'Search', 'anspress-question-answer' ), array( $this, 'search_page' ), false );
		ap_register_page( 'activity', __( 'Activity feed', 'anspress-question-answer' ), array( $this, 'activity_page' ) );
	}

	/**
	 * Layout of base page
	 */
	public function base_page() {
		global $questions, $wp;
		$query = $wp->query_vars;

		$tax_relation = @$wp->query_vars['ap_sc_atts_tax_relation'];
		$tax_relation = ! empty( $tax_relation ) ? $tax_relation : 'OR';

		$tags_operator = @$wp->query_vars['ap_sc_atts_tags_operator'];
		$tags_operator = ! empty( $tags_operator ) ? $tags_operator : 'IN';

		$categories_operator = @$wp->query_vars['ap_sc_atts_categories_operator'];
		$categories_operator = ! empty( $categories_operator ) ? $categories_operator : 'IN';

		$args = array();
		$args['tax_query'] = array( 'relation' => $tax_relation );

		if ( isset( $query['ap_sc_atts_tags'] ) && is_array( $query['ap_sc_atts_tags'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'question_tag',
				'field'    => 'slug',
				'terms'    => $query['ap_sc_atts_tags'],
				'operator' => $tags_operator,
			);
		} elseif ( isset( $_GET['ap_tag_sort'] ) && 0 != $_GET['ap_tag_sort'] ) {
			$cat = (int) $_GET['ap_tag_sort'];
			$args['tax_query'][] = array(
				'taxonomy' => 'question_tag',
				'field'    => 'term_id',
				'terms'    => array( $cat ),
			);
		}

		if ( isset( $query['ap_sc_atts_categories'] ) && is_array( $query['ap_sc_atts_categories'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'question_category',
				'field'    => 'slug',
				'terms'    => $query['ap_sc_atts_categories'],
				'operator' => $categories_operator,
			);
		} elseif ( isset( $_GET['ap_cat_sort'] ) && 0 != $_GET['ap_cat_sort'] ) {
			$cat = (int) $_GET['ap_cat_sort'];
			$args['tax_query'][] = array(
				'taxonomy' => 'question_category',
				'field'    => 'term_id',
				'terms'    => array( $cat ),
			);
		}

		/**
		 * FILTER: ap_main_questions_args
		 * Filter main question list args
		 * @var array
		 */
		$args = apply_filters( 'ap_main_questions_args', $args );

		$questions = ap_get_questions( $args );
		ap_get_template_part( 'base' );
	}

	/**
	 * Output single question page
	 * @return void
	 */
	public function question_page() {

		// Set Header as 404 if question id is not set.
		if( false === get_question_id() ){
			$this->set_404();
			return;
		}

		global $questions;

		$questions = ap_get_question( get_question_id() );

		if ( ap_have_questions() ) {
			/**
			 * Set current question as global post
			 * @since 2.3.3
			 */

			while ( ap_questions() ) : ap_the_question();
				global $post;
				setup_postdata( $post );
			endwhile;
			include( ap_get_theme_location( 'question.php' ) );

			wp_reset_postdata();
		} else {
			$this->set_404();
		}

	}

	/**
	 * Output ask page template
	 */
	public function ask_page() {
		include ap_get_theme_location( 'ask.php' );
	}

	/**
	 * Output edit page template
	 */
	public function edit_page() {
		$post_id = (int) sanitize_text_field( get_query_var( 'edit_post_id' ) );
		if ( ! ap_user_can_edit_question( $post_id ) ) {
				echo '<p>'.esc_attr__( 'You don\'t have permission to access this page.', 'anspress-question-answer' ).'</p>';
				return;
		} else {
			global $editing_post;
			$editing_post = get_post( $post_id );

			// Include theme file.
			include ap_get_theme_location( 'edit.php' );
		}
	}

	/**
	 * Load search page template
	 */
	public function search_page() {
		global $questions;
		$keywords   = sanitize_text_field( get_query_var( 'ap_s' ) );
		$type       = sanitize_text_field( wp_unslash( @$_GET['type'] ) );

		if ( '' == $type ) {
			$questions = ap_get_questions( array( 's' => $keywords ) );
			include( ap_get_theme_location( 'base.php' ) );
		} elseif ( 'user' == $type && ap_opt( 'enable_users_directory' ) ) {
			global $ap_user_query;
			$ap_user_query = ap_has_users( array( 'search' => $keywords, 'search_columns' => array( 'user_login', 'user_email', 'user_nicename' ) ) );
			include( ap_get_theme_location( 'users/users.php' ) );
		}
	}

	public function activity_page() {
		global $ap_activities;
	    $ap_activities = ap_get_activities( array( 'per_page' => 20) );

		include( ap_get_theme_location( 'activity/index.php' ) );
	}

	/**
	 * If page is not found then set header as 404
	 */
	public function set_404() {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		include ap_get_theme_location( 'not-found.php' );
	}

}

