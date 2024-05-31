<?php
/**
 * Render function for the categories block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

$terms = get_terms(
	array(
		'taxonomy'   => 'question_category',
		'number'     => $attributes['itemsPerPage'],
		'hide_empty' => false,
	)
);

$columnStyle = '';

if ( $attributes['columns'] > 1 ) {
	$columnStyle = 'display: grid; grid-template-columns: repeat(' . esc_attr( $attributes['columns'] ) . ', 1fr);';
}
?>
<div>
<?php if ( ! empty( $terms ) && ! is_wp_error( $terms ) && ! empty( $terms ) ) { ?>
	<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> className='wp-block-anspress-question-answer-categories-ccon' style="<?php echo esc_attr( $columnStyle ); ?>">
	<?php foreach ( $terms as $t ) { ?>
		<div>
			<div class='wp-block-anspress-question-answer-categories-ctitle'><?php echo esc_html( $t->name ); ?></div>
			<?php if ( $attributes['showDescription'] ) { ?>
				<p class='wp-block-anspress-question-answer-categories-cdesc'>
					<?php echo esc_html( wp_trim_words( $t->description, $attributes['descriptionLength'] ) ); ?>
				</p>
			<?php } ?>
			<?php if ( $attributes['showCount'] ) { ?>
				<p>
					<?php
					printf(
						/* translators: %d: number of questions */
						esc_attr__( 'Questions: %d', 'anspress-question-answer' ),
						(int) $t->count
					);
					?>
				</p>
			<?php } ?>
		</div>
	<?php } ?>
	</div>
	<?php
} else {
	echo esc_html_e( 'No categories found.', 'anspress-question-answer' );
}
?>
</div>
