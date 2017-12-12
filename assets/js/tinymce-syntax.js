(function () {
  tinymce.create('tinymce.plugins.apsyntax', {
    init: function (ed, url) {
      var getSelectedCode = function (editor) {
        var node = editor.selection.getNode();
        if(editor.$(node).is('pre')||editor.$(node).is('code')){
          return editor.$(node).html();
        }
        return null;
      };
      var getSelectedLang = function (editor) {
        var node = editor.selection.getNode();
        var matches;

        if(node){
          var aplang = editor.$(node).attr('aplang');
          if(aplang){
            return aplang;
          }
        }
        return null;
      };
      var getLanguages = function() {
        var values = [];
        ed.$.each(AP_Brushes, function(key,label){
          values.push({ text: label, value: key });
        });
        return values;
      }
      var isInline = function(editor) {
        var node = editor.selection.getNode();
        if(editor.$(node).is('code')){
          return true;
        }
        return false;
      }
      ed.addButton('apcode', {
        title: ed.getLang('anspress.i18n_insert_code'),
        icon: 'code',
        onclick: function () {
          // Open window
          var win = ed.windowManager.open({
            title: ed.getLang('anspress.i18n_insert_codes'),
            resizable: true,
            scrollbars: true,
            width: 700,
            height: 300,
            buttons: [{
                text: ed.getLang('anspress.i18n_insert'),
                subtype: 'primary',
                onclick: 'submit'
              },
              {
                text: 'Close',
                onclick: function () {
                  win.close();
                }
              }
            ],
            body: [
              {
                type: 'listbox',
                name: 'language',
                label: 'Language',
                values : getLanguages(),
                value: getSelectedLang(ed)
              },
              {
                type: 'checkbox',
                name: 'inline',
                label: ed.getLang('anspress.i18n_inline'),
                value: isInline(ed)
              },
              {
                type: 'textbox',
                name: 'code',
                label: false,
                text: ed.getLang('anspress.i18n_insert_your_code'),
                minWidth: 300,
                minHeight: 200,
                multiline: true,
                value: getSelectedCode(ed)
              }
            ],
            onsubmit: function(e){
              if(''!==e.data.code){
                var tag = e.data.inline ? 'code' : 'pre';
                ed.insertContent('<'+tag+' aplang="'+e.data.language+'" id="__new" contenteditable="false">'+tinyMCE.DOM.encode(e.data.code)+'</'+tag+'>');
                var rng = tinymce.DOM.createRng();
                ed.focus(); //give the editor focus
                ed.selection.select(ed.$('#__new').removeAttr('id')[0]); //select the inserted element
                ed.selection.collapse(0);
              }
              win.close();
            }
          });
        }
      });
    },
    createControl: function (n, cm) {
      return null;
    }
  });

  tinymce.PluginManager.add('apsyntax', tinymce.plugins.apsyntax);
})();