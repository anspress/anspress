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

		this.doAction();
		this.afterAjaxComplete();
	},

	doAjax: function(query, success, context, before, abort){
		/** Shorthand method for calling ajax */
		context 	= typeof context !== 'undefined' ? context : false;
		success 	= typeof success !== 'undefined' ? success : false;
		before 		= typeof before !== 'undefined' ? before : false;
		abort 		= typeof abort !== 'undefined' ? abort : false;

		if(abort && (typeof ApSite.ajax_id[query['ap_ajax_action']] !== 'undefined')){
			ApSite.ajax_id[query['ap_ajax_action']].abort();
		}

		var req = $.ajax({
					type: 'POST',
					url: ajaxurl, 
					data: query, 
					beforeSend: before, 
					success: success, 
					dataType: 'json',
					context:context,
				});
		ApSite.ajax_id[query['ap_ajax_action']] = req;

		return req;
	},

	doAction: function(){
		$('[data-action]').each(function(i){
			
			var action = $(this).data('action');

			if (typeof ApSite[action] === 'function')
				ApSite[action](this);
			else
				console.log('No "'+action+'" method found in AnsPress.site{}');

		});
	},
	
	/**
	 * Process to run after completing an ajax request
	 * @return {void}
	 * @since 2.0
	 */
	afterAjaxComplete:function(){
		var self = this;
		$( document ).ajaxComplete(function( event, data, settings ) {
			//console.log(data);
			if(typeof data !== 'undefined' && typeof data.responseJSON !== 'undefined' && typeof data.responseJSON.message !== 'undefined'){
				var type = typeof data.responseJSON.message_type === 'undefined' ? 'success' : data.responseJSON.message_type;
				self.addMessage(data.responseJSON.message, type);
				$('document').trigger('ap_after_ajax', data);
			}
		});
	},

	uniqueId: function(){
		return $('.ap-uid').length;
	},

	showLoading: function(elm){
		var uid = this.uniqueId();
		var offset = $(elm).offset();
		var top = offset.top;
		var left = offset.left;
		
		if($(elm).is('a')){
			var el = $('<div class="ap-loading-icon icon-sync ap-uid loadin_in_btn" data-uid="'+ uid +'"></div>');
			$(elm).append(el);
		}else if($(elm).is('input[type="text"]')){
			var top = top + ($(elm).outerHeight()/2) - 10;
			var left = left + $(elm).outerWidth() -30;
			var el = $('<div class="ap-loading-icon icon-sync ap-uid" data-uid="'+ uid +'"></div>');
			$('body').append(el);
			$(el).css({'top':top, 'left': left});
		}else {
			var top = top + 6;
			var left = left - 15;
			var el = $('<div class="ap-loading-icon icon-sync ap-uid" data-uid="'+ uid +'"></div>');
			$('body').append(el);			
			$(el).css({'top':top, 'left': left});
		}
		return el;
	},

	suggest_similar_questions: function(elm){
		$(elm).on('keyup', function(){
			var aj = ApSite.doAjax( 
				apAjaxData('ap_ajax_action=suggest_similar_questions&value=' + $(elm).val()), 
				function(data){
					if(typeof data['html'] !== 'undefined')
						$('#similar_suggestions').html(data['html']);
				}, 
				elm,
				function(){
					ApSite.showLoading(elm);
				},
				true
			);
		});

	}

}

})(jQuery)

function apAjaxData(param){
	var param = apQueryStringToJSON(param);
	param['action'] = 'ap_ajax';
	return param;
}

function apQueryStringToJSON(string) {

    var pairs = string.split('&');
    
    var result = {};
    pairs.forEach(function(pair) {
        pair = pair.split('=');
        result[pair[0]] = decodeURIComponent(pair[1] || '');
    });

    return JSON.parse(JSON.stringify(result));
}