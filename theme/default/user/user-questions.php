<div id="ap-lists">
	<?php ap_questions_tab(ap_user_link(ap_get_displayed_user_id(), 'questions')); ?>
	<?php if ( ap_have_questions() ) : ?>
		<div class="ap-questions">
			<?php					
				/* Start the Loop */
				while ( ap_questions() ) : ap_the_question();
					global $post;
					include(ap_get_theme_location('content-list.php'));
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