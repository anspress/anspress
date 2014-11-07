<div id="ap-lists" class="clearfix">
<?php if (!ap_opt('double_titles')):?>
	<div class="ap-tax-description">		
		<h2 class="entry-title"><?php printf(__('Tag: %s', 'ap'), $tag->name); ?> <span class="ap-tax-item-count"><?php printf( _n('1 Question', '%s Questions', $tag->count, 'ap'),  $tag->count); ?></span></h2>
		<?php if($tag->description !=''): ?>
			<p class="ap-tag-description"><?php echo $tag->description; ?></p>
		<?php else: ?>
			<p class="ap-tag-description"><?php _e('-- No description --', 'ap'); ?></p>
		<?php endif; ?>
	</div>
	<?php endif;?>
	<?php if ( $question->have_posts() ) : ?>
		<?php ap_questions_tab(); ?>
		<div class="question-list">
			<?php				
				/* Start the Loop */
				while ( $question->have_posts() ) : $question->the_post();
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
