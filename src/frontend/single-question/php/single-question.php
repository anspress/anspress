<?php
/**
 * Render dynamic profile nav block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

use AnsPress\Classes\Auth;
use AnsPress\Classes\Plugin;
use AnsPress\Classes\Router;
use AnsPress\Modules\Answer\AnswerService;
use AnsPress\Modules\Vote\VoteService;
use AnsPress\Classes\TemplateHelper;

$_post = ap_get_post( get_the_ID() );

$voteData = Plugin::get( VoteService::class )->getPostVoteData( get_the_ID() );

if ( ! Auth::currentUserCan( 'question:view', array( 'question' => $_post ) ) ) {
	?>
		<div class="anspress-apq-item-answer-disabled anspress-card">
			<?php esc_html_e( 'You are not allowed to view this question.', 'anspress-question-answer' ); ?>
		</div>
	<?php
	return;
}

?>
<style>
	body{
		--anspress-single-question-avatar-size: <?php echo (int) $attributes['avatarSize']; ?>px;
		--anspress-single-question-comment-avatar-size: <?php echo (int) $attributes['commentAvatarSize']; ?>px;
		--anspress-single-question-line-left: <?php echo (int) $attributes['avatarSize'] + ( (int) $attributes['commentAvatarSize'] / 2 ); ?>px;
	}
</style>
<div
	<?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>
	data-gutenberg-attributes="<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>"
	data-post-id="<?php the_ID(); ?>"
	data-anspress-template="<?php echo esc_attr( TemplateHelper::currentTemplateId() ); ?>"
	data-anspress-block="anspress/single-question"
	>
	<?php

	$questionCategories = get_the_terms( $_post->ID, 'question_category' );

	if ( $questionCategories && ! is_wp_error( $questionCategories ) ) {
		echo '<div class="anspress-apq-item-categories anspress-apq-item-categories">';
		echo '<span class="anspress-apq-item-cat-label">' . esc_html__( 'Categories / ', 'anspress-question-answer' ) . '</span>';

		foreach ( $questionCategories as $category ) {
			echo '<a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
		}

		echo '</div>';
	}
	?>

	<?php
	/**
	 * Action triggered after question title.
	 *
	 * @since 5.0.0
	 */
	do_action( 'anspress/after/question_title' );
	?>

	<div class="anspress-apq-item-terms">
		<?php
		$questionTags = get_the_terms( $_post->ID, 'question_tag' );

		if ( $questionTags && ! is_wp_error( $questionTags ) ) {
			echo '<div class="anspress-apq-item-tags anspress-apq-item-terms">';
			foreach ( $questionTags as $qtag ) {
				echo '<a href="' . esc_url( get_term_link( $qtag ) ) . '">' . esc_html( $qtag->name ) . '</a>';
			}
			echo '</div>';
		}
		?>
	</div>

	<?php
		Plugin::loadView(
			'src/frontend/single-question/php/button-subscribe.php',
			array(
				'post'       => $_post,
				'attributes' => $attributes,
			)
		);
		?>

	<div class="anspress-apq-item-c">
		<?php
		/**
		 * Action triggered before question content.
		 *
		 * @since 5.0.0
		 */
		do_action( 'anspress/before/question_content' );
		?>

		<?php
			Plugin::loadView(
				'src/frontend/single-question/php/item.php',
				array(
					'post'       => $_post,
					'attributes' => $attributes,
				)
			);

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

		<?php
		/**
		 * Action triggered before answers list.
		 *
		 * @since 5.0.0
		 */
		do_action( 'anspress/before/answers_list' )
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
					'src/frontend/single-question/php/answers.php',
					array(
						'question'     => $_post,
						'query'        => $query,
						'answers_args' => $answersArgs,
						'attributes'   => $attributes,
					)
				);
				?>
			</div>
			<?php if ( $answersArgs['have_pages'] ) : ?>
				<?php
					$loadMoreHref = Router::route(
						'v1.questions.answers',
						array(
							'question_id' => $_post->ID,
						)
					);

					$loadMoreData = wp_json_encode( array( 'page' => $currentPage + 1 ) );
				?>
				<anspress-link
					data-href="<?php echo esc_attr( $loadMoreHref ); ?>"
					data-method="GET"
					data-anspressel="load-more-answers"
					data-anspress-id="button:answers:loadmore:<?php echo (int) $_post->ID; ?>"
					data-anspress="<?php echo esc_attr( $loadMoreData ); ?>"
					class="anspress-load-more anspress-load-more-answers anspress-button anspress-btn-primary anspress-btn-sm"
					><?php esc_html_e( 'Load more answers', 'anspress-question-answer' ); ?> <span data-anspress-id="answers-count-<?php echo (int) $_post->ID; ?>" class="anspress-load-more-count"><?php echo esc_attr( number_format_i18n( $answersArgs['remaining_items'] ) ); ?></span></anspress-link>
			<?php endif; ?>
		</anspress-answer-list>
	</div>
	<?php
	if ( Auth::currentUserCan( 'answer:create', array( 'question' => $_post ) ) ) {
		Plugin::loadView(
			'src/frontend/single-question/php/answer-form.php',
			array(
				'question'   => get_post(),
				'attributes' => $attributes,
			)
		);
	} else {
		?>
			<div class="anspress-apq-item-answer-disabled anspress-card">
			<?php esc_html_e( 'This question is not accepting new answers or you are not allowed to submit an answer.', 'anspress-question-answer' ); ?>
			</div>
			<?php
	}
	/**
	 * Action triggered after question content.
	 *
	 * @since 5.0.0
	 */
	do_action( 'anspress/after/question_content' );
	?>
</div>
