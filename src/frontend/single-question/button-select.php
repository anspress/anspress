<?php
/**
 * Select and unselect answer button.
 *
 * @package AnsPress
 * @since   5.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check that post argument is set.
if ( ! isset( $args ['post'] ) ) {
	throw new InvalidArgumentException( 'Post argument is required.' );
}

// Check post type is answer.
if ( 'answer' !== $args['post']->post_type ) {
	return;
}

$isSelected     = false;
$selectedAnswer = (int) ap_selected_answer( $args['post']->post_parent );

if ( $selectedAnswer && $selectedAnswer === (int) $args['post']->ID ) {
	$isSelected = true;
}

$selecthref = 'anspress/v1/post/' . (int) $args['post']->post_parent . '/actions/select/' . (int) $args['post']->ID;

if ( $isSelected ) {
	$selecthref = 'anspress/v1/post/' . (int) $args['post']->post_parent . '/actions/unselect/' . (int) $args['post']->ID;
}
?>
<anspress-link
	data-anspress-id="button:select:<?php echo (int) $args['post']->post_parent; ?>"
	data-href="<?php echo esc_attr( $selecthref ); ?>"
	data-method="POST"
	class="anspress-apq-item-select-link <?php echo ! $isSelected ? 'apicon-check' : 'apicon-x'; ?> <?php echo $isSelected ? 'anspress-apq-item-select-active' : ''; ?>">
	<?php
	if ( $isSelected ) {
		esc_attr_e( 'Unselect', 'anspress-question-answer' );
	} else {
		esc_attr_e( 'Select', 'anspress-question-answer' );
	}
	?>
	</anspress-link>
