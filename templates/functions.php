<?php
/**
 * This file contains theme script, styles and other theme related functions.
 * This file can be overridden by creating a anspress directory in active theme folder.
 *
 * @license   https://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 * @author    Rahul Aryan <rah12@live.com>
 * @package   AnsPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue scripts.
 */
function ap_scripts_front() {
	ap_assets();

	// Localize scripts for enqueue.
	ap_localize_script();

	if ( ! is_anspress() && ap_opt( 'load_assets_in_anspress_only' ) ) {
		return;
	}

	ap_enqueue_scripts();

	$custom_css = '
		#anspress .ap-q-cells{
				margin-' . ( is_rtl() ? 'right' : 'left' ) . ': ' . ( ap_opt( 'avatar_size_qquestion' ) + 10 ) . 'px;
		}
		#anspress .ap-a-cells{
				margin-' . ( is_rtl() ? 'right' : 'left' ) . ': ' . ( ap_opt( 'avatar_size_qanswer' ) + 10 ) . 'px;
		}';

	wp_add_inline_style( 'anspress-main', $custom_css );
	do_action( 'ap_enqueue' );

	wp_enqueue_style( 'ap-overrides', ap_get_theme_url( 'css/overrides.css' ), array( 'anspress-main' ), AP_VERSION );
}
add_action( 'wp_enqueue_scripts', 'ap_scripts_front', 1 );

/**
 * Register widget positions.
 */
function ap_widgets_positions() {
	register_sidebar(
		array(
			'name'          => __( '(AnsPress) Before', 'anspress-question-answer' ),
			'id'            => 'ap-before',
			'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
			'after_widget'  => '</div>',
			'description'   => __( 'Widgets in this area will be shown before anspress body.', 'anspress-question-answer' ),
			'before_title'  => '<h3 class="ap-widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( '(AnsPress) Question List Top', 'anspress-question-answer' ),
			'id'            => 'ap-top',
			'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
			'after_widget'  => '</div>',
			'description'   => __( 'Widgets in this area will be shown before questions list.', 'anspress-question-answer' ),
			'before_title'  => '<h3 class="ap-widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( '(AnsPress) Sidebar', 'anspress-question-answer' ),
			'id'            => 'ap-sidebar',
			'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
			'after_widget'  => '</div>',
			'description'   => __( 'Widgets in this area will be shown in AnsPress sidebar except single question page.', 'anspress-question-answer' ),
			'before_title'  => '<h3 class="ap-widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( '(AnsPress) Question Sidebar', 'anspress-question-answer' ),
			'id'            => 'ap-qsidebar',
			'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
			'after_widget'  => '</div>',
			'description'   => __( 'Widgets in this area will be shown in single question page sidebar.', 'anspress-question-answer' ),
			'before_title'  => '<h3 class="ap-widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( '(AnsPress) Category Page', 'anspress-question-answer' ),
			'id'            => 'ap-category',
			'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
			'after_widget'  => '</div>',
			'description'   => __( 'Widgets in this area will be shown in category listing page.', 'anspress-question-answer' ),
			'before_title'  => '<h3 class="ap-widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( '(AnsPress) Tag page', 'anspress-question-answer' ),
			'id'            => 'ap-tag',
			'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
			'after_widget'  => '</div>',
			'description'   => __( 'Widgets in this area will be shown in tag listing page.', 'anspress-question-answer' ),
			'before_title'  => '<h3 class="ap-widget-title">',
			'after_title'   => '</h3>',
		)
	);

	register_sidebar(
		array(
			'name'          => __( '(AnsPress) Author page', 'anspress-question-answer' ),
			'id'            => 'ap-author',
			'before_widget' => '<div id="%1$s" class="ap-widget-pos %2$s">',
			'after_widget'  => '</div>',
			'description'   => __( 'Widgets in this area will be shown in authors page.', 'anspress-question-answer' ),
			'before_title'  => '<h3 class="ap-widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'ap_widgets_positions' );
