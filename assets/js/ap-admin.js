(function ($) {
	/*
	* for getting unchecked fields
	* source: http://tdanemar.wordpress.com/2010/08/24/jquery-serialize-method-and-checkboxes/
	*/
     $.fn.serialize = function (options) {
         return $.param(this.serializeArray(options));
     };

     $.fn.serializeArray = function (options) {
         var o = $.extend({
         checkboxesAsBools: false
     }, options || {});

     var rselectTextarea = /select|textarea/i;
     var rinput = /text|hidden|password|search/i;

     return this.map(function () {
         return this.elements ? $.makeArray(this.elements) : this;
     })
     .filter(function () {
         return this.name && !this.disabled &&
             (this.checked
             || (o.checkboxesAsBools && this.type === 'checkbox')
             || rselectTextarea.test(this.nodeName)
             || rinput.test(this.type));
         })
         .map(function (i, elem) {
             var val = $(this).val();
             return val == null ?
             null :
             $.isArray(val) ?
             $.map(val, function (val, i) {
                 return { name: elem.name, value: val };
             }) :
             {
                 name: elem.name,
                 value: (o.checkboxesAsBools && this.type === 'checkbox') ? //moar ternaries!
                        (this.checked ? '1' : '0') :
                        val
             };
         }).get();
     };

})(jQuery);

/* on start */
jQuery(function() {

    /* create document */
    APjs.admin = new APjs.admin();
    /* need to call init manually with jQuery */
    APjs.admin.initialize();

});

/* namespace */
window.APjs = {};
APjs.admin = function() {};


