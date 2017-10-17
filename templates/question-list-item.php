<?php
/**
 * Template for question list item.
 *
 * @link       https://anspress.io
 *
 * @since      0.1
 * @since      4.1.0 Replaced functions by @see ap_question().
 *
 * @author     Rahul Aryan<support@anspress.io>
 * @copyright  Copyright (c) 2017, Rahul Aryan
 * @package    AnsPress
 */

if ( ! ap_user_can_view_post( get_the_ID() ) ) {
	return;
}

$clearfix_class = array( 'ap-questions-item clearfix' );
?>
<div id="question-<?php ap_question()->the_ID(); ?>" <?php post_class( $clearfix_class ); ?>>
	<div class="ap-questions-inner">
		<div class="ap-avatar ap-pull-left">
			<a href="<?php ap_question()->the_author_link(); ?>">
				<?php ap_question()->the_author_avatar( ap_opt( 'avatar_size_list' ) ); ?>
			</a>
		</div>
		<div class="ap-list-counts">
			<!-- Votes count -->
			<?php if ( ! ap_opt( 'disable_voting_on_question' ) ) : ?>
				<span class="ap-questions-count ap-questions-vcount">
					<span><?php ap_question()->the_votes_net(); ?></span>
					<?php _e( 'Votes', 'anspress-question-answer' ); ?>
				</span>
			<?php endif; ?>

			<!-- Answer Count -->
			<a class="ap-questions-count ap-questions-acount" href="<?php echo ap_answers_link(); ?>">
				<span><?php ap_question()->the_answers_count(); ?></span>
				<?php _e( 'Ans', 'anspress-question-answer' ); ?>
			</a>
		</div>

		<div class="ap-questions-summery">
			<div class="ap-questions-title" itemprop="title">
				<?php
				// Do not show post status if published.
				if ( 'publish' !== ap_question()->post_status ) : ?>
					<span class="ap-post-status <?php echo esc_attr( ap_question()->post_status ); ?>"><?php echo esc_attr( ap_question()->get_status_object( 'label' ) ); ?></span>
				<?php endif; ?>

				<a class="ap-questions-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
			</div>

			<!-- Start .ap-display-question-meta -->
			<div class="ap-display-question-meta">
				<?php foreach ( ap_question()->get_display_meta() as $metak => $dmeta ) : ?>
					<div class="ap-display-meta-item <?php echo esc_attr( $metak ); ?>">
						<?php if( empty( $dmeta['html'] ) ) : ?>
							<?php echo ! empty( $dmeta['icon'] ) ? '<i class="' . esc_attr( $dmeta['icon'] ). '"></i> ' : ''; ?>
							<?php echo ! empty( $dmeta['text'] ) ? $dmeta['text'] : ''; ?>
						<?php else : ?>
							<?php echo wp_kses_post( $dmeta['html'] ); ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<!-- End .ap-display-question-meta -->

			<!-- Start .ap-taxonomies -->
			<?php if ( $taxos = ap_question()->get_all_terms() ) : ?>
				<div class="ap-taxonomies">
					<?php foreach ( $taxos as $taxo => $terms ) : ?>
						<?php if ( $terms ) : ?>
							<div class="ap-terms <?php echo esc_attr( $taxo ); ?>">
								<i class="apicon-<?php echo 'question_category' === $taxo ? 'category' : 'tag'; ?>"></i>
								<?php foreach ( $terms as $term ) : ?>
									<a href="<?php esc_url( get_term_link( $term ) ); ?>" title="<?php echo esc_attr( $term->description ); ?>"><?php echo esc_html( $term->name ); ?></a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<!-- End .ap-taxonomies -->

		</div>
	</div>
</div><!-- list item -->
