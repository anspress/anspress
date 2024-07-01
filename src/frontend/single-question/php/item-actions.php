<?php
/**
 * Item actions.
 *
 * @package AnsPress
 * @subpackage SingleQuestionBlock
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check for required variable $post.
if ( ! isset( $post ) ) {
	throw new InvalidArgumentException( 'Post argument is required.', 'anspress-question-answer' );
}


?>
<div class="anspress-apq-item-actions">
	<?php
	/**
	 * Action triggered before item actions.
	 *
	 * @since   5.0.0
	 */

	do_action( 'anspress/single_question/before_item_actions' );

	Plugin::loadView(
		'src/frontend/single-question/php/button-close.php',
		array( 'post' => $post )
	);

	Plugin::loadView(
		'src/frontend/single-question/php/button-feature.php',
		array( 'post' => $post )
	);

	Plugin::loadView(
		'src/frontend/single-question/php/button-delete.php',
		array( 'post' => $post )
	);

	Plugin::loadView(
		'src/frontend/single-question/php/button-edit.php',
		array( 'post' => $post )
	);

	Plugin::loadView(
		'src/frontend/single-question/php/button-report.php',
		array( 'post' => $post )
	);

	Plugin::loadView(
		'src/frontend/single-question/php/button-moderate.php',
		array( 'post' => $post )
	);

	Plugin::loadView(
		'src/frontend/single-question/php/button-private.php',
		array( 'post' => $post )
	);
	?>

	<a href="<?php the_permalink(); ?>" class="anspress-apq-item-action anspress-apq-item-action-view"><?php esc_html_e( 'Share', 'anspress-question-answer' ); ?></a>
	<?php

	/**
	 * Action triggered after item actions.
	 *
	 * @since   5.0.0
	 */
	do_action( 'anspress/single_question/after_item_actions' );
	?>
</div>
