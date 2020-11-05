/**
 * Javascript code for AnsPress fontend
 * @since 4.0.0
 * @package AnsPress
 * @author Rahul Aryan
 * @license GPL 3+
 */

(function ($) {
	AnsPress.models.Action = Backbone.Model.extend({
		defaults: {
			cb: '',
			post_id: '',
			title: '',
			label: '',
			query: '',
			active: false,
			header: false,
			href: '#',
			count: '',
			prefix: '',
			checkbox: '',
			multiple: false
		}
	});

	AnsPress.collections.Actions = Backbone.Collection.extend({
		model: AnsPress.models.Action
	});

	AnsPress.views.Action = Backbone.View.extend({
		id: function () {
			return this.postID;
		},
		className: function () {
			var klass = '';
			if (this.model.get('header')) klass += ' ap-dropdown-header';
			if (this.model.get('active')) klass += ' active';
			return klass;
		},
		tagName: 'li',
		template: "<# if(!header){ #><a href=\"{{href}}\" title=\"{{title}}\">{{{prefix}}}{{label}}<# if(count){ #><b>{{count}}</b><# } #></a><# } else { #>{{label}}<# } #>",
		initialize: function (options) {
			this.model = options.model;
			this.postID = options.postID;
			this.model.on('change', this.render, this);
			this.listenTo(this.model, 'remove', this.removed);
		},
		events: {
			'click a': 'triggerAction'
		},
		render: function () {
			var t = _.template(this.template);
			this.$el.html(t(this.model.toJSON()));
			this.$el.attr('class', this.className());
			return this;
		},
		triggerAction: function (e) {
			var q = this.model.get('query');
			if (_.isEmpty(q))
				return;

			e.preventDefault();
			var self = this;
			AnsPress.showLoading(e.target);
			var cb = this.model.get('cb');
			q.ap_ajax_action = 'action_' + cb;

			AnsPress.ajax({
				data: q,
				success: function (data) {
					AnsPress.hideLoading(e.target);
					if (data.redirect) window.location = data.redirect;

					if (data.success && (cb == 'status' || cb == 'toggle_delete_post'))
						AnsPress.trigger('changedPostStatus', { postID: self.postID, data: data, action: self.model });

					if (data.action) {
						self.model.set(data.action);
					}
					self.renderPostMessage(data);
					if (data.deletePost) AnsPress.trigger('deletePost', data.deletePost);
					if (data.answersCount) AnsPress.trigger('answerCountUpdated', data.answersCount);
				}
			});
		},
		renderPostMessage: function (data) {
			if (!_.isEmpty(data.postmessage))
				$('[apid="' + this.postID + '"]').find('postmessage').html(data.postmessage);
			else
				$('[apid="' + this.postID + '"]').find('postmessage').html('');
		},
		removed: function () {
			this.remove();
		}
	});


	AnsPress.views.Actions = Backbone.View.extend({
		id: function () {
			return this.postID;
		},
		searchTemplate: '<div class="ap-filter-search"><input type="text" search-filter placeholder="' + aplang.search + '" /></div>',
		tagName: 'ul',
		className: 'ap-actions',
		events: {
			'keyup [search-filter]': 'searchInput'
		},
		initialize: function (options) {
			this.model = options.model;
			this.postID = options.postID;
			this.multiple = options.multiple;
			this.action = options.action;
			this.nonce = options.nonce;

			AnsPress.on('changedPostStatus', this.postStatusChanged, this);
			this.listenTo(this.model, 'add', this.added);
		},
		renderItem: function (action) {
			var view = new AnsPress.views.Action({ model: action, postID: this.postID });
			this.$el.append(view.render().$el);
		},
		render: function () {
			var self = this;
			if (this.multiple)
				this.$el.append(this.searchTemplate);

			this.model.each(function (action) {
				self.renderItem(action);
			});

			return this;
		},
		postStatusChanged: function (args) {
			if (args.postID !== this.postID) return;

			// Remove post status class
			$("#post-" + this.postID).removeClass(function () {
				return this.className.split(' ').filter(function (className) { return className.match(/status-/) }).join(' ');
			});

			$("#post-" + this.postID).addClass('status-' + args.data.newStatus);
			var activeStatus = this.model.where({ cb: 'status', active: true });

			activeStatus.forEach(function (status) {
				status.set({ active: false });
			});
		},
		searchInput: function (e) {
			var self = this;

			clearTimeout(this.searchTO);
			this.searchTO = setTimeout(function () {
				self.search($(e.target).val(), e.target);
			}, 600);
		},
		search: function (q, e) {
			var self = this;

			var args = { nonce: this.nonce, ap_ajax_action: this.action, search: q, filter: this.filter, post_id: this.postID };

			AnsPress.showLoading(e);
			AnsPress.ajax({
				data: args,
				success: function (data) {
					console.log(data);
					AnsPress.hideLoading(e);
					if (data.success) {
						self.nonce = data.nonce;
						//self.model.reset();
						while (m = self.model.first()) {
							self.model.remove(m);
						}
						self.model.add(data.actions);
					}
				}
			});
		},
		added: function (model) {
			this.renderItem(model);
		}
	});

	AnsPress.models.Post = Backbone.Model.extend({
		idAttribute: 'ID',
		defaults: {
			actionsLoaded: false,
			hideSelect: ''
		}
	});

	AnsPress.views.Post = Backbone.View.extend({
		idAttribute: 'ID',
		templateId: 'answer',
		tagName: 'div',
		actions: { view: {}, model: {} },
		id: function () {
			return 'post-' + this.model.get('ID');
		},
		initialize: function (options) {
			this.listenTo(this.model, 'change:vote', this.voteUpdate);
			this.listenTo(this.model, 'change:hideSelect', this.selectToggle);
		},
		events: {
			'click [ap-vote] > a': 'voteClicked',
			'click [ap="actiontoggle"]:not(.loaded)': 'postActions',
			'click [ap="select_answer"]': 'selectAnswer'
		},
		voteClicked: function (e) {
			e.preventDefault();
			if ($(e.target).is('.disable'))
				return;

			self = this;
			var type = $(e.target).is('.vote-up') ? 'vote_up' : 'vote_down';
			var originalValue = _.clone(self.model.get('vote'));
			var vote = _.clone(originalValue);

			if (type === 'vote_up')
				vote.net = (vote.active === 'vote_up' ? vote.net - 1 : vote.net + 1);
			else
				vote.net = (vote.active === 'vote_down' ? vote.net + 1 : vote.net - 1);

			self.model.set('vote', vote);
			var q = $.parseJSON($(e.target).parent().attr('ap-vote'));
			q.ap_ajax_action = 'vote';
			q.type = type;

			AnsPress.ajax({
				data: q,
				success: function (data) {
					if (data.success && _.isObject(data.voteData))
						self.model.set('vote', data.voteData);
					else
						self.model.set('vote', originalValue); // Restore original value on fail
				}
			})
		},
		voteUpdate: function (post) {
			var self = this;
			this.$el.find('[ap="votes_net"]').text(this.model.get('vote').net);
			_.each(['up', 'down'], function (e) {
				self.$el.find('.vote-' + e).removeClass('voted disable').addClass(self.voteClass('vote_' + e));
			});
		},
		voteClass: function (type) {
			type = type || 'vote_up';
			var curr = this.model.get('vote').active;
			var klass = '';
			if (curr === 'vote_up' && type === 'vote_up')
				klass = 'active';

			if (curr === 'vote_down' && type === 'vote_down')
				klass = 'active';

			if (type !== curr && curr !== '')
				klass += ' disable';

			return klass + ' prist';
		},
		render: function () {
			var attr = this.$el.find('[ap-vote]').attr('ap-vote');
			try {
				this.model.set('vote', $.parseJSON(attr), { silent: true });
			} catch (err) {
				console.warn('Vote data empty', err)
			}
			return this;
		},
		postActions: function (e) {
			var self = this;
			var q = $.parseJSON($(e.target).attr('apquery'));
			if (typeof q.ap_ajax_action === 'undefined')
				q.ap_ajax_action = 'post_actions';

			AnsPress.ajax({
				data: q,
				success: function (data) {
					AnsPress.hideLoading(e.target);
					$(e.target).addClass('loaded');
					self.actions.model = new AnsPress.collections.Actions(data.actions);
					self.actions.view = new AnsPress.views.Actions({ model: self.actions.model, postID: self.model.get('ID') });
					self.$el.find('postActions .ap-actions').html(self.actions.view.render().$el);
				}
			});
		},

		selectAnswer: function (e) {
			e.preventDefault();
			var self = this;
			var q = $.parseJSON($(e.target).attr('apquery'));
			q.action = 'ap_toggle_best_answer';

			AnsPress.showLoading(e.target);
			AnsPress.ajax({
				data: q,
				success: function (data) {
					AnsPress.hideLoading(e.target);
					if (data.success) {
						if (data.selected) {
							self.$el.addClass('best-answer');
							$(e.target).addClass('active').text(data.label);
							AnsPress.trigger('answerToggle', [self.model, true]);
						} else {
							self.$el.removeClass('best-answer');
							$(e.target).removeClass('active').text(data.label);
							AnsPress.trigger('answerToggle', [self.model, false]);
						}
					}
				}
			});
		},
		selectToggle: function () {
			if (this.model.get('hideSelect'))
				this.$el.find('[ap="select_answer"]').addClass('hide');
			else
				this.$el.find('[ap="select_answer"]').removeClass('hide');
		}
	});

	AnsPress.collections.Posts = Backbone.Collection.extend({
		model: AnsPress.models.Post,
		initialize: function () {
			var loadedPosts = [];
			$('[ap="question"],[ap="answer"]').each(function (e) {
				loadedPosts.push({ 'ID': $(this).attr('apId') });
			});
			this.add(loadedPosts);
		}
	});

	AnsPress.views.SingleQuestion = Backbone.View.extend({
		initialize: function () {
			this.listenTo(this.model, 'add', this.renderItem);
			AnsPress.on('answerToggle', this.answerToggle, this);
			AnsPress.on('deletePost', this.deletePost, this);
			AnsPress.on('answerCountUpdated', this.answerCountUpdated, this);
			AnsPress.on('formPosted', this.formPosted, this);
			this.listenTo(AnsPress, 'commentApproved', this.commentApproved);
			this.listenTo(AnsPress, 'commentDeleted', this.commentDeleted);
			this.listenTo(AnsPress, 'commentCount', this.commentCount);
			this.listenTo(AnsPress, 'formPosted', this.submitComment);
		},
		events: {
			'click [ap="loadEditor"]': 'loadEditor',
		},
		renderItem: function (post) {
			var view = new AnsPress.views.Post({ model: post, el: '[apId="' + post.get('ID') + '"]' });
			view.render();
		},

		render: function () {
			var self = this;
			this.model.each(function (post) {
				self.renderItem(post);
			});

			return self;
		},

		loadEditor: function (e) {
			var self = this;
			AnsPress.showLoading(e.target);

			AnsPress.ajax({
				data: $(e.target).data('apquery'),
				success: function (data) {
					AnsPress.hideLoading(e.target);
					$('#ap-form-main').html(data);
					$(e.target).closest('.ap-minimal-editor').removeClass('ap-minimal-editor');
				}
			});
		},
		/**
		 * Handles answer form submission.
		 */
		formPosted: function (data) {
			if (data.success && data.form === 'answer') {
				AnsPress.trigger('answerFormPosted', data);
				$('apanswersw').show();
				tinymce.remove();

				// Clear editor contents
				$('#ap-form-main').html('');
				$('#answer-form-c').addClass('ap-minimal-editor');

				// Append answer to the list.
				$('apanswers').append($(data.html).hide());
				$(data.div_id).slideDown(300);
				$(data.div_id).apScrollTo(null, true);
				this.model.add({ 'ID': data.ID });
				AnsPress.trigger('answerCountUpdated', data.answersCount);
			}
		},
		answerToggle: function (args) {
			this.model.forEach(function (m, i) {
				if (args[0] !== m)
					m.set('hideSelect', args[1]);
			});
		},
		deletePost: function (postID) {
			this.model.remove(postID);
			$('#post-' + postID).slideUp(400, function () {
				$(this).remove();
			});
		},
		answerCountUpdated: function (counts) {
			$('[ap="answers_count_t"]').text(counts.text);
		},
		commentApproved: function (data, elm) {
			$('#comment-' + data.comment_ID).removeClass('unapproved');
			$(elm).remove();
			if (data.commentsCount)
				AnsPress.trigger('commentCount', { count: data.commentsCount, postID: data.post_ID });
		},
		commentDeleted: function (data, elm) {
			$(elm).closest('apcomment').css('background', 'red');
			setTimeout(function () {
				$(elm).closest('apcomment').remove();
			}, 1000);
			if (data.commentsCount)
				AnsPress.trigger('commentCount', { count: data.commentsCount, postID: data.post_ID });
		},
		commentCount: function (args) {
			var find = $('[apid="' + args.postID + '"]');
			find.find('[ap-commentscount-text]').text(args.count.text);
			if (args.count.unapproved > 0)
				find.find('[ap-un-commentscount]').addClass('have');
			else
				find.find('[ap-un-commentscount]').removeClass('have');

			find.find('[ap-un-commentscount]').text(args.count.unapproved);
		},
		submitComment: function (data) {
			if (!('new-comment' !== data.action || 'edit-comment' !== data.action))
				return;

			if (data.success) {
				AnsPress.hideModal('commentForm');
				if (data.action === 'new-comment')
					$('#comments-' + data.post_id).html(data.html);

				if (data.action === 'edit-comment') {
					$old = $('#comment-' + data.comment_id);
					$(data.html).insertAfter($old);
					$old.remove();

					$('#comment-' + data.comment_id).css('backgroundColor', 'rgba(255, 235, 59, 1)');
					setTimeout(function () {
						$('#comment-' + data.comment_id).removeAttr('style');
					}, 500)
				}

				if (data.commentsCount)
					AnsPress.trigger('commentCount', { count: data.commentsCount, postID: data.post_id });
			}
		}
	});

	var AnsPressRouter = Backbone.Router.extend({
		routes: {
			'comment/:commentID': 'commentRoute',
			//'comment/:commentID/edit': 'editCommentsRoute',
			'comments/:postID/all': 'commentsRoute',
			'comments/:postID': 'commentsRoute',
		},
		commentRoute: function (commentID) {
			self = this;

			AnsPress.hideModal('comment', false);
			$modal = AnsPress.modal('comment', {
				content: '',
				size: 'medium',
				hideCb: function () {
					AnsPress.removeHash();
				}
			});
			$modal.$el.addClass('single-comment');
			AnsPress.showLoading($modal.$el.find('.ap-modal-content'));
			AnsPress.ajax({
				data: { comment_id: commentID, ap_ajax_action: 'load_comments' },
				success: function (data) {
					if (data.success) {
						$modal.setTitle(data.modal_title);
						$modal.setContent(data.html);
						AnsPress.hideLoading($modal.$el.find('.ap-modal-content'));
					}
				}
			});
		},

		commentsRoute: function (postId, paged) {
			self = this;
			AnsPress.ajax({
				data: { post_id: postId, ap_ajax_action: 'load_comments' },
				success: function (data) {
					$('#comments-' + postId).html(data.html);
				}
			});
		},
		editCommentsRoute: function (commentID) {
			self = this;
			AnsPress.hideModal('commentForm', false);
			AnsPress.modal('commentForm', {
				hideCb: function () {
					AnsPress.removeHash();
				}
			});

			AnsPress.showLoading(AnsPress.modal('commentForm').$el.find('.ap-modal-content'));
			AnsPress.ajax({
				data: { comment: commentID, ap_ajax_action: 'comment_form' },
				success: function (data) {
					AnsPress.hideLoading(AnsPress.modal('commentForm').$el.find('.ap-modal-content'));
					AnsPress.modal('commentForm').setTitle(data.modal_title);
					AnsPress.modal('commentForm').setContent(data.html);
				}
			});
		}
	});

	$('[ap="actiontoggle"]').click(function () {
		if (!$(this).is('.loaded'))
			AnsPress.showLoading(this);
	});

	$(document).ready(function () {
		var apposts = new AnsPress.collections.Posts();
		var singleQuestionView = new AnsPress.views.SingleQuestion({ model: apposts, el: '#anspress' });
		singleQuestionView.render();

		var anspressRouter = new AnsPressRouter();
		if (!Backbone.History.started)
			Backbone.history.start();
	});


})(jQuery);
