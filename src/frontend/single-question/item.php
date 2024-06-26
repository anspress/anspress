<?php
/**
 * Answers content.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\GeneralException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

$_post = get_post();

$isQuestion = 'question' === $_post->post_type;

$selectedAnswer = ! $isQuestion ? ap_is_selected( $_post->ID ) : false;

$classes = array( 'anspress-apq-item' );

if ( $selectedAnswer ) {
	$classes[] = 'anspress-apq-item-selected';
}

?>
<anspress-item data-post-id="<?php echo (int) $_post->ID; ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-anspress-id="answer:<?php echo (int) $_post->ID; ?>">
	<div class="anspress-apq-item-avatar">
		<div class="anspress-avatar-link">
			<a href="<?php ap_profile_link(); ?>"><?php ap_author_avatar( ap_opt( 'avatar_size_qquestion' ) ); ?></a>
		</div>
	</div>
	<div class="anspress-apq-item-content">
		<div class="anspress-apq-item-qbody anspress-card">
			<div class="anspress-apq-item-metas">
				<div class="anspress-apq-item-author">
					<?php
					ap_user_display_name(
						array(
							'html' => true,
							'echo' => true,
						)
					);
					?>
				</div>
				<a href="<?php the_permalink(); ?>" class="anspress-apq-item-posted">
					<?php
					$posted = 'future' === get_post_status() ? __( 'Scheduled for', 'anspress-question-answer' ) : __( 'Published', 'anspress-question-answer' );

					$time = ap_get_time( get_the_ID(), 'U' );

					if ( 'future' !== get_post_status() ) {
						$time = ap_human_time( $time );
					}
					?>
					<time itemprop="datePublished" datetime="<?php echo esc_attr( ap_get_time( get_the_ID(), 'c' ) ); ?>"><?php echo esc_attr( $time ); ?></time>
				</a>
				<span class="anspress-apq-item-ccount">
					<?php $comment_count = get_comments_number(); ?>
					<?php
						// translators: %s comments count.
						echo wp_kses_post( sprintf( _n( '%s Comment', '%s Comments', $comment_count, 'anspress-question-answer' ), '<span itemprop="commentCount">' . (int) $comment_count . '</span>' ) );
					?>

				</span>
				<?php if ( ap_selected_answer( $_post->ID ) ) : ?>
					<div class="anspress-apq-item-selected-answer">
						<span ><?php esc_html_e( 'Solved', 'anspress-question-answer' ); ?></span>
					</div>
				<?php endif; ?>
			</div>
			<div class="anspress-apq-item-inner">
				<?php
					/**
					 * Action triggered before question content.
					 *
					 * @since   5.0.0
					 */
					do_action( 'anspress/single_question/before_content' );
				?>

				<div class="question-content" itemprop="text">
					<?php the_content(); ?>
				</div>

				<?php
					/**
					 * Action triggered after question content.
					 *
					 * @since   5.0.0
					 */
					do_action( 'anspress/single_question/after_content' );
				?>

			</div>

			<div class="anspress-apq-item-footer">
				<?php
					Plugin::loadView(
						'src/frontend/single-question/vote-button.php',
						array( 'ID' => $_post->ID )
					);
					?>
				<?php if ( ! $isQuestion ) : ?>
					<?php
						Plugin::loadView(
							'src/frontend/single-question/button-select.php',
							array( 'post' => $_post )
						);
					?>
				<?php endif; ?>

				<?php do_action( 'ap_post_footer' ); ?>

				<div class="anspress-apq-item-actions">
					<?php
						Plugin::loadView(
							'src/frontend/single-question/button-delete.php',
							array( 'post' => $_post )
						);
						?>
					<a href="<?php the_permalink(); ?>" class="anspress-apq-item-action anspress-apq-item-action-view"><?php esc_html_e( 'Edit', 'anspress-question-answer' ); ?></a>
					<a href="<?php the_permalink(); ?>" class="anspress-apq-item-action anspress-apq-item-action-view"><?php esc_html_e( 'Share', 'anspress-question-answer' ); ?></a>
					<a href="<?php the_permalink(); ?>" class="anspress-apq-item-action anspress-apq-item-action-view"><?php esc_html_e( 'Report', 'anspress-question-answer' ); ?></a>
				</div>
			</div>
		</div>

		<?php
			Plugin::loadView( 'src/frontend/common/comments/render.php', array( 'post' => $_post ) );
		?>
	</div>
</anspress-item>
