<?php if(ap_opt('enable_tags')) : ?>
<h1 class="entry-title">
		<?php if (!ap_opt('double_titles'))
		the_title(); 
		?>
	<a class="ap-btn ap-ask-btn-head pull-right" href="<?php echo ap_get_link_to('ask') ?>"><?php _e('Ask Question'); ?></a>
</h1>
	<div id="ap-tags" class="clearfix">
		<ul class="ap-term-list ap-inline-list">
			<?php foreach($tags as $key => $tag) : ?>
				<li>
					<div class="ap-term-list-inner">
						<a class="term-title" href="<?php echo get_category_link( $tag );?>"><span>
							<?php echo $tag->name; ?>
						</span></a>
						
						<span> &times; <?php echo $tag->count; ?></span>		
						<p><?php echo ap_truncate_chars($tag->description, 70); ?></p>					
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
<?php else: ?>
	<div class="ap-tax-disabled">
		<?php _e('Tags are disabled', 'ap'); ?>
	</div>
<?php endif; ?>