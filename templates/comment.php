<?php
/**
 * This file is responsible for displaying single comment.
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @subpackage Templates
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <rah12@live.com>
 * @since      4.1.0
 */

global $avatar_size;

$avatar_size = ! empty( $avatar_size ) ? $avatar_size : 30;
$comment     = get_comment();

$approved = '1' != $comment->comment_approved ? 'unapproved' : 'approved';
?>
<apcomment id="comment-<?php echo $comment->comment_ID; ?>" <?php comment_class( $approved ); ?>>
	<div itemprop="comment" itemscope itemtype="http://schema.org/Comment">
		<div class="ap-avatar"><?php echo get_avatar( $comment->user_id, $avatar_size ); ?></div>
		<div class="comment-inner">
			<div class="comment-header">
				<a href="<?php echo esc_url( ap_user_link( $comment->user_id ) ); ?>" class="ap-comment-author" itemprop="creator" itemscope itemtype="http://schema.org/Person">
					<span itemprop="name"><?php echo ap_user_display_name( $comment ); ?></span>
				</a> <?php _e( 'commented', 'anspress-question-answer' ); ?>

				<a href="<?php comment_link(); ?>" class="ap-comment-time">
					<time itemprop="dateCreated" datetime="<?php echo date( 'c', strtotime( $comment->comment_date ) ); ?>"><?php echo ap_human_time( $comment->comment_date_gmt, false ); ?></time>
				</a>

				<div class="ap-comment-actions">
					<?php foreach ( ap_comment_actions( $comment ) as $action ) : ?>
						<a href="<?php echo esc_url( $action['href'] ); ?>"
							<?php echo ! empty( $action['title'] ) ? ' title="' . $action['title'] . '"' : ''; ?>
							<?php echo ! empty( $action['query'] ) ? ' apajaxbtn apquery="' . esc_js( wp_json_encode( $action['query'] ) ) . '"' : ''; ?>
							>
							<?php echo esc_html( $action['label'] ); ?>
						</a>
					<?php endforeach; ?>

				</div>
			</div>

			<?php if ( ! is_single() ) : ?>
				<div class="ap-comment-content" itemprop="text"><?php comment_text(); ?></div>
			<?php else : ?>
				<?php
					$comment_text = strip_tags( get_comment_text() );
					$comment_c    = strlen( $comment_text );
				?>
				<div class="ap-comment-content" itemprop="text">
					<?php comment_text(); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</apcomment>
