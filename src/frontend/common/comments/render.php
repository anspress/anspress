<?php
/**
 * Comments template.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;
use AnsPress\Classes\Router;
use AnsPress\Exceptions\GeneralException;
use AnsPress\Modules\Comment\CommentService;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Tyring to cheat?' );
}

// Validate required arguments.
if ( ! isset( $post ) ) {
	throw new GeneralException( 'Post object is required.' );
}

$showingComments          = 3;
$totalComments            = get_comments_number( $post->ID );
$offset                   = absint( $args['offset'] ?? 0 );
$commentsWithourContainer = $args['withoutContainer'] ?? false;

$commentQuery = Plugin::get( CommentService::class )->getCommentsQuery(
	$post->ID,
	array(
		'number' => $showingComments,
		'offset' => $offset,
	)
);

$postComments = $commentQuery->get_comments();

$commentsData = Plugin::get( CommentService::class )->getCommentsData( $post, $showingComments, $offset );

if ( $commentsWithourContainer ) {
	if ( $totalComments ) {
		foreach ( $postComments as $postComment ) {
			Plugin::loadView(
				'src/frontend/common/comments/single-comment.php',
				array(
					'comment'    => $postComment,
					'attributes' => $attributes,
				)
			);
		}
	}

	return;
}
?>
<anspress-comment-list data-anspress-id="comment-list-<?php echo (int) $post->ID; ?>" data-post-id="<?php echo esc_attr( $post->ID ); ?>" id="anspress-comments-<?php echo esc_attr( $post->ID ); ?>" class="anspress-apq-item-comments anspress-comments" data-anspress="<?php echo esc_attr( wp_json_encode( $commentsData ) ); ?>">
	<div class="anspress-comments-count-c anspress-left-caret" data-anspress-count="<?php echo (int) $totalComments; ?>">
		<div class="anspress-comments-count">
			<?php
			echo wp_kses_post(
				sprintf(
				// translators: %1$d is the total number of comments.
					__( 'Total comments %1$s', 'anspress-question-answer' ),
					'<span data-anspressel="comments-total-count">' . number_format_i18n( $totalComments ) . '</span>'
				)
			);
			?>
		</div>
	</div>
	<div data-anspressel="comments-items" class="anspress-comments-items">
		<?php if ( $totalComments ) : ?>
			<?php foreach ( $postComments as $postComment ) : ?>
				<?php
					Plugin::loadView(
						'src/frontend/common/comments/single-comment.php',
						array(
							'comment'    => $postComment,
							'attributes' => $attributes,
						)
					);
				?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<div class="anspress-comments-footer anspress-comments-form-container">
		<?php
		$loadMoreHref = Router::route(
			'v1.posts.showComments',
			array(
				'post_id' => $post->ID,
			)
		);
		?>

		<anspress-link data-href="<?php echo esc_attr( $loadMoreHref ); ?>" data-method="GET" data-anspressel="comments-load-more" href="#" class="anspress-button anspress-comments-loadmore anspress-comments-loadmore-button"><?php esc_html_e( 'Load more', 'anspress-question-answer' ); ?></anspress-link>

		<?php
		$loadFormBtnHref = Router::route(
			'v1.posts.loadCommentForm',
			array(
				'post_id' => $post->ID,
			)
		);
		?>
		<anspress-link
			data-href="<?php echo esc_attr( $loadFormBtnHref ); ?>"
			data-method="POST"
			data-anspress="<?php echo esc_attr( wp_json_encode( array( 'form_loaded' => true ) ) ); ?>"
			class="anspress-button anspress-comments-add-button"><?php esc_html_e( 'Add comment', 'anspress-question-answer' ); ?></anspress-link>
	</div>
	<div data-anspress-id="comment:form:placeholder:<?php echo (int) $post->ID; ?>"></div>

</anspress-comment-list>
