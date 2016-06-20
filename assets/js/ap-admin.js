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

(function ($) {
	APjs.admin.prototype = {

		/* automatically called */
		initialize: function() {
			this.renameTaxo();
			this.editPoints();
			this.savePoints();
			this.newPointForm();
			this.deletePoint();
			this.badges();
			this.deleteFlag();
			this.ajaxBtn();
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
		},

		ajaxBtn: function(){
			$('.ap-ajax-btn').on('click', function(e) {
	            e.preventDefault();
	            var q = $(this).apAjaxQueryString();

	            $.ajax({
	            	url: ajaxurl,
	            	data: q,
	            	context: this,
	            	type: 'POST',
	            	success: function(data){
						if( typeof $(this).data('cb') !== 'undefined' ){
		                    var cb = $(this).data("cb");                       
		                    if( typeof APjs.admin[cb] === 'function' ){
		                        APjs.admin[cb](data, this);
		                    }
		                }
	            	}
	            });
	            
	        });
		},
		afterFlagClear : function(data, elm){
			$(elm).closest('tr').find('.column-flag .flag-count').text('0');
			$(elm).closest('tr').find('.column-flag .flag-count').removeClass('flagged');
			$(elm).remove();
		},
		replaceText: function(data, elm){
			$(elm).closest('li').find('strong').text(data);
		}

	}

})(jQuery);

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
	apLaodAvatar();

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
				var textJSON = jQuery(data).filter('#ap-response').html();
			    if( typeof textJSON !== 'undefined' && textJSON.length > 2 ){
			        data = JSON.parse(textJSON);
			    }
				console.log(data);
				if(typeof data['html'] !== 'undefined')
					jQuery('#similar_suggestions').html(data['html']);
			},
			context: this,
		});
	});

	ap_option_flag_note();
	ap_submit_menu();

	jQuery('.ap-dynamic-avatar').initial({fontSize:14, fontWeight:600});
    jQuery( document ).ajaxComplete(function( event, data, settings ) {
        jQuery('.ap-dynamic-avatar').initial({fontSize:14, fontWeight:600});
    });

    jQuery('[data-action="ap_media_uplaod"]').click(function(e) {
    	e.preventDefault();
    	$btn = jQuery(this);
		var image = wp.media({
			title: jQuery(this).data('title'),
			// mutiple: true if you want to upload multiple files at once
			multiple: false
		}).open().on('select', function(e){
			// This will return the selected image from the Media Uploader, the result is an object
			var uploaded_image = image.state().get('selection').first();
			// We convert uploaded_image to a JSON object to make accessing it easier
			// Output to the console uploaded_image
			var image_url = uploaded_image.toJSON().url;
			var image_id = uploaded_image.toJSON().id;

			// Let's assign the url value to the input field			
			jQuery($btn.data('urlc')).val(image_url);
			jQuery($btn.data('idc')).val(image_id);

			if(!jQuery($btn.data('urlc')).prev().is('img'))
				jQuery($btn.data('urlc')).before('<img id="ap_category_media_preview" src="'+image_url+'" />');
			else
				jQuery($btn.data('urlc')).prev().attr('src', image_url);
		});
	});

	jQuery('[data-action="ap_media_remove"]').click(function(e){
		e.preventDefault();
		jQuery('input[data-action="ap_media_value"]').val('');
		jQuery('img[data-action="ap_media_value"]').remove();
	});


	//if(typeof wpColorPicker !== 'undefined')
   		jQuery('#ap-category-color').wpColorPicker();


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

window.onload = function() {
	var lineOpt = {
        // Elements options apply to all of the options unless overridden in a dataset
        // In this case, we are setting the border of each horizontal bar to be 2px wide and green
        elements: {
            rectangle: {
                borderWidth: 2,
                borderColor: 'rgb(0, 255, 0)',
                borderSkipped: 'left'
            }
        },
        responsive: true,
        title: {
            display: false,
        },
        scales: {
	    	xAxes: [{	    			
                gridLines: {
                    color: "rgba(0, 0, 0, 0.05)",
                }
            }],
	    	yAxes: [{
	    		display: false,
                gridLines: {
                    color: "rgba(0, 0, 0, 0.05)",
                }   
            }]
	    }
    };
    
    if(typeof questionChartData !== 'undefined' ){
	    var ctx = document.getElementById("question-chart").getContext("2d");

	    window.myHorizontalBar = new Chart(ctx, {
	        type: 'doughnut',

	        data: questionChartData,
	        options: {
	        	legend: {
		        	display: false
		        },
	        	scales: {
			    	xAxes: [{
		    			ticks: {display: false}
		            }],
			    	yAxes: [{
		    			ticks: {display: false}  
		            }]
			    }
			}
	    });
	}

    if(typeof answerChartData !== 'undefined'){
	    var answerCtx = document.getElementById("answer-chart").getContext("2d");

	    window.myHorizontalBar = new Chart(answerCtx, {
        type: 'doughnut',

	        data: answerChartData,
	        options: {
	        	legend: {
		        	display: false
		        },
	        	scales: {
			    	xAxes: [{
		    			ticks: {display: false}
		            }],
			    	yAxes: [{
		    			ticks: {display: false}  
		            }]
			    }
			}
	    });
	}

    if(typeof latestanswerChartData!== 'undefined'){
	    var latestanswerCtx = document.getElementById("latestanswer-chart").getContext("2d");

	    window.latestanswer = new Chart(latestanswerCtx, {
	        type: 'line',
	        data: latestanswerChartData,
	        options: {        	
	        	legend: {
		        	display: false
		        },
	        	scales: {
			    	xAxes: [{
		    			display: false
		            }],
			    	yAxes: [{
		    			display: false
		            }]
			    }
			}
	    });
	}

	if(typeof latestquestionChartData!== 'undefined'){
	    var latestquestionCtx = document.getElementById("latestquestion-chart").getContext("2d");

	    window.latestquestion = new Chart(latestquestionCtx, {
	        type: 'line',
	        data: latestquestionChartData,
	        options: {        	
	        	legend: {
		        	display: false
		        },
	        	scales: {
			    	xAxes: [{
		    			display: false
		            }],
			    	yAxes: [{
		    			display: false
		            }]
			    }
			}
	    });
	}
};
