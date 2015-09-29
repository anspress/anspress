<div class="ap-questions-widget clearfix">
	<?php 
		if ( ap_have_questions() ) {
			/* Start the Loop */
			while ( ap_questions() ) : ap_the_question();
				$ans_count = ap_question_get_the_answer_count();
				$net_vote = ap_question_get_the_net_vote();
				?>
				<div class="ap-question-item">
					<a class="ap-question-title"href="<?php ap_question_the_permalink() ?>"><?php the_title() ?></a>
					<span class="ap-ans-count"><?php printf( _n('1 answer', '%d answers', $ans_count, 'ap'), $ans_count) ?></span>
					<span class="ap-vote-count"> | <?php printf( _n('1 vote', '%d votes', $net_vote, 'ap'), $net_vote) ?></span>
				</div>
				<?php
			endwhile;
		}else{
			_e('No questions found.', 'ap');
		}
	?>	
</div>


