<?php
/**
 * Template for category hover card
 *
 * @link https://anspress.io
 * @since 3.0.0
 * @package AnsPress
 */
?>
<div id="<?php echo $category->term_id; ?>_card" style="display:none">
	<div class="ap-card-content ap-hcard">
		<?php if ( ap_category_have_image( $category->term_id ) ) :   ?>
			<div class="ap-hcard-catimg" style="height: 90px;">
				<?php echo ap_get_category_image( $category->term_id, 90 ); ?>
			</div>
		<?php endif; ?>
		<div class="ap-hcard-head">
			<a class="ap-hcard-caticon" href="<?php echo get_category_link( $category ); ?>">
				<?php ap_category_icon( $category->term_id ); ?>
			</a>
			<div class="no-overflow">
				<span class="ap-hcard-ctitle"><?php echo $category->name; ?></span>
				<span class="ap-hcard-qcount">
					<?php printf( _n('1 Question', '%s Questions', $category->count, 'categories-for-anspress' ),  $category->count ); ?>
				</span>
			</div>
		</div>
		
		<?php if ( $category->description != '' ) :   ?>
			<p class="ap-hcard-cdesc"><?php echo ap_truncate_chars( $category->description, 120 ); ?></p>
		<?php endif; ?>

		<?php $sub_cat_count = count(get_term_children( $category->term_id, 'question_category' ) ); ?>
		<?php if ( $sub_cat_count > 0 ) : ?>
            <span class="ap-hcard-csub">
				<?php
					printf(_n('%d Sub category', '%d Sub categories', $sub_cat_count, 'categories-for-anspress' ), $sub_cat_count );
				?>
            </span>
		<?php endif; ?>
	</div>
</div>
