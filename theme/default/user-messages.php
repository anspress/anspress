<div id="ap-messages">
	<div class="ap-messages-left">
		<div class="ap-message-search">
			<input type="text" class="form-control" placeholder="<?php _e('Search messages', 'ap');  ?>" />
		</div>
		<?php ap_conversations_list(1); ?>
	</div>
	<div class="ap-messages-right" data-view="conversation">
		<?php ap_get_conversation_list(205); ?>		
	</div>
</div>