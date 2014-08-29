<div id="ap-user-badges">
	<ul class="ap-list-badges">
	<?php 
		if($user_badges):
			foreach($user_badges as $b){
				echo '<li><ul class="ap-badge-item clearfix">';
					$badge = ap_badge_by_id($b->badge_id);
					echo '<li class="ap-badge-type"><i class="ap-icon-badge badge-'.$badge['type'].'"></i><span>'.$badge['type'].'</span></li>';
					echo '<li class="ap-badge-info"><strong>'.$badge['title'].'</strong><span>'.sprintf($badge['description'], $badge['value']).'</span></li>';
					echo '<li class="ap-total-badge">'.sprintf(_n('<span>1</span> Badge', '<span>%d</span> Badges', $count_badges[$b->badge_id], 'ap'), $count_badges[$b->badge_id]).'</li>';
					echo '<li class="ap-badge-date ap-icon-clock">'.ap_human_time($b->date, false).'</li>';
				echo '</ul></li>';
			}
		else:
			echo '<li class="ap-nobadges">'. __('No badges earned yet!', 'ap') .'</li>';
		endif;
	?>
	</ul>
</div>