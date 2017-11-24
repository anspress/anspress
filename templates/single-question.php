<?php
/**
 * This file is responsible for displaying question page
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @subpackage Templates
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <support@anspress.io>
 *
 * @since      0.0.1
 * @since      4.1.0 Renamed file from question.php.
 * @since      4.1.2 Removed @see ap_recent_post_activity().
 */

?>
<div id="ap-single" class="ap-q clearfix">

	<div class="ap-question-lr ap-row" itemtype="https://schema.org/Question" itemscope="">
		<div class="ap-q-left <?php echo (is_active_sidebar( 'ap-qsidebar' ) ) ? 'ap-col-8' : 'ap-col-12'; ?>">
			<?php
				/**
				 * Action hook triggered before question meta in single question.
				 *
				 * @since 4.1.2
				 */
				do_action( 'ap_before_question_meta' );
			?>
			<div class="ap-question-meta clearfix">
				<?php echo ap_question_metas(); // xss ok. ?>
			</div>

			<div ap="question" apid="<?php the_ID(); ?>">
				<div id="question" role="main" class="ap-content">
					<div class="ap-single-vote"><?php ap_vote_btn(); ?></div>
					<?php
					/**
					 * Action triggered before question title.
					 *
					 * @since 	2.0
					 */
					do_action( 'ap_before_question_title' );
					?>
					<div class="ap-avatar">
						<a href="<?php ap_profile_link(); ?>">
							<?php ap_author_avatar( ap_opt( 'avatar_size_qquestion' ) ); ?>
						</a>
					</div>
					<div class="ap-cell clearfix">
						<div class="ap-cell-inner">
							<div class="ap-q-metas">
								<span itemprop="author" itemscope itemtype="http://schema.org/Person">
									<?php echo ap_user_display_name( [ 'html' => true ] ); ?>
								</span>
								<a href="<?php the_permalink(); ?>" class="ap-posted">
									<?php
										printf(
											'<time itemprop="datePublished" datetime="%1$s">%2$s</time>',
											ap_get_time( get_the_ID(), 'c' ),
											sprintf(
												__( 'Posted %s', 'anspress-question-answer' ),
												ap_human_time( ap_get_time( get_the_ID(), 'U' ) )
											)
										);
									?>
								</a>
							</div>

							<!-- Start ap-content-inner -->
							<div class="ap-q-inner">
								<?php
								/**
								 * Action triggered before question content.
								 *
								 * @since 	2.0.0
								 */
								do_action( 'ap_before_question_content' );
								?>

								<div class="question-content ap-q-content" itemprop="text">
									<?php the_content(); ?>
								</div>

								<?php
									/**
									 * Action triggered after question content.
									 *
									 * @since 	2.0.0
									 */
									do_action( 'ap_after_question_content' );
								?>
							</div>

							<div class="ap-post-footer clearfix">
								<?php ap_post_actions_buttons() ?>
								<?php do_action( 'ap_post_footer' ); ?>
								<?php echo ap_comment_btn_html(); ?>
							</div>
						</div>

						<?php ap_post_comments(); ?>
					</div>
				</div>
			</div>

			<?php
				// Get answers.
				ap_answers();

				// Get answer form.
				ap_get_template_part( 'answer-form' );
			?>
		</div>

		<?php if ( is_active_sidebar( 'ap-qsidebar' ) ) { ?>
			<div class="ap-question-right ap-col-4">
				<div class="ap-question-info">
					<?php dynamic_sidebar( 'ap-qsidebar' ); ?>
				</div>
			</div>
		<?php } ?>

	</div>
</div>
