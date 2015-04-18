<div class="ap-user-posts">
	<h3>
		<?php _e('Top posts', 'ap'); ?>
		<?php ap_user_top_posts_tab(); ?>
	</h3>
	<?php
		if(ap_have_questions(array('author' => $user_id, 'showposts' => 10))){
			while ( $answers->have_posts() ) : $answers->the_post();
				?>
					<div class="ap-user-posts-item">
						<a href="#" class="ap-user-posts-title"><?php the_title(); ?></a>
						<a href="#" class="ap-user-posts-vote"><?php the_title(); ?></a>
					</div>
				<?php
			endwhile;
			
			wp_reset_postdata();
		}
	?>
</div>

