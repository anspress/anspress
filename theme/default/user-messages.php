<div id="ap-messages">
	<div class="ap-messages-left">
		<div class="ap-messages-left-c">
			<div class="ap-message-search">
				<form method="GET" data-role="ap-search-conversations">
					<input type="text" class="form-control" name="s" placeholder="<?php _e('Search messages', 'ap');  ?>" data-action="ap-search-conversations" />
					<input type="hidden" name="action" value="ap_message_search" />
					<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('search_message'); ?>" />
				</form>
			</div>
			<div id="ap-conversation-scroll" data-offset="1" data-args="<?php echo wp_create_nonce('conversations_list'); ?>">
				<?php ap_conversations_list(); ?>
			</div>
		</div>
	</div>
	<!--<div class="ap-messages-right" data-view="conversation">
	</div>-->
</div>