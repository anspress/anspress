<?php
/**
 * Edit post button.
 *
 * @package AnsPress
 * @since   5.0.0
 */

use AnsPress\Classes\Auth;
use AnsPress\Classes\PostHelper;
use AnsPress\Classes\Router;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check that post argument is set.
if ( ! isset( $args ['post'] ) ) {
	throw new InvalidArgumentException( 'Post argument is required.' );
}

if ( ! PostHelper::isValidPostType( $post->post_type ) ) {
	return;
}

$context = array( $post->post_type => $post );

if ( ! Auth::currentUserCan( $post->post_type . ':update', $context ) ) {
	return;
}

?>
<?php if ( 'question' === $post->post_type ) : ?>
	<a href="<?php echo esc_url( ap_post_edit_link( $post ) ); ?>"class="anspress-apq-item-action anspress-apq-item-action-delete"><?php esc_html_e( 'Edit', 'anspress-question-answer' ); ?></a>
<?php else : ?>
	<?php
		$href = Router::route(
			'v1.answers.actions',
			array(
				'answer_id' => $post->ID,
				'action'    => 'load-answer-edit-form',
			)
		);
	?>
	<anspress-link data-href="<?php echo esc_attr( $href ); ?>" data-method="POST" class="anspress-apq-item-action anspress-apq-item-action-edit"><?php esc_html_e( 'Edit', 'anspress-question-answer' ); ?></anspress-link>
<?php endif; ?>
