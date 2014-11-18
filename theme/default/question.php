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
if(ap_user_can_view_question()){
?>
<div id="ap-single" class="clearfix" itemtype="http://schema.org/Question" itemscope="">
	<header class="ap-qhead clearfix">		
		<div class="ap-qtitle-meta">
			<a class="ap-btn ap-ask-btn-head pull-right" href="<?php echo ap_get_link_to('ask') ?>"><?php _e('Ask Question'); ?></a>
			<?php if (!ap_opt("double_titles")):?>
				<h1 class="entry-title" itemprop="name">
					<a href="<?php the_permalink() ?>" itemprop="url"><?php the_title(); ?></a>
				</h1>
			<?php else:?>
				<h1 style="display:none" class="entry-title" itemprop="name">
					<?php the_title(); ?>
				</h1>
			<?php endif;?>
			<div class="ap-qtopmeta">
				<?php ap_favorite_html(); ?>		
			</div>
		</div>
	</header>
	<div class="ap-question-lr">		
		<div class="ap-question-left">
			<div id="discussion">
				<div id="question" role="main" class="ap-content question" data-id="<?php echo get_the_ID(); ?>">					
					<div class="ap-question-cells clearfix">
						<div class="ap-avatar">
							<a href="<?php echo ap_user_link(get_the_author_meta('ID'))?>">
								<?php echo get_avatar( get_the_author_meta( 'user_email' ), ap_opt('avatar_size_qquestion') ); ?>
							</a>
						</div>
						<div class="ap-content-inner no-overflow">				
							<div class="ap-qmainc">
								<div class="ap-user-meta">
									<div class="ap-single-vote"><?php ap_vote_html(); ?></div>
									<div class="ap-meta">
										<?php 
											$a=" e ";$b=" ";$time=get_option('date_format').$b.get_option('time_format').$a.get_option('gmt_offset');
											printf('<a href="%3$s?rel=author" class="author"><span itemprop="author">%s</span></a><span> %4$s </span><a href="%5$s"><time datetime="%6$s" title="%6$s" is="relative-time">%s %7$s</time></a>', 
											ap_user_display_name(false, true), 
											ap_human_time( get_the_time('U')),
											ap_user_link(get_the_author_meta('ID')),
											__('asked about','ap'),
											get_permalink(),
											get_the_time($time),
											__('ago','ap')
											);
										?>
									</div>									
								</div>			
								<div class="question-content" itemprop="text">
									<?php the_content(); ?>									
								</div>
								
								<ul class="ap-user-actions clearfix">
									<li><?php ap_edit_q_btn_html(); ?></li>					
									<li><?php ap_comment_btn_html(); ?></li>					
									<li><?php ap_close_vote_html(); ?></li>	
									<li><?php ap_flag_btn_html(); ?></li>
									<li><?php ap_post_delete_btn_html(); ?></li>
								</ul>
								<?php ap_post_edited_time();?>
							</div>
							<div class="ap-qfooter">								
								<div class="ap-tlitem">
									<?php echo ap_get_latest_history_html(get_the_ID(), true, true); ?>
								</div>
								<div class="ap-tlitem">
									<span class="ap-icon-hit ap-tlicon"></span>
									<ul class="ap-question-meta">
										<li>
											<?php 
												printf( __( '<span>Asked</span><strong><time itemprop="datePublished" datetime="%s">%s Ago</time></strong>', 'ap' ), get_the_time('c', get_question_id()), ap_human_time( get_the_time('U')));
											?>
										</li>
										<li>
											<?php 
												$count = ap_count_ans(get_the_ID());
												printf( _n('<span>Answer</span><strong data-view="ap-answer-count-label">1 Answer</strong>', '<span>Answers</span><strong data-view="ap-answer-count-label">%d Answers</strong>', $count, 'ap'), $count) ; 
											?>
										</li>
										<li>
											<?php 
												$view_count = ap_get_qa_views();
												printf( _n('<span>Viewed</span><strong>1 Times</strong>', '<span>Viewed</span><strong>%d Times</strong>', $view_count, 'ap'), $view_count) ;
											?>
										</li>
										<li>
											<?php 
												printf( __( '<span>Active</span><strong><time class="updated" itemprop="dateUpdated" datetime="%s">%s Ago</time></strong>', 'ap' ), mysql2date('c', ap_last_active(get_question_id())),  ap_human_time( mysql2date('U', ap_last_active(get_question_id()))));
											?>
										</li>
									</ul>
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
		</div>
	</div>
</div>

<?php 

}else{
	echo '<div class="ap-pending-notice ap-icon-clock">'.__('You do not have permission to view this question.').'</div>';
}
endwhile ;

?>
