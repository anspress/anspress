<div id="ap-lists" class="clearfix">
	<h3 class="ap-user-page-title clearfix">
		<?php ap_get_template_part('list-head'); ?>
	</h3>
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