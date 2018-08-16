<?php
/**
 * Display answers tab in single question page.
 *
 * @package AnsPress
 * @subpackage Templates
 * @since 4.2.0
 */

namespace AnsPress\Post;

$tab_links = get_answers_tab_links();
?>
<ul id="answers-order" class="ap-answers-tab ap-ul-inline clearfix">

	<?php if ( ! empty( $tab_links ) ) : ?>
		<?php foreach ( (array) $tab_links as $k => $nav ) : ?>
			<li <?php echo ( ! empty( $nav['active'] ) ? ' class="active"' : '' ); ?>>
				<a href="<?php echo esc_url( $nav['link'] . '#answers-order' ); ?>">
					<?php echo esc_attr( $nav['title'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	<?php endif; ?>

</ul>
