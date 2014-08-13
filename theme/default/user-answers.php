<div id="ap-user-answer">
	<div id="answers-c">
	<?php if ( $answer->have_posts() ) : ?>
	<?php ap_ans_list_tab(); ?>
		<div id="answers">
			<?php
				while ( $answer->have_posts() ) : $answer->the_post(); 
					include(ap_get_theme_location('answer-list.php'));
				endwhile ;
			?>
		</div>
	<?php ap_pagination('', 2, $paged, $answer); ?>
	<?php
		else : 
			_e('No answers posted yet.', 'ap');
		endif; 
	?>	
	</div>
</div>