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
	<div class="pull-right">
		<?php ap_ask_btn(); ?>
	</div>

	<?php ap_get_template_part( 'search-form' ); ?>
	<?php ap_list_filters(); ?>
</div>

