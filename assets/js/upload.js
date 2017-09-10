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
    $('.ap-field-type-upload input[type="file"]').on('change', function(){
      var args = $(this).data('upload');
      var parent = $(this).closest('.ap-form-group');
      var counter = $(this).parent().find('b');
      parent.removeClass('ap-have-errors');
      parent.find('.ap-field-errors').remove();

      if($(this).parent().prev().is('.ap-upload-list'))
        $(this).parent().prev().remove();

      $('<div class="ap-upload-list"></div>').insertBefore($(this).parent());

      if(this.files.length > args.max_files){
        AnsPress.trigger('snackbar', {'snackbar': {'success': false, 'message': args.label_max_added}});
        parent.addClass('ap-have-errors');
        $('<div class="ap-field-errors"><span class="ap-field-error">'+args.label_max_added+'</span></div>').insertBefore(parent.find('.ap-upload-list'));
      }

      counter.text(0);
      for(var i = 0; i < this.files.length; i++){
        var f = this.files[i];
        var ext = getExt(f.name);

        if($(this).attr('accept').indexOf( ext ) === -1){
          AnsPress.trigger('snackbar', {'snackbar': {'success': false, 'message': args.label_deny_type}});
          parent.addClass('ap-have-errors');
        }

        $('<div><span class="ext">'+ext+'</span>'+f.name+'<span class="size">'+bytesToSize(f.size)+'</span></div>').appendTo($(this).parent().prev());
        counter.text(i+1);
      }

    });
  })
})(jQuery);