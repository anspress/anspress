<?php dynamic_sidebar( 'ap-top' ); ?>

<?php ap_get_template_part('list-head'); ?>
<?php if ( ap_have_questions() ) : ?>
	<div class="ap-questions">
		<?php
			/* Start the Loop */
			while ( ap_questions() ) : ap_the_question();
				ap_get_template_part('content-list');
			endwhile;
		?>
	</div>
<?php ap_questions_the_pagination(); ?>
<?php
	else :
		ap_get_template_part('content-none');
	endif;
?>



