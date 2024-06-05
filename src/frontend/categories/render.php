<?php
/**
 * Render function for the categories block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

use AnsPress\Classes\Plugin;

$queryVarKey   = 'ap_cat_paged';
$currentPage   = max( 1, (int) get_query_var( $queryVarKey ) );
$currentOffset = ( $currentPage - 1 ) * $attributes['itemsPerPage'];

$termsArgs = array(
	'taxonomy'   => 'question_category',
	'number'     => $attributes['itemsPerPage'],
	'hide_empty' => false,
	'offset'     => $currentOffset,
);

$query = new WP_Term_Query( $termsArgs );
$terms = $query->get_terms();

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

	<?php if ( $attributes['showPagination'] ) : ?>

		<?php
		if ( $count > 0 && $count > $attributes['itemsPerPage'] ) {
			$totalPages = $count / $attributes['itemsPerPage'];

			include Plugin::getPathTo( 'src/frontend/common/pagination.php' );
		}
		?>

	<?php endif; ?>

	<?php
} else {
	echo esc_html_e( 'No categories found.', 'anspress-question-answer' );
}
?>


</div>
