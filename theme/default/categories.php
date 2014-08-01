<?php if(ap_opt('enable_categories')): ?>
<div id="ap-categories" class="clearfix">
    <?php 
	$categories = get_terms( array('taxonomy' => 'question_category'), array( 'hide_empty' => false )); 
		
	echo '<ul class="ap-term-list">';
		foreach($categories as $key => $category) :
			if($category->parent == 0){
				echo '<li><div class="ap-taxo-inner">';
				echo '<a class="taxo-title" href="'.get_category_link( $category ).'">';
				echo $category->name;			
				echo '</a>';
				
				if(!empty($category->description))
					echo '<p class="desc">' . ap_truncate_chars($category->description, 75, '..').'</p>';
				
				echo '<div class="taxo-footer clearfix">';
				echo '<ul class="taxo-status clearfix">';
				
				$sub_cat_count = count(get_term_children( $category->term_id, 'question_category' ));
				
				if($sub_cat_count >0)
					echo '<li class="sub-cat-count"><a href="#">' .$sub_cat_count.' '.__('Sub Categories', 'ap') .'<i class="aicon-arrow-down"></i></a></li>';
					
				echo '<li class="question-count">' .$category->count.' '.__('Questions', 'ap') .'</li>';
				echo '<li class="cat-feed"><a class="aicon-rss cat-feed-link" href="' . get_term_feed_link($category->term_id, 'question_category') . '" title="Subscribe to '. $category->name .'" rel="nofollow"></a></li>';
				echo '</ul>';
				
				ap_child_cat_list($category->term_id);
				
				echo '</div>';
				
				
				echo '</div></li>';
			}
		endforeach;
	echo'</ul>';
	?>
</div>
<?php else: ?>
	<div class="ap-tax-disabled">
		<?php _e('Categories are disabled', 'ap'); ?>
	</div>
<?php endif; ?>