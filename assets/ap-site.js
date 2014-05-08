

function ap_vote(){
	jQuery('[data-vote="vote"] a').click(function(e){
		e.preventDefault();
		var args = jQuery(this).data('args');
		jQuery.ajax({  
			type: 'POST',  
			url: ajaxurl,  
			data: {  
				action: 'ap_vote_on_post',  
				args: args,  
			},  
			context:this,
			success: function(data, textStatus, XMLHttpRequest){ 
				var result = data.split('_');
				if(result[0] == '1'){
					if(result[1] == 'voted'){
						jQuery(this).addClass('voted');
						if(result[2] == 'up') jQuery(this).parent().find('.vote-down').addClass('disable');
						if(result[2] == 'down') jQuery(this).parent().find('.vote-up').addClass('disable');
					}
					else if(result[1] == 'undo'){
						jQuery(this).removeClass('voted');
						if(result[2] == 'up') jQuery(this).parent().find('.vote-down').removeClass('disable');
						if(result[2] == 'down') jQuery(this).parent().find('.vote-up').removeClass('disable');
					}
					jQuery(this).parent().find('.net-vote-count').text(result[3])
				}else{
					alert(result[1]);
				}
			},  
			error: function(MLHttpRequest, textStatus, errorThrown){  
				alert(errorThrown);  
			}  
		});  

	});
}

//favourite
function ap_favourite(){
	jQuery('a.favourite-btn').click(function(e){
		e.preventDefault();
		var args = jQuery(this).data('args');
		jQuery.ajax({  
			type: 'POST',  
			url: ajaxurl,  
			data: {  
				action: 'ap_add_to_favourite',  
				args: args,  
			},  
			context:this,
			success: function(data){ 
				var result = data.split('_');
				if(result[0] == '1'){
					if(result[1] == 'added') jQuery(this).addClass('added');
					else if(result[1] == 'removed') jQuery(this).removeClass('added');
					jQuery(this).text(result[2]);
					jQuery(this).attr('title',result[3]);
				}else{
					alert(result[1]);
				}
			},  
			error: function(MLHttpRequest, textStatus, errorThrown){  
				alert(errorThrown);  
			}  
		});  

	});
}

//close
function ap_close(){
	jQuery('a.close-btn').click(function(e){
		e.preventDefault();
		var args = jQuery(this).data('args');
		jQuery.ajax({  
			type: 'POST',  
			url: ajaxurl,  
			data: {  
				action: 'ap_vote_for_close',  
				args: args,  
			},  
			context:this,
			success: function(data){ 
				var result = data.split('_');
				if(result[0] == '1'){
					if(result[1] == 'added') jQuery(this).addClass('closed');
					else if(result[1] == 'removed') jQuery(this).removeClass('closed');
					jQuery(this).text(result[2]);
					jQuery(this).attr('title',result[3]);
				}else{
					alert(result[1]);
				}
			},  
			error: function(MLHttpRequest, textStatus, errorThrown){  
				alert(errorThrown);  
			}  
		});  

	});
}

// get flag note modal
function ap_flag_modal(){
	jQuery('a.flag-btn.can-flagged').click(function(e){
		e.preventDefault();
		var args = jQuery(this).data('args');
		jQuery.ajax({  
			type: 'POST',  
			url: ajaxurl,  
			data: {  
				action: 'ap_flag_note_modal',  
				args: args,  
			},  
			context:this,
			success: function(data){ 
				jQuery(data).appendTo('body');
				jQuery(jQuery(this).attr('href')).modal('show');

			},  
			error: function(MLHttpRequest, textStatus, errorThrown){  
				alert(errorThrown);  
			}  
		});  

	});
	
	jQuery('body').delegate(':radio[name="note_id"]', 'click', function () {
		jQuery(':radio[name="note_id"]').closest('.note').removeClass('active');
		jQuery(this).closest('.note').addClass('active');
    });
	
	jQuery('body').delegate('#submit-flag-question', 'click', function(e){
		e.preventDefault();		
		var args = jQuery(this).data('args');
		var note_id = jQuery(this).closest('.flag-note').find(':radio[name="note_id"]:checked').val();
		var other_note = jQuery(this).closest('.flag-note').find('#other-note').val();
		jQuery.ajax({  
			type: 'POST',  
			url: ajaxurl,  
			data: {  
				action: 'ap_submit_flag_note',  
				args: args,  
				note_id: note_id,  
				other_note: other_note,  
			},  
			context:this,
			success: function(data){ 
				var id = jQuery(this).closest('.flag-note').attr('id');
				var to_update = jQuery('#flag_' + jQuery(this).data('update'));
				var result = data.split('_');
				
				jQuery('#'+id).modal('hide');
				jQuery('#'+id).remove();
				
				if(result[0] == '1'){
					if(result[1] == 'flagged') to_update.addClass('flagged');
					to_update.text(result[2]);
					to_update.attr('title',result[3]);
				}else{
					alert(result[1]);
				}
			},  
			error: function(MLHttpRequest, textStatus, errorThrown){  
				alert(errorThrown);  
			}  
		});  
	});
}

