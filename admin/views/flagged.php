<?php
/**
 * Control the output of flagged page
 * @link 	https://anspress.io
 * @since 	2.0.0-alpha2
 * @author 	Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

?>
<div class="wrap">
	<div id="apicon-users" class="icon32"><br/></div>
	<h2><?php esc_attr_e( 'Flagged question & answer', 'anspress-question-answer' ); ?></h2>
	<?php do_action( 'ap_after_admin_page_title' ) ?>
	<form id="flagged-filter" method="get">
		<input type="hidden" name="page" value="<?php echo sanitize_text_field( $_REQUEST['page'] ); ?>" />
		<?php $flagged_table->views() ?>
		<?php $flagged_table->advanced_filters(); ?>
		<?php $flagged_table->display() ?>
	</form>
</div>
