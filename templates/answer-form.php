<?php
/**
 * Answer form template.
 *
 * @link https://anspress.net
 * @since unknown
 * @license GPL3+
 * @package AnsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
			<a href="<?php echo esc_url( ap_user_link( get_current_user_id() ) ); ?>">
				<?php echo get_avatar( get_current_user_id(), ap_opt( 'avatar_size_qquestion' ) ); ?>
			</a>
		</div>
		<div id="ap-drop-area" class="ap-cell ap-form-c clearfix">
			<div class="ap-cell-inner">
				<div class="ap-minimal-placeholder">
					<div class="ap-dummy-editor<?php echo esc_attr( ap_opt( 'answer_text_editor' ) ? ' ap-dummy-quick-editor' : '' ); ?>">
						<?php if ( ap_opt( 'answer_text_editor' ) ) : ?>
						<div class="quicktags-toolbar hide-if-no-js ap-dummy-toolbar">
							<input type="button" class="ed_button button button-small" value="b">
							<input type="button" class="ed_button button button-small" value="i">
							<input type="button" class="ed_button button button-small" value="link">
							<input type="button" class="ed_button button button-small" value="b-quote">
							<input type="button" class="ed_button button button-small" value="del">
							<input type="button" class="ed_button button button-small" value="ins">
							<input type="button" class="ed_button button button-small" value="img">
							<input type="button" class="ed_button button button-small" value="ul">
							<input type="button" class="ed_button button button-small" value="ol">
							<input type="button" class="ed_button button button-small" value="li">
							<input type="button" class="ed_button button button-small" value="code">
							<input type="button" class="ed_button button button-small" value="close tags">
						</div>
						<?php endif; ?>
					</div>

					<div class="ap-dummy-placeholder"><?php esc_attr_e( 'Write your answer.', 'anspress-question-answer' ); ?></div>
					<div class="ap-editor-fade" ap="loadEditor" data-apquery="<?php echo esc_js( $ajax_query ); ?>"></div>
				</div>
				<div id="ap-form-main">
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php ap_get_template_part( 'login-signup' ); ?>
