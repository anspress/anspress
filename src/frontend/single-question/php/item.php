<?php
/**
 * Answers content.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;
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

$isQuestion = 'question' === $post->post_type;

$selectedAnswer = ! $isQuestion ? ap_is_selected( $post->ID ) : false;

$classes = array( 'anspress-apq-item' );

if ( $selectedAnswer ) {
	$classes[] = 'anspress-apq-item-selected';
}
?>
<anspress-item data-post-id="<?php echo (int) $post->ID; ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-anspress-id="<?php echo esc_attr( $post->post_type . ':' . $post->ID ); ?>">

	<div class="anspress-apq-item-avatar">
		<div class="anspress-avatar-link">
			<a href="<?php ap_profile_link(); ?>"><?php ap_author_avatar( $attributes['avatarSize'] ); ?></a>
		</div>
	</div>
	<div class="anspress-apq-item-content">
		<?php
		/**
		 * Action triggered before itme.
		 *
		 * @since   5.0.0
		 */
		do_action( 'anspress/single_question/before_item' );
		?>
		<div class="anspress-apq-item-qbody anspress-card">
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
				<span class="anspress-apq-item-ccount">
					<?php $comment_count = get_comments_number(); ?>
					<?php
						// translators: %s comments count.
						echo wp_kses_post( sprintf( _n( '%s Comment', '%s Comments', $comment_count, 'anspress-question-answer' ), '<span itemprop="commentCount">' . (int) $comment_count . '</span>' ) );
					?>

				</span>
				<div class="anspress-apq-item-meta-badges">
					<?php if ( $isQuestion && ap_is_featured_question( $post ) ) : ?>
						<div class="anspress-apq-item-featuredlabel">
							<?php esc_html_e( 'Featured', 'anspress-question-answer' ); ?>
						</div>
					<?php endif; ?>
					<?php if ( $isQuestion && ap_selected_answer( $post->ID ) ) : ?>
						<div class="anspress-apq-item-selected-answer">
							<span ><?php esc_html_e( 'Solved', 'anspress-question-answer' ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( $isQuestion && is_post_closed( $post ) ) : ?>
						<div class="anspress-apq-item-closedlabel">
							<span ><?php esc_html_e( 'Closed', 'anspress-question-answer' ); ?></span>
						</div>
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
			<div class="anspress-apq-item-inner">
				<?php
					/**
					 * Action triggered before question content.
					 *
					 * @since   5.0.0
					 */
					do_action( 'anspress/single_question/before_item_content' );
				?>

				<div class="question-content" itemprop="text">
					<?php the_content(); ?>
				</div>

				<?php
					/**
					 * Action triggered after question content.
					 *
					 * @since   5.0.0
					 */
					do_action( 'anspress/single_question/after_item_content' );
				?>

			</div>

			<div class="anspress-apq-item-footer">
				<?php
					Plugin::loadView(
						'src/frontend/single-question/php/vote-button.php',
						array( 'ID' => $post->ID )
					);
					?>
				<?php if ( ! $isQuestion ) : ?>
					<?php
						Plugin::loadView(
							'src/frontend/single-question/php/button-select.php',
							$args
						);
					?>
				<?php endif; ?>

				<?php
				Plugin::loadView(
					'src/frontend/single-question/php/item-actions.php',
					$args
				);
				?>
			</div>
		</div>

		<?php
		/**
		 * Action triggered after item.
		 *
		 * @since   5.0.0
		 */
		do_action( 'anspress/single_question/after_item' );
		?>

		<?php
			Plugin::loadView( 'src/frontend/common/comments/render.php', $args );
		?>
	</div>

</anspress-item>
