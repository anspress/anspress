<?php
/**
 * Activity reference content template.
 *
 * @link       https://anspress.net
 * @since      4.1.2
 * @license    GPL3+
 * @package    AnsPress
 * @subpackage Templates
 *
 * @global object $activities Activity query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $this->has_action() ) {
	return;
}

$type    = $this->object->action['ref_type']; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$post_id = $this->object->q_id; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

if ( 'answer' === $type && ! empty( $this->object->a_id ) ) {
	$post_id = $this->object->a_id; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
} elseif ( 'post' === $type && ! empty( $this->object->a_id ) ) {
	$post_id = $this->object->a_id; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
}

if ( 'comment' === $type && ap_user_can_read_comment( $this->object->c_id ) ) {
	echo wp_kses_post( get_comment_excerpt( $this->object->c_id ) ) . '<a href="' . esc_url( ap_get_short_link( array( 'ap_c' => $this->object->c_id ) ) ) . '">' . esc_attr__( 'View comment', 'anspress-question-answer' ) . '</a>';
} elseif ( ! empty( $post_id ) && ! $this->in_group ) {
	echo '<a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html( get_the_title( $post_id ) ) . '</a>';
}
