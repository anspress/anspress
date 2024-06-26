<?php
/**
 * Questions block render.
 *
 * @package AnsPress
 */

use AnsPress\Classes\Plugin;

$attributes = $attributes ?? array();

$args = array(
	'post_type'      => 'question',
	'posts_per_page' => $attributes['itemsPerPage'],
	'orderby'        => $attributes['query']['orderBy'],
	'order'          => $attributes['query']['order'],
	'author__in'     => $attributes['query']['authors'],
	's'              => $attributes['query']['search'],
);

$currentAuthorId = (int) get_query_var( 'author' );

if ( $attributes['currentAuthor'] ) {

	// Show error if author is not set.
	if ( ! is_archive() || ! is_author() || empty( $currentAuthorId ) ) {
		echo esc_html__( 'Author is not set or not author archive page.', 'anspress-question-answer' );

		return;
	}


	// Remove author__in query.
	unset( $args['author__in'] );
	$args['author'] = $currentAuthorId;
}

// Add category and tag query.
if ( ! empty( $attributes['query']['categories'] ) ) {
	$args['tax_query'][] = array(
		'taxonomy' => 'question_category',
		'field'    => 'term_id',
		'terms'    => $attributes['query']['categories'],
		'operator' => 'IN',
	);
}

if ( ! empty( $attributes['query']['tags'] ) ) {
	$args['tax_query'][] = array(
		'taxonomy' => 'question_tag',
		'field'    => 'term_id',
		'terms'    => $attributes['query']['tags'],
		'operator' => 'IN',
	);
}

$query = new WP_Query( $args );
?>

<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<?php if ( $query->have_posts() ) : ?>
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			?>
			<div id="post-<?php the_ID(); ?>" <?php post_class( 'wp-block-anspress-questions-item' ); ?>>
				<?php if ( $attributes['displayAvatar'] ) : ?>
					<div class="wp-block-anspress-questions-avatar">
						<a href="<?php ap_profile_link(); ?>">
							<?php ap_author_avatar( ap_opt( 'avatar_size_list' ) ); ?>
						</a>
					</div>
				<?php endif; ?>

				<div class="wp-block-anspress-questions-content">
					<div class="wp-block-anspress-questions-title" itemprop="name">
						<?php ap_question_status(); ?>
						<a class="wp-block-anspress-questions-link" itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php echo esc_html( get_the_title() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
					</div>
					<div class="wp-block-anspress-questions-meta">
						<?php if ( ap_is_featured_question() ) : ?>
							<div>
								<?php esc_attr_e( 'Featured', 'anspress-question-answer' ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $attributes['displaySolved'] && ap_have_answer_selected() ) : ?>
							<div>
								<?php esc_attr_e( 'Solved', 'anspress-question-answer' ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $attributes['displayViews'] ) : ?>
							<div>
								<?php
									$view_count = ap_get_post_field( 'views' );

									printf(
										// translators: %s is views count i.e. 2.1k views.
										esc_attr__( '%s views', 'anspress-question-answer' ),
										esc_attr( ap_short_num( $view_count ) )
									);
								?>
							</div>
						<?php endif; ?>

						<?php if ( $attributes['displayActivity'] ) : ?>
							<?php echo wp_kses_post( ap_recent_activity() ); ?>
						<?php endif; ?>

						<?php if ( $attributes['displayCategories'] && ap_post_have_terms( get_the_ID(), 'question_category' ) ) : ?>
							<div>
								<?php
								$tags = get_the_terms( get_the_ID(), 'question_category' );

								if ( $tags && count( $tags ) > 0 ) :
									?>
									<?php esc_attr_e( 'Categories:', 'anspress-question-answer' ); ?>
									<?php $i = 1; ?>
									<?php foreach ( $tags as $t ) : ?>
										<a href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a>
										<?php ++$i; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $attributes['displayTags'] && ap_post_have_terms( get_the_ID(), 'question_tag' ) ) : ?>
							<div>
								<?php
								$tags = get_the_terms( get_the_ID(), 'question_tag' );

								if ( $tags && count( $tags ) > 0 ) :
									?>
									<?php esc_attr_e( 'Tags:', 'anspress-question-answer' ); ?>
									<?php $i = 1; ?>
									<?php foreach ( $tags as $t ) : ?>
										<a href="<?php echo esc_url( get_term_link( $t ) ); ?>"><?php echo esc_html( $t->name ); ?></a>
										<?php ++$i; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php
						/**
						 * Used to filter question display meta.
						 *
						 * @since 5.0.0
						 */
						$metas = do_action( 'anspress/questions/meta' );
						?>
					</div>
				</div>

				<?php if ( $attributes['displayVoteCount'] && $attributes['displayAnsCount'] ) : ?>
					<div class="wp-block-anspress-questions-counts">
						<?php if ( $attributes['displayVoteCount'] ) : ?>
							<div class="wp-block-anspress-questions-count wp-block-anspress-questions-vcount">
								<div itemprop="upvoteCount"><?php ap_votes_net(); ?></div>
								<?php esc_attr_e( 'Votes', 'anspress-question-answer' ); ?>
							</div>
						<?php endif; ?>

						<?php if ( $attributes['displayAnsCount'] ) : ?>
							<a class="wp-block-anspress-questions-count wp-block-anspress-questions-acount" href="<?php echo esc_url( ap_answers_link() ); ?>">
								<div itemprop="answerCount"><?php ap_answers_count(); ?></div>
								<?php esc_attr_e( 'Ans', 'anspress-question-answer' ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endwhile; ?>

		<?php if ( $attributes['displayPagination'] ) : ?>

			<?php
				$totalPages = $query->max_num_pages;
				include Plugin::getPathTo( 'src/frontend/common/pagination.php' );
			?>

		<?php endif; ?>

		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<div><?php esc_attr_e( 'No questions found.', 'anspress-question-answer' ); ?></div>
	<?php endif; ?>
</div>
