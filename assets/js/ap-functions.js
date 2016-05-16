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



/**
 * https://github.com/melanke/Watch.JS
 **/
"use strict";(function(t){"object"===typeof exports?module.exports=t():"function"===typeof define&&define.amd?define(t):(window.WatchJS=t(),window.watch=window.WatchJS.watch,window.unwatch=window.WatchJS.unwatch,window.callWatchers=window.WatchJS.callWatchers)})(function(){function t(){u=null;for(var a=0;a<v.length;a++)v[a]();v.length=0}var k={noMore:!1,useDirtyCheck:!1},p=[],l=[],w=[],C=!1;try{C=Object.defineProperty&&Object.defineProperty({},"x",{})}catch(Y){}var x=function(a){var b={};return a&&"[object Function]"==b.toString.call(a)},g=function(a){return"[object Array]"===Object.prototype.toString.call(a)},y=function(a){return"[object Object]"==={}.toString.apply(a)},H=function(a,b){var c=[],d=[];if("string"!=typeof a&&"string"!=typeof b){if(g(a))for(var e=0;e<a.length;e++)void 0===b[e]&&c.push(e);else for(e in a)a.hasOwnProperty(e)&&void 0===b[e]&&c.push(e);if(g(b))for(var f=0;f<b.length;f++)void 0===a[f]&&d.push(f);else for(f in b)b.hasOwnProperty(f)&&void 0===a[f]&&d.push(f)}return{added:c,removed:d}},q=function(a){if(null==a||"object"!=typeof a)return a;var b=a.constructor(),c;for(c in a)b[c]=a[c];return b},R=function(a,b,c,d){try{Object.observe(a,function(a){a.forEach(function(a){a.name===b&&d(a.object[a.name])})})}catch(e){try{Object.defineProperty(a,b,{get:c,set:function(a){d.call(this,a,!0)},enumerable:!0,configurable:!0})}catch(f){try{Object.prototype.__defineGetter__.call(a,b,c),Object.prototype.__defineSetter__.call(a,b,function(a){d.call(this,a,!0)})}catch(h){I(a,b,d)}}}},J=function(a,b,c){try{Object.defineProperty(a,b,{enumerable:!1,configurable:!0,writable:!1,value:c})}catch(d){a[b]=c}},I=function(a,b,c){l[l.length]={prop:b,object:a,orig:q(a[b]),callback:c}},n=function(a,b,c,d){if("string"!=typeof a&&(a instanceof Object||g(a))){if(g(a)){if(K(a,"__watchall__",b,c),void 0===c||0<c)for(var e=0;e<a.length;e++)n(a[e],b,c,d)}else{var f=[];for(e in a)"$val"==e||!C&&"watchers"===e||Object.prototype.hasOwnProperty.call(a,e)&&f.push(e);B(a,f,b,c,d)}d&&L(a,"$$watchlengthsubjectroot",b,c)}},B=function(a,b,c,d,e){if("string"!=typeof a&&(a instanceof Object||g(a)))for(var f=0;f<b.length;f++)D(a,b[f],c,d,e)},D=function(a,b,c,d,e){"string"!=typeof a&&(a instanceof Object||g(a))&&!x(a[b])&&(null!=a[b]&&(void 0===d||0<d)&&n(a[b],c,void 0!==d?d-1:d),K(a,b,c,d),e&&(void 0===d||0<d)&&L(a,b,c,d))},S=function(a,b){if(!(a instanceof String)&&(a instanceof Object||g(a)))if(g(a)){for(var c=["__watchall__"],d=0;d<a.length;d++)c.push(d);E(a,c,b)}else{var e=function(a){var c=[],d;for(d in a)a.hasOwnProperty(d)&&(a[d]instanceof Object?e(a[d]):c.push(d));E(a,c,b)};e(a)}},E=function(a,b,c){for(var d in b)b.hasOwnProperty(d)&&M(a,b[d],c)},v=[],u=null,N=function(){u||(u=setTimeout(t));return u},O=function(a){null==u&&N();v[v.length]=a},F=function(a,b,c,d){var e=null,f=-1,h=g(a);n(a,function(d,c,r,m){var g=N();f!==g&&(f=g,e={type:"update"},e.value=a,e.splices=null,O(function(){b.call(this,e);e=null}));if(h&&a===this&&null!==e){if("pop"===c||"shift"===c)r=[],m=[m];else if("push"===c||"unshift"===c)r=[r],m=[];else if("splice"!==c)return;e.splices||(e.splices=[]);e.splices[e.splices.length]={index:d,deleteCount:m?m.length:0,addedCount:r?r.length:0,added:r,deleted:m}}},1==c?void 0:0,d)},T=function(a,b,c,d,e){a&&b&&(D(a,b,function(a,b,A,k){a={type:"update"};a.value=A;a.oldvalue=k;(d&&y(A)||g(A))&&F(A,c,d,e);c.call(this,a)},0),(d&&y(a[b])||g(a[b]))&&F(a[b],c,d,e))},K=function(a,b,c,d){var e=!1,f=g(a);a.watchers||(J(a,"watchers",{}),f&&U(a,function(c,e,f,h){P(a,c,e,f,h);if(0!==d&&f&&(y(f)||g(f))){var k,l;c=a.watchers[b];if(h=a.watchers.__watchall__)c=c?c.concat(h):h;l=c?c.length:0;for(h=0;h<l;h++)if("splice"!==e)n(f,c[h],void 0===d?d:d-1);else for(k=0;k<f.length;k++)n(f[k],c[h],void 0===d?d:d-1)}}));a.watchers[b]||(a.watchers[b]=[],f||(e=!0));for(f=0;f<a.watchers[b].length;f++)if(a.watchers[b][f]===c)return;a.watchers[b].push(c);if(e){var h=a[b];c=function(){return h};e=function(c,e){var f=h;h=c;if(0!==d&&a[b]&&(y(a[b])||g(a[b]))&&!a[b].watchers){var m,l=a.watchers[b].length;for(m=0;m<l;m++)n(a[b],a.watchers[b][m],void 0===d?d:d-1)}a.watchers&&(a.watchers.__wjs_suspend__||a.watchers["__wjs_suspend__"+b])?V(a,b):k.noMore||f===c||(e?P(a,b,"set",c,f):z(a,b,"set",c,f),k.noMore=!1)};k.useDirtyCheck?I(a,b,e):R(a,b,c,e)}},z=function(a,b,c,d,e){if(void 0!==b){var f,h=a.watchers[b];if(f=a.watchers.__watchall__)h=h?h.concat(f):f;f=h?h.length:0;for(var g=0;g<f;g++)h[g].call(a,b,c,d,e)}else for(b in a)a.hasOwnProperty(b)&&z(a,b,c,d,e)},Q="pop push reverse shift sort slice unshift splice".split(" "),W=function(a,b,c,d){J(a,c,function(){var e=0,f,h,g;if("splice"===c){g=arguments[0];h=a.slice(g,g+arguments[1]);f=[];for(e=2;e<arguments.length;e++)f[e-2]=arguments[e];e=g}else f=0<arguments.length?arguments[0]:void 0;g=b.apply(a,arguments);"slice"!==c&&("pop"===c?(h=g,e=a.length):"push"===c?e=a.length-1:"shift"===c?h=g:"unshift"!==c&&void 0===f&&(f=g),d.call(a,e,c,f,h));return g})},U=function(a,b){if(x(b)&&a&&!(a instanceof String)&&g(a))for(var c=Q.length,d;c--;)d=Q[c],W(a,a[d],d,b)},M=function(a,b,c){if(void 0===c&&a.watchers[b])delete a.watchers[b];else for(var d=0;d<a.watchers[b].length;d++)a.watchers[b][d]==c&&a.watchers[b].splice(d,1);for(d=0;d<p.length;d++){var e=p[d];e.obj==a&&e.prop==b&&e.watcher==c&&p.splice(d,1)}for(c=0;c<l.length;c++)d=l[c],e=d.object.watchers,(d=d.object==a&&d.prop==b&&e&&(!e[b]||0==e[b].length))&&l.splice(c,1)},V=function(a,b){O(function(){delete a.watchers.__wjs_suspend__;delete a.watchers["__wjs_suspend__"+b]})},G=null,P=function(a,b,c,d,e){w[w.length]={obj:a,prop:b,mode:c,newval:d,oldval:e};null===G&&(G=setTimeout(X))},X=function(){var a=null;G=null;for(var b=0;b<w.length;b++)a=w[b],z(a.obj,a.prop,a.mode,a.newval,a.oldval);a&&(w=[])},L=function(a,b,c,d){var e;e="$$watchlengthsubjectroot"===b?q(a):q(a[b]);p.push({obj:a,prop:b,actual:e,watcher:c,level:d})};setInterval(function(){for(var a=0;a<p.length;a++){var b=p[a];if("$$watchlengthsubjectroot"===b.prop){var c=H(b.obj,b.actual);if(c.added.length||c.removed.length)c.added.length&&B(b.obj,c.added,b.watcher,b.level-1,!0),b.watcher.call(b.obj,"root","differentattr",c,b.actual);b.actual=q(b.obj)}else{c=H(b.obj[b.prop],b.actual);if(c.added.length||c.removed.length){if(c.added.length)for(var d=0;d<b.obj.watchers[b.prop].length;d++)B(b.obj[b.prop],c.added,b.obj.watchers[b.prop][d],b.level-1,!0);z(b.obj,b.prop,"differentattr",c,b.actual)}b.actual=q(b.obj[b.prop])}}for(a in l){var b=l[a],c=b.object[b.prop],d=b.orig,e=c,f=void 0,g=!0;if(d!==e)if(y(d))for(f in d){if((C||"watchers"!==f)&&d[f]!==e[f]){g=!1;break}}else g=!1;g||(b.orig=q(c),b.callback(c))}},50);k.watch=function(){x(arguments[1])?n.apply(this,arguments):g(arguments[1])?B.apply(this,arguments):D.apply(this,arguments)};k.unwatch=function(){x(arguments[1])?S.apply(this,arguments):g(arguments[1])?E.apply(this,arguments):M.apply(this,arguments)};k.callWatchers=z;k.suspend=function(a,b){a.watchers&&(a.watchers["__wjs_suspend__"+(void 0!==b?b:"")]=!0)};k.onChange=function(){(x(arguments[2])?T:F).apply(this,arguments)};return k});
var apObjectWatching = {};

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
