<?php
/**
 * Render dynamic profile nav block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>>
	<div class="wp-block-anspress-single-question-avatar">
		<a href="<?php ap_profile_link(); ?>">
			<?php ap_author_avatar( ap_opt( 'avatar_size_qquestion' ) ); ?>
		</a>
	</div>
	<div class="wp-block-anspress-single-question-content">
		<div class="wp-block-anspress-single-question-metas">
			<div class="wp-block-anspress-single-question-author">
				<?php
					ap_user_display_name(
						array(
							'html' => true,
							'echo' => true,
						)
					);
					?>
			</div>
			<a href="<?php the_permalink(); ?>" class="wp-block-anspress-single-question-posted">
				<?php
				$posted = 'future' === get_post_status() ? __( 'Scheduled for', 'anspress-question-answer' ) : __( 'Published', 'anspress-question-answer' );

				$time = ap_get_time( get_the_ID(), 'U' );

				if ( 'future' !== get_post_status() ) {
					$time = ap_human_time( $time );
				}
				?>
				<time itemprop="datePublished" datetime="<?php echo esc_attr( ap_get_time( get_the_ID(), 'c' ) ); ?>"><?php echo esc_attr( $time ); ?></time>
			</a>
			<span class="wp-block-anspress-single-question-ccount">
				<?php $comment_count = get_comments_number(); ?>
				<?php
					// translators: %s comments count.
					echo wp_kses_post( sprintf( _n( '%s Comment', '%s Comments', $comment_count, 'anspress-question-answer' ), '<span itemprop="commentCount">' . (int) $comment_count . '</span>' ) );
				?>
			</span>
		</div>
		<div class="wp-block-anspress-single-question-inner">
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

		<div class="ap-post-footer clearfix">
			<?php do_action( 'ap_post_footer' ); ?>
		</div>
	</div>
	<div class="wp-block-anspress-single-question-votes">
		<a class="apicon-thumb-up wp-block-anspress-single-question-vote-up" href="#" title="Up vote this question"></a>
		<span class="wp-block-anspress-single-question-count">0</span>
		<a data-tipposition="bottom center" class="apicon-thumb-down wp-block-anspress-single-question-vote-down" href="#" title="Down vote this question">

		</a>
	</div>
</div>
