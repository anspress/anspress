<?php
/**
 * This template is used for displaying single comment item.
 *
 * @link http://anspress.io
 * @since 2.4
 */
?>

<li <?php comment_class('clearfix'.$class); ?> id="li-comment-<?php comment_ID(); ?>">
	<!-- comment #<?php comment_ID(); ?> -->
	<div id="comment-<?php comment_ID(); ?>" class="clearfix">
		<div class="ap-avatar ap-pull-left">
			<a href="<?php echo ap_user_link($comment->user_id); ?>">
			<!-- TODO: OPTION - Avatar size -->
			<?php echo get_avatar($comment->user_id, 30); ?>
			</a>
		</div><!-- close .ap-avatar -->
		<div class="ap-comment-content no-overflow">
			<div class="ap-comment-header">
				<a href="<?php echo ap_user_link($comment->user_id); ?>" class="ap-comment-author"><?php echo ap_user_display_name($comment->user_id); ?></a>

				<?php $a = ' e ';$b = ' ';$time = get_option('date_format').$b.get_option('time_format').$a.get_option('gmt_offset');
						printf(' - <a title="%3$s" href="#li-comment-%4$s" class="ap-comment-time"><time datetime="%1$s">%2$s</time></a>',
						get_comment_time('c'),
						ap_human_time(get_comment_time('U')),
						get_comment_time($time),
						get_comment_ID()
					);

					// Comment actions
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
				do_action('ap_after_comment_content', $comment);
			?>
			<?php if ('0' == $comment->comment_approved) : ?>
				<p class="comment-awaiting-moderation"><?php _e('Your comment is awaiting moderation.', 'ap'); ?></p>
			<?php endif; ?>
		</div><!-- close .ap-comment-content -->
	</div><!-- close #comment-* -->
</li><!-- close #li-comment-* -->
