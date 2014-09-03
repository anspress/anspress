<?php
/**
 * This file is responsible for displaying question page
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @package    AnsPress
 * @license    http://opensource.org/licenses/gpl-license.php  GNU Public License
 * @author    Rahul Aryan <rah12@live.com>
 */

while ( $question->have_posts() ) : $question->the_post();
if($question->post->post_status == 'publish'){

?>
<div id="ap-single" class="clearfix">
	<header class="ap-qhead clearfix">
		<div class="ap-avatar">
			<?php echo get_avatar( get_the_author_meta( 'user_email' ), ap_opt('avatar_size_qquestion') ); ?>
		</div>
		<div class="ap-qtitle-meta">
			<h1 class="ap-q-title">
				<?php the_title(); ?>
			</h1>
			<div class="ap-qtopmeta">
				<div class="ap-last-activity">
					<i class="ap-icon-clock ap-meta-icon"></i>
					<?php 							
						printf(
							'<span class="when">%s %s ago</span>',
							ap_get_latest_history_html(get_the_ID()),
							ap_human_time( mysql2date('U', ap_last_active(get_question_id())))
						); 
						ap_user_display_name();
					?>
				</div>
				<?php ap_favorite_html(); ?>		
			</div>
		</div>
	</header>
	<div class="ap-question-lr">		
		<div class="ap-question-left ap-tab-content">
			<div id="discussion" class="active">				
				<div id="question" role="main" class="ap-content question" data-id="<?php echo get_the_ID(); ?>">					
					<div class="ap-question-cells clearfix">											
						<div class="ap-content-inner">				
							<div class="ap-qmainc">
							<div class="ap-user-meta">								
								<div class="ap-meta">
									<?php 
										printf( __( '%s <span class="when">asked about %s ago</span>', 'ap' ), ap_user_display_name() , ap_human_time( get_the_time('U')));
									?>							
								</div>			
							</div>			
							<div class="question-content">
								<?php the_content(); ?>									
							</div>
							
							<ul class="ap-user-actions clearfix">
								<li><div class="ap-single-vote"><?php ap_vote_html(); ?></div></li>
								<li><?php ap_edit_q_btn_html(); ?></li>					
								<li><?php ap_comment_btn_html(); ?></li>					
								<li><?php ap_close_vote_html(); ?></li>	
								<li><?php ap_flag_btn_html(); ?></li>
								<li><?php ap_post_delete_btn_html(); ?></li>
							</ul>
							</div>
							<div class="ap-qfooter">
								<span class="ap-qtline"></span>
								<div class="ap-tlitem">
									<?php echo ap_get_latest_history_html(get_the_ID(), true, true); ?>
								</div>																
							</div>
							<?php comments_template(); ?>
						</div>						
					</div>		
				</div>
				
				<?php 
					if(ap_have_ans(get_the_ID())){ 
						ap_answers_list(get_the_ID(), 'voted');
					} 
				?>
					
				<?php 
					if(ap_user_can_answer(get_question_id()))
						include(ap_get_theme_location('answer-form.php')); 
				?>

		</div>
		</div>
		<div class="ap-question-right">
			<div class="ap-question-right-inner">
				<?php ap_question_side_tab(get_question_id()); ?>
				<!-- Start Views and Answers -->
				<div class="ap-question-side">			
					<ul class="ap-question-meta">
						<li>
							<?php 
								printf( __( '<span>Asked</span><strong>%s Ago</strong>', 'ap' ), ap_human_time( get_the_time('U', get_question_id())));
							?>
						</li>
						<li>
							<?php 
								$count = ap_count_ans(get_question_id());
								printf( _n('<span>Answer</span><strong data-view="ap-answer-count-label">1 Answer</strong>', '<span>Answers</span><strong data-view="ap-answer-count-label">%d Answers</strong>', $count, 'ap'), $count) ; 
							?>
						</li>
						<li>
							<?php 
								$view_count = ap_get_qa_views(get_question_id());
								printf( _n('<span>Viewed</span><strong>1 Times</strong>', '<span>Viewed</span><strong>%d Times</strong>', $view_count, 'ap'), $view_count) ;
							?>
						</li>
						<li>
							<?php 
								printf( __( '<span>Active</span><strong>%s Ago</strong>', 'ap' ), ap_human_time( mysql2date('U', ap_last_active(get_question_id()))));
							?>
						</li>
					</ul>
				</div>
				<!-- End Views and Answers -->
				
				<!-- Start labels -->
				<div class="ap-question-side">
					<h3 class="ap-question-side-title">
						<?php _e('Labels', 'ap'); ?>					
						<?php ap_change_label_html(get_question_id()); ?>
					</h3>
					<div data-view="ap-labels-list">
						<?php echo ap_get_question_label(get_question_id(), true); ?>
					</div>
				</div>
				<!-- End labels -->
				
				<!-- Start Category -->
				<?php if(ap_opt('enable_categories')): ?>
				<div class="ap-question-side">
					<h3 class="ap-question-side-title"><?php _e('Categories', 'ap'); ?></h3>
					<?php ap_question_categories_html(get_question_id()); ?>
				</div>
				<?php endif; ?>
				<!-- End Category -->
				
				<!-- Start Tags -->

				<?php if(ap_opt('enable_tags')): ?>
				<div class="ap-question-side">
					<h3 class="ap-question-side-title"><?php _e('Tags', 'ap'); ?></h3>
					<?php ap_question_tags_html(get_question_id()); ?>
				</div>
				<?php endif; ?>
				<!-- End Tags -->
				
				<!-- Start participants -->
				<div class="ap-question-side">			
					<?php ap_get_all_parti(30, get_question_id()); ?>
				</div>
				<!-- End participants -->
			</div>
			<?php dynamic_sidebar( 'ap-qsidebar' ); ?>
		</div>
	</div>
</div>
<?php 
}else{
	echo '<div class="ap-pending-notice ap-icon-clock">'.__('This question is being reviewed by moderator, will be published after review.').'</div>';
}
endwhile ;

?>