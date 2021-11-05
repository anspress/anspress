<?php
/**
 * Categories page.
 *
 * Display categories page
 *
 * @link        http://anspress.net
 * @since       4.0
 * @package     AnsPress
 * @subpackage  Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $question_categories;
?>

<?php dynamic_sidebar( 'ap-top' ); ?>

<div class="ap-row">
	<div class="<?php echo is_active_sidebar( 'ap-category' ) && is_anspress() ? 'ap-col-9' : 'ap-col-12'; ?>">
		<div id="ap-categories" class="clearfix">
			<ul class="ap-term-category-box clearfix">

				<?php foreach ( (array) $question_categories as $key => $category ) : ?>
					<li class="clearfix">
						<div class="ap-category-item">
							<div class="ap-cat-img-c">

								<?php ap_category_icon( $category->term_id ); ?>

								<span class="ap-term-count">
									<?php
										echo esc_attr(
											sprintf(
												// translators: %d is category question count.
												_n( '%d Question', '%d Questions', $category->count, 'anspress-question-answer' ),
												(int) $category->count
											)
										);
									?>
								</span>

								<a class="ap-categories-feat" style="height:<?php echo (int) ap_opt( 'categories_image_height' ); ?>px" href="<?php echo esc_url( get_category_link( $category ) ); ?>">
									<?php echo wp_kses_post( ap_get_category_image( $category->term_id, ap_opt( 'categories_image_height' ) ) ); ?>
								</a>
							</div>

							<div class="ap-term-title">
								<a class="term-title" href="<?php echo esc_url( get_category_link( $category ) ); ?>">
									<?php echo esc_html( $category->name ); ?>
								</a>

								<?php $sub_cat_count = count( get_term_children( $category->term_id, 'question_category' ) ); ?>

								<?php if ( $sub_cat_count > 0 ) : ?>
									<span class="ap-sub-category">
										<?php
											echo esc_attr(
												sprintf(
													// Translators: %d contains count of sub category.
													_n( '%d Sub category', '%d Sub categories', (int) $sub_cat_count, 'anspress-question-answer' ),
													(int) $sub_cat_count
												)
											);
										?>
									</span>
								<?php endif; ?>

							</div>

							<?php if ( ! empty( $category->description ) ) : ?>
								<div class="ap-taxo-description">
									<?php echo esc_html( ap_truncate_chars( $category->description, 120 ) ); ?>
								</div>
							<?php endif; ?>

						</div>
					</li>
				<?php endforeach; ?>

			</ul>
		</div>
		<?php ap_pagination(); ?>
	</div>

	<?php if ( is_active_sidebar( 'ap-category' ) && is_anspress() ) : ?>
		<div class="ap-question-right ap-col-3">
			<?php dynamic_sidebar( 'ap-category' ); ?>
		</div>
	<?php endif; ?>
</div>
