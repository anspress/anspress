<?php
/**
 * Display question list header
 * Shows sorting, search, tags, category filter form. Also shows a ask button.
 *
 * @package AnsPress
 * @author  Rahul Aryan <rah12@live.com>
 */

?>

<div class="ap-list-head clearfix">
	<div class="pull-right">
		<?php ap_ask_btn(); ?>
	</div>

	<?php ap_get_template_part( 'search-form' ); ?>
	<?php ap_list_filters(); ?>
</div>


<?php
/**
 * Display an alert showing count for unpublished questions.
 *
 * @since 4.1.13
 */

$questions_count = (int) get_user_meta( get_current_user_id(), '__ap_unpublished_questions', true );

if ( $questions_count > 0 ) {
	$text = sprintf( _n( '%d question is', '%d questions are', $questions_count, 'anspress-question-answer' ), $questions_count );

	echo '<div class="ap-unpublished-alert ap-alert warning"><i class="apicon-pin"></i>';
	printf(
		// Translators: Placeholder contain link to unpublished questions.
		esc_html__( 'Your %s unpublished. ', 'anspress-question-answer' ),
		'<a href="' . esc_url( ap_get_link_to( '/' ) ) . '?unpublished=true">' . esc_attr( $text ) . '</a>'
	);
	echo '</div>';
}
?>
