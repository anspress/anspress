(function($){
  function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes == 0) return '0 Byte';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
  }

  function getExt(filename){
    return (/[.]/.exec(filename)) ? /[^.]+$/.exec(filename) : undefined;
  }

  $(document).ready(function(){
    // Show snackbar on error.
    function checkError(files, args) {
      if(files.length > args.max_files){
        AnsPress.trigger('snackbar', {'snackbar': {'success': false, 'message': args.label_max_added}});
        parent.addClass('ap-have-errors');
        $('<div class="ap-field-errors"><span class="ap-field-error">'+args.label_max_added+'</span></div>').insertBefore(parent.find('.ap-upload-list'));

        return true;
      }
      return false;
    }

    $('.ap-editor-imgesel input[type="file"]').on('change', function(){
      var self = this;
      var args = $(this).data('upload');
      var parent = $(this).closest('.ap-editor-imgesel');
      var fieldName = $(self).attr('name');

      // Check if only allowed numbers of files are uploaded.
      if( $('[name="'+fieldName+'"]').length > args.max_files ){
        AnsPress.trigger('snackbar', {'snackbar': {'success': false, 'message': args.label_max_added}});
        parent.addClass('ap-have-errors');
        $('<div class="ap-field-errors"><span class="ap-field-error">'+args.label_max_added+'</span></div>').insertBefore(parent.find('.ap-upload-list'));

        return;
      }

      if (this.files && this.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
          if(typeof tinymce !== 'undefined') {
            tinymce.activeEditor.insertContent('<img src="'+e.target.result+'" data-apimagename="'+self.files[0].name+'" />');
          }

          var $this = $(self), $clone = $this.clone();
          $clone.hide();
          $clone.insertAfter(parent);
          $this.val('');
        }

        reader.readAsDataURL(this.files[0]);
      }
    });

    $('.ap-field-type-upload input[type="file"]').on('change', function(){
      var self = this;

      var args = $(this).data('upload');
      var parent = $(this).closest('.ap-form-group');
      var counter = $(this).parent().find('b');
      parent.removeClass('ap-have-errors');
      parent.find('.ap-field-errors').remove();

      // Check for error.
      if(checkError(this.files, args)){
        return;
      }

      counter.text(0);
      for(var i = 0; i < this.files.length; i++){
        var f = this.files[i];
        var ext = getExt(f.name);

        if($(this).attr('accept').indexOf( ext ) === -1){
          AnsPress.trigger('snackbar', {'snackbar': {'success': false, 'message': args.label_deny_type}});
          parent.addClass('ap-have-errors');
        }

        $('<div><span class="ext">'+ext+'</span>'+f.name+'<span class="size">'+bytesToSize(f.size)+'</span></div>').appendTo(parent.find('.ap-upload-list'));
        counter.text(i+1);
      }

    });
  });

  AnsPress.on('openSelectImage', function(form, fieldname, cb){
    cb = cb||false;
    $form = $(form);
    $field = $('<input type="file" name="'+fieldname+'" />').hide();
    if($form.find('.image-hidden-wrap').length == 0)
      $form.append('<div class="image-hidden-wrap" style="height:0;overflow:hidden"></div>');
    $wrap = $('.image-hidden-wrap');
    $wrap.append($field);
    $field.on('change', function(){
      var self = this;
      if (this.files && this.files[0]) {
        var reader = new FileReader();

        reader.onload = function(e) {
          if(cb) cb(e.target.result, self.files);
        }

        reader.readAsDataURL(this.files[0]);
      }
    });
    $field.click();
  });
})(jQuery);