<?php

 $clearfix_class = array('clearfix');

?>

<article id="post-0" <?php post_class($clearfix_class); ?>>
	<div class="no-questions">
		<?php _e('No question asked yet!, be the first to ask a question.', 'ap'); ?>		
		<a href="<?php echo get_permalink(ap_opt('ask_page_id')); ?>"><?php _e('Ask question', 'ap'); ?></a>
	</div>
</article><!-- list item -->
