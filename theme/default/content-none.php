<?php

 $clearfix_class = array('clearfix');

?>

<article id="post-0" <?php post_class($clearfix_class); ?>>
	<div class="no-questions">
		<?php _e('No question asked yet!, be the first to ask a question.', 'ap'); ?>
		
		<h2><?php _e('Ask question', 'ap'); ?></h2>
		<?php ap_ask_form(); ?>
	</div>
</article><!-- list item -->
