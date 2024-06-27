<?php
/**
 * Delete post button.
 *
 * @package AnsPress
 * @since   5.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check that post argument is set.
if ( ! isset( $args ['post'] ) ) {
	throw new InvalidArgumentException( 'Post argument is required.' );
}

if ( 'question' === $args['post']->post_type ) {
	$href = 'anspress/v1/post/' . $args['post']->ID . '/actions/delete-question';
} else {
	$href = 'anspress/v1/post/' . $args['post']->post_parent . '/answers/' . $args['post']->ID;
}
?>
<anspress-link data-href="<?php echo esc_attr( $href ); ?>" data-method="POST" class="anspress-apq-item-action anspress-apq-item-action-view"><?php esc_html_e( 'Delete', 'anspress-question-answer' ); ?></anspress-link>
