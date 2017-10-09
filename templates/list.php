<div id="ap-lists" class="clearfix">
	<?php if ( have_posts() ) : ?>
		<div class="question-list">
		<?php
			/* Start the Loop */
			while ( have_posts() ) : the_post();
				ap_get_template_part( 'question-list-item.php' );
			endwhile;
		?>
		</div>
	<?php ap_pagination(); ?>

	<?php else : ?>
		<p class="ap-no-questions">
			<?php esc_attr_e( 'There are no questions matching your query or you do not have permission to read them.', 'anspress-question-answer' ); ?>
		</p>
	<?php endif; ?>

</div>

