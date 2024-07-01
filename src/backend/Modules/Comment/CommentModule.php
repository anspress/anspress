<?php
/**
 * Answer module.
 *
 * @package AnsPress
 * @since   5.0.0
 */

namespace AnsPress\Modules\Comment;

use AnsPress\Classes\AbstractModule;
use AnsPress\Classes\RestRouteHandler;
use WP_REST_Server;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

/**
 * Comment module class.
 */
class CommentModule extends AbstractModule {
	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		add_filter( 'get_comment_link', array( $this, 'commentLink' ), 10, 2 );
		add_filter( 'preprocess_comment', array( $this, 'preprocessComment' ) );
	}

	/**
	 * Manipulate question and answer comments link.
	 *
	 * @param string     $link    The comment permalink with '#comment-$id' appended.
	 * @param WP_Comment $comment The current comment object.
	 */
	public function commentLink( $link, $comment ) {
		$_post = ap_get_post( $comment->comment_post_ID );

		if ( ! in_array( $_post->post_type, array( 'question', 'answer' ), true ) ) {
			return $link;
		}

		$permalink = get_permalink( $_post );
		return $permalink . '#/comment/' . $comment->comment_ID;
	}

	/**
	 * Change comment_type while adding comments for question or answer.
	 *
	 * @param array $commentdata Comment data array.
	 * @return array
	 * @since 5.0.0
	 */
	public function preprocessComment( $commentdata ) {
		if ( ! empty( $commentdata['comment_post_ID'] ) ) {
			$post_type = get_post_type( $commentdata['comment_post_ID'] );

			if ( in_array( $post_type, array( 'question', 'answer' ), true ) ) {
				$commentdata['comment_type'] = 'anspress';
			}
		}

		return $commentdata;
	}
}
