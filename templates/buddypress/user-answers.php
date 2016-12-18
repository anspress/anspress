<div id="ap-lists" class="clearfix">
	<?php if ( $answers->have_posts() ) : ?>
		<div class="question-list">
			<?php

				/* Start the Loop */
				while ( $answers->have_posts() ) : $answers->the_post();
					global $post;
					include(ap_get_theme_location('answer-list.php'));
				endwhile;
			?>
		</div>
	<?php
		ap_pagination(false, $answers->max_num_pages);
	?>
	<?php
		else :
			include(ap_get_theme_location('content-none.php'));
		endif;
	?>
</div>