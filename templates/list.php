<div id="ap-lists" class="clearfix">
	<?php if ( have_posts() ) : ?>
		<div class="question-list">
		<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post();
				ap_get_template_part( 'question-list-item' );
			endwhile;
		?>
		</div>
	<?php ap_pagination(); ?>

	<?php else : ?>
		<?php ap_get_template_part( 'no-questions' ); ?>
	<?php endif; ?>
</div>
