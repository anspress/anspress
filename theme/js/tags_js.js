function ap_tags_item(items){
	tagsitems = '';
	jQuery.each(items, function(i){
		tagsitems += '<li><a href=# data-action="ap-add-tag" data-name="'+ this +'">'+ this +'</a></li>';
	});
	return tagsitems;
}

jQuery(document).ready(function(){
	if(jQuery('[data-role="ap-tagsinput"]').length > 0){

		jQuery('[data-role="ap-tagsinput"]').tagsinput({
			freeInput: true,
			addOnBlur: false,
			maxTags: ap_max_tags,
		});

		jQuery('[data-role="ap-tagsinput"]').tagsinput('input').blur(function(e){
			jQuery(document).mouseup(function (e){
				var container = jQuery('#ap-suggestions');

				if (!container.is(e.target)	&& container.has(e.target).length === 0){
					container.hide();
				}
			});
		});

	}

	jQuery('body').delegate('.bootstrap-tagsinput input', 'keyup', function(){
		var value = jQuery(this).val();

		if(value.length == 0)
			return;

		/* abort previous ajax request */
		if(typeof tagsquery !== 'undefined'){
			tagsquery.abort();
		}

		AnsPress.site.showLoading(this);

		tagsquery = jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: {
				action:'ap_suggest_tags',
				q: value
			},
			context:this,
			dataType:'json',
			success: function(data){
				AnsPress.site.hideLoading(this);
				console.log(data);
				if(!data.status)
					return;

				var container = jQuery(this).closest('.bootstrap-tagsinput'),
					position = container.offset();

				if(jQuery('#ap-tag-suggestions').length ==0)
					jQuery('body').append('<ul id="ap-tag-suggestions" class="ap-tag-suggestions" style="display:none"></ul>');

				if(data['items']){
					var html = ap_tags_item(data['items']);
					jQuery('#ap-tag-suggestions').html(html).css({'top': (position.top + container.height() + 20), 'left': position.left}).show();
				}

			}
		});
	});

	jQuery('body').delegate('[data-action="ap-add-tag"]', 'click touchstart', function(e){
		e.preventDefault();
		jQuery('[data-role="ap-tagsinput"]').tagsinput('add', jQuery(this).attr('data-name'));
		jQuery('[data-role="ap-tagsinput"]').tagsinput('input').val('');
		jQuery('#ap-tag-suggestions').hide();
	});

	jQuery('body').delegate('#ask_form .ap-btn-submit', 'click', function() {
		console.log(jQuery('.bootstrap-tagsinput input').val());
        jQuery('[data-role="ap-tagsinput"]').tagsinput('add', jQuery('.bootstrap-tagsinput input').val());
        jQuery('.bootstrap-tagsinput input').val('')
    });

});

