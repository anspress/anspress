(function () {
  tinymce.create('tinymce.plugins.anspress', {
    init: function (ed, url) {
      // ed.on('PreProcess', function (e) {
      //   $content = jQuery(e.node);
      //   $ta_name = jQuery('input[name="'+jQuery(ed.getElement()).attr('id') + '-images[]"]');
      //   $ta_name.each(function(){
      //     $filename = jQuery(this).attr('data-filename');
      //     if($content.find('[data-apimagename="'+$filename+'"]').length === 0)
      //       jQuery(this).remove();
      //   });
      // });

      var replaceImages = function(e){
        //jQuery(e.content).find('').
        e.content = e.content.replace(/<img([^>]+data-apimagename.*?[\w\W]+?)\/>/g, function(match, t) {
          regex = /data-apimagename\=\"(.*)\"/g;
          apimagename = regex.exec(match);
          if(apimagename)
            return '{{apimage "'+apimagename[1]+'"}}';
        });
      }

      // Replace blob image src.
      ed.on('SaveContent', function (e) {
        replaceImages(e);
      });
      ed.on('GetContent', function (e) {
        replaceImages(e);
      });

      ed.addButton('apmedia', {
        title: ed.getLang('anspress.i18n_insert_image'),
        icon: 'image',
        onclick: function () {
          $form = jQuery(ed.getElement()).closest('form');
          $fieldname = jQuery(ed.getElement()).attr('id') + '-images[]';
          AnsPress.trigger('openSelectImage', $form, $fieldname, function(result, files){
            ed.insertContent('<img src="'+result+'" data-apimagename="'+files[0].name+'" />');
          });
        }
      });
    },
    createControl: function (n, cm) {
      return null;
    }
  });

  tinymce.PluginManager.add('anspress', tinymce.plugins.anspress);
})();
