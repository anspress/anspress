<?php
/**
 * AnsPress comments handling.
 *
 * @author       Rahul Aryan <rah12@live.com>
 * @license      GPL-3.0+
 * @link         https://anspress.net
 * @copyright    2014 Rahul Aryan
 * @package      AnsPress
 * @subpackage   Comments Hooks
 */

// phpcs:disable Universal.Files.SeparateFunctionsFromOO.Mixed

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if comment delete is locked.
 *
 * @param  integer $comment_id     Comment ID.
 * @return bool
 * @since  3.0.0
 */
function ap_comment_delete_locked( $comment_id ) {
	$comment       = get_comment( $comment_id );
	$commment_time = mysql2date( 'U', $comment->comment_date_gmt ) + (int) ap_opt( 'disable_delete_after' );
	return time() > $commment_time;
}
