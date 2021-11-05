<?php
/**
 * Template used to display comment item in notification.
 *
 * @link        http://anspress.net
 * @since       4.0
 * @package     AnsPress
 * @subpackage  Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="ap-noti-item clearfix">
	<div class="ap-noti-avatar"><?php $this->the_actor_avatar(); ?></div>
	<a class="ap-noti-inner" href="<?php $this->the_permalink(); ?>">
		<strong class="ap-not-actor"><?php $this->the_actor(); ?></strong> <?php $this->the_verb(); ?>
		<strong class="ap-not-ref"><?php $this->the_ref_title(); ?></strong>
		<time class="ap-noti-date"><?php $this->the_date(); ?></time>
	</a>
</div>
