<?php
/**
 * Control the output of reputation page
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
    <h2>
		<?php esc_attr_e( 'AnsPress Reputation', 'anspress-question-answer' ); ?>
		<a class="add-new-h2" href="#" data-button="ap-new-reputation"><?php esc_attr_e( 'New reputation', 'anspress-question-answer' ); ?></a>
    </h2>
    <form id="anspress-reputation-table" method="get">
		<input type="hidden" name="page" value="<?php echo sanitize_text_field( @$_GET['page'] ); ?>" />
		<?php $reputation_table->display() ?>
    </form>
</div>
