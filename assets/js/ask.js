(function($){
  AnsPress.views.AskView = Backbone.View.extend({
		initialize: function(){
		},

    events: {
      'submit [ap="questionForm"]': 'questionForm'
    },

    /**
		 * Handles question form submission.
		 */
		questionForm: function(e){
			var self = this;
			// Clear previous errors
			$(e.target).find('.have-error').removeClass('have-error');
			$(e.target).find('.error').remove();
      AnsPress.showLoading($(e.target).find('.ap-btn-submit'));

			// Ajax request
			AnsPress.ajax({
				data: $(e.target).serialize(),
				success: function(data){
					if(typeof grecaptcha !== 'undefined')
            grecaptcha.reset(widgetId1);

					AnsPress.hideLoading($(e.target).find('.ap-btn-submit'));
					// Clear upload files
					if(AnsPress.uploader) AnsPress.uploader.splice();

					if(data.success){
						window.location = data.redirect;
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

  var askView = new AnsPress.views.AskView({el: '#ap-ask-page'});
})(jQuery);