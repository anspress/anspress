<?php
/**
 * This template is used for displaying comment list.
 *
 * @link http://anspress.io
 * @since unknown
 */
if (post_password_required() || (ap_opt('logged_in_can_see_comment') && !is_user_logged_in())) {
    return;
}

?>

<?php if (have_comments()) : ?>	
		<?php wp_list_comments(array('max_depth' => 0, 'callback' => 'ap_comment', 'style' => 'ul')); ?>	

		<?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
			<li id="comment-nav-below" class="navigation" role="navigation">
				<div class="nav-previous"><?php previous_comments_link(__('&larr; Older Comments', 'anspress-question-answer')); ?></div>
				<div class="nav-next"><?php next_comments_link(__('Newer Comments &rarr;', 'anspress-question-answer')); ?></div>
			</li>
		<?php endif; ?>
	</div><!-- close .ap-comments -->
	<?php endif; ?>

