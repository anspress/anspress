<?php
/**
 * This template is used for displaying single comment item.
 *
 * @link https://anspress.io
 * @since 2.4
 */
$class = '0' == $comment->comment_approved ? ' unapproved' : '';
?>
<div id="comment-<?php comment_ID(); ?>" <?php comment_class( 'ap-comment clearfix'. $class ); ?>>
	<div class="ap-avatar ap-pull-left">
		<?php ap_user_link_avatar( $comment->user_id, 30 ); ?>
	</div><!-- close .ap-avatar -->
	<div class="ap-comment-content no-overflow">
		<div class="ap-comment-header">
			<a href="<?php echo ap_user_link($comment->user_id ); ?>" class="ap-comment-author"><?php echo ap_user_display_name($comment->user_id ); ?></a>

			<?php
					printf(' - <a href="#li-comment-%3$s" class="ap-comment-time"><time datetime="%1$s">%2$s</time></a>',
						get_comment_time('c', true ),
						ap_human_time(get_comment_time('U', true ) ),
						get_comment_ID()
					);

					// Comment actions.
					ap_comment_actions_buttons();
			?>
		</div><!-- close .ap-comment-header -->
		<div class="ap-comment-texts">
			<?php comment_text(); ?>
		</div>
		<?php
			/**
			 * ACTION: ap_after_comment_content
			 * Action called after comment content.
			 *
			 * @since 2.0.1
			 */
			do_action('ap_after_comment_content', $comment );
		?>
		<?php if ( '0' == $comment->comment_approved ) : ?>
			<p class="comment-awaiting-moderation"><?php _e('This comment is awaiting moderation.', 'anspress-question-answer' ); ?></p>
		<?php endif; ?>
	</div><!-- close .ap-comment-content -->
</div><!-- close #comment-* -->
