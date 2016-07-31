<?php
/**
 * Question class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
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
 * Check if there is post in loop
 * @return boolean
 */
function ap_have_questions() {
	global $questions;

	if ( $questions ) {
		return $questions->have_posts();
	}
}

function ap_questions() {
	global $questions;

	if ( $questions ) {
		return $questions->have_posts();
	}
}

function ap_the_question() {
	global $questions;
	return $questions->the_post();
}

function ap_question_the_object() {
	global $questions, $post;

	if ( $questions ) {
		return $questions->post;
	}

	return $post;
}

/**
 * Echo active question ID
 * @since 2.1
 */
function ap_question_the_ID() {
	echo ap_question_get_the_ID();
}

/**
 * Return question ID active in loop
 * @return integer|false
 * @since 2.1
 */
function ap_question_get_the_ID() {
	return ap_question_the_object()->ID;

	return false;
}

/**
 * echo current question post_parent
 * @since 2.1
 */
function ap_question_the_post_parent() {
	echo ap_question_get_the_post_parent();
}

/**
 * Returns the question post parent ID
 * @return integer
 * @since 2.1
 */
function ap_question_get_the_post_parent() {
	$question = ap_question_the_object();

	return $question->post_parent;
}

function ap_question_get_the_author_id() {
	echo ap_question_get_author_id();
}

