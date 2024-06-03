<?php
/**
 * Render pagination block.
 *
 * @package AnsPress
 */

?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<nav aria-label="Pagination">
		<div class="wp-block-anspress-pagination-ul">
			<div class="wp-block-anspress-pagination-item">
				<a class="wp-block-anspress-pagination-link" href="#">Previous</a>
			</div>
			<div class="wp-block-anspress-pagination-item">
				<a class="wp-block-anspress-pagination-link" href="#">1</a>
			</div>
			<div class="wp-block-anspress-pagination-item">
				<a class="wp-block-anspress-pagination-link" href="#">2</a>
			</div>
			<div class="wp-block-anspress-pagination-item">
				<a class="wp-block-anspress-pagination-link" href="#">3</a>
			</div>
			<div class="wp-block-anspress-pagination-item">
				<a class="wp-block-anspress-pagination-link" href="#">Next</a>
			</div>
		</div>
	</nav>
</div>
