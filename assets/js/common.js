/**
 * Common AnsPress functions and constructor.
 * @author Rahul Aryan
 * @license GPL 3+
 * @since 4.0
 */

// For preventing global namespace pollution, keep everything in AnsPress object.
window.AnsPress = _.extend({
	models: {},
	views: {},
	collections: {},
	loadTemplate: function(id){
		if(jQuery('#apTemplate').length==0)
			jQuery('<script id="apTemplate" type="text/html"></script>').appendTo('body');

		jQuery.get(apTemplateUrl + '/' + id + ".html", function(html){
			var tempCont = jQuery('#apTemplate');
			tempCont.text(html + tempCont.text());
			AnsPress.trigger('templateLoaded');
		});
	},
	getTemplate: function(templateId){
		return function(){
			if(jQuery('#apTemplate').length==0)
				return '';

			var regex = new RegExp("#START BLOCK "+templateId+" #([\\S\\s]*?)#END BLOCK "+templateId+" #", "g");
			var match = regex.exec(jQuery('#apTemplate').text());

			if(match == null)
				return '';

			if(match[1]) return match[1];
		}
	},
	isJSONString: function(str) {
		try {
			return jQuery.parseJSON(str);
		} catch (e) {
			return false;
		}
	},
	ajaxResponse: function(data){
		data = jQuery(data);
		if( typeof data.filter('#ap-response') === 'undefined' ){
			console.log('Not a valid AnsPress ajax response.');
			return {};
		}
		var parsedJSON = this.isJSONString(data.filter('#ap-response').html());
		if(!parsedJSON || parsedJSON === 'undefined' || !_.isObject(parsedJSON))
			return {};

		return parsedJSON;
	},
	ajax: function(options){
		var self = this;
		options = _.defaults(options, {
			async: false,
			url: ajaxurl,
			method: 'POST',
		});

		// Convert data to query string if object.
		if(_.isObject(options.data))
			options.data = jQuery.param(options.data);

		options.data = 'action=ap_ajax&' + options.data;

		var success = options.success;
		delete options.success;
		options.success = function(data){
			var context = options.context||null;
			var parsedData = self.ajaxResponse(data);
			if(parsedData.snackbar){
				AnsPress.trigger('snackbar', parsedData)
			}

			if(typeof success === 'function'){
				data = jQuery.isEmptyObject(parsedData) ? data : parsedData;
				success(data, context);
			}
		};

		return jQuery.ajax(options);
	},
	uniqueId: function() {
		return jQuery('.ap-uid').length;
	},
	showLoading: function(elm) {
		/*hide any existing loading icon*/
		AnsPress.hideLoading(elm);
		var customClass = jQuery(elm).data('loadclass')||'';
		var isText = jQuery(elm).is('input[type="text"]');
		var uid = this.uniqueId();
		var el = jQuery('<div class="ap-loading-icon ap-uid '+customClass+ (isText ? ' is-text' : '') +'" id="apuid-' + uid + '"><i></i></div>');
		jQuery('body').append(el);
		var offset = jQuery(elm).offset();
		var height = jQuery(elm).outerHeight();
		var width = isText ? 40 : jQuery(elm).outerWidth();
		el.css({
			top: offset.top,
			left: isText ? offset.left + jQuery(elm).outerWidth() - 40 : offset.left,
			height: height,
			width: width
		});

		jQuery(elm).data('loading', '#apuid-' + uid);
		return '#apuid-' + uid;
	},

	hideLoading: function(elm) {
		if( 'all' == elm )
				jQuery('.ap-loading-icon').hide();
		else
				jQuery(jQuery(elm).data('loading')).hide();
	},
	getUrlParam: function(key) {
		var qs = jQuery.apParseParams(window.location.href);
		if(typeof key !== 'undefined')
			return typeof qs[key] !== 'undefined' ? qs[key] : null;

		return qs;
	}
}, Backbone.Events);

_.templateSettings = {
	evaluate:    /<#([\s\S]+?)#>/g,
	interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
	escape:      /\{\{([^\}]+?)\}\}(?!\})/g,
};

