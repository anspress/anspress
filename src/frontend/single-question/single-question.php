<?php
/**
 * Render dynamic profile nav block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

use AnsPress\Classes\Plugin;
use AnsPress\Modules\Answer\AnswerService;
use AnsPress\Modules\Vote\VoteService;

$_post = ap_get_post( get_the_ID() );

$voteData = Plugin::get( VoteService::class )->getPostVoteData( get_the_ID() );


?>
<style>
	body{
		--anspress-single-question-avatar-size: <?php echo (int) ap_opt( 'avatar_size_qquestion' ); ?>px;
	}
</style>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> data-gutenberg-attributes="<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>" data-post-id="<?php the_ID(); ?>">

	<h1 class="anspress-apq-item-title"><?php the_title(); ?></h1>

	<div class="anspress-apq-item-c">

		<?php
			Plugin::loadView( 'src/frontend/single-question/item.php' );

			$currentPage = 1;

			// Answers.
			$query = Plugin::get( AnswerService::class )->getAnswersQuery(
				array(
					'post_parent' => $_post->ID,
					'paged'       => $currentPage,
					'answer_id'   => get_query_var( 'answer_id' ),
				)
			);

			$answersArgs = Plugin::get( AnswerService::class )->getAnswersData(
				$query,
				$_post,
				$currentPage
			);

			$answersClass = 'anspress-answers';

			if ( ap_selected_answer( $_post->ID ) ) {
				$answersClass .= ' anspress-answers-selected';
			}
			?>
			<anspress-answer-list data-anspress-id="answers-<?php echo (int) $_post->ID; ?>" class="<?php echo esc_attr( $answersClass ); ?>" data-anspress="<?php echo esc_attr( wp_json_encode( $answersArgs ) ); ?>">
				<?php if ( get_query_var( 'answer_id' ) ) : ?>
					<div class="anspress-apq-item-single-answer-info anspress-card">
						<?php esc_html_e( 'You are viewing one of many answers to this question. Click the button to show all answers.', 'anspress-question-answer' ); ?>
						<a href="<?php the_permalink(); ?>" class="anspress-button anspress-btn-primary anspress-btn-sm"><?php esc_html_e( 'Back to all answers', 'anspress-question-answer' ); ?></a>
					</div>
				<?php endif; ?>

				<div data-anspressel="answers-items" class="anspress-answers-items">
					<?php
					Plugin::loadView(
						'src/frontend/single-question/answers.php',
						array(
							'question'     => $_post,
							'query'        => $query,
							'answers_args' => $answersArgs,
						)
					);
					?>
				</div>
				<?php if ( $answersArgs['have_pages'] ) : ?>
					<button data-anspressel="load-more-answers" class="anspress-load-more anspress-load-more-answers anspress-button anspress-btn-primary anspress-btn-sm"
						><?php esc_html_e( 'Load more answers', 'anspress-question-answer' ); ?> <span data-anspress-id="answers-count-<?php echo (int) $_post->ID; ?>" class="anspress-load-more-count"><?php echo esc_attr( number_format_i18n( $answersArgs['remaining_items'] ) ); ?></span></button>
				<?php endif; ?>
			</anspress-answer-list>

		<?php Plugin::loadView( 'src/frontend/single-question/answer-form.php', array( 'question' => get_post() ) ); ?>
	</div>
</div>
