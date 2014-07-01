<div id="answer_<?php echo get_the_ID(); ?>" class="answer">
	<div class="ap-content clearfix">		
		<div class="ap-single-vote"><?php ap_vote_html(); ?></div>	
		<div class="ap-content-inner">						
			<div class="ap-user-meta">
				<div class="ap-avatar">
					<?php echo get_avatar( get_the_author_meta( 'user_email' ), ap_opt('avatar_size_question') ); ?>
				</div>
				<div class="ap-meta">
					<?php 
						printf( __( '%s <span class="when">asked about %s ago</span>', 'ap' ), ap_user_display_name() , ap_human_time( get_the_time('U')));
					?>							
				</div>			
			</div>
			<div class="answer-content">
				<?php the_content(); ?>
			</div>			
			<ul class="ap-user-actions clearfix ">
				<li>
					<a href="<?php echo ap_answer_edit_link(); ?>" class="edit-btn" title="<?php _e('Edit this question', 'ap'); ?>"><?php _e('Edit', 'ap') ; ?></a>
				</li>				
				<li><?php ap_comment_btn_html(); ?></li>
				<li><?php ap_flag_btn_html(); ?></li>
			</ul>
			<?php comments_template(); ?>	
		</div>			
	</div>	
</div>