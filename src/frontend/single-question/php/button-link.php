<?php
/**
 * Share post button.
 *
 * @package AnsPress
 * @since   5.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check that post argument is set.
if ( ! isset( $post ) ) {
	throw new InvalidArgumentException( 'Post argument is required.' );
}

?>
<a href="<?php the_permalink(); ?>" class="anspress-apq-item-action anspress-apq-item-action-view"><?php esc_html_e( 'Link', 'anspress-question-answer' ); ?></a>
