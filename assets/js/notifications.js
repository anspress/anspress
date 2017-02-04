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
			'seen': '',
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
			//this.model.on('change', this.render, this);
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
			this.$el.parent().addClass('have-comments');
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
						} else {
							dp.show();
						}

						var notiModel = new AnsPress.collections.Notifications(data.notifications);
						var notificationsView = new AnsPress.views.Notifications({ model: notiModel, mark_args: data.mark_args, total: data.total });
						$('#noti-dp').html(notificationsView.render().$el);
					}
				}
			});
		}
  });

	$(document).ready(function(){
		var notiRouter = new NotiRouter();

		if(!Backbone.History.started)
			Backbone.history.start();

		$('.anspress-menu-notifications a').click(function(){
			if($(this).attr('href') === '#'){
				$(this).attr('href' , '#apNotifications');
				$('#noti-dp').hide();
			}else{
				$(this).attr('href', '#');
			}
		})

		$(document).mouseup(function (e){
			var container = $('#noti-dp');
			if (!container.is(e.target) && container.has(e.target).length === 0){
				container.hide();
				Backbone.history.navigate('', {trigger: true});
			}
		});
	});

})(jQuery);
