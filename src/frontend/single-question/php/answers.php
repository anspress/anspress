<?php
/**
 * Answers content
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\GeneralException;
use AnsPress\Modules\Answer\AnswerService;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check question is set or not.
if ( ! isset( $args['question'] ) ) {
	throw new GeneralException( 'Question not set.' );
}

// Check query is set or not.
if ( ! isset( $args['query'] ) ) {
	throw new GeneralException( 'Query not set.' );
}

// Check answers_args is set or not.
if ( ! isset( $args['answers_args'] ) ) {
	throw new GeneralException( 'Answers args not set.' );
}

$query = $args['query'];
?>

<?php if ( $query->have_posts() ) : ?>
	<?php
	while ( $query->have_posts() ) {
		$query->the_post();
		Plugin::loadView(
			'src/frontend/single-question/php/item.php',
			array(
				'post'       => get_post(),
				'attributes' => $attributes,
			)
		);
	}
	wp_reset_postdata();
	?>
	<?php
endif;
