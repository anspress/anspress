<?php
/**
 * Display AnsPress user page
 *
 * @link http://wp3.in
 * @since 2.0.1
 *
 * @package AnsPress
 */
?>
<div class="ap-user-rep">
	<?php
		if(ap_has_reputations()){

			while ( ap_reputations() ) : ap_the_reputation();
				ap_get_template_part('user/reputation-content');
			endwhile;

		}else{
			_e('No users found');
		}

	?>
</div>
