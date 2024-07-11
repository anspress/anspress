<?php
/**
 * Display the ask form.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Router;
use AnsPress\Exceptions\GeneralException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat, huh?' );
}

// Check if $attributes is set.
if ( ! isset( $attributes ) ) {
	throw new GeneralException( 'Attributes not set in ask-form.' );
}

$questionFormArgs = array(
	'form_action'  => Router::route(
		'v1.questions.create',
	),
	'load_tinymce' => 'question_content',
);

$tagsData = wp_json_encode(
	array(
		'search_path' => Router::route(
			'v1.tags.index'
		),
		'key_field'   => 'term_id',
		'value_field' => 'name',
		'multiple'    => true,
		// 'selected'    => $selectedTagsMap,
	)
);

$categoryData = wp_json_encode(
	array(
		'search_path' => Router::route(
			'v1.categories.index'
		),
		'key_field'   => 'term_id',
		'value_field' => 'name',
		'multiple'    => false,
		// 'selected'    => $selectedCategoriesMap,
	)
);
?>
<anspress-question-form
	data-anspress-id="form:ask:c"
	<?php echo wp_kses_data( get_block_wrapper_attributes() ); ?>
	data-gutenberg-attributes="<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>"
	data-anspress="<?php echo esc_attr( wp_json_encode( $questionFormArgs ) ); ?>">

	<form class="anspress-form anspress-answer-form" method="post" data-anspress-form="question">
		<div data-anspress-field="question_title" class="anspress-form-group">
			<label for="question_title" class="anspress-form-label"><?php esc_html_e( 'Title', 'anspress-question-answer' ); ?></label>
			<input type="text" class="anspress-form-control" id="question_title" name="question_title" required>
		</div>

		<div data-anspress-field="question_content" class="anspress-form-group">
			<label for="question_content" class="anspress-form-label"><?php esc_html_e( 'Content', 'anspress-question-answer' ); ?></label>
			<textarea class="anspress-form-control" id="question_content" name="question_content" required></textarea>
		</div>

		<div data-anspress-field="question_tags" class="anspress-form-group">
			<label for="question_tags" class="anspress-form-label"><?php esc_html_e( 'Tags', 'anspress-question-answer' ); ?></label>
			<anspress-dropdown
				data-anspress-id="question_tags"
				label="<?php esc_attr_e( 'Tags', 'anspress-question-answer' ); ?>"
				data-anspress="<?php echo esc_js( $tagsData ); ?>"
				as="field"
				name="question_tags"></anspress-dropdown>
		</div>

		<div data-anspress-field="question_category" class="anspress-form-group">
			<label for="question_category" class="anspress-form-label"><?php esc_html_e( 'Category', 'anspress-question-answer' ); ?></label>

			<anspress-dropdown
				data-anspress-id="question_category"
				label="<?php esc_attr_e( 'Categories', 'anspress-question-answer' ); ?>"
				data-anspress="<?php echo esc_js( $categoryData ); ?>"
				as="field"
				name="question_category"></anspress-dropdown>
		</div>

		<div data-anspress-field="private_question" class="anspress-form-group">
			<label for="private_question" class="anspress-form-label">
				<input type="checkbox" id="private_question" name="private_question" value="1">
				<?php esc_html_e( 'Private question', 'anspress-question-answer' ); ?>
			</label>
		</div>

		<div class="anspress-form-group">
			<button data-anspress-id="button:question:form" type="submit" class="anspress-button anspress-button-primary"><?php esc_html_e( 'Submit', 'anspress-question-answer' ); ?></button>
		</div>
	</form>
</anspress-answer-form>
