<?php
/**
 * Render dynamic tabs block.
 *
 * @package AnsPress
 * @since 5.0.0
 */

use AnsPress\Classes\TemplateHelper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// If tabs is empty then return.
if ( empty( $attributes['tabs'] ) ) {
	echo esc_html__( 'No tabs configured for this block.', 'anspress-question-answer' );
}

$currentUrl = TemplateHelper::currentPageUrl();

$urlParamKey = $attributes['urlParamKey'];

$activeTab = isset( $_GET[ $urlParamKey ] ) ? sanitize_text_field( wp_unslash( $_GET[ $urlParamKey ] ) ) : ''; // @codingStandardsIgnoreLine WordPress.Security.NonceVerification.Recommended

if ( empty( $activeTab ) ) {
	$activeTab = $attributes['defaultTabKey'];
}
?>
<div <?php echo wp_kses_post( get_block_wrapper_attributes( array( 'anspress-tabs' ) ) ); ?>>
	<?php if ( empty( $activeTab ) ) : ?>
		<div class="anspress-tabs-error"><?php esc_html_e( 'No tab selected. Make sure to set a default tab in blocks settings.', 'anspress-question-answer' ); ?></div>
	<?php else : ?>
		<div class="anspress-tabs-nav" role="tablist" aria-label="tabbed content">
			<?php foreach ( $attributes['tabs'] as $i => $tabItem ) { ?>
				<div class="anspress-tabs-tab" role="tab" aria-selected="" aria-controls="<?php echo esc_attr( $tabItem['label'] ); ?>" tabindex="<?php echo (int) $i; ?>">
					<a class="anspress-tabs-tab-link <?php echo $activeTab === $tabItem['key'] ? 'anspress-tabs-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( $attributes['urlParamKey'] => $tabItem['key'] ), $currentUrl ) ); ?>"><?php echo esc_html( $tabItem['label'] ); ?></a>
				</div>
			<?php } ?>
		</div>

		<div class="anspress-tabs-tab-content">
			<?php echo wp_kses_post( $content ); ?>
		</div>
	<?php endif; ?>

</div>
