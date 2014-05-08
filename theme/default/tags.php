<div id="ap-tags" class="ap-container clearfix">
<?php

	$paged 			= (get_query_var('paged')) ? get_query_var('paged') : 1;
	$per_page    	= ap_opt('tags_per_page');
	$total_terms 	= wp_count_terms('question_tags'); 	
	$offset      	= $per_page * ( $paged - 1) ;
	$args = array(
		'number'		=> $per_page,
		'offset'       	=> $offset,
		'hide_empty'    => false, 
	);
	
	$tags = get_terms( 'question_tags' , $args); 
		
	echo '<ul class="ap-tags-list">';
		foreach($tags as $key => $tag) :

			echo '<li>';
			echo '<div class="tag-list-inner">';
			echo '<a class="ap-tags" href="'.get_category_link( $tag ).'"><span>';
			echo $tag->name;						
			echo '</span></a>';
			echo '<a class="aicon-rss feed-link" href="' . get_term_feed_link($tag->term_id, 'question_tags') . '" title="Subscribe to '. $tag->name .'" rel="nofollow"></a>';
			echo '<span> &times; '.$tag->count.'</span>';			
			echo '<p>'.ap_truncate_chars($tag->description, 60).'</p>';						
			echo '</div>';
			echo '</li>';

		endforeach;
	echo'</ul>';
	
	ap_pagination(ceil( $total_terms / $per_page ), $range = 1, $paged);
?>

</div>
