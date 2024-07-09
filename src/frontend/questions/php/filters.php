<?php
/**
 * Display questions filters.
 *
 * @since 5.0.0
 * @package AnsPress
 */

namespace AnsPress\Frontend\Questions;

use AnsPress\Classes\Router;
use AnsPress\Classes\TemplateHelper;
use AnsPress\Exceptions\GeneralException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check if currentQueriesArgs is set.
if ( ! isset( $currentQueriesArgs ) ) {
	throw new GeneralException( 'currentQueriesArgs is not set.' );
}

// Chekc if attributes is set or not.
$attributes = $attributes ?? array();

$filterOptions = TemplateHelper::questionFilterOptions();

$activeFilterKey = ! empty( $currentQueriesArgs['args:filter'] ) ? $currentQueriesArgs['args:filter'][0] : 'all';

$activeFilter = array_search( $activeFilterKey, array_column( $filterOptions, 'key' ), true );

$filterData = wp_json_encode(
	array(
		'options'  => $filterOptions,
		'selected' => false !== $activeFilter ? array( $filterOptions[ $activeFilter ] ) : array(),

	)
);

$orderByOptions   = TemplateHelper::questionOrderByOptions();
$activeOrderByKey = ! empty( $currentQueriesArgs['args:orderby'] ) ? $currentQueriesArgs['args:orderby'][0] : 'active';

$activeOrderBy = array_search( $activeOrderByKey, array_column( $orderByOptions, 'key' ), true );

$orderByData = wp_json_encode(
	array(
		'options'  => $orderByOptions,
		'selected' => false !== $activeOrderBy ? array( $orderByOptions[ $activeOrderBy ] ) : array(),
	)
);

$orderOptions = TemplateHelper::questionOrderOptions();

$activeOrderKey = ! empty( $currentQueriesArgs['args:order'] ) ? $currentQueriesArgs['args:order'][0] : 'desc';

$activeOrder = array_search( $activeOrderKey, array_column( $orderOptions, 'key' ), true );

$orderData = wp_json_encode(
	array(
		'options'  => $orderOptions,
		'selected' => false !== $activeOrder ? array( $orderOptions[ $activeOrder ] ) : array(),
	)
);

$selectedCategories = TemplateHelper::currentQuestionQuerySelectedTerms( 'question_category', $currentQueriesArgs['args:categories'] ?? array() );

$selectedCategoriesMap = array_map(
	function ( $category ) {
		return array(
			'key'   => $category->term_id,
			'value' => $category->name,
		);
	},
	$selectedCategories
);

$categoriesData = wp_json_encode(
	array(
		'search_path' => Router::route(
			'v1.categories.index'
		),
		'key_field'   => 'term_id',
		'value_field' => 'name',
		'multiple'    => true,
		'selected'    => $selectedCategoriesMap,
	)
);

$selectedTags = TemplateHelper::currentQuestionQuerySelectedTerms( 'question_tag', $currentQueriesArgs['args:tags'] ?? array() );

$selectedTagsMap = array_map(
	function ( $tag ) {
		return array(
			'key'   => $tag->term_id,
			'value' => $tag->name,
		);
	},
	$selectedTags
);

$tagsData = wp_json_encode(
	array(
		'search_path' => Router::route(
			'v1.tags.index'
		),
		'key_field'   => 'term_id',
		'value_field' => 'name',
		'multiple'    => true,
		'selected'    => $selectedTagsMap,
	)
);

?>

<div class="anspress-questions-args">
	<anspress-queries>
		<?php if ( $attributes['displaySearch'] ?? true ) : ?>
			<div class="anspress-questions-search">
				<input data-anspress-id="keywords" type="text" class="anspress-questions-search-input anspress-form-control" placeholder="<?php esc_attr_e( 'Search questions', 'anspress-question-answer' ); ?>" name="keywords" value="<?php echo esc_attr( $currentQueriesArgs['keywords'] ?? '' ); ?>">
			</div>
		<?php endif; ?>

		<div class="anspress-questions-queries">
			<anspress-dropdown
				data-anspress-id="args:filter"
				label="<?php esc_attr_e( 'Filter', 'anspress-question-answer' ); ?>"
				show-selected="false"
				data-anspress="<?php echo esc_js( $filterData ); ?>"></anspress-dropdown>

			<anspress-dropdown
				data-anspress-id="args:orderby"
				label="<?php esc_attr_e( 'Order by', 'anspress-question-answer' ); ?>"
				show-selected="false"
				data-anspress="<?php echo esc_js( $orderByData ); ?>"></anspress-dropdown>

			<anspress-dropdown
				data-anspress-id="args:order"
				label="<?php esc_attr_e( 'Order', 'anspress-question-answer' ); ?>"
				show-selected="false"
				data-anspress="<?php echo esc_js( $orderData ); ?>"></anspress-dropdown>

			<?php if ( $attributes['displayCategoriesFilter'] ?? true ) : ?>
				<anspress-dropdown
					data-anspress-id="args:categories"
					label="<?php esc_attr_e( 'Categories', 'anspress-question-answer' ); ?>"
					show-selected="false"
					data-anspress="<?php echo esc_js( $categoriesData ); ?>"></anspress-dropdown>
			<?php endif; ?>

			<?php if ( $attributes['displayTagsFilter'] ?? true ) : ?>
				<anspress-dropdown
					data-anspress-id="args:tags"
					label="<?php esc_attr_e( 'Tags', 'anspress-question-answer' ); ?>"
					show-selected="false"
					data-anspress="<?php echo esc_js( $tagsData ); ?>"></anspress-dropdown>
			<?php endif; ?>
		</div>

		<div class="anspress-questions-queries-selections"></div>

		<div class="anspress-questions-queries-buttons">
			<a href="<?php echo esc_url( TemplateHelper::currentPageUrl() ); ?>" class="anspress-button"><?php esc_attr_e( 'Clear', 'anspress-question-answer' ); ?></a>
			<button class="anspress-button anspress-questions-args-submit"><?php esc_attr_e( 'Apply', 'anspress-question-answer' ); ?></button>
		</div>
	</anspress-queries>
</div>