APjs.admin.prototype = {

	/* automatically called */
	initialize: function() {
		this.saveOptions();
		this.renameTaxo();
		this.editPoints();
		this.savePoints();
		this.newPointForm();
		this.deletePoint();
		this.badges();
		this.deleteFlag();
	},

	saveOptions: function(){
		jQuery('#options_form').submit(function(){
			jQuery.each(jQuery(this).find('input:checkbox:not(:checked)'), function(index, val) {
				var name = jQuery(this).attr('name');
				console.log(name);
				var hidden = '_hidden_'+name;
				jQuery(this).attr('name', '');
				jQuery('input[name="'+ hidden + '"]').attr('name', name);
			});

			return true;
		});
	},
	renameTaxo: function(){
		jQuery('.ap-rename-taxo').click(function(e){
			e.preventDefault();

			jQuery.ajax({
				url: ajaxurl,
				data: {action: 'ap_taxo_rename'},
				context:this,
				success: function(data){
					jQuery(this).closest('.error').remove();
					location.reload();
				}
			});
			return false;
		});
	},
	editPoints:function(){
		jQuery('.wp-admin').delegate('[data-action="ap-edit-reputation"]', 'click', function(e){
			e.preventDefault();
			var id = jQuery(this).attr('href');
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'ap_edit_reputation',
					id: id
				},
				context:this,
				dataType:'json',
				success: function(data){
					if(data['status']){
						jQuery('#ap-reputation-edit').remove();
						jQuery('#anspress-reputation-table').hide();
						jQuery('#anspress-reputation-table').after(data['html']);
					}
				}
			});
		});
	},
	savePoints:function(){
		jQuery('.wp-admin').delegate('[data-action="ap-save-reputation"]', 'submit', function(e){
			e.preventDefault();
			jQuery('.button-primary', this).attr('disabled', 'disabled');
			var id = jQuery(this).attr('href');
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				cache: false,
				data:  jQuery(this).serialize({ checkboxesAsBools: true }),
				context:this,
				dataType:'json',
				success: function(data){
					if(data['status']){
						jQuery('.wrap').empty().html(data['html']);
					}
				}
			});

			return false;
		});
	},
	newPointForm:function(){
		jQuery('.wp-admin').delegate('[data-button="ap-new-reputation"]', 'click', function(e){
			e.preventDefault();
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:  {
					action: 'ap_new_reputation_form'
				},
				context:this,
				dataType:'json',
				success: function(data){
					jQuery('#ap-reputation-edit').remove();
					jQuery('#anspress-reputation-table').hide();
					jQuery('#anspress-reputation-table').after(data['html']);
				}
			});

			return false;
		});
	},
	deletePoint:function(){
		jQuery('.wp-admin').delegate('[data-button="ap-delete-reputation"]', 'click', function(e){
			e.preventDefault();
			var id = jQuery(this).attr('href');
			var args = jQuery(this).data('args');
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:  {
					action: 'ap_delete_reputation',
					args: args
				},
				context:this,
				dataType:'json',
				success: function(data){
					jQuery(this).closest('tr').slideUp(200);
				}
			});

			return false;
		});
	},
	badges:function(){
		jQuery('.wp-admin').delegate('[data-action="ap-edit-badge"]', 'click', function(e){
			e.preventDefault();
			var id = jQuery(this).attr('href');
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: {
					action: 'ap_edit_badges',
					id: id
				},
				context:this,
				dataType:'json',
				success: function(data){
					if(data['status']){
						jQuery('#ap-badge-edit').remove();
						jQuery('#anspress-badge-table').hide();
						jQuery('#anspress-badge-table').after(data['html']);
					}
				}
			});
		});

		jQuery('.wp-admin').delegate('[data-action="ap-save-badge"]', 'submit', function(e){
			e.preventDefault();
			jQuery('.button-primary', this).attr('disabled', 'disabled');
			var id = jQuery(this).attr('href');
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:  jQuery(this).serialize({ checkboxesAsBools: true }),
				context:this,
				dataType:'json',
				success: function(data){
					if(data['status']){
						jQuery('.wrap').empty().html(data['html']);
					}
				}
			});

			return false;
		});
		jQuery('.wp-admin').delegate('[data-button="ap-new-badge"]', 'click', function(e){
			e.preventDefault();
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:  {
					action: 'ap_new_badge_form'
				},
				context:this,
				dataType:'json',
				success: function(data){
					jQuery('#ap-badge-edit').remove();
					jQuery('#anspress-badge-table').hide();
					jQuery('#anspress-badge-table').after(data['html']);
				}
			});

			return false;
		});
		jQuery('.wp-admin').delegate('[data-button="ap-delete-badge"]', 'click', function(e){
			e.preventDefault();
			var id = jQuery(this).attr('href');
			var args = jQuery(this).data('args');
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:  {
					action: 'ap_delete_badge',
					args: args
				},
				context:this,
				dataType:'json',
				success: function(data){
					jQuery(this).closest('tr').slideUp(200);
				}
			});

			return false;
		});
	},


	deleteFlag : function(){
		jQuery('[data-action="ap-delete-flag"]').click(function(e){
			e.preventDefault();
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data:  jQuery(this).attr('href'),
				context:this,
				success: function(data){
					jQuery(this).closest('.flag-item').remove();
				}
			});
		});
	}
}

function ap_option_flag_note(){
	jQuery('body').delegate('[data-action="ap_add_field"]', 'click', function(){
		var copy 	= jQuery(this).data('copy');
		var field_c = jQuery(this).data('field');
		var count = (jQuery(field_c+' > div').length)+1;
		var html = jQuery('#'+copy+' .ap-repeatbable-field').html();
		html = html.replace('#', count).replace('#', count).replace('#', count);
		jQuery(html).appendTo('#'+field_c);

	});
	jQuery('body').delegate('[data-action="ap_delete_field"]', 'click', function(){
		var toggle = jQuery(this).data('toggle');

		jQuery('#'+toggle).remove();
	});
}

