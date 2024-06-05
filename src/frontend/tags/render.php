<?php
/**
 * Render function for the tags block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

use AnsPress\Classes\Plugin;

$queryVarKey   = 'ap_tag_paged';
$currentPage   = max( 1, (int) get_query_var( $queryVarKey ) );
$currentOffset = ( $currentPage - 1 ) * $attributes['itemsPerPage'];

$termsArgs = array(
	'taxonomy'   => 'question_tag',
	'number'     => $attributes['itemsPerPage'],
	'hide_empty' => false,
	'offset'     => $currentOffset,
);

$terms = get_terms( $termsArgs );
$count = (int) wp_count_terms(
	array(
		'taxonomy'   => 'question_tag',
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
	<div class='wp-block-anspress-question-answer-tags-ccon' style="<?php echo esc_attr( $columnStyle ); ?>">
		<?php foreach ( $terms as $t ) { ?>
			<div class="wp-block-anspress-question-answer-tags-citem">
				<a class='wp-block-anspress-question-answer-tags-ctitle' href="<?php echo esc_url( get_term_link( $t->term_id, 'question_tag' ) ); ?>">
					<?php echo esc_html( $t->name ); ?>
				</a>

				<?php if ( $attributes['showCount'] ) { ?>
					<div class="wp-block-anspress-question-answer-tags-ccount">
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
					<div class='wp-block-anspress-question-answer-tags-cdesc'>
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
	echo esc_html_e( 'No tags found.', 'anspress-question-answer' );
}
?>
</div>
