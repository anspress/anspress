<div id="ap-lists">
	<h3 class="ap-user-page-title clearfix">
		<?php echo ap_page_title() ?>		
	</h3>
	<?php if ( ap_have_questions() ) : ?>
		<?php ap_get_template_part('list-head'); ?>
		<div class="ap-questions">
			<?php
				/* Start the Loop */
				while ( ap_questions() ) : ap_the_question();
					ap_get_template_part('user/list-question');
				endwhile;
			?>
		</div>
	<?php ap_questions_the_pagination(); ?>
	<?php
		else :
			_e('No question asked by this user yet.', 'anspress-question-answer');
		endif;
	?>
</div>