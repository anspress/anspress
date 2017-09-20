<?php
/**
 * Form and controls of ask form
 *
 * @link         https://anspress.io
 * @license      GPL-3.0+
 * @package      AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Generate answer form.
 *
 * @param  mixed   $question_id  Question iD.
 * @param  boolean $editing      true if post is being edited.
 * @return void
 */
function ap_answer_form( $question_id, $editing = false ) {
	// if ( ! ap_user_can_answer( $question_id ) && ! $editing ) {
	// 	return;
	// }

	anspress()->get_form( 'answer' )->generate();
}

/**
 * Insert and update answer.
 *
 * @param  array $question_id Question ID.
 * @param  array $args     Answer arguments.
 * @param  bool  $wp_error Return wp error.
 * @return bool|object|int
 */
function ap_save_answer( $question_id, $args, $wp_error = false) {
	$question = ap_get_post( $question_id );

	if ( isset( $args['is_private'] ) && $args['is_private'] ) {
		$args['post_status'] = 'private_post';
	}

	$args = wp_parse_args( $args, array(
		'post_title' 		  => $question->post_title,
		'post_author' 	  => get_current_user_id(),
		'post_status' 	  => 'publish',
		'post_name' 		  => '',
		'comment_status'  => 'open',
	) );

	/**
	 * Filter question description before saving.
	 * @param string $content Post content.
	 * @since unknown
	 * @since 3.0.0 Moved from process-form.php
	 */
	$args['post_content'] = apply_filters( 'ap_form_contents_filter', $args['post_content'] );
	$args['post_type'] 	  = 'answer';
	$args['post_parent']  = $question_id;

	if ( isset( $args['ID'] ) ) {
		/**
		 * Can be used to modify `$args` before updating answer
		 * @param array $args Answer arguments.
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_pre_update_answer', $args );
	} else {
		/**
		 * Can be used to modify args before inserting answer.
		 * @param array $args Answer arguments.
		 * @since 2.0.1
		 */
		$args = apply_filters( 'ap_pre_insert_answer', $args );
	}

	$post_id = wp_insert_post( $args, true );

	if ( true === $wp_error && is_wp_error( $post_id ) ) {
		return $post_id;
	}

	if ( $post_id ) {
		$qameta_args = [ 'last_updated' => current_time( 'mysql' ) ];

		if ( isset( $args['anonymous_name'] ) ) {
			$qameta_args['fields'] = [ 'anonymous_name' => $args['anonymous_name'] ];
		}

		ap_insert_qameta( $post_id, $qameta_args );
		$activity_type = isset( $args['ID'] ) ? 'edit_answer' : 'new_answer';

		// Add answer activity meta.
		ap_update_post_activity_meta( $post_id, $activity_type, get_current_user_id() );
		ap_update_post_activity_meta( $question->ID, $activity_type, get_current_user_id() );

		if ( ap_isset_post_value( 'ap-medias' ) ) {
			$ids = ap_sanitize_unslash( 'ap-medias', 'r' );
			ap_set_media_post_parent( $ids, $post_id );
		}
	}
	return $post_id;
}

function ap_answer_post_ajax_response( $question_id, $answer_id ) {
	$question = ap_get_post( $question_id );
	// Get existing answer count.
	$current_ans = ap_count_published_answers( $question_id );

	if ( $current_ans == 1 ) {
		global $post;
		$post = $question;
		setup_postdata( $post );
	} else {
		global $post;
		$post = ap_get_post( $answer_id );
		setup_postdata( $post );
	}

	ob_start();
	global $answers;
	global $withcomments;
	$withcomments = true;

	$answers = ap_get_answers( array( 'p' => $answer_id ) );
	while ( ap_have_answers() ) : ap_the_answer();
		ap_get_template_part( 'answer' );
	endwhile;

	$html = ob_get_clean();
	$count_label = sprintf( _n( '%d Answer', '%d Answers', $current_ans, 'anspress-question-answer' ), $current_ans );

	$result = array(
		'success'       => true,
		'ID'            => $answer_id,
		'action'        => 'new_answer',
		'div_id'        => '#post-' . get_the_ID(),
		'can_answer'    => ap_user_can_answer( $post->ID ),
		'html'          => $html,
		'snackbar'      => [ 'message' => __( 'Answer submitted successfully', 'anspress-question-answer' ) ],
		'answersCount'  => [ 'text' => $count_label, 'number' => $current_ans ],
	);

	ap_ajax_json( $result );
}
