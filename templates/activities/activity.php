<?php
/**
 * Activity item template.
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

?>
<div class="ap-activity-item">

	<?php if ( $activities->have_group_items() ) : ?>
		<div class="ap-activity-icon">
			<i class="<?php $activities->the_icon(); ?>"></i>
		</div>
	<?php else : ?>
		<div class="ap-activity-avatar">
			<?php echo $activities->get_avatar(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>

	<div class="ap-activity-right">

		<?php if ( $activities->have_group_items() ) : ?>

			<div class="ap-activity-content">
				<div class="ap-activity-header">
					<?php
					echo wp_kses_post(
						ap_user_display_name(
							array(
								'user_id'      => $activities->get_user_id(),
								'html'         => true,
								'full_details' => true,
							)
						)
					);
					?>
					<span class="ap-activity-verb"><?php $activities->the_verb(); ?></span>
					<span>
					<?php
						$count = $activities->count_group();

						echo esc_attr(
							sprintf(
								// translators: %d is activity count.
								_n( 'with other activity', 'with other %d activities', $count, 'anspress-question-answer' ), // phpcs:ignore WordPress.WP.I18n
								(int) $count
							)
						);
					?>
					</span>
				</div>

				<div class="ap-activity-ref">
					<a href="<?php echo esc_url( get_permalink( $activities->get_q_id() ) ); ?>"><?php echo esc_html( get_the_title( $activities->get_q_id() ) ); ?></a>
				</div>

				<div class="ap-activities-same">
					<?php $activities->group_start(); ?>

					<?php
					while ( $activities->have_group() ) :
						$activities->the_object();
						?>
						<div class="ap-activity-same">
							<div class="ap-activity-avatar">
								<?php echo wp_kses_post( $activities->get_avatar( 35 ) ); ?>
							</div>

							<div class="ap-activity-right">
								<div class="ap-activity-header">
									<?php
									echo wp_kses_post(
										ap_user_display_name(
											array(
												'user_id' => $activities->get_user_id(),
												'html'    => true,
												'full_details' => true,
											)
										)
									);
									?>
								</div>

								<div class="ap-activity-ref">
									<span class="ap-activity-verb"><?php $activities->the_verb(); ?></span> <time class="ap-activity-date"><?php echo esc_html( ap_human_time( $activities->get_the_date(), false ) ); ?></time>
								</div>

								<div class="ap-activity-ref">
									<?php $activities->the_ref_content(); ?>
								</div>
							</div>

						</div>
					<?php endwhile; ?>

					<?php $activities->group_end(); ?>
				</div>

			</div>

		<?php else : ?>
			<div class="ap-activity-content">

				<div class="ap-activity-header">
					<?php
					echo wp_kses_post(
						ap_user_display_name(
							array(
								'user_id'      => $activities->get_user_id(),
								'html'         => true,
								'full_details' => true,
							)
						)
					);
					?>
					<span class="ap-activity-verb"><?php $activities->the_verb(); ?></span>
					<time class="ap-activity-date"><?php echo esc_html( ap_human_time( $activities->get_the_date(), false ) ); ?></time>
				</div>

				<div class="ap-activity-ref">
					<?php $activities->the_ref_content(); ?>
				</div>

			</div>
		<?php endif; ?>

	</div>
</div>
