<?php
/**
 * Template used for generating single answer item.
 *
 * @author Rahul Aryan <rah12@live.com>
 * @link https://anspress.net/anspress
 * @package AnsPress
 * @subpackage Templates
 * @since 0.1
 * @since 4.1.2 Removed @see ap_recent_post_activity().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ap_user_can_read_answer() ) :

	?>

<div id="post-<?php the_ID(); ?>" class="answer<?php echo ap_is_selected() ? ' best-answer' : ''; ?>" apid="<?php the_ID(); ?>" ap="answer">
	<div class="ap-content" itemprop="suggestedAnswer<?php echo ap_is_selected() ? ' acceptedAnswer' : ''; ?>" itemscope itemtype="https://schema.org/Answer">
		<div class="ap-single-vote"><?php ap_vote_btn(); ?></div>
		<div class="ap-avatar">
			<a href="<?php ap_profile_link(); ?>">
				<?php ap_author_avatar( ap_opt( 'avatar_size_qanswer' ) ); ?>
			</a>
		</div>
		<div class="ap-cell clearfix">
			<meta itemprop="@id" content="<?php the_ID(); ?>" /> <!-- This is for structured data, do not delete. -->
			<meta itemprop="url" content="<?php the_permalink(); ?>" /> <!-- This is for structured data, do not delete. -->
			<div class="ap-cell-inner">
				<div class="ap-q-metas">
					<?php echo wp_kses_post( ap_user_display_name( array( 'html' => true ) ) ); ?>
					<a href="<?php the_permalink(); ?>" class="ap-posted">
						<time itemprop="datePublished" datetime="<?php echo esc_attr( ap_get_time( get_the_ID(), 'c' ) ); ?>">
							<?php
							echo esc_attr(
								sprintf(
									// translators: %s is time.
									__( 'Posted %s', 'anspress-question-answer' ),
									ap_human_time( ap_get_time( get_the_ID(), 'U' ) )
								)
							);
							?>
						</time>
					</a>
					<span class="ap-comments-count">
						<?php $comment_count = get_comments_number(); ?>
						<span itemprop="commentCount"><?php echo (int) $comment_count; ?></span>
						<?php
							echo esc_attr( sprintf( _n( 'Comment', 'Comments', $comment_count, 'anspress-question-answer' ) ) );
						?>
					</span>
				</div>

				<div class="ap-q-inner">
					<?php
					/**
					 * Action triggered before answer content.
					 *
					 * @since   3.0.0
					 */
					do_action( 'ap_before_answer_content' );
					?>

					<div class="ap-answer-content ap-q-content" itemprop="text" ap-content>
						<?php the_content(); ?>
					</div>

					<?php
					/**
					 * Action triggered after answer content.
					 *
					 * @since   3.0.0
					 */
					do_action( 'ap_after_answer_content' );
					?>

				</div>

				<div class="ap-post-footer clearfix">
					<?php echo ap_select_answer_btn_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php ap_post_actions_buttons(); ?>
					<?php do_action( 'ap_answer_footer' ); ?>
				</div>

			</div>
			<?php ap_post_comments(); ?>
		</div>

	</div>
</div>

	<?php
endif;
