/**
 * Javascript code for AnsPress fontend
 * @since 4.0.0
 * @package AnsPress
 * @author Rahul Aryan
 * @license GPL 3+
 */

(function($) {
	AnsPress.loadTemplate('question');

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
		id: function(){
			return this.postID;
		},
		className: function(){
			var klass = '';
			if(this.model.get('header')) klass += ' ap-dropdown-header';
			if(this.model.get('active')) klass += ' active';
			return klass;
		},
		tagName: 'li',
		template: AnsPress.getTemplate('action'),
		initialize: function(options){
			this.model = options.model;
			this.postID = options.postID;
			this.model.on('change', this.render, this);
			this.listenTo(this.model, 'remove', this.removed);
		},
		events: {
			'click a': 'triggerAction'
		},
		render: function(){
			var t = _.template(this.template());
			this.$el.html(t(this.model.toJSON()));
			this.$el.attr('class', this.className());
			return this;
		},
		triggerAction: function(e){
			var q = this.model.get('query');
			if(_.isEmpty(q))
				return;

			e.preventDefault();
			var self = this;
			AnsPress.showLoading(e.target);
			var cb = this.model.get('cb');
			q.ap_ajax_action = 'action_'+cb;

			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.target);
					if(data.redirect) window.location = data.redirect;

					if(data.success && ( cb=='status' || cb=='toggle_delete_post'))
						AnsPress.trigger('changedPostStatus', {postID: self.postID, data:data, action:self.model});

					if(data.action){
						self.model.set(data.action);
					}

					if(data.postMessage) self.renderPostMessage(data.postMessage);
					if(data.deletePost) AnsPress.trigger('deletePost', data.deletePost);
					if(data.answersCount) AnsPress.trigger('answerCountUpdated', data.answersCount);
				}
			});
		},
		renderPostMessage: function(message){
			if(!_.isEmpty(message))
				$('#post-'+this.postID).find('postMessage').html(message);
			else
				$('#post-'+this.postID).find('postMessage').html('');
		},
		removed: function(){
      this.remove();
    }
	});


	AnsPress.views.Actions = Backbone.View.extend({
		id: function(){
			return this.postID;
		},
		searchTemplate: '<div class="ap-filter-search"><input type="text" search-filter placeholder="'+aplang.search+'" /></div>',
		tagName: 'ul',
		className: 'ap-actions',
		events: {
			'keyup [search-filter]': 'searchInput'
		},
		initialize: function(options){
			this.model = options.model;
			this.postID = options.postID;
			this.multiple = options.multiple;
			this.action = options.action;
			this.nonce = options.nonce;

			AnsPress.on('changedPostStatus', this.postStatusChanged, this);
			this.listenTo(this.model, 'add', this.added);
		},
		renderItem: function(action){
			var view = new AnsPress.views.Action({ model: action, postID: this.postID });
			this.$el.append(view.render().$el);
		},
		render: function(){
			var self = this;
			if(this.multiple)
        this.$el.append(this.searchTemplate);

			this.model.each(function(action){
				self.renderItem(action);
			});

			return this;
		},
		postStatusChanged: function(args){
			if(args.postID !== this.postID) return;

			// Remove post status class
			$("#post-"+this.postID).removeClass( function() {
				return this.className.split(' ').filter(function(className) {return className.match(/status-/)}).join(' ');
			});

			$("#post-"+this.postID).addClass('status-'+args.data.newStatus);
			var activeStatus = this.model.where({cb: 'status', active: true });
			activeStatus.forEach(function(status){
				status.set({active: false});
			});
		},
		searchInput: function(e){
      var self = this;

      clearTimeout(this.searchTO);
      this.searchTO = setTimeout(function(){
        self.search($(e.target).val(), e.target);
      }, 600);
    },
		search: function(q, e){
      var self = this;

      var args = { nonce: this.nonce, ap_ajax_action: this.action, search: q, filter: this.filter, post_id: this.postID };

      AnsPress.showLoading(e);
			AnsPress.ajax({
				data: args,
				success: function(data){
					console.log(data);
          AnsPress.hideLoading(e);
          if(data.success){
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
		added: function(model){
      this.renderItem(model);
    }
	});

	AnsPress.models.Post = Backbone.Model.extend({
		idAttribute: 'ID',
		defaults:{
			actionsLoaded: false,
			hideSelect: ''
		}
	});

	AnsPress.models.Comment = Backbone.Model.extend({
		idAttribute: 'ID',
		defaults:{
			ID: '',
			userID: '',
			avatar: '',
			content: '',
			actions: ''
		}
	});

	AnsPress.collections.Comments = Backbone.Collection.extend({
		model: AnsPress.models.Comment
	});

	AnsPress.views.Comment = Backbone.View.extend({
		tagName: 'ap-comment',
		id: function(){
			return 'comment-' + this.model.id;
		},
		className: function(){
			var klass = this.model.get('class');
			if(this.model.get('approved')==='0')
				klass += ' unapproved';
			return klass
		},
		template: AnsPress.getTemplate('comment'),
		initialize: function(options){
			this.model = options.model;
			this.postID = options.postID;
			this.model.on('change', this.render, this);
		},
		events: {
			'click [ap="comment_action"]': 'actions',
			'click [ap="edit_comment"]': 'editCommentForm'
		},
		render: function(){
			var t = _.template(this.template());
			this.$el.html(t(this.model.toJSON()));
			this.$el.attr('class', this.className());
			return this;
		},
		actions: function(e){
			e.preventDefault();
			var self = this;
			var q = $.parseJSON($(e.target).attr('ap-query'));
			AnsPress.showLoading(e.target);
			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.target);
					if(data.success){
						if(data.model)self.model.set(data.model);
						if(data.commentsCount)
							AnsPress.trigger('commentCount', {count: data.commentsCount, postID: self.postID });

						if(data.action === 'delete_comment')
							AnsPress.trigger('removeComment', self.model);
					}
				}
			});
		},
		editCommentForm: function(e){
			e.preventDefault();
			AnsPress.trigger('loadCommentEdit', {postID: this.postID, e: e, comment: this.model});
		}
	});

	AnsPress.views.Comments = Backbone.View.extend({
		initialize: function(options){
			this.model = options.model;
			this.postID = options.postID;
			this.collapsed = options.collapsed;
			this.collapsed_msg = options.collapsed_msg;
			this.offset = options.offset;
			this.query = options.query;
			AnsPress.on('removeComment', this.removeComment, this);
			this.listenTo(this.model, 'remove', this.commentRemoved);
			this.listenTo(this.model, 'add', this.newComment);
		},
		events: {
			'click .ap-comments-more': 'loadMore'
		},
		renderItem: function(comment){
			this.$el.parent().addClass('have-comments');
			var view = new AnsPress.views.Comment({ model: comment, postID: this.postID });
			this.$el.append(view.render().$el);
			return view;
		},
		appendLoadMore: function(){
			this.$el.find('.ap-comments-more').remove();
			if(this.collapsed){
				this.$el.append('<a href="#" class="ap-comments-more">'+this.collapsed_msg+'</a>');
			}
		},
		render: function(){
			var self = this;
			if(this.model.length > 0){
				this.model.each(function(comment){
					self.renderItem(comment);
				});

				this.appendLoadMore();
			}

			return this;
		},
		removeComment: function(comment){
			this.model.remove(comment);
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
			this.appendLoadMore();
			view.$el.hide().slideDown(400);

			this.$el.apScrollTo(null, true);
		},
		loadMore: function(e){
			e.preventDefault();
			var self = this;
			AnsPress.showLoading(e.target);
			this.query.offset = this.offset;
			AnsPress.ajax({
				data: this.query,
				success: function(data){
					AnsPress.hideLoading(e.target);
					self.collapsed = data.collapsed;
					self.collapsed_msg = data.collapsed_msg;
					self.offset = data.offset;

					if(data.comments.length>0){
						self.model.add(data.comments);
					}

				}
			});
		}
	});

	AnsPress.views.Post = Backbone.View.extend({
		idAttribute: 'ID',
		templateId: 'answer',
		tagName: 'div',
		actions: { view: {}, model: {} },
		id: function(){
			return 'post-' + this.model.get('ID');
		},
		initialize: function(options){
			this.listenTo(this.model, 'change:vote', this.voteUpdate);
			this.listenTo(this.model, 'change:hideSelect', this.selectToggle);
			this.listenTo(AnsPress, 'commentCount', this.commentCount);
			this.listenTo(AnsPress, 'loadCommentEdit', this.loadCommentEdit);
		},
		events: {
			'click [ap-vote] > a': 'voteClicked',
			'click [ap="actiontoggle"]:not(.loaded)': 'postActions',
			'click [ap="select_answer"]': 'selectAnswer',
			'click [ap="comment_btn"]': 'loadComments',
			'click [ap="new-comment"]': 'loadCommentForm',
			'submit [comment-form]': 'submitComment',
			'click [ap="cancel-comment"]': 'hideCommentForm'
		},
		voteClicked: function(e){
			e.preventDefault();
			if($(e.target).is('.disable'))
				return;

			self = this;
			var type = $(e.target).is('.vote-up') ? 'vote_up' : 'vote_down';
			var originalValue = _.clone(self.model.get('vote'));
			var vote = _.clone(originalValue);

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
					if (data.success && _.isObject(data.voteData))
						self.model.set('vote', data.voteData);
					else
						self.model.set('vote', originalValue); // Restore original value on fail
				}
			})
		},
		voteUpdate: function(post){
			var self = this;
			this.$el.find('[ap="votes_net"]').text(this.model.get('vote').net);
			_.each(['up', 'down'], function(e){
				self.$el.find('.vote-'+e).removeClass('voted disable').addClass(self.voteClass('vote_'+e));
			});
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
		},
		postActions: function(e){
			var self = this;
			var q = $.parseJSON($(e.target).attr('ap-query'));
			if(typeof q.ap_ajax_action === 'undefined')
				q.ap_ajax_action = 'post_actions';

			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.target);
					$(e.target).addClass('loaded');
					self.actions.model = new AnsPress.collections.Actions(data.actions);
					self.actions.view = new AnsPress.views.Actions({ model: self.actions.model, postID: self.model.get('ID') });
					self.$el.find('postActions .ap-actions').html(self.actions.view.render().$el);
				}
			});
		},

		selectAnswer: function(e){
			e.preventDefault();
			var self = this;
			var q = $.parseJSON($(e.target).attr('ap-query'));
			q.ap_ajax_action = 'toggle_best_answer';

			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.target);
					if(data.success){
						if(data.action === 'selected'){
							self.$el.addClass('best-answer');
							$(e.target).addClass('active').text(data.label);
							AnsPress.trigger('answerToggle', [self.model, true]);
						}else{
							self.$el.removeClass('best-answer');
							$(e.target).removeClass('active').text(data.label);
							AnsPress.trigger('answerToggle', [self.model, false]);
						}
					}
				}
			});
		},
		selectToggle: function(){
			if(this.model.get('hideSelect'))
				this.$el.find('[ap="select_answer"]').addClass('hide');
			else
				this.$el.find('[ap="select_answer"]').removeClass('hide');
		},
		loadComments: function(e){
			e.preventDefault();
			var self = this;
			var elm = $(e.currentTarget);
			var commentDiv = '#comments-'+self.model.id;

			if(elm.is('.loaded')){
				if($(commentDiv).is('.have-comments'))
					$(commentDiv).find('.ap-comments').slideUp(400, function(){
						$(commentDiv).removeClass('have-comments');
					});
				else if( self.comments.length > 0 )
					$(commentDiv).addClass('have-comments').find('.ap-comments').slideDown(400);
				return;
			}

			var q = $.parseJSON(elm.attr('ap-query'));
			q.ap_ajax_action = 'load_comments';
			AnsPress.showLoading(elm);
			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(elm);
					elm.addClass('loaded');
					self.comments = new AnsPress.collections.Comments(data.comments);

					var options = {
						model: self.comments,
						postID: self.model.id,
						el: commentDiv+' .ap-comments',
						collapsed: data.collapsed,
						collapsed_msg: data.collapsed_msg,
						offset: data.offset,
						query: q
					};

					var view = new AnsPress.views.Comments(options);
					view.render();
					view.$el.hide().slideDown(400);
				}
			});
		},
		loadCommentForm: function(e, comment){
			e.preventDefault();

			if(_.isEmpty($(e.target).attr('ap-query'))){
				if(this.$el.find('.ap-comment-no-perm').length === 0){
					var q = {msg: $(e.target).attr('ap-msg')};
					var t = _.template(AnsPress.getTemplate('comment-no-permission')());
					this.$el.find('apComments').append(t(q));
				}else{
					this.$el.find('.ap-comment-no-perm').remove();
				}
				return;
			}

			if(this.$el.find('[comment-form]').length === 0){
				var q = $.parseJSON($(e.target).attr('ap-query'));
				var t = _.template(AnsPress.getTemplate('comment-form')());
				this.$el.find('apComments').append(t(q));
				this.$el.find('[comment-form]').hide().slideDown(400, function(){
					$(this).find('textarea').focus();
				});
			}else{
				this.$el.find('[comment-form]').slideUp(400, function(){
					$(this).remove();
				});
			}
		},
		loadCommentEdit: function(args){
			if(this.model.id !== args.postID)
				return;

			if(this.$el.find('[comment-form]').length > 0)
				this.$el.find('[comment-form]').remove();

			var q = $.parseJSON($(args.e.target).attr('ap-query'));
			var t = _.template(AnsPress.getTemplate('comment-form')());
			this.$el.find('apComments').append(t(q));
			this.$el.find('[comment-form]').hide().slideDown(400, function(){
				$(this).find('textarea').val(args.comment.get('content')).focus();
			});
		},
		submitComment: function(e){
			var self = this;
			AnsPress.showLoading(e.target);
			AnsPress.ajax({
				data: $(e.target).serialize(),
				success: function(data){
					AnsPress.hideLoading(e.target);
					if(data.success){
						if(self.comments && data.action === 'new-comment')
							self.comments.add(data.comment);

						if(self.comments && data.action === 'edit-comment')
							self.comments.get(data.comment.ID).set(data.comment);

						if(data.commentsCount)
							AnsPress.trigger('commentCount', {count: data.commentsCount, postID: self.model.id });

						self.$el.find('[comment-form]').remove();
					}
				}
			});
			return false;
		},
		hideCommentForm: function(e){
			e.preventDefault();
			$(e.target).closest('[comment-form]').remove();
		},
		commentCount: function(args){
			if(this.model.id !== args.postID)
				return;

			this.$el.find('[ap-commentscount-text]').text(args.count.text);

			if(args.count.unapproved > 0 )
				this.$el.find('[ap-un-commentscount]').addClass('have');
			else
				this.$el.find('[ap-un-commentscount]').removeClass('have');

			this.$el.find('[ap-un-commentscount]').text(args.count.unapproved);
		}
	});

	AnsPress.collections.Posts = Backbone.Collection.extend({
		model: AnsPress.models.Post,
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
			this.listenTo(this.model, 'add', this.renderItem);
			AnsPress.on('answerToggle', this.answerToggle, this);
			AnsPress.on('deletePost', this.deletePost, this);
			AnsPress.on('answerCountUpdated', this.answerCountUpdated, this);
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
		/**
		 * Handles answer form submission.
		 */
		answerForm: function(e){
			var self = this;
			AnsPress.showLoading($(e.target).find('.ap-btn-submit'));

			// Clear previous errors
			$(e.target).find('.have-error').removeClass('have-error');
			$(e.target).find('.error').remove();

			// Ajax request
			AnsPress.ajax({
				data: $(e.target).serialize(),
				success: function(data){
					// Redirect if have redirect property.
					if(data.redirect){
						window.location = data.redirect;
						return;
					}

					if(typeof grecaptcha !== 'undefined' && typeof widgetId1 !== 'undefined')
            grecaptcha.reset(widgetId1);

					AnsPress.trigger('answerFormPosted', data);
					AnsPress.hideLoading($(e.target).find('.ap-btn-submit'));
					// Clear upload files
					if(AnsPress.uploader) AnsPress.uploader.splice();
					if(data.success){
						$('ap-answers-w').show();
						// Clear editor contents
						$('#description').val('');
						if (typeof tinyMCE !== 'undefined' && data.success)
							tinyMCE.activeEditor.setContent('');

						// Append anwer to the list.
						$('ap-answers').append($(data['html']).hide());
						$(data.div_id).slideDown(800);
						self.model.add({'ID': data.ID});
						AnsPress.trigger('answerCountUpdated', data.answersCount);
					}

					// If form have errors then show it
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
		},
		answerToggle: function(args){
			this.model.forEach(function(m, i) {
				if(args[0] !== m)
					m.set('hideSelect', args[1]);
			});
		},
		deletePost: function(postID){
			this.model.remove(postID);
			$('#post-'+postID).slideUp(400, function(){
				$(this).remove();
			});
		},
		answerCountUpdated: function(counts){
			$('[ap-answerscount-text]').text(counts.text);
		}
	});

	AnsPress.views.Modal = Backbone.View.extend({
		id: 'ap-modal',
		className: 'ap-modal',
		template: AnsPress.getTemplate('modal'),
		events: {
			'click [close-modal]': 'hide'
		},
		initialize: function(options){
			this.data = options;
		},
		render: function(){
			var t = _.template(this.template());
			this.$el.html(t(this.data));
			return this;
		},
		hide: function(e){
			e.preventDefault();
			this.remove();
		}
	});

  var AnsPressRouter = Backbone.Router.extend({
		routes: {
			'comment/:commentID': 'commentRoute'
		},
		commentRoute: function (query, page) {
			AnsPress.ajax({
				data: ajaxurl + '?action=ap_ajax&ap_ajax_action=get_comment&comment_id='+query,
				success: function(data){
					if(data.success){
						var commentsModel = new AnsPress.collections.Comments(data.comment);
						var modalView = new AnsPress.views.Modal({
							content: '<apComments class="have-comments"><div class="ap-comments"></div></apComments>',
							size: 'medium'
						});
						$('body').append(modalView.render().$el);
						var commentsView = new AnsPress.views.Comments({model: commentsModel, el: modalView.$el.find('.ap-comments')});

						modalView.$el.find('.ap-modal-content').html(commentsView.render().$el);
					}
				}
			});
		}
  });

	$('[ap="actiontoggle"]').click(function(){
		if(!$(this).is('.loaded'))
			AnsPress.showLoading(this);
	});

	$(document).ready(function(){
		var apposts = new AnsPress.collections.Posts();
		var singleQuestionView = new AnsPress.views.SingleQuestion({ model: apposts, el: '#anspress' });
		singleQuestionView.render();

		var anspressRouter = new AnsPressRouter();
		if(!Backbone.History.started)
			Backbone.history.start();

		if(apShowComments)
			$('[ap="comment_btn"]').each(function(){
				$(this).click();
			});
	});


})(jQuery);









