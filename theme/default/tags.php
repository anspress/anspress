<?php if(ap_opt('enable_tags')) : ?>
	<div id="ap-tags" class="clearfix">
		<ul class="ap-tags-list ap-inline-list">
			<?php foreach($tags as $key => $tag) : ?>
				<li>
					<div class="tag-list-inner">
						<a class="ap-tags" href="<?php echo get_category_link( $tag );?>"><span>
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