function ap_load_comment_form(){
	jQuery('[data-form="comment"]').click(function(e){
		e.preventDefault();
		var args 	= jQuery(this).data('args'),
			elem	= jQuery(this).attr('href');
		
		if(jQuery(this).is('.ajax-done')){
			jQuery(elem+' #respond').slideToggle();
		}else{
			jQuery.ajax({  
				type: 'POST',  
				url: ajaxurl,  
				data: {  
					action: 'ap_load_comment_form',  
					args: args,
				},  
				context:this,
				success: function(data, textStatus, XMLHttpRequest){ 
					if(jQuery(data).selector == 'not_logged_in'){
						ap_not_logged_in();
					}else{
						jQuery(data).appendTo(elem);
						jQuery(this).addClass('ajax-done');
						jQuery('html, body').animate({
							scrollTop: (jQuery('#respond').offset().top) - 50
						}, 500);
					}
				},  
				error: function(MLHttpRequest, textStatus, errorThrown){  
					alert(errorThrown);  
				}  
			});  			
		}
	});
}


function ap_edit_comment_form(){
	jQuery('body').delegate('[data-action="edit-comment"]', 'click', function(e){
		e.preventDefault();
		var args 	= jQuery(this).data('args');

		jQuery.ajax({  
			url: ajaxurl,  
			data: {  
				action: 'ap_edit_comment_form',  
				args: args,
			},  
			context:this,
			success: function(data, textStatus, XMLHttpRequest){ 
				if(jQuery(data).selector == 'not_logged_in'){
					ap_not_logged_in();
				}else{
					jQuery(this).closest('.comment-content').html(data);
				}
			},  
			error: function(MLHttpRequest, textStatus, errorThrown){  
				alert(errorThrown);  
			}  
		});  			
		
	});
}
function ap_save_comment(){
	// this is the id of the form
	jQuery('body').delegate('[data-action="save-inline-comment"]', 'click', function(e) {
		e.preventDefault();
		jQuery.ajax({
			type: 'POST',			
			url: ajaxurl,
			data: {  
				action: 'ap_save_comment_form',  
				args: jQuery(jQuery(this).data('elem')).serialize(),
			},
			context:this,			
			success: function(data){
			  jQuery(this).closest('.comment-content').html(data);
			}
		});

	});
}
function ap_delete_comment(){
	jQuery('body').delegate('[data-action="delete-comment"]', 'click', function(e){
		e.preventDefault();
		if(confirm(jQuery(this).data('confirm'))){
			var args 	= jQuery(this).data('args');

			jQuery.ajax({  
				url: ajaxurl,  
				data: {  
					action: 'ap_delete_comment',  
					args: args,
				},  
				context:this,
				success: function(data, textStatus, XMLHttpRequest){ 
					if(jQuery(data).selector == 'not_logged_in'){
						ap_not_logged_in();
					}else{
						jQuery(this).closest('li.comment').remove();
					}
				},  
				error: function(MLHttpRequest, textStatus, errorThrown){  
					alert(errorThrown);  
				}  
			});  
		}		
	});
}
function ap_not_logged_in(){

	if(jQuery('#please-login').length){
		jQuery('#please-login').slideToggle();
	}else{
		jQuery.ajax({  
			type: 'POST',  
			url: ajaxurl,  
			data: {  
				action: 'ap_not_logged_in_messgae'
			},  
			context:this,
			success: function(data, textStatus, XMLHttpRequest){ 
				jQuery(data).appendTo('body');
			},  
			error: function(MLHttpRequest, textStatus, errorThrown){  
				alert(errorThrown);  
			}  
		});  			
	}
}


function ap_change_status(){
	jQuery('[data-action="change-status"]').click(function(e){
		e.preventDefault();
		
		jQuery(this).closest('#change-status').toggleClass('active');
		var args 	= jQuery(this).data('args');		
		
	});	
	jQuery('[data-action="set-status"]').click(function(e){
		e.preventDefault();
		jQuery.ajax({  
			type: 'POST',  
			url: ajaxurl,  
			data: {  
				action: 'ap_set_status',
				args: jQuery(this).data('args')
			},  
			context:this,
			success: function(data, textStatus, XMLHttpRequest){ 
				//jQuery('#change-status').replaceWith(data);
				location.reload(); 
			},  
			error: function(MLHttpRequest, textStatus, errorThrown){  
				//alert(errorThrown);  
			}  
		});	
	});
}

function ap_toggle_sub_cat(){
	jQuery('.sub-cat-count').click(function(e){
		e.preventDefault();
		jQuery(this).closest('.taxo-footer').find('.child').slideToggle(200);
	});
}

jQuery(document).ready(function (){  
	ap_vote();
	ap_favourite();
	ap_close();
	ap_flag_modal();
	ap_load_comment_form();
	ap_edit_comment_form();
	ap_save_comment();
	ap_delete_comment();
	ap_change_status();
	ap_toggle_sub_cat();
	
	jQuery('body').delegate('#please-login button', 'click' ,function(){
		jQuery(this).parent().slideToggle();
	});
	
}); 

