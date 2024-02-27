(function ($) {
    $(document).ready(function () {
        $(document).on('focus', '.wp-editor-area', function() {
            if (!$(this).data('autogrow_initialized')) {
                $(this).autogrow({
                    onInitialize: true
                }).data('autogrow_initialized', true);
            }
        });

        $('.ap-categories-list li .ap-icon-arrow-down').on('click', function (e) {
            e.preventDefault();
            $(this).parent().next().slideToggle(200);
        });


        $('.ap-radio-btn').on('click', function () {
            $(this).toggleClass('active');
        });

        $('.bootstrap-tagsinput > input').on('keyup', function (event) {
            $(this).css(width, 'auto');
        });

        $('.ap-label-form-item').on('click', function (e) {
            e.preventDefault();
            $(this).toggleClass('active');
            var hidden = $(this).find('input[type="hidden"]');
            hidden.val(hidden.val() == '' ? $(this).data('label') : '');
        });

    });

    $('[ap-loadmore]').on('click', function (e) {
        e.preventDefault();
        var self = this;
        var args = JSON.parse($(this).attr('ap-loadmore'));
        args.action = 'ap_ajax';

        if (typeof args.ap_ajax_action === 'undefined')
            args.ap_ajax_action = 'bp_loadmore';

        AnsPress.showLoading(this);
        AnsPress.ajax({
            data: args,
            success: function (data) {
                AnsPress.hideLoading(self);
                console.log(data.element);
                if (data.success) {
                    $(data.element).append(data.html);
                    $(self).attr('ap-loadmore', JSON.stringify(data.args));
                    if (!data.args.current) {
                        $(self).hide();
                    }
                }
            }
        });
    });

})(jQuery);
