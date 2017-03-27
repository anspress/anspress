var uploadsModel;
(function($){
  AnsPress.models.Upload = Backbone.Model.extend({
    defaults: {
      id: '',
      oldId: '',
      fileName: '',
      type: '',
      fileSize: '',
      uploaded: true,
      failed: false,
      url: '',
      nonce: ''
    }
  });

  AnsPress.collections.Uploads = Backbone.Collection.extend({
    model: AnsPress.models.Upload
  });

  AnsPress.views.Upload = Backbone.View.extend({
    idAttribute: 'id',
    template: $('#ap-upload-template').html(),
    id: function(){
      return this.model.get('id');
    },
    tagName: 'div',
    className: 'ap-upload-item',
    initialize: function(options){
      this.model = options.model;
      this.model.on('change:failed', this.uploadFailed, this);
      this.model.on('change:id', this.idChanged, this);
      this.model.on('change', this.render, this);
      this.listenTo( AnsPress, 'apUploadProgress', this.progress)
      this.listenTo( this.model, 'remove', this.removeEl)
    },
    events:{
      'click .insert-to-post': 'insertImage',
      'click .apicon-trashcan': 'removedUpload',
    },
    render: function(){
      var t = $(this.template + '<input name="ap-medias[]" value="'+this.model.get('id')+'" type="hidden">');
      if(this.model.get('uploaded'))
        this.$el.addClass('done');
      if(this.model.get('isImage'))
        this.$el.addClass('is-image');
      t.filter('.ap-upload-name').text(this.model.get('fileName') + ' (' + this.model.get('fileSize') + ')');
      this.$el.html(t);
      return this;
    },
    uploadFailed: function(){
      this.$el.removeClass('done').addClass('failed');
    },
    idChanged: function(){
      this.$el.attr('id', this.model.get('id'));
      this.$el.find('input').val(this.model.get('id'));
    },
    removedUpload: function(e){
      e.preventDefault();
      var self = this;
      AnsPress.ajax({
        data: {
          ap_ajax_action: 'delete_attachment',
          attachment_id: self.model.get('id'),
          __nonce: self.model.get('nonce'),
        },
        success: function(data){
          if(data.success){
            $('#'+self.model.get('id')).remove();

            setTimeout(function(){
              AnsPress.uploader.removeFile(self.model.get('id'));
            }, 400);

            if(typeof tinyMCE !== 'undefined'){
              tinyMCE.activeEditor.dom.remove(tinymce.activeEditor.dom.select('img[src="'+self.model.get('url')+'"]'));
              tinyMCE.activeEditor.execCommand('mceInsertContent',false,'');
            }
          }
        }
      });
    },
    insertImage: function(e){
      e.preventDefault();
      if(this.model.get('isImage')){
        addImageToEditor(this.model.get('url'), this.model.get('id'));
      }
    },
    progress: function(data){
      if(this.model.get('id') === data.id)
        this.$el.find('.ap-progress').css('width', data.per+'%')
    },
    removeEl: function(){
      console.log('remove');
      this.$el.remove();
    }
  });

  AnsPress.views.Uploads = Backbone.View.extend({
    el: '#ap-upload',
    initialize: function(options){
      this.model = options.model;
      this.model.on('add', this.adddedUplaod, this);
      this.listenTo(AnsPress, 'uploadsRemoved', this.removeAll)
    },
    renderItem: function(upload){
      var view = new AnsPress.views.Upload({ model: upload });
      this.$el.append(view.render().$el);
    },
    render: function(){
      var self = this;
      if(this.model){
        this.model.each(function(upload){
          self.renderItem(upload);
        });
      }
      return this;
    },
    adddedUplaod: function(upload){
      this.renderItem(upload);
    },
    removeAll: function(){
      var self = this;
      if(this.model){
        this.model.each(function(upload, i){
          self.model.remove(upload);
        });
      }
    }
  });

  var addImageToEditor = function(url, id){
    var html = '<img src="'+url+'" id="' + id + '" />';
    if(typeof tinyMCE !== 'undefined')
      tinyMCE.activeEditor.execCommand('mceInsertContent', false, html);
    else
      jQuery('.wp-editor-area').val(jQuery('.wp-editor-area').val() + html);
  };

  if($('#ap-uploads-data').length> 0){
    uploadsModel = new AnsPress.collections.Uploads(JSON.parse($('#ap-uploads-data').html()));
    var uploadsView = new AnsPress.views.Uploads({ model: uploadsModel });
    uploadsView.render();

    wpUploaderInit.browse_button = 'pickfiles';
    wpUploaderInit.container = 'ap-upload';
    wpUploaderInit.init = {
      FilesAdded: function(up, files) {
        var self = this,
          warningShown = false;

        var template = $('#ap-upload-template').html();
        plupload.each(files, function(file) {
          if (up.files.length > up.settings.maxfiles) {
            if(!warningShown)
              alert(aplang.attached_max);
            up.removeFile(file);
            warningShown = true;
            return;
          }

          uploadsModel.add({
            id: file.id,
            oldId: file.id,
            fileName: file.name,
            type: file.type,
            fileSize: plupload.formatSize(file.size),
            url: '',
            nonce: '',
            uploaded: false
          });

          AnsPress.uploader.start();
        });

      },
      UploadProgress: function(up, file) {
        AnsPress.trigger('apUploadProgress', {id: file.id, per: file.percent});
      },
      FileUploaded: function(up, file, info) {
        var prevId = file.id;
        var data = AnsPress.ajaxResponse(info.response);
        if(data.success){
          uploadsModel.get(prevId).set({'id': data.attachment_id, 'nonce': data.delete_nonce, done: data.success, isImage: data.is_image, url: data.attachment_url, uploaded: true });

          if(data.is_image){
            addImageToEditor(data.attachment_url, data.attachment_id);
          }
        }

        if(!data.success){
          AnsPress.trigger('snackbar', data);
          uploadsModel.get(file.id).set({'failed': true});
          up.removeFile(file);
        }
      },
      Error: function(up, args) {
        if(args.code === -600){
          AnsPress.trigger('snackbar', { success: false, snackbar : { message: aplang.file_size_error }});
        }
      },
      FilesRemoved: function(){
        AnsPress.trigger('uploadsRemoved');
      }
    };

    AnsPress.uploader = new plupload.Uploader(wpUploaderInit);
    AnsPress.uploader.init();

    $('.ap-field-description').on('drag dragstart dragenter dragover', function(e) {
      $(this).addClass('dragging');
    });

    $('.ap-field-description').on('dragend  dragleave drop', function(e) {
      $(this).removeClass('dragging');
    })
  }
})(jQuery);
