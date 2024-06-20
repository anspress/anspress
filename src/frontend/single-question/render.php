<?php
/**
 * Render dynamic profile nav block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

use AnsPress\Classes\Plugin;
use AnsPress\Modules\Vote\VoteService;

$_post = ap_get_post( get_the_ID() );

$voteData = Plugin::get( VoteService::class )->getPostVoteData( get_the_ID() );

?>
<div <?php echo wp_kses_data( get_block_wrapper_attributes() ); ?> data-gutenberg-attributes="<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>" data-post-id="<?php the_ID(); ?>">
	<?php
		Plugin::loadView( 'src/frontend/single-question/item.php' );
		Plugin::loadView( 'src/frontend/single-question/answers.php', array( 'question' => get_post() ) );
	?>

	<?php Plugin::loadView( 'src/frontend/single-question/answer-form.php', array( 'question' => get_post() ) ); ?>
</div>
