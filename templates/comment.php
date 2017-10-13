<?php
/**
 * This file is responsible for displaying single comment.
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @subpackage Templates
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 * @since      4.1.0
 */

$comment = get_comment();
?>
<apcomment class="<?php comment_class(); ?>">
	<div itemscope itemtype="http://schema.org/Comment">
		<div class="ap-avatar"><?php echo get_avatar( $comment->user_id, 30 ); ?></div>
		<div class="comment-inner">
			<div class="comment-header">
				<a href="<?php echo esc_url( ap_user_link( $comment->user_id ) ); ?>" class="ap-comment-author" itemprop="creator" itemscope itemtype="http://schema.org/Person">
					<span itemprop="name"><?php echo ap_user_display_name( $comment->user_id ); ?></span>
				</a> commented

				<a href="<?php comment_link(); ?>" class="ap-comment-time">
					<time itemprop="dateCreated" datetime="<?php echo date( 'c', strtotime( $comment->comment_date ) ); ?>"><?php echo ap_human_time( $comment->comment_date_gmt, false ); ?></time>
				</a>

				<div class="ap-comment-actions">
					<?php foreach ( ap_comment_actions( $comment ) as $action ) : ?>
						<a href="#" ap="<?php echo ! empty( $action['cb'] ) ? $action['cb'] : 'comment_action'; ?>"<?php echo ! empty( $action['title'] ) ? ' title="' . $action['title'] . '"' : ''; ?><?php ! empty( $action['query'] ) ? ' ap-query="' . esc_js( wp_json_encode( $action['query'] ) ) . '"' : '' ?>>
							<?php echo esc_html( $action['label'] ); ?>
						</a>
					<?php endforeach; ?>

				</div>
			</div>
			<div class="comment-content" itemprop="text"><?php comment_text(); ?></div>
		</div>
	</div>
</apcomment>
