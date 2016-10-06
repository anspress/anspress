<?php
/**
 * Question class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-3.0+
 * @link      https://anspress.io/
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Question_Query' ) ) :

	/**
	 * Question
	 *
	 * This class is for retriving questions based on $args
	 */
	class Question_Query extends WP_Query {

		public $args = array();

		/**
		 * Initialize class
		 * @param array $args
		 * @access public
		 * @since  2.0
		 */
		public function __construct( $args = array() ) {

			if ( is_front_page() ) {
				$paged = (isset( $_GET['ap_paged'] )) ? (int) $_GET['ap_paged'] : 1; } else {
				$paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1; }

				if ( isset( $args['post_parent'] ) ) {
					$post_parent = $args['post_parent'];
				} else {
					$post_parent = (get_query_var( 'parent' )) ? get_query_var( 'parent' ) : false;
				}

				$defaults = array(
					'showposts'     => ap_opt( 'question_per_page' ),
					'paged'         => $paged,
				);

				$args['post_status'][] = 'publish';
				$args['post_status'][] = 'closed';

			if ( $post_parent ) {
				$this->args['post_parent'] = $post_parent;
			}

				$this->args = wp_parse_args( $args, $defaults );

			if ( get_query_var( 'ap_s' ) != '' ) {
				$this->args['s'] = sanitize_text_field( get_query_var( 'ap_s' ) );
			}

			if ( isset( $this->args[ 'sortby' ] ) ) {
				$this->orderby_questions();
			}

				$this->args['post_type'] = 'question';

				$args = $this->args;

			/**
			 * Initialize parent class
			 */
			parent::__construct( $args );
		}

		/**
		 * Modify orderby args
		 * @return void
		 */
		public function orderby_questions() {
			switch ( $this->args[ 'sortby' ] ) {
				case 'answers' :
					$this->args[ 'orderby' ] = 'meta_value_num';
					$this->args[ 'meta_key' ] = ANSPRESS_ANS_META;
				break;
				case 'views' :
					$this->args[ 'orderby' ] = 'meta_value_num';
					$this->args[ 'meta_key' ] = ANSPRESS_VIEW_META;
				break;
				case 'unanswered' :
					$this->args[ 'orderby' ] = 'meta_value_num date';
					$this->args[ 'meta_key' ] = ANSPRESS_ANS_META ;
					$this->args[ 'meta_value' ] = 0 ;
				break;
				case 'voted' :
					$this->args['orderby'] = 'meta_value_num';
					$this->args['meta_key'] = ANSPRESS_VOTE_META;
				break;
				case 'unsolved' :
					$this->args['orderby'] = 'meta_value_num date';
					$this->args['meta_key'] = ANSPRESS_SELECTED_META;
					$this->args['meta_compare'] = '=';
					$this->args['meta_value'] = false;

				break;
				case 'oldest' :
					$this->args['orderby'] = 'date';
					$this->args['order'] = 'ASC';
				break;
				case 'active' :
					$this->args['orderby'] = 'meta_value';
					$this->args['meta_key'] = ANSPRESS_UPDATED_META;
					$this->args['meta_query']  = array(
						'relation' => 'OR',
						[ 'key' => ANSPRESS_UPDATED_META ],
					);
				break;

				// TOOD: Add more orderby like most viewed, and user order like 'answered by user_id', 'asked_by_user_id'
			}

		}

	}


endif;

if ( ! function_exists('ap_get_questions' ) ) {
	function ap_get_questions($args = array()) {

		if ( is_front_page() ) {
			$paged = (isset( $_GET['ap_paged'] )) ? (int) $_GET['ap_paged'] : 1;
		} else {
			$paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;
		}

		if ( ! isset( $args['post_parent'] ) ) {
			$args['post_parent'] = (get_query_var( 'parent' )) ? get_query_var( 'parent' ) : false;
		}

		if ( ! isset( $args['sortby'] ) && isset( $_GET['ap_filter'], $_GET['ap_filter']['sort'] ) ) {
			$args['sortby'] = sanitize_text_field( wp_unslash( $_GET['ap_filter']['sort'] ) );
		}

		if ( is_super_admin() || current_user_can( 'ap_view_private' ) ) {
			$args['post_status'][] = 'private_post';
		}

		if ( is_super_admin() || current_user_can( 'ap_view_moderate' ) ) {
			$args['post_status'][] = 'moderate';
		}

		$args = wp_parse_args( $args, array(
			'showposts'     => ap_opt( 'question_per_page' ),
			'paged'         => $paged,
			'ap_query'      => 'featured_post',
			'sortby'      	=> 'active',
		));

		return new Question_Query( $args );
	}
}


/**
 * Get an question by ID
 * @param  integer $question_id
 * @return Question_Query
 * @since 2.1
 */
function ap_get_question($question_id) {
	$args = array( 'p' => $question_id, 'ap_query' => 'single_question' );

	if ( ap_user_can_view_future_post( $question_id ) ) {
		$args['post_status'][] = 'future';
	}

	if ( ap_user_can_view_private_post( $question_id ) ) {
		$args['post_status'][] = 'private_post';
	}

	if ( ap_user_can_view_moderate_post( $question_id ) ) {
		$args['post_status'][] = 'moderate';
	}

	return new Question_Query( $args );
}

/**
 * Check if active post is private post
 * @return boolean
 * @since  2.1
 */
function ap_question_is_private() {
	return is_private_post();
}

/**
 * output questions page pagination
 * @return string pagination html tag
 */
function ap_questions_the_pagination() {
	global $questions;
	ap_pagination( false, $questions->max_num_pages );
}




	/**
	 * Output comment template if enabled.
	 * @return void
	 * @since 2.1
	 */
function ap_question_the_comments() {
	if ( ! ap_opt( 'disable_comments_on_question' ) ) {
		echo '<div id="post-c-'.get_the_ID().'" class="ap-comments comment-container '. ( get_comments_number() > 0 ? 'have' : 'no' ) .'-comments">';
		// comments_template();
		echo '</div>';
	}
}

	/**
	 * Output answer form
	 * @return void
	 * @since 2.1
	 */
function ap_question_the_answer_form() {
	include( ap_get_theme_location( 'answer-form.php' ) );
}

/**
 * Output answers of current question.
 * @since 2.1
 */
function ap_question_the_answers() {
	global $answers;

	$answers = ap_get_best_answer();
	include( ap_get_theme_location( 'best_answer.php' ) );

	$answers = ap_get_answers();

	include( ap_get_theme_location( 'answers.php' ) );
	wp_reset_postdata();
}
