<?php
/**
 * This file is responsible for displaying question page
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author    Rahul Aryan <rah12@live.com>
 */

?>
<div id="ap-single" class="ap-q clearfix" itemtype="https://schema.org/Question" itemscope="">

	<h1 class="entry-title"><a href="<?php echo get_the_permalink(); ?>"><?php the_title(); ?></a></h1>
	<div class="ap-question-lr">
		<div class="ap-q-left">
			<div class="ap-question-meta clearfix">
			<?php echo ap_question_metas(); // xss ok. ?>
		</div>

		<div ap="question" apId="<?php the_ID(); ?>">
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
							<?php echo ap_user_display_name( [ 'html' => true ] ); ?>
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
							<?php ap_recent_post_activity(); ?>
							<?php echo ap_post_status_badge(); // xss okay. ?>
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
					</div>
				</div>
			</div>
		</div>
			<a class="ap-eq-view-ans" href="<?php echo get_the_permalink(); ?>">
				<?php _e( 'View all answers', 'anspress-question-answer' ); ?>
			</a>
		</div>

	</div>
</div>
