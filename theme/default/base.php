<?php dynamic_sidebar( 'ap-top' ); ?>
<div class="row">
	<div id="ap-lists" class="<?php echo is_active_sidebar( 'ap-sidebar' ) && is_anspress() ? 'col-md-8' : 'col-md-12' ?>">
		<?php ap_questions_tab(get_permalink()); ?>
		<?php if ( $questions->have_posts() ) : ?>
			<div class="ap-questions">
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
	<?php if ( is_active_sidebar( 'ap-sidebar' ) && is_anspress()){ ?>
		<div class="ap-question-right col-md-4">
			<div class="ap-question-info">
				<?php dynamic_sidebar( 'ap-sidebar' ); ?>
			</div>
		</div>
	<?php } ?>
</div>


