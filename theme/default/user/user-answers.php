<div id="ap-lists">
	<?php //ap_questions_tab(ap_user_link(ap_get_displayed_user_id(), 'ans')); ?>
	<?php if ( ap_have_answers() ) : ?>
		<div class="ap-answers">
			<?php					
				while ( ap_have_answers() ) : ap_the_answer();
					include(ap_get_theme_location('answer-list.php'));
				endwhile ;
			?>
		</div>
	<?php ap_answers_the_pagination(); ?>
	<?php
		else : 
			include(ap_get_theme_location('content-none.php'));
		endif; 
	?>	
</div>