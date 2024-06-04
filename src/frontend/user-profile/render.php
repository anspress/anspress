<?php
/**
 * Render dynamic profile block.
 *
 * @package AnsPress
 * @subpackage Block
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

?>

<?php if ( ! is_author() ) : ?>

	<div>
		<?php esc_html_e( 'Please use this block on author page.', 'anspress-question-answer' ); ?>
	</div>

<?php else : ?>
	<?php
	$author_id = get_queried_object_id();
	if ( ! $author_id ) {
		return '';
	}

	$avatar_position = isset( $attributes['avatarPosition'] ) ? $attributes['avatarPosition'] : 'left';
	$avatar_size     = isset( $attributes['avatarSize'] ) ? $attributes['avatarSize'] : 96;
	$display_name    = isset( $attributes['displayName'] ) ? $attributes['displayName'] : true;
	$meta_fields     = isset( $attributes['metaFields'] ) ? $attributes['metaFields'] : array();

	$avatar = get_avatar( $author_id, $avatar_size );
	$name   = $display_name ? '<p class="user-name">' . esc_html( get_the_author_meta( 'display_name', $author_id ) ) . '</p>' : '';

	$meta_info = '';

	foreach ( $meta_fields as $meta_field ) {
		$meta_value = get_user_meta( $author_id, $meta_field, true );
		if ( $meta_value ) {
			$meta_info .= '<p class="user-meta">' . esc_html( $meta_field ) . ': ' . esc_html( $meta_value ) . '</p>';
		}
	}
	?>
	<div class="user-profile-block avatar-<?php echo esc_attr( $avatar_position ); ?>">
		<?php echo wp_kses_post( $avatar ); ?>
		<div class="user-info">
			<?php echo wp_kses_post( $name ); ?>
			<div class=""><?php the_author_meta( 'description' ); ?></div>
			<?php echo wp_kses_post( $meta_info ); ?>
		</div>
	</div>

	<?php
	endif;
