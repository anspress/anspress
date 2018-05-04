<?php if ( $questions->have_posts() ) :
	/* Start the Loop */
	while ( $questions->have_posts() ) :
		$questions->the_post();
		global $post;
		?>
		<article class="clearfix">
			<div class="ap-avatar">
				<a href="<?php echo ap_user_link(); ?>">
					<?php echo get_avatar( get_the_author_meta( 'user_email' ), 25 ); ?>
				</a>
			</div>
			<div class="summery">
				<a class="question-hyperlink" href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a>
				<?php // echo ap_get_question_label(null, true); ?>
			</div>
			<div class="ans-count"><span><?php echo ap_count_ans_meta() . ' ans'; ?></span></div>
		</article><!-- list item -->
		<?php
	endwhile;
	endif;
?>
<a class="ap-btn ap-btn-blue ap-view-all-btn block" href="<?php echo get_category_link( $category ); ?>"><?php _e( 'View all questions', 'anspress-question-answer' ); ?></a>
