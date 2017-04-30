<?php
	/**
	 * Tags page layout
	 *
	 * @link http://anspress.io
	 * @since 1.0
	 *
	 * @package WordPress/AnsPress
	 */

	global $question_tags;
?>
<?php dynamic_sidebar( 'ap-top' ); ?>

<div id="ap-tags" class="row">
	<div class="<?php echo is_active_sidebar( 'ap-tags' ) && is_anspress() ? 'ap-col-9' : 'ap-col-12' ?>">

		<div class="ap-list-head clearfix">
			<form id="ap-search-form" class="ap-search-form pull-left" action="<?php echo ap_get_link_to('tags'); ?>?type=tags">
			    <input name="ap_s" type="text" class="ap-form-control" placeholder="<?php _e('Search tags...', 'ap'); ?>" value="<?php echo sanitize_text_field( get_query_var('ap_s') ); ?>" />
			    <input name="type" type="hidden" value="tags" />
			</form>
			<?php ap_tags_tab(); ?>
		</div><!-- close .ap-list-head.clearfix -->

		<ul class="ap-term-tag-box clearfix">
			<?php foreach($question_tags as $key => $tag) : ?>
				<li class="clearfix">
					<div class="ap-tags-item">
						<div class="ap-term-title">
							<a class="term-title" href="<?php echo get_tag_link( $tag );?>">
								<?php echo $tag->name; ?>
							</a>
							<span class="ap-tagq-count">
								&times; <?php printf(_n('%d Question', '%d Questions', $tag->count, 'tags-for-anspress'), $tag->count) ?>
							</span>
						</div>

						<div class="ap-taxo-description">
							<?php
								if($tag->description != '')
									echo $tag->description;
								else
									_e('No description.', 'tags-for-anspress');
							?>
						</div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul><!-- close .ap-term-tag-box.clearfix -->

		<?php ap_pagination(); ?>
	</div><!-- close #ap-tags -->

	<?php if ( is_active_sidebar( 'ap-tags' ) && is_anspress()){ ?>
		<div class="ap-tags-sidebar ap-col-3">
			<?php dynamic_sidebar( 'ap-tags' ); ?>
		</div>
	<?php } ?>

</div><!-- close .row -->

