(function($){
    $(document).ready(function () {
        $( document ).click(function (e) {
            e.stopPropagation();
            if (!$(e.target).is('.ap-dropdown-toggle') && !$(e.target).closest('.open').is('.open') && !$(e.target).closest('form').is('form')) {
               $('.ap-dropdown').removeClass('open');
            }
        });

        // Dropdown toggle
        $('body').delegate('.ap-dropdown-toggle, .ap-dropdown-menu > a', 'click', function(e){
            e.preventDefault();
            $('.ap-dropdown').not($(this).closest('.ap-dropdown')).removeClass('open');
            $(this).closest('.ap-dropdown').toggleClass('open');
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
        
        apLaodAvatar();            

        $( document ).ajaxComplete(function( event, data, settings ) {
            apLaodAvatar();
        });

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

    /**
     * Ajax callback for subscribe button.
     * @param  {object} data Ajax success data.
     */
    apFunctions.apAppendEditor = function ( data, el ){
        $('.ap-field-description').html(data);
        //$('#description').hide();
        $(el).closest('.ap-minimal-editor').removeClass('ap-minimal-editor');
    }

    /**
     * Ajax callback for subscribe button.
     * @param  {object} data Ajax success data.
     */
    apFunctions.initScrollbar = function ( data, el ){
           
    }

})(jQuery);


