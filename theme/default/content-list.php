<?php
/**
 * Question list item template
 *
 * @link https://anspress.io
 * @since 0.1
 * @license GPL 2+
 * @package AnsPress
 */

if ( ! ap_user_can_view_post(get_the_ID() ) ) {
	return;
}

global $post;
$clearfix_class = array( 'ap-questions-item clearfix' );

?>
<div id="question-<?php ap_question_the_ID(); ?>" <?php post_class($clearfix_class ); ?>>
    <div class="ap-questions-inner">
        <div class="ap-avatar ap-pull-left">
			<a href="<?php ap_question_the_author_link(); ?>"<?php ap_hover_card_attributes(ap_question_get_author_id() ); ?>>
				<?php ap_question_the_author_avatar(ap_opt('avatar_size_list' ) ); ?>
            </a>
        </div>
        <div class="ap-list-counts">
			<?php ap_question_the_answer_count(); ?>
			<?php ap_question_the_net_vote(); ?>
        </div>
        <div class="ap-questions-summery no-overflow">
            <span class="ap-questions-title entry-title" itemprop="title">
				<?php ap_question_the_status(); ?>
				<a class="ap-questions-hyperlink" itemprop="url" href="<?php ap_question_the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
            </span>
            <div class="ap-display-question-meta">
				<?php echo ap_display_question_metas() ?>
            </div>
        </div>
    </div>
</div><!-- list item -->