(function($){
	//pass in just the context as a $(obj) or a settings JS object
	$.fn.autogrow = function(opts) {
		var that = $(this).css({
			overflow: 'hidden',
			resize: 'none'
		}) //prevent scrollies
		,
		selector = that.selector,
		defaults = {
				context: $(document) //what to wire events to
				,
				animate: true //if you want the size change to animate
				,
				speed: 50 //speed of animation
				,
				fixMinHeight: true //if you don't want the box to shrink below its initial size
				,
				cloneClass: 'autogrowclone' //helper CSS class for clone if you need to add special rules
				,
				onInitialize: false //resizes the textareas when the plugin is initialized
			};
			opts = $.isPlainObject(opts) ? opts : {
				context: opts ? opts : $(document)
			};
			opts = $.extend({}, defaults, opts);
			that.each(function(i, elem) {
				var min, clone;
				elem = $(elem);
			//if the element is "invisible", we get an incorrect height value
			//to get correct value, clone and append to the body.
			if (elem.is(':visible') || parseInt(elem.css('height'), 10) > 0) {
				min = parseInt(elem.css('height'), 10) || elem.innerHeight();
			} else {
				clone = elem.clone().addClass(opts.cloneClass).val(elem.val()).css({
					position: 'absolute',
					visibility: 'hidden',
					display: 'block'
				});
				$('body').append(clone);
				min = clone.innerHeight();
				clone.remove();
			}
			if (opts.fixMinHeight) {
				elem.data('autogrow-start-height', min); //set min height
			}
			elem.css('height', min);
			if (opts.onInitialize && elem.length) {
				resize.call(elem[0]);
			}
		});
			opts.context.on('keyup paste focus', selector, resize);

			function resize(e) {
				var box = $(this),
				oldHeight = box.innerHeight(),
				newHeight = this.scrollHeight,
				minHeight = box.data('autogrow-start-height') || 0,
				clone;
			if (oldHeight < newHeight) { //user is typing
				this.scrollTop = 0; //try to reduce the top of the content hiding for a second
				opts.animate ? box.stop().animate({
					height: newHeight
				}, opts.speed) : box.innerHeight(newHeight);
			} else if (!e || e.which == 8 || e.which == 46 || (e.ctrlKey && e.which == 88)) { //user is deleting, backspacing, or cutting
				if (oldHeight > minHeight) { //shrink!
					//this cloning part is not particularly necessary. however, it helps with animation
					//since the only way to cleanly calculate where to shrink the box to is to incrementally
					//reduce the height of the box until the $.innerHeight() and the scrollHeight differ.
					//doing this on an exact clone to figure out the height first and then applying it to the
					//actual box makes it look cleaner to the user
					clone = box.clone()
					//add clone class for extra css rules
					.addClass(opts.cloneClass)
					//make "invisible", remove height restriction potentially imposed by existing CSS
					.css({
						position: 'absolute',
						zIndex: -10,
						height: ''
					})
					//populate with content for consistent measuring
					.val(box.val());
					box.after(clone); //append as close to the box as possible for best CSS matching for clone
					do { //reduce height until they don't match
					newHeight = clone[0].scrollHeight - 1;
					clone.innerHeight(newHeight);
				} while (newHeight === clone[0].scrollHeight);
					newHeight++; //adding one back eliminates a wiggle on deletion
					clone.remove();
					box.focus(); // Fix issue with Chrome losing focus from the textarea.
					//if user selects all and deletes or holds down delete til beginning
					//user could get here and shrink whole box
					newHeight < minHeight && (newHeight = minHeight);
					oldHeight > newHeight && opts.animate ? box.stop().animate({
						height: newHeight
					}, opts.speed) : box.innerHeight(newHeight);
				} else { //just set to the minHeight
					box.innerHeight(minHeight);
				}
			}
		}
		return that;
	};

	jQuery.fn.apScrollTo = function(elem, toBottom, speed) {
		toBottom = toBottom||false;
		var parentPos = $(this).scrollTop() - $(this).offset().top;
		var top = toBottom ? $(this).offset().top + $(this).height() : $(this).offset().top;
		$('html, body').stop();
		$('html, body').animate({
			scrollTop: top
		}, speed == undefined ? 1000 : speed);

		if(elem != undefined)
			$(this).animate({
				scrollTop: parentPos + $(elem).offset().top
			}, speed == undefined ? 1000 : speed);

		return this;
	};

	AnsPress.views.Snackbar = Backbone.View.extend({
		id: 'ap-snackbar',
		template: '<div class="ap-snackbar<# if(success){ #> success<# } #>">{{message}}</div>',
		hover: false,
		initialize: function(){
			AnsPress.on('snackbar', this.show, this);
		},
		events: {
			'mouseover': 'toggleHover',
			'mouseout': 'toggleHover',
		},
		show: function(data){
			var self = this;
			this.data = data.snackbar;
			this.data.success = data.success;
			this.$el.removeClass('snackbar-show');
			this.render();
			setTimeout(function(){
				self.$el.addClass('snackbar-show');
			}, 0);
			this.hide();
		},
		toggleHover:function(){
			clearTimeout(this.hoveTimeOut);
			this.hover = !this.hover;
			if(!this.hover)
				this.hide();
		},
		hide: function(){
			var self = this;
			if(!self.hover)
				this.hoveTimeOut = setTimeout(function(){
					self.$el.removeClass('snackbar-show');
				}, 2000);
		},
		render: function(){
			if(this.data){
				var t = _.template(this.template);
				this.$el.html(t(this.data));
			}
			return this;
		}
	});

	$.fn.AnsPressTags = function() {
		var suggestion, suggTimeOut;

		function sanitizeTag(str) {
			str = str.replace(/^\s+|\s+$/g, ''); // trim
			str = str.toLowerCase();

			str = str.replace(/\s+/g, '-') // collapse whitespace and replace by -
				.replace(/-+/g, '-'); // collapse dashes
				return str;
		}

		function sanitizeLabel(str) {
			var div = document.createElement('div');
			div.appendChild(document.createTextNode(str));
			return div.innerHTML;
		}

		$(document).mouseup(function(e) {
			if(suggestion)
				suggestion.dontHide = true;

			var container = $('.ap-tags-suggestion');
			if (!container.is(e.target) && container.has(e.target).length === 0) {
				container.remove();
			}
		});

		function renderItem(val, tag, fieldName, typeField, opt){
			if(typeField.parent().find('.ap-tag-item[data-val="'+val+'"]').length > 0 )
				return;

			// Check max tags allowed.
			if(typeField.parent().find('.ap-tag-item').length >= opt.max_tags){
				AnsPress.trigger( 'snackbar', {'success' : false, 'snackbar' : {'message': opt.label_max_tag_added} } );
				return false;
			}

			$newFieldName = fieldName + '['+val+']';
			var html = $('<span class="ap-tag-item" data-val="'+val+'">'+tag+'<input type="hidden" name="'+$newFieldName+'" value="'+tag+'" /></span>');

			if(suggestion)
				suggestion.find('li[data-val="'+val+'"]').remove();

			$(html).insertBefore(typeField);

			AnsPress.trigger('renderTagItem', html, val, tag, fieldName, typeField);
		}

		/**
		 * Filter matching lists in suggestion.
		 * @param {object} typeField
		 */
		function filterMatchingLists(typeField, opt){
			if(!suggestion) return;

			$allListElements = suggestion.find('li');

			var $matchingListElements = $allListElements.not('.disable').filter(function(i, li){
				var listItemText = $(li).text().toUpperCase(), searchText = typeField.val().toUpperCase();
				return ~listItemText.indexOf(searchText);
			});

			$allListElements.each(function(){
				var regex = new RegExp('('+typeField.val()+')', 'ig');
				$(this).html($(this).text().replace(regex, '<b>$1</b>'));
			});

			$notMatched = $.grep($allListElements, function(element) {
				return $.inArray(element, $matchingListElements) === -1;
			});

			$.each($notMatched,function(el){
				$(this).remove();
			});

			if ( suggestion.find('li:not(.disable)').length === 0){
				suggestion.append('<li class="disable">' + opt.label_no_matching + ' ' + ( opt.add_tag ? opt.label_add_new + '<input type="text" placeholder="Type and hit enter" class="new-tag-entry" />' : '') + '</li>');
			}
		}

		/**
		 * Render suggestion dropdown.
		 * @param {object|array} tags
		 * @param {object} fieldName
		 * @param {object} typeField
		 */
		function renderSuggestion(tags, fieldName, typeField, opt){
			var self = this;
			var list = '';
			var is_object = typeof tags === 'object';

			if(!$.isEmptyObject(tags)){
				$.each(tags, function(i, tag){
					var index_val = is_object ? i : tag;
					if(typeField.closest('.ap-tag-wrap').find("span.ap-tag-item[data-val='"+index_val+"']").length == 0)
						list += '<li data-val="'+index_val+'">'+tag+'</li>';
				});
			} else if(opt.add_tag) {
				typeField.allowNewTag = true;
				list += '<li class="disable">'+opt.label_no_tags+' '+opt.label_add_new+'<input type="text" placeholder="Type and hit enter" class="new-tag-entry" /></li>';
			} else {
				list += '<li class="disable">'+opt.label_no_tags+'</li>';
			}

			var html = $('<ul class="ap-tags-suggestion">'+list+'</ul>').css('left', typeField.position().left);
			filterMatchingLists(typeField, opt);

			return html;
		}

		function removeSuggestion(){
			if(suggestion){
				suggestion.remove();
				suggestion = null;
				clearTimeout(suggTimeOut);
			}
		}

		return this.each(function() {
			var self = $(this);

			var opt = self.data('options');
			var fieldName = self.data('name');
			var typeField = $(this).find('#'+self.data('id'));
			var tags = self.find('script').length>0 ? JSON.parse(self.find('script').html()) : {};

			// Remove tag when its clicked.
			self.on('click', '.ap-tag-item', function(){
				$(this).remove();
			});

			//focus type field when clicked on wrapper.
			self.on('click', function(e){
				if(e.target!==this) return;
				typeField.click().focus();
			});

			self.on('keydown', '.new-tag-entry', function(e) {
				if(e.keyCode == 13){ // Prevent submit form on Enter
					e.preventDefault();

					var val = $(this).val().trim();
					renderItem(sanitizeTag(val), sanitizeLabel(val), fieldName, typeField, opt);
					$(this).val('');
					typeField.val('');
				}
			});

			self.on('click', '.ap-tags-suggestion li:not(.disable)', function(e){
				if(e.target!==this) return;

				renderItem($(this).attr('data-val'), $(this).text(), fieldName, typeField, opt);
				$(this).remove();
				typeField.val('');
			});

			typeField.on('keydown', function(e) {
				if(e.keyCode == 13) // Prevent submit form on Enter
					e.preventDefault();

				// Delete on backspace
				if(e.keyCode == 8){
					if(typeField.val() === '' && typeField.prev().is('.ap-tag-item'))
						typeField.prev().remove();
					removeSuggestion();
				}

				if(e.keyCode==40 && !suggestion){
					suggestion = $(renderSuggestion(tags, fieldName, typeField, opt));
					self.append(suggestion);
				}

				if(e.keyCode == 38 || e.keyCode == 40) {
					var inputs = suggestion.find('.ap-tag-item');
					var index = suggestion.find('li.focus').index();
					var items = suggestion.find('li').length-1;
					suggestion.find('li').removeClass('focus');

					if(index == -1)
						index = 0;
					else
						e.keyCode == 40 ? index++ : index--;

					var currentLi = suggestion.find('li').eq(index);

					if(currentLi.length>0){
						currentLi.addClass('focus');
						var pos = currentLi.position();
						$(suggestion).animate({
							scrollTop: pos.top
						}, 0);
					}
				}
			});

			typeField.on('keyup', function(e) {
				if(!suggestion){
					suggestion = $(renderSuggestion(tags, fieldName, typeField, opt));
					self.append(suggestion);
				}

				var focused = suggestion.find('li.focus');

				if(focused.length>0){
					if(e.keyCode == 13 || e.keyCode == 188){
						var val = $(this).val().replace(/,\s*$/, '');
						renderItem(focused.attr('data-val'), focused.text(), fieldName, typeField, opt);
						$(this).val('');
						removeSuggestion();
					}
				}

				if(e.keyCode != 38 && e.keyCode != 40)
					filterMatchingLists(typeField, opt);

			});

			typeField.on('click', function(e){
				removeSuggestion();
				suggestion = $(renderSuggestion(tags, fieldName, typeField, opt));
				self.append(suggestion);
			});

			typeField.on('blur', function(e){
				if(suggestion && suggestion.find('li input').is('.new-tag-entry'))
					return;

				clearTimeout(suggTimeOut);
				suggTimeOut = setTimeout(function(){
					removeSuggestion();
				}, 200);
			});
		});
	};

	var re = /([^&=]+)=?([^&]*)/g;
	var decode = function (str) {
			return decodeURIComponent(str.replace(/\+/g, ' '));
	};
	$.apParseParams = function (query) {
		// recursive function to construct the result object
		function createElement(params, key, value) {
			key = key + '';
			// if the key is a property
			if (key.indexOf('.') !== -1) {
				// extract the first part with the name of the object
				var list = key.split('.');
				// the rest of the key
				var new_key = key.split(/\.(.+)?/)[1];
				// create the object if it doesnt exist
				if (!params[list[0]]) params[list[0]] = {};
				// if the key is not empty, create it in the object
				if (new_key !== '') {
						createElement(params[list[0]], new_key, value);
				} else console.warn('parseParams :: empty property in key "' + key + '"');
			} else
			// if the key is an array
			if (key.indexOf('[') !== -1) {
				// extract the array name
				var list = key.split('[');
				key = list[0];
				// extract the index of the array
				var list = list[1].split(']');
				var index = list[0]
				// if index is empty, just push the value at the end of the array
				if (index == '') {
					if (!params) params = {};
					if (!params[key] || !$.isArray(params[key])) params[key] = [];
					params[key].push(value);
				} else
				// add the value at the index (must be an integer)
				{
					if (!params) params = {};
					if (!params[key] || !$.isArray(params[key])) params[key] = [];
					params[key][parseInt(index)] = value;
				}
			} else
			// just normal key
			{
					if (!params) params = {};
					params[key] = value;
			}
		}
		// be sure the query is a string
		query = query + '';
		if (query === '') query = window.location + '';
		var params = {}, e;
		if (query) {
			// remove # from end of query
			if (query.indexOf('#') !== -1) {
					query = query.substr(0, query.indexOf('#'));
			}

			// remove ? at the begining of the query
			if (query.indexOf('?') !== -1) {
					query = query.substr(query.indexOf('?') + 1, query.length);
			} else return {};
			// empty parameters
			if (query == '') return {};
			// execute a createElement on every key and value
			while (e = re.exec(query)) {
				var key = decode(e[1]);
				var value = decode(e[2]);
				createElement(params, key, value);
			}
		}
		return params;
	};

	var apSnackbarView = new AnsPress.views.Snackbar();
	$('body').append(apSnackbarView.render().$el);

})(jQuery);

