<?php
/**
 * This template is used for displaying comment form.
 *
 * @link http://anspress.io
 * @since 2.4
 */
?>
<div class="ap-comment-submit">
	<input type="submit" value="<?php _e( 'Comment', 'ap' ); ?>" name="submit">
	<a href="#" data-action="cancel-comment" data-id="<?php echo $comment_post_ID; ?>"><?php _e( 'Cancel', 'ap' ); ?></a>
</div>
<div class="ap-comment-textarea">
	<textarea name="comment" rows="3" aria-required="true" id="ap-comment-textarea" class="ap-form-control autogrow" placeholder="<?php _e( 'Respond to the post.', 'ap' ); ?>"><?php echo $content; ?></textarea>
</div>
