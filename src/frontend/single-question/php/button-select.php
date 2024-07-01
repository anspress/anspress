<?php
/**
 * Select and unselect answer button.
 *
 * @package AnsPress
 * @since   5.0.0
 */

use AnsPress\Classes\Router;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Trying to cheat?' );
}

// Check that post argument is set.
if ( ! isset( $post ) ) {
	throw new InvalidArgumentException( 'Post argument is required.' );
}

// Check post type is answer.
if ( 'answer' !== $post->post_type ) {
	return;
}

$isSelected     = false;
$selectedAnswer = (int) ap_selected_answer( $post->post_parent );

if ( $selectedAnswer && $selectedAnswer === (int) $post->ID ) {
	$isSelected = true;
}

$selecthref = 'anspress/v1/post/' . (int) $post->post_parent . '/actions/select/' . (int) $post->ID;

$href = Router::route(
	'v1.answers.actions',
	array(
		'answer_id' => $post->ID,
		'action'    => $isSelected ? 'unselect' : 'select',
	)
);
?>
<anspress-link
	data-anspress-id="button:select:<?php echo (int) $post->post_parent; ?>"
	data-href="<?php echo esc_attr( $href ); ?>"
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
