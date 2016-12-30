<?php
/**
 * BuddyPress answer item.
 *
 * Template used to render answer item in loop
 *
 * @link     https://anspress.io
 * @since    4.0.0
 * @license  GPL 3+
 * @package  WordPress/AnsPress
 */

if ( ! ap_user_can_view_post( get_the_ID() ) ) {
	return;
}

?>
<div id="post-<?php the_ID(); ?>" <?php post_class( ); ?>>

	<div class="ap-answer-single ap-asingle">
		<div class="ap-asingle-title entry-title" itemprop="title">
			<?php ap_answer_status(); ?>
			<a class="ap-asingle-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
		</div>

		<div class="ap-asingle-content clearfix">
			<div class="ap-avatar ap-pull-left">
				<a href="<?php ap_profile_link(); ?>"<?php ap_hover_card_attr(); ?>>
					<?php ap_author_avatar( 40 ); ?>
				</a>
			</div>
			<div class="ap-asingle-desc no-overflow">
				<?php echo ap_truncate_chars( get_the_content(), 200 ); ?>
			</div>
		</div>

		<div class="ap-asingle-meta">
			<a href="<?php the_permalink(); ?>" class="ap-view-question"><?php esc_html_e( 'View Question', 'anspress-question-answer' ); ?></a>
			<span><?php printf( _n( '%d Vote', '%d Votes', ap_get_votes_net(), 'anspress-question-answer' ), ap_get_votes_net() ); ?></span>
			<a href="<?php the_permalink(); ?>" class="ap-posted">
				<time itemprop="datePublished" datetime="<?php echo ap_get_time( get_the_ID(), 'c' ); ?>">
					<?php printf( 'Posted %s', ap_human_time( ap_get_time( get_the_ID(), 'U' ) ) ); ?>
				</time>
			</a>
			<?php ap_recent_post_activity(); ?>
		</div>

	</div>
</div>
