<?php
/**
 * BuddyPress question item.
 *
 * Template used to render question item in BuddyPress
 * profile questions page.
 *
 * @link     https://anspress.net
 * @since    4.0.0
 * @license  GPL 3+
 * @package  WordPress/AnsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! ap_user_can_view_post( get_the_ID() ) ) {
	return;
}

?>

<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="ap-answer-single ap-bpsingle">
		<div class="ap-bpsingle-title entry-title" itemprop="title">
			<?php ap_answer_status(); ?>
			<a class="ap-bpsingle-hyperlink" itemprop="url" href="<?php the_permalink(); ?>" title="<?php echo esc_html( get_the_title() ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
		</div>

		<div class="ap-bpsingle-content clearfix">
			<div class="ap-avatar ap-pull-left">
				<a href="<?php ap_profile_link(); ?>">
					<?php ap_author_avatar( 40 ); ?>
				</a>
			</div>
			<div class="ap-bpsingle-desc no-overflow">
				<time itemprop="datePublished" datetime="<?php echo esc_attr( ap_get_time( get_the_ID(), 'c' ) ); ?>" class="ap-bpsingle-published">
					<?php
						// translators: %s is human time difference.
						echo esc_attr( sprintf( 'Posted %s', ap_human_time( ap_get_time( get_the_ID(), 'U' ) ) ) );
					?>
				</time>
				<a href="<?php the_permalink(); ?>" class="apicon-answer ap-bpsingle-acount">
					<?php
						// translators: %d is total answer count.
						echo esc_attr( sprintf( _n( '%d Answer', '%d Answers', ap_get_answers_count(), 'anspress-question-answer' ), ap_get_answers_count() ) );
					?>
				</a>

				<p><?php echo esc_html( wp_trim_words( get_the_content(), 30, '...' ) ); ?></p>
				<a href="<?php the_permalink(); ?>" class="ap-view-question"><?php esc_html_e( 'View Question', 'anspress-question-answer' ); ?></a>
			</div>
		</div>

		<div class="ap-bpsingle-meta">

			<span class="apicon-thumb-up">
				<?php
					// translators: %d is net votes count.
					echo esc_attr( sprintf( _n( '%d Vote', '%d Votes', ap_get_votes_net(), 'anspress-question-answer' ), ap_get_votes_net() ) );
				?>
			</span>

			<?php ap_question_metas(); ?>
		</div>

	</div>
</div>
