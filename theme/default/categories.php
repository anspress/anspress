<?php if(ap_opt('enable_categories')): ?>
<h1 class="entry-title">
		<?php if (!ap_opt('double_titles'))
		the_title(); 
		?>
	<a class="ap-btn ap-ask-btn-head pull-right" href="<?php echo ap_get_link_to('ask') ?>"><?php _e('Ask Question'); ?></a>
</h1>
<div id="ap-categories" class="clearfix">
	<ul class="ap-term-list ap-inline-list">
		<?php foreach($categories as $key => $category) : ?>
			<li>
				<div class="ap-term-list-inner">
					<a class="term-title" href="<?php echo get_category_link( $category );?>"><span>
						<?php echo $category->name; ?>
					</span></a>
					
					<span> &times; <?php echo $category->count; ?></span>		
					<p><?php echo ap_truncate_chars($category->description, 70); ?></p>
					<?php
						$sub_cat_count = count(get_term_children( $category->term_id, 'question_category' ));
						
						if($sub_cat_count >0){
							echo '<div class="ap-term-sub">';
							echo '<div class="sub-cat-count">' .$sub_cat_count.' '.__('Sub Categories', 'ap') .'</div>';
							
							ap_child_cat_list($category->term_id);
							echo '</div>';
						}
					?>
				</div>				
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php else: ?>
	<div class="ap-tax-disabled">
		<?php _e('Categories are disabled', 'ap'); ?>
	</div>
<?php endif; ?>