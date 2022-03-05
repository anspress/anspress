/**
 *	Author: Will Schleter
 *	based on: http://www.jamesrohal.com
 */
SyntaxHighlighter.brushes.MatlabKey = function()
{
	var keywords = 'break case catch classdef continue else elseif end for function global if otherwise parfor persistent return spmd switch try while';
	var functions = ' ';

	this.regexList = [
		{ regex: /%.*$/gm,	css: 'comments' }, // one line comments
		{ regex: /\%\{[\s\S]*?\%\}/gm, css: 'comments'}, // multiline comments
		{ regex: SyntaxHighlighter.regexLib.singleQuotedString, css: 'string' },
		{ regex: SyntaxHighlighter.regexLib.doubleQuotedString, css: 'string'},
		{ regex: new RegExp(this.getKeywords(keywords), 'gm'), css: 'keyword' }
	];
}

SyntaxHighlighter.brushes.MatlabKey.prototype	= new SyntaxHighlighter.Highlighter();
SyntaxHighlighter.brushes.MatlabKey.aliases		= ['matlabkey', 'matlab'];