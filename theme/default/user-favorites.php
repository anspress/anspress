<div id="ap-user-favorites">
	<?php if ( $question->have_posts() ) : ?>
		<div class="question-list">
	<?php

		/* Start the Loop */
		while ( $question->have_posts() ) : $question->the_post();
			include(ap_get_theme_location('content-list.php'));
		endwhile;
	?>
		</div>
	<?php ap_pagination('', 2, $paged, $question); ?>
	<?php
		else : 
			_e('No favourite questions', 'ap');
		endif; 
	?>
</div>