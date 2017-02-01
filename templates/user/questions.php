<?php
/**
 * User question template
 * Display user profile questions.
 *
 * @link https://anspress.io
 * @since 4.0.0
 * @package AnsPress
 */

?>

<?php if ( ap_have_questions() ) : ?>
	<div class="ap-questions">
		<?php
		/* Start the Loop */
		while ( ap_have_questions() ) : ap_the_question();
			ap_get_template_part( 'question-list-item' );
		endwhile;
		?>
	</div>

	<?php ap_questions_the_pagination(); ?>

<?php
	else :
		ap_get_template_part( 'content-none' );
	endif;
?>
