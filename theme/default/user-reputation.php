<?php
if($reputation){
	foreach($reputation as $r){
		?>
			<div class="ap-reputation-item">
				<span class="point"><?php echo $r->apmeta_value ?></span>
				<div class="no-overflow">
					<span class="info"><?php echo ap_get_reputation_info($r); ?></span>
					<span class="time"><?php printf( __('%s ago', 'ap'), ap_human_time($r->apmeta_date, false)); ?></span>
				</div>
			</div>
		<?php
	}
}else{
	_e('User does not have any reputation yet', 'ap');
}
