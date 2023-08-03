<?php
/**
 * AnsPress answer loop related functions and classes
 *
 * @package      AnsPress
 * @author       Rahul Aryan <rah12@live.com>
 * @license      GPL-3.0+
 * @link         https://anspress.net
 * @copyright    2014 Rahul Aryan
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Question
 *
 * This class is for retrieving answers based on $args
 */
class Answers_Query extends WP_Query {

	/**
	 * Answer query arguments
	 *
	 * @var array
	 */
	public $args = array();

	/**
	 * Initialize class
	 *
	 * @param array $args Query arguments.
	 * @access public
	 * @since  2.0
	 * @since  4.1.2 Fixed: pagination issue.
	 */
	public function __construct( $args = array() ) {
		global $answers;
		$paged    = (int) max( 1, get_query_var( 'ap_paged', 1 ) );
		$defaults = array(
			'question_id'            => get_question_id(),
			'ap_query'               => true,
			'ap_current_user_ignore' => false,
			'ap_answers_query'       => true,
			'showposts'              => ap_opt( 'answers_per_page' ),
			'paged'                  => $paged,
			'only_best_answer'       => false,
			'ignore_selected_answer' => false,
			'post_status'            => array( 'publish' ),
			'ap_order_by'            => ap_opt( 'answers_sort' ),
		);

		if ( get_query_var( 'answer_id' ) ) {
			$defaults['p'] = get_query_var( 'answer_id' );
		}

		$this->args                = wp_parse_args( $args, $defaults );
		$this->args['ap_order_by'] = sanitize_title( $this->args['ap_order_by'] );

		// Check if user can read private post.
		if ( ap_user_can_view_private_post() ) {
			$this->args['post_status'][] = 'private_post';
		}

		// Check if user can read moderate posts.
		if ( ap_user_can_view_moderate_post() ) {
			$this->args['post_status'][] = 'moderate';
		}

		// Show trash posts to super admin.
		if ( is_super_admin() ) {
			$this->args['post_status'][] = 'trash';
		}

		if ( isset( $this->args['question_id'] ) ) {
			$question_id = $this->args['question_id'];
		}

		if ( ! isset( $this->args['author'] ) && empty( $question_id ) && empty( $this->args['p'] ) ) {
			$this->args = array();
		} else {
			if ( ! empty( $question_id ) ) {
				$this->args['post_parent'] = $question_id;
			}

			$this->args['post_type'] = 'answer';
			$args                    = $this->args;

			/**
			 * Initialize parent class
			 */
			parent::__construct( $args );
		}
	}

	/**
	 * Get answers of current question.
	 *
	 * @return WP_Post[]|int[]
	 */
	public function get_answers() {
		return parent::get_posts();
	}

	/**
	 * Get next answer in loop.
	 *
	 * @return WP_Post
	 */
	public function next_answer() {
		return parent::next_post();
	}

	/**
	 * Undo the pointer to next
	 */
	public function reset_next() {
		--$this->current_post;
		$this->post = $this->posts[ $this->current_post ];

		return $this->post;
	}

