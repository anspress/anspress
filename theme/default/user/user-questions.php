<div id="ap-lists">
	<h3 class="ap-user-page-title clearfix">
		<?php echo ap_page_title() ?>
		<?php ap_question_sorting(ap_user_link(ap_get_displayed_user_id(), 'questions')); ?>
	</h3>
	<?php if ( ap_have_questions() ) : ?>
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
			include(ap_get_theme_location('content-none.php'));
		endif; 
	?>	
</div>