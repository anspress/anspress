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
			url: ajaxurl,
			method: 'POST',
		});

		// COnvert data to query string if object.
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

	jQuery.fn.apScrollTo = function(elem, speed) {
		var parentPos = $(this).scrollTop() - $(this).offset().top;
		$('html, body').animate({
			scrollTop: $(this).offset().top
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
})
