<div id="ap-lists" class="clearfix">
	<?php ap_questions_tab(get_permalink()); ?>
	<?php if ( $questions->have_posts() ) : ?>
		<div class="question-list">
			<?php
				
				/* Start the Loop */
				while ( $questions->have_posts() ) : $questions->the_post();
					global $post;
					include(ap_get_theme_location('content-list.php'));
				endwhile;
			?>
		</div>
	<?php 
		ap_pagination();
	?>
	<?php
		else : 
			include(ap_get_theme_location('content-none.php'));
		endif; 
	?>	
</div>