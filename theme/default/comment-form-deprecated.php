<?php
/**
 * This template is used for displaying comment form.
 *
 * @link https://anspress.io
 * @since 2.4
 */
$subscribed = false;
if( is_object( $ap_comment ) ){
	$subscribed = ap_is_user_subscribed( $ap_comment->comment_post_ID, 'comment', $ap_comment->user_id );
}

?>
<div class="ap-avatar ap-pull-left">
	<?php echo get_avatar( get_current_user_id(), 30 ); ?>
</div>
<div class="ap-comment-inner no-overflow">
	<textarea placeholder="<?php _e('Your comment..', 'anspress-question-answer' ); ?>" class="ap-form-control autogrow" id="ap-comment-textarea" aria-required="true" rows="3" name="content"><?php echo isset( $ap_comment, $ap_comment->comment_content ) ? $ap_comment->comment_content : ''; ?></textarea>

    <div class="ap-comment-footer clearfix">
        <label>
            <input type="checkbox" value="1" name="notify" <?php checked( $subscribed, true ); ?> />
			<?php _e('Notify me of follow-up comments', 'anspress-question-answer' ); ?>
        </label>
		<button type="submit" class="ap-comment-submit ap-btn"><?php _e( 'Submit', 'anspress-question-answer' ); ?></button>
		<a data-action="cancel-comment" class="ap-comment-cancel" href="#"><?php _e('Cancel', 'anspress-question-answer' ); ?></a>
    </div>
</div>

