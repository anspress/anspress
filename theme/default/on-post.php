<h1 class="entry-title">
	<?php if (!ap_opt('double_titles')):?>
	<?php printf(__('Discussion on "%s"', 'ap'), get_the_title()); ?>
	<?php endif;?>
	<?php ap_ask_btn(get_the_ID()); ?>
</h1>
<div id="ap-on-post" class="clearfix">
	<?php ///ap_questions_tab(); ?>
	<?php if ( $question->have_posts() ) : ?>
		<div class="question-list">
	<?php
		
		/* Start the Loop */
		while ( $question->have_posts() ) : $question->the_post();
			global $post;
			include(ap_get_theme_location('content-list.php'));
		endwhile;
	?>
		</div>	
	<?php
		else : 
			include(ap_get_theme_location('content-none.php'));
		endif; 
	?>	
</div>