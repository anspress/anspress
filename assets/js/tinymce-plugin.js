(function () {
  tinymce.create('tinymce.plugins.anspress', {
    init: function (ed, url) {
      ed.on('PreProcess', function (e) {
        $content = jQuery(e.node);
        $ta_name = jQuery('input[name="'+jQuery(ed.getElement()).attr('id') + '-images[]"]');
        $ta_name.each(function(){
          $filename = jQuery(this).attr('data-filename');
          if($content.find('[data-apimagename="'+$filename+'"]').length === 0)
            jQuery(this).remove();
        });
      });

      // Replace blob image src.
      ed.on('SaveContent', function (e) {

        //jQuery(e.content).find('').
        e.content = e.content.replace(/<img([^>]+data-apimagename.*?[\w\W]+?)\/>/g, function(match, t) {
          regex = /data-apimagename\=\"(.*)\"/g;
          apimagename = regex.exec(match);

          regex = /alt\=\"([^"]*)\"/g;
          alt = regex.exec(match);
          return '{{apimage "'+apimagename[1]+'" "'+alt[1]+'"}}';
        });
      });
      ed.addButton('apmedia', {
        title: ed.getLang('anspress.i18n_insert_image'),
        icon: 'image',
        onclick: function () {
          // Open window
          var win = ed.windowManager.open({
            title: ed.getLang('anspress.i18n_insert_media'),
            resizable: true,
            scrollbars: true,
            width: 500,
            height: 300,
            buttons: [{
                text: 'Insert',
                subtype: 'primary',
                onclick: function () {
                  var preview = win.find('#image-preview')[0]['$el']['context'];
                  var title = win.find('#file-title')[0]['$el']['context'];
                  ed.insertContent('<img src="'+jQuery(preview).find('img').attr('src')+'" alt="'+jQuery(title).val()+'" data-apimagename="'+jQuery(preview).find('img').attr('data-apimagename')+'" />');
                  win.close();
                }
              },
              {
<<<<<<< HEAD
<<<<<<< HEAD
                ed.getLang('anspress.i18n_close'),
=======
                text: ed.getLang('anspress.i18n_close'),
>>>>>>> anspress/master
=======
                text: ed.getLang('anspress.i18n_close'),
>>>>>>> anspress/master
                onclick: function () {
                  win.close();
                }
              }
            ],
            body: [{
                type: 'button',
                name: 'file-browser',
                label: ed.getLang('anspress.i18n_select_file'),
                text: ed.getLang('anspress.i18n_browse_from_computer'),
                onclick: function (e) {
                  $ta_name = jQuery(ed.getElement()).attr('id') + '-images[]';
                  var input = jQuery('<input name="'+ $ta_name +'" type="file" accept="image/*" />').hide();
                  input.insertAfter(ed.getElement());

                  input.on('change', function (e) {
                    var self = this;
                    if (this.files && this.files[0]) {
                      var reader = new FileReader();
                      reader.onload = function (e) {
                        var preview = win.find('#image-preview')[0]['$el']['context'];
                        jQuery(preview).html('<div class="ap-image-prev" style="position: relative"><img style="height: 150px;width: auto;max-width: 100%" src="' + e.target.result + '" data-apimagename="'+self.files[0].name+'" /></div>');
                        input.attr('data-filename', self.files[0].name);
                      }
                      reader.readAsDataURL(this.files[0]);
                    }
                  });
                  input.click();
                }
              },
              {
                type: 'textbox',
                name: 'file-title',
                label: ed.getLang('anspress.i18n_image_title'),
                minWidth: 300
              },
              {
                type: 'container',
                name: 'image-preview',
                label: ed.getLang('anspress.i18n_media_preview'),
              },
            ]
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
