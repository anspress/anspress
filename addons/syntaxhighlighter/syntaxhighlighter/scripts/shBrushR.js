/**
 *  Author: Yihui Xie <xie@yihui.name>
 *  URL: http://yihui.name/en/2010/09/syntaxhighlighter-brush-for-the-r-language
 *  License: GPL-2 | GPL-3
 */
SyntaxHighlighter.brushes.R = function()
{
    var keywords = 'if else repeat while function for in next break TRUE FALSE NULL Inf NaN NA NA_integer_ NA_real_ NA_complex_ NA_character_';
    var constants = 'LETTERS letters month.abb month.name pi';
    this.regexList = [
	{ regex: SyntaxHighlighter.regexLib.singleLinePerlComments,	css: 'comments' },
	{ regex: SyntaxHighlighter.regexLib.singleQuotedString,		css: 'string' },
	{ regex: SyntaxHighlighter.regexLib.doubleQuotedString,		css: 'string' },
	{ regex: new RegExp(this.getKeywords(keywords), 'gm'),		css: 'keyword' },
	{ regex: new RegExp(this.getKeywords(constants), 'gm'),		css: 'constants' },
	{ regex: /[\w._]+[ \t]*(?=\()/gm,				css: 'functions' },
    ];
};
SyntaxHighlighter.brushes.R.prototype	= new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.R.aliases	= ['r', 's', 'splus'];
