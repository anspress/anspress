<div id="answer_<?php echo get_the_ID(); ?>" class="answer<?php echo ap_is_best_answer(get_the_ID()) ? ' selected' : ''; ?>" data-id="<?php echo get_the_ID(); ?>">
	<div class="ap-content clearfix">		
		<div class="ap-content-inner">						
			<div class="ap-user-meta">
				<div class="ap-avatar">
					<?php echo get_avatar( get_the_author_meta( 'user_email' ), ap_opt('avatar_size_question') ); ?>
				</div>
				<div class="ap-meta">
					<?php 
						printf( __( '<span class="when">answered about %s ago</span>', 'ap' ), ap_human_time( get_the_time('U')));
					?>							
				</div>			
			</div>
			<div class="answer-content">
				<strong class="ap-answer-title"><?php echo get_the_title($answer->post->post_parent); ?></strong>
				<?php echo ap_truncate_chars(strip_tags(get_the_content()), 100); ?>
				<div class="ap-ans-action">
					<a href="<?php echo ap_answer_edit_link(); ?>" class="edit-btn" title="<?php _e('Edit this question', 'ap'); ?>"><?php _e('Edit', 'ap') ; ?></a>
					
					<?php ap_flag_btn_html(); ?>
					<?php ap_post_delete_btn_html(); ?>
				</div>
			</div>	
		</div>			
	</div>	
</div>