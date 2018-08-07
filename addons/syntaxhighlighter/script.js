function path(){
  var args = arguments,
  result = [];

  for(var i = 0; i < args.length; i++)
  result.push(args[i].replace('@', apBrushPath));

  return result
};

SyntaxHighlighter.autoloader.apply(null,path(
  'applescript @shBrushAppleScript.js',
  'actionscript3 as3 @shBrushAS3.js',
  'bash shell @shBrushBash.js',
  'coldfusion cf @shBrushColdFusion.js',
  'cpp c @shBrushCpp.js',
  'c# c-sharp csharp @shBrushCSharp.js',
  'css @shBrushCss.js',
  'delphi pascal @shBrushDelphi.js',
  'diff patch pas @shBrushDiff.js',
  'erl erlang @shBrushErlang.js',
  'groovy @shBrushGroovy.js',
  'java @shBrushJava.js',
  'jfx javafx @shBrushJavaFX.js',
  'js jscript javascript @shBrushJScript.js',
  'perl pl @shBrushPerl.js',
  'php @shBrushPhp.js',
  'text plain @shBrushPlain.js',
  'py python @shBrushPython.js',
  'ruby rails ror rb @shBrushRuby.js',
  'sass scss @shBrushSass.js',
  'scala @shBrushScala.js',
  'sql @shBrushSql.js',
  'vb vbnet @shBrushVb.js',
  'xml xhtml xslt html @shBrushXml.js'
));

SyntaxHighlighter.defaults.toolbar = false;
SyntaxHighlighter.all();

(function($){


  $(document).ready(function(){
    $('#anspress').on('click', '[apinsertcode]', function(e){
      AnsPress.mceSelected = null;
      AnsPress.trigger('openCodeModal');
    });

    AnsPress.on('openCodeModal', function(el){
      var code, lang, inline;
      code = '';
      lang = '';
      inline = false;

      var opt = '';
      $.each(AP_Brushes, function(k,v){
        opt += '<option value="'+k+'">'+v+'</option>';
      });

      AnsPress.modal('code', {
        title: aplang.shTitle,
        content: '<form apcodeform><div class="ap-form-group"><label class="ap-form-label">'+aplang.shLanguage+'</label><select name="language" class="ap-form-control">'+opt+'</select></div><div class="ap-from-group"><label class="ap-form-label">'+aplang.shInline+' <input type="checkbox" value="1" name="inline" /></label></div><textarea id="ap-code-textarea" class="ap-form-control" rows="10" placeholder="'+aplang.shTxtPlholder+'"></textarea><button type="submit" class="ap-btn">'+aplang.shButton+'</button></form>',
        size: 'medium',
      });
    });

    $('body').on( 'submit', '[apcodeform]', function(e){

      var code = $(this).find('textarea').val();
      code = code.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
      code = tinyMCE.DOM.encode(code).replace(/^[\s]+/gm, function(m) {
        var leadingSpaces = arguments[0].length;
        var str = '';
        while(leadingSpaces > 0) {
          str += '&nbsp;';
          leadingSpaces--;
        }
        return str;
      });
      code = code.replace(/[\r\n]\s*/g, '<br />');

      var lang = $(this).find('select').val();
      var tag = $(this).find('input').is(":checked") ? 'code' : 'pre';

      var attr = 'language="'+lang+'"';
      var cont = '[apcode '+attr+']<'+tag+' data-mce-contenteditable="false">'+code+'</'+tag+'>[/apcode]';
      tinymce.activeEditor.insertContent(cont);
      tinymce.activeEditor.focus();
      tinymce.activeEditor.selection.collapse(0);
      AnsPress.hideModal('code');

      return false;
    });



  });

})(jQuery);