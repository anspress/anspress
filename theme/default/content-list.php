<?php
/**
 * Question list item template
 *
 * @link http://wp3.in
 * @since 0.1
 * @license GPL 2+ 
 * @package AnsPress
 */

if(!ap_user_can_view_post(get_the_ID()))
	return;

global $post;
$clearfix_class = array('list-question clearfix');

?>
<article id="question-<?php the_ID(); ?>" <?php post_class($clearfix_class); ?>>
	<?php if ( is_private_post()) : ?>
		<div class="private-question-label">
			<span><?php _e( 'Private Question', 'ap' ); ?></span>
		</div>
	<?php endif; ?>		
	<div class="ap-list-inner">
		<div class="ap-avatar ap-pull-left">
			<a href="<?php echo ap_user_link(); ?>">
				<!-- TODO: OPTION - avatar size in question list -->
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 45 ); ?>
			</a>
		</div>								
		<div class="summery no-overflow">
			<span class="question-title entry-title" itemprop="title">
				<a class="question-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title(); ?>"><?php the_title(); ?></a>
			</span>
			<ul class="ap-display-question-meta ap-ul-inline">
				<?php echo ap_display_question_metas() ?>
				<!-- TODOD: hook question labels ap_get_question_label(null, true);  -->
			</ul>
		</div>				
	</div>	
	<div class="ap-count">	
		<a class="ap-answer-count ap-tip" href="<?php echo ap_answers_link(); ?>" title="<?php _e('Total answers', 'ap'); ?>">
			<?php
				$ans_count = ap_count_answer_meta();
				printf(_n('%s %d %s answer', '%s %d %s answers', $ans_count, 'ap'), '<span>', $ans_count, '</span>') ?>
		</a>						
	</div>
	<div class="ap-list-history">
		<?php echo ap_get_latest_history_html(get_the_ID(), true) ?>
	</div>
</article><!-- list item -->
