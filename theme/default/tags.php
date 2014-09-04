<?php if(ap_opt('enable_tags')) : ?>
<h1 class="ap-q-title">
	<?php the_title(); ?>
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