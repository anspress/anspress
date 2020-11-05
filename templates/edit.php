<?php

/**
 * Edit page
 *
 * @link https://anspress.net
 * @since 2.0.1
 * @license GPL 2+
 * @package AnsPress
 */

if ( $editing_post->post_type == 'question' ) {
	ap_edit_question_form();
} elseif ( $editing_post->post_type == 'answer' ) {
	ap_edit_answer_form( $editing_post->post_parent );
}
