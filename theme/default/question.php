<?php
/**
 * This file is responsible for displaying question page
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @license    http://opensource.org/licenses/gpl-license.php  GNU Public License
 * @author    Rahul Aryan <rah12@live.com>
 */

global $post;
?>
<div id="ap-single" class="clearfix" itemtype="http://schema.org/Question" itemscope="">	
	<div class="ap-question-lr">		
		<div class="ap-question-left">
			<div id="question" role="main" class="ap-content question" data-id="<?php echo get_the_ID(); ?>">
				<?php 
					/**
					 * ACTION: ap_before_question_title
					 * @since 	2.0
					 */
					do_action('ap_before_question_title', $post);
				?>
				<?php 
					/**
					 * By default this title is hidden
					 */
					if(ap_opt('show_title_in_question')) : 
				?>
					<h1 class="entry-title"><?php the_title(); ?></h1>
				<?php endif; ?>

				<?php 
					/**
					 * ACTION: ap_after_question_title
					 * @since 	2.0
					 */
					do_action('ap_after_question_title', $post);
				?>
				<div class="ap-question-cells clearfix">
					<div class="ap-avatar ap-pull-left">
						<a href="<?php echo ap_user_link(get_the_author_meta('ID'))?>">
							<?php echo get_avatar( get_the_author_meta( 'user_email' ), ap_opt('avatar_size_qquestion') ); ?>
						</a>
						<div class="ap-single-vote"><?php ap_vote_html(); ?></div>
					</div>
					<!-- Start ap-content-inner -->
					<div class="ap-content-inner no-overflow">
	
						<div class="ap-question-metas clearfix">
							<?php ap_favorite_html(); ?>
							<?php ap_user_display_meta(true, false, true); ?>

							<!-- TODO: Show all questions history on toggle -->
							<ul class="ap-display-question-meta ap-ul-inline">
								<?php echo ap_display_question_metas() ?>
							</ul>
							
						</div>

						<div class="question-content ap-post-content" itemprop="text">
							<?php the_content(); ?>									
						</div>
						<?php 
							/**
							 * ACTION: ap_after_question_content
							 * @since 	2.0
							 */
							do_action('ap_after_question_content', $post);
						?>

						<ul class="ap-user-actions ap-ul-inline clearfix">
							<?php ap_post_actions_buttons() ?>
						</ul>

						<?php 
							/**
							 * ACTION: ap_after_question_actions
							 * @since 	2.0
							 */
							global $post;
							do_action('ap_after_question_actions', $post);
						?>

						<?php comments_template(); ?>

					</div>
					<!-- End ap-content-inner -->					
				</div>		
			</div>
			
			<?php

				if(ap_have_ans(get_the_ID())){
					include(ap_get_theme_location('best_answer.php'));
					ap_get_answers();
				} 
			?>
				
			<?php 
				if(ap_user_can_answer(get_question_id()))
					include(ap_get_theme_location('answer-form.php')); 
			?>
		</div>
		<!--
		<div class="ap-question-right">
			<div class="ap-question-right-inner">
				<?php ap_question_side_tab(get_question_id()); ?>

				<div class="ap-question-side">
					<h3 class="ap-question-side-title">
						<?php _e('Labels', 'ap'); ?>					
						<?php ap_change_label_html(get_question_id()); ?>
					</h3>
					<div data-view="ap-labels-list">
						<?php echo ap_get_question_label(get_question_id(), true); ?>
					</div>
				</div>

				<?php if(ap_opt('enable_categories')): ?>
				<div class="ap-question-side">
					<h3 class="ap-question-side-title"><?php _e('Categories', 'ap'); ?></h3>
					<?php ap_question_categories_html(get_question_id()); ?>
				</div>
				<?php endif; ?>


				<?php if(ap_opt('enable_tags')): ?>
				<div class="ap-question-side">
					<h3 class="ap-question-side-title"><?php _e('Tags', 'ap'); ?></h3>
					<?php ap_question_tags_html(get_question_id()); ?>
				</div>
				<?php endif; ?>

				<div class="ap-question-side">			
					<?php ap_get_all_parti(30, get_question_id()); ?>
				</div>

			</div>
		</div>-->
	</div>
</div>
