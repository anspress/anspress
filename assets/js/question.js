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
			count: ''
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
				$('#post-'+this.postID).find('post-message').html(message);
			else
				$('#post-'+this.postID).find('post-message').html('');
		}
	});

	AnsPress.views.Actions = Backbone.View.extend({
		id: function(){
			return this.postID;
		},
		tagName: 'ul',
		className: 'ap-actions',
		initialize: function(options){
			this.model = options.model;
			this.postID = options.postID;
			AnsPress.on('changedPostStatus', this.postStatusChanged, this);
		},
		renderItem: function(action){
			var view = new AnsPress.views.Action({ model: action, postID: this.postID });
			this.$el.append(view.render().$el);
		},
		render: function(){
			var self = this;
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
		}
	});

	AnsPress.models.Post = Backbone.Model.extend({
		idAttribute: 'ID',
		defaults:{
			actionsLoaded: false,
			hideSelect: ''
		}
	});

	AnsPress.collections.Comments = Backbone.Collection.extend({
		model: Backbone.Model.extend({
			idAttribute: 'ID',
			defaults:{
				ID: '',
				userID: '',
				avatar: '',
				content: '',
				actions: ''
			}
		})
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
			this.model.on('change', this.render, this);
		},
		events: {
			'click [ap="comment_action"]': 'actions'
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
						if(data.commentsCount){
							$('[ap-commentscount-text]').text(data.commentsCount.text);
						}
						if(data.action === 'delete_comment')
							AnsPress.trigger('removeComment', self.model);
					}
				}
			});
		}
	});

	AnsPress.views.Comments = Backbone.View.extend({
		initialize: function(options){
			this.model = options.model;
			this.postID = options.postID;
			AnsPress.on('removeComment', this.removeComment, this);
			this.listenTo(this.model, 'remove', this.commentRemoved);
			this.listenTo(this.model, 'add', this.newComment);
		},
		renderItem: function(comment){
			var view = new AnsPress.views.Comment({ model: comment, postID: this.postID });
			this.$el.find('.ap-comments').append(view.render().$el);
			return view;
		},
		render: function(){
			var self = this;
			if(this.model){
				this.$el.addClass('have-comments');
				this.model.each(function(comment){
					self.renderItem(comment);
				});
			}

			return this;
		},
		removeComment: function(comment){
			this.model.remove(comment);
		},
		commentRemoved: function(comment){
			if(this.model.size() === 0)
				this.$el.removeClass('have-comments');
			$('#comment-'+comment.id).slideUp(400, function(){
				$(this).remove();
			});
		},
		newComment: function(comment){
			var view = this.renderItem(comment);
			view.$el.hide().slideDown(400);
			this.$el.find('.ap-comments').apScrollTo('#comment-'+comment.id);
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
			q.ap_ajax_action = 'post_actions';
			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.target);
					$(e.target).addClass('loaded');
					self.actions.model = new AnsPress.collections.Actions(data.actions);
					self.actions.view = new AnsPress.views.Actions({ model: self.actions.model, postID: self.model.get('ID') });
					self.$el.find('post-actions .ap-actions').html(self.actions.view.render().$el);
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
			if($(e.currentTarget).is('.loaded')){
				if(this.$el.find('ap-comments').is('.have-comments'))
					this.$el.find('ap-comments').find('.ap-comments').slideUp(400, function(){
						$(this).parent('ap-comments').removeClass('have-comments');
					});
				else
					this.$el.find('ap-comments').addClass('have-comments').find('.ap-comments').slideDown(400);
				return;
			}
			var q = $.parseJSON($(e.currentTarget).attr('ap-query'));
			q.ap_ajax_action = 'load_comments';
			AnsPress.showLoading(e.currentTarget);
			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.currentTarget);
					$(e.currentTarget).addClass('loaded');
					self.comments = new AnsPress.collections.Comments(data.comments);
					var view = new AnsPress.views.Comments({ model: self.comments, postID: self.model.id, el: '#comments-'+self.model.id });
					view.render();
					view.$el.hide().slideDown(400);
				}
			});
		},
		loadCommentForm: function(e){
			e.preventDefault();
			if(this.$el.find('[comment-form]').length === 0){
				var q = $.parseJSON($(e.target).attr('ap-query'));
				var t = _.template(AnsPress.getTemplate('comment-form')());
				this.$el.find('ap-comments').append(t(q));
				this.$el.find('[comment-form]').hide().slideDown(400, function(){
					$(this).find('textarea').focus();
				})
			}else{
				this.$el.find('[comment-form]').slideUp(400, function(){
					$(this).remove();
				});
			}
		},
		submitComment: function(e){
			var self = this;
			AnsPress.showLoading(e.target);
			AnsPress.ajax({
				data: $(e.target).serialize(),
				success: function(data){
					AnsPress.hideLoading(e.target);
					if(data.success){
						if(self.comments){
							self.comments.add(data.comment);
						}
						if(data.commentsCount){
							self.$el.find('[ap-commentscount-text]').text(data.commentsCount.text);
						}
						$(e.target).find('textarea').val('');
					}
				}
			});
			return false;
		},
		hideCommentForm: function(e){
			e.preventDefault();
			$(e.target).closest('[comment-form]').remove();
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

	$('[ap="actiontoggle"]').click(function(){
		if(!$(this).is('.loaded'))
			AnsPress.showLoading(this);
	});

	var apposts = new AnsPress.collections.Posts();
	var singleQuestionView = new AnsPress.views.SingleQuestion({ model: apposts, el: '#anspress' });
	singleQuestionView.render();

})(jQuery);









