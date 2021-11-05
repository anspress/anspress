<?php
/**
 * Tag page
 * Display list of question of a tag
 *
 * @package AnsPress
 * @subpackage Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php dynamic_sidebar( 'ap-top' ); ?>

<div class="row">

	<div id="ap-lists" class="<?php echo is_active_sidebar( 'ap-tag' ) ? 'ap-col-9' : 'ap-col-12'; ?>">
		<div class="ap-taxo-detail clearfix">

			<h2 class="entry-title">
				<?php echo esc_html( $question_tag->name ); ?>
				<span class="ap-tax-item-count">
					<?php
						// translators: %d is count of question.
						echo esc_attr( sprintf( _n( '%d Question', '%d Questions', $question_tag->count, 'anspress-question-answer' ), $question_tag->count ) );
					?>
				</span>
			</h2>

			<?php if ( ! empty( $question_tag->description ) ) : ?>
				<p class="ap-taxo-description"><?php echo wp_kses_post( $question_tag->description ); ?></p>
			<?php endif; ?>

		</div>

		<?php ap_get_template_part( 'question-list' ); ?>
	</div>

	<?php if ( is_active_sidebar( 'ap-tag' ) && is_anspress() ) : ?>

		<div class="ap-question-right ap-col-3">
			<?php dynamic_sidebar( 'ap-tag' ); ?>
		</div>

	<?php endif; ?>

</div>
