<?php
/**
 * Control the output of AnsPress dashboard
 *
 * @link http://anspress.io
 * @since 2.0.0-alpha2
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

$question_count = ap_total_posts_count('question');
$answer_count = ap_total_posts_count('answer');
$flagged_count = ap_total_posts_count('both', 'flag');

?>
<div id="anspress" class="wrap">
	<?php do_action('ap_before_admin_page_title') ?>
	<h2><?php _e('AnsPress Dashboard', 'ap') ?></h2>

	<div class="row ap-dash-tiles">
		<div class="ap-dash-tile col-md-6">
			<div class="ap-dash-tile-in ap-tile anspress-stats-count">
				<ul class="clearfix">
					<li>
						<a href="<?php echo admin_url( 'edit.php?post_type=question' ); ?>">
							<strong><?php echo $question_count->publish; ?></strong>
							<span><?php _e('Questions', 'ap') ?></span>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'edit.php?post_type=answer' ); ?>">
							<strong><?php echo $answer_count->publish; ?></strong>
							<span><?php _e('Answers', 'ap') ?></span>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=anspress_moderate' ); ?>">
							<strong><?php echo $question_count->moderate + $answer_count->moderate. ($question_count->moderate + $answer_count->moderate > 0 ? '<i class="ap-need-att">i</i>' : ''); ?></strong>
							<span><?php _e('Moderate', 'ap') ?></span>
						</a>
					</li>
					<li>
						<a href="<?php echo admin_url( 'admin.php?page=anspress_flagged' ); ?>">
							<strong><?php echo $flagged_count->total. ($flagged_count->total > 0 ? '<i class="ap-need-att">i</i>' : ''); ?></strong>
							<span><?php _e('Flagged', 'ap') ?></span>
						</a>
					</li>
				</ul>
			</div>
			<div class="ap-dash-tile-in ap-dash-questions">
				<h3 class="ap-dash-title"><?php _e('Latest Questions', 'ap') ?></h3>
				<?php
					ap_get_questions(array('sortby' => 'newest'));
					if ( ap_have_questions() ):
				?>
				<div class="ap-user-posts">
					<?php while ( ap_questions() ) : ap_the_question(); ?>
						<div class="ap-user-posts-item clearfix">
							<a class="ap-user-posts-vcount ap-tip<?php echo ap_question_best_answer_selected() ? ' answer-selected' :''; ?>" href="<?php ap_question_the_permalink(); ?>" title="<?php _e('Answers'); ?>"><?php echo ap_icon('answer', true); ?><?php echo ap_question_get_the_answer_count(); ?></a>
							<span class="ap-user-posts-active"><?php ap_question_the_active_ago(); ?></span>
							<a class="ap-user-posts-ccount ap-tip" href="<?php ap_question_the_permalink(); ?>" title="<?php _e('Comments', 'ap'); ?>"><?php echo ap_icon('comment', true); ?><?php echo get_comments_number(); ?></a>
							<div class="no-overflow"><a href="<?php ap_question_the_permalink(); ?>" class="ap-user-posts-title"><?php the_title(); ?></a></div>				
						</div>	
					
					<?php 
						endwhile;
						wp_reset_postdata();
					?>

					<?php 
						else: 
							_e('There is no question yet.', 'ap');
						endif; 
					?>
				</div>
			</div>
		</div>
		<div class="ap-dash-tile col-md-6 ">
			<div class="ap-dash-tile col-md-4 clearfix">
				<div class="anspress-links">
					<a href="http://anspress.io/" target="_blank"><i class="apicon-anspress-icon"></i><?php _e('About AnsPress', 'ap'); ?></a>
					<a href="http://anspress.io/questions/" target="_blank"><i class="apicon-question"></i><?php _e('Support Q&A', 'ap'); ?></a>
					<a href="http://anspress.io/themes/" target="_blank"><i class="apicon-info"></i><?php _e('AnsPress Themes', 'ap'); ?></a>
					<a href="http://github.com/anspress/anspress" target="_blank"><i class="apicon-mark-github"></i><?php _e('Github Repo', 'ap'); ?></a>
				</div>
				<div class="ap-ext-notice">
					<i class="apicon-puzzle"></i>
					<h3>AnsPress extensions</h3>
					<p>Extend AnsPress capabilities, get free extensions</p>
					<a href="http://anspress.io/extensions/" target="_blank">Browse</a>
				</div>
			</div>

		</div>
		
	</div>

</div>