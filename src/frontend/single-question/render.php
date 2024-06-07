<?php
/**
 * Render dynamic profile nav block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

$_post = ap_get_post( get_post() );
$vote  = ap_get_vote( $_post->ID, get_current_user_id(), 'vote' );

$voteData = array(
	'votesUp'   => $_post->votes_up,
	'votesDown' => $_post->votes_down,
	'voted'     => $vote ? ( '-1' === $vote->vote_value ? 'vote_down' : 'vote_up' ) : null,
);

?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> data-gutenberg-attributes="<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>" data-post-id="<?php the_ID(); ?>">
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

	<div class="wp-block-anspress-single-question-votes" data-post-id="<?php the_ID(); ?>" data-vote-data="<?php echo esc_attr( wp_json_encode( $voteData ) ); ?>">
		<button class="apicon-thumb-up wp-block-anspress-single-question-vote-up" href="#" title="Up vote this question"></button>
		<span class="wp-block-anspress-single-question-count">
			<?php echo (int) $_post->votes_net; ?>
		</span>
		<button data-tipposition="bottom center" class="apicon-thumb-down wp-block-anspress-single-question-vote-down" href="#" title="Down vote this question"></button>
	</div>
</div>
