<?php
/**
 * AnsPress answer loop related functions and classes
 *
 * @package   AnsPress
 * @author    Rahul Aryan <support@anspress.io>
 * @license   GPL-2.0+
 * @link      http://anspress.io
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Answers_Query' ) ) :

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
				$question_id = $this->args['question_id']; }

			if ( ! empty( $question_id ) ) {
				$this->args['post_parent'] = $question_id; }

			if ( isset( $this->args['sortby'] ) ) {
				$this->orderby_answers();
			}

			// Check if requesting only for best Answer
			if ( isset( $this->args['only_best_answer'] ) && $this->args['only_best_answer'] ) {
				$this->args['meta_query'] = array(
					array(
						'key'           => ANSPRESS_BEST_META,
						'type'          => 'BOOLEAN',
						'compare'       => '=',
						'value'         => '1',
					),
				);
			}

			$this->args['post_type'] = 'answer';

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
		public function orderby_answers() {
			$this->args['meta_query'] = array();

			switch ( $this->args['sortby'] ) {

				case 'voted' :
					$this->args['orderby'] = 'meta_value_num' ;
					$this->args['meta_query']  = array(
						'relation' => 'AND',
						array(
							'key'       => ANSPRESS_VOTE_META,
						)
					);
				break;

				case 'oldest':
					$this->args['orderby'] = 'meta_value date';
					$this->args['order'] = 'ASC';
				break;

				case 'newest':
					$this->args['orderby'] = 'meta_value date';
					$this->args['order'] = 'DESC';
				break;

				default:
					$this->args['orderby'] = 'meta_value';
					$this->args['meta_key'] = ANSPRESS_UPDATED_META;
					$this->args['meta_query']  = array(
						'relation' => 'AND',
						array(
							'key' => ANSPRESS_UPDATED_META,
						)
					);
				break;
			}

			if ( ! $this->args['include_best_answer'] ) {
				$this->args['meta_query'][] = array(
				'key'           => ANSPRESS_BEST_META,
				'type'          => 'BOOLEAN',
				'compare'       => '!=',
				'value'         => '1',
				); }

		}

	}

endif;

/**
 * Display answers of a question
 * @param  array $args Answers query arguments.
 * @return void
 * @since  2.0
 */
function ap_get_answers($args = array()) {

	if ( empty( $args['question_id'] ) ) {
		$args['question_id'] = get_question_id();
	}

	if ( ! isset( $args['sortby'] ) ) {
		$args['sortby'] = (isset( $_GET['ap_sort'] )) ? sanitize_text_field( wp_unslash( $_GET['ap_sort'] ) ) : ap_opt( 'answers_sort' );
	}

	if ( is_super_admin() || current_user_can( 'ap_view_private' ) ) {
		$args['post_status'][] = 'private_post';
	}

	if ( is_super_admin() || current_user_can( 'ap_view_moderate' ) ) {
		$args['post_status'][] = 'moderate';
	}

	if ( isset( $_GET['show_answer'] ) ) {
		$args['ap_query'] = 'order_answer_to_top';
		$args['order_answer_id'] = (int) $_GET['show_answer'];
	}

	anspress()->answers = new Answers_Query( $args );
}

/**
 * Get an answer by ID
 * @param  integer $answer_id Answers ID.
 * @return void
 * @since 2.1
 */
function ap_get_answer($answer_id) {
	anspress()->answers = new Answers_Query( array( 'p' => $answer_id ) );
}

/**
 * Get select answer object
 * @param integer $question_id Question ID.
 * @since   2.0
 */
function ap_get_best_answer($question_id = false) {

	if ( ! $question_id ) {
		$question_id = get_question_id();
	}

	$args = array( 'only_best_answer' => true );
	anspress()->answers = new Answers_Query( $args );
}

/**
 * Check if there are posts in the loop
 * @return boolean
 */
function ap_have_answers() {
	return anspress()->answers->have_posts();
}

function ap_answers() {
	return anspress()->answers->have_posts();
}

function ap_the_answer() {
	return anspress()->answers->the_post();
}

/**
 * Ge the post object of currently irritrated post
 * @return object
 */
function ap_answer_the_object() {
	if ( ! @anspress()->answers ) {
		return;
	}

	return anspress()->answers->post;
}

/**
 * Echo active answer id
 * @return void
 * @since 2.1
 */
function ap_answer_the_answer_id() {
	echo ap_answer_get_the_answer_id();
}

/**
 * Get the active answer id
 * @return integer
 * @since 2.1
 */
function ap_answer_get_the_answer_id() {
	if ( ! is_object( ap_answer_the_object() ) ) {
		return false;
	}

	return ap_answer_the_object()->ID;
}

/**
 * Echo active answer question id
 * @return void
 * @since 2.1
 */
function ap_answer_the_question_id() {
	echo ap_answer_get_the_question_id();
}

/**
 * Get the active answer question id
 * @return integer
 * @since 2.1
 */
function ap_answer_get_the_question_id() {
	return ap_answer_the_object()->post_parent;
}

/**
 * Check if user can view current answer
 * @return boolean
 * @since 2.1
 */
function ap_answer_user_can_view() {
	return ap_user_can_view_post( ap_answer_get_the_answer_id() );
}

/**
 * Check if current answer is selected as a best
 * @param integer|boolean $answer_id Answer ID.
 * @return boolean
 * @since 2.1
 */
function ap_answer_is_best($answer_id = false) {
	$answer_id = ap_parameter_empty( $answer_id, @ap_answer_get_the_answer_id() );

	$meta = get_post_meta( $answer_id, ANSPRESS_BEST_META, true );

	if ( $meta ) { return true; }

	return false;
}

