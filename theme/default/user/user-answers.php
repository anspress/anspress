<div id="ap-lists">
	<h3 class="ap-user-page-title clearfix">
		<?php echo ap_page_title() ?>
	</h3>
	<?php if ( ap_have_answers() ) : ?>
		<div class="ap-answers">
			<?php					
				while ( ap_have_answers() ) : ap_the_answer();
					ap_get_template_part('user/list-answer');
				endwhile ;
			?>
		</div>
	<?php ap_answers_the_pagination(); ?>
	<?php
		else : 
			include(ap_get_theme_location('content-none.php'));
		endif; 
	?>	
</div>