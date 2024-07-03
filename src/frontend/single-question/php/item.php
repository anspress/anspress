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
			<?php
			Plugin::loadView(
				'src/frontend/single-question/php/item-meta.php',
				$args
			);
			?>
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
