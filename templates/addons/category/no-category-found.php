<?php
/**
 * When visitor try to browse category page without setting query_var then
 * this is show.
 *
 * @link http://anspress.net
 * @since 4.0
 * @package AnsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="ap-no-category-found ap-404">
	<p class="ap-notice ap-yellow"><?php esc_attr_e( 'No category is set!', 'anspress-question-answer' ); ?></p>
</div>
