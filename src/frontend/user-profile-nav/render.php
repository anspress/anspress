<?php
/**
 * Render dynamic profile nav block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div class='wp-block-anspress-user-profile-nav-items'>
		<div class='wp-block-anspress-user-profile-nav-item active-nav'>
			<a href='#'><?php esc_attr_e( 'Questions', 'anspress-question-answer' ); ?></a>
		</div>
		<div class='wp-block-anspress-user-profile-nav-item'>
			<a href='#'><?php esc_attr_e( 'Reputations', 'anspress-question-answer' ); ?></a>
		</div>
	</div>
</div>
