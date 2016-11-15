if(!AnsPress) AnsPress = {};

(function($){
  wpUploaderInit.browse_button = 'pickfiles';
  wpUploaderInit.container = 'ap-upload';
  wpUploaderInit.init = {
    FilesAdded: function(up, files) {
      var self = this,
        warningShown = false;
      $('form[name="answer_form"]').append('<input type="hidden" name="have_attachments" value="true">');

      plupload.each(files, function(file) {
        if (up.files.length > up.settings.maxfiles) {
          if(!warningShown)
            alert(aplang.attached_max);
          up.removeFile(file);
          warningShown = true;
          return;
        }

        $('#ap-upload').append( '<div id="' + file.id + '" class="ap-upload-item"><span class="ap-upload-name">' + file.name + ' (' + plupload.formatSize(file.size) + ')</span><a href="#" class="apicon-trashcan"></a><div class="ap-progress"></div></div>');

        AnsPress.uploader.start();

        $('#' + file.id + ' a').one().click(function(e) {
          e.preventDefault();
          var attachmentId = $(this).attr('data-id');
          var q = JSON.parse($(this).attr('ap-query'));
          q.ap_ajax_action = 'delete_attachment';

          AnsPress.ajax({
            data: q,
            success: function(data){
              if(data.success){
                up.removeFile(file);
                $('#' + file.id).remove();
                if(typeof tinyMCE !== 'undefined'){
                  tinyMCE.activeEditor.dom.remove(attachmentId);
                  tinyMCE.activeEditor.execCommand('mceInsertContent',false,'');
                }
              }
            }
          });

        });
      });

    },
    UploadProgress: function(up, file) {
      $('#'+file.id+' .ap-progress').css('width', file.percent+'%');
    },
    FileUploaded: function(up, file, info) {
      var data = AnsPress.ajaxResponse(info.response);
      if(data.success){
        $('#' + file.id + ' a')
          .attr('ap-query', JSON.stringify({ 'attachment_id' : data.attachment_id, '__nonce': data.delete_nonce }))
          .attr('data-id', data.attachment_id);

        $('#' + file.id).addClass('done');

        if(data.is_image){
          var html = '<img src="'+data.attachment_url+'" id="' + data.attachment_id + '" />';
          if(typeof tinyMCE !== 'undefined')
            tinyMCE.activeEditor.execCommand('mceInsertContent',false, html);
          else
            jQuery('.wp-editor-area').val(jQuery('.wp-editor-area').val() + html);
        }
      }

      if(!data.success){
        AnsPress.trigger('snackbar', data);
        $('#' + file.id).addClass('failed');
      }
    },
    FilesRemoved: function(up, file){
      plupload.each(file, function(f) {
        $('#ap-upload #'+f.id).remove();
      });
    },
    Error: function(up, args) {
      if(args.code === -600){
        AnsPress.trigger('snackbar', { success: false, snackbar : { message: aplang.file_size_error }});
      }
    }
  };

  AnsPress.uploader = new plupload.Uploader(wpUploaderInit);
  AnsPress.uploader.init();

  $('.ap-field-description').on('drag dragstart dragenter dragover', function(e) {
    console.log('drag')
    $(this).addClass('dragging');
  });

  $('.ap-field-description').on('dragend  dragleave drop', function(e) {
    console.log('drag remove')
    $(this).removeClass('dragging');
  })
})(jQuery);
