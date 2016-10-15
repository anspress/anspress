<?php
/**
 * Question query
 *
 * @package AnsPress
 */

/**
 * Exit if the file is accessed directly over web.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * QA Query class
 *
 * @since 3.1.0
 */
class Question_Query extends WP_Query {
	/**
	 * Store post type.
	 *
	 * @var string
	 */
	private $post_type;

	/**
	 * Initialize class.
	 *
	 * @param array|string $args Query args.
	 */
	public function __construct( $args = '' ) {
		if ( is_front_page() ) {
			$paged = (isset( $_GET['ap_paged'] )) ? (int) $_GET['ap_paged'] : 1; // input var ok.
		} else {
			$paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1;
		}

		if ( isset( $args['post_parent'] ) ) {
			$post_parent = $args['post_parent'];
		} else {
			$post_parent = (get_query_var( 'parent' )) ? get_query_var( 'parent' ) : false;
		}

		$defaults = array(
			'showposts' 	      => ap_opt( 'question_per_page' ),
			'paged' 	          => $paged,
			'ap_query' 	        => true,
			'ap_sortby' 	      => 'active',
			'ap_question_query' => true,
		);

		$args['post_status'][] = 'all';
		$this->args = wp_parse_args( $args, $defaults );

		if ( $post_parent ) {
			$this->args['post_parent'] = $post_parent;
		}

		if ( '' !== get_query_var( 'ap_s' ) ) {
			$this->args['s'] = sanitize_text_field( get_query_var( 'ap_s' ) );
		}

		$this->args['post_type'] = 'question';

		parent::__construct( $this->args );
	}

	/**
	 * Get posts.
	 */
	public function get_questions() {
		return parent::get_posts();
	}

	/**
	 * Update loop index to next post.
	 */
	public function next_question() {
		return parent::next_post();
	}

	/**
	 * Undo the pointer to next.
	 */
	public function reset_next() {
		$this->current_post--;
		$this->post = $this->posts[ $this->current_post ];
		return $this->post;
	}

	/**
	 * Set current question in loop.
	 */
	public function the_question() {
		global $post;
		$this->in_the_loop = true;

		if ( -1 === $this->current_post ) {
			   do_action_ref_array( 'ap_query_loop_start', array( &$this ) );
		}

		$post = $this->next_question(); // override ok.

		setup_postdata( $post );
		anspress()->current_question = $post;
	}

	/**
	 * Check if loop have questions.
	 *
	 * @return boolean
	 */
	public function have_questions() {
		return parent::have_posts();
	}

	/**
	 * Rewind questions in loop.
	 */
	public function rewind_questions() {
		parent::rewind_posts();
	}

	/**
	 * Check if main question query.
	 *
	 * @return boolean
	 */
	public function is_main_query() {
		return anspress()->questions === $this;
	}

	/**
	 * Reset current question in loop.
	 */
	public function reset_questions_data() {
		parent::reset_postdata();

		if ( ! empty( $this->post ) ) {
			anspress()->current_question = $this->post;
		}
	}

	/**
	 * Utility method to get all the ids in this request
	 *
	 * @return array of question ids
	 */
	public function get_ids() {
		if ( $this->ap_ids ) {
			return;
		}

		$this->ap_ids = [ 'post_ids' => array(), 'attach_ids' => array() ];
		foreach ( (array) $this->posts as $_post ) {
			$this->ap_ids['post_ids'][] = $_post->ID;
			$this->ap_ids['attach_ids'] = array_merge( explode( ',', $_post->attach ), $this->ap_ids['attach_ids'] );
			if ( ! empty( $_post->post_author ) ) {
				$this->ap_ids['user_ids'][] = $_post->post_author;
			}
		}
		// Unique ids only.
		foreach ( (array) $this->ap_ids as $k => $ids ) {
			$this->ap_ids[ $k ] = array_unique( $ids );
		}
	}

	/**
	 * Pre fetch current users vote on all answers
	 */
	public function pre_fetch() {
		$this->get_ids();
		ap_user_votes_pre_fetch( $this->ap_ids['post_ids'] );
		ap_post_attach_pre_fetch( $this->ap_ids['attach_ids'] );
		ap_post_author_pre_fetch( $this->ap_ids['user_ids'] );
	}
}

/**
 * Get posts with qameta fields.
 *
 * @param  object|integer|null $post Post object.
 * @return object
 */
