<?php
/**
 * BuddyPress answer item.
 *
 * Template used to render answer item in loop
 *
 * @link     https://anspress.net
 * @since    4.0.0
 * @license  GPL 3+
 * @package  WordPress/AnsPress
 */

if ( ! ap_user_can_view_post( get_the_ID() ) ) {
	return;
}

?>
<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="ap-answer-single ap-bpsingle">
		<div class="ap-bpsingle-title entry-title" itemprop="title">
			<?php ap_answer_status(); ?>
			<a class="ap-bpsingle-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
		</div>

		<div class="ap-bpsingle-content clearfix">
			<div class="ap-avatar ap-pull-left">
				<a href="<?php ap_profile_link(); ?>">
					<?php ap_author_avatar( 40 ); ?>
				</a>
			</div>
			<div class="ap-bpsingle-desc no-overflow">
				<a href="<?php the_permalink(); ?>" class="ap-bpsingle-published">
					<time itemprop="datePublished" datetime="<?php echo ap_get_time( get_the_ID(), 'c' ); ?>">
						<?php
							echo esc_html(
								sprintf(
									// Translators: %s contain human readable time.
									__( 'Posted %s', 'anspress-question-answer' ),
									ap_human_time( ap_get_time( get_the_ID(), 'U' ) )
								)
							);
						?>
					</time>
				</a>
				<p><?php echo ap_truncate_chars( get_the_content(), 200 ); ?></p>
				<a href="<?php the_permalink(); ?>" class="ap-view-question"><?php esc_html_e( 'View Question', 'anspress-question-answer' ); ?></a>
			</div>
		</div>

		<div class="ap-bpsingle-meta">
			<span class="apicon-thumb-up"><?php printf( _n( '%d Vote', '%d Votes', ap_get_votes_net(), 'anspress-question-answer' ), ap_get_votes_net() ); ?></span>
			<?php if ( ap_is_selected( get_the_ID() ) ) : ?>
				<span class="ap-bpsingle-selected apicon-check" title="<?php esc_attr_e( 'This answer is selected as best', 'anspress-question-answer' ); ?>"><?php esc_attr_e( 'Selected', 'anspress-question-answer' ); ?></span>
			<?php endif; ?>
			<?php ap_recent_post_activity(); ?>
		</div>

	</div>
</div>
