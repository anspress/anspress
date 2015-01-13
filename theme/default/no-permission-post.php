<div class="ap-no-permission-to-view">
	<?php
		$post_type = get_post_status( );
		if($post_type == 'private_post')
			printf(__('This is a private %s.'), get_post_type());
		elseif($post_type == 'moderate')
			$type = sprintf(__('This %s waiting for moderation.'), get_post_type());
	?>
</div>