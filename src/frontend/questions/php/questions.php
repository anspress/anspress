<?php
/**
 * Questions block render.
 *
 * @package AnsPress
 */

namespace AnsPress\Blocks\Questions;

use AnsPress\Classes\Plugin;
use AnsPress\Classes\PostHelper;
use AnsPress\Classes\TemplateHelper;
use WP_Query;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

$attributes         = $attributes ?? array();
$showQueryModifiers = $attributes['displayQueryModifiers'] ?? true;
$currentTerm        = $attributes['currentTerm'] ?? false;
$currentTermObject  = null;

// Check if terms archive page.
if ( $currentTerm && is_archive() && is_tax( array( 'question_category', 'question_tag' ) ) ) {
	$currentTermObject = get_queried_object();
}

$currentQueriesArgs = $showQueryModifiers ? TemplateHelper::currentQuestionsQueryArgs() : array();

$args = array(
	'post_type'      => 'question',
	'posts_per_page' => $attributes['itemsPerPage'],
	'orderby'        => $attributes['query']['orderBy'],
	'order'          => $attributes['query']['order'],
	'author__in'     => $attributes['query']['authors'],
	's'              => $attributes['query']['search'],
	'paged'          => max( 1, get_query_var( 'ap_question_paged' ) ),
	'ap_query'       => true,
	'post_status'    => 'publish',
);

if ( $showQueryModifiers ) {
	if ( ! empty( $currentQueriesArgs['keywords'] ) ) {
		$args['s'] = $currentQueriesArgs['keywords'];
	}

	if ( ! empty( $currentQueriesArgs['args:filter'] ) ) {
		$filter = $currentQueriesArgs['args:filter'][0];

		$args['ap_filter'] = $filter;

		if ( 'moderate' === $filter && current_user_can( 'ap_view_moderate' ) ) {
			$args['post_status'] = 'moderate';
		}

		if ( 'private_post' === $filter && current_user_can( 'ap_view_private' ) ) {
			$args['post_status'] = 'private_post';
		}
	}

	if ( ! empty( $currentQueriesArgs['args:orderby'] ) ) {
		$args['ap_order_by'] = $currentQueriesArgs['args:orderby'][0];
	}

	if ( ! empty( $currentQueriesArgs['args:order'] ) ) {
		$args['order'] = $currentQueriesArgs['args:order'][0];
	}

	// Add categories if present in current args.
	$categories = $currentQueriesArgs['args:categories'] ?? array();
	if ( ! empty( $categories ) ) {
		$attributes['query']['categories'] = array_merge( $attributes['query']['categories'] ?? array(), $categories );
	}

	// Add tags if present in current args.
	$tags = $currentQueriesArgs['args:tags'] ?? array();
	if ( ! empty( $tags ) ) {
		$attributes['query']['tags'] = array_merge( $attributes['query']['tags'] ?? array(), $tags );
	}
}

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

// When current term is set and is taxonomy archive page always show current term.
if ( $currentTermObject ) {
	$termId = $currentTermObject->term_id;

	if ( 'question_category' === $currentTermObject->taxonomy ) {
		$attributes['query']['categories']     = array( $termId );
		$attributes['displayCategoriesFilter'] = false;
	} elseif ( 'question_tag' === $currentTermObject->taxonomy ) {
		$attributes['query']['tags']     = array( $termId );
		$attributes['displayTagsFilter'] = false;
	}
}

// Add category and tag query.
if ( ! empty( $attributes['query']['categories'] ) ) {
	$categoryIds = array_filter( array_map( 'intval', $attributes['query']['categories'] ) );

	if ( ! empty( $categoryIds ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'question_category',
			'field'    => 'term_id',
			'terms'    => $categoryIds,
			'operator' => 'IN',
		);
	}
}

if ( ! empty( $attributes['query']['tags'] ) ) {
	$tagIds = array_filter( array_map( 'intval', $attributes['query']['tags'] ) );

	if ( ! empty( $tagIds ) ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'question_tag',
			'field'    => 'term_id',
			'terms'    => $tagIds,
			'operator' => 'IN',
		);
	}
}

