
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
		if(jQuery('.ap-signup-fields #password').length > 0){		
			jQuery('.ap-signup-fields #password1').on('keyup', jQuery.proxy(this.checkPasswordMatch, this, '.ap-signup-fields #password', '.ap-signup-fields #password1'));
			jQuery('.ap-signup-fields #password').on('keyup', jQuery.proxy(this.checkPasswordLength, this, '#password'));
		}
		
		this.checkEmail();
		this.checkEmailAvailable();
		
		this.castVote();
		this.favorite();
		this.appendMessageBox();
		this.close();
		this.loadFlagModal();
		this.flagModalSubmit();
		this.followUser();
		this.dropdown();
		this.dropdownClose();
		this.load_comment_form();
		this.load_edit_comment_form();
		this.submitAnswer();
		this.submitQuestion();
		
		this.toggleLoginSignup();
		
		this.saveComment();
		this.updateComment();
		this.deleteComment();
		
		this.selectBestAnswer();
		this.deletePost();
		this.tab();
		this.labelSelect();
		this.saveLabel();
		this.tagsScript();
		this.tagsSuggestion();
		this.addTag();
		this.uploadForm();
		this.saveProfile();
		this.sendMessage();
		this.showConversation();
		this.newMessageButton();
		this.userSuggestion();
		this.searchMessage();
		this.editMessage();
		this.deleteMessage();
		this.questionSuggestion();
		this.flushRules();
		this.loadNewTagForm();
		this.newTag();
		this.loginAccor();
		
		
		jQuery('body').delegate('.ap-modal-bg, .ap-modal-close', 'click', function () {
			jQuery('.ap-modal.active').toggleClass('active');
		});
		
		jQuery('body').delegate('.ap-open-modal', 'click', function(e){
			e.preventDefault();
			var id = jQuery(this).attr('href');
			/* ensure all other models are closed */
			jQuery('.ap-open-modal').each(function() {
				var model_id = jQuery(this).attr('href')
				jQuery(model_id).removeClass('active');
			});

			/* activate our model */
			jQuery(id).addClass('active');
		});
		
		
	},
	
	appendMessageBox: function(){
		if(jQuery('#ap-messagebox').length == '0')
			jQuery('body').append('<div id="ap-messagebox"></div>');
	},
	
	showLoading: function(message){
		/* set the default message if no message passed */
		if(typeof message == 'undefined')message = aplang.loading;

		if(jQuery('#ap-ajax-loading').length > 0)
			jQuery('#ap-ajax-loading').text(message);
		else
			jQuery('#ap-messagebox').append('<div style="display:none" id="ap-ajax-loading">'+message+'</div>');
		
		jQuery('#ap-ajax-loading').slideDown(200);
	},
	
	/* change the message of loading */
	addMessage: function(message, type){
		jQuery('<div class="ap-message-item '+type+'">'+message+'</div>').appendTo('#ap-messagebox').delay(5000).slideUp(200);
	},
	
	hideLoading:function(){
		jQuery('#ap-ajax-loading').delay(500).slideUp(200);
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
	  var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
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
		var self = this;
		jQuery('.anspress').delegate('form #email', 'keyup blur', function(){
			var field = jQuery(this);		
			var parent = jQuery(field).closest('.form-group');

			if (!self.isEmail(field.val())){
				self.fieldValidationClass(parent, 'has-error');
				if(parent.find('.help-block').length == 0)
					self.helpBlock(parent, aplang.not_valid_email);
				
				self.toggleFormSubmission(jQuery(field).closest('form'), false);
			}
			else{
				parent.removeClass('has-error');
				self.fieldValidationClass(parent, 'has-feedback');
				if(parent.find('.help-block').length >0)
					parent.find('.help-block').remove();
				
				self.toggleFormSubmission(jQuery(field).closest('form'), true);
			}
		});
	},
	

	/* check if email is available */
	checkEmailAvailable: function(){
		var self = this;
		jQuery('.anspress').delegate('form #email', 'blur', function(){
			var field = jQuery(this);		
			var parent = field .closest('.form-group');
			
			if(!jQuery(parent).is('.has-error'))
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
						self.fieldValidationClass(parent, 'has-feedback');
						if(parent.find('.help-block').length >0)
							parent.find('.help-block').remove();
						
						self.toggleFormSubmission(field.closest('form'), true);
					}else{
						self.fieldValidationClass(parent, 'has-error');
						if(parent.find('.help-block').length == 0)
							self.helpBlock(parent, aplang.email_already_in_use);
						
						self.toggleFormSubmission(field.closest('form'), false);
					}
					
				} 
			});
		});			
	},
	doAjaxForm: function(data, callback, context){
		context 	= typeof context !== 'undefined' ? context : false;
		callback 	= typeof callback !== 'undefined' ? callback : false;
		
		return jQuery.ajax({  
			type: 		'POST',  
			url: 		ajaxurl,  
			data: 		data,
			dataType: 	'json',
			context: 	context,
			success: 	callback 
		});	
	},
	castVote:function() {
		var self = this;
		jQuery('.anspress').delegate('[data-action="vote"] a', 'click', function(e){
			e.preventDefault();
			var args = jQuery(this).data('args');
			self.showLoading(aplang.voting_on_post);
			jQuery.ajax({  
				type: 'POST',  
				url: ajaxurl,  
				data: {  
					action: 'ap_vote_on_post',  
					args: args,  
				},
				dataType: 'json',
				context:this,
				success: function(result, textStatus, XMLHttpRequest){ 
					self.hideLoading();
					if(result['action'] == 'voted' || result['action'] == 'undo'){
						if(result['action'] == 'voted'){
							jQuery(this).addClass('voted');
							if(result['type'] == 'up') jQuery(this).parent().find('.vote-down').addClass('disable');
							if(result['type'] == 'down') jQuery(this).parent().find('.vote-up').addClass('disable');
							
							jQuery(this).trigger('voted', result);
						}
						else if(result['action'] == 'undo'){
							jQuery(this).removeClass('voted');
							if(result['type'] == 'up') jQuery(this).parent().find('.vote-down').removeClass('disable');
							if(result['type'] == 'down') jQuery(this).parent().find('.vote-up').removeClass('disable');
							jQuery(this).trigger('undo_vote', result);
						}
						jQuery(this).parent().find('.net-vote-count').text(result['count']);
						self.addMessage(result['message'], 'success');
					}else{
						self.addMessage(result['message'], 'error');
					}
				} 
			});  

		});
	},
	favorite:function (){
		var self = this;
		jQuery('.anspress').delegate('[data-action="ap-favorite"]', 'click', function(e){
			e.preventDefault();

			/* show loading message */
			self.showLoading(aplang.adding_to_fav);
			
			var args = jQuery(this).data('args');
			jQuery.ajax({  
				type: 'POST',  
				url: ajaxurl,  
				data: {  
					action: 'ap_add_to_favorite',  
					args: args,  
				},
				dataType: 'json',
				context:this,
				success: function(data){ 
					/* hide loading message */
					self.hideLoading();
					if(data['action'] == 'added' || data['action'] == 'removed'){
						if(data['action'] == 'added') jQuery(this).addClass('added');
						else if(data['action'] == 'removed') jQuery(this).removeClass('added');
						jQuery(this).html(data['count']);
						jQuery(this).attr('title',data['title']);
						jQuery(this).parent().find('span').html(data['text']);
						self.addMessage(data['message'], 'success');
					}else{
						self.addMessage(data['message'], 'error');
					}
				},  
				error: function(MLHttpRequest, textStatus, errorThrown){  
					alert(errorThrown);  
				}  
			});  

		});
	},
	
	close:function(){
		var self = this;
		jQuery('.anspress').delegate('[data-action="close-question"]', 'click', function(e){
			e.preventDefault();
			var args = jQuery(this).data('args');
			
			self.showLoading(aplang.requesting_for_closing);
			
			jQuery.ajax({  
				type: 'POST',  
				url: ajaxurl,  
				data: {  
					action: 'ap_vote_for_close',  
					args: args,  
				}, 
				dataType: 'json',				
				context:this,
				success: function(result){ 
					/* hide loading message */
					self.hideLoading();
					if(result['row']){
						if(result['action'] == 'added') 
							jQuery(this).addClass('closed');
						else if(result['action'] == 'removed') 
							jQuery(this).removeClass('closed');
						
						jQuery(this).text(result['text']);
						jQuery(this).attr('title', result['title']);
						self.addMessage(result['message'], 'success');
						
					}else{
						self.addMessage(result['message'], 'error');
					}
				},  
				error: function(MLHttpRequest, textStatus, errorThrown){  
					alert(errorThrown);  
				}  
			});  

		});
	},
	
	modalShow:function(id){
		jQuery('.ap-modal').addClass('active');			
	},
	
	loadFlagModal:function (){
		var self = this;
		jQuery('.anspress').delegate('[data-action="flag-modal"]', 'click', function(e){
			e.preventDefault();
			if(!jQuery(this).is('.loaded')){
				var args = jQuery(this).data('args');
				self.showLoading();
				jQuery.ajax({  
					type: 'POST',  
					url: ajaxurl,  
					data: {  
						action: 'ap_flag_note_modal',  
						args: args,  
					},  
					context:this,
					success: function(data){ 
						self.hideLoading();
						jQuery(data).appendTo('body');
						self.modalShow(jQuery(this).attr('href'));
						jQuery(this).addClass('loaded');
					},  
					error: function(MLHttpRequest, textStatus, errorThrown){  
						alert(errorThrown);  
					}  
				});
			}else{
				self.modalShow(jQuery(this).attr('href'));
			}

		});
		
		jQuery('body').delegate(':radio[name="note_id"]', 'click', function () {
			jQuery(':radio[name="note_id"]').closest('.note').removeClass('active');
			jQuery(this).closest('.note').addClass('active');
		});		
		
	},
	
	flagModalSubmit: function(){
		var self = this;
		jQuery('body').delegate('#submit-flag-question', 'click', function(e){
			e.preventDefault();		
			var args = jQuery(this).data('args');
			var note_id = jQuery(this).closest('.flag-note').find(':radio[name="note_id"]:checked').val();
			var other_note = jQuery(this).closest('.flag-note').find('#other-note').val();
			self.showLoading(aplang.sending_request);
			jQuery.ajax({  
				type: 'POST',  
				url: ajaxurl,  
				data: {  
					action: 'ap_submit_flag_note',  
					args: args,  
					note_id: note_id,  
					other_note: other_note,  
				},
				dataType:'json',
				context:this,
				success: function(result){ 
					self.hideLoading();
					var id = jQuery(this).closest('.flag-note').attr('id');
					var to_update = jQuery('#flag_' + jQuery(this).data('update'));
					
					jQuery('#'+id).removeClass('active');
					//jQuery('#'+id).remove();
					
					if(result['row'] == '1'){
						if(result['action'] == 'flagged') to_update.addClass('flagged');
						to_update.text(result['text']);
						to_update.attr('title',result['title']);
						self.addMessage(result['message'], 'success');
					}else{
						self.addMessage(result['message'], 'error');
					}
				},  
				error: function(MLHttpRequest, textStatus, errorThrown){  
					alert(errorThrown);  
				}  
			});  
		});
	},
	
	followUser: function(){
		var self = this;
		jQuery('body').delegate('[data-action="ap-follow"]', 'click', function(e){
			e.preventDefault();		
			var args = jQuery(this).data('args');			
			self.showLoading(aplang.sending_request);
			jQuery.ajax({  
				type: 'POST',  
				url: ajaxurl,  
				data: {  
					action: 'ap_follow',  
					args: args
				},
				dataType:'json',
				context:this,
				success: function(data){
					self.hideLoading();
				
					if(data['action'] == 'pleazelogin'){
						self.addMessage(data['message'], 'error');
					}else if(data['action'] == 'follow'){
						jQuery(this).text(data['text']);
						jQuery(this).attr('title', data['title']);
						jQuery(this).removeClass('ap-icon-plus');
						jQuery(this).addClass('ap-unfollow ap-icon-minus');
						jQuery('[data-id="'+data['id']+'"] [data-view="ap-followers"]').text(data['followers_count']);
						self.addMessage(data['message'], 'success');
					}else if(data['action'] == 'unfollow'){
						jQuery(this).text(data['text']);
						jQuery(this).attr('title', data['title']);
						jQuery(this).removeClass('ap-unfollow ap-icon-minus');
						jQuery(this).addClass('ap-icon-plus');
						jQuery('[data-id="'+data['id']+'"] [data-view="ap-followers"]').text(data['followers_count']);
						self.addMessage(data['message'], 'success');
					}else{
						self.addMessage(data['message'], 'error');
					}
				},  
				error: function(MLHttpRequest, textStatus, errorThrown){  
					alert(errorThrown);  
				}  
			});  
		});
	},
	
	dropdown:function(){
		jQuery('.anspress').delegate('.ap-dropdown-toggle', 'click', function(e){
			e.preventDefault();
			jQuery(this).closest('.ap-dropdown').toggleClass('open');
		});
		
		jQuery('.anspress').delegate('.ap-dropdown-menu a', 'click', function(e){
			jQuery(this).closest('.ap-dropdown').removeClass('open');
		});
	},
	dropdownClose:function(){
		jQuery('[data-toggle="ap-dropdown"]').click(function(e){
			e.preventDefault();
			jQuery(this).closest('.ap-dropdown').removeClass('open');
		});
	},
	
	load_comment_form: function(){
		var self = this;
		jQuery('.anspress').delegate('[data-action="ap-load-comment"]', 'click', function(e){
			e.preventDefault();
			var args 	= jQuery(this).data('args'),
				elem	= jQuery(this).attr('href');
			
			if(jQuery(elem).length === 0)
				jQuery(this).closest('ul').after('<div id="'+elem.replace('#', '')+'"></div>');
				
			if(jQuery(this).is('.ajax-done')){
				jQuery(elem+' #respond').slideToggle();
			}else{
				self.showLoading(aplang.loading_comment_form);
				jQuery.ajax({  
					type: 'POST',  
					url: ajaxurl,  
					data: {  
						action: 'ap_load_comment_form',  
						args: args,
					},  
					context:this,
					success: function(data){
						self.hideLoading();
						
						if(jQuery('.comment-form-c').length > 0)
							jQuery('.comment-form-c').remove();
						
						jQuery(elem).append(data);
						jQuery(this).addClass('ajax-done');
						jQuery('html, body').animate({
							scrollTop: (jQuery('#respond').offset().top) - 50
						}, 500);

					},  
					error: function(MLHttpRequest, textStatus, errorThrown){  
						alert(errorThrown);  
					}  
				});  			
			}
		});
	}, 
	
	load_edit_comment_form: function(){
		var self = this;
		jQuery('body').delegate('[data-button="ap-edit-comment"]', 'click', function(e){
			e.preventDefault();
			var args 	= jQuery(this).data('args');
			self.showLoading(aplang.loading_comment_form);
			jQuery.ajax({  
				url: ajaxurl,  
				data: {  
					action: 'ap_edit_comment_form',  
					args: args,
				},  
				context:this,
				success: function(data, textStatus, XMLHttpRequest){
					self.hideLoading();
					if(jQuery(data).selector == 'not_logged_in'){
						ap_not_logged_in();
					}else{
						jQuery(this).closest('.comment-content').html(data);
					}
				}
			});  			
			
		});
	},
	updateView: function(data, text){
		jQuery('[data-view="'+data+'"]').text(text);
	},
	
	submitAnswer: function(){
		var self = this;
		jQuery('.anspress').delegate('[data-action="ap-submit-answer"]', 'submit', function(e){
			var form = jQuery(this);
			var btn = jQuery(this).find('.btn-submit-ans');
			/* Disable the button while submitting */
			btn.attr('disabled', 'disabled').addClass('disabled');
			
			var fields = jQuery(this).serialize();
			self.showLoading(aplang.submitting_your_answer);
			
			jQuery.post(ajaxurl, fields+'&action=ap_submit_answer', function(responce){
				btn.removeAttr('disabled').removeClass('disabled');				
				self.hideLoading();
				
				if(responce['action'] == 'new_answer'){
					//rest the from
					form[0].reset();
					if(!responce['can_answer'])
						jQuery('#answer-form-c').hide();
						
					/* Update answer count */
					
					jQuery('[data-view="ap-answer-count"]').text(responce['count']);
					jQuery('[data-view="ap-answer-count-label"]').text(responce['count_label']);
					
					self.addMessage(responce['message'], 'success');
					
					if(jQuery('#answers').length === 0){
						jQuery('#question').after(jQuery(responce['html']));
						jQuery(responce['div_id']).hide();
					}else
						jQuery('#answers').append(jQuery(responce['html']).hide());				
					
					jQuery(responce['div_id']).slideDown(500);
					
					if(typeof responce['redirect_to'] !== 'undefined')
						window.location.replace(responce['redirect_to']);

				}else if(responce['action'] == 'answer_edited'){
					self.addMessage(responce['message'], 'success');
					
					if(typeof responce['redirect_to'] !== 'undefined')
						window.location.replace(responce['redirect_to']);
				
				}else if(responce['action'] == 'validation_falied'){
					self.addMessage(responce['message'], 'error');
					self.appendFormError('#answer_form', responce['error']);
				
				}else{
					self.addMessage(responce['message'], 'error');
				}
			}, 'json');			
			
			
			return false
		});
	},
	
	submitQuestion: function(){
		var self = this;
		jQuery('.anspress').delegate('[data-action="ap-submit-question"]', 'submit', function(e){
			var form = jQuery(this);
			var btn = jQuery(this).find('.btn-submit-ask');
			/* Disable the button while submitting */
			btn.attr('disabled', 'disabled').addClass('disabled');
			
			var fields = jQuery(this).serialize();
			self.showLoading(aplang.submitting_your_question);
			
			jQuery.post(ajaxurl, fields+'&action=ap_submit_question', function(responce){
				btn.removeAttr('disabled').removeClass('disabled');				
				self.hideLoading();
					
				if(responce['action'] == 'new_question' || responce['action'] == 'edited_question'){
					form[0].reset();
					self.addMessage(responce['message'], 'success');
					window.location.replace(responce['redirect_to']);
					
				}else if(responce['action'] == 'validation_falied'){
					self.clearError('#ask_question_form');
					self.addMessage(responce['message'], 'error');
					self.appendFormError('#ask_question_form', responce['error']);
					/* if there is an error we reload the captcha regardless, as it won't be valid a second time */
					Recaptcha.reload();
				}else{
					self.addMessage(responce['message'], 'error');
				}
				jQuery(this).trigger('submitQuestion', responce);
			}, 'json');			
			
			
			return false
		});
	},
	
	appendFormError: function(form, error){	
		var self = this;
		if(typeof error !== 'undefined'){
			jQuery.each(error, function(i, message) {
				var parent = jQuery(form).find('#'+i).closest('.form-group');
				parent.addClass('has-error');
				self.helpBlock(parent, message);
			});
		}
	},
	
	clearError: function(form){
		jQuery(form).find('.has-error').removeClass('has-error');
		jQuery(form).find('.help-block').remove();
	},
	
	toggleLoginSignup: function(){
		jQuery('.anspress').delegate('[data-toggle="ap-signup-form"]', 'click', function(e){
			e.preventDefault();
			
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,  
				data: {  
					action: 'ap_toggle_login_signup',  
					args: jQuery(this).data('args'),
				},  
				context:this,
				success: function(data){ 
					jQuery('#ap-login-signup').empty().append(data);
				}
			}); 
			
		});
	},
	saveComment: function(){
		var self = this;
		jQuery('.anspress').delegate('#commentform', 'submit', function(){
			var form = jQuery(this);
			var fields = jQuery(this).serialize();
			self.showLoading(aplang.submitting_your_comment);
			
			jQuery.post(jQuery(this).attr('action'), fields, function(responce){
				self.hideLoading();
				form[0].reset();
				if(responce['status'] && jQuery('#li-comment-'+responce['comment_ID']).length ==0){
					jQuery('#comments-'+responce['comment_post_ID']+' ul.commentlist').append(jQuery(responce['html']).hide().slideDown(300));
					
					self.addMessage(responce['message'], 'success');
				}
				
			}, 'json'); 
			
			return false
		});
	},
	deleteComment: function (){
		var self = this;
		jQuery('.anspress').delegate('[data-button="ap-delete-comment"]', 'click', function(e){
			e.preventDefault();
			if(confirm(jQuery(this).data('confirm'))){
				var args 	= jQuery(this).data('args');
				self.showLoading(aplang.deleting_comment);
				jQuery.ajax({  
					url: ajaxurl,  
					data: {  
						action: 'ap_delete_comment',  
						args: args,
					},  
					context:this,
					dataType:'json',
					success: function(data){
						self.hideLoading();
						
						if(data['status']){
							jQuery(this).closest('li.comment').remove();
							self.addMessage(data['message'], 'success');
						}else{
							self.addMessage(data['message'], 'error');
						}
					} 
				});  
			}		
		});
	},
	updateComment: function(){
		var self = this;
		jQuery('.anspress').delegate('[data-action="ap-edit-comment"]', 'submit', function(e) {
			e.preventDefault();
			self.showLoading(aplang.updating_comment);
			jQuery.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {  
					action: 'ap_update_comment',  
					args: jQuery(this).serialize(),
				},
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['status']){
						var item = jQuery(this).closest('li');
						jQuery(item).after(data['html']);
						item.remove();
						self.addMessage(data['message'], 'success');
					}else{
						self.addMessage(data['message'], 'error');
					}
				}
			});
			return false;
		});
	},
	selectBestAnswer: function(){
		var self = this;
		jQuery('.anspress').delegate('[data-button="ap-select-answer"]', 'click', function(e) {
			e.preventDefault();
			self.showLoading();
			jQuery.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {  
					action: 'ap_set_best_answer',  
					args: jQuery(this).data('args'),
				},
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['action'] == 'selected'){
						jQuery('[data-button="ap-select-answer"]').not(this).hide();
						jQuery(this).after(data['html']);
						jQuery(this).closest('.answer').addClass('selected');						
						jQuery(this).remove();
						
						self.addMessage(data['message'], 'success');
					}else if(data['action'] == 'unselected'){
						jQuery(this).after(data['html']);
						jQuery(this).closest('.answer').removeClass('selected');
						jQuery(this).remove();
						self.addMessage(data['message'], 'success');
					}else{
						self.addMessage(data['message'], 'error');
					}
				}
			});
		});
	},
	
	deletePost: function(){
		var self = this;
		jQuery('.anspress').delegate('[data-button="ap-delete-post"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.deleting_post);
			jQuery.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {  
					action: 'ap_delete_post',  
					args: jQuery(this).data('args'),
				},
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['action'] == 'question'){
						self.addMessage(data['message'], 'success');
						window.location.replace(data['redirect_to']);						
					}else if(data['action'] == 'answer'){
						self.addMessage(data['message'], 'success');
						self.updateView('ap-answer-count-label', data['count_label']);
						self.updateView('ap-answer-count', data['count']);
						
						if(!data['remove'])
							jQuery(data['div']).slideUp(200);
						else
							jQuery('#answers-c').remove();
							
					}else{
						self.addMessage(data['message'], 'error');
					}
				}
			});
		});
	},
	loadEditForm:function(){
		var self = this;
		jQuery('.anspress').delegate('[data-button="ap-edit-post"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.loading_form);
			
			jQuery.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: jQuery(this).data('args'),
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['action']){
						self.addMessage(data['message'], 'success');
						jQuery('#ap-single').html(data['html']);										
					}else{
						self.addMessage(data['message'], 'error');
					}
				}
			});
		});
	},
	tab:function(){
		jQuery('[data-action="ap-tab"] a').click(function(e){
			e.preventDefault();
			var current = jQuery(this).closest('ul').find('.active a').attr('href');
			var div = jQuery(this).attr('href');

			jQuery(current).removeClass('active');
			
			jQuery(this).closest('ul').find('li').removeClass('active');
			jQuery(this).parent().addClass('active');			
			
			jQuery(div).addClass('active');
		});
	},
	labelSelect:function(){
		jQuery('.anspress').delegate('[data-action="ap-label-select"] li:not(.ap-select-footer)', 'click', function(e){
			e.preventDefault();
			jQuery(this).toggleClass('selected');
		});
	},
	saveLabel:function(){
		var self = this;
		jQuery('.anspress').delegate('[data-button="ap-save-label"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.saving_labels);	
			var args = [];
			jQuery(this).closest('ul').find('li.selected').each(function(i){
				args[i] = jQuery(this).data('args');
			});
			jQuery.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {
					action:'ap_save_labels',
					args:args,
					id:jQuery(this).data('id'),
					nonce:jQuery(this).data('nonce')
				},
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['status']){
						self.addMessage(data['message'], 'success');
						jQuery(this).closest('.ap-dropdown').removeClass('open');
						jQuery('[data-view="ap-labels-list"]').html(jQuery(data['html']));
						jQuery('[data-view="ap-labels-list"]').find('li').hide()
						var i = 1;
						jQuery('[data-view="ap-labels-list"]').find('li').each(function(){
							jQuery(this).delay(i*100).slideDown(300);
							i++;
						});
					}else{
						self.addMessage(data['message'], 'error');
					}
				}
			});
		});
	},
	tagsScript:function(){
		
		if(jQuery('[data-role="ap-tagsinput"]').length > 0){
			jQuery('[data-role="ap-tagsinput"]').tagsinput({
				freeInput: false,
				maxTags: ap_max_tags,
			});
			jQuery('[data-role="ap-tagsinput"]').tagsinput('input').blur(function(e){
				jQuery(document).mouseup(function (e){
					var container = jQuery('#ap-suggestions');

					if (!container.is(e.target)	&& container.has(e.target).length === 0){
						container.hide();
					}
				});
			});

		}
	},
	tagsSuggestion: function(){
		this.tagsquery;
		var self = this;
		
		jQuery('.anspress').delegate('#ask_question_form .bootstrap-tagsinput input', 'keyup', function(){
			var value = jQuery(this).val();
			
			if(value.length == 0)
				return;
				
			/* abort previous ajax request */
			if(typeof self.tagsquery !== 'undefined'){
				self.tagsquery.abort();
			}
			
			self.showLoading(aplang.loading_suggestions);	
			self.tagsquery = jQuery.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {
					action:'ap_suggest_tags',
					q: value
				},
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					var container = jQuery(this).closest('.bootstrap-tagsinput'),
						position = container.offset();
					
					if(jQuery('#ap-suggestions').length ==0)
						jQuery('.anspress').append('<div id="ap-suggestions" class="ap-suggestions" style="display:none"></div>');
					
					if(data['items']){
						self.tagItems(data['items']);
						
						jQuery('#ap-suggestions').html(self.tagsitems+data['form']).css({'top': (position.top + container.height() + 20), 'left': position.left, 'width': container.width()}).show();
					}else if(data['form']){
						jQuery('#ap-suggestions').html(data['form']).css({'top': (position.top + container.height() + 20), 'left': position.left, 'width': container.width()}).show();
					}
					
				}
			});
		});
	},
	tagItems: function(items){
		this.tagsitems = '';
		var self = this;
		jQuery.each(items, function(i){
			self.tagsitems += '<div class="ap-tag-item" data-action="ap-add-tag" data-name="'+ this.name +'"><div class="ap-tag-item-inner">';
			self.tagsitems += '<div class="tag-title"><strong>'+ this.name +'</strong> &times; <span>'+this.count+'</span></div>';			
			self.tagsitems += '<span class="tag-description">'+ this.description +'</strong>';			
			self.tagsitems += '</div></div>';
		});
	},
	
	addTag: function(){
		var self = this;
		
		jQuery('.anspress').delegate('[data-action="ap-add-tag"]', 'click touchstart', function(){
			jQuery('[data-role="ap-tagsinput"]').tagsinput('add', jQuery(this).data('name'));
			jQuery('[data-role="ap-tagsinput"]').tagsinput('input').val('');
			jQuery('#ap-suggestions').hide();
		});
	},
	uploadForm:function(){
		var self = this;
		jQuery('[data-action="ap-upload-field"]').change(function(){
			jQuery(this).closest('form').submit();
		});
		
		jQuery('[data-action="ap-upload-form"]').submit(function(){
			jQuery(this).ajaxSubmit({
				beforeSubmit:  function(){
					self.showLoading(aplang.uploading);
				},
				success: function(data){
					self.hideLoading();
					jQuery('body').trigger('uploadForm', data);
					if(data['status']){
						self.addMessage(data['message'], 'success');					
					}else{
						self.addMessage(data['message'], 'error');
					}
				},
				url:ajaxurl,
				dataType:'json'
			});
			
			return false
		});
		
		jQuery('body').on('uploadForm', function(e, data){
			if(data['view'] == '[data-view="cover"]')
				jQuery(data['view']).attr('style', data['background-image']);
			else if(data['view'] == '[data-view="avatar-main"]')
				jQuery(data['view']).html(data['image']);
			
		});
	},
	
	saveProfile: function(){
		var self = this;
		jQuery('[data-action="ap-edit-profile"]').submit(function(){
			jQuery(this).ajaxSubmit({
				beforeSubmit:  function(){
					self.showLoading(aplang.saving_profile);
				},
				success: function(data){
					self.hideLoading();
					if(data['status']){
						self.addMessage(data['message'], 'success');
					}else if(responce['action'] == 'validation_falied'){
						self.clearError('#ask_question_form');
						self.addMessage(responce['message'], 'error');
						self.appendFormError('#ask_question_form', responce['error']);				
					}else{
						self.addMessage(data['message'], 'error');
					}
				},
				url:ajaxurl,
				dataType:'json'
			});
			return false;
		});
	},
	sendMessage:function(){
		var self = this;
		jQuery('.anspress').delegate('[data-action="ap-send-message"]', 'submit', function(){
			jQuery(this).ajaxSubmit({
				beforeSubmit:  function(){
					self.showLoading(aplang.sending_message);
				},
				success: function(data){
					self.hideLoading();
					if(data['status'] == true){
						self.addMessage(data['message'], 'success');
						var container = jQuery('[data-view="conversation"] > ul');
						
						if(container.length == 0){
							jQuery('[data-view="conversation"]').html(jQuery(data['html']).hide());
							jQuery('[data-view="conversation"] > ul').slideDown(200);
							jQuery('[data-view="conversation"] > form').slideDown(200);
						}else{	
							container.append(jQuery(data['html']).hide());
							container.find('li:last-child').slideDown(200);
						}
						jQuery('textarea.autogrow').autogrow({onInitialize: true});						
					}else if(data['status'] == 'validation_falied'){
						self.clearError(this);
						self.addMessage(data['message'], 'error');
						self.appendFormError(this, data['error']);				
					}else{
						self.addMessage(data['message'], 'error');
					}
				},
				url:ajaxurl,
				dataType:'json',
				clearForm:true,
				context:this
			});
			return false;
		});
	},
	showConversation:function(){
		var self = this;
		
		jQuery('.anspress').delegate('[data-action="ap-show-conversation"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.loading_conversation);
			jQuery(this).parent().find('li.active').removeClass('active');
			jQuery(this).addClass('active');
			jQuery.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {
					action:'ap_show_conversation',
					args: jQuery(this).data('args')
				},
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['status']){
						jQuery('[data-view="conversation"]').html(data['html']);
						self.addMessage(data['message'], 'success');
					}
					else{
						self.addMessage(data['message'], 'error');
					}
					
				}
			});
		});
		if(!ap_url_string_value('to'))
			jQuery('[data-action="ap-show-conversation"].active').click();
	},
	newMessageButton: function(){
		var self = this;
		jQuery('.anspress').delegate('[data-button="ap-new-message"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.loading_new_message_form);
			jQuery.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {
					action:'ap_new_message_form'
				},
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['status']){
						jQuery('[data-view="conversation"]').html(data['html']);
						jQuery('textarea.autogrow').autogrow({onInitialize: true});
						jQuery('[data-action="ap-suggest-user"]').tagsinput({
							freeInput: false,
							itemValue: 'value',
							itemText: 'text',
						});
						//if(window.location.search.match("to?=").length > 0)
							//jQuery('[data-action="ap-suggest-user"]').tagsinput('add', { "value": ap_url_string_value('to'), "text": ap_url_string_value('dname')});
							
						jQuery('[data-action="ap-suggest-user"]').tagsinput('input').blur(function(e){
							jQuery(document).mouseup(function (e){
								var container = jQuery('#ap-suggestions');

								if (!container.is(e.target)	&& container.has(e.target).length === 0){
									container.hide();
								}
							});
						});
					}
					
				}
			});
		});
	
		if(ap_url_string_value('to'))
			jQuery('[data-button="ap-new-message"]').click();
	},
	userSuggestion: function(){
		this.usersquery;
		var self = this;
		
		jQuery('.anspress').delegate('[data-action="ap-add-user"]', 'click', function(){
			jQuery('[data-action="ap-suggest-user"]').tagsinput('add', { "value": jQuery(this).data('id'), "text": jQuery(this).data('name')});
			jQuery('[data-action="ap-suggest-user"]').tagsinput('input').val('');
			jQuery('#ap-suggestions').hide();
		});
		
		jQuery('.anspress').delegate('#ap-new-message .bootstrap-tagsinput input', 'keyup', function(){
			var value = jQuery(this).val();
			
			if(value.length == 0)
				return;
				
			/* abort previous ajax request */
			if(typeof self.usersquery !== 'undefined'){
				self.usersquery.abort();
			}
			
			self.showLoading(aplang.loading_suggestions);
			
			self.usersquery = self.doAjaxForm(
				{action:'ap_search_users', q: value},
				function(data){
					self.hideLoading();
					var container = jQuery(this).closest('.bootstrap-tagsinput'),
						position = container.offset();
					
					if(jQuery('#ap-suggestions').length ==0)
						jQuery('.anspress').append('<div id="ap-suggestions" class="ap-suggestions user-suggestions" style="display:none"></div>');
					
					if(data['items']){
						self.userItems(data['items']);
						
						jQuery('#ap-suggestions').html(self.useritems).css({'top': (position.top + container.height() + 20), 'left': position.left}).show();
					}					
				},
				this
			);
		});

	},
	userItems: function(items){
		this.useritems = '';
		var self = this;
		jQuery.each(items, function(i){
			self.useritems += '<div class="ap-suggestion-user" data-action="ap-add-user" data-name="'+ this.name +'" data-id="'+ this.id +'"><div class="ap-user-item-inner clearfix">';
			self.useritems += '<div class="suggestion-avatar">'+ this.avatar +'</div>';			
			self.useritems += '<span class="name">'+ this.name +'</strong>';			
			self.useritems += '</div></div>';
		});
	},
	loadMoreConversations:function(elem){
		var self = this;
		self.showLoading(aplang.loading_more_conversations);
		var offset = jQuery(elem).attr('data-offset');
		
		if(typeof offset !== 'undefined')
		self.doAjaxForm(
			{action:'ap_load_conversations', offset: offset, args: jQuery(elem).attr('data-args')}, 
			function(data){
				self.hideLoading();				
				if(data['status']){
					self.addMessage(data['message'], 'success');
					jQuery(elem).attr('data-offset', parseInt(offset)+1);
					jQuery('#ap-conversation-scroll ul').append(jQuery(data['html']).html());
					jQuery('#ap-conversation-scroll').perfectScrollbar('update');
				}else{
					self.addMessage(data['message'], 'error');
				}
			}
		);
	},
	searchMessage: function(){
		var self = this;
		
		jQuery('.anspress').delegate('[data-action="ap-search-conversations"]', 'keyup', function(){
			var value = jQuery(this).val();
			
			if(value.length == 0)
				return;
				
			/* abort previous ajax request */
			if(typeof self.messageSearchXhr !== 'undefined'){
				self.messageSearchXhr.abort();
			}
			
			jQuery(this).closest('form').submit();
		});
		
		jQuery('[data-role="ap-search-conversations"]').submit(function(){
			self.messageSearch = jQuery(this).ajaxSubmit({
				beforeSubmit:  function(){
					self.showLoading(aplang.searching_conversations);
				},
				success: function(data){
					self.hideLoading();
					if(data['status']){
						jQuery(this).removeAttr('data-offset');
						jQuery('#ap-conversation-scroll ul').html(jQuery(data['html']).html());
						jQuery('#ap-conversation-scroll').perfectScrollbar('update');
					}else{
						self.addMessage(data['message'], 'error');
					}
				},
				url:ajaxurl,
				dataType:'json',
				type: 'GET'
			});
			self.messageSearchXhr = self.messageSearch.data('jqxhr');
			return false;
		});
	},
	editMessage: function(){
		var self = this;
		jQuery('.anspress').delegate('[data-button="ap-edit-message"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.loading_message_edit_form);
			self.doAjaxForm(
				{action:'ap_message_edit_form', args: jQuery(this).data('args')}, 
				function(data){
					self.hideLoading();				
					if(data['status']){
						self.addMessage(data['message'], 'success');
						jQuery(this).closest('.ap-message').find('[data-view="ap-message-content"]').html(data['html']);
						jQuery('textarea.autogrow').autogrow({onInitialize: true});
					}else{
						self.addMessage(data['message'], 'error');
					}
				},
				this
			);
		});
		
		jQuery('.anspress').delegate('[data-action="ap-edit-message"]', 'submit', function(){
			jQuery(this).ajaxSubmit({
				beforeSubmit:  function(){
					self.showLoading(aplang.updating_message);
				},
				success: function(data){
					self.hideLoading();
					if(data['status']){
						self.addMessage(data['message'], 'success');
						jQuery(this).after(data['html']);
						jQuery(this).remove();
					}else{
						self.addMessage(data['message'], 'error');
					}
				},
				url:ajaxurl,
				dataType:'json',
				type: 'POST',
				context:this
			});

			return false;
		});
	},
	deleteMessage:function(){
		var self = this;
		jQuery('.anspress').delegate('[data-button="ap-delete-message"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.deleting_message);
			self.doAjaxForm(
				{action:'ap_delete_message', args: jQuery(this).data('args')}, 
				function(data){
					self.hideLoading();				
					if(data['status']){
						self.addMessage(data['message'], 'success');
						jQuery(this).closest('.ap-message').remove();
					}else{
						self.addMessage(data['message'], 'error');
					}
				},
				this
			);
		});
	},
	questionSuggestion: function(){
		this.qquery;
		var self = this;
		
		jQuery('.anspress').delegate('#ap-quick-ask-input', 'keyup', function(){
			var value = jQuery(this).val();
			
			if(value.length == 0)
				return;
				
			/* abort previous ajax request */
			if(typeof self.qquery !== 'undefined'){
				self.qquery.abort();
			}
			
			self.showLoading(aplang.loading_suggestions);	
			self.qquery = self.doAjaxForm(
				{
					action:'ap_suggest_questions',
					q: value
				},
				function(data){
					self.hideLoading();
					var container = jQuery(this).closest('.ap-qaf-inner'),
						position = container.offset();
					
					if(jQuery('#ap-qsuggestions').length ==0)
						jQuery('.anspress').append('<div id="ap-qsuggestions" class="ap-qsuggestions" style="display:none"></div>');
					
					if(data['items']){
						self.qsItems(data['items']);
						
						jQuery('#ap-qsuggestions').html(self.qsitems).css({'top': (position.top + container.height() + 10), 'left': position.left, 'width': container.width()}).show();
					}
					
				},
				this
			);
		});
		
		jQuery('.anspress').delegate('#ap-qsuggestions', 'click', function(e){
			jQuery('#ap-qsuggestions').toggle();
		});

	},
	qsItems: function(items){
		this.qsitems = '';
		var self = this;
		jQuery.each(items, function(i){
			self.qsitems += this.html;
		});
	},
	flushRules:function(){
		var self = this;
		
		jQuery('.ap-missing-rules > a').click(function(e){
			e.preventDefault();
			self.showLoading(aplang.sending);
			self.doAjaxForm(
				{action: 'ap_install_rewrite_rules', args: jQuery(this).data('args')},
				function(){
					self.hideLoading();
					jQuery('.ap-missing-rules').hide();
				}
			);
		});

	},
	submitAjaxForm: function(form, before, after){
		jQuery('.anspress').delegate(form, 'submit', function(){
			jQuery(this).ajaxSubmit({
				beforeSubmit:  	before,
				success: 		after,
				url:			ajaxurl,
				dataType:		'json'
			});
			
			return false;
		});
	},
	
	
	loadNewTagForm:function(){
		var self = this;
		jQuery('.anspress').delegate('#ap-load-new-tag-form', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.loading);
			self.doAjaxForm(
				{action: 'ap_load_new_tag_form', args: jQuery(this).data('args')},
				function(data){
					self.hideLoading();
					if(data['status']){
						jQuery(this).closest('.ap-suggestions').html(data['html']);
					}else{
						self.addMessage(data['message'], 'error');
					}
				}, 
				this
			);
		});
	},
	newTag:function(){
		var self = this;
		self.submitAjaxForm(
			'#ap_new_tag_form',
			function(){
				self.showLoading(aplang.sending);
			},
			function(data){
				self.hideLoading();
				if(data['status']){
					self.addMessage(data['message'], 'success');
					jQuery('[data-role="ap-tagsinput"]').tagsinput('add', data['tag']['slug']);
					jQuery('[data-role="ap-tagsinput"]').tagsinput('input').val('');
					jQuery('#ap-suggestions').hide();
				}else{
					self.addMessage(data['message'], 'error');
				}
			}
		);
	},
	loginAccor:function(){
		var self = this;
		jQuery('.ap-ac-accordion > strong').click(function(){
			var $elm = jQuery(this).parent();
			if(!$elm.hasClass('active'))
			{
				jQuery('.ap-ac-accordion').removeClass('active');
				jQuery('.ap-ac-accordion .accordion-content').hide();
				jQuery(this).parent().addClass('active');
				jQuery(this).next().slideToggle();
			}
			else
			{
				$elm.removeClass('active');
				$elm.find('.accordion-content').hide();
			}
		});
	}
	
};


function ap_label_select_template(state) {
	var color = jQuery(state.element).data('color');
	if (!color) color = '#ddd'; // optgroup
	return "<span class='question-label-color' style='background:" + color + "'></span>" + state.text;
}
		
jQuery(document).ready(function (){  
	
	jQuery(document).mouseup(function (e)
	{
		var container = jQuery('#ap-qsuggestions');

		if (!container.is(e.target) // if the target of the click isn't the container...
			&& container.has(e.target).length === 0) // ... nor a descendant of the container
		{
			container.hide();
		}
	});
	
	if(typeof QTags !== 'undefined')
		QTags.addButton( 'ap_code', 'code block','<pre>', '</pre>', 'q' );
	
}); 
function ap_url_string_value(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results == null ? false : decodeURIComponent(results[1].replace(/\+/g, " "));
}
