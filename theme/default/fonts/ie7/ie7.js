/* To avoid CSS expressions while still supporting IE 7 and IE 6, use this script */
/* The script tag referring to this file must be placed before the ending body tag. */

/* Use conditional comments in order to target IE 7 and older:
	<!--[if lt IE 8]><!-->
	<script src="ie7/ie7.js"></script>
	<!--<![endif]-->
*/

(function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'anspress\'">' + entity + '</span>' + html;
	}
	var icons = {
		'aicon-thumbs-up': '&#xe600;',
		'aicon-thumbs-up2': '&#xe601;',
		'aicon-question': '&#xe602;',
		'aicon-info': '&#xe603;',
		'aicon-flag': '&#xe604;',
		'aicon-attachment': '&#xe605;',
		'aicon-eye': '&#xe606;',
		'aicon-eye-blocked': '&#xe607;',
		'aicon-link': '&#xe608;',
		'aicon-bubble': '&#xe609;',
		'aicon-bubbles': '&#xe60a;',
		'aicon-wrench': '&#xe60b;',
		'aicon-cog': '&#xe60c;',
		'aicon-pie': '&#xe60d;',
		'aicon-spinner': '&#xe60e;',
		'aicon-spinner2': '&#xe60f;',
		'aicon-user': '&#xe610;',
		'aicon-users': '&#xe611;',
		'aicon-switch': '&#xe612;',
		'aicon-power-cord': '&#xe613;',
		'aicon-star': '&#xe614;',
		'aicon-menu': '&#xe625;',
		'aicon-menu2': '&#xe626;',
		'aicon-star2': '&#xe615;',
		'aicon-tag': '&#xe624;',
		'aicon-tags': '&#xe629;',
		'aicon-close': '&#xe61d;',
		'aicon-arrow-down': '&#xe616;',
		'aicon-arrow-up': '&#xe617;',
		'aicon-arrow-up2': '&#xe618;',
		'aicon-arrow-down2': '&#xe619;',
		'aicon-menu3': '&#xe61a;',
		'aicon-arrow-down3': '&#xe61b;',
		'aicon-arrow-up3': '&#xe61c;',
		'aicon-clock': '&#xe61e;',
		'aicon-palette': '&#xe61f;',
		'aicon-pencil': '&#xe62a;',
		'aicon-chevron-up': '&#xe620;',
		'aicon-chevron-down': '&#xe621;',
		'aicon-folder-close': '&#xe622;',
		'aicon-folder-close-alt': '&#xe623;',
		'aicon-caret-down': '&#xe627;',
		'aicon-caret-up': '&#xe628;',
		'aicon-edit': '&#xe62b;',
		'aicon-rss': '&#xe62c;',
		'0': 0
		},
		els = document.getElementsByTagName('*'),
		i, c, el;
	for (i = 0; ; i += 1) {
		el = els[i];
		if(!el) {
			break;
		}
		c = el.className;
		c = c.match(/aicon-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
}());
