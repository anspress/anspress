<?php
/**
 * Pagination template.
 *
 * @package AnsPress
 * @since 5.0.0
 */

$big = 999999999;

$request = remove_query_arg( 'ap_question_paged' );
$current = max( 1, get_query_var( 'ap_question_paged' ) );

$home_root = parse_url( home_url() );
$home_root = ( isset( $home_root['path'] ) ) ? $home_root['path'] : '';
$home_root = preg_quote( $home_root, '|' );

$request = preg_replace( '|^' . $home_root . '|i', '', $request );
$request = preg_replace( '|^/+|', '', $request );

$items = paginate_links(
	array(
		'base'      => add_query_arg( 'ap_question_paged', '%#%', home_url( $home_root . $request ) ),
		'format'    => '?ap_question_paged=%#%',
		'current'   => max( 1, get_query_var( 'ap_question_paged' ) ),
		'total'     => $totalPages,
		'prev_text' => __( '&laquo; Prev', 'anspress-question-answer' ),
		'next_text' => __( 'Next &raquo;', 'anspress-question-answer' ),
		'type'      => 'array',
		'mid_size'  => 2,
	)
);

if ( is_array( $items ) ) {
	$pagination = '<nav class="anspress-pagination">';
	foreach ( $items as $item ) {
		$pagination .= '<div class="anspress-pagination-page-item">' . str_replace( 'page-numbers', 'page-link', $item ) . '</div>';
	}
	$pagination .= '</nav>';

	echo $pagination;
}