jQuery(document).ready(function($){
	$( document ).click(function (e) {
		e.stopPropagation();
		if (!$(e.target).is('.ap-dropdown-toggle') && !$(e.target).closest('.open').is('.open') && !$(e.target).closest('form').is('form')) {
				$('.ap-dropdown').removeClass('open');
		}
	});

	// Dropdown toggle
	$('body').on('click', '.ap-dropdown-toggle, .ap-dropdown-menu > a', function(e){
		e.preventDefault();
		$('.ap-dropdown').not($(this).closest('.ap-dropdown')).removeClass('open');
		$(this).closest('.ap-dropdown').toggleClass('open');
	});

	// Subscribe button.
	$('[ap-subscribe]').click(function(e){
		e.preventDefault();
		var self = $(this);
		var query = JSON.parse(self.attr('ap-query'));
		query.ap_ajax_action = 'subscribe';

		AnsPress.ajax({
			data: query,
			success: function(data){
				if(data.count) self.next().text(data.count);
				if(data.label) self.text(data.label);
			}
		})
	});

	$('body').on('click', '.ap-droptogg', function(e){
		e.preventDefault();
		$(this).closest('.ap-dropdown').removeClass('open');
		$(this).closest('#noti-dp').hide();
	});

	// Ajax button.
	$('body').on('click', '[ap-ajax-btn]', function(e){
		var self = this;
		e.preventDefault();
		if($(this).is('.loaded'))
			return;
		var self = $(this);
		var query = JSON.parse(self.attr('ap-query'));
		AnsPress.showLoading(self);
		AnsPress.ajax({
			data: query,
			success: function(data){
				$(self).addClass('loaded');
				AnsPress.hideLoading(e.target);
				if(typeof data.btn !== 'undefined')
					if(data.btn.hide) self.hide();

				if(typeof data.cb !== 'undefined')
					AnsPress.trigger(data.cb, data);
			}
		})
	});

	$('[data-role="ap-tags"]').AnsPressTags();

	function apAddRepeatField(el, values){
		values = values||false;
		var args = $(el).data('args');
		args['index'] = $(el).find('[data-repeat-id]').length;
		var template = $('#'+args.key+'-template').text();

		var t = _.template(template);
		t = t(args);
		var regex = /(class|id|for)="([^"]+)"/g;

		var t = t.replace(regex, function(match, group) {
			return match.replace(/[[\]]/g, '');
		});

		var html = $('<div class="ap-repeatable-item" data-repeat-id="'+args.index+'">'+ t +'<a href="#" class="ap-repeatable-delete">'+args.label_delete+'</a></div>');
		$.each(values, function(childName, v){
			html.find('[name="'+args.key+'['+args.index+']['+childName+']"]').val(v);
		});

		var errors = $('#'+args.key+'-errors');

		if ( errors.length > 0 ) {
			var errors_json = JSON.parse(errors.html());
			$.each(errors_json, function(i, err){
				$.each(err, function(field, messages){
					var fieldWrap = html.find('[name="'+args.key+'['+i+']['+field+']"]').closest('.ap-form-group');
					fieldWrap.addClass('ap-have-errors');
					var errContain = $('<div class="ap-field-errors"></div>');
					$.each(messages, function(code, msg){
						errContain.append('<span class="ap-field-error code-'+code+'">'+msg+'</span>');
					})
					$(errContain).insertAfter(fieldWrap.find('label'));
				});
			});
		}

		$(el).find('.ap-fieldrepeatable-item').append(html);
	}

	$('[data-role="ap-repeatable"]').each(function(){
		var self = this;


		$(this).find('.ap-repeatable-add').on('click', function(e){
			e.preventDefault();

			var self = $(this);
			var query = JSON.parse(self.attr('ap-query'));
			AnsPress.showLoading(self);

			$count = $('[name="'+query.id+'-groups"]');
			query.current_groups = $count.val();
			$count.val(parseInt(query.current_groups)+1);

			$nonce = $('[name="'+query.id+'-nonce"]');
			query.current_nonce = $nonce.val();

			AnsPress.ajax({
				data: query,
				success: function(data){
					AnsPress.hideLoading(e.target);
					$(data.html).insertBefore(self);
					$nonce.val(data.nonce);
				}
			})
		});

		$(this).on('click', '.ap-repeatable-delete', function(e){
			e.preventDefault();
			$(this).closest('.ap-form-group').remove();
		});

	});

	$('body').on('click', '.ap-form-group', function(){
		$(this).removeClass('ap-have-errors');
	});

	$('[apform]').each(function(){
		var self = $(this);

		$(self).ajaxForm({
			url: ajaxurl,
			beforeSerialize: function() {
				if(typeof tinymce !== undefined)
					tinymce.triggerSave();

				$('.ap-form-errors, .ap-field-errors').remove();
				$('.ap-have-errors').removeClass('ap-have-errors');
			},
			success: function(data) {
				data = AnsPress.ajaxResponse(data);
				if(data.snackbar){
					AnsPress.trigger('snackbar', data)
				}

				if(typeof data.form_errors !== 'undefined'){
					$formError = $('<div class="ap-form-errors"></div>').prependTo(self);

					$.each(data.form_errors, function(i, err){
						$formError.append('<span class="ap-form-error ecode-'+i+'">'+err+'</div>');
					});

					$.each(data.fields_errors, function(i, errs){
						$('.ap-field-'+i).addClass('ap-have-errors');
						$('.ap-field-'+i).find('.ap-field-errorsc').html('<div class="ap-field-errors"></div>');

						$.each(errs.error, function(code, err){
							$('.ap-field-' + i).find('.ap-field-errors').append('<span class="ap-field-error ecode-'+code+'">'+err+'</span>');
						});
					});

					self.apScrollTo();
				}

				if(typeof data.redirect !== 'undefined'){
					window.location = data.redirect;
				}
			}
		});
	});

});

window.AnsPress.Helper = {
	toggleNextClass: function(el){
		jQuery(el).closest('.ap-field-type-group').find('.ap-fieldgroup-c').toggleClass('show');
	}
}
