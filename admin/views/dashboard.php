<?php
/**
 * Control the output of AnsPress dashboard
 *
 * @link http://wp3.in
 * @since 2.0.0-alpha2
 * @author Rahul Aryan <rah12@live.com>
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
<div id="ap-admin-dashboard" class="wrap">
	<?php do_action('ap_before_admin_page_title') ?>
	<h2><?php _e('AnsPress Dashboard', 'ap') ?></h2>

	<div id="ap-dash-tiles">
		<div class="grid-sizer"></div>
		<div class="ap-dash-tile col-4">
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
		</div>
		<div class="ap-dash-tile col-2">
			<div class="ap-dash-tile-in ap-dash-questions">
				<h3 class="ap-dash-title"><?php _e('Questions', 'ap') ?></h3>
				<?php
					$questions = new Question_Query(array('showposts' => 5));
					if($questions->have_posts()):
				?>
					<ul>
						<?php
							while ( $questions->have_posts() ) : $questions->the_post();
								global $post;
								$answers = get_post_meta(get_the_ID(), ANSPRESS_ANS_META, true);
								$vote = get_post_meta(get_the_ID(), ANSPRESS_VOTE_META, true);
								$view = get_post_meta(get_the_ID(), ANSPRESS_VIEW_META, true);
								echo '<li class="clearfix">';
								echo '<div class="ap-avatar">'.get_avatar( $post->post_author, 30 ).'</div>';
								echo '<div class="ap-q-post"><a class="ap-q-title" href="'.get_permalink().'">'.get_the_title().'</a>
										<div class="ap-q-meta">
											<span class="ap-a-count">'.sprintf(_n('1 answer', '%d answers', $answers, 'ap'), $answers).'</span>
											<span class="ap-vote-count">'.sprintf(_n('1 vote', '%d votes', $vote, 'ap'), $vote).'</span>
											<span class="ap-view-count">'.sprintf(_n('1 view', '%d views', $view, 'ap'), $view).'</span>
										</div>
										</div>';
								echo '</li>';
							endwhile;
							wp_reset_postdata();
						?>
					</ul>
				<?php 
					else: 
						_e('There is no question yet.', 'ap');
					endif; 
				?>
			</div>
		</div>
		<div class="ap-dash-tile col-2">
			<div class="ap-dash-tile-in ap-tile anspress-support-link ap-tile-card">
				<p><?php _e('Have any questions ?', 'ap') ?></p>
				<a href="http://wp3.in/questions/ask" target="_blank"><?php _e('Ask for support', 'ap') ?></a>
			</div>
		</div>

		<div class="ap-dash-tile col-2">
			<div class="ap-dash-tile-in ap-dash-questions">
				<h3 class="ap-dash-title"><?php _e('Answers', 'ap') ?></h3>
				<?php
					$answers = new Answers_Query(array('showposts' => 5));
					if($answers->have_posts()):
				?>
					<ul>
						<?php
							while ( $answers->have_posts() ) : $answers->the_post();
								global $post;
								$vote = get_post_meta(get_the_ID(), ANSPRESS_VOTE_META, true);
								$view = get_post_meta(get_the_ID(), ANSPRESS_VIEW_META, true);
								echo '<li class="clearfix">';
								echo '<div class="ap-avatar">'.get_avatar( $post->post_author, 30 ).'</div>';
								echo '<div class="ap-q-post"><a class="ap-q-title" href="'.get_permalink().'">'.substr(strip_tags(get_the_content()), 0, 50).'</a>
										<div class="ap-q-meta">
											<span class="ap-parent-link"><a href="'.get_permalink( $post->post_parent ).'">'.__('View question', 'ap').'</a></span>
											<span class="ap-vote-count">'.sprintf(_n('1 vote', '%d votes', $vote, 'ap'), $vote).'</span>
											<span class="ap-view-count">'.sprintf(_n('1 view', '%d views', $view, 'ap'), $view).'</span>
										</div>
										</div>';
								echo '</li>';
							endwhile;
							wp_reset_postdata();
						?>
					</ul>
				<?php 
					else: 
						_e('There is no question yet.', 'ap');
					endif; 
				?>
			</div>
		</div>
		
	</div>

</div>