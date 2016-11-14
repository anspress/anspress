if(!AnsPress) AnsPress = {};

(function($){
  wpUploaderInit.browse_button = 'pickfiles';
  wpUploaderInit.container = 'ap-upload';
  wpUploaderInit.init = {
    FilesAdded: function(up, files) {
      var self = this;
      $('form[name="answer_form"]').append('<input type="hidden" name="have_attachments" value="true">');
      plupload.each(files, function(file) {
        $('#ap-upload').append( '<div id="' + file.id + '" class="ap-upload-item"><span class="ap-upload-name">' + file.name + ' (' + plupload.formatSize(file.size) + ')</span><a href="#">Remove</a><div class="ap-progress"></div></div>');

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

        var html = '<img src="'+data.attachment_url+'" id="' + data.attachment_id + '" />';

        if(typeof tinyMCE !== 'undefined')
          tinyMCE.activeEditor.execCommand('mceInsertContent',false, html);
        else
          jQuery('.wp-editor-area').val(jQuery('.wp-editor-area').val() + html);
      }
    },
    FilesRemoved: function(up, file){
      plupload.each(file, function(f) {
        $('#ap-upload #'+f.id).remove();
      });
    }
  };

  AnsPress.uploader = new plupload.Uploader(wpUploaderInit);
  AnsPress.uploader.init();
})(jQuery);
