<?php
/**
 * This file is responsible for displaying question page
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author    Rahul Aryan <support@anspress.io>
 */
?>
<div id="ap-single" class="ap-q clearfix" itemtype="https://schema.org/Question" itemscope="">

	<h1 class="entry-title"><a href="<?php echo get_the_permalink() ?>"><?php the_title(); ?></a></h1>
	<div class="ap-question-lr">
		<div class="ap-q-left">
			<div class="ap-question-meta clearfix">
				<?php echo ap_display_question_metas(); ?>
			</div>
			<div id="question" role="main" class="ap-content question" data-id="<?php ap_question_the_ID(); ?>">
				<div class="ap-single-vote"><?php ap_question_the_vote_button(); ?></div>
				<?php
					/**
					 * ACTION: ap_before_question_title
					 * @since 	2.0
					 */
					do_action('ap_before_question_content');
				?>
				<div class="ap-avatar">
					<a href="<?php ap_question_the_author_link(); ?>"<?php ap_hover_card_attributes(ap_question_get_author_id()); ?>>
						<?php ap_question_the_author_avatar( ap_opt('avatar_size_qquestion') ); ?>
					</a>
				</div>
				<div class="ap-q-cells clearfix">
					<div class="ap-q-metas">
						<?php ap_user_display_meta(true, false, true); ?>
						<span><?php ap_question_the_time(); ?></span>
					</div>

					<!-- Start ap-content-inner -->
					<div class="ap-q-inner">
						<div class="question-content ap-q-content" itemprop="text">
							<?php the_content(); ?>
						</div>

						<?php
							/**
							 * ACTION: ap_after_question_content
							 * @since 	2.0.0-alpha2
							 */
							do_action('ap_after_question_content');
						?>

						<?php ap_question_the_active_time(); ?>
						<?php ap_post_status_description(ap_question_get_the_ID());	?>

						<?php
							/**
							 * ACTION: ap_after_question_actions
							 * @since 	2.0
							 */
							do_action('ap_after_question_actions');
						?>
					</div>
					
				</div>
			</div>
			<a class="ap-eq-view-ans" href="<?php echo get_the_permalink() ?>">
				<?php _e('View all answers', 'anspress-question-answer'); ?>
			</a>
		</div>

	</div>
</div>
