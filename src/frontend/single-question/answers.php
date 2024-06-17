<?php
/**
 * Answers content
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\Plugin;
use AnsPress\Exceptions\GeneralException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Check question is set or not.
if ( ! isset( $args['question'] ) ) {
	throw new GeneralException( 'Question not set.' );
}

// Answers.
$query = new WP_Query(
	array(
		'post_type'      => 'answer',
		'post_parent'    => $args['question']->ID,
		'posts_per_page' => ap_opt( 'answers_per_page' ),
		'paged'          => 0,
		'order'          => 'ASC',
		'orderby'        => 'menu_order date',
	)
);

?>
<div data-anspressel="answers" class="anspress-answers">
	<div class="anspress-answers-items">
		<?php if ( $query->have_posts() ) : ?>
			<?php
			while ( $query->have_posts() ) {
				$query->the_post();
				Plugin::loadView( 'src/frontend/single-question/item.php', array( 'post' => get_post() ) );
			}
			wp_reset_postdata();
			?>
		<?php endif; ?>
	</div>

	<?php if ( ap_have_answers() ) : ?>
		<?php ap_answers_the_pagination(); ?>
	<?php endif; ?>
</div>
