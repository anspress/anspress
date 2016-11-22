/**
 * Javascript code for AnsPress fontend
 * @since 2.0
 * @package AnsPress
 * @author Rahul Aryan
 * @license GPL 2+
 */

(function($) {
	AnsPress.models.Post = Backbone.Model.extend({
		idAttribute: 'ID',
		defaults:{
			actionsLoaded: false,
			actions: [],
			hideSelect: '',
			postMessage: ''
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
			this.model.on('change:hideSelect', this.selectToggle, this);
			this.model.on('change:actions', this.renderPostActions, this);
			this.model.on('change:postMessage', this.renderPostMessage, this);
		},
		events: {
			'click [ap-vote] > a': 'voteClicked',
			'click [ap="actiontoggle"]': 'postActions',
			'click [ap-action]': 'postAction',
			'click [ap="select_answer"]': 'selectAnswer'
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
			if(!this.model.get('actionsLoaded'))
				AnsPress.ajax({
					data: q,
					success: function(data){
						AnsPress.hideLoading(e.target);
						$(e.target).addClass('loaded');
						self.model.set({'actions': data.actions, 'actionsLoaded': true });
						self.renderPostActions(e);
					}
				});
		},
		renderPostActions: function(e){
			var t = _.template($('#ap-template-actions').html());
			this.$el.find('post-actions .ap-actions').html(t(this.model.toJSON()));
		},
		postAction: function(e){
			e.preventDefault();
			var self = this;
			AnsPress.showLoading(e.target);

			var action = $(e.target).attr('ap-action');
			var q = $.parseJSON($(e.target).attr('ap-query'));
			q.ap_ajax_action = 'action_'+action;

			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.target);
					var actions = _.clone(self.model.get('actions'));
					actions[action] = _.defaults(data.action, actions[action]);
					self.model.set('actions', actions);

					if(data.postMessage)
						self.model.set('postMessage', data.postMessage);
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
		renderPostMessage: function(post){
			var msg = post.get('postMessage');
			if(!msg.class) msg.class = 'ap-pmsg';
			if(!_.isEmpty(msg.text))
				this.$el.find('post-message').html('<div class="'+msg.class+'">'+msg.text+'</div>');
			else
				this.$el.find('post-message').html('');
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
			this.model.on('add', this.renderItem, this);
			AnsPress.on('answerToggle', this.answerToggle, this);
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
					AnsPress.hideLoading($(e.target).find('.ap-btn-submit'));
					// Clear upload files
					if(AnsPress.uploader) AnsPress.uploader.splice();

					if(data.success){
						// Clear editor contents
						$('#description').val('');
						if (typeof tinyMCE !== 'undefined' && data.success)
							tinyMCE.activeEditor.setContent('');

						// Append anwer to the list.
						$('#answers').append($(data['html']).hide());
						$(data.div_id).slideDown(500);
						self.model.add({'ID': data.ID});
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
		}
	});

	$('[ap="actiontoggle"]').click(function(){
		if(!$(this).is('.checked') && !$(this).is('.loaded'))
			AnsPress.showLoading(this);
		$(this).toggleClass('checked');
	});


})(jQuery);

var apposts = new AnsPress.collections.Posts();

var singleQuestionView = new AnsPress.views.SingleQuestion({ model: apposts, el: '#ap-single' });
singleQuestionView.render();







