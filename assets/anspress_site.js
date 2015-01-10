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

		this.doAction();
		this.afterAjaxComplete();
		this.appendFormError();
		this.appendMessageBox();
		this.ap_comment_form();
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

		//if(typeof ApSite.loading[action] !== 'undefined')
			ApSite.hideLoading();

		var new_success = function new_success(data){
			ApSite.hideLoading();
			if(success !== false)
				success(data);
		};

		var new_before = function new_before(){
			ApSite.showLoading();			

			if(before !== false)
				before();
		};

		var req = $.ajax({
					type: 'POST',
					url: ajaxurl, 
					data: query, 
					beforeSend: new_before, 
					success: new_success, 
					dataType: 'json',
					context:context,
				});
		ApSite.ajax_id[action] = req;

		return req;
	},

	doAction: function(action){
		var action = typeof action !== 'undefined' ? '[data-action="'+action+'"]' : '[data-action]';

		$(action).each(function(i){

			var action = $(this).attr('data-action');

			if (typeof ApSite[action] === 'function')
				ApSite[action](this);
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

			if(typeof data !== 'undefined' && typeof data.responseJSON !== 'undefined' && typeof data.responseJSON.ap_responce !== 'undefined'){

				if(typeof data.responseJSON.message !== 'undefined'){
					var type = typeof data.responseJSON.message_type === 'undefined' ? 'success' : data.responseJSON.message_type;				
					ApSite.addMessage(data.responseJSON.message, type);
				}

				$(document).trigger('ap_after_ajax', data.responseJSON);
				console.log(data.responseJSON.do !== 'undefined' && typeof ApSite[data.responseJSON.do] === 'function');
				if (data.responseJSON.do !== 'undefined' && typeof ApSite[data.responseJSON.do] === 'function')
					ApSite[data.responseJSON.do](data.responseJSON);
			}
		});
	},

	uniqueId: function(){
		return $('.ap-uid').length;
	},

	showLoading: function(){
		var uid = this.uniqueId();
		var el = $('<div class="ap-loading-icon ap-uid" id="apuid-'+ uid +'"><i class="icon-sync"><i></div>');
		$('body').append(el);
		
		return '#apuid-'+ uid;
	},

	hideLoading: function(){
		$('.ap-loading-icon').hide();
	},

	suggest_similar_questions: function(elm){
		var uid = this.uniqueId();
		$(elm).on('keyup', function(){
			ApSite.doAjax( 
				apAjaxData('ap_ajax_action=suggest_similar_questions&value=' + $(elm).val()), 
				function(data){
					if(typeof data['html'] !== 'undefined')
						$('#similar_suggestions').html(data['html']);
				}, 
				elm,
				false,
				true
			);
		});

	},

	ap_ajax_form:function(form){		
		$('body').delegate(form,'submit', function(){
			
			if(typeof tinyMCE !== 'undefined')
				tinyMCE.triggerSave();

			ApSite.doAjax( 
				apAjaxData($(form).formSerialize()), 
				function(data){
					console.log(data);
				}, 
				form
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
		if(typeof data.redirect_to !== 'undefined')
			window.location.replace(data.redirect_to);
	},

	append:function(data){
		if(typeof data.container !== 'undefined')
			$(data.container).append(data.html);
	},

	load_comment_form: function(elm){
		var q = $(elm).data('query');

		$(elm).click( function(e){
			e.preventDefault();
			
			ApSite.doAjax( 
				apAjaxData(q), 
				function(data){
					$('.ap-comment-form').remove();					

					$('html, body').animate({
						scrollTop: ($(data.container).offset().top) - 50
					}, 500);

					if(typeof $(elm).attr('data-toggle') !== 'undefined')
						$($(elm).attr('data-toggle')).hide();

					$('#ap-comment-textarea').focus();
					ApSite.doAction('ap_ajax_form');
				}, 
				elm,
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
				}, 
				this
			);
			return false;
		})
	},
	delete_comment: function(elm){
		var q = $(elm).data('query');

		$(elm).click( function(e){
			e.preventDefault();
			
			ApSite.doAjax( 
				apAjaxData(q), 
				function(data){
					if(typeof $(elm).attr('data-toggle') !== 'undefined')
						$($(elm).attr('data-toggle')).hide();
				}, 
				elm,
				false,
				true
			);
		});
	},

	ap_subscribe: function(elm){
		var q = $(elm).data('query');

		$(elm).click( function(e){
			e.preventDefault();
			
			ApSite.doAjax( 
				apAjaxData(q), 
				function(data){
					if(data.message_type == 'success'){
						$(elm).addClass('active');
						$(elm).closest('.ap-subscribe').addClass('active');
					}else{
						$(elm).removeClass('active');
						$(elm).closest('.ap-subscribe').removeClass('active');
					}
				}, 
				elm,
				function(){
					$(elm).closest('.ap-subscribe').toggleClass('active');
				}
			);
		});
	}



}

})(jQuery);



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
