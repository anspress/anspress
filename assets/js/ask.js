(function ($) {
	AnsPress.views.AskView = Backbone.View.extend({
		initialize: function () {},

		events: {
			'submit [ap="questionForm"]': 'questionForm',
			'keyup [data-action="suggest_similar_questions"]': 'questionSuggestion'
		},

		/**
		 * Handles question form submission.
		 */
		questionForm: function (e) {
			var self = this;
			// Clear previous errors
			$(e.target).find('.have-error').removeClass('have-error');
			$(e.target).find('.error').remove();
			AnsPress.showLoading($(e.target).find('.ap-btn-submit'));

			// Ajax request
			AnsPress.ajax({
				data: $(e.target).serialize(),
				success: function (data) {
					if (typeof grecaptcha !== 'undefined' && typeof widgetId1 !== 'undefined')
						grecaptcha.reset(widgetId1);

					AnsPress.hideLoading($(e.target).find('.ap-btn-submit'));
					// Clear upload files
					if (AnsPress.uploader) AnsPress.uploader.splice();

					if (data.success) {
						window.location = data.redirect;
					}

					// If form have errors then show it
					if (data.errors) {
						_.each(data.errors, function (err, i) {
							$('.ap-field-' + i).addClass('have-error')
							if (i === 'description' && $('.ap-field-ap_upload').length > 0)
								i = 'ap_upload';

							$('.ap-field-' + i).append('<span class="error">' + err + '</span>');
						});
					}
				}
			});
			return false;
		},

		suggestTimeout: null,
		questionSuggestion: function (e) {
			var self = this;
			if (disable_q_suggestion || false)
				return;

			var title = $(e.target).val();
			var inputField = this;
			if (title.length == 0)
				return;

			if (self.suggestTimeout != null) clearTimeout(self.suggestTimeout);

			self.suggestTimeout = setTimeout(function () {
				self.suggestTimeout = null;
				AnsPress.ajax({
					data: {
						ap_ajax_action: 'suggest_similar_questions',
						__nonce: ap_nonce,
						value: title
					},
					success: function (data) {
						console.log(data);
						$("#similar_suggestions").html(data.html);
					}
				});
			}, 800);
		}
	});

	var askView = new AnsPress.views.AskView({
		el: '#ap-ask-page'
	});
})(jQuery);