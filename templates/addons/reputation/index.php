<?php
/**
 * Template for user reputations item.
 *
 * Render reputation item in authors page.
 *
 * @author  Rahul Aryan <rah12@live.com>
 * @link    https://anspress.net/
 * @since   4.0.0
 * @package AnsPress
 */

?>
<table class="ap-reputations">
	<tbody>
		<?php
		while ( $reputations->have() ) :
			$reputations->the_reputation();
?>
			<?php ap_get_template_part( 'addons/reputation/item', [ 'reputations' => $reputations ] ); ?>
		<?php endwhile; ?>
	</tbody>
</table>

<?php if ( $reputations->total_pages > 1 ) : ?>
	<a href="#" ap-loadmore="
	<?php
	echo esc_js(
		wp_json_encode(
			array(
				'ap_ajax_action' => 'load_more_reputation',
				'__nonce'        => wp_create_nonce( 'load_more_reputation' ),
				'current'        => 1,
				'user_id'        => $reputations->args['user_id'],
			)
		)
	);
?>
" class="ap-loadmore ap-btn" ><?php esc_attr_e( 'Load More', 'anspress-question-answer' ); ?></a>
<?php endif; ?>
