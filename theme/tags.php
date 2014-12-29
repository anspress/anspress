<?php
	/**
	 * Tags page layout
	 *
	 * @link http://wp3.in
	 * @since 1.0
	 *
	 * @package AnsPress
	 * @subpackage Tags for AnsPress
	 */

	global $question_tags;
?>

<div id="ap-tags" class="clearfix">
	<ul class="ap-term-tag-box">
		<?php foreach($question_tags as $key => $tag) : ?>
			<li>
				<a class="term-title" href="<?php echo get_category_link( $tag );?>">
					<?php echo $tag->name; ?>
				</a>
				&times; <?php echo $tag->count ?>
				<div class="ap-taxo-description">
					<?php
						if($tag->description != '')
							echo substr($tag->description, 0, 110) ;
						else
							_e('No dscription.', 'tags_for_anspress');
					?>
				</div>
			</li>
		<?php endforeach; ?>
	</ul>
</div>
<?php ap_pagination(); ?>
