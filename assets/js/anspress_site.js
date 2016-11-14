/**
 * Javascript code for AnsPress fontend
 * @since 2.0
 * @package AnsPress
 * @author Rahul Aryan
 * @license GPL 2+
 */

// For preventing global namespace pollution, keep everything in AnsPress object.
window.AnsPress = _.extend({
	models: {},
	views: {},
	collections: {},
	loadTemplate: function(id, cb){
		var self = this;

		if(jQuery('#apTemplate-'+id).length === 0){
			jQuery('<script id="apTemplate-'+id+'" type="text/html"></script>').appendTo('body');
			jQuery.get(apTemplateUrl + '/' + id + ".html", function(html){
				jQuery('#apTemplate-'+id).html(html);
				self.trigger('templateLoaded-'+id, html);
				if(cb) cb(html);
			});
		}else{
			self.trigger('templateLoaded-'+id, jQuery('#apTemplate-'+id).html());
		}
	},
	isJSONString: function(str) {
		try {
			return JSON.parse(str);
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
		var uid = this.uniqueId();
		var el = jQuery('<div class="ap-loading-icon ap-uid '+customClass+'" id="apuid-' + uid + '"></div>');
		jQuery('body').append(el);
		var offset = jQuery(elm).offset();
		var height = jQuery(elm).outerHeight();
		var width = jQuery(elm).outerWidth();

		el.css({
			top: offset.top,
			left: offset.left,
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

(function($) {
	AnsPress.models.Post = Backbone.Model.extend({
		idAttribute: 'ID',
		defaults:{
			actions: [
				{ id: 'comment', label: 'Comment' },
				{ id: 'select', label: 'Select' },
				{ id: 'edit', label: 'Edit' },
				{
					id: 'status',
					label: 'Status',
					sub:[
						{ id: 'publish', label: 'Publish' },
						{ id: 'trash', label: 'Trash' },
						{ id: 'private', label: 'Private' },
						{ id: 'moderate', label: 'Moderate' }
					]
				}
			]
		}
	});

	AnsPress.views.Post = Backbone.View.extend({
		idAttribute: 'ID',
		templateId: 'answer',
		tagName: 'div',
		id: function(){
			return 'post-' + this.model.get('ID');
		},
		initialize: function(options){
			this.model.on('change:vote', this.voteUpdate, this);
		},
		events: {
			'click [ap-vote] > a': 'voteClicked'
		},
		voteClicked: function(e){
			e.preventDefault();
			if($(e.target).is('.disable'))
				return;

			self = this;
			var type = $(e.target).is('.vote-up') ? 'vote_up' : 'vote_down';
			var vote = _.clone(self.model.get('vote'));

			if(type === 'vote_up')
				vote.net = ( vote.active === 'vote_up' ? vote.net - 1 : vote.net + 1);
			else
				vote.net = ( vote.active === 'vote_down' ? vote.net + 1 : vote.net - 1);

			self.model.set('vote', vote);
			var q = $.parseJSON($(e.target).parent().attr('ap-vote'));
			q.ap_ajax_action = 'vote';
			q.type = type;

			AnsPress.ajax({
				data: q,
				success: function(data) {
					if (_.isObject(data.voteData))
						self.model.set('vote', data.voteData);
				}
			})
		},
		voteUpdate: function(post){
			var self = this;
			this.$el.find('[ap="votes_net"]').text(this.model.get('vote').net);
			_.each(['up', 'down'], function(e){
				self.$el.find('.vote-'+e).removeClass('voted disable').addClass(self.voteClass('vote_'+e));
			})

		},
		voteClass: function(type){
			type = type||'vote_up';
			var curr = this.model.get('vote').active;
			var klass = '';
			if(curr === 'vote_up' && type === 'vote_up')
				klass = 'active';

			if(curr === 'vote_down' && type === 'vote_down')
				klass = 'active';

			if(type !== curr && curr !== '')
				klass += ' disable';

			return klass + ' prist';
		},
		render: function(){
			var attr = this.$el.find('[ap-vote]').attr('ap-vote');
			this.model.set('vote', $.parseJSON(attr), {silent: true});
			return this;
		}
	});

	AnsPress.collections.Posts = Backbone.Collection.extend({
		model: AnsPress.models.Post,
		//url: ajaxurl + '?action=fetch_answers&question_id='+apQuestionID,
		initialize: function(){
			var loadedPosts = [];
			$('[ap="question"],[ap="answer"]').each(function(e){
				loadedPosts.push({ 'ID' : $(this).attr('ap-id')});
			});
			this.add(loadedPosts);
		}
	});

	AnsPress.views.SingleQuestion = Backbone.View.extend({
		initialize: function(){
			this.model.on('add', this.renderItem, this);
		},
		events: {
			'click [ap="loadEditor"]': 'loadEditor',
			'submit [ap="answerForm"]': 'answerForm'
		},
		renderItem: function(post){
			var view = new AnsPress.views.Post({ model: post, el: '#post-'+post.get('ID') });
			view.render();
		},

		render: function(){
			var self = this;
			this.model.each(function(post){
				self.renderItem(post);
			});

			return self;
		},

		loadEditor: function(e){
			var self = this;
			AnsPress.showLoading(e.target);
			AnsPress.ajax({
				data: $(e.target).attr('ap-query'),
				success: function(data){
					AnsPress.hideLoading(e.target);
					$('.ap-field-description').html(data);
					$(e.target).closest('.ap-minimal-editor').removeClass('ap-minimal-editor');
				}
			});
		},

		answerForm: function(e){
			var self = this;
			AnsPress.showLoading($(e.target).find('.ap-btn-submit'));
			$(e.target).find('.have-error').removeClass('have-error');
			$(e.target).find('.error').remove();

			AnsPress.ajax({
				data: $(e.target).serialize(),
				success: function(data){
					AnsPress.hideLoading($(e.target).find('.ap-btn-submit'));
					if(data.success){
						if(typeof data.allow_upload !== 'undefined'){

						}

						$('#description').val('');
						if (typeof tinyMCE !== 'undefined' && data.success)
							tinyMCE.activeEditor.setContent('');

						$('#answers').append($(data['html']).hide());
						$(data.div_id).slideDown(500);
						self.model.add({'ID': data.ID});
					}

					if(data.errors){
						_.each(data.errors, function(err, i){
							$('.ap-field-'+i).addClass('have-error')
							if(i==='description' && $('.ap-field-ap_upload').length > 0)
								i = 'ap_upload';

							$('.ap-field-'+i).append('<span class="error">'+err+'</span>');
						});
					}
				}
			});
			return false;
		}

	});

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
})(jQuery);

var apposts = new AnsPress.collections.Posts();

var singleQuestionView = new AnsPress.views.SingleQuestion({ model: apposts, el: '#ap-single' });
singleQuestionView.render();

var apSnackbarView = new AnsPress.views.Snackbar();
jQuery('body').append(apSnackbarView.render().$el);






