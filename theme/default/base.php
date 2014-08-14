<div id="ap-lists" class="clearfix">
	<?php if ( $question->have_posts() ) : ?>
		<div class="question-list">
	<?php
		ap_questions_tab();
		/* Start the Loop */
		while ( $question->have_posts() ) : $question->the_post();
			include(ap_get_theme_location('content-list.php'));
		endwhile;
	?>
		</div>
	<?php ap_pagination('', 2, $paged, $question); ?>
	<?php
		else : 
			include(ap_get_theme_location('content-none.php'));
		endif; 
	?>	
</div>


