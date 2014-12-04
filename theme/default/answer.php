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
							$a=" e ";$b=" ";$time=get_option('date_format').$b.get_option('time_format').$a.get_option('gmt_offset');
							printf( '<a href="%6$s" class="author"><span>%s</span></a> <span>%5$s</span> <a href="#answer_%7$s"><time datetime="%3$s" title="%3$s" is="relative-time">%s %4$s</time></a>',
							ap_user_display_name(false, true) ,
							ap_human_time( get_the_time('U')),
							get_the_time($time),
							__('ago','ap'),
							__('answered about','ap'),
							ap_user_link(get_the_author_meta('ID')),
							get_the_ID()
							);
						?>						
					</div>			
				</div>
				<div class="answer-content" itemprop="text">
					<?php
						the_content();
					?>
				</div>			
				<ul class="ap-user-actions clearfix">
					<li><?php ap_edit_a_btn_html() ; ?></li>					
					<li><?php ap_comment_btn_html(); ?></li>
					<li><?php ap_flag_btn_html(); ?></li>
					<li><?php ap_post_delete_btn_html(); ?></li>
				</ul>
				<?php ap_post_edited_time();?>
			</div>
			<?php
				$history = ap_get_latest_history_html(get_the_ID(), true, true);
				if($history):
			?>	
			<div class="ap-qfooter">							
				<div class="ap-tlitem">
					<?php echo ap_get_latest_history_html(get_the_ID(), true, true); ?>
				</div>				
			</div>
			<?php endif; ?>
			<?php comments_template(); ?>
		</div>			
	</div>	
</div>