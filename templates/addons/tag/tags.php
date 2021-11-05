<?php
/**
 * Tags page layout
 *
 * @link http://anspress.net
 * @since 1.0
 *
 * @package AnsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $question_tags;
?>

<?php dynamic_sidebar( 'ap-top' ); ?>

<div id="ap-tags" class="row">
	<div class="<?php echo is_active_sidebar( 'ap-tags' ) && is_anspress() ? 'ap-col-9' : 'ap-col-12'; ?>">

		<div class="ap-list-head clearfix">
			<form id="ap-search-form" class="ap-search-form">
				<button class="ap-btn ap-search-btn" type="submit"><?php esc_attr_e( 'Search', 'anspress-question-answer' ); ?></button>
				<div class="ap-search-inner no-overflow">
					<input name="ap_s" type="text" class="ap-search-input ap-form-input" placeholder="<?php esc_attr_e( 'Search tags', 'anspress-question-answer' ); ?>" value="<?php echo esc_attr( get_query_var( 'ap_s' ) ); ?>" />
				</div>
			</form>

			<?php ap_list_filters(); ?>
		</div><!-- close .ap-list-head.clearfix -->

		<ul class="ap-term-tag-box clearfix">
			<?php foreach ( $question_tags as $key => $question_tag ) : ?>
				<li class="clearfix">
					<div class="ap-tags-item">
						<a class="ap-term-title" href="<?php echo esc_url( get_tag_link( $question_tag ) ); ?>">
							<?php echo esc_html( $question_tag->name ); ?>
						</a>
						<span class="ap-tagq-count">
							<?php
								echo esc_attr(
									sprintf(
										// translators: %d is question count.
										_n( '%d Question', '%d Questions', $question_tag->count, 'anspress-question-answer' ),
										$question_tag->count
									)
								);
							?>
						</span>
					</div>
				</li>
			<?php endforeach; ?>
		</ul><!-- close .ap-term-tag-box.clearfix -->

		<?php ap_pagination(); ?>
	</div><!-- close #ap-tags -->

	<?php if ( is_active_sidebar( 'ap-tags' ) && is_anspress() ) { ?>
		<div class="ap-tags-sidebar ap-col-3">
			<?php dynamic_sidebar( 'ap-tags' ); ?>
		</div>
	<?php } ?>

</div><!-- close .row -->

