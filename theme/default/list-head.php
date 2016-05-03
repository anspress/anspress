<?php
/**
 * Display question list header
 * Shows sorting, search, tags, category filter form. Also shows a ask button.
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 */
?>

<div class="ap-list-head clearfix">
	<div class="row">
		<div class="col-md-6 col-sm-12">
			<?php ap_get_template_part('search-form'); ?>		
		</div>
		<div class="col-md-6 col-sm-12">
			<form id="ap-filter" class="ap-filter clearfix">
				<?php ap_list_filters(); ?>
				<a id="ap-question-sorting-reset" href="#" title="<?php _e('Reset sorting and filter', 'anspress-question-answer'); ?>"><?php echo ap_icon('x', true); ?></a>
			</form>
			<?php
				// Hide ask button if user page
				if( !is_ap_user() ){
					ap_ask_btn();
				}
			?>
		</div>
	</div>
</div>
