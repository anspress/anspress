(function($){
    $(document).ready(function () {
        $('textarea.autogrow, textarea#post_content').autogrow({
            onInitialize: true
        });

        $('.ap-categories-list li .ap-icon-arrow-down').click(function(e) {
            e.preventDefault();
            $(this).parent().next().slideToggle(200);
        });


        $('.ap-radio-btn').click(function() {
            $(this).toggleClass('active');
        });

        $('.bootstrap-tagsinput > input').keyup(function(event) {
            $(this).css(width, 'auto');
        });

        $('.ap-label-form-item').click(function(e) {
            e.preventDefault();
            $(this).toggleClass('active');
            var hidden = $(this).find('input[type="hidden"]');
            hidden.val(hidden.val() == '' ? $(this).data('label') : '');
        });

    });

    $('[ap-loadmore]').click(function(e){
        e.preventDefault();
        var self = this;
        var args = JSON.parse($(this).attr('ap-loadmore'));
        args.action = 'ap_ajax';

        if(typeof args.ap_ajax_action === 'undefined')
            args.ap_ajax_action = 'bp_loadmore';

        AnsPress.showLoading(this);
        AnsPress.ajax({
            data: args,
            success: function(data){
                AnsPress.hideLoading(self);
                console.log(data.element);
                if(data.success){
                    $(data.element).append(data.html);
                    $(self).attr('ap-loadmore', JSON.stringify(data.args));
                    if(!data.args.current){
                        $(self).hide();
                    }
                }
            }
        });
    });

})(jQuery);


