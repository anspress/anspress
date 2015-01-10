<?php
/**
 * Display user profile page
 *
 * @link http://wp3.in
 * @since unknown
 * @package AnsPress
 */


$user_id 		= ap_user_page_user_id();
$ap_user 		= ap_user();
$ap_user_data 	= ap_user_data();
$description 	= ap_get_current_user_meta('description');
?>

<div data-view="cover" class="ap-cover-bg" <?php ap_user_cover_style($user_id); ?>>
	<?php ap_cover_upload_form(); ?>
</div>

<?php ap_profile_user_stats_counts() ?>


<!-- start about me -->		
<?php if($description != '') : ?>
	<div class="ap-profile-box ap-about-me">
		<h3 class="ap-box-title"><?php _e('About Me', 'ap'); ?></h3>
		<p class="about-me">
			<?php echo $description; ?>
		</p>
	</div>			
<?php endif; ?>
<!-- End about me -->


<?php if(ap_user_answer_count($user_id) > 0) : ?>
	<div class="ap-profile-box border-top margin-top-20 clearfix">
		<h3 class="ap-box-title"><?php printf(__('Answers (%d)', 'ap'), ap_user_answer_count($user_id)); ?></h3>
		<div class="row">
			<div class="col-md-9">
				<?php 
					$answers = new Answers_Query(array('author' => $user_id, 'include_best_answer' => true, 'showposts' => 5));
					
					while ( $answers->have_posts() ) : $answers->the_post();
						include ap_get_theme_location('content-answer.php');
					endwhile;
					
					wp_reset_postdata(); 
				?>
			</div>
			<div class="col-md-3">
				<ul class="ap-cpt-statics">
					<li><?php _e('Total answers', 'ap') ?> <span><?php echo ap_user_answer_count($user_id) ?></span></li>
					<li><?php _e('Best answers', 'ap') ?> <span><?php echo ap_user_best_answer_count($user_id) ?></span></li>
				</ul>
				<a class="ap-btn ap-btn-view-all" href="<?php echo ap_user_link($user_id, 'answers') ?>"><?php _e('View all answers', 'ap') ?></a>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php if(ap_user_question_count($user_id) > 0) : ?>
	<div class="ap-profile-box border-top margin-top-20 clearfix">
		<h3 class="ap-box-title"><?php printf(__('Questions (%d)', 'ap'), ap_user_question_count($user_id)); ?></h3>
		<div class="row">
			<div class="col-md-9">
				<?php 
					$answers = new Question_Query(array('author' => $user_id, 'showposts' => 5));
					
					while ( $answers->have_posts() ) : $answers->the_post();
						global $post;				
						echo '<div class="ap-answer-post clearfix">';
						echo '<a class="ap-answer-count ap-tip" href="'.ap_answers_link().'" title="'.__('Total answers', 'ap').'"><span>'. ap_count_answer_meta().'</span>'.__('Ans', 'ap').'</a>';		
						echo '<div class="ap-ans-content no-overflow">';
						echo '<a class="ap-title" href="'.get_permalink().'">'.get_the_title().'</a>';
						echo '<ul class="ap-display-question-meta ap-ul-inline">';
						echo ap_display_question_metas();
						echo '</ul>';
						echo '</div></div>';
					endwhile;
					
					wp_reset_postdata(); 
				?>
			</div>
			<div class="col-md-3">
				<ul class="ap-cpt-statics">
					<li><?php _e('Total questions', 'ap') ?> <span><?php echo ap_user_question_count($user_id) ?></span></li>
					<li><?php _e('Solved questions', 'ap') ?> <span><?php echo ap_user_best_answer_count($user_id) ?></span></li>
				</ul>
				<a class="ap-btn ap-btn-view-all" href="<?php echo ap_user_link($user_id, 'questions') ?>"><?php _e('View all questions', 'ap') ?></a>
			</div>
		</div>
	</div>
<?php endif; ?>
