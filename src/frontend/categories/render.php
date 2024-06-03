<?php
/**
 * Render function for the categories block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

$currentPage   = isset( $_GET['ap_page'] ) ? intval( $_GET['ap_page'] ) : 1; // @codingStandardsIgnoreLine
$currentOffset = ( $currentPage - 1 ) * $attributes['itemsPerPage'];

$termsArgs = array(
	'taxonomy'   => 'question_category',
	'number'     => $attributes['itemsPerPage'],
	'hide_empty' => false,
	'offset'     => $currentOffset,
);

$terms = get_terms( $termsArgs );
$count = (int) wp_count_terms(
	array(
		'taxonomy'   => 'question_category',
		'hide_empty' => false,
	)
);

$columnStyle = '';

if ( $attributes['columns'] > 1 ) {
	$columnStyle = 'display: grid; grid-template-columns: repeat(' . esc_attr( $attributes['columns'] ) . ', 1fr);';
}
?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>

<?php if ( ! empty( $terms ) && ! is_wp_error( $terms ) && ! empty( $terms ) ) { ?>
	<div class='wp-block-anspress-question-answer-categories-ccon' style="<?php echo esc_attr( $columnStyle ); ?>">
		<?php foreach ( $terms as $t ) { ?>
			<?php
			$apCategoryMeta = get_term_meta( $t->term_id, 'ap_category', true );

			$style = '';

			if ( ! empty( $apCategoryMeta['image'] ) && ! empty( $apCategoryMeta['image']['url'] ) ) {
				$style = 'background-image: url(' . esc_url( $apCategoryMeta['image']['url'] ) . ');';
			}

			if ( ! empty( $apCategoryMeta['color'] ) ) {
				$style .= 'background-color: ' . esc_attr( $apCategoryMeta['color'] ) . ';';
			}
			?>
			<div class="wp-block-anspress-question-answer-categories-citem">
				<?php if ( ! empty( $attributes['showImage'] ) ) { ?>
					<div class='wp-block-anspress-question-answer-categories-cimage' style="<?php echo esc_attr( $style ); ?>">
					</div>
				<?php } ?>

				<a class='wp-block-anspress-question-answer-categories-ctitle' href="<?php echo esc_url( get_term_link( $t->term_id, 'question_category' ) ); ?>">
					<?php echo esc_html( $t->name ); ?>
				</a>

				<?php if ( $attributes['showCount'] ) { ?>
					<div class="wp-block-anspress-question-answer-categories-ccount">
						<?php
						printf(
							/* translators: %d: number of questions */
							esc_attr__( 'Questions: %d', 'anspress-question-answer' ),
							(int) $t->count
						);
						?>
					</div>
				<?php } ?>

				<?php if ( $attributes['showDescription'] ) { ?>
					<div class='wp-block-anspress-question-answer-categories-cdesc'>
						<?php echo esc_html( wp_trim_words( $t->description, $attributes['descriptionLength'] ) ); ?>
					</div>
				<?php } ?>

			</div>
		<?php } ?>
	</div>

	<div class='wp-block-anspress-categories-p'>
		<nav aria-label="Pagination">
			<div class="wp-block-anspress-question-answer-categories-p-ul">
				<?php
				$totalPages = ceil( $count / $attributes['itemsPerPage'] );
				$prevPage   = $currentPage - 1;
				$nextPage   = $currentPage + 1;
				$range      = 3; // Number of pages to show around the current page.
				?>
				<div class="wp-block-anspress-question-answer-categories-p-item">
					<?php if ( $currentPage > 1 ) { ?>
						<a class="wp-block-anspress-question-answer-categories-p-link" href="<?php echo esc_url( add_query_arg( 'ap_page', $prevPage ) ); ?>">
							<?php echo esc_html__( 'Previous', 'anspress-question-answer' ); ?>
						</a>
					<?php } ?>
				</div>

				<?php

				if ( $totalPages > 1 ) {
					// Display first page link.
					if ( $currentPage > $range + 1 ) {
						?>
						<div class="wp-block-anspress-question-answer-categories-p-item">
							<a class="wp-block-anspress-question-answer-categories-p-link" href="<?php echo esc_url( add_query_arg( 'ap_page', 1 ) ); ?>">
								<?php echo esc_attr( number_format_i18n( 1 ) ); ?>
							</a>
						</div>
						<?php if ( $currentPage > $range + 2 ) { ?>
							<div class="wp-block-anspress-question-answer-categories-p-item">
								<span>...</span>
							</div>
						<?php } ?>
						<?php
					}

					// Display pages around the current page.
					$minValue = max( 1, $currentPage - $range );
					$maxValue = min( $totalPages, $currentPage + $range );

					for ( $i = $minValue; $i <= $maxValue; $i++ ) {
						?>
						<div class="wp-block-anspress-question-answer-categories-p-item">
							<a class="wp-block-anspress-question-answer-categories-p-link <?php echo $currentPage === $i ? 'active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'ap_page', $i ) ); ?>">
								<?php echo esc_attr( number_format_i18n( $i ) ); ?>
							</a>
						</div>
						<?php
					}

					// Display last page link.
					if ( $currentPage < $totalPages - $range ) {
						if ( $currentPage < $totalPages - $range - 1 ) {
							?>
							<div class="wp-block-anspress-question-answer-categories-p-item">
								<span>...</span>
							</div>
						<?php } ?>
						<div class="wp-block-anspress-question-answer-categories-p-item">
							<a class="wp-block-anspress-question-answer-categories-p-link" href="<?php echo esc_url( add_query_arg( 'ap_page', $totalPages ) ); ?>">
								<?php echo esc_attr( number_format_i18n( $totalPages ) ); ?>
							</a>
						</div>
						<?php
					}
				}
				?>

				<div class="wp-block-anspress-question-answer-categories-p-item">
					<?php if ( $currentPage < $totalPages ) { ?>
						<a class="wp-block-anspress-question-answer-categories-p-link" href="<?php echo esc_url( add_query_arg( 'ap_page', $nextPage ) ); ?>">
							<?php echo esc_html__( 'Next', 'anspress-question-answer' ); ?>
						</a>
					<?php } ?>
				</div>
			</div>
		</nav>
	</div>

	<?php
} else {
	echo esc_html_e( 'No categories found.', 'anspress-question-answer' );
}
?>


</div>
