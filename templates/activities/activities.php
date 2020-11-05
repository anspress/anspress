<?php
/**
 * Activities template.
 *
 * @link       https://anspress.net
 * @since      4.1.2
 * @license    GPL3+
 * @package    AnsPress
 * @subpackage Templates
 *
 * @global object $activities Activity query.
 */

?>
<div class="ap-activities">
	<?php if ( $activities->have() ) : ?>

		<?php
		// Loop for getting activities.
		while ( $activities->have() ) :
			$activities->the_object();
			// Shows date and time for timeline.
			$activities->the_when();

			/**
			 * Load activity item. Here we are not using `get_template_part()` because
			 * we wants to let template easily access PHP variables.
			 */
			include ap_get_theme_location( 'activities/activity.php' );
		endwhile;
		?>

		<?php
		// Wether to show load more button or not.
		if ( ! $activities->have_pages() ) :
		?>
			<div class="ap-activity-end ap-activity-item">
				<div class="ap-activity-icon">
					<i class="apicon-check"></i>
				</div>
				<p><?php _e( 'That\'s all!', 'anspress-question-answer' ); ?></p>
			</div>
		<?php else : ?>
			<div class="ap-activity-more ap-activity-item">
				<div class="ap-activity-icon">
					<i class="apicon-dots"></i>
				</div>
				<div>
					<?php $activities->more_button(); ?>
				</div>
			</div>
		<?php endif; ?>

	<?php
	else :
		// When no activities found.
		?>
		<p><?php esc_attr_e( 'No activities found!', 'anspress-question-answer' ); ?></p>
	<?php endif; ?>

</div>
