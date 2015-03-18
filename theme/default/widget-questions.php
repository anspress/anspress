<div class="ap-questions-widget clearfix">
	<?php 
		if ( $questions->have_posts() ) {
			/* Start the Loop */
			while ( $questions->have_posts() ) : $questions->the_post();
				$ans_count = ap_count_answer_meta();
				$net_vote = ap_net_vote();
				?>
				<div class="ap-question-item">
					<a class="ap-question-title"href="<?php the_permalink() ?>"><?php the_title() ?></a>
					<span class="ap-ans-count"><?php printf( _n('1 answer', '%d answers', $ans_count, 'ap'), $ans_count) ?></span>
					<span class="ap-vote-count"> | <?php printf( _n('1 vote', '%d votes', $net_vote, 'ap'), $net_vote) ?></span>
				</div>
				<?php
			endwhile;
		}else{
			_e('No related questions found.', 'ap');
		}
	?>	
</div>


