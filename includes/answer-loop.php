<?php
/**
 * AnsPress answer loop related functions and classes
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
 * Question
 *
 * This class is for retriving answers based on $args
 */
class Answers_Query extends WP_Query {

	/**
	 * Answer query arguments
	 * @var array
	 */
	public $args = array();

	/**
	 * Initialize class
	 * @param array $args Query arguments.
	 * @access public
	 * @since  2.0
	 */
	public function __construct( $args = array() ) {
		global $answers;

		$paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;

		$defaults = array(
			'question_id'           => get_question_id(),
			'ap_query'      		=> true,
			'ap_answers_query'      => true,
			'showposts'             => ap_opt( 'answers_per_page' ),
			'paged'                 => $paged,
			'only_best_answer'      => false,
			'include_best_answer'   => false,
		);

		$args['post_status'][] = 'publish';
		$args['post_status'][] = 'closed';

		$this->args = wp_parse_args( $args, $defaults );

		if ( isset( $this->args['question_id'] ) ) {
			$question_id = $this->args['question_id'];
		}

		if ( ! empty( $question_id ) ) {
			$this->args['post_parent'] = $question_id;
		}


		$this->args['post_type'] = 'answer';

		$args = $this->args;

		/**
		 * Initialize parent class
		 */
		parent::__construct( $args );
	}

	public function get_answers() {
		return parent::get_posts();
	}

	public function next_answer() {
		return parent::next_post();
	}
	// undo the pointer to next
	public function reset_next() {

		$this->current_post--;
		$this->post = $this->posts[$this->current_post];

		return $this->post;
	}

	public function the_answer() {
		global $post;
		$this->in_the_loop = true;

		if ( $this->current_post == -1 ) {
			   do_action_ref_array( 'ap_query_loop_start', array( &$this ) );
		}

		$post = $this->next_answer();

		setup_postdata( $post );
		anspress()->current_answer = $post;
	}

	public function have_answers() {
		return parent::have_posts();
	}

	public function rewind_answers() {
		parent::rewind_posts();
	}

	public function is_main_query() {
		return $this == anspress()->answers;
	}


	public function reset_answers_data() {
		parent::reset_postdata();

		if ( ! empty( $this->post ) ) {
			anspress()->current_answer = $this->post;
		}
	}

	/**
	 * Utility method to get all the ids in this request
	 *
	 * @return array of mdia ids
	 */
	public function get_ids() {

		$ids = array();

		if ( empty( $this->request ) ) {
			return $ids;
		}

		global $wpdb;
		$ids = $wpdb->get_col( $this->request );
		return $ids;
	}

}


/**
 * Display answers of a question
 * @param  array $args Answers query arguments.
 * @return Answers_Query
 * @since  2.0
 */
function ap_get_answers($args = array()) {

	if ( empty( $args['question_id'] ) ) {
		$args['question_id'] = get_question_id();
	}

	if ( ! isset( $args['sortby'] ) ) {
		$args['ap_sortby'] = (isset( $_GET['ap_sort'] )) ? sanitize_text_field( wp_unslash( $_GET['ap_sort'] ) ) : ap_opt( 'answers_sort' );
	}

	// if ( is_super_admin() || current_user_can( 'ap_view_private' ) ) {
		$args['post_status'][] = 'private_post';
	// }
	if ( is_super_admin() || current_user_can( 'ap_view_moderate' ) ) {
		$args['post_status'][] = 'moderate';
	}

	if ( is_super_admin() ) {
		$args['post_status'][] = 'trash';
	}

	// if ( isset( $_GET['show_answer'] ) ) {
	// 	$args['ap_query'] = 'order_answer_to_top';
	// 	$args['order_answer_id'] = (int) $_GET['show_answer'];
	// }

	return new Answers_Query( $args );
}

/**
 * Get an answer by ID
 * @param  integer $answer_id Answers ID.
 * @return Answers_Query
 * @since 2.1
 */
function ap_get_answer($answer_id) {
	return new Answers_Query( array( 'p' => $answer_id ) );
}

/**
 * Get selected answer object
 * @param  integer $question_id Question ID.
 * @since  2.0
 */
function ap_get_best_answer($question_id = false) {
	if ( false === $question_id ) {
		$question_id = get_question_id();
	}

	$args = array( 'only_best_answer' => true, 'question_id' => $question_id );
	return new Answers_Query( $args );
}

/**
 * Check if there are posts in the loop
 * @return boolean
 */
function ap_have_answers() {
	global $answers;

	if ( $answers ) {
		return $answers->have_posts();
	}
}

function ap_answers() {
	global $answers;
	if ( $answers ) {
		return $answers->have_posts();
	}
}

function ap_the_answer() {
	global $answers;
	if ( $answers ) {
		return $answers->the_post();
	}
}

/**
 * Ge the post object of currently irritrated post
 * @return object
 */
function ap_answer_the_object() {
	global $answers;
	if ( ! $answers ) {
		return;
	}

	return $answers->post;
}









/**
 * Check if user can view current answer
 * @return boolean
 * @since 2.1
 */
function ap_answer_user_can_view() {
	return ap_user_can_view_post( get_the_ID() );
}

/**
 * Check if current answer is selected as a best
 * @param integer|boolean $answer_id Answer ID or Object.
 * @return boolean
 * @since 2.1
 */
function ap_is_selected( $answer ) {
	$post = ap_get_post( $answer );
	return $post->selected;
}

/**
 * Output comment template if enabled.
 * @return void
 * @since 2.1
 */
function ap_answer_the_comments() {
	if ( ! ap_opt( 'disable_comments_on_answer' ) ) {
		echo '<div id="post-c-'.get_the_ID().'" class="ap-comments comment-container '. ( get_comments_number() > 0 ? 'have' : 'no' ) .'-comments">';
		// comments_template();
		echo '</div>';
	}
}

/**
 * output answers pagination
 */
function ap_answers_the_pagination() {
	global $answers;
	ap_pagination( false, $answers->max_num_pages );
}


/**
 * Return numbers of published answers.
 * @param  integer $question_id Question ID.
 * @return integer
 */
function ap_count_published_answers($question_id) {
	global $wpdb;
	$query = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = %d AND (post_status = %s OR post_status = %s) AND post_type = %s", $question_id, 'publish', 'closed', 'answer' );
	$key = md5( $query );

	$cache = wp_cache_get( $key, 'ap_count' );
	if ( false !== $cache ) {
		return $cache;
	}

	$count = $wpdb->get_var( $query );
	wp_cache_set( $key, $count, 'ap_count' );
	return $count;
}



/**
 * Count all answers excluding best answer.
 *
 * @return int
 */
function ap_count_other_answer($question_id = false) {
	if ( ! $question_id ) {
		$question_id = get_question_id();
	}

	$count = ap_get_answers_count( $question_id );

	if ( ap_have_answer_selected( $question_id ) ) {
		return (int) ($count - 1);
	}

	return (int) $count;
}

/**
 * Unselect an answer as best.
 * @param  integer $post_id Post ID.
 */
function ap_unselect_answer( $post_id ) {
	$post = ap_get_post( $post_id );

	do_action( 'ap_unselect_answer', $post->post_author, $post->post_parent, $post->ID );
	ap_unset_selected_answer( $post->post_parent );

	if ( ap_opt( 'close_selected' ) ) {
		wp_update_post( array( 'ID' => $post->post_parent, 'post_status' => 'publish' ) );
	}

	ap_update_user_best_answers_count_meta( $post->post_author );
	ap_update_user_solved_answers_count_meta( $post->post_author );
}
