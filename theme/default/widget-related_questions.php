<div class="ap-rq-widget clearfix">
	<?php
		if ( ap_have_questions() ) {
			/* Start the Loop */
			while ( ap_questions() ) : ap_the_question();
				$ans_count = ap_question_get_the_answer_count();
				$net_vote = ap_question_get_the_net_vote();
				?>
				<div class="ap-rq-post">
					<a href="<?php the_permalink() ?>"><?php the_title() ?></a>
					<span class="ap-ans-count"><?php printf( _n('1 Answer', '%d Answers', $ans_count, 'anspress-question-answer'), $ans_count) ?></span>
					|
					<span class="ap-vote-count"><?php printf( _n('1 Vote', '%d Votes', $net_vote, 'anspress-question-answer'), $net_vote) ?></span>
				</div>
				<?php
			endwhile;
		}else{
			_e('No related questions found.', 'anspress-question-answer');
		}
	?>	
</div>


