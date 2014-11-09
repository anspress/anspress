<h1 class="entry-title">
	<?php if (!ap_opt('double_titles')){
	the_title();}
	else {
	echo '<span style="visibility:hidden">'.get_the_title().'</span>';}
	?>
	<?php ap_ask_btn(); ?>
</h1>
<?php dynamic_sidebar( 'ap-top' ); ?>
<div id="ap-lists" class="clearfix">
	<?php ap_questions_tab(); ?>
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
	<?php ap_pagination('', 2, $paged, $question); ?>
	<?php
		else : 
			include(ap_get_theme_location('content-none.php'));
		endif; 
	?>	
</div>