	/**
	 * Setup current answer in loop.
	 */
	public function the_answer() {
		global $post;
		$this->in_the_loop = true;

		if ( -1 === $this->current_post ) {
			do_action_ref_array( 'ap_query_loop_start', array( &$this ) );
		}

		$post = $this->next_answer(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		setup_postdata( $post );
		anspress()->current_answer = $post;
	}

	/**
	 * Check if there are answers in loop.
	 *
	 * @return WP_Post[]|int[]
	 */
	public function have_answers() {
		return parent::have_posts();
	}

	/**
	 * Rewind answers in loop and reset index.
	 *
	 * @return mixed
	 */
	public function rewind_answers() {
		parent::rewind_posts();
	}

	/**
	 * Check if this is main query.
	 *
	 * @return WP_Post[]
	 */
	public function is_main_query() {
		return anspress()->answers === $this;
	}

	/**
	 * Reset answers data in loop.
	 */
	public function reset_answers_data() {
		parent::reset_postdata();

		if ( ! empty( $this->post ) ) {
			anspress()->current_answer = $this->post;
		}
	}

	/**
	 * Utility method to get all the ids in this request
	 *
	 * @return array of media ids
	 */
	public function get_ids() {
		if ( $this->ap_ids ) {
			return;
		}

		$this->ap_ids = array(
			'post_ids'   => array(),
			'attach_ids' => array(),
			'user_ids'   => array(),
		);

		foreach ( (array) $this->posts as $_post ) {
			$this->ap_ids['post_ids'][] = $_post->ID;
			$this->ap_ids['attach_ids'] = array_filter( array_merge( explode( ',', $_post->attach ), $this->ap_ids['attach_ids'] ) );

			if ( ! empty( $_post->post_author ) ) {
				$this->ap_ids['user_ids'][] = $_post->post_author;
			}

			// Add activities user_id to array.
			if ( ! empty( $_post->activities ) && ! empty( $_post->activities['user_id'] ) ) {
				$this->ap_ids['user_ids'][] = $_post->activities['user_id'];
			}
		}

		// Unique ids only.
		foreach ( (array) $this->ap_ids as $k => $ids ) {
			$this->ap_ids[ $k ] = array_unique( $ids );
		}
	}



	/**
	 * Pre fetch current users vote on all answers
	 *
	 * @since 3.1.0
	 * @since 4.1.2 Prefetch posts activity.
	 */
	public function pre_fetch() {
		$this->get_ids();
		ap_prefetch_recent_activities( $this->ap_ids['post_ids'], 'a_id' );
		ap_user_votes_pre_fetch( $this->ap_ids['post_ids'] );
		ap_post_attach_pre_fetch( $this->ap_ids['attach_ids'] );

		if ( ! empty( $this->ap_ids['user_ids'] ) ) {
			ap_post_author_pre_fetch( $this->ap_ids['user_ids'] );
		}

		do_action( 'ap_pre_fetch_answer_data', $this->ap_ids );
	}
}


/**
 * Display answers of a question
 *
 * @param  array $args Answers query arguments.
 * @return Answers_Query
 * @since  2.0
 */
function ap_get_answers( $args = array() ) {
	if ( empty( $args['question_id'] ) ) {
		$args['question_id'] = get_question_id();
	}

	if ( ! isset( $args['ap_order_by'] ) ) {
		$order_by = ap_sanitize_unslash( 'order_by', 'g' );

		$args['ap_order_by'] = ! empty( $order_by ) ? $order_by : ap_opt( 'answers_sort' );
	}

	return new Answers_Query( $args );
}

/**
 * Get an answer by ID
 *
 * @param  integer $answer_id Answers ID.
 * @return Answers_Query
 * @since 2.1
 */
function ap_get_answer( $answer_id ) {
	return new Answers_Query( array( 'p' => $answer_id ) );
}

/**
 * Get selected answer object
 *
 * @param  integer $question_id Question ID.
 * @since  2.0
 */
function ap_get_best_answer( $question_id = false ) {
	if ( false === $question_id ) {
		$question_id = get_question_id();
	}

	$args = array(
		'only_best_answer' => true,
		'question_id'      => $question_id,
	);
	return new Answers_Query( $args );
}

/**
 * Check if there are posts in the loop
 *
 * @return boolean
 */
function ap_have_answers() {
	global $answers;

	if ( $answers ) {
		return $answers->have_posts();
	}

	return false;
}

/**
 * Setup answer in loop.
 *
 * @return WP_Post
 */
function ap_the_answer() {
	global $answers;
	if ( $answers ) {
		return $answers->the_post();
	}
}

/**
 * Check total answers found in loop.
 *
 * @return int
 */
function ap_total_answers_found() {
	global $answers;
	return $answers->found_posts;
}

/**
 * Ge the post object of currently iterated post
 *
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
 *
 * @return boolean
 * @since 2.1
 */
function ap_answer_user_can_view() {
	return ap_user_can_view_post( get_the_ID() );
}

/**
 * Output answers pagination. Should be used inside a loop.
 *
 * @return void.
 */
function ap_answers_the_pagination() {
	if ( get_query_var( 'answer_id' ) ) {
		echo '<a class="ap-all-answers" href="' . esc_url( get_permalink( get_question_id() ) ) . '">' .
		// translators: %d is total answer count of question.
		esc_attr( sprintf( __( 'You are viewing 1 out of %d answers, click here to view all answers.', 'anspress-question-answer' ), ap_get_answers_count( get_question_id() ) ) ) . '</a>';
	} else {
		global $answers;
		$paged = ( get_query_var( 'ap_paged' ) ) ? get_query_var( 'ap_paged' ) : 1;
		ap_pagination( $paged, $answers->max_num_pages, '?ap_paged=%#%', get_permalink( get_question_id() ) . 'page/%#%/' );
	}
}

/**
 * Return numbers of published answers.
 *
 * @param  integer $question_id Question ID.
 * @return integer
 */
function ap_count_published_answers( $question_id ) {
	global $wpdb;
	$query = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->posts where post_parent = %d AND post_status = %s AND post_type = %s", $question_id, 'publish', 'answer' );

	$count = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB

	return $count;
}

/**
 * Count all answers excluding best answer.
 *
 * @param false|int $question_id Question id.
 * @return int
 */
function ap_count_other_answer( $question_id = false ) {
	if ( ! $question_id ) {
		$question_id = get_question_id();
	}

	$count = ap_get_answers_count( $question_id );

	if ( ap_have_answer_selected( $question_id ) ) {
		return (int) ( $count - 1 );
	}

	return (int) $count;
}

/**
 * Return paged position of answer.
 *
 * @param boolean|integer $question_id Question ID.
 * @param boolean|integer $answer_id Answer ID.
 * @return integer
 * @since 4.0.0
 */
function ap_get_answer_position_paged( $question_id = false, $answer_id = false ) {
	global $wpdb;

	if ( false === $question_id ) {
		$question_id = get_question_id();
	}

	if ( false === $answer_id ) {
		$answer_id = get_query_var( 'answer_id' );
	}

	$user_id     = get_current_user_id();
	$ap_order_by = ap_get_current_list_filters( 'order_by', 'active' );

	if ( 'voted' === $ap_order_by ) {
		$orderby = 'CASE WHEN IFNULL(qameta.votes_up - qameta.votes_down, 0) >= 0 THEN 1 ELSE 2 END ASC, ABS(qameta.votes_up - qameta.votes_down) DESC';
	} if ( 'oldest' === $ap_order_by ) {
		$orderby = "{$wpdb->posts}.post_date ASC";
	} elseif ( 'newest' === $ap_order_by ) {
		$orderby = "{$wpdb->posts}.post_date DESC";
	} else {
		$orderby = 'qameta.last_updated DESC ';
	}

	$post_status = array( 'publish' );

	// Check if user can read private post.
	if ( ap_user_can_view_private_post() ) {
		$post_status[] = 'private_post';
	}

	// Check if user can read moderate posts.
	if ( ap_user_can_view_moderate_post() ) {
		$post_status[] = 'moderate';
	}

	// Show trash posts to super admin.
	if ( is_super_admin() ) {
		$post_status[] = 'trash';
	}

	$status = "p.post_status IN ('" . implode( "','", $post_status ) . "')";

	$ids = $wpdb->get_col( $wpdb->prepare( "SELECT p.ID FROM $wpdb->posts p LEFT JOIN $wpdb->ap_qameta qameta ON qameta.post_id = p.ID  WHERE p.post_type = 'answer' AND p.post_parent = %d AND ( $status OR ( p.post_author = %d AND p.post_status IN ('publish', 'private_post', 'trash', 'moderate') ) ) ORDER BY $orderby", $question_id, $user_id ) ); // phpcs:ignore WordPress.DB

	$pos   = (int) array_search( $answer_id, $ids ) + 1; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
	$paged = ceil( $pos / ap_opt( 'answers_per_page' ) );

	return $paged;
}

/**
 * Echo post status of a answer.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 */
function ap_answer_status( $_post = null ) {
	ap_question_status( $_post );
}
