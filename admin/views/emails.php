<?php
/**
 * Email templates.
 *
 * @link       https://anspress.net
 * @since      4.0.1
 * @author     Rahul Aryan <rah12@live.com>
 * @package    AnsPress
 * @subpackage Admin Views
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $wpdb;
$i = 1;
?>

<table class="form-table">
	<tbody>
		<tr>
			<th scope="row" valign="top">
				<label><?php _e( 'More options', 'anspress-question-answer' ); ?>:</label>
			</th>
			<td>
				<p><?php _e( 'More email options can be found in addon options', 'anspress-question-answer' ); ?> <a class="button" href="<?php echo admin_url( 'admin.php?page=anspress_addons&active_addon=free%2Femail.php' ); ?>"><?php esc_attr_e( 'More email options', 'anspress-question-answer' ); ?></a></p>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				<label><?php _e( 'Select Template', 'anspress-question-answer' ); ?>:</label>
			</th>
			<td>
				<?php
					$active    = ap_isset_post_value( 'active_template', 'new_question' );
					$templates = array(
						'new_question'  => __( 'New Question', 'anspress-question-answer' ),
						'new_answer'    => __( 'New Answer', 'anspress-question-answer' ),
						'new_comment'   => __( 'New Comment', 'anspress-question-answer' ),
						'edit_question' => __( 'Edit Question', 'anspress-question-answer' ),
						'edit_answer'   => __( 'Edit Answer', 'anspress-question-answer' ),
					);
				?>
				<select id="select-templates" name="email_templates">
					<?php foreach ( $templates as $template => $label ) : ?>
						<option value="<?php echo esc_attr( $template ); ?>" <?php selected( $template, $active ); ?>><?php echo esc_attr( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p><?php esc_attr_e( 'The template selected here will appear below.', 'anspress-question-answer' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				<label><?php _e( 'Edit Template', 'anspress-question-answer' ); ?>:</label>
			</th>
			<td>
					<div id="template-holder">
						<?php AnsPress\Addons\Email::init()->template_form( $active ); ?>
					</div>
			</td>
		</tr>
	</tbody>
</table>

<script type="text/javascript">
	jQuery(document).ready(function($){
		$('#select-templates').on('change', function(){
			var self = this;
			AnsPress.showLoading(self);

			AnsPress.ajax({
				data: {
					action: 'ap_email_template',
					__nonce: '<?php echo wp_create_nonce( 'ap_email_template' ); ?>',
					template: $(self).val()
				},
				success: function(data){
					tinymce.execCommand('mceRemoveEditor',true, 'form_email_template-body');
					AnsPress.hideLoading(self);
					$('#template-holder').html(data);
					tinymce.execCommand('mceAddEditor',true, 'form_email_template-body');
				}
			});
		});
	});
</script>
<style>
	.ap-email-allowed-tags pre{
	display: inline;
	background: #eee;
	margin-right: 15px;
	}
</style>

