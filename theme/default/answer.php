<div id="answer_<?php echo get_the_ID(); ?>" class="answer<?php echo ap_is_best_answer(get_the_ID()) ? ' selected' : ''; ?>" data-id="<?php echo get_the_ID(); ?>">
	<div class="ap-content clearfix">		
		<div class="ap-avatar">
			<?php echo get_avatar( get_the_author_meta( 'user_email' ), ap_opt('avatar_size_qanswer') ); ?>
			<?php echo ap_select_answer_btn_html(get_the_ID()); ?>
		</div>
		<div class="ap-content-inner no-overflow">
			<div class="ap-amainc">
				<div class="ap-user-meta">									
					<div class="ap-meta">
						<?php 
							printf( __( '%s <span class="when">answered about %s ago</span>', 'ap' ), ap_user_display_name() , ap_human_time( get_the_time('U')));
						?>							
					</div>			
				</div>
				<div class="answer-content">
					<?php echo apply_filters('the_content', get_the_content()); ?>
				</div>			
				<ul class="ap-user-actions clearfix ">
					<li>
						<div class="ap-single-vote">
							<?php ap_vote_html(); ?>
						</div>
					</li>
					<li>
						<a href="<?php echo ap_answer_edit_link(); ?>" class="edit-btn" title="<?php _e('Edit this question', 'ap'); ?>"><?php _e('Edit', 'ap') ; ?></a>
					</li>				
					<li><?php ap_comment_btn_html(); ?></li>
					<li><?php ap_flag_btn_html(); ?></li>
					<li><?php ap_post_delete_btn_html(); ?></li>
				</ul>
			</div>
			<?php comments_template(); ?>
		</div>			
	</div>	
</div>