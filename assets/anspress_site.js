/**
 * Javascript code for AnsPress fontend
 * @since 2.0
 * @package AnsPress
 * @author Rahul Aryan
 * @license GPL 2+
 */
(function($){
/* on start */
$(function() {
     
    /* create document */
    AnsPress.site = new AnsPress.site();
    /* need to call init manually with $ */
    AnsPress.site.initialize();
 
});
 
/* namespace */
window.AnsPress = {};
AnsPress.site = function() {};
 

AnsPress.site.prototype = {

	/** Initalize the class */
	initialize: function(){
		ApSite = this;
		this.ajax_id = new Object();
		this.loading = new Object();
		this.errors;
		this.ajaxData;

		this.afterAjaxComplete();
		this.appendFormError();
		this.appendMessageBox();
		this.ap_comment_form();
		this.afterPostingAnswer();

	},

	doAjax: function(query, success, context, before, abort){
		/** Shorthand method for calling ajax */
		context 	= typeof context !== 'undefined' ? context : false;
		success 	= typeof success !== 'undefined' ? success : false;
		before 		= typeof before !== 'undefined' ? before : false;
		abort 		= typeof abort !== 'undefined' ? abort : false;

		var action = apGetValueFromStr(query, 'ap_ajax_action');

		if(abort && (typeof ApSite.ajax_id[action] !== 'undefined')){
			ApSite.ajax_id[action].abort();
		}

		ApSite.showLoading();

		var req = $.ajax({
					type: 'POST',
					url: ajaxurl, 
					data: query, 
					beforeSend: before, 
					success: success, 
					dataType: 'json',
					context:context,
				});
		ApSite.ajax_id[action] = req;

		return req;
	},

	doAction: function(action){
		var self = this;
		var action = typeof action !== 'undefined' ? '[data-action="'+action+'"]' : '[data-action]';
		var actions = new Object();
		$(action).each(function(i){

			var action = $(this).attr('data-action');
			if(typeof actions[action] !== 'undefined')
				return;

			actions[action] = '1';

			if (typeof self[action] === 'function')
				self[action]('[data-action="'+action+'"]');
			/*else
				console.log('No "'+action+'" method found in AnsPress.site{}');*/

		});
	},
	
	/**
	 * Process to run after completing an ajax request
	 * @return {void}
	 * @since 2.0
	 */
	afterAjaxComplete:function(){
		$( document ).ajaxComplete(function( event, data, settings ) {
			ApSite.hideLoading();
			if(typeof data !== 'undefined' && typeof data.responseJSON !== 'undefined' && typeof data.responseJSON.ap_responce !== 'undefined'){
				var data = data.responseJSON;
				if(typeof data.message !== 'undefined'){
					var type = typeof data.message_type === 'undefined' ? 'success' : data.message_type;				
					ApSite.addMessage(data.message, type);
				}

				$(document).trigger('ap_after_ajax', data);
				
				if (typeof data.do !== 'undefined' && typeof ApSite[data.do] === 'function')
					ApSite[data.do](data);

				if (typeof data.view !== 'undefined'){
					$.each(data.view, function(i, view){
						$('[data-view="'+ i +'"]').text(view);
						
						if(view !== 0)
							$('[data-view="'+ i +'"]').removeClass('ap-view-count-0');
					});
				}

			}
		});
	},

	uniqueId: function(){
		return $('.ap-uid').length;
	},

	showLoading: function(){
		var uid = this.uniqueId();
		var el = $('<div class="ap-loading-icon ap-uid" id="apuid-'+ uid +'"><i class="apicon-sync"><i></div>');
		$('body').append(el);
		
		return '#apuid-'+ uid;
	},

	hideLoading: function(){
		$('.ap-loading-icon').hide();
	},

	suggest_similar_questions: function(action){
		$(action).on('keyup', function(){
			if($.trim($(this).val()) == '')
				return;

			ApSite.doAjax( 
				apAjaxData('ap_ajax_action=suggest_similar_questions&value=' + $(this).val()), 
				function(data){
					if(typeof data['html'] !== 'undefined')
						$('#similar_suggestions').html(data['html']);
				}, 
				this,
				false,
				true
			);
		});

	},

	ap_ajax_form:function(action){		
		$('body').delegate(action,'submit', function(){
			
			if(typeof tinyMCE !== 'undefined')
				tinyMCE.triggerSave();

			ApSite.doAjax( 
				apAjaxData($(this).formSerialize()), 
				function(data){
					if(typeof tinyMCE !== 'undefined' && typeof data.type !== 'undefined' && data.type == 'success')
						tinyMCE.activeEditor.setContent('');
				}, 
				this
			);
			return false;
		})
	},

	appendFormError: function(){
		$(document).on('ap_after_ajax', function(e, data){
			if(typeof data.errors !== 'undefined'){
				ApSite.clearFormErrors(data.form);
				$.each(data.errors, function(i, message) {
					var parent = $('#' + data.form ).find('#'+i).closest('.ap-form-fields');
					parent.addClass('ap-have-error');
					ApSite.helpBlock(parent, message);
				});
			}
		});
	},

	helpBlock:function(elm, message){
		/* remove existing help block */
		if($(elm).find('.ap-form-error-message').length > 0)
			$(elm).find('.ap-form-error-message').remove();
			
		$(elm).append('<p class="ap-form-error-message">'+message+'</p>');
	},

	clearFormErrors: function(form){
		var elm = $('#' + form ).find('.ap-have-error');
		elm.find('.ap-form-error-message').remove();
		elm.removeClass('ap-have-error');		
	},

	appendMessageBox: function(){
		if($('#ap-notify').length == '0')
			$('body').append('<div id="ap-notify"></div>');
	},
	addMessage: function(message, type){
		var icon = aplang[type];
		$('<div class="ap-notify-item '+type+'"><i class="'+icon+'"></i>'+message+'</div>').appendTo('#ap-notify').animate({'margin-left': 0}, 500).delay(5000).fadeOut(200);

		
	},

	redirect:function(data){
		console.log(typeof data.redirect_to !== 'undefined');
		if(typeof data.redirect_to !== 'undefined')
			window.location.replace(data.redirect_to);
	},

	append:function(data){
		if(typeof data.container !== 'undefined')
			$(data.container).append(data.html);
	},

	load_comment_form: function(action){

		$('body').delegate(action, 'click', function(e){
			e.preventDefault();
			var q = $(this).attr('data-query');
			ApSite.doAjax( 
				apAjaxData(q), 
				function(data){
					$('.ap-comment-form').remove();					
					$(this).closest('.ap-content-inner').append(data.html);

					$('html, body').animate({
						scrollTop: ($(data.container).offset().top) - 50
					}, 500);

					if(typeof $(this).attr('data-toggle') !== 'undefined')
						$($(this).attr('data-toggle')).hide();

					$('#ap-comment-textarea').focus();

					$($(this).attr('href')).addClass('have-comments').removeClass('no-comment');
				}, 
				this,
				false,
				true
			);
		});

	},

	ap_comment_form:function(){		
		$('body').delegate('#ap-commentform','submit', function(){
			
			if(typeof tinyMCE !== 'undefined')
				tinyMCE.triggerSave();

			ApSite.doAjax( 
				apAjaxData($(this).formSerialize()), 
				function(data){
					if(data['action'] == 'new_comment' && data['message_type'] == 'success'){
						$('#comments-'+data['comment_post_ID']+' ul.ap-commentlist').append($(data['html']).hide().slideDown(100));
					}else if(data['action'] == 'edit_comment' && data['message_type'] == 'success'){
						$('#li-comment-'+data.comment_ID).replaceWith($(data['html']).hide().slideDown(100));
						$('.ap-comment-form').remove();
					}
					$(this)[0].reset();
					$('.ap-comment-form').fadeOut(200, function(){$(this).remove()});
				}, 
				this
			);
			return false;
		})
	},
	delete_comment: function(action){
		$('body').delegate(action, 'click', function(e){
			e.preventDefault();
			var q = $(this).attr('data-query');
			ApSite.doAjax( 
				apAjaxData(q), 
				function(data){
					if(typeof $(this).attr('data-toggle') !== 'undefined')
						$($(this).attr('data-toggle')).hide();
				}, 
				this,
				false,
				true
			);
		});
	},

	ap_subscribe: function(action){
		$(action).click( function(e){
			e.preventDefault();
			var q = $(this).attr('data-query');
			ApSite.doAjax( 
				apAjaxData(q), 
				function(data){
					if(data.action == 'subscribed'){
						$(this).addClass('active');
						$(this).closest('.ap-subscribe').addClass('active');
					}else{
						$(this).removeClass('active');
						$(this).closest('.ap-subscribe').removeClass('active');
					}
				}, 
				this,
				function(){
					$(this).closest('.ap-subscribe').toggleClass('active');
				}
			);
		});
	},

	vote:function(action ){

		$('body').delegate(action +' a', 'click', function(e){
			e.preventDefault();
			var q = $(this).attr('data-query');

			ApSite.doAjax( 
				apAjaxData(q), 
				function(data){
					var vote_c = $(this).parent();
					vote_c.find('.ap-vote-fade').remove();
					if(typeof data['action'] !== 'undefined' && data['action'] == 'voted' || data['action'] == 'undo'){
						if(data['action'] == 'voted'){
							
							$(this).addClass('voted');
							if(data['type'] == 'vote_up') vote_c.find('.vote-down').addClass('disable');
							if(data['type'] == 'vote_down') vote_c.find('.vote-up').addClass('disable');
							
							$(this).trigger('voted', data);
						}
						else if(data['action'] == 'undo'){
							$(this).removeClass('voted');
							if(data['type'] == 'vote_up') vote_c.find('.vote-down').removeClass('disable');
							if(data['type'] == 'vote_down') vote_c.find('.vote-up').removeClass('disable');
							$(this).trigger('undo_vote', data);
						}
						vote_c.find('.net-vote-count').text(data['count']);
					}
				}, 
				this,
				false
			);
		});
	},

	afterPostingAnswer: function(){
		$(document).on('ap_after_ajax', function(e, data){
			if(typeof data.action !== 'undefined' && data.action == 'new_answer'){
				if($('#answers').length === 0){
					$('#question').after($(data['html']));
					$(data['div_id']).hide();
				}else
					$('#answers').append($(data['html']).hide());				
				
				$(data.div_id).slideDown(500);
			}
		});
	},

	select_answer: function(action){
		$('body').delegate(action, 'click', function(e){
			e.preventDefault();
			var q = $(this).attr('data-query');

			ApSite.doAjax( 
				apAjaxData(q)
			);
		});
	},

	ap_delete_post: function(action){
		$('body').delegate(action, 'click', function(e){
			e.preventDefault();
			var q = $(this).attr('data-query');

			ApSite.doAjax( 
				apAjaxData(q), 
				function(data){
					if(typeof data.action !== 'undefined' && data.action == 'delete_answer')
						$(data.div_id).slideUp(500).fadeOut(300, function(){ $(this).remove(); })
				}, 
				this,
				false
			);
		});
	},

	ap_upload_field:function(action){
		var self = this;
		var form 
		$(action).change(function(){
			$(this).closest('form').submit();
		});
		
		$('[data-action="ap_upload_form"]').submit(function(){
			$(this).ajaxSubmit({
				success: function(data){
					$('body').trigger('uploadForm', data);
				},
				url:ajaxurl,
				dataType:'json'
			});
			
			return false
		});
	}

}

})(jQuery);

jQuery(document).ready(function(){
	ApSite.doAction();
})



function apAjaxData(param){
	param = param + '&action=ap_ajax';
	return param;
}

function apQueryStringToJSON(string) {

    var pairs = string.split('&');
    
    var result = {};
    pairs.forEach(function(pair) {
        pair = pair.split('=');
        result[pair[0]] = encodeURIComponent(pair[1] || '');
    });

    return JSON.parse(JSON.stringify(result));
}

function apGetValueFromStr(q, name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(q);
    return results == null ? false : decodeURIComponent(results[1].replace(/\+/g, " "));
}
