<?php
/**
 * User question template
 * Display user profile questions.
 *
 * @link https://anspress.io
 * @since 4.0.0
 * @package AnsPress
 *
 * @since 4.1.13 Fixed pagination issue when in main user page.
 */

global $wp;

?>

<?php if ( ap_have_questions() ) : ?>
	<div class="ap-questions">
		<?php
		/* Start the Loop */
		while ( ap_have_questions() ) :
			ap_the_question();
			ap_get_template_part( 'question-list-item' );
		endwhile;
		?>
	</div>

	<?php
		$paged = ( get_query_var( 'ap_paged' ) ) ? get_query_var( 'ap_paged' ) : 1;
		ap_questions_the_pagination( $paged );
	?>


<?php
	else :
		ap_get_template_part( 'content-none' );
	endif;
?>
