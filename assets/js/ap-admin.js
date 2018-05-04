	/* on start */

jQuery(function () {
	jQuery.fn.apAjaxQueryString = function () {
		var query = jQuery(this).data('query').split("::");

		var newQuery = {};

		newQuery['action'] = 'ap_ajax';
		newQuery['ap_ajax_action'] = query[0];
		newQuery['__nonce'] = query[1];
		newQuery['args'] = {};

		var newi = 0;
		jQuery.each(query,function(i){
			if(i != 0 && i != 1){
				newQuery['args'][newi] = query[i];
				newi++;
			}
		});

		return newQuery;
	};

	/* create document */
	APjs.admin = new APjs.admin();
	/* need to call init manually with jQuery */
	APjs.admin.initialize();
});

/* namespace */
window.APjs = {};
APjs.admin = function () {};

(function($){
	APjs.admin.prototype = {

		/* automatically called */
		initialize: function () {
			this.renameTaxo();
			this.editPoints();
			this.savePoints();
			this.deleteFlag();
			this.ajaxBtn();
		},


		renameTaxo: function () {
			jQuery('.ap-rename-taxo').click(function (e) {
				e.preventDefault();
				jQuery.ajax({
					url: ajaxurl,
					data: {
						action: 'ap_taxo_rename'
					},
					context: this,
					success: function (data) {
						jQuery(this).closest('.error').remove();
						location.reload();
					}
				});
				return false;
			});
		},
		editPoints: function () {
			jQuery('.wp-admin').on('click', '[data-action="ap-edit-reputation"]', function (e) {
				e.preventDefault();
				var id = jQuery(this).attr('href');
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: {
						action: 'ap_edit_reputation',
						id: id
					},
					context: this,
					dataType: 'json',
					success: function (data) {
						if (data['status']) {
							jQuery('#ap-reputation-edit').remove();
							jQuery('#anspress-reputation-table').hide();
							jQuery('#anspress-reputation-table').after(data['html']);
						}
					}
				});
			});
		},

		savePoints: function () {
			jQuery('.wp-admin').on('submit', '[data-action="ap-save-reputation"]', function (e) {
				e.preventDefault();
				jQuery('.button-primary', this).attr('disabled', 'disabled');
				var id = jQuery(this).attr('href');
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					cache: false,
					data: jQuery(this).serialize({
						checkboxesAsBools: true
					}),
					context: this,
					dataType: 'json',
					success: function (data) {
						if (data['status']) {
							jQuery('.wrap').empty().html(data['html']);
						}
					}
				});

				return false;
			});
		},
		deleteFlag: function () {
			jQuery('[data-action="ap-delete-flag"]').click(function (e) {
				e.preventDefault();
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: jQuery(this).attr('href'),
					context: this,
					success: function (data) {
						jQuery(this).closest('.flag-item').remove();
					}
				});
			});
		},

		ajaxBtn: function () {
			$('.ap-ajax-btn').on('click', function (e) {
				e.preventDefault();
				var q = $(this).apAjaxQueryString();
				$.ajax({
					url: ajaxurl,
					data: q,
					context: this,
					type: 'POST',
					success: function (data) {
						if (typeof $(this).data('cb') !== 'undefined') {
							var cb = $(this).data("cb");
							if (typeof APjs.admin[cb] === 'function') {
								APjs.admin[cb](data, this);
							}
						}
					}
				});

			});
		},
		replaceText: function (data, elm) {
			$(elm).closest('li').find('strong').text(data);
		}

	}

	$(document).ready(function() {
		$('#select-question-for-answer').on('keyup', function () {
			if (jQuery.trim(jQuery(this).val()) == '')
				return;
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'ap_ajax',
					ap_ajax_action: 'suggest_similar_questions',
					value: jQuery(this).val(),
					is_admin: true
				},
				success: function (data) {
					var textJSON = jQuery(data).filter('#ap-response').html();
					if (typeof textJSON !== 'undefined' && textJSON.length > 2) {
						data = JSON.parse(textJSON);
					}
					console.log(data);
					if (typeof data['html'] !== 'undefined')
						jQuery('#similar_suggestions').html(data['html']);
				},
				context: this,
			});
		});

		$('[data-action="ap_media_uplaod"]').click(function (e) {
			e.preventDefault();
			$btn = jQuery(this);
			var image = wp.media({
				title: jQuery(this).data('title'),
				// mutiple: true if you want to upload multiple files at once
				multiple: false
			}).open().on('select', function (e) {
				// This will return the selected image from the Media Uploader, the result is an object
				var uploaded_image = image.state().get('selection').first();
				// We convert uploaded_image to a JSON object to make accessing it easier
				// Output to the console uploaded_image
				var image_url = uploaded_image.toJSON().url;
				var image_id = uploaded_image.toJSON().id;

				// Let's assign the url value to the input field
				jQuery($btn.data('urlc')).val(image_url);
				jQuery($btn.data('idc')).val(image_id);

				if (!jQuery($btn.data('urlc')).prev().is('img'))
					jQuery($btn.data('urlc')).before('<img id="ap_category_media_preview" src="' + image_url + '" />');
				else
					jQuery($btn.data('urlc')).prev().attr('src', image_url);
			});
		});

		$('[data-action="ap_media_remove"]').click(function (e) {
			e.preventDefault();
			$('input[data-action="ap_media_value"]').val('');
			$('img[data-action="ap_media_value"]').remove();
		});

		$('.checkall').click(function () {
			var checkbox = $(this).closest('.ap-tools-ck').find('input[type="checkbox"]:not(.checkall)');
			checkbox.prop('checked', $(this).prop("checked"));
		})

		$('#' + $('#ap-tools-selectroles').val()).slideDown();

		$('#ap-tools-selectroles').change(function () {
			var id = '#' + $(this).val();
			$('.ap-tools-roleitem').hide();
			$(id).fadeIn(300);
		})

	});

})(jQuery);