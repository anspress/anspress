
/* on start */
jQuery(function() {
     
    /* create document */
    APjs.site = new APjs.site();
    /* need to call init manually with jQuery */
    APjs.site.initialize();
 
});
 
/* namespace */
window.APjs = {};
APjs.site = function() {};
 

APjs.site.prototype = {
	
	/* automatically called */
	initialize: function() {
		if(jQuery('.for-non-logged-in #password').length > 0){		
			jQuery('.for-non-logged-in #password1').on('click chnage keyup', jQuery.proxy(this.checkPasswordMatch, this, '.for-non-logged-in #password', '.for-non-logged-in #password1'));
			jQuery('.for-non-logged-in #password').on('click chnage keyup', jQuery.proxy(this.checkPasswordLength, this, '#password'));
		}
		if(jQuery('.for-non-logged-in #email').length > 0)
			jQuery('.for-non-logged-in #email').on('click chnage keyup', jQuery.proxy(this.checkEmail, this, '.for-non-logged-in #email'));
			
		if(jQuery('.for-non-logged-in #username').length > 0)
			jQuery('.for-non-logged-in #username').on('click chnage keyup', jQuery.proxy(this.checkUsernameLength, this, '.for-non-logged-in #username'));
			
		if(jQuery('.for-non-logged-in #username').length > 0)
			jQuery('.for-non-logged-in #username').blur( jQuery.proxy(this.checkUsernameAvilable, this, '.for-non-logged-in #username'));
		
		if(jQuery('.for-non-logged-in #email').length > 0)
			jQuery('.for-non-logged-in #email').blur( jQuery.proxy(this.checkEmailAvilable, this, '.for-non-logged-in #email'));

		this.castVote();
		this.favourite();
	},
	
	/* add bootstrap validation class */
	fieldValidationClass: function(field, vclass){
		jQuery(field).addClass(vclass);
	},
	
	/* add help block below field */
	helpBlock:function(elm, message){
		/* remove existing help block */
		if(jQuery(elm).find('.help-block').length > 0)
			jQuery(elm).find('.help-block').remove();
			
		jQuery(elm).append('<span class="help-block">'+message+'</span>');
	},
	
	toggleFormSubmission:function(form, toggle){
		//jQuery(form).submit(toggle);		
		if(toggle && jQuery(form).find('.has-error').length == 0)
			jQuery(form).find('button[type="submit"]').removeAttr('disabled');
		else
			jQuery(form).find('button[type="submit"]').attr('disabled', 'disabled');
	},
	
	isEmail : function (email) {
	  var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	  return regex.test(email);
	},
	
 	/* check for matching password between to fields  */
	checkPasswordMatch : function (field1, field2) {
		var password = jQuery(field1).val();
		var confirmPassword = jQuery(field2).val();
		
		var parent = jQuery(field2).closest('.form-group');
		if (password != confirmPassword){
			this.fieldValidationClass(parent, 'has-error');
			if(parent.find('.help-block').length == 0)
				this.helpBlock(parent, aplang.password_field_not_macthing);
			
			this.toggleFormSubmission(jQuery(field2).closest('form'), false);
		}
		else{
			parent.removeClass('has-error');
			this.fieldValidationClass(parent, 'has-feedback');
			if(parent.find('.help-block').length >0)
				parent.find('.help-block').remove();
			
			this.toggleFormSubmission(jQuery(field2).closest('form'), true);
		}
	},
	
	/* check for password length */
	checkPasswordLength:function(field){
		var password = jQuery(field).val(),
			parent = jQuery(field).closest('.form-group');
		
		if(password.length < 6){
			this.fieldValidationClass(parent, 'has-error');
			this.helpBlock(parent, aplang.password_length_less);
			this.toggleFormSubmission(jQuery(field).closest('form'), false);
		}else{
			parent.removeClass('has-error');
			this.fieldValidationClass(parent, 'has-feedback');
			parent.find('.help-block').remove();
			this.toggleFormSubmission(jQuery(field).closest('form'), true);
		}
	},
	
	/* check for valid email */
	checkEmail: function(field){
		var field = jQuery(field);		
		var parent = jQuery(field).closest('.form-group');
		
		if (!this.isEmail(field.val())){
			this.fieldValidationClass(parent, 'has-error');
			if(parent.find('.help-block').length == 0)
				this.helpBlock(parent, aplang.not_valid_email);
			
			this.toggleFormSubmission(jQuery(field).closest('form'), false);
		}
		else{
			parent.removeClass('has-error');
			this.fieldValidationClass(parent, 'has-feedback');
			if(parent.find('.help-block').length >0)
				parent.find('.help-block').remove();
			
			this.toggleFormSubmission(jQuery(field).closest('form'), true);
		}	
	},
	
	/* check length of username */
	checkUsernameLength: function(field){
		var field = jQuery(field);		
		var parent = field .closest('.form-group');
		if (field.val().length <= 4){
			this.fieldValidationClass(parent, 'has-error');
			if(parent.find('.help-block').length == 0)
				this.helpBlock(parent, aplang.username_less);
			
			this.toggleFormSubmission(field.closest('form'), false);
		}
		else{
			parent.removeClass('has-error');
			this.fieldValidationClass(parent, 'has-feedback');
			if(parent.find('.help-block').length >0)
				parent.find('.help-block').remove();
			
			this.toggleFormSubmission(field.closest('form'), true);
		}
	},
	
	/* check if username is available */
	checkUsernameAvilable: function(field){
		var field = jQuery(field);		
		var parent = field .closest('.form-group');
		
		jQuery.ajax({  
			type: 'POST',  
			url: ajaxurl,  
			data: {  
				action: 'ap_check_username',  
				username: jQuery(field).val(),  
			},  
			context:this,
			success: function(data){ 
				if(data == 'true'){
					parent.removeClass('has-error');
					this.fieldValidationClass(parent, 'has-feedback');
					if(parent.find('.help-block').length >0)
						parent.find('.help-block').remove();
					
					this.toggleFormSubmission(field.closest('form'), true);
				}else{
					this.fieldValidationClass(parent, 'has-error');
					if(parent.find('.help-block').length == 0)
						this.helpBlock(parent, aplang.username_not_avilable);
					
					this.toggleFormSubmission(field.closest('form'), false);
				}
				
			} 
		}); 
	},
	/* check if email is available */
	checkEmailAvilable: function(field){
		var field = jQuery(field);		
		var parent = field .closest('.form-group');
		
		jQuery.ajax({  
			type: 'POST',  
			url: ajaxurl,  
			data: {  
				action: 'ap_check_email',  
				email: jQuery(field).val(),  
			},  
			context:this,
			success: function(data){ 
				if(data == 'true'){
					parent.removeClass('has-error');
					this.fieldValidationClass(parent, 'has-feedback');
					if(parent.find('.help-block').length >0)
						parent.find('.help-block').remove();
					
					this.toggleFormSubmission(field.closest('form'), true);
				}else{
					this.fieldValidationClass(parent, 'has-error');
					if(parent.find('.help-block').length == 0)
						this.helpBlock(parent, aplang.email_already_in_use);
					
					this.toggleFormSubmission(field.closest('form'), false);
				}
				
			} 
		}); 
	},
	
	castVote:function() {
		jQuery('[data-action="vote"] a').click(function(e){
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
	},
	favourite:function (){
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
				dataType: 'json',
				context:this,
				success: function(data){ 
					if(data['action'] == 'added' || data['action'] == 'removed'){
						if(data['action'] == 'added') jQuery(this).addClass('added');
						else if(data['action'] == 'removed') jQuery(this).removeClass('added');
						jQuery(this).text(data['count']);
						jQuery(this).attr('title',data['title']);
						jQuery(this).parent().find('span').html(data['text']);
					}else{
						alert(data['title']);
					}
				},  
				error: function(MLHttpRequest, textStatus, errorThrown){  
					alert(errorThrown);  
				}  
			});  

		});
	},
	
 
};


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

