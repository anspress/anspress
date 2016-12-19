<?php
/**
 * AnsPress image upload modal
 * Handle image uploading and importing from url
 *
 * @link https://anspress.io
 * @since 2.4
 * @author Rahul Aryan <support@anspress.io>
 * @package AnsPress
 */
?>

<div class="ap-mediam">
	<div class="ap-mediam-types">
		<div class="ap-mediam-pc ap-mediam-type clerafix">
			<?php echo ap_icon('cloud-upload', true); ?>
			<?php _e('Upload from computer', 'anspress-question-answer'); ?>
		</div>
		<div class="ap-mediam-pc ap-mediam-type clerafix">
			<?php echo ap_icon('globe', true); ?>
			<?php _e('Image from link', 'anspress-question-answer'); ?>
		</div>
	</div>
</div>
