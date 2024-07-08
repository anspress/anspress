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

?>
<?php
$filterData = wp_json_encode(
	array(
		'options'  => array(
			array(
				'key'   => 'all',
				'value' => __( 'All', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'open',
				'value' => __( 'Open', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'closed',
				'value' => __( 'Closed', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'solved',
				'value' => __( 'Resolved', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'unresolved',
				'value' => __( 'Unresolved', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'featured',
				'value' => __( 'Featured', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'unanswered',
				'value' => __( 'Unanswered', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'moderate',
				'value' => __( 'Moderate', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'private_post',
				'value' => __( 'Private', 'anspress-question-answer' ),
			),
		),
		'selected' => array(
			array(
				'key'   => 'all',
				'value' => __( 'All', 'anspress-question-answer' ),
			),
		),

	)
);

$orderByData = wp_json_encode(
	array(
		'options'  => array(
			array(
				'key'   => 'votes',
				'value' => __( 'Votes count', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'date',
				'value' => __( 'Date', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'active',
				'value' => __( 'Last active', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'answers',
				'value' => __( 'Answers count', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'views',
				'value' => __( 'Views count', 'anspress-question-answer' ),
			),
		),
		'selected' => array(
			array(
				'key'   => 'votes',
				'value' => __( 'Votes count', 'anspress-question-answer' ),
			),
		),
	)
);

$orderData = wp_json_encode(
	array(
		'options'  => array(
			array(
				'key'   => 'asc',
				'value' => __( 'Ascending', 'anspress-question-answer' ),
			),
			array(
				'key'   => 'desc',
				'value' => __( 'Descending', 'anspress-question-answer' ),
			),
		),
		'selected' => array(
			array(
				'key'   => 'desc',
				'value' => __( 'Descending', 'anspress-question-answer' ),
			),
		),
	)
);

$categoriesData = wp_json_encode(
	array(
		'search_path' => Router::route(
			'v1.categories.index'
		),
		'key_field'   => 'term_id',
		'value_field' => 'name',
		'multiple'    => true,
	)
);

$tagsData = wp_json_encode(
	array(
		'search_path' => Router::route(
			'v1.tags.index'
		),
		'key_field'   => 'term_id',
		'value_field' => 'name',
		'multiple'    => true,
	)
);

?>

<div class="anspress-questions-args">
	<anspress-queries>
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

			<anspress-dropdown
				data-anspress-id="args:categories"
				label="<?php esc_attr_e( 'Categories', 'anspress-question-answer' ); ?>"
				show-selected="false"
				data-anspress="<?php echo esc_js( $categoriesData ); ?>"></anspress-dropdown>

			<anspress-dropdown
				data-anspress-id="args:tags"
				label="<?php esc_attr_e( 'Tags', 'anspress-question-answer' ); ?>"
				show-selected="false"
				data-anspress="<?php echo esc_js( $tagsData ); ?>"></anspress-dropdown>

			<div class="anspress-questions-queries-buttons">
				<a href="<?php echo esc_url( TemplateHelper::currentPageUrl() ); ?>" class="anspress-button"><?php esc_attr_e( 'Clear', 'anspress-question-answer' ); ?></a>
				<button class="anspress-button anspress-questions-args-submit"><?php esc_attr_e( 'Apply', 'anspress-question-answer' ); ?></button>
			</div>
		</div>

		<div class="anspress-queries-selections"></div>
	</anspress-queries>
</div>
