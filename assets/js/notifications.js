/**
 * Javascript code for AnsPress notifications dropdown.
 *
 * @since 4.0.0
 * @package AnsPress
 * @author Rahul Aryan
 * @license GPL 3+
 */

(function($) {
	AnsPress.loadTemplate('notifications');

	AnsPress.models.Notification = Backbone.Model.extend({
		idAttribute: 'ID',
		defaults:{
			ID: '',
			userID: '',
			avatar: '',
			content: '',
			actions: ''
		}
	});

	AnsPress.collections.Notifications = Backbone.Collection.extend({
		model: AnsPress.models.Notification
	});

	AnsPress.views.Notification = Backbone.View.extend({
		id: function(){
			return 'noti-' + this.model.id;
		},
		/*className: function(){
			var klass = this.model.get('class');
			if(this.model.get('approved')==='0')
				klass += ' unapproved';
			return klass
		},*/
		template: AnsPress.getTemplate('notification'),
		initialize: function(options){
			this.model = options.model;
			/*this.postID = options.postID;
			this.model.on('change', this.render, this);*/
		},
		events: {
			/*'click [ap="comment_action"]': 'actions',
			'click [ap="edit_comment"]': 'editCommentForm'*/
		},
		render: function(){
			var t = _.template(this.template());
			this.$el.html(t(this.model.toJSON()));
			//this.$el.attr('class', this.className());
			return this;
		}
	});

	AnsPress.views.Notifications = Backbone.View.extend({
		template: AnsPress.getTemplate('notifications'),
		initialize: function(options){
			this.model = options.model;
			/*this.postID = options.postID;
			AnsPress.on('removeComment', this.removeComment, this);
			this.listenTo(this.model, 'remove', this.commentRemoved);
			this.listenTo(this.model, 'add', this.newComment);*/
		},
		renderItem: function(notification){
			this.$el.parent().addClass('have-comments');
			var view = new AnsPress.views.Notification({ model: notification });
			this.$el.find('.scroll-wrap').append(view.render().$el);
			return view;
		},
		render: function(){
			var self = this;
			var t = _.template(this.template());
			this.$el.html(t());
			if(this.model.length > 0){
				this.model.each(function(notification){
					self.renderItem(notification);
				});
			}

			return this;
		},
		removeComment: function(notification){
			this.model.remove(notification);
		},
		commentRemoved: function(comment){
			if(this.model.size() === 0)
				this.$el.parent().removeClass('have-comments');
			$('#comment-'+comment.id).slideUp(400, function(){
				$(this).remove();
			});
		},
		newComment: function(comment){
			var view = this.renderItem(comment);
			view.$el.hide().slideDown(400);
			this.$el.apScrollTo('#comment-'+comment.id);
		}
	});

	var NotiRouter = Backbone.Router.extend({
		routes: {
			'apNotifications': 'notiRoute'
		},
		dpPos: function(html, anchor){
			var pos = anchor.offset();
			pos.top = pos.top + anchor.height();
			pos.left = pos.left - html.width() + anchor.width()
			html.css(pos);
		},
		notiRoute: function (query, page) {
			var self = this;
			AnsPress.ajax({
				data: ajaxurl + '?action=ap_ajax&ap_ajax_action=get_notifications',
				success: function(data){
					var anchor = $('.anspress-menu-notifications');
					var dp = $('#noti-dp');
					if(data.success){
						if(dp.length == 0) {
							var html = $('<div id="noti-dp"></div>');
							$('body').append(html);
							self.dpPos(html, anchor);
						}

						var notiModel = new AnsPress.collections.Notifications(data.notifications);
						var notificationsView = new AnsPress.views.Notifications({model: notiModel});
						$('#noti-dp').html(notificationsView.render().$el);
					}
				}
			});
		}
  });

	$(document).ready(function(){
		var notiRouter = new NotiRouter();
		Backbone.history.start();
	});
})(jQuery);
