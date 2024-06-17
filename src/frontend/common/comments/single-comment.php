<?php
/**
 * Single comment template.
 *
 * @package AnsPress
 * @since 5.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

if ( ! isset( $args['comment'] ) ) {
	return;
}

$postComment = $args['comment'];
?>
<div class="wp-block-anspress-single-comment anspress-comments-item">
	<a class="anspress-comments-avatar" href="<?php echo esc_url( get_comment_author_url( $postComment ) ); ?>" style="height: 30px;width: 30px">
		<?php echo get_avatar( $postComment->user_id || $postComment->comment_author_email, 30 ); ?>
	</a>
	<div class="anspress-comments-inner anspress-comments-form-container">
		<div class="anspress-comments-meta">
			<a href="<?php echo esc_url( ap_user_link( $postComment->user_id ) ); ?>" class="anspress-comments-author"><?php echo esc_html( get_comment_author( $postComment ) ); ?></a>
			<?php esc_html_e( 'commented', 'anspress-question-answer' ); ?>
			<a href="<?php echo esc_url( get_comment_link( $postComment ) ); ?>" class="wp-block-anspress-single-comment-posted">
				<?php
				$posted = 'future' === get_post_status( $postComment->comment_post_ID ) ? __( 'Scheduled for', 'anspress-question-answer' ) : __( 'Published', 'anspress-question-answer' );

				$time = ap_get_time( $postComment->comment_ID, 'U' );

				if ( 'future' !== get_post_status( $postComment->comment_post_ID ) ) {
					$time = ap_human_time( $time );
				}
				?>
				<time itemprop="datePublished" datetime="<?php echo esc_attr( comment_date( 'c', $postComment ) ); ?>">
					<?php
						echo esc_attr(
							sprintf(
								/* translators: %s: human-readable time difference */
								_x( '%s ago', '%s = human-readable time difference', 'anspress-question-answer' ),
								human_time_diff(
									get_comment_time( 'U', false, true, $postComment ),
									current_datetime()->getTimestamp()
								)
							)
						);
						?>
				</time>
			</a>
		</div>
		<div class="anspress-comments-content">
			<?php echo wp_kses_post( $postComment->comment_content ); ?>
		</div>
		<div class="anspress-comments-actions">
			<?php if ( ap_user_can_delete_comment( $postComment->comment_ID ) ) : ?>
				<a data-anspressel @click.prevent="deleteComment" href="#" class="anspress-comments-delete" data-comment-id="<?php echo (int) $postComment->comment_ID; ?>">
					<?php esc_html_e( 'Delete', 'anspress-question-answer' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( ap_user_can_edit_comment( $postComment->comment_ID ) ) : ?>
				<a data-anspressel @click.prevent="editComment" href="#" class="anspress-comments-edit" data-comment-id="<?php echo (int) $postComment->comment_ID; ?>">
					<?php esc_html_e( 'Edit', 'anspress-question-answer' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
