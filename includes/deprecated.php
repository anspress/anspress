<?php
/**
 * Contain list of function which are deprecated
 *
 * @package   AnsPress
 * @author    Rahul Aryan <rah12@live.com>
 * @license   GPL-3.0+
 * @link      https://anspress.net
 * @copyright 2014 Rahul Aryan
 */

if ( ! function_exists( '_deprecated_function' ) ) {
	require_once ABSPATH . WPINC . '/functions.php';
}

/**
 * Return hover card attributes.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @return string
 *
 * @deprecated 4.1.13
 */
function ap_get_hover_card_attr( $_post = null ) {
	_deprecated_function( __FUNCTION__, '4.1.13' );
}

/**
 * Echo hover card attributes.
 *
 * @param  mixed $_post Post ID, Object or null.
 * @deprecated 4.1.13
 */
function ap_hover_card_attr( $_post = null ) {
	_deprecated_function( __FUNCTION__, '4.1.13' );
}


/**
 * Ge the post object of currently irritrated post
 *
 * @return object
 * @deprecated 4.2.0
 */
function ap_answer_the_object() {
	_deprecated_function( __FUNCTION__, '4.2.0' );

	global $answers;
	if ( ! $answers ) {
		return;
	}

	return $answers->post;
}

/**
 * Output answers tab.
 *
 * @param string|boolean $base Current page url.
 * @since 2.0.1
 * @deprecated 4.2.0
 */
function ap_answers_tab( $base = false ) {
	$sort = ap_sanitize_unslash( 'order_by', 'r', ap_opt( 'answers_sort' ) );

	if ( ! $base ) {
		$base = get_permalink();
	}

	$navs = array(
		'active' => array(
			'link'  => add_query_arg( [ 'order_by' => 'active' ], $base ),
			'title' => __( 'Active', 'anspress-question-answer' ),
		),
	);

	if ( ! ap_opt( 'disable_voting_on_answer' ) ) {
		$navs['voted'] = array(
			'link'  => add_query_arg( [ 'order_by' => 'voted' ], $base ),
			'title' => __( 'Voted', 'anspress-question-answer' ),
		);
	}

	$navs['newest'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'newest' ], $base ),
		'title' => __( 'Newest', 'anspress-question-answer' ),
	);
	$navs['oldest'] = array(
		'link'  => add_query_arg( [ 'order_by' => 'oldest' ], $base ),
		'title' => __( 'Oldest', 'anspress-question-answer' ),
	);

	echo '<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">';
	foreach ( (array) $navs as $k => $nav ) {
		echo '<li' . ( $sort === $k ? ' class="active"' : '' ) . '><a href="' . esc_url( $nav['link'] . '#answers-order' ) . '">' . esc_attr( $nav['title'] ) . '</a></li>';
	}
	echo '</ul>';
}
