(function () {
  tinymce.create('tinymce.plugins.anspress', {
    init: function (ed, url) {
      // Replace blob image src.
      ed.on('SaveContent', function (e) {
        e.content = e.content.replace(/<img([^>]+data-apimagename.*?[\w\W]+?)\/>/g, function(match, t) {
          regex = /data-apimagename\=\"([\S+]*)\"/g;
          apimagename = regex.exec(match);

          regex = /alt\=\"([\S+]*)\"/g;
          alt = regex.exec(match);
          return '{{apimage "'+apimagename[1]+'" "'+alt[1]+'"}}';
        });
      });
      ed.addButton('apmedia', {
        title: 'Insert image',
        icon: 'image',
        onclick: function () {
          // Open window
          var win = ed.windowManager.open({
            title: 'Insert Media (AnsPress)',
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
                text: 'Close',
                onclick: function () {
                  win.close();
                }
              }
            ],
            body: [{
                type: 'button',
                name: 'file-browser',
                label: 'Select File',
                text: 'Browse from computer',
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
                label: 'Image title',
                minWidth: 300
              },
              {
                type: 'container',
                name: 'image-preview',
                label: 'Media preview',
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