<?php
/**
 * Display question list
 *
 * This template is used in base page, category, tag , etc
 *
 * @link https://anspress.io
 * @since 4.0.0
 *
 * @package AnsPress
 */

?>
<div id="ap-bp-question">
	<?php ap_get_template_part('list-head'); ?>
	<?php if ( ap_have_questions() ) : ?>
		<div class="ap-questions">
			<?php
				/* Start the Loop */
				while ( ap_have_questions() ) : ap_the_question();
					ap_get_template_part( 'content-list' );
				endwhile;
			?>
		</div>
	<?php ap_questions_the_pagination(); ?>
	<?php
		else :
			ap_get_template_part( 'content-none' );
		endif;
	?>
</div>
