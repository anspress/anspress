<?php
/**
 * Control the output of question select
 *
 * @link https://anspress.io
 * @since 2.0.0-alpha2
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */

// If this file is called directly, abort.
if (! defined('WPINC')) {
    die;
}

?>
<div id="ap-admin-dashboard" class="wrap">
	<?php do_action('ap_before_admin_page_title') ?>

	<h2><?php _e('Select a question for new answer', 'anspress-question-answer') ?></h2>
	<p><?php _e('Slowly type for question suggestion and then click select button right to question title.', 'anspress-question-answer') ?></p>

	<?php do_action('ap_after_admin_page_title') ?>

	<div class="ap-admin-container">
		<form class="question-selection">
			<input type="text" name="question_id" class="ap-select-question" id="select-question-for-answer" />
			<input type="hidden" name="is_admin" value="true" />
		</form>
		<div id="similar_suggestions">
		</div>
	</div>

</div>