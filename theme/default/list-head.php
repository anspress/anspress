<?php
/**
 * Display question list header
 *
 * Shows sorting, search, tags, category filter form. Also shows a ask button.
 *
 * @package AnsPress
 * @author  Rahul Aryan <support@anspress.io>
 */
?>
<div class="ap-list-head clearfix">			
	<form id="ap-search-form" class="ap-search-form pull-left" action="<?php echo ap_get_link_to('search'); ?>">
	    <input name="ap_s" type="text" class="ap-form-control" placeholder="<?php _e('Search questions...', 'ap'); ?>" value="<?php echo sanitize_text_field( get_query_var('ap_s') ); ?>" />
	</form>
	<?php
		// Hide ask button if user page
		if(!is_ap_user()):
			ap_ask_btn();
		endif;
	?>
	<form id="ap-question-sorting" class="ap-questions-sorting">
		<?php ap_question_sorting(); ?>
		<?php do_action('ap_list_head'); ?>
	</form>
</div>
