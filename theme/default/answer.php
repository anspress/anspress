<?php
/**
 * Answer content
 *
 * @author Rahul Aryan <support@anspress.io>
 * @link https://anspress.io/anspress
 * @since 0.1
 *
 * @package AnsPress
 */
global $post;
$have_permission = ap_user_can_read_answer( $post );
?>
<div id="answer_<?php the_ID(); ?>" <?php post_class() ?> data-id="<?php the_ID(); ?>">
    <div class="ap-content" itemprop="suggestedAnswer<?php echo ap_answer_is_best() ? ' acceptedAnswer' : ''; ?>" itemscope itemtype="https://schema.org/Answer">
		<div class="ap-single-vote"><?php ap_answer_the_vote_button(); ?></div>
        <div class="ap-avatar">
			<a href="<?php ap_answer_the_author_link(); ?>"<?php ap_hover_card_attributes(ap_answer_get_author_id() ); ?>>
				<?php ap_answer_the_author_avatar(); ?>
            </a>
        </div>
        <div class="ap-a-cells clearfix">
            <div class="ap-q-metas">
				<?php ap_user_display_meta(true, false, true ); ?>
				<?php ap_answer_the_time(); ?>
            </div>
            <div class="ap-q-inner">
                <?php
					/**
					 * ACTION: ap_before_answer_content
					 * @since   3.0.0
					 */
					do_action('ap_before_answer_content' );

				?>
                <div class="ap-answer-content ap-q-content" itemprop="text">
					<?php the_content(); ?>
                </div>
                <?php
					/**
					 * ACTION: ap_after_answer_content
					 * @since   3.0.0
					 */
					do_action('ap_after_answer_content' );

				?>
				<?php if ( $have_permission ) :   ?>
					<?php ap_answer_the_active_time(); ?>
					<?php ap_post_status_description(ap_answer_get_the_answer_id() ) ?>
					<?php ap_post_actions_buttons() ?>
				<?php endif; ?>
            </div>
			<?php ap_answer_the_comments(); ?>
        </div>
    </div>
</div>