if(typeof wpNavMenu != 'undefined'){
	wpNavMenu.addApLink = function( processMethod ) {
		var $checked = jQuery('.aplinks ul .menu-item-title input[type="radio"]:checked');
		var url = $checked.data('url'),
			label = $checked.data('title');


		processMethod = wpNavMenu.addMenuItemToBottom;

		// Show the ajax spinner
		jQuery('.aplinks .spinner').show();
		this.addLinkToMenu( url, label, processMethod, function() {
			// Remove the ajax spinner
			jQuery('.aplinks .spinner').hide();
		});
	};
}
function ap_submit_menu(){
	// Show the ajax spinner
	jQuery('.aplinks #submit-aplinks').click(function(){
		wpNavMenu.addApLink( wpNavMenu.addMenuItemToBottom );
	});


}
function ap_add_link_to_menu(url, label, callback) {
	callback = callback || function(){};

	ap_add_item_to_menu({
		'-1': {
			'menu-item-type': 'custom',
			'menu-item-url': url,
			'menu-item-title': label
		}
	}, callback);
}
function ap_add_item_to_menu(menuItem, callback) {
	var menu = jQuery('#menu').val(),
		nonce = jQuery('#menu-settings-column-nonce').val(),
		params;

	callback = callback || function(){};

	params = {
		'action': 'add-menu-item',
		'menu': menu,
		'menu-settings-column-nonce': nonce,
		'menu-item': menuItem
	};

	jQuery.post( ajaxurl, params, function(menuMarkup) {
		var ins = jQuery('#menu-instructions');
		menuMarkup = jQuery.trim( menuMarkup ); // Trim leading whitespaces

		// Make it stand out a bit more visually, by adding a fadeIn
		jQuery( 'li.pending' ).hide().fadeIn('slow');
		jQuery( '.drag-instructions' ).show();
		if( ! ins.hasClass( 'menu-instructions-inactive' ) && ins.siblings().length )
			ins.addClass( 'menu-instructions-inactive' );

		callback();
	});
}
jQuery(document).ready(function (jQuery){

	jQuery('#select-question-for-answer').on('keyup', function(){
		if(jQuery.trim(jQuery(this).val()) == '')
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
			success: function(data){
				if(typeof data['html'] !== 'undefined')
					jQuery('#similar_suggestions').html(data['html']);
			},
			dataType: 'json',
			context: this,
		});
	});

	ap_option_flag_note();
	ap_submit_menu();

	jQuery('.ap-dynamic-avatar').initial({fontSize:14, fontWeight:600});
    jQuery( document ).ajaxComplete(function( event, data, settings ) {
        jQuery('.ap-dynamic-avatar').initial({fontSize:14, fontWeight:600});
    });

    jQuery('#ap-category-upload').click(function(e) {
    	e.preventDefault();
		var image = wp.media({
			title: 'Upload Image',
			// mutiple: true if you want to upload multiple files at once
			multiple: false
		}).open().on('select', function(e){
			// This will return the selected image from the Media Uploader, the result is an object
			var uploaded_image = image.state().get('selection').first();
			// We convert uploaded_image to a JSON object to make accessing it easier
			// Output to the console uploaded_image
			console.log(uploaded_image);
			var image_url = uploaded_image.toJSON().url;
			var image_id = uploaded_image.toJSON().id;
			// Let's assign the url value to the input field
			jQuery('#ap_category_media_url').val(image_url);
			jQuery('#ap_category_media_id').val(image_id);
			jQuery('#ap_category_media_url').before('<img id="ap_category_media_preview" src="'+image_url+'" />');
		});
	});

	jQuery('#ap-category-upload-remove').click(function(e){
		e.preventDefault();
		jQuery('#ap_category_media_url').val('');
		jQuery('#ap_category_media_id').val('');
		jQuery('#ap_category_media_preview').remove();
	});

	jQuery(document).ready(function($){
		if(typeof wpColorPicker !== 'undefined')
	   		jQuery('#ap-category-color').wpColorPicker();
	});

	jQuery('.checkall').click(function(){
		var checkbox = jQuery(this).closest('.ap-tools-ck').find('input[type="checkbox"]:not(.checkall)');
		checkbox.prop('checked', jQuery(this).prop("checked"));
	})

	jQuery('#' + jQuery('#ap-tools-selectroles').val()).slideDown();

	jQuery('#ap-tools-selectroles').change(function(){
		var id = '#' + jQuery(this).val();
		jQuery('.ap-tools-roleitem').hide();
		jQuery(id).fadeIn(300);
	})

});