function ap_question_get_author_id() {
	$question = ap_question_the_object();
	return $question->post_author;
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
 * echo user profile link
 * @return 2.1
 */
function ap_question_the_author_link() {
	echo ap_user_link();
}

/**
 * Return the author profile link
 * @return string
 * @since 2.1
 */
function ap_question_get_the_author_link() {
	return ap_user_link( ap_question_get_author_id() );
}

function ap_question_the_author_avatar($size = 45) {
	echo ap_question_get_the_author_avatar( $size );
}

/**
 * Return question author avatar
 * @param  integer $size
 * @return string
 * @since 2.1
 */
function ap_question_get_the_author_avatar($size = 45) {
	return get_avatar( ap_question_get_author_id(), $size );
}

function ap_question_the_answer_count() {
	$count = ap_question_get_the_answer_count();
	echo '<a class="ap-questions-count ap-questions-acount" href="'.ap_answers_link().'">'. sprintf( _n( '%s ans', '%s ans', $count, 'anspress-question-answer' ), '<span>'.$count.'</span>' ).'</a>';
}

/**
 * Return active question answer count
 * @return integer
 * @since 2.1
 */
function ap_question_get_the_answer_count() {
	return ap_count_answer_meta( ap_question_get_the_ID() );
}

/**
 * Echo active question total vote
 * @return void
 * @since 2.1
 */
function ap_question_the_net_vote() {
	if ( ! ap_opt( 'disable_voting_on_question' ) ) {
		?>
            <span class="ap-questions-count ap-questions-vcount">
                <span><?php echo ap_question_get_the_net_vote(); ?></span>
                <?php  _e( 'votes', 'anspress-question-answer' ); ?>
            </span>
        <?php
	}
}

	/**
	 * Return count of net vote of a question
	 * @return integer
	 * @since 2.1
	 */
function ap_question_get_the_net_vote() {
	return ap_net_vote( ap_question_the_object() );
}

/**
 * Echo active question permalink
 * @return void
 * @since 2.1
 */
function ap_question_the_permalink() {
	echo ap_question_get_the_permalink();
}

/**
 * Return active question permalink
 * @return string
 * @since 2.1
 */
function ap_question_get_the_permalink() {
	return get_the_permalink( ap_question_get_the_ID() );
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
	 * Output active question vote button
	 * @return 2.1
	 */
function ap_question_the_vote_button() {
	ap_vote_btn( ap_question_the_object() );
}

	/**
	 * Get active question post status
	 * @return void
	 * @since 2.1
	 */
function ap_question_the_status() {
	if ( ap_question_the_object()->post_status == 'private_post' ) {
		echo '<span class="ap-post-type private ap-notice gray">'.__( 'Private', 'anspress-question-answer' ).'</span>'; } elseif (ap_question_the_object()->post_status == 'moderate')
	echo '<span class="ap-post-type moderate ap-notice yellow">'.__( 'Moderate', 'anspress-question-answer' ).'</span>';
	elseif (ap_question_the_object()->post_status == 'closed')
	echo '<span class="ap-post-type closed ap-notice red">'.__( 'Closed', 'anspress-question-answer' ).'</span>';
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

/**
 * Echo time current question was active
 * @return void
 * @since 2.1
 */
function ap_question_the_active_ago() {
	echo ap_human_time( ap_question_get_the_active_ago(), false );
}

	/**
	 * Return the question active ago time
	 * @return string
	 * @since 2.1
	 */
function ap_question_get_the_active_ago() {
	return ap_last_active( ap_question_get_the_ID() );
}

	/**
	 * Echo view count for current question
	 * @since 2.1
	 */
function ap_question_the_view_count() {
	echo ap_question_get_the_view_count();
}

	/**
	 * Return total view count
	 * @return integer
	 * @since 2.1
	 */
function ap_question_get_the_view_count() {
	return ap_get_qa_views( ap_question_get_the_ID() );
}

	/**
	 * Echo questions subscriber count
	 * @since 2.1
	 */
function ap_question_the_subscriber_count() {
	echo ap_question_get_the_subscriber_count();
}
	/**
	 * Return the subscriber count for active question
	 * @return integer
	 * @since 2.1
	 */
function ap_question_get_the_subscriber_count() {
	return ap_subscribers_count( ap_question_get_the_ID() );
}

/**
 * Check if best answer is selected for question.
 * @param  false|integer $question_id
 * @return boolean
 */
function ap_question_best_answer_selected($question_id = false) {
	if ( false === $question_id ) {
		$question_id = ap_question_get_the_ID();
	}

	// Get question post meta.
	$meta = get_post_meta( $question_id, ANSPRESS_SELECTED_META, true );

	if ( ! $meta ) {
		return false;
	}

	return true;
}

function ap_question_the_active_time($question_id = false) {
	echo ap_question_get_the_active_time();
}

function ap_question_get_the_active_time($question_id = false) {
	$question_id = ap_parameter_empty( $question_id, @ap_question_get_the_ID() );
	return ap_latest_post_activity_html( $question_id );
}

/**
 * Output question created time.
 * @param  boolean|integer $question_id Question ID.
 * @param  string          $format      Format of time.
 */
function ap_question_the_time($question_id = false, $format = 'U') {
	$question_id = ap_parameter_empty( $question_id, ap_question_get_the_ID() );
	printf(
		'<time itemprop="datePublished" datetime="%1$s">%2$s</time>',
		ap_question_get_the_time( $question_id, 'c' ),
		sprintf(
			__( 'Posted %s', 'anspress-question-answer' ),
			ap_human_time( ap_question_get_the_time( $question_id, $format ) )
		)
	);
}

function ap_question_get_the_time($question_id = false, $format = '') {
	$question_id = ap_parameter_empty( $question_id, @ap_question_get_the_ID() );
	return get_post_time( $format, true, $question_id, true );
}

function ap_question_the_time_relative($question_id = false) {
	echo ap_question_get_the_time_relative( $question_id );
}

function ap_question_get_the_time_relative($question_id = false) {
	$question_id = ap_parameter_empty( $question_id, @ap_question_get_the_ID() );
	return ap_question_get_the_time( $question_id, 'U' );
}

/**
 * Check if current post is marked as featured
 * @param  boolean|integer $question_id    Question ID to check.
 * @return boolean
 * @since 2.2.0.1
 */
function ap_is_featured_question($question_id = false) {
	if ( false === $question_id ) {
		$question_id = get_the_ID(); }

	$featured = get_option( 'featured_questions' );

	if ( is_array( $featured ) && in_array( $question_id, $featured ) ) {
		return true;
	}

	return false;
}
