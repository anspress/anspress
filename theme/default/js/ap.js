(function($){
    $(document).ready(function () {
        $( document ).click(function (e) {
            e.stopPropagation();
            if (!$(e.target).is('.ap-dropdown-toggle') && !$(e.target).closest('.open').is('.open') && !$(e.target).closest('form').is('form')) {
               $('.ap-dropdown').removeClass('open');
            }
        });
        
        $('body').delegate('.ap-dropdown-menu a', 'click', function(e){
            $(this).closest('.ap-dropdown').removeClass('open');
        });

        // Dropdown toggle
        $('body').delegate('.ap-dropdown-toggle', 'click', function(e){
            e.preventDefault();
            $('.ap-dropdown').removeClass('open');
            $(this).closest('.ap-dropdown').addClass('open');
        });

        $('.ap-tip').aptip();

        $('#ap-conversation-scroll').scrollTop(0);

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

        $('body').delegate('#ap-question-sorting .ap-dropdown-menu a', 'click', function(e) {
            e.preventDefault();
            var val = $(this).data('value');
            $(this).closest('.ap-dropdown-menu').find('input[type="hidden"]').val(val);
            $(this).closest('form').submit();
        });

        $('body').delegate('#ap-question-sorting-reset', 'click', function(e) {
            e.preventDefault();
            $('#ap-question-sorting').find('input[type="hidden"]').val('');
            $(this).closest('form').submit();
        });

        $('body').delegate('#ap-question-sorting', 'submit', function(){
            AnsPress.site.showLoading(this);
            var form_data = $(this).serialize().replace(/[^&]+=&/g, '').replace(/&[^&]+=$/g, '');
            $.ajax({
                type: 'GET',
                dataType: 'html',
                data: form_data,
                success: function(data){
                    AnsPress.site.hideLoading('#ap-question-sorting');
                    var html = $(data);
                    window.history.replaceState('', '', '?' + form_data);

                    $('#anspress').html(html.find('#anspress'));

                    $(document).trigger('apAfterSorting');
                }
            });

            return false;
        });

        $('body').delegate('.ap-notify-item', 'click', function(e) {
            e.preventDefault();
            $(this).hide();
        });

        if($('[data-action="ap_chart"]').length > 0){
            $('[data-action="ap_chart"]').each(function(index, el) {
                var type = $(this).data('type');
                $(this).peity(type);
            });

        }

        $('.ap-dynamic-avatar').initial({fontSize:14, fontWeight:600});

        $( document ).ajaxComplete(function( event, data, settings ) {
            $('.ap-dynamic-avatar').initial({fontSize:14, fontWeight:600});
        });

        /*if($('.ap_collapse_menu').length > 0){
            var menu = $('.ap_collapse_menu'),
                menuwidth = menu.width(),
                dropdown = menu.find('.ap-dropdown .ap-dropdown-menu');

            var itemwidth = 0;
            var start_moving = false;

            menu.find('.ap-dropdown').hide();

            menu.find('li').each(function(index, el) {
                itemwidth = parseInt(itemwidth) + parseInt($(this).outerWidth());
                if((itemwidth + parseInt($(this).next().outerWidth())) > menuwidth)
                    start_moving = true;

                if(start_moving && !$(this).is('.ap-user-menu-more')){
                    dropdown.append($(this).clone());
                    $(this).remove();
                    menu.find('.ap-dropdown').show();
                }

            });
        }*/

        $('.ap-notification-scroll').scrollbar();

        $('.ap-label-form-item').click(function(e) {
            e.preventDefault();
            $(this).toggleClass('active');
            var hidden = $(this).find('input[type="hidden"]');
            hidden.val(hidden.val() == '' ? $(this).data('label') : '');
        });

        apLoadingDot();

        $('.ap-sidetoggle').click(function(){            
            $('#ap-notifiside').toggle();
            apNotiScrollHeight();
            ApSite.notificationAsRead();
        });

    });

    function apNotiScrollHeight(){
        $('#ap-notifiside .ap-notification-scroll.scroll-wrapper').css({ 'height':  $('.ap-notification-items').outerHeight() - $('.ap-notification-head').outerHeight() })
    }

    function ap_chk_activity_scroll(e) {
        if (($('#ap-conversation-scroll .ap-no-more-message').length == 0)) {
            var elem = $(e.currentTarget);
            if (elem[0].scrollHeight - elem.scrollTop() == elem.outerHeight()) {
                APjs.site.loadMoreConversations(elem);
            }
        }
    }

    /**
     * Ajax callback for subscribe button.
     * @param  {object} data Ajax success data.
     */
    apFunctions.apSubscribeBtnCB = function ( data, el ){
        if (data.action == 'subscribed') {
            $(el).addClass('active');
        } else {
            $(el).removeClass('active');
        }
    }


})(jQuery);