function ap_get_post( $post = null ) {
	if ( empty( $post ) && isset( $GLOBALS['post'] ) ) {
		$post = $GLOBALS['post'];
	}

	if ( $post instanceof WP_Post || is_object( $post ) ) {
		$_post = $post;
	} elseif ( false !== $_post = wp_cache_get( $post, 'posts' ) ) {
		$_post = $_post;
	} else {
		$_post = WP_Post::get_instance( $post );
	}

	if ( $_post && ! isset( $_post->ap_qameta_wrapped ) ) {
		$_post = ap_append_qameta( $_post );
		wp_cache_set( $_post->ID, $_post, 'posts' );
	}

	return $_post;
}

/**
 * Check if there is post in loop
 *
 * @return boolean
 */
function ap_have_questions() {
	global $questions;

	if ( $questions ) {
		return $questions->have_posts();
	}
}

/**
 * Set current question in loop.
 *
 * @return Object
 */
function ap_the_question() {
	global $questions;
	return $questions->the_question();
}

/**
 * Return total numbers of questions found.
 *
 * @return integer
 */
function ap_total_questions_found() {
	global $questions;
	return $questions->found_posts;
}

/**
 * Return link of user profile page
 *
 * @return string
 */
function ap_get_profile_link() {
	global $post;
	return ap_user_link( $post->post_author );
}

/**
 * Echo user profile link
 */
function ap_profile_link() {
	echo ap_get_profile_link(); // xss ok.
}

/**
 * Return question author avatar.
 *
 * @param  integer $size Avatar size.
 * @param  mixed   $_post Post.
 * @return string
 */
function ap_get_author_avatar( $size = 45, $_post = null ) {
	$_post = ap_get_post( $_post );
	return get_avatar( $_post->post_author, $size );
}

/**
 * Echo question author avatar.
 *
 * @param  integer $size Avatar size.
 * @param  mixed   $_post Post.
 */
function ap_author_avatar( $size = 45, $_post = null ) {
	echo ap_get_author_avatar( $size, $_post ); // xss ok.
}

/**
 * Return hover card attributes.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 * @return string
 */
function ap_get_hover_card_attr( $_post = null ) {
	$_post = ap_get_post( $_post );
	return ap_hover_card_attributes( $_post->post_author );
}

/**
 * Echo hover card attributes.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 */
function ap_hover_card_attr( $_post = null ) {
	echo ap_get_hover_card_attr( $_post ); // xss ok.
}

/**
 * Return total published answer count.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 * @return integer
 */
function ap_get_answers_count( $_post = null ) {
	$_post = ap_get_post( $_post );
	return $_post->answers;
}

/**
 * Echo total votes count of a post.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 */
function ap_answers_count( $_post = null ) {
	echo ap_get_answers_count( $_post ); // xss ok.
}

/**
 * Return count of net vote of a question.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 * @return integer
 */
function ap_get_votes_net( $_post = null ) {
	$_post = ap_get_post( $_post );
	return $_post->votes_net;
}

/**
 * Echo count of net vote of a question.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 */
function ap_votes_net( $_post = null ) {
	echo ap_get_votes_net( $_post ); // xss ok.
}


/**
 * Echo post status of a question.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 */
function ap_status( $_post = null ) {
	$_post = ap_get_post( $_post );
	$status_obj = get_post_status_object( $_post->post_status );
	echo '<span class="ap-post-status ' . esc_attr( $_post->post_status ) . '">' . esc_attr( $status_obj->label ) . '</span>';
}

/**
 * Question meta to display.
 *
 * @param false|integer $question_id question id.
 */
function ap_question_metas( $question_id = false ) {
	if ( false === $question_id ) {
		$question_id = get_the_ID();
	}

	$metas = array();
	if ( ! is_question() ) {
		if ( ap_have_answer_selected() ) {
			$metas['solved'] = '<span class="ap-best-answer-label ap-tip" title="' . __( 'answer accepted', 'anspress-question-answer' ) . '">' . __( 'Solved', 'anspress-question-answer' ) . '</span>';
		}

		$view_count = ap_get_qa_views();
		$metas['views'] = '<i>' . sprintf( __( '%d views', 'anspress-question-answer' ), $view_count ) . '</i>';
		$metas['history'] = ap_latest_post_activity_html( $question_id, ! is_question() );
	}

	// If featured question.
	if ( ap_is_featured_question( $question_id ) ) {
		$metas['featured'] = __( 'Featured', 'anspress-question-answer' );
	}

	/*
     * FILTER: ap_display_question_meta
     * Used to filter question display meta
	 */
	$metas = apply_filters( 'ap_display_question_metas', $metas, $question_id );

	$output = '';
	if ( ! empty( $metas ) && is_array( $metas ) ) {
		foreach ( $metas as $meta => $display ) {
			$output .= "<span class='ap-display-meta-item {$meta}'>{$display}</span>";
		}
	}

	echo $output; // xss ok.
}

