<?php
/**
 * Render dynamic profile nav block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

$attrtibuteActiveTab = $attributes['activeTab'] ?? null;

$activePage = $attrtibuteActiveTab ? $attrtibuteActiveTab : get_query_var( 'ap_page', 'questions' );

$pageItems = array(
	'questions'   => array(
		'title'   => __( 'Questions', 'anspress-question-answer' ),
		'link'    => ap_user_link( get_queried_object_id() ),
		'default' => true,
	),
	'reputations' => array(
		'title' => __( 'Reputations', 'anspress-question-answer' ),
		'link'  => ap_user_link( get_queried_object_id(), 'reputations' ),
	),
);
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div class='wp-block-anspress-user-profile-nav-items'>
		<?php foreach ( $pageItems as $key => $item ) : ?>
			<div class='wp-block-anspress-user-profile-nav-item <?php echo $activePage === $key ? 'active-nav' : ''; ?>'>
				<a href="<?php echo esc_url( $item['link'] ); ?>"><?php echo esc_attr( $item['title'] ); ?></a>
			</div>
		<?php endforeach; ?>
	</div>
</div>
