<div id="answer_<?php echo get_the_ID(); ?>" class="answer">
	<div class="ap-content">
		<div class="vote-single pull-left">
			<?php ap_vote_html(get_the_ID()); ?>
		</div>	
		<div class="content-inner">
			<div class="user-header clearfix">
				<div class="ap-avatar">
					<?php echo get_avatar( get_the_author_meta( 'user_email' ), 30 ); ?>
				</div>					
				<div class="user-meta">
					<?php 
						printf( __( '%s <span class="when">answered about %s ago</span>	', 'ap' ), ap_user_display_name() , ap_human_time( get_the_time('U')));
					?>							
				</div>
				
			</div>
			<div class="answer-content">
				<?php the_content(); ?>
			</div>			
			<ul class="user-actions">
				<li>
					<a href="<?php echo ap_answer_edit_link(); ?>" class="btn edit-btn aicon-edit" title="<?php _e('Edit this question', 'ap'); ?>"><?php _e('Edit', 'ap') ; ?></a>
				</li>				
				<li><?php ap_comment_btn_html(); ?></li>
				<li class="pull-right"><?php ap_flag_btn_html(); ?></li>
			</ul>			
		</div>	
		<?php comments_template(); ?>		
	</div>	
</div>