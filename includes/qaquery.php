<?php
// Exit if the file is accessed directly over web
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * QA Query class
 *
 * @since 3.1.0
 */
class QA_Query extends WP_Query {
	private $post_type;

	public function __construct( $query = '' ) {
		if ( is_front_page() ) {
			$paged = (isset( $_GET['ap_paged'] )) ? (int) $_GET['ap_paged'] : 1; 
		} else {
			$paged = (get_query_var( 'paged' )) ? get_query_var( 'paged' ) : 1; 
		}

		if ( isset( $args['post_parent'] ) ) {
			$post_parent = $args['post_parent'];
		} else {
			$post_parent = (get_query_var( 'parent' )) ? get_query_var( 'parent' ) : false;
		}

		$defaults = array(
			'showposts'     => ap_opt( 'question_per_page' ),
			'paged'         => $paged,
			'ap_query'      => 'list',
		);

		$args['post_status'][] = 'all';
		//$args['post_status'][] = 'closed';

		if ( $post_parent ) {
			$this->args['post_parent'] = $post_parent;
		}

		$this->args = wp_parse_args( $args, $defaults );

		if ( get_query_var( 'ap_s' ) != '' ) {
			$this->args['s'] = sanitize_text_field( get_query_var( 'ap_s' ) );
		}

		if ( isset( $this->args[ 'sortby' ] ) ) {
			//$this->orderby_questions();
		}
		$this->args['post_type'] = 'question';

		parent::__construct( $this->args );
	}

	public function get_questions() {
		return parent::get_posts();
	}

	public function next_question() {
		return parent::next_post();
	}
	// undo the pointer to next
	public function reset_next() {

		$this->current_post--;
		$this->post = $this->posts[$this->current_post];

		return $this->post;
	}

	public function the_question() {

		global $post;
		$this->in_the_loop = true;

		if ( $this->current_post == -1 ) {
			   do_action_ref_array( 'ap_query_loop_start', array( &$this ) );
		}

		$post = $this->next_media();

		setup_postdata( $post );
		anspress()->current_question = $post;
	}

	public function have_questions() {
		return parent::have_posts();
	}

	public function rewind_questions() {
		parent::rewind_posts();
	}

	public function is_main_query() {
		return $this == anspress()->questions;
	}


	public function reset_questions_data() {
		parent::reset_postdata();

		if ( ! empty( $this->post ) ) {
			anspress()->current_question = $this->post;
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

function ap_query( $args = array() ) {
	return new QA_Query($args );
}