$query = new WP_Query( $args );
?>

<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<?php
	if ( $attributes['displayQueryModifiers'] ?? true ) {
		Plugin::loadView(
			'src/frontend/questions/php/filters.php',
			array(
				'attributes'         => $attributes,
				'currentQueriesArgs' => $currentQueriesArgs,
			)
		);
	}
	?>
	<?php if ( $query->have_posts() ) : ?>
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();

			$postStatus = get_post_status();
			$tags       = $attributes['displayTags'] ? get_the_terms( get_the_ID(), 'question_tag' ) : array();
			$categories = $attributes['displayCategories'] ? get_the_terms( get_the_ID(), 'question_category' ) : array();

			$postClasses = 'wp-block-anspress-questions-item anspress-post-status-' . $postStatus;

			if ( is_post_closed( get_the_ID() ) ) {
				$postClasses .= ' anspress-post-status-closed';
			}

			if ( ap_is_featured_question() ) {
				$postClasses .= ' anspress-post-status-featured';
			}

			if ( ap_have_answer_selected() ) {
				$postClasses .= ' anspress-post-status-selected-answer';
			}
			?>
			<div id="post-<?php the_ID(); ?>" <?php post_class( $postClasses ); ?>>
				<div class="wp-block-anspress-questions-item-head">
					<?php if ( $attributes['displayAvatar'] ) : ?>
						<div class="wp-block-anspress-questions-avatar anspress-avatar-shape-<?php echo esc_attr( $attributes['avatarShape'] ); ?>">
							<a href="<?php ap_profile_link(); ?>">
								<?php ap_author_avatar( $attributes['avatarSize'] ); ?>
							</a>
						</div>
					<?php endif; ?>

					<div class="wp-block-anspress-questions-content">
						<div class="wp-block-anspress-questions-title" itemprop="name">
							<a class="wp-block-anspress-questions-link" itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php echo esc_html( get_the_title() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
						</div>
						<div class="wp-block-anspress-questions-meta">
							<?php if ( $attributes['displaySolved'] && ap_have_answer_selected() ) : ?>
								<div class="anspress-badge anspress-badge-selected-answer">
									<?php esc_attr_e( 'Solved', 'anspress-question-answer' ); ?>
								</div>
							<?php endif; ?>

							<?php if ( ap_is_featured_question() ) : ?>
								<div class="anspress-badge anspress-badge-featured">
									<?php esc_attr_e( 'Featured', 'anspress-question-answer' ); ?>
								</div>
							<?php endif; ?>

							<?php if ( 'publish' !== $postStatus ) : ?>
								<div class="anspress-badge anspress-badge-<?php echo esc_attr( $postStatus ); ?>">
									<?php echo esc_attr( PostHelper::postStatusLabel( get_post() ) ); ?>
								</div>
							<?php endif; ?>

							<?php if ( is_post_closed( get_the_ID() ) ) : ?>
								<div class="anspress-badge anspress-badge-closed">
									<?php esc_attr_e( 'Closed', 'anspress-question-answer' ); ?>
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

				<?php if ( $tags || $categories ) : ?>
					<div class="wp-block-anspress-questions-item-footer">
						<?php if ( $tags ) : ?>
							<?php TemplateHelper::displayQuestionTerms( $tags, __( 'Tags: ', 'anspress-question-answer' ) ); ?>
						<?php endif; ?>

						<?php if ( $categories ) : ?>
							<?php TemplateHelper::displayQuestionTerms( $categories, __( 'Categories: ', 'anspress-question-answer' ) ); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endwhile; ?>

		<?php if ( $attributes['displayPagination'] ) : ?>
			<?php
				$totalPages = $query->max_num_pages;
				Plugin::loadView(
					'src/frontend/common/pagination.php',
					array(
						'totalPages' => $totalPages,
						'attributes' => $attributes,
					)
				);
			?>
		<?php endif; ?>

		<?php wp_reset_postdata(); ?>
	<?php else : ?>
		<div><?php esc_attr_e( 'No questions were found. Please clear any active filters.', 'anspress-question-answer' ); ?></div>
	<?php endif; ?>
</div>
