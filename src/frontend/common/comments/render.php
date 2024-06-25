<?php
/**
 * Comments template.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\GeneralException;
use AnsPress\Modules\Comment\CommentService;

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
		'orderby' => 'comment_ID',
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
<anspress-comment-list data-anspress-id="comment-list-<?php echo (int) $args['post']->ID; ?>" data-post-id="<?php echo esc_attr( $args['post']->ID ); ?>" id="anspress-comments-<?php echo esc_attr( $args['post']->ID ); ?>" class="anspress-apq-item-comments anspress-comments" data-anspress="<?php echo esc_attr( wp_json_encode( $commentsData ) ); ?>">
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
						array( 'comment' => $postComment )
					);
				?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<div class="anspress-comments-footer anspress-comments-form-container">
		<a data-anspressel="comments-load-more" href="#" class="anspress-button anspress-comments-loadmore anspress-comments-loadmore-button"><?php esc_html_e( 'Load more', 'anspress-question-answer' ); ?></a>
	</div>

	<?php
	Plugin::loadView(
		'src/frontend/common/comments/comment-form.php',
		array(
			'post'        => $args['post'],
			'form_loaded' => false,
		)
	);
	?>

</anspress-comment-list>
