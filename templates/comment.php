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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $avatar_size;

$avatar_size = ! empty( $avatar_size ) ? $avatar_size : 30;
$comment     = get_comment(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

$approved = '1' != $comment->comment_approved ? 'unapproved' : 'approved'; // phpcs:ignore Universal.Operators.StrictComparisons.LooseNotEqual
?>
<apcomment id="comment-<?php echo (int) $comment->comment_ID; ?>" <?php comment_class( $approved ); ?>>
	<div itemprop="comment" itemscope itemtype="http://schema.org/Comment">
		<meta itemprop="@id" content="<?php the_ID(); ?>" /> <!-- This is for structured data, do not delete. -->
		<div class="ap-avatar"><?php echo get_avatar( $comment->user_id, $avatar_size ); ?></div>
		<div class="comment-inner">
			<div class="comment-header">
				<a href="<?php echo esc_url( ap_user_link( $comment->user_id ) ); ?>" class="ap-comment-author" itemprop="creator" itemscope itemtype="http://schema.org/Person">
					<span itemprop="name"><?php echo esc_html( ap_user_display_name( $comment ) ); ?></span>
				</a> <?php esc_attr_e( 'commented', 'anspress-question-answer' ); ?>

				<a href="<?php comment_link(); ?>" class="ap-comment-time">
					<time itemprop="dateCreated" datetime="<?php echo esc_attr( gmdate( 'c', strtotime( $comment->comment_date ) ) ); ?>"><?php echo esc_attr( ap_human_time( $comment->comment_date_gmt, false ) ); ?></time>
				</a>

				<div class="ap-comment-actions">
					<?php foreach ( ap_comment_actions( $comment ) as $comment_action ) : ?>
						<a href="<?php echo esc_url( $comment_action['href'] ); ?>"
							<?php echo ' class="comment-' . esc_attr( str_replace( ' ', '-', strtolower( $comment_action['label'] ) ) ) . '"'; ?>
							<?php echo ! empty( $comment_action['title'] ) ? ' title="' . esc_attr( $comment_action['title'] ) . '"' : ''; ?>
							<?php echo ! empty( $comment_action['query'] ) ? ' apajaxbtn apquery="' . esc_js( wp_json_encode( $comment_action['query'] ) ) . '"' : ''; ?>
							>
							<?php echo esc_html( $comment_action['label'] ); ?>
						</a>
					<?php endforeach; ?>

				</div>
			</div>

			<?php if ( ! is_single() ) : ?>
				<div class="ap-comment-content" itemprop="text"><?php comment_text(); ?></div>
			<?php else : ?>
				<?php
					$comment_text = wp_strip_all_tags( get_comment_text() );
					$comment_c    = strlen( $comment_text );
				?>
				<div class="ap-comment-content" itemprop="text">
					<?php comment_text(); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</apcomment>
