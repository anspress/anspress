<?php 
	get_header(); 
?>
	<section id="ap-container" class="ap-container">
		<div class="ap-main">
			<?php include ap_get_page_template(); ?>
		</div>
		<div id="ap-sidebar">
			<?php get_sidebar(); ?>
		</div>
	</section>
<?php
get_footer();