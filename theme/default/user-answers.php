<div id="ap-user-answers">
	<?php
		ap_answers_tab(ap_user_link(ap_user_page_user_id(), 'answers'));
		while ( $answers->have_posts() ) : $answers->the_post();
			include ap_get_theme_location('content-answer.php');
		endwhile;
	?>
</div>