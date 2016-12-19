<div id="ap-lists" class="clearfix">
	<?php if ( have_posts() ) : ?>
		<div class="question-list">
	<?php
		/* Start the Loop */
		while ( have_posts() ) : the_post();
			include(ap_get_theme_location('content-list.php'));
		endwhile;
	?>
		</div>
	<?php ap_pagination(); ?>
	<?php
		else : 
			include(ap_get_theme_location('content-none.php'));
		endif; 
	?>	
</div>

