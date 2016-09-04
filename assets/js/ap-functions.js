/**
 * Contain general JavaScript functions used in AnsPress
 * @author Rahul Aryan
 * @license GPL 2+
 * @since 2.0
 */
 ;(function($){

 	$.fn.aptip = function(settings) {

 		var config = $.extend( {
 			'theme': '',
 			'delay': '',
 			'title': '',
 			'before': '',
 			'ajax': '',
 			'position': 'top center'
 		}, settings);


 		this.ajax_running = false;

 		var plug = this;

 		function altertitle(el){
 			if(typeof $(el).attr('title') !== 'undefined'){
 				$(el).data('aptiptitle', $(el).attr('title'));
 				$(el).removeAttr('title');
 			}
 		}

 		function position(el){
 			var winheight 	= $(window).height();
 			var offset 	= $(el).offset();
 			var height 	= $(el).outerHeight();
 			var width 	= $(el).outerWidth();
 			var tipposition 	= $(el).data('tipposition') || false;

 			var setpos = config.position.split(/ +/);

 			if( tipposition ){
 				setpos =  tipposition.split(/ +/);
 			}

 			var x = 'top';
 			switch(setpos[0]){
 				case 'bottom':
 				x = offset.top + height + 10;
 				break;

 				case 'center':
 				x = offset.top + (height/2)  - (tip.outerHeight()/2);
 				break;

 				default:
 				x = offset.top - tip.outerHeight() - 10;
 				break;
 			}

 			var y = 'right';
 			switch(setpos[1]){
 				case 'left':
 				y = offset.left - width;
 				break;

 				case 'center':
 				y = offset.left + (width/2)  - (tip.outerWidth()/2);
 				break;

 				default:
 				y = offset.left + width;
 				break;
 			}
 			tip.addClass('x-'+ setpos[0] +' y-'+setpos[1]);

 			var s = $(document).scrollTop();
 			
 			// Keep it inside window.
 			if( y < 0 )	y = 5;
 			if( s > x )	x = offset.top + width;

 			var inside_x = $(window).scrollTop() + winheight - tip.outerHeight();

 			if( x > inside_x )
 				x = x - tip.outerHeight();
 			
 			tip.css({
 				overflow: 'absolute',
 				top: x,
 				left: y
 			});

 			tip.find('.arrow').css({
 				top: x,
 				left: y
 			});
 		}

 		function showtip(el){
 			var elm = $(el);
            var id = elm.data('userid') || elm.data('catid') || false;
            var action = elm.data('action') || false;
            var tipquery = elm.data('tipquery') || false;
            
 	 		if( id && !tipquery ){
 	 			var is_term = elm.data('catid') || false;
 	 			is_term = is_term? '&type=cat' : '';
	 			elm.data('tipquery', 'action=ap_ajax&ap_ajax_action=hover_card&id='+ id+ is_term);
                tipquery = elm.data('tipquery');
	 		}

	 		if( tipquery !== false )
	 			config.ajax = tipquery;
	 		
 			altertitle(el);

 			if(config.title == ''){
 				var title = elm.data('aptiptitle');
 			}else{
 				var title = config.title;
 			}

 			if( title.length == 0 ){
 				return;
 			}

 			tip = $('<div class="ap-tooltip '+ config.theme +'"><div class="ap-tooltip-in">'+ title +'</div></div>');

 			if(config.ajax != '' && !plug.ajax_running){
 				if ( $(elm.attr('data-ajax')).length == 0 && $('#' + id + '_card').length == 0 ) {
 					plug.ajax_running = true;
	 				$.ajax({
	                    type: 'POST',
	                    url: ajaxurl,
	                    data: config.ajax+'&ap_ajax_nonce='+ap_nonce,
	                    success: function(data) {
	                    	var dataText = $(data);
					        var data = {};

					        //Parse response text JSON
					        var textJSON = dataText.filter('#ap-response').html();
					 
					        if( typeof textJSON !== 'undefined' && textJSON.length > 2 )
					            data = JSON.parse(textJSON);
					            if( (data.apTemplate||false) && 'object' === typeof data.apTemplate )
					            
					            apLoadTemplate(data.apTemplate.name, data.apTemplate.template, function(template){
				            		var html = $(Ta.render(template, data.apData));
				            		console.log(html);
				                	var count = parseInt( $('.aptip-data').length );
				                	plug.data_id = 'aptipd-'+ (count+1);
				                	html.addClass( 'aptip-data '+ plug.data_id );
				                	elm.attr('data-ajax', '.'+plug.data_id);
				                    $('body').append(html.clone());
				                    tip.find('.ap-tooltip-in').html(html.show());
				                    position(el);						                    	
					            });
	                    	
	                    	
	                        plug.ajax_running = false;
	                    }
	                });
	            }else{
	            	var html = $( '#' + id + '_card' ).html();
                    tip.find('.ap-tooltip-in').html( $(html).show() );
	            }

 			}

 			if(config.before != ''){
 				var before_callback = config.before;
 				before_callback(tip, el, function(){
 					position(el);
 				});
 			}

 			tip.appendTo('body');
 			position(el);
 		}

		this.each(function() {
			$this = $(this);

			var item = this;

			$this.mouseenter(function(){
				if(config.delay != ''){
					delay = setTimeout(function() {
						showtip(item);
					}, config.delay);
				}else{
					showtip(this);					
				}

			}).mouseleave(function(){
				if(typeof tip !== 'undefined')
					tip.remove();

				if(typeof delay !== 'undefined')
					clearTimeout( delay );
			})
		});

		return this;
 	}

	//pass in just the context as a $(obj) or a settings JS object
	$.fn.autogrow = function(opts) {
		var that = $(this).css({
			overflow: 'hidden',
			resize: 'none'
		}) //prevent scrollies
		,
		selector = that.selector,
		defaults = {
				context: $(document) //what to wire events to
				,
				animate: true //if you want the size change to animate
				,
				speed: 50 //speed of animation
				,
				fixMinHeight: true //if you don't want the box to shrink below its initial size
				,
				cloneClass: 'autogrowclone' //helper CSS class for clone if you need to add special rules
				,
				onInitialize: false //resizes the textareas when the plugin is initialized
			};
			opts = $.isPlainObject(opts) ? opts : {
				context: opts ? opts : $(document)
			};
			opts = $.extend({}, defaults, opts);
			that.each(function(i, elem) {
				var min, clone;
				elem = $(elem);
			//if the element is "invisible", we get an incorrect height value
			//to get correct value, clone and append to the body.
			if (elem.is(':visible') || parseInt(elem.css('height'), 10) > 0) {
				min = parseInt(elem.css('height'), 10) || elem.innerHeight();
			} else {
				clone = elem.clone().addClass(opts.cloneClass).val(elem.val()).css({
					position: 'absolute',
					visibility: 'hidden',
					display: 'block'
				});
				$('body').append(clone);
				min = clone.innerHeight();
				clone.remove();
			}
			if (opts.fixMinHeight) {
				elem.data('autogrow-start-height', min); //set min height
			}
			elem.css('height', min);
			if (opts.onInitialize && elem.length) {
				resize.call(elem[0]);
			}
		});
			opts.context.on('keyup paste', selector, resize);

			function resize(e) {
				var box = $(this),
				oldHeight = box.innerHeight(),
				newHeight = this.scrollHeight,
				minHeight = box.data('autogrow-start-height') || 0,
				clone;
			if (oldHeight < newHeight) { //user is typing
				this.scrollTop = 0; //try to reduce the top of the content hiding for a second
				opts.animate ? box.stop().animate({
					height: newHeight
				}, opts.speed) : box.innerHeight(newHeight);
			} else if (!e || e.which == 8 || e.which == 46 || (e.ctrlKey && e.which == 88)) { //user is deleting, backspacing, or cutting
				if (oldHeight > minHeight) { //shrink!
					//this cloning part is not particularly necessary. however, it helps with animation
					//since the only way to cleanly calculate where to shrink the box to is to incrementally
					//reduce the height of the box until the $.innerHeight() and the scrollHeight differ.
					//doing this on an exact clone to figure out the height first and then applying it to the
					//actual box makes it look cleaner to the user
					clone = box.clone()
					//add clone class for extra css rules
					.addClass(opts.cloneClass)
					//make "invisible", remove height restriction potentially imposed by existing CSS
					.css({
						position: 'absolute',
						zIndex: -10,
						height: ''
					})
					//populate with content for consistent measuring
					.val(box.val());
					box.after(clone); //append as close to the box as possible for best CSS matching for clone
					do { //reduce height until they don't match
					newHeight = clone[0].scrollHeight - 1;
					clone.innerHeight(newHeight);
				} while (newHeight === clone[0].scrollHeight);
					newHeight++; //adding one back eliminates a wiggle on deletion
					clone.remove();
					box.focus(); // Fix issue with Chrome losing focus from the textarea.
					//if user selects all and deletes or holds down delete til beginning
					//user could get here and shrink whole box
					newHeight < minHeight && (newHeight = minHeight);
					oldHeight > newHeight && opts.animate ? box.stop().animate({
						height: newHeight
					}, opts.speed) : box.innerHeight(newHeight);
				} else { //just set to the minHeight
					box.innerHeight(minHeight);
				}
			}
		}
		return that;
	};

	$.fn.center = function () {

		this.css({"position":"fixed"});
		if($(window).height() > $(this).outerHeight()){
			this.css("top", Math.max(0, ($(window).height() - $(this).outerHeight()) / 2) + "px");
		}else{
			this.css("top", 50 );
			this.css("height", $(window).height()- 80 );
		}

		this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +
			$(window).scrollLeft()) + "px");
		return this;
	}

	$.fn.apAjaxQueryString = function () {
		var query = $(this).data('query').split("::");		
		
		var newQuery = {};
	
		newQuery['action'] = 'ap_ajax';
		newQuery['ap_ajax_action'] = query[0];
		newQuery['__nonce'] = query[1];
		newQuery['args'] = {};

		var newi = 0;
		$.each(query,function(i){
			if(i != 0 && i != 1){
		   		newQuery['args'][newi] = query[i];
		   		newi++;
			}		   
		});

		return newQuery;
	}

	$.fn.apGetSelector = function(){
	  var e = $(this);

	  // the `id` attribute *should* be unique.
	  if (e.attr('id')) { return '#'+e.attr('id') }

	  if (e.attr('secondary_id')) {
	    return '[secondary_id='+e.attr('secondary_id')+']'
	  }

	  $(e).attr('secondary_id', (new Date()).getTime());

	  return '[secondary_id='+e.attr('secondary_id')+']'
	};
})(jQuery);

