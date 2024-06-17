<?php
/**
 * Comments template.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\GeneralException;
use AnsPress\Modules\Core\CommentService;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Tyring to cheat?' );
}

// Validate required arguments.
if ( ! isset( $args['post'] ) ) {
	throw new GeneralException( 'Post object is required.' );
}

$showingComments          = 3;
$totalComments            = get_comments_number( $args['post']->ID );
$offset                   = absint( $args['offset'] ?? 0 );
$commentsWithourContainer = $args['withoutContainer'] ?? false;

$commentQuery = new WP_Comment_Query(
	array(
		'type'    => 'anspress',
		'post_id' => $args['post']->ID,
		'status'  => 'approve',
		'orderby' => 'comment_date_gmt',
		'order'   => 'ASC',
		'number'  => $showingComments,
		'offset'  => $offset,
	)
);

$postComments = $commentQuery->get_comments();

$commentsData = Plugin::get( CommentService::class )->getCommentsData( $args['post'], $showingComments, $offset );

if ( $commentsWithourContainer ) {
	if ( $totalComments ) {
		foreach ( $postComments as $postComment ) {
			Plugin::loadView(
				'src/frontend/common/comments/single-comment.php',
				array( 'comment' => $postComment )
			);
		}
	}

	return;
}
?>
<div data-anspressel="comments" class="anspress-apq-item-comments anspress-comments" data-anspress="<?php echo esc_attr( wp_json_encode( $commentsData ) ); ?>">
	<div class="anspress-comments-line"></div>
	<div data-anspressel="comments-items" class="anspress-comments-items">
		<?php if ( $totalComments ) : ?>
			<?php foreach ( $postComments as $postComment ) : ?>
				<?php
					Plugin::loadView(
						'src/frontend/common/comments/single-comment.php',
						array( 'comment' => $postComment )
					);
				?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<div class="anspress-comments-footer anspress-comments-form-container">
		<span class="anspress-comments-count">
		<?php
		if ( $totalComments ) {
			echo wp_kses_post(
				sprintf(
				// translators: %1$d is the total number of comments.
					__( 'Total %1$s.', 'anspress-question-answer' ),
					'<span data-anspressel="comments-total-count">' . number_format_i18n( $totalComments ) . '</span>'
				)
			);
		}
		?>
		</span>
		<a data-anspressel="comments-load-more" @click.prevent="loadMoreComments" href="#" class="anspress-comments-loadmore anspress-comments-loadmore-button"><?php esc_html_e( 'Load more', 'anspress-question-answer' ); ?></a>
	</div>
	<a data-anspressel="comments-toggle-form" @click.prevent="toggleCommentForm" href="#" class="anspress-comments-add-comment-button anspress-button" data-post-id="<?php echo (int) $args['post']->ID; ?>">
		<?php esc_html_e( 'Add your comment', 'anspress-question-answer' ); ?>
	</a>
</div>
