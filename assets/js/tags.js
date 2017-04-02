(function($){
	'use strict';

	function apSanitizeTitle(str) {
	  str = str.replace(/^\s+|\s+$/g, ''); // trim
	  str = str.toLowerCase();

	  // remove accents, swap ñ for n, etc
	  var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;";
	  var to   = "aaaaaeeeeeiiiiooooouuuunc------";

	  /*for (var i=0, l=from.length ; i<l ; i++) {
	  	str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
	  }*/

	  str = str.replace(/\s+/g, '-') // collapse whitespace and replace by -
	    .replace(/-+/g, '-'); // collapse dashes

	    return str;
	}

	function apAddTag(str, container){
		str = str.replace(/,/g, '');
		str = str.trim();
		str = apSanitizeTitle(str);
		
		if( str.length > 0 ){

			var htmlTag = {
				element : 'li',
				class : 'ap-tagssugg-item',
				itemValueClass : 'ap-tag-item-value',
				button : {
					class : 'ap-tag-add',
					icon : 'apicon-plus',
				},
				input : '',
				accessibilityText : apTagsTranslation.addTag
			}
			
			// Add tag to the main container (holder list), 
			// Else add tag to a specific container (suggestion list)
			if(!container){
				
				var container = '#ap-tags-holder';
				htmlTag.button.class = 'ap-tag-remove';
				htmlTag.button.icon = 'apicon-x';
				htmlTag.input = '<input type="hidden" name="tags[]" value="'+str+'" />';
				htmlTag.accessibilityText = apTagsTranslation.deleteTag;
				
				var exist_el = false;
				$(container).find('.'+htmlTag.class).find('.'+htmlTag.itemValueClass).each(function(index, el) {
					if(apSanitizeTitle($(this).text()) == str)
						exist_el = $(this);
				});
				if (exist_el !== false) { // If the element already exist, stop and dont add tag
					exist_el.animate({opacity: 0}, 100, function(){
						exist_el.animate({opacity: 1}, 400);
					});
					return; 
				}
				
				if (!$('#tags').is(':focus'))
					$('#tags').val('').focus();
					
				$('#ap-tags-suggestion').hide();
				
				// Message for screen reader
				// Timeout used to resolve a bug with JAWS and IE...
				setTimeout(function() {
					$('#ap-tags-aria-message').text(str + " " + apTagsTranslation.tagAdded);
				}, 250);
			}
			
			var html = $('<'+htmlTag.element+' class="'+htmlTag.class+'" title="'+htmlTag.accessibilityText+'"><button role="button" class="'+htmlTag.button.class+'"><span class="'+htmlTag.itemValueClass+'">'+str+'</span><i class="'+htmlTag.button.icon+'"></i></button>'+htmlTag.input+'</'+htmlTag.element+'>');
			html.appendTo(container).fadeIn(300);
			
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
				AnsPress.hideLoading(this);
				
				console.log(data);
				
				$('#ap-tags-suggestion').html('');
				
				if(!data.status)
					return;

				if (!$('#ap-tags-suggestion').is(':visible')) {
					$('#ap-tags-suggestion').show();
				}
						
				if(data['items']){
					$.each(data['items'], function(index, val) {
						val = decodeURIComponent(val);
						var holderItems = [];
						$("#ap-tags-holder .ap-tag-item-value").each(function() {
							holderItems.push($(this).text())
						});
						if ($.inArray(val, holderItems)<0) // Show items that was not already inside the holder list
							apAddTag(val, '#ap-tags-suggestion');
					});
				}
				
				// Message for screen reader
				// Timeout used to resolve a bug with JAWS and IE...
				setTimeout(function() {
					$('#ap-tags-aria-message').text(apTagsTranslation.suggestionsAvailable);
				}, 250);
			}
		});
	}

	$(document).ready(function(){

		$('#tags').on('apAddNewTag',function(e){
			e.preventDefault();
			apAddTag($(this).val().trim(','));
			$(this).val('');
		});

		$('#tags').on('keydown', function(e) {
			if(e.keyCode == 13) { // Prevent submit form on Enter
			  	e.preventDefault();
			  	return false;
			}
			if(e.keyCode == 38 || e.keyCode == 40) {
				var inputs = $('#ap-tags-suggestion').find('.ap-tag-add');
				var focused = $('#ap-tags-suggestion').find('.focus');
				var index = inputs.index(focused);
				
				if(index != -1) {
					if(e.keyCode == 38) // up arrow
						index--;
					if(e.keyCode == 40) // down arrow
						index++;
				}
				else {
					if(e.keyCode == 38) // up arrow
						index = inputs.length-1;
					if(e.keyCode == 40) // down arrow
						index = 0;
				}
				
				if (index >= inputs.length)
					index = -1;
				
				inputs.removeClass('focus');
				
				if(index != -1) {
					inputs.eq(index).addClass('focus');
					$(this).val(inputs.eq(index).find('.ap-tag-item-value').text());
				} 
				else {
					$(this).val($(this).attr('data-original-value'));
				}
			}
		});

		$('#tags').on('keyup focus', function(e) {
			e.preventDefault();
			var val = $(this).val().trim();
			clearTimeout(window.tagtime);
			if(e.keyCode != 9 && e.keyCode != 37 && e.keyCode != 38 && e.keyCode != 39 && e.keyCode != 40) { // Do nothing on Tab and arrows keys
				if(e.keyCode == 13 || e.keyCode == 188 ) { // "Enter" or ","
					clearTimeout(window.tagtime);
					$(this).trigger('apAddNewTag');
				} else {
					$(this).attr('data-original-value', $(this).val());
					window.tagtime = setTimeout(function() {
						apTagsSuggestion(val);
					}, 200);
				}
			}
		});
		
		$('#ap-tags-suggestion').on('click', '.ap-tagssugg-item', function(e) {
			apAddTag($(this).find('.ap-tag-item-value').text());
			$(this).remove();
		});

		$('body').on('click focusin', function(e) {
			if ($('#ap-tags-suggestion').is(':visible') && $(e.target).parents('#ap-tags-add').length <= 0)
			  	$('#ap-tags-suggestion').hide();
		});
		
		$('body').on('click', '.ap-tagssugg-item', function(event) {
			var itemValue = $(this).find('.ap-tag-item-value').text();
			
			// Message for screen reader
			// Timeout used to resolve a bug with JAWS and IE...
			setTimeout(function() {
				$('#ap-tags-aria-message').text(itemValue + " " + apTagsTranslation.tagRemoved);
			}, 250);
			
			$(this).remove();
			$('#ap-tags-list-title').focus();
		});
		
		// Message used by screen reader to get suggestions list or a confirmation when a tag is added
		$('body').append('<div role="status" id="ap-tags-aria-message" aria-live="polite" aria-atomic="true" class="sr-only"></div>');
	})

})(jQuery)
