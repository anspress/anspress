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
		}
	});


})(jQuery);

var apposts = new AnsPress.collections.Posts();

var singleQuestionView = new AnsPress.views.SingleQuestion({ model: apposts, el: '#ap-single' });
singleQuestionView.render();







