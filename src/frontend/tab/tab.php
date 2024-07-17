<?php
/**
 * Render dynamic tabs block.
 *
 * @package AnsPress
 * @since 5.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

$urlParamKey = $block->context['anspress/tabs/urlParamKey'] ?? 'appage';
$blockTabs   = $block->context['anspress/tabs/tabs'] ?? array();

$currentTab = isset( $_GET[ $urlParamKey ] ) ? sanitize_text_field( wp_unslash( $_GET[ $urlParamKey ] ) ) : ''; // @codingStandardsIgnoreLine WordPress.Security.NonceVerification.Recommended

$filteredTabs = array_filter(
	$blockTabs,
	function ( $tab ) use ( $currentTab ) {
		return $tab['key'] === $currentTab;
	}
);

$indices = array_keys( $filteredTabs );
$index   = array_shift( $indices );

if ( $attributes['tabIndex'] !== $index ) {
	return;
}

echo wp_kses_post( $content );
