<?php
/**
 * AnsPress questions widget template.
 *
 * @link https://anspress.net/anspress
 * @since 2.0.1
 * @author Rahul Aryan <rah12@live.com>
 * @package AnsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ap-questions-widget clearfix">
	<?php if ( ap_have_questions() ) : ?>
		<?php
		while ( ap_have_questions() ) :
			ap_the_question();
			?>
			<div class="ap-question-item">
				<a class="ap-question-title" href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a>
				<span class="ap-ans-count">
					<?php
						// translators: %d is total answer count of a question.
						echo esc_attr( sprintf( _n( '%d Answer', '%d Answers', ap_get_answers_count(), 'anspress-question-answer' ), ap_get_answers_count() ) );
					?>
				</span>
				|
				<span class="ap-vote-count">
					<?php
						// translators: %d is total votes count.
						echo esc_attr( sprintf( _n( '%d Vote', '%d Votes', ap_get_votes_net(), 'anspress-question-answer' ), ap_get_votes_net() ) );
					?>
				</span>
			</div>
		<?php endwhile; ?>
	<?php else : ?>
		<?php esc_attr_e( 'No questions found.', 'anspress-question-answer' ); ?>
	<?php endif; ?>
</div>
