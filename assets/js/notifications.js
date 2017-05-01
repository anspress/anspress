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
			'ID': '',
			'verb': '',
			'verb_label': '',
			'icon': '',
			'avatar': '',
			'hide_actor': '',
			'actor': '',
			'ref_title': '',
			'ref_type': '',
			'points': '',
			'date': '',
			'permalink': '',
			'seen': ''
		}
	});

	AnsPress.collections.Notifications = Backbone.Collection.extend({
		model: AnsPress.models.Notification
	});

	AnsPress.views.Notification = Backbone.View.extend({
		id: function(){
			return 'noti-' + this.model.id;
		},
		template: AnsPress.getTemplate('notification'),
		initialize: function(options){
			this.model = options.model;
		},
		render: function(){
			var t = _.template(this.template());
			this.$el.html(t(this.model.toJSON()));
			return this;
		}
	});

	AnsPress.views.Notifications = Backbone.View.extend({
		template: AnsPress.getTemplate('notifications'),
		initialize: function(options){
			this.model = options.model;
			this.mark_args = options.mark_args;
			this.total = options.total;

			this.listenTo(this.model, 'add', this.newNoti);
			this.listenTo(AnsPress, 'notificationAllRead', this.allRead);
		},
		renderItem: function(notification){
			var view = new AnsPress.views.Notification({ model: notification });
			this.$el.find('.scroll-wrap').append(view.render().$el);
			return view;
		},
		render: function(){
			var self = this;
			var t = _.template(this.template());
			this.$el.html(t({'mark_args' : this.mark_args, 'total': this.total}));
			if(this.model.length > 0){
				this.model.each(function(notification){
					self.renderItem(notification);
				});
			}

			return this;
		},
		newNoti: function(noti){
			this.renderItem(noti);
		},
		allRead: function(){
			this.total = 0;
			this.model.each(function(notification){
				notification.set('seen', 1);
			});
			this.render();
		}
	});

	AnsPress.views.NotiDropdown = Backbone.View.extend({
		id: 'noti-dp',
		initialize: function(options){
			//this.model = options.model;
			this.anchor = options.anchor;
			this.fetched = false;
		},
		dpPos: function(){
			var pos = this.anchor.offset();
			pos.top = pos.top + this.anchor.height();
			pos.left = pos.left - this.$el.width() + this.anchor.width()
			this.$el.css(pos);
		},
		fetchNoti: function (query, page) {
			if( this.fetched ){
				this.dpPos();
				return;
			}

			var self = this;
			AnsPress.ajax({
				data: ajaxurl + '?action=ap_ajax&ap_ajax_action=get_notifications',
				success: function(data){
					self.fetched = true;
					if(data.success){
						var notiModel = new AnsPress.collections.Notifications(data.notifications);
						var notificationsView = new AnsPress.views.Notifications({ model: notiModel, mark_args: data.mark_args, total: data.total });
						self.$el.html(notificationsView.render().$el);
						self.dpPos();
						self.$el.show();
					}
				}
			});
		},
		render: function(){
			this.$el.hide();
			return this;
		}
  });

	$(document).ready(function(){
		var anchor = $('a[href="#apNotifications"]');
		var dpView = new AnsPress.views.NotiDropdown({anchor: anchor});
		$('body').append(dpView.render().$el);

		anchor.click(function(e){
			e.preventDefault();
			dpView.fetchNoti();
			if(dpView.fetched)
				dpView.$el.toggle();
		});

		$(document).mouseup(function (e){
			if (!anchor.is(e.target) && !dpView.$el.is(e.target) && dpView.$el.has(e.target).length === 0){
				dpView.$el.hide();
			}
		});
	});

})(jQuery);
