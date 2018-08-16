<?php
/**
 * Pagination for answers (in single question).
 *
 * @package AnsPress
 * @subpackage Templates
 */

namespace AnsPress\Post;
?>

<?php
/**
 * Before rendering answers pagination.
 *
 * @since 4.2.0
 */
do_action( 'ap_before_answers_pagination' );
?>

<div class="ap-pagination ap-display-flex align-item-center">
	<div class="ap-pagination-links">
		<?php answer_pagination_links(); ?>
	</div>

	<div class="ap-pagination-count ap-text-muted ap-text-small">
		<?php answer_pagination_count(); ?>
	</div>
</div>

<?php
/**
 * After rendering answers pagination.
 *
 * @since 4.2.0
 */
do_action( 'ap_after_answers_pagination' );
