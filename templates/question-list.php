<?php
	/**
	 * Display question list
	 *
	 * This template is used in base page, category, tag , etc
	 *
	 * @link https://anspress.io
	 * @since unknown
	 *
	 * @package AnsPress
	 */
?>
<?php dynamic_sidebar( 'ap-top' ); ?>
<div class="ap-row">
	<div id="ap-lists" class="<?php echo is_active_sidebar( 'ap-sidebar' ) && is_anspress() ? 'ap-col-9' : 'ap-col-12' ?>">
		<?php if( !get_query_var( 'ap_hide_list_head' ) ): ?>
			<?php ap_get_template_part('list-head'); ?>
		<?php endif; ?>

		<?php if ( ap_have_questions() ) : ?>
			<div class="ap-questions">
				<?php
					/* Start the Loop */
					while ( ap_have_questions() ) : ap_the_question();
						ap_get_template_part('question-list-item');
					endwhile;
				?>
			</div>
		<?php ap_questions_the_pagination(); ?>
		<?php
			else :
				ap_get_template_part('content-none');
			endif;
		?>
	</div>
	<?php if ( is_active_sidebar( 'ap-sidebar' ) && is_anspress()){ ?>
		<div class="ap-question-right ap-col-3">
			<?php dynamic_sidebar( 'ap-sidebar' ); ?>
		</div>
	<?php } ?>
</div>


