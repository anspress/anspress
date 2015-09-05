(function($){
	'use strict';

	function apSanitizeTitle(str) {
	  str = str.replace(/^\s+|\s+$/g, ''); // trim
	  str = str.toLowerCase();

	  // remove accents, swap ñ for n, etc
	  var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
	  var to   = "aaaaaeeeeeiiiiooooouuuunc------";

	  for (var i=0, l=from.length ; i<l ; i++) {
	  	str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
	  }

	  str = str.replace(/[^a-z0-9 -]/g, '') // remove invalid chars
	    .replace(/\s+/g, '-') // collapse whitespace and replace by -
	    .replace(/-+/g, '-'); // collapse dashes

	    return str;
	}

	function apAddTag(str, append){
		str = str.replace(/,/g, '');
		str = str.trim();
		str = apSanitizeTitle(str);

		if( str.length > 0 ){
			var html = $('<span class="ap-tagssugg-item" style="display:none">'+str+'<i class="ap-tag-remove">×</i><input type="hidden" name="tags[]" value="'+str+'" /></span>');

			if(typeof append === 'undefined'){
				var exsists = false;
				var exsist_el = false;
				$('#ap-tags-holder .ap-tagssugg-item').each(function(index, el) {
					if(apSanitizeTitle($(this).text()) == str){
						exsists = true;
						exsist_el = $(this);
					}
				});

				if(exsists){
					$(exsist_el).animate({opacity: 0}, 100, function(){
						$(this).animate({opacity: 1}, 400);
					});
				}else{
					html.appendTo('#ap-tags-holder').fadeIn(300);
				}
			}else{
				return html;
			}
		}
	}

	function apTagsSuggestion(value){
		if(typeof window.tagsquery !== 'undefined'){
			window.tagsquery.abort();
		}
		window.tagsquery = jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action:'ap_tags_suggestion',
				q: value
			},
			context:this,
			dataType:'json',
			success: function(data){
				AnsPress.site.hideLoading(this);
				console.log(data);
				if(!data.status)
					return;

				var container = jQuery(this).closest('.bootstrap-tagsinput'),
					position = container.offset();

				$('#ap-tags-suggestion').html('');

				if(data['items']){
					$.each(data['items'], function(index, val) {
						var html = apAddTag(val, false);
						$('#ap-tags-suggestion').append($(html).fadeIn(300));
					});
				}

			}
		});
	}

	$(document).ready(function(){

		$(window).keydown(function(event){
			if( event.keyCode == 13 && $(event.target).is('#ap-tags-input') ) {
				event.preventDefault();
				return false;
			}
		});

		$('#tags').on('apAddNewTag',function(e){
			e.preventDefault();
			apAddTag($(this).val().trim(','));
			$(this).val('');
		});

		$('#tags').keyup(function(e){
			e.preventDefault();
			if(e.keyCode == 13 || e.keyCode == 188 )
			{
				clearTimeout(window.tagtime);
				$(this).trigger('apAddNewTag');
			}
		});

		$('body').delegate('.ap-tagssugg-item', 'click', function(event) {
			$(this).remove();
		});

		$('#tags').keypress(function(e) {
			var val = $(this).val().trim();
			clearTimeout(window.tagtime);
			window.tagtime = setTimeout(function() {
				if(val.length > 1){
					apTagsSuggestion(val);
				}
			}, 200);
		});

		$('#ap-tags-suggestion').delegate('span', 'click', function(e) {
			apAddTag($(this).text());
			$(this).remove();
		});
	})

})(jQuery)