/**
 * Get current answer author id
 * @return integer
 * @since 2.1
 */
function ap_answer_get_author_id() {
	return ap_answer_the_object()->post_author;
}

/**
 * Echo user profile link
 * @since 2.1
 */
function ap_answer_the_author_link() {
	echo ap_answer_get_the_author_link();
}

/**
 * Return the author profile link
 * @return string
 * @since 2.1
 */
function ap_answer_get_the_author_link() {
	return ap_user_link( ap_answer_get_author_id() );
}

/**
 * Output current answer author avatar
 * @param  boolean|integer $size Size of avatar.
 */
function ap_answer_the_author_avatar($size = false) {
	$size = ap_parameter_empty( ap_opt( 'avatar_size_qanswer' ), $size );
	echo ap_answer_get_the_author_avatar( $size );
}

/**
 * Return answer author avatar
 * @param  integer $size Avatar size.
 * @return string
 * @since 2.1
 */
function ap_answer_get_the_author_avatar($size = 45) {
	return get_avatar( ap_answer_get_author_id(), $size );
}

/**
 * Output active answer vote button
 * @since 2.1
 */
function ap_answer_the_vote_button() {
	ap_vote_btn( ap_answer_the_object() );
}

/**
 * Output comment template if enabled.
 * @return void
 * @since 2.1
 */
function ap_answer_the_comments() {
	if ( ap_opt( 'show_comments_by_default' ) && ! ap_opt( 'disable_comments_on_answer' ) ) {
		comments_template(); }
}

/**
 * Echo time current answer was active
 * @return void
 * @since 2.1
 */
function ap_answer_the_active_ago() {
	echo ap_human_time( ap_answer_get_the_active_ago(), false );
}

/**
 * Return the answer active ago time
 * @return string
 * @since 2.1
 */
function ap_answer_get_the_active_ago() {
	return ap_last_active( ap_answer_get_the_answer_id() );
}

/**
 * Echo active answer permalink
 * @return void
 * @since 2.1
 */
function ap_answer_the_permalink() {
	echo ap_answer_get_the_permalink();
}

/**
 * Return active answer permalink
 * @return string
 * @since 2.1
 */
function ap_answer_get_the_permalink() {
	return esc_url( get_the_permalink( ap_answer_get_the_answer_id() ) );
}

/**
 * Echo active answer total vote
 * @return void
 * @since 2.1
 */
function ap_answer_the_net_vote() {
	if ( ! ap_opt( 'disable_voting_on_answer' ) ) {
		?>
			<span class="ap-questions-count ap-questions-vcount">
				<span><?php echo ap_answer_get_the_net_vote(); ?></span>
				<?php  esc_attr_e( 'votes', 'ap' ); ?>
			</span>
		<?php
	}
}

/**
 * Return count of net vote of a answer
 * @return integer
 * @since 2.1
 */
function ap_answer_get_the_net_vote() {
	return ap_net_vote( ap_answer_the_object() );
}

function ap_answer_the_vote_class() {
	echo ap_answer_get_the_vote_class();
}

/**
 * Get vote class of active answer
 * @return string
 */
function ap_answer_get_the_vote_class() {
	$vote = ap_answer_get_the_net_vote();

	if ( $vote > 0 ) {
		return 'positive'; } elseif ($vote < 0)
	return 'negative';
}

/**
 * output answers pagination
 */
function ap_answers_the_pagination() {
	$answers = anspress()->answers;
	ap_pagination( false, $answers->max_num_pages );
}

/**
 * Output answer active time
 * @param  boolean|integer $answer_id Answer ID.
 */
function ap_answer_the_active_time($answer_id = false) {
	echo ap_answer_get_the_active_time( $answer_id );
}

/**
 * Return last active time of answer
 * @param  boolean|integer $answer_id Answer ID.
 * @return string
 */
function ap_answer_get_the_active_time($answer_id = false) {
	$answer_id = ap_parameter_empty( $answer_id, @ap_answer_get_the_answer_id() );
	return ap_latest_post_activity_html( $answer_id );
}

/**
 * Output answer time in human readable format
 * @param  boolean|integer $answer_id      If outside of loop, post ID can be passed.
 * @param  integer         $format         WP time format.
 * @return void
 */
function ap_answer_the_time($answer_id = false, $format = 'U') {
	$answer_id = ap_parameter_empty( $answer_id, @ap_answer_get_the_answer_id() );
	printf( esc_attr__( '%s Ago', 'ap' ),
		'<a href="'. get_permalink( $answer_id ) .'"><time itemprop="datePublished" datetime="%s">'. ap_human_time( ap_answer_get_the_time( $answer_id, $format ) ) .'</time></a>'
	);
}

/**
 * Return answer time
 * @param  boolean|integer $answer_id      If outside of loop, post ID can be passed.
 * @param  integer         $format         WP time format.
 * @return string
 */
function ap_answer_get_the_time($answer_id = false, $format = '') {
	$answer_id = ap_parameter_empty( $answer_id, @ap_answer_get_the_answer_id() );
	return get_the_time( $format, $answer_id );
}

/**
 * Output count of total numbers of Answers
 * @since 2.1
 */
function ap_answer_the_count() {
	echo ap_answer_get_the_count();
}

/**
 * Return the count of total numbers of Answers
 * @return integer
 * @since 2.1
 */
function ap_answer_get_the_count() {
	return anspress()->answers->found_posts;
}
