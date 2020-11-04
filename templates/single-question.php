<?php
/**
 * This file is responsible for displaying question page
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @subpackage Templates
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <rah12@live.com>
 *
 * @since      0.0.1
 * @since      4.1.0 Renamed file from question.php.
 * @since      4.1.2 Removed @see ap_recent_post_activity().
 * @since      4.1.5 Fixed date grammar when post is not published.
 */

?>
<div id="ap-single" class="ap-q clearfix">

	<div class="ap-question-lr ap-row" itemtype="https://schema.org/Question" itemscope="">
		<div class="ap-q-left <?php echo ( is_active_sidebar( 'ap-qsidebar' ) ) ? 'ap-col-8' : 'ap-col-12'; ?>">
			<?php
				/**
				 * Action hook triggered before question meta in single question.
				 *
				 * @since 4.1.2
				 */
				do_action( 'ap_before_question_meta' );
			?>
			<div class="ap-question-meta clearfix">
				<?php ap_question_metas(); // xss ok. ?>
			</div>
			<?php
				/**
				 * Action hook triggered after single question meta.
				 *
				 * @since 4.1.5
				 */
				do_action( 'ap_after_question_meta' );
			?>
			<div ap="question" apid="<?php the_ID(); ?>">
				<div id="question" role="main" class="ap-content">
					<div class="ap-single-vote"><?php ap_vote_btn(); ?></div>
					<?php
					/**
					 * Action triggered before question title.
					 *
					 * @since   2.0
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
								<span class="ap-author" itemprop="author" itemscope itemtype="http://schema.org/Person">
									<?php echo ap_user_display_name( [ 'html' => true ] ); ?>
								</span>
								<a href="<?php the_permalink(); ?>" class="ap-posted">
									<?php
									$posted = 'future' === get_post_status() ? __( 'Scheduled for', 'anspress-question-answer' ) : __( 'Published', 'anspress-question-answer' );

									$time = ap_get_time( get_the_ID(), 'U' );

									if ( 'future' !== get_post_status() ) {
										$time = ap_human_time( $time );
									}

									printf( '<time itemprop="datePublished" datetime="%1$s">%2$s</time>', ap_get_time( get_the_ID(), 'c' ), $time );
									?>
								</a>
								<span class="ap-comments-count">
									<?php $comment_count = get_comments_number(); ?>
									<?php printf( _n( '%s Comment', '%s Comments', $comment_count, 'anspress-question-answer' ), '<span itemprop="commentCount">' . (int) $comment_count . '</span>' ); ?>
								</span>
							</div>

							<!-- Start ap-content-inner -->
							<div class="ap-q-inner">
								<?php
								/**
								 * Action triggered before question content.
								 *
								 * @since   2.0.0
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
									 * @since   2.0.0
									 */
									do_action( 'ap_after_question_content' );
								?>
							</div>

							<div class="ap-post-footer clearfix">
								<?php ap_post_actions_buttons(); ?>
								<?php do_action( 'ap_post_footer' ); ?>
							</div>
						</div>

						<?php ap_post_comments(); ?>
					</div>
				</div>
			</div>

			<?php
				/**
				 * Action triggered before answers.
				 *
				 * @since   4.1.8
				 */
				do_action( 'ap_before_answers' );
			?>

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
