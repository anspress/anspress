<?php
/**
 * Display item meta.
 *
 * @since 5.0.0
 * @package AnsPress
 */

use AnsPress\Classes\PostHelper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check post is set or not.
if ( ! isset( $post ) ) {
	throw new InvalidArgumentException( esc_attr__( 'Post argument is required.', 'anspress-question-answer' ) );
}

// Check for $attributes is set or not.
if ( ! isset( $attributes ) ) {
	throw new InvalidArgumentException( esc_attr__( 'Attributes argument is required.', 'anspress-question-answer' ) );
}
?>
<div class="anspress-apq-item-metas">
	<div class="anspress-apq-item-author">
		<?php
		ap_user_display_name(
			array(
				'html' => true,
				'echo' => true,
			)
		);
		?>
	</div>
	<a href="<?php the_permalink(); ?>" class="anspress-apq-item-posted">
		<?php
		$posted = 'future' === get_post_status() ? __( 'Scheduled for', 'anspress-question-answer' ) : __( 'Published', 'anspress-question-answer' );

		$time = ap_get_time( get_the_ID(), 'U' );

		if ( 'future' !== get_post_status() ) {
			$time = ap_human_time( $time );
		}
		?>
		<time itemprop="datePublished" datetime="<?php echo esc_attr( ap_get_time( get_the_ID(), 'c' ) ); ?>"><?php echo esc_attr( $time ); ?></time>
	</a>
	<div class="anspress-apq-item-meta-badges">
		<?php if ( PostHelper::isQuestion( $post ) ) : ?>
			<?php if ( ap_is_featured_question( $post ) ) : ?>
				<div class="anspress-apq-item-featuredlabel">
					<?php esc_html_e( 'Featured', 'anspress-question-answer' ); ?>
				</div>
			<?php endif; ?>
				<?php if ( ap_selected_answer( $post->ID ) ) : ?>
				<div class="anspress-apq-item-selected-answer">
					<span ><?php esc_html_e( 'Solved', 'anspress-question-answer' ); ?></span>
				</div>
			<?php endif; ?>
				<?php if ( is_post_closed( $post ) ) : ?>
				<div class="anspress-apq-item-closedlabel">
					<span ><?php esc_html_e( 'Closed', 'anspress-question-answer' ); ?></span>
				</div>
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( PostHelper::isPrivateStatus( $post ) ) : ?>
			<div class="anspress-apq-item-privatelabel">
				<span><?php esc_html_e( 'Private', 'anspress-question-answer' ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( PostHelper::isModerateStatus( $post ) ) : ?>
			<div class="anspress-apq-item-moderatelabel">
				<span><?php esc_html_e( 'Moderate', 'anspress-question-answer' ); ?></span>
			</div>
		<?php endif; ?>
	</div>
</div>
