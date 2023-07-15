<?php
/**
 * This file is responsible for displaying question page
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @license    https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author     Rahul Aryan <rah12@live.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="ap-single" class="ap-q clearfix" itemtype="https://schema.org/Question" itemscope="">

	<h1 class="entry-title"><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h1>
	<div class="ap-question-lr">
		<div class="ap-q-left">
			<div class="ap-question-meta clearfix">
			<?php ap_question_metas(); ?>
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
							<?php
								ap_user_display_name(
									array(
										'html' => true,
										'echo' => true,
									)
								);
								?>
							<a href="<?php the_permalink(); ?>" class="ap-posted">
								<?php
									echo esc_attr(
										sprintf(
											'<time itemprop="datePublished" datetime="%1$s">%2$s</time>',
											ap_get_time( get_the_ID(), 'c' ),
											sprintf(
												// translators: %s is human readable time difference.
												__( 'Posted %s', 'anspress-question-answer' ),
												ap_human_time( ap_get_time( get_the_ID(), 'U' ) )
											)
										)
									);
									?>
							</a>
							<?php ap_recent_post_activity(); ?>
							<?php echo ap_post_status_badge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
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
			<a class="ap-eq-view-ans" href="<?php the_permalink(); ?>">
				<?php esc_attr_e( 'View all answers', 'anspress-question-answer' ); ?>
			</a>
		</div>

	</div>
</div>