/**
 * For returning default value if passed value is undefined.
 * @param  {mixed} $value   A value to check
 * @param  {mixed} $default return this if $value is undefined
 * @return {string}
 * @since 2.0
 **/
 function ap_default($value, $default){
 	if(typeof $value !== 'undefined')
 		return $value;

 	return $default;
 }

 function apLoadingDot(){
 	i = 0;
 	setInterval(function() {
 		jQuery('.ap-loading-dot').html( Array( (++i % 4)+1 ).join('.') );
 	}, 300);
 }

 function apAjaxData(param) {
 	param = param + '&action=ap_ajax';
 	return param;
 }

 function apQueryStringToJSON(string) {
 	var pairs = string.split('&');
 	var result = {};
 	pairs.forEach(function(pair) {
 		pair = pair.split('=');
 		result[pair[0]] = encodeURIComponent(pair[1] || '');
 	});
 	return JSON.parse(JSON.stringify(result));
 }

 function apGetValueFromStr(q, name) {
 	name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
 	var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
 	results = regex.exec(q);
 	return results == null ? false : decodeURIComponent(results[1].replace(/\+/g, " "));
 }

 function apCenterBox(elm){
 	var elm         = jQuery(elm);
 	var parent      = elm.parent();

 	parent.css({position: 'relative'});

 	elm.css("left", (parent.width()-elm.width())/2);
 	elm.css("top", (parent.height()-elm.height())/2);
 }

function apIsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

function apLoadTemplate(name, template, cb) {
	cb = cb || false;	
	if(jQuery('#template-'+name).length === 0){
		jQuery.get(template, function(data) {
			jQuery('<script id="template-'+name+'" type="text/html">'+ data +'</script>').appendTo('body');
			jQuery(name).trigger(name, data);
			if(cb) cb(data);
		});
	}else{
		if(cb) 
			cb(jQuery('#template-'+name).html());
	}
}

function apParseAjaxResponse(data){
	if(apIsJsonString(data))
		return {};
	
    data = jQuery(data);    
    if( typeof data.filter('#ap-response') === 'undefined' ){
        console.log('Not a valid AnsPress ajax response.');
        return {};
    }

    var textJSON = data.filter('#ap-response').html();
    if( typeof textJSON !== 'undefined' && textJSON.length > 2 ){
        return JSON.parse(textJSON);
    }
    return {};
}

/**
 * Tangular.js
 * https://github.com/petersirka/Tangular
 */
var Tangular={};Tangular.helpers={},Tangular.version="v1.5.2",Tangular.cache={},Tangular.debug=!1,Tangular.settings={delimiters:["{{","}}"]},Tangular.register=function(r,n){return Tangular.helpers[r]=n,Tangular},Tangular.compile=function(r){r||(r="");for(var n,e=-1,a=[],t=-1,u=0,i=r.length,l=0,s=Tangular.settings.delimiters[0].length;i>e++;){var g=r.substring(e,e+s);if(-1===t)if(g!==Tangular.settings.delimiters[0]);else{if(-1!==t){l++;continue}n=r.substring(u,e),a.push(n?'unescape("'+escape(n)+'")':'""'),t=e+s}else if(g===Tangular.settings.delimiters[1]){if(l>0){l--;continue}a.push(r.substring(t,e).trim()),u=e+s,t=-1;continue}}n=r.substring(u,i),a.push(n?'unescape("'+escape(n)+'")':'""'),i=a.length;for(var o="$output+=",f='var $s=this,$output="",$t,$v;',c=[],p=!1,d=0,T=0;i>T;T++)if(T%2!==0){var h=a[T],$=!1,e=h.lastIndexOf("|"),v=null,b=h.substring(0,3);"if "===b&&(h="if( "+h.substring(3)+"){",$=!0),"foreach "===h.substring(0,8)&&(v=h.split(" "),"var"===v[1]&&v.splice(1,1),c.push(v[1]),$=!0,p=!0,d++);var m=h.substring(0,5);if("endif"===m||"fi"===h?(h="}",$=!0):"else"===m?(h="} else {",$=!0):"else if"===h.substring(0,7)?(h="}else if( "+h.substring(8)+"){",$=!0):("end"===b||"endfor"===h.substring(0,6))&&(h=c.length?"}})()}":"}}",c.pop(),$=!0,d--,0===d&&(p=!1)),h=$?Tangular.append(h,c,p,"$s").trim():Tangular.helper(h,c,p,"$s"),$){if(v){var w=Tangular.append(v[3],c,p,"$s");h="if ("+w+"&&"+w+".length){(function(){for(var i=0,length="+w+".length;i<length;i++){var "+v[1]+"="+w+"[i];var $index=i;"}f+=h}else f+=o+h+";"}else f+=o+a[T]+";";var x=59===f.charCodeAt(f.length-1);return Tangular.debug&&(console.log("Tangular:"),console.log("function(helpers,$) {"+f+(x?"":";")+"return $output;"),console.log(r.trim()),console.log("---------------------------"),console.log("")),function(r,n){return new Function("helpers","$",f+(x?"":";")+"return $output;").call(r,function(r){var n=Tangular.helpers[r];return n?n:(console.warn('Tangular helper "'+r+'" not found.'),function(r){return void 0===r?"undefined":null===r?"null":r.toString()})},n)}},Tangular.helper=function(r,n,e){var a,t=r.indexOf("|");if(-1===t)return a=Tangular.append(r.trim(),n,e,"$s").trim(),'helpers("encode").call($s,'+a+")";a=Tangular.append(r.substring(0,t).trim(),n,e).trim(),r=r.substring(t+1).trim().split("|");for(var u="",i=0,l=r.length;l>i;i++){var s,g=r[i].trim().replace("()","");t=g.indexOf("("),-1===t?(s=g,g=".call($s,$t)"):(s=g.substring(0,t),g=".call($s,$t,"+g.substring(t+1)),g='$t=helpers("'+s+'")'+g,u+=g+";"}return'"";$t='+a+";"+u+"$output+=$t"},Tangular.append=function(r,n,e,a){return void 0===n&&(n=[]),r?r.replace(/[\_\$a-zá-žÁ-ŽA-Z0-9\s\.]+/g,function(r,e,t){var u=t.substring(e-1,e),i=!1,l=r.trim();switch(('"'===u||"'"===u||"."===u)&&(i=!0),r.trim()){case"else":case"end":case"endfor":case"endif":case"fi":case"foreach":case"if":case"else if":return r;case"$index":if(!i)return r}if(""===l)return"";if(i)return r;i=!1;for(var s=0,g=n.length;g>s;s++){var o=n[s].length;if(l.substring(0,o)===n[s]){if(l.length!==o){var u=l.substring(o,o+1);if("."!==u&&"+"!==u)continue}i=!0;break}}if("$"===l)return"Tangular.$wrap($)";if("$."===l.substring(0,2)&&(a="$",l=l.substring(2)),i)return l;u=l.substring(0,1);var f=u.charCodeAt(0);return f>47&&58>f?l:"Tangular.$wrap("+(a||"$s")+',"'+l+'")'}):"Tangular.$wrap("+(a||"$s")+")"},Tangular.$wrap=function(r,n,e){if(!r)return r;if(!n)return r;var a=Tangular.cache[n];if(null===a)return r[n];if(a||(a=n.split("."),Tangular.cache[n]=1===a.length?null:a),1===a.length)return r[n];for(var t=r,u=0,i=a.length;i>u;u++){var l=a[u];if(t=t[l],!t)return u+1===i?t:e}return t},Tangular.render=function(r,n,e){return(void 0===n||null===n)&&(n={}),"string"==typeof r&&(r=Tangular.compile(r)),r(n,e)},Tangular.register("encode",function(r){return(void 0===r||null===r)&&(r=""),r.toString().replace(/[<>&"]/g,function(r){switch(r){case"&":return"&amp;";case"<":return"&lt;";case">":return"&gt;";case'"':return"&quot;"}return r})}),Tangular.register("raw",function(r){return(void 0===r||null===r)&&(r=""),r}),"undefined"!=typeof global?global.Tangular=global.Ta=Tangular:"undefined"!=typeof window&&(window.Tangular||(window.Tangular=Tangular),window.Ta=Tangular);
//Tangular.debug = true;
Ta.register('objLength', function(obj) {
    return Object.keys(obj).length;
});

function apCamelize(str) {
  return str.replace(/(?:^\w|[A-Z]|\b\w)/g, function(letter, index) {
    return index == 0 ? letter.toLowerCase() : letter.toUpperCase();
  }).replace(/\s+/g, '');
}

function apMergeObj(obj1,obj2){
  for (var p in obj2) {
    try {
      // Property in destination object set; update its value.
      if ( obj2[p].constructor==Object ) {
        obj1[p] = MergeRecursive(obj1[p], obj2[p]);

      } else {
        obj1[p] = obj2[p];

      }

    } catch(e) {
      // Property in destination object not set; create it and set its value.
      obj1[p] = obj2[p];
    }
  }

  return obj1;
}
function apLaodAvatar(){	
    jQuery("img[src*='ANSPRESS_AVATAR_SRC']").each(function(index, el) {
        var name = jQuery(el).attr('src').replace('http://ANSPRESS_AVATAR_SRC::', '');
        jQuery(el).initial({fontSize:30, fontWeight:600, name: name });
    });
}



