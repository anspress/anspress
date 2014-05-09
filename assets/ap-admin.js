

function ap_option_flag_note(){
	jQuery('#add-flag-note').click(function(e){
		e.preventDefault();
		var count = (jQuery('.flag-note-ite').length)+1;
		var clone = jQuery('#first-note').clone().removeAttr('id');
		clone.find('input').attr('value', '');
		clone.find('input').attr('name', 'anspress_opt[flag_note]['+count+'][title]');
		clone.find('textarea').attr('name', 'anspress_opt[flag_note]['+count+'][description]');
		clone.find('textarea').text('');
		jQuery(clone).insertBefore(this);
	});
	jQuery('body').delegate('.delete-flag-note', 'click', function(){
		jQuery(this).parent().remove();
	});
}

// Tab for option page
function ap_tab(){
	
	jQuery('#ap_opt_nav li a').click(function(e){
		e.preventDefault();
		jQuery('#ap_opt_nav li').removeClass('active');
		jQuery(this).parent().addClass('active');
		var t = jQuery(this).attr('href');
		jQuery('.tab-content .tab-pane').removeClass('active');
		jQuery(t).addClass('active');
		
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

	jQuery( "#ap_q_search" ).autocomplete({
		source: function( request, response ) {  
			jQuery.getJSON( ajaxurl + "?callback=?&action=search_questions", request, function( data ) {  
				response( jQuery.map( data, function( item ) {
					jQuery.each( item, function( i, val ) {
						val.label = val.url; // build result for autocomplete from suggestion array data
					} );
					return item;
				} ) );
		  } );  
		},
		minLength: 2,
		select: function( event, ui ) {
			jQuery('#ap_q').val(ui.item.id);
		},
	});
	
	ap_option_flag_note();
	ap_tab();
	ap_submit_menu();
	//wpNavMenu.init();
}); 