/**
 * Get recent activity of a post.
 *
 * @param  object|integer|null $_post Post ID, Object or null.
 * @return string
 */
function ap_get_recent_post_activity( $_post = null ) {
	$_post = ap_get_post( $_post );
	return ap_latest_post_activity_html( $_post->ID );
}

/**
 * Echo recent activity of a post.
 */
function ap_recent_post_activity() {
	echo ap_get_recent_post_activity(); // xss ok.
}

/**
 * Get a specific post field.
 *
 * @param  string              $field Post field name.
 * @param  object|integer|null $_post Post ID, Object or null.
 * @return mixed
 */
function ap_get_post_field( $field, $_post = null ) {
	$_post = ap_get_post( $_post );
	if ( isset( $_post->$field ) ) {
		return $_post->$field;
	}
	return '';
}

/**
 * Echo specific post field.
 *
 * @param  string              $field Post field name.
 * @param  object|integer|null $_post Post ID, Object or null.
 */
function ap_post_field( $field = null, $_post = null ) {
	echo ap_get_post_field( $field, $_post ); // xss ok.
}


/**
 * Get last active time in human readable format.
 *
 * @param  mixed $post_id Post ID/Object.
 * @return string
 * @since  2.4.8 Convert mysql date to GMT.
 */
function ap_get_last_active( $post_id = null ) {
	$p = ap_get_post( $post_id );
	$date = ! empty( $p->last_updated ) ? $p->last_updated : $p->post_modified_gmt;
	return ap_human_time( get_gmt_from_date( $date ), false );
}

/**
 * Echo last active time in human readable format.
 *
 * @param  mixed $post_id Post ID/Object.
 * @since  2.4.8 Convert mysql date to GMT.
 */
function ap_last_active( $post_id = null ) {
	echo esc_attr( ap_get_last_active( $post_id ) );
}


/**
 * Check if question have answer selected.
 *
 * @param  mixed $question Post object or ID.
 * @return boolean
 */
function ap_have_answer_selected( $question = null ) {
	$question = ap_get_post( $question );
	return ! empty( $question->selected_id );
}

/**
 * Return the ID of selected answer from a question.
 *
 * @param object|null|integer $_post Post object, ID or null.
 * @return integer
 */
function ap_selected_answer( $_post = null ) {
	$_post = ap_get_post( $_post );
	return $_post->selected_id;
}

/**
 * Return post time.
 *
 * @param  mixed  $_post   Post ID, Object or null.
 * @param  string $format Date format.
 * @return String
 */
function ap_get_time( $_post = null, $format = '' ) {
	$_post = ap_get_post( $_post );
	return get_post_time( $format, true, $_post->ID, true );
}

/**
 * Check if current post is marked as featured
 *
 * @param  boolean|integer $question    Question ID to check.
 * @return boolean
 * @since 2.2.0.1
 */
function ap_is_featured_question( $question = null ) {
	$question = ap_get_post( $question );
	return (bool) $question->featured;
}

/**
 * Get terms of a question.
 *
 * @param  boolean|string $taxonomy Taxonomy slug.
 * @param  mixed          $_post     Post object, ID or null.
 * @return string
 */
function ap_get_terms( $taxonomy = false, $_post = null ) {
	$_post = ap_get_post( $_post );
	if ( ! empty( $_post->terms ) ) {
		return $_post->terms;
	}
	return false;
}


/**
 * Check if post have terms of a taxonomy.
 *
 * @param  boolean|integer $post_id  Post ID.
 * @param  string          $taxonomy Taxonomy name.
 * @return boolean
 */
function ap_post_have_terms( $post_id = false, $taxonomy = 'question_category' ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$terms = get_the_terms( $post_id, 'question_category' );
	if ( ! empty( $terms ) ) {
		return true;
	}

	return false;
}

/**
 * Check if question or answer have attachemnts.
 *
 * @param  mixed $_post Post.
 * @return boolean
 */
function ap_have_attach( $_post = null ) {
	$_post = ap_get_post( $_post );
	if ( ! empty( $_post->attach ) ) {
		return true;
	}
	return false;
}

/**
 * Get attachment ids of a question or answer.
 *
 * @param  mixed $_post Post.
 * @return string|boolean
 */
function ap_get_attach( $_post = null ) {
	$_post = ap_get_post( $_post );
	if ( ! empty( $_post->attach ) ) {
		return explode( ',', $_post->attach );
	}
	return false;
}
