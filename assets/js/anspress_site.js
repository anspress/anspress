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
			var parsedData = self.ajaxResponse(data);
			if(parsedData.snackbar){
				AnsPress.trigger('snackbar', parsedData)
			}
			if(typeof success === 'function'){
				data = jQuery.isEmptyObject(parsedData) ? data : parsedData;
				var context = options.context||null;
				success(data, context);
			}
		};

		return jQuery.ajax(options);
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
			user: {
				ID: '',
				avatar: '',
				displayMeta: '',
				profileLink: false
			},
			activity: '',
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
			],
			comments: {},
			dateTime: '',
			postedOn: '',
			voteButton: ''
		}
	});

	AnsPress.views.Vote = Backbone.View.extend({
		idAttribute: 'ID',
	});

	AnsPress.views.Post = Backbone.View.extend({
		idAttribute: 'ID',
		templateId: 'answer',
		tagName: 'div',
		id: function(){
			return 'post-' + this.model.get('ID');
		},
		initialize: function(options){
			this.model.on('change', this.render, this);
			AnsPress.on('templateLoaded-'+this.templateId, this.templateLoaded, this);

			// Fetch template from url.
			AnsPress.loadTemplate(this.templateId);
		},
		templateLoaded: function(html){
			this.template = html;
			this.render();
		},
		events: {
			'click [ap-btnvote]': 'voteClicked'
		},
		voteClicked: function(e){
			e.preventDefault();
			if($(e.target).is('.disable'))
				return;

			self = this;
			var type = $(e.target).data('type');
			var vote = _.clone(self.model.get('vote'));

			if(type === 'vote_up')
				vote.net = ( vote.active === 'vote_up' ? vote.net - 1 : vote.net + 1);
			else
				vote.net = ( vote.active === 'vote_down' ? vote.net + 1 : vote.net - 1);

			self.model.set('vote', vote);
			var q = $(e.target).attr('ap-btnvote');
			AnsPress.ajax({
				url: ajaxurl,
				method: 'POST',
				data: 'ap_ajax_action=vote&' + q,
				success: function(data) {
					if (_.isObject(data.voteData))
						self.model.set('vote', data.voteData);
				}
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
				klass += 'disable';

			return klass;
		},
		render: function(){
			if(this.template){
				var t = _.template(this.template);
				this.$el.attr('class', this.model.get('class'));
				var obj = _.extend(this.model.toJSON(), {
					voteUpClass: this.voteClass('vote_up'),
					voteDownClass: this.voteClass('vote_down')
				});
				this.$el.html(t(obj));
			}
			return this;
		}
	});

	AnsPress.collections.Posts = Backbone.Collection.extend({
		model: AnsPress.models.Post,
		url: ajaxurl + '?action=fetch_answers&question_id='+apQuestionID,
	});

	AnsPress.views.Posts = Backbone.View.extend({
		initialize: function(){
			this.model.on('add', this.renderItem, this);
		},
		renderItem: function(post){
			var view = new AnsPress.views.Post({ model: post });
			this.$el.append(view.render().$el);
		},

		render: function(){
			var self = this;
			this.model.each(function(post){
				self.renderItem(post);
			});

			return self;
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
apposts.fetch();

var appostview = new AnsPress.views.Posts({ model: apposts });
jQuery('#answers').html(appostview.render().$el);

var apSnackbarView = new AnsPress.views.Snackbar();
jQuery('body').append(apSnackbarView.render().$el);
