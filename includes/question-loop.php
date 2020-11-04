<?php
/**
 * Question class
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net/
 * @copyright 2014 Rahul Aryan
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


if ( ! function_exists( 'ap_get_questions' ) ) {
	function ap_get_questions( $args = [] ) {

		if ( is_front_page() ) {
			$paged = ( isset( $_GET['ap_paged'] ) ) ? (int) $_GET['ap_paged'] : 1;
		} else {
			$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		}

		if ( ! isset( $args['post_parent'] ) ) {
			$args['post_parent'] = get_query_var( 'parent' ) ? get_query_var( 'parent' ) : false;
		}

		$args = wp_parse_args(
			$args, array(
				'showposts' => ap_opt( 'question_per_page' ),
				'paged'     => $paged,
				'ap_query'  => 'featured_post',
			)
		);

		return new Question_Query( $args );
	}
}


/**
 * Get an question by ID.
 *
 * @param  integer $question_id Question ID.
 * @return Question_Query
 * @since 2.1
 */
function ap_get_question( $question_id ) {
	$args = array(
		'p'        => $question_id,
		'ap_query' => 'single_question',
	);

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
 * Output questions page pagination.
 *
 * @param integer|false $paged Current paged value.
 *
 * @return void
 * @since 4.1.0 Added new argument `$paged`.
 */
function ap_questions_the_pagination( $paged = false ) {
	if ( is_front_page() ) {
		$paged = get_query_var( 'page' );
	} elseif ( get_query_var( 'ap_paged' ) ) {
		$paged = get_query_var( 'ap_paged' );
	} elseif ( get_query_var( 'paged' ) ) {
		$paged = get_query_var( 'paged' );
	}

	ap_pagination( $paged, anspress()->questions->max_num_pages );
}
