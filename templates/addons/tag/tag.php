<?php
/**
 * Tag page
 * Display list of question of a tag
 * @package AnsPress
 */
?>
<?php dynamic_sidebar( 'ap-top' ); ?>
<div class="row">
	<div id="ap-lists" class="<?php echo is_active_sidebar( 'ap-tag' ) && is_anspress() ? 'ap-col-9' : 'ap-col-12' ?>">
		<div class="ap-taxo-detail clearfix">
			<h2 class="entry-title"><?php printf(__('Question tag: %s','anspress-question-answer'), $question_tag->name); ?> <span class="ap-tax-item-count"><?php printf( _n('1 Question', '%s Questions', $question_tag->count, 'anspress-question-answer'),  $question_tag->count); ?></span></h2>
			<?php if($question_tag->description !=''): ?>
				<p class="ap-taxo-description"><?php echo $question_tag->description; ?></p>
			<?php endif; ?>
		</div>
		<?php ap_get_template_part('list-head'); ?>
		<?php if ( ap_have_questions() ) : ?>
			<div class="ap-questions">
				<?php
					
					/* Start the Loop */
					while ( ap_have_questions() ) : ap_the_question();
						global $post;
						include(ap_get_theme_location('question-list-item.php'));
					endwhile;
				?>
			</div>
			<?php ap_questions_the_pagination(); ?>
		<?php
			else : 
				include(ap_get_theme_location('content-none.php'));
			endif; 
		?>	
	</div>
	<?php if ( is_active_sidebar( 'ap-tag' ) && is_anspress()){ ?>
		<div class="ap-question-right ap-col-3">
			<?php dynamic_sidebar( 'ap-tag' ); ?>
		</div>
	<?php } ?>
</div>
