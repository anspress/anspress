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

})(jQuery);


