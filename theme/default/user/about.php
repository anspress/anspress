<?php
/**
 * Display user about page
 *
 * @link http://wp3.in
 * @since unknown
 * @package AnsPress
 */
?>
<div class="ap-about">
	<div class="row">
		<div class="col-md-8">
			<div class="ap-about-block">
				<h3><?php echo ap_icon('reputation', true); ?> <?php _e('Reputation', 'ap'); ?></h3>
				<div class="ap-about-block-c">		
					<div class="ap-about-rep clearfix">
						<div class="ap-pull-left">
							<span class="ap-about-rep-label"><?php _e('Total', 'ap'); ?></span>
							<span class="ap-about-rep-count"><?php ap_user_the_reputation(); ?></span>
						</div>
						<div class="ap-about-rep-chart">
							<span data-action="ap_chart" data-type="bar" data-peity='{"fill" : ["#8fc77e"], "height": 45, "width": "100%"}'><?php echo ap_user_get_28_days_reputation(); ?></span>		
						</div>
						<div class="ap-user-rep">
							<?php
								if(ap_has_reputations(array('number' => 5))){
									while ( ap_reputations() ) : ap_the_reputation();
										ap_get_template_part('user/reputation-content');
									endwhile;
								}
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="ap-about-block">
				<h3><?php echo ap_icon('thumbs-up-down', true); ?> <?php _e('Votes', 'ap'); ?></h3>
				<div class="ap-about-block-c">		
					<div class="ap-about-votes row">
						<div class="col-md-6">
							<span class="ap-about-vote-label"><?php printf(__('%d votes received', 'ap'), ap_user_total_votes_received()); ?></span>
							<span data-action="ap_chart" data-type="donut" data-peity='{ "fill": ["#9087FF", "#eeeeee"],   "innerRadius": 20, "radius": 25 }'><?php echo ap_user_votes_received_percent(); ?>/100</span>
							<span class="ap-vote-count"><b><?php ap_user_the_meta('__up_vote_received'); ?></b><?php _e('up', 'ap'); ?></span>
							<span class="ap-vote-count"><b><?php ap_user_the_meta('__down_vote_received'); ?></b><?php _e('down', 'ap'); ?></span>	
						</div>
						<div class="col-md-6">
							<span class="ap-about-vote-label"><?php printf(__('%d votes casted', 'ap'), ap_user_total_votes_casted()); ?></span>
							<span data-action="ap_chart" data-type="donut" data-peity='{ "fill": ["#9087FF", "#eeeeee"],   "innerRadius": 20, "radius": 25 }'><?php echo ap_user_votes_casted_percent(); ?>/100</span>
							<span class="ap-vote-count"><b><?php ap_user_the_meta('__up_vote_casted'); ?></b><?php _e('up', 'ap'); ?></span>
							<span class="ap-vote-count"><b><?php ap_user_the_meta('__down_vote_casted'); ?></b><?php _e('down', 'ap'); ?></span>							
						</div>						
					</div>
				</div>
			</div>
			<div class="ap-about-block top-answers">
				<h3><?php echo ap_icon('answer', true); ?> <?php _e('Top Answers', 'ap'); ?></h3>
				<div class="ap-about-block-c">		
					<?php ap_get_answers(array('author' => ap_get_displayed_user_id(), 'showposts' => 5, 'sortby' => 'voted')); ?>		
					<?php if(ap_have_answers()): ?>
						<?php while ( ap_answers() ) : ap_the_answer(); ?>
							<?php ap_get_template_part('user/list-answer'); ?>
						<?php endwhile; ?>
						<div class="ap-user-posts-footer">
							<?php printf(__('More answers by %s', 'ap'), ap_user_get_the_display_name()); ?>
							<a href="<?php echo ap_user_link(ap_get_displayed_user_id(), 'answers'); ?>"><?php _e('view', 'ap'); ?>&rarr;</a>
						</div>
					<?php else: ?>
						<?php _e('No answer posted yet!', 'ap'); ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="ap-about-block top-answers">
				<h3><?php echo ap_icon('question', true); ?> <?php _e('New questions', 'ap'); ?></h3>
				<div class="ap-about-block-c">		
					<?php ap_get_questions(array('author' => ap_get_displayed_user_id(), 'showposts' => 5, 'sortby' => 'newest')); ?>		
					
					<?php if(ap_have_questions()): ?>

						<?php while ( ap_questions() ) : ap_the_question(); ?>
							<?php ap_get_template_part('user/list-question'); ?>
						<?php endwhile; ?>
						
						<div class="ap-user-posts-footer">
							<?php printf(__('More questions by %s', 'ap'), ap_user_get_the_display_name()); ?>
							<a href="<?php echo ap_user_link(ap_get_displayed_user_id(), 'questions'); ?>"><?php _e('view', 'ap'); ?>&rarr;</a>
						</div>
					<?php else: ?>

						<?php _e('No question asked yet!', 'ap'); ?>

					<?php endif; ?>
					
					<?php wp_reset_postdata(); ?>
				</div>
			</div>		
		</div>
		<div class="col-md-4">
			<ul class="ap-about-stats">
				<li><?php echo ap_icon('answer', true); ?><?php printf(__('%d answers, %d selected', 'ap'), ap_user_get_the_meta('__total_answers'), ap_user_get_the_meta('__best_answers')); ?></li>
				<li><?php echo ap_icon('question', true); ?><?php printf(__('%d questions, %d solved', 'ap'), ap_user_get_the_meta('__total_questions'), ap_user_get_the_meta('__solved_answers')); ?></li>
				<li><?php echo ap_icon('clock', true); ?><?php printf(__('Member for %s', 'ap'), ap_user_get_member_for()); ?></li>
				<li><?php echo ap_icon('eye', true); ?><?php printf(__('%d profile views', 'ap'), ap_user_get_the_meta('__profile_views')); ?></li>
				<li><?php echo ap_icon('clock', true); ?><?php printf(__('Last seen %s ago', 'ap'), ap_human_time(ap_user_get_the_meta('__last_active'), false)); ?></li>
			</ul>
			<?php dynamic_sidebar( 'ap-user-about' ); ?>
		</div>
	</div>
</div>