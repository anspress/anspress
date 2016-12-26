<?php
	/**
	 * Categories page
	 *
	 * Display categories page
	 *
	 * @link http://anspress.io
	 * @since 1.0
	 *
	 * @package AnsPress
	 * @subpackage Categories for AnsPress
	 */

	global $question_categories;
?>
<?php dynamic_sidebar( 'ap-top' ); ?>
<div class="row">
	<div id="ap-lists" class="<?php echo is_active_sidebar( 'ap-category' ) && is_anspress() ? 'col-md-9' : 'col-md-12' ?>">
        <div id="ap-categories" class="clearfix">
            <ul class="ap-term-category-box clearfix">
				<?php foreach ( $question_categories as $key => $category ) : ?>
                    <li class="clearfix">
                        <div class="ap-category-item">
                            <div class="ap-cat-img-c">
                            	<?php ap_category_icon( $category->term_id ); ?>
                                <span class="ap-term-count">
									<?php printf(_n('%d Question', '%d Questions', $category->count, 'categories-for-anspress' ), $category->count ) ?>
                                </span>
								<a class="ap-categories-feat" style="height:<?php echo ap_opt('categories_image_height'); ?>px" href="<?php echo get_category_link( $category );?>">
									<?php echo ap_get_category_image( $category->term_id, ap_opt('categories_image_height') ); ?>
                                </a>
                            </div>

                            <div class="ap-term-title">
								<a class="term-title" href="<?php echo get_category_link( $category );?>">
									<?php echo $category->name; ?>
                                </a>
								<?php $sub_cat_count = count(get_term_children( $category->term_id, 'question_category' ) ); ?>
								<?php if ( $sub_cat_count > 0 ) : ?>
                                    <span class="ap-sub-category">
										<?php 
											printf(_n('%d Sub category', '%d Sub categories', $sub_cat_count, 'categories-for-anspress' ), $sub_cat_count );
										?>
                                    </span>
								<?php endif; ?>
                            </div>

							<?php if ( $category->description != '' ) : ?>
                                <div class="ap-taxo-description">
									<?php echo ap_truncate_chars($category->description, 70 ); ?>
                                </div>
							<?php endif; ?>
                        </div>
                    </li>
				<?php endforeach; ?>
            </ul>
        </div>
		<?php ap_pagination( ); ?>
    </div>
	<?php if ( is_active_sidebar( 'ap-category' ) && is_anspress() ) { ?>
    <div class="ap-question-right col-md-3">
		<?php dynamic_sidebar( 'ap-category' ); ?>
    </div>
	<?php } ?>
</div>
