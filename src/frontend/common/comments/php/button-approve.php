<?php
/**
 * Approve comment button.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Auth;
use AnsPress\Classes\Router;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check that comment argument is set.
if ( ! isset( $comment ) ) {
	throw new InvalidArgumentException( 'Comment argument is required.' );
}

// Check if comment is approved.
if ( 1 === (int) $comment->comment_approved ) {
	return;
}

// Check permission.
if ( ! Auth::currentUserCan( 'comment:approve', array( 'comment' => $comment ) ) ) {
	return;
}

$approveHref = Router::route(
	'v1.comments.actions',
	array(
		'comment_id' => $comment->comment_ID,
		'action'     => 'approve-comment',
	)
);

?>
<anspress-link
	data-href="<?php echo esc_attr( $approveHref ); ?>"
	data-method="POST"
	class="anspress-comments-edit"><?php esc_html_e( 'Approve', 'anspress-question-answer' ); ?></anspress-link>
