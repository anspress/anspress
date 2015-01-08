(function($){
/* on start */
$(function() {
     
    /* create document */
    APjs.site = new APjs.site();
    /* need to call init manually with $ */
    //APjs.site.initialize();
 
});
 
/* namespace */
window.APjs = {};
APjs.site = function() {};
 

APjs.site.prototype = {
	
	/* automatically called */
	initialize: function() {

		if($('.ap-signup-fields #password').length > 0){		
			$('.ap-signup-fields #password1').on('keyup', $.proxy(this.checkPasswordMatch, this, '.ap-signup-fields #password', '.ap-signup-fields #password1'));
			$('.ap-signup-fields #password').on('keyup', $.proxy(this.checkPasswordLength, this, '#password'));
		}
		
		/*this.onWidnowReady();
		this.afterAjaxComplete();
		this.checkEmail();
		this.checkEmailAvailable();
		//this.bind();
		
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
		
		this.expandToggle();

		this.removeHasError();*/
		
		

		$('body').delegate('.ap-modal-bg, .ap-modal-close', 'click', function () {
			$('.ap-modal.active').toggleClass('active');
		});
		
		$('body').delegate('.ap-open-modal', 'click', function(e){
			e.preventDefault();
			var id = $(this).attr('href');
			/* ensure all other models are closed */
			$('.ap-open-modal').each(function() {
				var model_id = $(this).attr('href')
				$(model_id).removeClass('active');
			});

			/* activate our model */
			$(id).addClass('active');
		});
		
		
	},

	onWidnowReady:function(){
		var self = this;
		$(window).ready(function(){
			self.foldContent();
		})
		
	},

	/**
	 * Process to run after completing an ajax request
	 * @return {void}
	 * @since 2.0
	 */
	afterAjaxComplete:function(){
		var self = this;
		$( document ).ajaxComplete(function( event, data, settings ) {
			console.log(data);
			if(typeof data !== 'undefined' && typeof data.responseJSON !== 'undefined' && typeof data.responseJSON.message !== 'undefined'){
				var type = typeof data.responseJSON.message_type === 'undefined' ? 'success' : data.responseJSON.message_type;
				self.addMessage(data.responseJSON.message, type);
				$('document').trigger('ap_after_ajax', data);
			}
		});
	},

	inputVal:function(elm){
		return $(elm).val();
	},

	parseAjaxData: function(string, elm){
		var self = this;
		var data = apQueryStringToJSON(string);
		
		$.each(data, function(k, v){
			var new_v = '';

			switch(v) {
			    case '__INPUT_VAL__':
			        new_v = self.inputVal(elm);
			        break;
			}

			if(new_v !== '')
				data[k] = new_v;
		});

		data['action'] = 'ap_ajax';

		return data;	
	},

	html_content: function(id, html, elm){
		$('#'+id).html(html);
	},

	parseAjaxSuccess: function(data){
		var self = APjs.site;
		var success = apQueryStringToJSON($(this).data('success'));

		$.each(success, function(k, v){			
			switch(k) {
			    case 'html_content':
			        self.html_content(v, data['html'], this);
			        break;
			}

		});
	},

	/**
	 * do actions as defined in data-action
	 * @return {void}
	 * @since  2.0
	 */
	doAction: function(){
		var self = this;
		var ajax_q = new Object();

		$('[data-action]').each(function(i){
			var action = $(this).data('action');
			//self.[action]();
			/*var e = $(this).data('bind');
			
			var q = $(this).data('query');

			if(typeof q === 'undefined')
				return;

			$(this).on(e, function(){
				self.loading = self.showLoading(this);
				var data = self.parseAjaxData($(this).data('query'), this);
				
				if(typeof ajax_q[i] !== 'undefined'){
					ajax_q[i].abort();
				}

				 ajax_q[i] = self.doAjax(data, self.parseAjaxSuccess, this);
			});*/
		});
	},

	
	
	appendMessageBox: function(){
		if($('#ap-messagebox').length == '0')
			$('body').append('<div id="ap-messagebox"></div>');
	},
	
	showLoading: function(message){
		/* set the default message if no message passed */
		if(typeof message == 'undefined')message = aplang.loading;

		if($('#ap-ajax-loading').length > 0)
			$('#ap-ajax-loading').text(message);
		else
			$('#ap-messagebox').append('<div style="display:none" id="ap-ajax-loading">'+message+'</div>');
		
		$('#ap-ajax-loading').slideDown(200);
	},
	
	/* change the message of loading */
	addMessage: function(message, type){
		$('<div class="ap-message-item '+type+'">'+message+'</div>').appendTo('#ap-messagebox').delay(5000).slideUp(200);
	},
	
	hideLoading:function(){
		$('#ap-ajax-loading').delay(500).slideUp(200);
	},
	
	/* add bootstrap validation class */
	fieldValidationClass: function(field, vclass){
		$(field).addClass(vclass);
	},
	
	/* add help block below field */
	
	
	toggleFormSubmission:function(form, toggle){
		//$(form).submit(toggle);		
		if(toggle && $(form).find('.has-error').length == 0)
			$(form).find('button[type="submit"]').removeAttr('disabled');
		else
			$(form).find('button[type="submit"]').attr('disabled', 'disabled');
	},
	
	isEmail : function (email) {
	  var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	  return regex.test(email);
	},
	
 	/* check for matching password between to fields  */
	checkPasswordMatch : function (field1, field2) {
		var password = $(field1).val();
		var confirmPassword = $(field2).val();
		
		var parent = $(field2).closest('.form-group');
		if (password != confirmPassword){
			this.fieldValidationClass(parent, 'has-error');
			if(parent.find('.help-block').length == 0)
				this.helpBlock(parent, aplang.password_field_not_macthing);
			
			this.toggleFormSubmission($(field2).closest('form'), false);
		}
		else{
			parent.removeClass('has-error');
			this.fieldValidationClass(parent, 'has-feedback');
			if(parent.find('.help-block').length >0)
				parent.find('.help-block').remove();
			
			this.toggleFormSubmission($(field2).closest('form'), true);
		}
	},
	
	/* check for password length */
	checkPasswordLength:function(field){
		var password = $(field).val(),
			parent = $(field).closest('.form-group');
		
		if(password.length < 6){
			this.fieldValidationClass(parent, 'has-error');
			this.helpBlock(parent, aplang.password_length_less);
			this.toggleFormSubmission($(field).closest('form'), false);
		}else{
			parent.removeClass('has-error');
			this.fieldValidationClass(parent, 'has-feedback');
			parent.find('.help-block').remove();
			this.toggleFormSubmission($(field).closest('form'), true);
		}
	},
	
	/* check for valid email */
	checkEmail: function(field){
		var self = this;
		$('body').delegate('form #email', 'keyup blur', function(){
			var field = $(this);		
			var parent = $(field).closest('.form-group');

			if (!self.isEmail(field.val())){
				self.fieldValidationClass(parent, 'has-error');
				if(parent.find('.help-block').length == 0)
					self.helpBlock(parent, aplang.not_valid_email);
				
				self.toggleFormSubmission($(field).closest('form'), false);
			}
			else{
				parent.removeClass('has-error');
				self.fieldValidationClass(parent, 'has-feedback');
				if(parent.find('.help-block').length >0)
					parent.find('.help-block').remove();
				
				self.toggleFormSubmission($(field).closest('form'), true);
			}
		});
	},
	

	/* check if email is available */
	checkEmailAvailable: function(){
		var self = this;
		$('body').delegate('form #email', 'blur', function(){
			var field = $(this);		
			var parent = field .closest('.form-group');
			
			if(!$(parent).is('.has-error'))
			$.ajax({  
				type: 'POST',  
				url: ajaxurl,  
				data: {  
					action: 'ap_check_email',  
					email: $(field).val(),  
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
		
		return $.ajax({  
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
		$('body').delegate('[data-action="vote"] a', 'click', function(e){
			e.preventDefault();
			var args = $(this).data('args');
			self.showLoading(aplang.voting_on_post);
			$.ajax({  
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
							$(this).addClass('voted');
							if(result['type'] == 'up') $(this).parent().find('.vote-down').addClass('disable');
							if(result['type'] == 'down') $(this).parent().find('.vote-up').addClass('disable');
							
							$(this).trigger('voted', result);
						}
						else if(result['action'] == 'undo'){
							$(this).removeClass('voted');
							if(result['type'] == 'up') $(this).parent().find('.vote-down').removeClass('disable');
							if(result['type'] == 'down') $(this).parent().find('.vote-up').removeClass('disable');
							$(this).trigger('undo_vote', result);
						}
						$(this).parent().find('.net-vote-count').text(result['count']);
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
		$('body').delegate('[data-action="ap-favorite"]', 'click', function(e){
			e.preventDefault();

			/* show loading message */
			self.showLoading(aplang.adding_to_fav);
			
			var args = $(this).data('args');
			$.ajax({  
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
						if(data['action'] == 'added') $(this).addClass('added');
						else if(data['action'] == 'removed') $(this).removeClass('added');
						$(this).html(data['count']);
						$(this).attr('title',data['title']);
						$(this).parent().find('span').html(data['text']);
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
		$('body').delegate('[data-action="close-question"]', 'click', function(e){
			e.preventDefault();
			var args = $(this).data('args');
			
			self.showLoading(aplang.requesting_for_closing);
			
			$.ajax({  
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
							$(this).addClass('closed');
						else if(result['action'] == 'removed') 
							$(this).removeClass('closed');
						
						$(this).text(result['text']);
						$(this).attr('title', result['title']);
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
		$('.ap-modal').addClass('active');			
	},
	
	loadFlagModal:function (){
		var self = this;
		$('body').delegate('[data-action="flag-modal"]', 'click', function(e){
			e.preventDefault();
			if(!$(this).is('.loaded')){
				var args = $(this).data('args');
				self.showLoading();
				$.ajax({  
					type: 'POST',  
					url: ajaxurl,  
					data: {  
						action: 'ap_flag_note_modal',  
						args: args,  
					},  
					context:this,
					success: function(data){ 
						self.hideLoading();
						$(data).appendTo('body');
						self.modalShow($(this).attr('href'));
						$(this).addClass('loaded');
					},  
					error: function(MLHttpRequest, textStatus, errorThrown){  
						alert(errorThrown);  
					}  
				});
			}else{
				self.modalShow($(this).attr('href'));
			}

		});
		
		$('body').delegate(':radio[name="note_id"]', 'click', function () {
			$(':radio[name="note_id"]').closest('.note').removeClass('active');
			$(this).closest('.note').addClass('active');
		});		
		
	},
	
	flagModalSubmit: function(){
		var self = this;
		$('body').delegate('#submit-flag-question', 'click', function(e){
			e.preventDefault();		
			var args = $(this).data('args');
			var note_id = $(this).closest('.flag-note').find(':radio[name="note_id"]:checked').val();
			var other_note = $(this).closest('.flag-note').find('#other-note').val();
			self.showLoading(aplang.sending_request);
			$.ajax({  
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
					var id = $(this).closest('.flag-note').attr('id');
					var to_update = $('#flag_' + $(this).data('update'));
					
					$('#'+id).removeClass('active');
					//$('#'+id).remove();
					
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
		$('body').delegate('[data-action="ap-follow"]', 'click', function(e){
			e.preventDefault();		
			var args = $(this).data('args');			
			self.showLoading(aplang.sending_request);
			$.ajax({  
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
						$(this).text(data['text']);
						$(this).attr('title', data['title']);
						$(this).removeClass('ap-icon-plus');
						$(this).addClass('ap-unfollow ap-icon-minus');
						$('[data-id="'+data['id']+'"] [data-view="ap-followers"]').text(data['followers_count']);
						self.addMessage(data['message'], 'success');
					}else if(data['action'] == 'unfollow'){
						$(this).text(data['text']);
						$(this).attr('title', data['title']);
						$(this).removeClass('ap-unfollow ap-icon-minus');
						$(this).addClass('ap-icon-plus');
						$('[data-id="'+data['id']+'"] [data-view="ap-followers"]').text(data['followers_count']);
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
		$('body').delegate('.ap-dropdown-toggle', 'click', function(e){
			e.preventDefault();
			$(this).closest('.ap-dropdown').toggleClass('open');
		});
		
		$('body').delegate('.ap-dropdown-menu a', 'click', function(e){
			$(this).closest('.ap-dropdown').removeClass('open');
		});
	},
	dropdownClose:function(){
		$('[data-toggle="ap-dropdown"]').click(function(e){
			e.preventDefault();
			$(this).closest('.ap-dropdown').removeClass('open');
		});
	},
	
	load_comment_form: function(){
		var self = this;
		$('body').delegate('[data-action="ap-load-comment"]', 'click', function(e){
			e.preventDefault();
			var args 	= $(this).data('args'),
				elem	= $(this).attr('href');
			
			if($(elem).length === 0)
				$(this).closest('ul').after('<div id="'+elem.replace('#', '')+'"></div>');
				
			if($(this).is('.ajax-done')){
				$(elem+' #respond').slideToggle();
			}else{
				self.showLoading(aplang.loading_comment_form);
				$.ajax({  
					type: 'POST',  
					url: ajaxurl,  
					data: {  
						action: 'ap_load_comment_form',  
						args: args,
					},  
					context:this,
					success: function(data){
						self.hideLoading();
						
						if($('.comment-form-c').length > 0)
							$('.comment-form-c').remove();
						
						$(elem).append(data);
						$(this).addClass('ajax-done');
						$('html, body').animate({
							scrollTop: ($('#respond').offset().top) - 50
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
		$('body').delegate('[data-button="ap-edit-comment"]', 'click', function(e){
			e.preventDefault();
			var args 	= $(this).data('args');
			self.showLoading(aplang.loading_comment_form);
			$.ajax({  
				url: ajaxurl,  
				data: {  
					action: 'ap_edit_comment_form',  
					args: args,
				},  
				context:this,
				success: function(data, textStatus, XMLHttpRequest){
					self.hideLoading();
					if($(data).selector == 'not_logged_in'){
						ap_not_logged_in();
					}else{
						$(this).closest('.comment-content').html(data);
					}
				}
			});  			
			
		});
	},
	updateView: function(data, text){
		$('[data-view="'+data+'"]').text(text);
	},
	
	submitAnswer: function(){
		var self = this;
		$('body').delegate('[data-action="ap-submit-answer"]', 'submit', function(e){
			var form = $(this);
			var btn = $(this).find('.btn-submit-ans');
			/* Disable the button while submitting */
			btn.attr('disabled', 'disabled').addClass('disabled');
			
			var fields = $(this).serialize();
			self.showLoading(aplang.submitting_your_answer);
			
			$.post(ajaxurl, fields+'&action=ap_submit_answer', function(responce){
				btn.removeAttr('disabled').removeClass('disabled');				
				self.hideLoading();
				
				if(responce['action'] == 'new_answer'){
					//rest the from
					form[0].reset();
					if(!responce['can_answer'])
						$('#answer-form-c').hide();
						
					/* Update answer count */
					
					$('[data-view="ap-answer-count"]').text(responce['count']);
					$('[data-view="ap-answer-count-label"]').text(responce['count_label']);
					
					self.addMessage(responce['message'], 'success');
					
					if($('#answers').length === 0){
						$('#question').after($(responce['html']));
						$(responce['div_id']).hide();
					}else
						$('#answers').append($(responce['html']).hide());				
					
					$(responce['div_id']).slideDown(500);
					
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
		$('body').delegate('[data-action="ap-submit-question"]', 'submit', function(e){
			var form = $(this);
			var btn = $(this).find('.btn-submit-ask');
			/* Disable the button while submitting */
			btn.attr('disabled', 'disabled').addClass('disabled');
			
			var fields = $(this).serialize();
			self.showLoading(aplang.submitting_your_question);
			
			$.post(ajaxurl, fields+'&action=ap_submit_question', function(responce){
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
				$(this).trigger('submitQuestion', responce);
			}, 'json');			
			
			
			return false
		});
	},
	
	
	
	clearError: function(form){
		$(form).find('.has-error').removeClass('has-error');
		$(form).find('.help-block').remove();
	},
	

	deleteComment: function (){
		var self = this;
		$('body').delegate('[data-button="ap-delete-comment"]', 'click', function(e){
			e.preventDefault();
			if(confirm($(this).data('confirm'))){
				var args 	= $(this).data('args');
				self.showLoading(aplang.deleting_comment);
				$.ajax({  
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
							$(this).closest('li.comment').remove();
							self.addMessage(data['message'], 'success');
						}else{
							self.addMessage(data['message'], 'error');
						}
					} 
				});  
			}		
		});
	},

	selectBestAnswer: function(){
		var self = this;
		$('body').delegate('[data-button="ap-select-answer"]', 'click', function(e) {
			e.preventDefault();
			self.showLoading();
			$.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {  
					action: 'ap_set_best_answer',  
					args: $(this).data('args'),
				},
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['action'] == 'selected'){
						$('[data-button="ap-select-answer"]').not(this).hide();
						$(this).after(data['html']);
						$(this).closest('.answer').addClass('selected');						
						$(this).remove();
						
						self.addMessage(data['message'], 'success');
					}else if(data['action'] == 'unselected'){
						$(this).after(data['html']);
						$(this).closest('.answer').removeClass('selected');
						$(this).remove();
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
		$('body').delegate('[data-button="ap-delete-post"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.deleting_post);
			$.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {  
					action: 'ap_delete_post',  
					args: $(this).data('args'),
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
							$(data['div']).slideUp(200);
						else
							$('#answers-c').remove();
							
					}else{
						self.addMessage(data['message'], 'error');
					}
				}
			});
		});
	},
	loadEditForm:function(){
		var self = this;
		$('body').delegate('[data-button="ap-edit-post"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.loading_form);
			
			$.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: $(this).data('args'),
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['action']){
						self.addMessage(data['message'], 'success');
						$('#ap-single').html(data['html']);										
					}else{
						self.addMessage(data['message'], 'error');
					}
				}
			});
		});
	},
	tab:function(){
		$('[data-action="ap-tab"] a').click(function(e){
			e.preventDefault();
			var current = $(this).closest('ul').find('.active a').attr('href');
			var div = $(this).attr('href');

			$(current).removeClass('active');
			
			$(this).closest('ul').find('li').removeClass('active');
			$(this).parent().addClass('active');			
			
			$(div).addClass('active');
		});
	},
	labelSelect:function(){
		$('body').delegate('[data-action="ap-label-select"] li:not(.ap-select-footer)', 'click', function(e){
			e.preventDefault();
			$(this).toggleClass('selected');
		});
	},
	saveLabel:function(){
		var self = this;
		$('body').delegate('[data-button="ap-save-label"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.saving_labels);	
			var args = [];
			$(this).closest('ul').find('li.selected').each(function(i){
				args[i] = $(this).data('args');
			});
			$.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {
					action:'ap_save_labels',
					args:args,
					id:$(this).data('id'),
					nonce:$(this).data('nonce')
				},
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['status']){
						self.addMessage(data['message'], 'success');
						$(this).closest('.ap-dropdown').removeClass('open');
						$('[data-view="ap-labels-list"]').html($(data['html']));
						$('[data-view="ap-labels-list"]').find('li').hide()
						var i = 1;
						$('[data-view="ap-labels-list"]').find('li').each(function(){
							$(this).delay(i*100).slideDown(300);
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
		
		if($('[data-role="ap-tagsinput"]').length > 0){
			$('[data-role="ap-tagsinput"]').tagsinput({
				freeInput: false,
				maxTags: ap_max_tags,
			});
			$('[data-role="ap-tagsinput"]').tagsinput('input').blur(function(e){
				$(document).mouseup(function (e){
					var container = $('#ap-suggestions');

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
		
		$('body').delegate('#ask_question_form .bootstrap-tagsinput input', 'keyup', function(){
			var value = $(this).val();
			
			if(value.length == 0)
				return;
				
			/* abort previous ajax request */
			if(typeof self.tagsquery !== 'undefined'){
				self.tagsquery.abort();
			}
			
			self.showLoading(aplang.loading_suggestions);	
			self.tagsquery = $.ajax({
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
					var container = $(this).closest('.bootstrap-tagsinput'),
						position = container.offset();
					
					if($('#ap-suggestions').length ==0)
						$('body').append('<div id="ap-suggestions" class="ap-suggestions" style="display:none"></div>');
					
					if(data['items']){
						self.tagItems(data['items']);
						
						$('#ap-suggestions').html(self.tagsitems+data['form']).css({'top': (position.top + container.height() + 20), 'left': position.left, 'width': container.width()}).show();
					}else if(data['form']){
						$('#ap-suggestions').html(data['form']).css({'top': (position.top + container.height() + 20), 'left': position.left, 'width': container.width()}).show();
					}
					
				}
			});
		});
	},
	tagItems: function(items){
		this.tagsitems = '';
		var self = this;
		$.each(items, function(i){
			self.tagsitems += '<div class="ap-tag-item" data-action="ap-add-tag" data-name="'+ this.name +'"><div class="ap-tag-item-inner">';
			self.tagsitems += '<div class="tag-title"><strong>'+ this.name +'</strong> &times; <span>'+this.count+'</span></div>';			
			self.tagsitems += '<span class="tag-description">'+ this.description +'</strong>';			
			self.tagsitems += '</div></div>';
		});
	},
	
	addTag: function(){
		var self = this;
		
		$('body').delegate('[data-action="ap-add-tag"]', 'click touchstart', function(){
			$('[data-role="ap-tagsinput"]').tagsinput('add', $(this).data('name'));
			$('[data-role="ap-tagsinput"]').tagsinput('input').val('');
			$('#ap-suggestions').hide();
		});
	},
	uploadForm:function(){
		var self = this;
		$('[data-action="ap-upload-field"]').change(function(){
			$(this).closest('form').submit();
		});
		
		$('[data-action="ap-upload-form"]').submit(function(){
			$(this).ajaxSubmit({
				beforeSubmit:  function(){
					self.showLoading(aplang.uploading);
				},
				success: function(data){
					self.hideLoading();
					$('body').trigger('uploadForm', data);
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
		
		$('body').on('uploadForm', function(e, data){
			if(data['view'] == '[data-view="cover"]')
				$(data['view']).attr('style', data['background-image']);
			else if(data['view'] == '[data-view="avatar-main"]')
				$(data['view']).html(data['image']);
			
		});
	},
	
	saveProfile: function(){
		var self = this;
		$('[data-action="ap-edit-profile"]').submit(function(){
			$(this).ajaxSubmit({
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
		$('body').delegate('[data-action="ap-send-message"]', 'submit', function(){
			$(this).ajaxSubmit({
				beforeSubmit:  function(){
					self.showLoading(aplang.sending_message);
				},
				success: function(data){
					self.hideLoading();
					if(data['status'] == true){
						self.addMessage(data['message'], 'success');
						var container = $('[data-view="conversation"] > ul');
						
						if(container.length == 0){
							$('[data-view="conversation"]').html($(data['html']).hide());
							$('[data-view="conversation"] > ul').slideDown(200);
							$('[data-view="conversation"] > form').slideDown(200);
						}else{	
							container.append($(data['html']).hide());
							container.find('li:last-child').slideDown(200);
						}
						$('textarea.autogrow').autogrow({onInitialize: true});						
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
		
		$('body').delegate('[data-action="ap-show-conversation"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.loading_conversation);
			$(this).parent().find('li.active').removeClass('active');
			$(this).addClass('active');
			$.ajax({
				type: 'POST',			
				url: ajaxurl,
				data: {
					action:'ap_show_conversation',
					args: $(this).data('args')
				},
				context:this,
				dataType:'json',				
				success: function(data){
					self.hideLoading();
					if(data['status']){
						$('[data-view="conversation"]').html(data['html']);
						self.addMessage(data['message'], 'success');
					}
					else{
						self.addMessage(data['message'], 'error');
					}
					
				}
			});
		});
		if(!ap_url_string_value('to'))
			$('[data-action="ap-show-conversation"].active').click();
	},
	newMessageButton: function(){
		var self = this;
		$('body').delegate('[data-button="ap-new-message"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.loading_new_message_form);
			$.ajax({
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
						$('[data-view="conversation"]').html(data['html']);
						$('textarea.autogrow').autogrow({onInitialize: true});
						$('[data-action="ap-suggest-user"]').tagsinput({
							freeInput: false,
							itemValue: 'value',
							itemText: 'text',
						});
						//if(window.location.search.match("to?=").length > 0)
							//$('[data-action="ap-suggest-user"]').tagsinput('add', { "value": ap_url_string_value('to'), "text": ap_url_string_value('dname')});
							
						$('[data-action="ap-suggest-user"]').tagsinput('input').blur(function(e){
							$(document).mouseup(function (e){
								var container = $('#ap-suggestions');

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
			$('[data-button="ap-new-message"]').click();
	},
	userSuggestion: function(){
		this.usersquery;
		var self = this;
		
		$('body').delegate('[data-action="ap-add-user"]', 'click', function(){
			$('[data-action="ap-suggest-user"]').tagsinput('add', { "value": $(this).data('id'), "text": $(this).data('name')});
			$('[data-action="ap-suggest-user"]').tagsinput('input').val('');
			$('#ap-suggestions').hide();
		});
		
		$('body').delegate('#ap-new-message .bootstrap-tagsinput input', 'keyup', function(){
			var value = $(this).val();
			
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
					var container = $(this).closest('.bootstrap-tagsinput'),
						position = container.offset();
					
					if($('#ap-suggestions').length ==0)
						$('body').append('<div id="ap-suggestions" class="ap-suggestions user-suggestions" style="display:none"></div>');
					
					if(data['items']){
						self.userItems(data['items']);
						
						$('#ap-suggestions').html(self.useritems).css({'top': (position.top + container.height() + 20), 'left': position.left}).show();
					}					
				},
				this
			);
		});

	},
	userItems: function(items){
		this.useritems = '';
		var self = this;
		$.each(items, function(i){
			self.useritems += '<div class="ap-suggestion-user" data-action="ap-add-user" data-name="'+ this.name +'" data-id="'+ this.id +'"><div class="ap-user-item-inner clearfix">';
			self.useritems += '<div class="suggestion-avatar">'+ this.avatar +'</div>';			
			self.useritems += '<span class="name">'+ this.name +'</strong>';			
			self.useritems += '</div></div>';
		});
	},
	loadMoreConversations:function(elem){
		var self = this;
		self.showLoading(aplang.loading_more_conversations);
		var offset = $(elem).attr('data-offset');
		
		if(typeof offset !== 'undefined')
		self.doAjaxForm(
			{action:'ap_load_conversations', offset: offset, args: $(elem).attr('data-args')}, 
			function(data){
				self.hideLoading();				
				if(data['status']){
					self.addMessage(data['message'], 'success');
					$(elem).attr('data-offset', parseInt(offset)+1);
					$('#ap-conversation-scroll ul').append($(data['html']).html());
					$('#ap-conversation-scroll').perfectScrollbar('update');
				}else{
					self.addMessage(data['message'], 'error');
				}
			}
		);
	},
	searchMessage: function(){
		var self = this;
		
		$('body').delegate('[data-action="ap-search-conversations"]', 'keyup', function(){
			var value = $(this).val();
			
			if(value.length == 0)
				return;
				
			/* abort previous ajax request */
			if(typeof self.messageSearchXhr !== 'undefined'){
				self.messageSearchXhr.abort();
			}
			
			$(this).closest('form').submit();
		});
		
		$('[data-role="ap-search-conversations"]').submit(function(){
			self.messageSearch = $(this).ajaxSubmit({
				beforeSubmit:  function(){
					self.showLoading(aplang.searching_conversations);
				},
				success: function(data){
					self.hideLoading();
					if(data['status']){
						$(this).removeAttr('data-offset');
						$('#ap-conversation-scroll ul').html($(data['html']).html());
						$('#ap-conversation-scroll').perfectScrollbar('update');
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
		$('body').delegate('[data-button="ap-edit-message"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.loading_message_edit_form);
			self.doAjaxForm(
				{action:'ap_message_edit_form', args: $(this).data('args')}, 
				function(data){
					self.hideLoading();				
					if(data['status']){
						self.addMessage(data['message'], 'success');
						$(this).closest('.ap-message').find('[data-view="ap-message-content"]').html(data['html']);
						$('textarea.autogrow').autogrow({onInitialize: true});
					}else{
						self.addMessage(data['message'], 'error');
					}
				},
				this
			);
		});
		
		$('body').delegate('[data-action="ap-edit-message"]', 'submit', function(){
			$(this).ajaxSubmit({
				beforeSubmit:  function(){
					self.showLoading(aplang.updating_message);
				},
				success: function(data){
					self.hideLoading();
					if(data['status']){
						self.addMessage(data['message'], 'success');
						$(this).after(data['html']);
						$(this).remove();
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
		$('body').delegate('[data-button="ap-delete-message"]', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.deleting_message);
			self.doAjaxForm(
				{action:'ap_delete_message', args: $(this).data('args')}, 
				function(data){
					self.hideLoading();				
					if(data['status']){
						self.addMessage(data['message'], 'success');
						$(this).closest('.ap-message').remove();
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
		
		$('body').delegate('#ap-quick-ask-input', 'keyup', function(){
			var value = $(this).val();
			
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
					var container = $(this).closest('.ap-qaf-inner'),
						position = container.offset();
					
					if($('#ap-qsuggestions').length ==0)
						$('body').append('<div id="ap-qsuggestions" class="ap-qsuggestions" style="display:none"></div>');
					
					if(data['items']){
						self.qsItems(data['items']);
						
						$('#ap-qsuggestions').html(self.qsitems).css({'top': (position.top + container.height() + 10), 'left': position.left, 'width': container.width()}).show();
					}
					
				},
				this
			);
		});
		
		$('body').delegate('#ap-qsuggestions', 'click', function(e){
			$('#ap-qsuggestions').toggle();
		});

	},
	qsItems: function(items){
		this.qsitems = '';
		var self = this;
		$.each(items, function(i){
			self.qsitems += this.html;
		});
	},
	flushRules:function(){
		var self = this;
		
		$('.ap-missing-rules > a').click(function(e){
			e.preventDefault();
			self.showLoading(aplang.sending);
			self.doAjaxForm(
				{action: 'ap_install_rewrite_rules', args: $(this).data('args')},
				function(){
					self.hideLoading();
					$('.ap-missing-rules').hide();
				}
			);
		});

	},
	submitAjaxForm: function(form, before, after){
		$('body').delegate(form, 'submit', function(){
			$(this).ajaxSubmit({
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
		$('body').delegate('#ap-load-new-tag-form', 'click', function(e){
			e.preventDefault();
			self.showLoading(aplang.loading);
			self.doAjaxForm(
				{action: 'ap_load_new_tag_form', args: $(this).data('args')},
				function(data){
					self.hideLoading();
					if(data['status']){
						$(this).closest('.ap-suggestions').html(data['html']);
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
					$('[data-role="ap-tagsinput"]').tagsinput('add', data['tag']['slug']);
					$('[data-role="ap-tagsinput"]').tagsinput('input').val('');
					$('#ap-suggestions').hide();
				}else{
					self.addMessage(data['message'], 'error');
				}
			}
		);
	},
	loginAccor:function(){
		var self = this;
		$('.ap-ac-accordion > strong').click(function(){
			var $elm = $(this).parent();
			if(!$elm.hasClass('active'))
			{
				$('.ap-ac-accordion').removeClass('active');
				$('.ap-ac-accordion .accordion-content').hide();
				$(this).parent().addClass('active');
				$(this).next().slideToggle();
			}
			else
			{
				$elm.removeClass('active');
				$elm.find('.accordion-content').hide();
			}
		});
	},
	foldContent: function(){
		$('[data-action="ap_fold_content"]').each(function(e){
			if($('.ap-fold-inner', this).height() > 80)
				$(this).next().show();
		});
	},

	expandToggle: function(){
		var self = this;
		$('[data-button="ap_expand_toggle"]').click(function(e){
			e.preventDefault();

			$(this).prev().animate({'height': $(this).prev().find('> *').height() }, 200);
			$(this).hide();

		});
	},

	removeHasError: function(){
		$('.ap-have-error input, .ap-have-error textarea, .ap-have-error select').click(function(){
			$(this).closest('.ap-have-error').addClass('being-edited');
		});
	}


	
};

})(jQuery);


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

