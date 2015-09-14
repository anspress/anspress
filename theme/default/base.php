<?php
	/**
	 * Display question list
	 *
	 * This template is used in base page, category, tag , etc
	 *
	 * @link http://anspress.io
	 * @since unknown
	 *
	 * @package AnsPress
	 */
?>
<?php dynamic_sidebar( 'ap-top' ); ?>
<div class="row">
	<div id="ap-lists" class="<?php echo is_active_sidebar( 'ap-sidebar' ) && is_anspress() ? 'col-md-9' : 'col-md-12' ?>">
		<?php ap_get_template_part('list-head'); ?>
		<?php if ( ap_have_questions() ) : ?>
			<div class="ap-questions">
				<?php
					/* Start the Loop */
					while ( ap_questions() ) : ap_the_question();
						ap_get_template_part('content-list');
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
		<div class="ap-question-right col-md-3">
			<?php dynamic_sidebar( 'ap-sidebar' ); ?>
		</div>
	<?php } ?>
</div>


