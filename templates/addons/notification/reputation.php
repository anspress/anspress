<?php
/**
 * Notification reputation type template.
 *
 * Render notification item if ref_type is reputation.
 *
 * @author  Rahul Aryan <support@anspress.io>
 * @link    https://anspress.io/
 * @since   4.0.0
 * @package WordPress/AnsPress
 */

?>
<div class="ap-noti-item clearfix">
	<div class="ap-noti-rep"><?php $this->the_reputation_points(); ?></div>
	<a class="ap-noti-inner" href="<?php $this->the_permalink(); ?>">
		<?php $this->the_verb(); ?>
		<time class="ap-noti-date"><?php $this->the_date(); ?></time>
	</a>
</div>
