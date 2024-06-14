<?php
/**
 * Comments template.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\GeneralException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Tyring to cheat?' );
}

// Validate required arguments.
if ( ! isset( $args['post'] ) ) {
	throw new GeneralException( 'Post object is required.' );
}

$showingComments = 3;
$totalComments   = get_comments_number( $args['post']->ID );

$commentQuery = new WP_Comment_Query(
	array(
		'type'    => 'anspress',
		'post_id' => $args['post']->ID,
		'status'  => 'approve',
		'orderby' => 'comment_date_gmt',
		'order'   => 'DESC',
		'number'  => $showingComments,
	)
);

$postComments = $commentQuery->get_comments();

if ( empty( $postComments ) ) {
	return;
}

$commentsData = array(
	'postId'        => $args['post']->ID,
	'totalComments' => $totalComments,
	'showing'       => $showingComments,
	'canComment'    => ap_user_can_comment( $args['post']->ID ),
);
?>
<div class="wp-block-anspress-single-comments anspress-comments" data-anspress="<?php echo esc_attr( wp_json_encode( $commentsData ) ); ?>">
	<div class="anspress-comments-line"></div>
	<div class="anspress-comments-items">
		<?php foreach ( $postComments as $postComment ) : ?>
			<?php
				Plugin::loadView(
					'src/frontend/common/comments/single-comment.php',
					array( 'comment' => $postComment )
				);
			?>
		<?php endforeach; ?>
	</div>
	<div class="anspress-comments-footer anspress-comments-form-container">
		<span class="anspress-comments-count">
		<?php
		if ( ! $totalComments ) {
			esc_attr_e( 'No Comments', 'anspress-question-answer' );
		} else {
			printf(
				// translators: %1$d is the number of comments, %2$d is the total number of comments.
				esc_attr__( 'Showing %1$d of total %2$d.', 'anspress-question-answer' ),
				esc_attr( number_format_i18n( $showingComments ) ),
				esc_attr(
					number_format_i18n( $totalComments )
				)
			);
		}
		?>
		</span>
		<a href="#" class="anspress-comments-loadmore anspress-comments-loadmore-button"><?php esc_html_e( 'Load more', 'anspress-question-answer' ); ?></a>
		<?php esc_attr_e( 'or', 'anspress-question-answer' ); ?>
		<a href="#" class="anspress-comments-add anspress-comments-add-comment-button" data-post-id="<?php echo (int) $args['post']->ID; ?>">
			<?php esc_html_e( 'Add your comment', 'anspress-question-answer' ); ?>
		</a>
	</div>
</div>
