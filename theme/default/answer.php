<div id="answer_<?php echo get_the_ID(); ?>" class="answer<?php echo ap_is_best_answer(get_the_ID()) ? ' selected' : ''; ?>" data-id="<?php echo get_the_ID(); ?>" itemprop="suggestedAnswer<?php echo ap_is_best_answer(get_the_ID()) ? ' acceptedAnswer' : ''; ?>" itemtype="http://schema.org/Answer" itemscope="">
	<div class="ap-content clearfix">		
		<div class="ap-avatar">
			<a href="<?php echo ap_user_link(get_the_author_meta('ID')); ?>">
				<?php echo get_avatar( get_the_author_meta( 'user_email' ), ap_opt('avatar_size_qanswer') ); ?>
			</a>
			<?php echo ap_select_answer_btn_html(get_the_ID()); ?>
		</div>
		<div class="ap-content-inner no-overflow">
			<div class="ap-amainc">
				<div class="ap-user-meta">
					<div class="ap-single-vote">
							<?php ap_vote_html(); ?>
					</div>
					<div class="ap-meta">
						<?php
							printf( __( '<a href="'.ap_user_link(get_the_author_meta('ID')).'" class="author"><span>%s</span></a> <span class="when">answered about %s ago</span>', 'ap' ), ap_user_display_name(false, true) , ap_human_time( get_the_time('U')));
						?>							
					</div>			
				</div>
				<div class="answer-content" itemprop="text">
					<?php the_content(); ?>
				</div>			
				<ul class="ap-user-actions clearfix">
					<li><?php ap_edit_a_btn_html() ; ?></li>					
					<li><?php ap_comment_btn_html(); ?></li>
					<li><?php ap_flag_btn_html(); ?></li>
					<li><?php ap_post_delete_btn_html(); ?></li>
				</ul>
			</div>
			<div class="ap-qfooter">
				<?php
					$history = ap_get_latest_history_html(get_the_ID(), true, true);
					if($history):
				?>				
					<div class="ap-tlitem">
						<?php echo ap_get_latest_history_html(get_the_ID(), true, true); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php comments_template(); ?>
		</div>			
	</div>	
</div>