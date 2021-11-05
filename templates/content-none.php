<?php
/**
 * Template used when no content found.
 *
 * @package AnsPress
 * @author  Rahul Aryan <rah12@live.com>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_url = add_query_arg( array( 'paged' => 1 ), esc_url( filter_input( INPUT_SERVER, 'REQUEST_URI' ) ) );
?>

<article id="post-0" class="clearfix">
	<div class="no-questions">
		<?php esc_attr_e( 'Sorry! No question found.', 'anspress-question-answer' ); ?>
		<?php if ( get_query_var( 'paged' ) || get_query_var( 'ap_paged' ) ) : ?>
			<?php
				$paged = get_query_var( 'paged', 0 ) > 1 ? get_query_var( 'paged', 0 ) : get_query_var( 'ap_paged', 0 ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			?>

			<?php if ( $paged > 0 ) : ?>
				<div class="ap-pagging-warning">
					<?php
						printf(
							// translators: %d is currently paged value.
							esc_attr__( 'Showing results with pagination active, you are currently on page %d. Click here to return to the initial page', 'anspress-question-answer' ),
							(int) $paged
						);
					?>
					<a href="<?php echo esc_url( $current_url ); ?>"><?php esc_attr_e( 'go to page 1', 'anspress-question-answer' ); ?></a>
				</div>
			<?php endif; ?>

		<?php endif; ?>
	</div>
</article><!-- list item -->
