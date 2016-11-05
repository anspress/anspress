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
		}
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
			actions: '',
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
			return 'answer_' + this.model.get('ID');
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
			$.ajax({
				url: ajaxurl,
				method: 'POST',
				data: 'action=ap_ajax&ap_ajax_action=vote&' + q,
				success: function(data) {
					data = apParseAjaxResponse(data);
					if (data['message_type'] == 'success') {
						self.model.set('vote', data.voteData);
					}
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
			this.el.append(view.render().el);
		},

		render: function(){
			var self = this;
			this.model.each(function(post){
				self.renderItem(post);
			});

			return self;
		}
	});
})(jQuery);

var apposts = new AnsPress.collections.Posts();
apposts.fetch();

var appostview = new AnsPress.views.Posts({ model: apposts });
jQuery('#answers').html(appostview.render().$el);
