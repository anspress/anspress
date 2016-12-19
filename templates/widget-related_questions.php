<div class="ap-rq-widget clearfix">
	<?php
		if ( ap_have_questions() ) {
			/* Start the Loop */
			while ( ap_have_questions() ) : ap_the_question();
				?>
				<div class="ap-rq-post">
					<a href="<?php the_permalink() ?>"><?php the_title() ?></a>
					<span class="ap-ans-count"><?php printf( _n('1 Answer', '%d Answers', ap_get_answers_count(), 'anspress-question-answer'), ap_get_answers_count()) ?></span>
					|
					<span class="ap-vote-count"><?php printf( _n('1 Vote', '%d Votes', ap_get_votes_net(), 'anspress-question-answer'), ap_get_votes_net()) ?></span>
				</div>
				<?php
			endwhile;
		}else{
			_e('No related questions found.', 'anspress-question-answer');
		}
	?>	
</div>


