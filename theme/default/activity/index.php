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
<div class="row">
	<div id="ap-activity" class="<?php echo is_active_sidebar( 'ap-activity' ) && is_anspress() ? 'col-md-9' : 'col-md-12' ?>">
		<?php if ( ap_has_activities() ) : ?>
			<div class="ap-activities">
				<?php
					/* Start the Loop */
					while ( ap_activities() ) : ap_the_activity();
						ap_get_template_part('activity/item');
					endwhile;
				?>
			</div>
		<?php ap_activity_pagination(); ?>
		<?php
			else :
				ap_get_template_part('content-none');
			endif;
		?>
	</div>
	<?php if ( is_active_sidebar( 'ap-activity' ) && is_anspress()){ ?>
		<div class="ap-question-right col-md-3">
			<?php dynamic_sidebar( 'ap-activity' ); ?>
		</div>
	<?php } ?>
</div>


