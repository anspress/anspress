<?php
/**
 * Comments template.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Exceptions\GeneralException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Tyring to cheat?' );
}

// Validate required arguments.
if ( ! isset( $args['post'] ) ) {
	throw new GeneralException( 'Post object is required.' );
}

$commentQuery = new WP_Comment_Query(
	array(
		'type'    => 'anspress',
		'post_id' => $args['post']->ID,
		'status'  => 'approve',
		'orderby' => 'comment_date_gmt',
		'order'   => 'ASC',
	)
);

$postComments = $commentQuery->get_comments();

if ( empty( $postComments ) ) {
	return;
}
?>
<div class="wp-block-anspress-single-comments anspress-comments">
	<?php foreach ( $postComments as $postComment ) : ?>
		<div class="wp-block-anspress-single-comment anspress-comments-item">
			<a class="anspress-comments-avatar" href="<?php echo esc_url( get_comment_author_url( $postComment ) ); ?>" style="height: 30px;width: 30px">
				<?php echo get_avatar( $postComment, 30 ); ?>
			</a>
			<div class="anspress-comments-content">
				<div class="anspress-comments-meta">
					<a href="<?php echo esc_url( ap_user_link( $postComment->user_id ) ); ?>" class="anspress-comments-author">
						<?php echo esc_html( get_comment_author( $postComment ) ); ?>
					</a>
					<?php esc_html_e( 'commented', 'anspress-question-answer' ); ?>
					<a href="<?php echo esc_url( get_comment_link( $postComment ) ); ?>" class="wp-block-anspress-single-comment-posted">
						<?php
						$posted = 'future' === get_post_status( $postComment->comment_post_ID ) ? __( 'Scheduled for', 'anspress-question-answer' ) : __( 'Published', 'anspress-question-answer' );

						$time = ap_get_time( $postComment->comment_ID, 'U' );

						if ( 'future' !== get_post_status( $postComment->comment_post_ID ) ) {
							$time = ap_human_time( $time );
						}
						?>
						<time itemprop="datePublished" datetime="<?php echo esc_attr( ap_get_time( $postComment->comment_ID, 'c' ) ); ?>"><?php echo esc_attr( $time ); ?></time>
					</a>
				</div>
				<?php echo wp_kses_post( $postComment->comment_content ); ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
