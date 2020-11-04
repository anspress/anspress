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

?>
<div id="ap-bp-question">
	<?php if ( ap_have_answers() ) : ?>
		<?php
			/* Start the Loop */
		while ( ap_have_answers() ) :
			ap_the_answer();
			ap_get_template_part( 'buddypress/answer-item' );
			endwhile;
		?>
	<?php ap_answers_the_pagination(); ?>
	<?php
		else :
			ap_get_template_part( 'content-none' );
		endif;
	?>
</div>
