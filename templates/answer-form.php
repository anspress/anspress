<?php
/**
 * Answer form template.
 *
 * @link https://anspress.net
 * @since unknown
 * @license GPL3+
 * @package AnsPress
 */

$ajax_query = wp_json_encode(
	array(
		'ap_ajax_action' => 'load_tinymce',
		'question_id'    => get_question_id(),
	)
);
?>

<?php if ( ap_user_can_answer( get_question_id() ) ) : ?>
	<div id="answer-form-c" class="ap-minimal-editor">
		<div class="ap-avatar ap-pull-left">
			<a href="<?php echo ap_user_link( get_current_user_id() ); ?>">
				<?php echo get_avatar( get_current_user_id(), ap_opt( 'avatar_size_qquestion' ) ); ?>
			</a>
		</div>
		<div id="ap-drop-area" class="ap-cell ap-form-c clearfix">
			<div class="ap-cell-inner">
				<div class="ap-minimal-placeholder">
					<div class="ap-dummy-editor"></div>
					<div class="ap-dummy-placeholder"><?php _e( 'Write your answer.', 'anspress-question-answer' ); ?></div>
					<div class="ap-editor-fade" ap="loadEditor" data-apquery="<?php echo esc_js( $ajax_query ); ?>"></div>
				</div>
				<div id="ap-form-main">
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php ap_get_template_part( 'login-signup' ); ?>
