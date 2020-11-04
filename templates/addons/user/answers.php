<?php
/**
 * Display answers list
 *
 * This template is used in base page, category, tag , etc
 *
 * @link https://anspress.net
 * @since 4.0.0
 *
 * @package AnsPress
 */

global $answers;
?>

	<?php if ( ap_have_answers() ) : ?>
		<div id="ap-bp-answers">
		<?php
			/* Start the Loop */
		while ( ap_have_answers() ) :
			ap_the_answer();
			ap_get_template_part( 'addons/user/answer-item' );
			endwhile;
		?>
		</div>
		<?php
		if ( $answers->max_num_pages > 1 ) {
			$args = wp_json_encode(
				[
					'ap_ajax_action' => 'user_more_answers',
					'__nonce'        => wp_create_nonce( 'loadmore-answers' ),
					'type'           => 'answers',
					'current'        => 1,
					'user_id'        => get_queried_object_id(),
				]
			);

			echo '<a href="#" class="ap-bp-loadmore ap-btn" ap-loadmore="' . esc_js( $args ) . '">' . esc_attr__( 'Load more answers', 'anspress-question-answer' ) . '</a>';
		}
		?>

		<?php
		else :
			_e( 'No answer posted by this user.', 'anspress-question-answer' );
		endif;
	?>
