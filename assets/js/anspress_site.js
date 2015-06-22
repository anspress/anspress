/**
 * Javascript code for AnsPress fontend
 * @since 2.0
 * @package AnsPress
 * @author Rahul Aryan
 * @license GPL 2+
 */
(function($) {
    /* on start */
    $(function() {
        /* create document */
        AnsPress.site = new AnsPress.site();
        /* need to call init manually with $ */
        AnsPress.site.initialize();
    });
    /* namespace */
    window.AnsPress = {};
    AnsPress.site = function() {};
    AnsPress.site.prototype = {
        /** Initalize the class */
        initialize: function() {
            ApSite = this;
            this.ajax_id = new Object();
            this.loading = new Object();
            this.errors;
            this.ajaxData;
            this.appendFormError();
            this.appendMessageBox();
            this.ap_comment_form();
            this.afterPostingAnswer();
            //this.suggest_similar_questions();
            this.ap_ajax_form();
            this.load_comment_form();
            this.delete_comment();
            this.ap_subscribe();
            this.vote();
            this.select_answer();
            this.ap_delete_post();
            this.ap_upload_field();
            this.change_status();
            this.load_profile_field();
            this.ap_post_upload_field();
            this.tinyMCEeditorToggle();
            this.tab();
            this.set_featured();
            this.modal();
            this.expand();
            this.follow();
            this.updateCover();
            this.hoverCard();
            this.delete_notification();
            this.mark_as_read();
        },
        doAjax: function(query, success, context, before, abort) {
            /** Shorthand method for calling ajax */
            context = typeof context !== 'undefined' ? context : false;
            success = typeof success !== 'undefined' ? success : false;
            before = typeof before !== 'undefined' ? before : false;
            abort = typeof abort !== 'undefined' ? abort : false;
            var action = apGetValueFromStr(query, 'ap_ajax_action');
            if (abort && (typeof ApSite.ajax_id[action] !== 'undefined')) {
                ApSite.ajax_id[action].abort();
            }
            var req = $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: query,
                beforeSend: before,
                success: success,
                dataType: 'json',
                context: context,
                global: true
            });
            ApSite.ajax_id[action] = req;
            return req;
        },
        doAction: function(action) {
            var self = this;
            var action = typeof action !== 'undefined' ? '[data-action="' + action + '"]' : '[data-action]';
            var actions = new Object();
            $(action).each(function(i) {
                var action = $(this).attr('data-action');
                if (typeof actions[action] !== 'undefined') return;
                actions[action] = '1';
                //if (typeof self[action] === 'function')
                self[action]('[data-action="' + action + '"]');
                /*else
                console.log('No "'+action+'" method found in AnsPress.site{}');*/
            });
        },
        uniqueId: function() {
            return $('.ap-uid').length;
        },
        showLoading: function(elm) {
            /*hide any existing loading icon*/
            AnsPress.site.hideLoading(elm);

            var uid = this.uniqueId();
            var el = $('<div class="ap-loading-icon ap-uid" id="apuid-' + uid + '"><i class="apicon-sync"><i></div>');
            $('body').append(el);
            var offset = $(elm).offset();
            var height = $(elm).outerHeight();
            var width = $(elm).outerWidth();

            if($(elm).is('a, button, input[type="submit"], form')){
                el.css({
                    top: offset.top,
                    left: offset.left,
                    height: height,
                    width: width
                });
            }else{
                el.css({
                    top: offset.top + 14,
                    left: offset.left + width - 20
                });
            }

            $(elm).data('loading', '#apuid-' + uid);

            return '#apuid-' + uid;
        },
        hideLoading: function(elm) {
            $($(elm).data('loading')).hide();
        },
        suggest_similar_questions: function() {
            $('[data-action="suggest_similar_questions"]').on('keyup keydown', function() {
                if ($.trim($(this).val()) == '') return;
                ApSite.doAjax(apAjaxData('ap_ajax_action=suggest_similar_questions&value=' + $(this).val()), function(data) {
                    if (typeof data['html'] !== 'undefined') $('#similar_suggestions').html(data['html']);
                }, this, false, true);
            });
        },
        ap_ajax_form: function() {
            $('body').delegate('[data-action="ap_ajax_form"]', 'submit', function() {
                AnsPress.site.showLoading(this);
                if (typeof tinyMCE !== 'undefined') tinyMCE.triggerSave();
                ApSite.doAjax(apAjaxData($(this).formSerialize()), function(data) {
                    AnsPress.site.hideLoading(this);
                    if (typeof tinyMCE !== 'undefined' && typeof data.type !== 'undefined' && data.type == 'success') tinyMCE.activeEditor.setContent('');
                }, this);
                return false;
            })
        },
        appendFormError: function() {
            $(document).on('ap_after_ajax', function(e, data) {
                if (typeof data.errors !== 'undefined') {
                    ApSite.clearFormErrors(data.form);
                    $.each(data.errors, function(i, message) {
                        var parent = $('#' + data.form).find('#' + i).closest('.ap-form-fields');
                        parent.addClass('ap-have-error');
                        ApSite.helpBlock(parent, message);
                    });
                }
            });
        },
        helpBlock: function(elm, message) {
            /* remove existing help block */
            if ($(elm).find('.ap-form-error-message').length > 0) $(elm).find('.ap-form-error-message').remove();
            $(elm).append('<p class="ap-form-error-message">' + message + '</p>');
        },
        clearFormErrors: function(form) {
            var elm = $('#' + form).find('.ap-have-error');
            elm.find('.ap-form-error-message').remove();
            elm.removeClass('ap-have-error');
        },
        appendMessageBox: function() {
            if ($('#ap-notify').length == '0') $('body').append('<div id="ap-notify"></div>');
        },
        addMessage: function(message, type) {
            var icon = aplang[type];
            $('<div class="ap-notify-item ' + type + '"><i class="' + icon + '"></i><div class="ap-notify-content">' + message + '</div></div>').appendTo('#ap-notify').animate({
                'margin-left': 0
            }, 500).delay(5000).fadeOut(200);
        },
        redirect: function(data) {
            if (typeof data.redirect_to !== 'undefined') window.location.replace(data.redirect_to);
        },
        reload: function(data) {
            location.reload();
        },
        append: function(data) {
            if (typeof data.container !== 'undefined') $(data.container).append(data.html);
        },
        updateText: function(data) {
            if (typeof data.container !== 'undefined') $(data.container).text(data.text);
        },
        updateHtml: function(data) {
            if (typeof data.container !== 'undefined') $(data.container).html(data.html);
        },
        toggle_active_class: function(data) {
            if (typeof data.toggle_active_class_container !== 'undefined'){
                $(data.toggle_active_class_container).find('li').removeClass('active');
                $(data.toggle_active_class_container).find(data.active).addClass('active');
            } 
        },
        append_before: function(data) {
            if (typeof data.append_before_container !== 'undefined')
                $(data.append_before_container).before(data.html);
        },
        remove_if_exists: function(data) {
            if (typeof data.remove_if_exists_container !== 'undefined' && $(data.remove_if_exists_container).length > 0)
                $(data.remove_if_exists_container).remove();
        },
        clearForm: function(data) {
            if (typeof tinyMCE !== 'undefined') 
                tinyMCE.activeEditor.setContent('');
        },
        load_comment_form: function() {
            $('body').delegate('[data-action="load_comment_form"]', 'click', function(e) {
                e.preventDefault();
                ApSite.showLoading(this);
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    ApSite.hideLoading(this);
                    var button = $(this);

                    if(!data.view_default){
                        if ($(data.html).is('.ap-comment-block')) {
                            var c = button.closest('.ap-q-inner');
                            c.find('.ap-comment-block').remove();
                            c.append(data.html);
                         } else {
                            $('.ap-comment-form').remove();
                            $(this).closest('.ap-q-inner').append(data.html);
                        }
                    }else{
                        $(data.container).append(data.html);
                    }
                    
                    if ($(data.container).length > 0) $('html, body').animate({
                        scrollTop: ($(data.container).offset().top) - 150
                    }, 500);

                    jQuery('textarea.autogrow, textarea#post_content').keyup();
                    
                    if (typeof button.attr('data-toggle') !== 'undefined') $(button.attr('data-toggle')).hide();
                    $('#ap-comment-textarea').focus();
                    $(button.attr('href')).addClass('have-comments').removeClass('no-comment');
                }, this, false, true);
            });
        },
        ap_comment_form: function() {
            $('body').delegate('#ap-commentform', 'submit', function() {
                ApSite.showLoading(this);
                if (typeof tinyMCE !== 'undefined') tinyMCE.triggerSave();
                ApSite.doAjax(apAjaxData($(this).formSerialize()), function(data) {
                    ApSite.hideLoading(this);
                    if (data['action'] == 'new_comment' && data['message_type'] == 'success') {
                        $('#comments-' + data['comment_post_ID'] + ' ul.ap-commentlist').append($(data['html']).hide().slideDown(100));
                    } else if (data['action'] == 'edit_comment' && data['message_type'] == 'success') {
                        $('#li-comment-' + data.comment_ID).replaceWith($(data['html']).hide().slideDown(100));
                        $('.ap-comment-form').remove();
                    }
                    $(this)[0].reset();
                    $('.ap-comment-form').fadeOut(200, function() {
                        $(this).remove()
                    });
                }, this);
                return false;
            })
        },
        delete_comment: function() {
            $('body').delegate('[data-action="delete_comment"]', 'click', function(e) {
                e.preventDefault();
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    if (typeof $(this).attr('data-toggle') !== 'undefined' && data.message_type == 'success') $($(this).attr('data-toggle')).hide();
                }, this, false, true);
            });
        },
        ap_subscribe: function() {
            $('[data-action="ap_subscribe"]').click(function(e) {
                e.preventDefault();
                AnsPress.site.showLoading(this);
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    AnsPress.site.hideLoading(this);
                    if (data.action == 'subscribed') {
                        $(this).addClass('active');
                        $(this).closest('.ap-subscribe').addClass('active');
                    } else {
                        $(this).removeClass('active');
                        $(this).closest('.ap-subscribe').removeClass('active');
                    }
                }, this, function() {
                    $(this).closest('.ap-subscribe').toggleClass('active');
                });
            });
        },
        vote: function() {
            $('body').delegate('[data-action="vote"] a', 'click', function(e) {
                e.preventDefault();
                AnsPress.site.showLoading(this);
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    AnsPress.site.hideLoading(this);
                    var vote_c = $(this).parent();
                    vote_c.find('.ap-vote-fade').remove();
                    if (typeof data['action'] !== 'undefined' && data['action'] == 'voted' || data['action'] == 'undo') {
                        if (data['action'] == 'voted') {
                            $(this).addClass('voted');
                            if (data['type'] == 'vote_up') vote_c.find('.vote-down').addClass('disable');
                            if (data['type'] == 'vote_down') vote_c.find('.vote-up').addClass('disable');
                            $(this).trigger('voted', data);
                        } else if (data['action'] == 'undo') {
                            $(this).removeClass('voted');
                            if (data['type'] == 'vote_up') vote_c.find('.vote-down').removeClass('disable');
                            if (data['type'] == 'vote_down') vote_c.find('.vote-up').removeClass('disable');
                            $(this).trigger('undo_vote', data);
                        }
                        vote_c.find('.net-vote-count').text(data['count']);
                    }
                }, this, false);
            });
        },
        afterPostingAnswer: function() {
            $(document).on('ap_after_ajax', function(e, data) {
                if (typeof data.action !== 'undefined' && data.action == 'new_answer') {
                    if ($('#answers').length === 0) {
                        $('#question').after($(data['html']));
                        $(data['div_id']).hide();
                    } else $('#answers').append($(data['html']).hide());
                    $(data.div_id).slideDown(500);
                }
            });
        },
        select_answer: function() {
            $('body').delegate('[data-action="select_answer"]', 'click', function(e) {
                e.preventDefault();
                AnsPress.site.showLoading(this);
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data){AnsPress.site.hideLoading(this);});
            });
        },
        ap_delete_post: function() {
            $('body').delegate('[data-action="ap_delete_post"]', 'click', function(e) {
                e.preventDefault();
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    if (typeof data.action !== 'undefined' && data.action == 'delete_answer') $(data.div_id).slideUp(500).fadeOut(300, function() {
                        $(this).remove();
                    })
                }, this, false);
            });
        },
        ap_upload_field: function() {
            var self = this;
            var form
            $('[data-action="ap_upload_field"]').change(function() {
                $(this).closest('form').submit();
            });
            $('[data-action="ap_upload_form"]').submit(function() {
                $(this).ajaxSubmit({
                    success: function(data) {
                        $('body').trigger('uploadForm', data);
                    },
                    url: ajaxurl,
                    dataType: 'json'
                });
                return false
            });
        },
        change_status: function() {
            $('body').delegate('[data-action="ap_change_status"]', 'click', function(e) {
                e.preventDefault();
                var c = $(this).closest('ul').prev();
                AnsPress.site.showLoading(c);
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    AnsPress.site.hideLoading(c);
                }, this, false, true);
            });
        },
        load_profile_field: function() {
            $('body').delegate('[data-action="ap_load_user_field_form"]', 'click', function(e) {
                e.preventDefault();
                AnsPress.site.showLoading(this);
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    AnsPress.site.hideLoading(this);
                }, this, false);
            });
        },
        ap_post_upload_field: function() {            
            $('body').delegate('[data-action="ap_post_upload_field"]', 'click', function(e) {
                e.preventDefault();
                $('[name="post_upload_image"]').click();
            });

            $('body').delegate('[name="post_upload_image"]', 'change', function(e) {
                var clone = $(this).clone();
                $(clone).appendTo('#hidden-post-upload');
                $('#hidden-post-upload').submit();
            });

            $('body').delegate( '#hidden-post-upload', 'submit', function() {
                var cont = $('[data-action="ap_post_upload_field"]').closest('.ap-upload-o');
                
                $(this).ajaxSubmit({
                    beforeSubmit: function(){
                        ApSite.showLoading(cont);
                    },
                    success: function(data) {
                        ApSite.hideLoading(cont);
                        $('body').trigger('postUploadForm', data);

                        if(typeof data['html'] !== 'undefined' ){
                            ApSite.addImageInEditor(data['html']);
                            $('.ap-post-upload-form').append('<input type="hidden" name="attachment_ids[]" value="'+data['attachment_id']+'" />');
                        }

                    },
                    url: ajaxurl,
                    dataType: 'json',
                    type: 'POST'
                });

                return false;
            });

            $('body').delegate('.ap-upload-remote-link, [data-action="post_image_close"]', 'click', function(e) {
                e.preventDefault();
                $('.ap-upload-link-rc').toggle();
            });

            $('body').delegate('[data-action="post_image_ok"]', 'click', function(e) {
                e.preventDefault();
                $('.ap-upload-link-rc').toggle();
                if($(this).prev().val() != '' )
                    ApSite.addImageInEditor('<img src="'+$(this).prev().val()+'" />');
            });
        },
        addImageInEditor: function(html){
            console.log(html);
            if(typeof tinyMCE !== 'undefined')
                tinyMCE.activeEditor.execCommand('mceInsertContent',false, html);
            else
                $('.wp-editor-area').val($('.wp-editor-area').val() + html);
        },
        previewLocalImage: function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    ApSite.addImageInEditor( '<img src="'+e.target.result+'" />' );
                }

                reader.readAsDataURL(input.files[0]);
            }
        },
        tinyMCEeditorToggle: function(){            
            $('body').delegate('[data-action="ap_fullscreen_toggle"]', 'click', function(e) {
                e.preventDefault();
                $(this).toggleClass('active');
                tinyMCE.activeEditor.execCommand('mceFullscreen');                
            });
        },
        tab: function(){
            $('body').delegate('.ap-tab-nav a', 'click', function(e) {
                e.preventDefault();
                var container = $(this).attr('href');
                $('.ap-tab-container > *').removeClass('active');
                $('.ap-tab-nav >li').removeClass('active');
                $(this).parent().addClass('active');
                $(container).toggleClass('active');
            });
        },

        set_featured: function(){
            $('body').delegate('[data-action="set_featured"]', 'click', function(e) {
                e.preventDefault();
                var c = $(this).closest('ul').prev();
                AnsPress.site.showLoading(c);
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    AnsPress.site.hideLoading(c);
                }, this, false, true);
            });
        },

        modal: function(){
            $('body').delegate('[data-action="ap_modal"]', 'click', function(e) {
                e.preventDefault();

                var modal       = $( $(this).data('toggle') );
                modal.addClass('open');
                
                var mod_in      = modal.find('.ap-modal-inner');
                var mod_w       = mod_in.width();
                var mod_h       = mod_in.height();
                var screen_w    = $(window).width();
                var screen_h    = $(window).height();               

                mod_in.css({ 'left' : (screen_w - mod_w)/2, 'top' :  (screen_h/2) - (mod_h/2) });

            });

            $('body').delegate('[data-action="ap_modal_close"]', 'click', function(e) {
                $('.ap-modal').removeClass('open');
            });
        },
        expand: function(){
            $('body').delegate('[data-action="ap_expand"]', 'click', function(e) {
                e.preventDefault();
                var el = $(this).data('expand'),
                    parent = $(el).parent();

                $(parent).animate({ 'height' : $(el).height() });

                $(this).hide();

            });
        },
        follow: function() {
            $('body').delegate('[data-action="ap_follow"]', 'click', function(e) {
                e.preventDefault();
                AnsPress.site.showLoading(this);
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    AnsPress.site.hideLoading(this);
                    if (data.action == 'follow') {
                        $(this).addClass('active');
                    } else {
                        $(this).removeClass('active');
                    }
                }, this, function() {
                    $(this).toggleClass('active');
                });
            });
        },
        updateCover: function(){
            $(document).on('ap_after_ajax', function(e, data) {
                if (typeof data.action !== 'undefined' && data.action === 'cover_uploaded') {
                    $('[data-view="user_cover_'+ data.user_id +'"]').css({'background-image': 'url('+data.image+')'});
                }
            });
        },
        hoverCard:function(){
            console.log(!disable_hover_card);
            if(!disable_hover_card)
            $('[data-action="ap_hover_card"]').tooltipster({
                theme: 'ap-hover-card',
                delay:500,
                animation: 'fade',
                interactive:true,
                content: 'Loading...',
                functionBefore: function(origin, continueTooltip) {

                    // we'll make this function asynchronous and allow the tooltip to go ahead and show the loading notification while fetching our data
                    continueTooltip();
                    var q = $(this).attr('data-query'),
                        user_id = $(this).attr('data-userid');

                    // next, we want to check if our data has already been cached
                    if ( $('#user_'+user_id+'_card').length == 0) {                        
                        $.ajax({
                            type: 'POST',
                            url: ajaxurl,
                            data: q+'&ap_ajax_nonce='+ap_nonce,
                            success: function(data) {
                                $('body').append(data);
                                // update our tooltip content with our returned data and cache it
                                origin.tooltipster('content', $(data).show() );
                                $(data).show();
                            }
                        });
                    }else{
                        var html = $('#user_'+user_id+'_card').html();
                        origin.tooltipster('content', $(html).show() );
                    }
                }
            });
        },
        delete_notification: function() {
            $('body').delegate('[data-action="ap_delete_notification"]', 'click', function(e) {
                e.preventDefault();
                AnsPress.site.showLoading(this);
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    AnsPress.site.hideLoading(this);
                    if(typeof data.container !== 'undefined')
                        $(data.container).remove();
                }, this);
            });
        },
        mark_as_read: function() {
            $('body').delegate('[data-action="ap_markread_notification"]', 'click', function(e) {
                e.preventDefault();
                AnsPress.site.showLoading(this);
                var q = $(this).attr('data-query');
                ApSite.doAjax(apAjaxData(q), function(data) {
                    AnsPress.site.hideLoading(this);

                }, this);
            });

            $(document).on('ap_after_ajax', function(e, data) {
                if (typeof data.action !== 'undefined') {
                    if(data.action === 'mark_read_notification'){
                        $(data.container).removeClass('unread');
                        $(data.container).find('.ap-btn-markread').remove();
                    }else if(data.action === 'mark_all_read' ){
                        $('.ap-notification-item').removeClass('unread');
                        $('.ap-notification-item').find('.ap-btn-markread').remove();
                    }
                }
            });
        },

    }
})(jQuery);

(function($) {
    $(document).ajaxComplete(function(event, data, settings) {

        if (typeof data !== 'undefined' && typeof data.responseJSON !== 'undefined' && typeof data.responseJSON.ap_responce !== 'undefined') {
            var data = data.responseJSON;
            if (typeof data.message !== 'undefined') {
                var type = typeof data.message_type === 'undefined' ? 'success' : data.message_type;
                ApSite.addMessage(data.message, type);
                
                if(typeof grecaptcha !== 'undefined' && data.message_type !== 'success')
                    grecaptcha.reset(widgetId1);
            }
            $(document).trigger('ap_after_ajax', data);
            
            if (typeof data.do !=='undefined'){
                if($.isArray(data.do)){
                    $.each(data.do, function(index, el) {
                        if(typeof ApSite[data.do[index]] === 'function')
                            ApSite[data.do[index]](data);
                    });
                }else{
                    if(typeof ApSite[data.do] === 'function')
                        ApSite[data.do](data);
                }
            }
            console.log(data.view);
            if (typeof data.view !== 'undefined') {
                $.each(data.view, function(i, view) {
                    var html = $(view);
                    if(typeof data.view_html !== 'undefined' && html.is('[data-view="' + i + '"]')){               
                        html = html.children();
                        $('[data-view="' + i + '"]').html(html);
                    }else{
                        $('[data-view="' + i + '"]').text(view);
                        $('[data-view="' + i + '"]').removeClass('ap-view-count-0');
                    }
                });
            }
        }
        
    });
})(jQuery);

function apAjaxData(param) {
    param = param + '&action=ap_ajax';
    return param;
}

function apQueryStringToJSON(string) {
    var pairs = string.split('&');
    var result = {};
    pairs.forEach(function(pair) {
        pair = pair.split('=');
        result[pair[0]] = encodeURIComponent(pair[1] || '');
    });
    return JSON.parse(JSON.stringify(result));
}

function apGetValueFromStr(q, name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(q);
    return results == null ? false : decodeURIComponent(results[1].replace(/\+/g, " "));
}

function apCenterBox(elm){
    var elm         = jQuery(elm);
    var parent      = elm.parent();

    parent.css({position: 'relative'});

    elm.css("left", (parent.width()-elm.width())/2);
    elm.css("top", (parent.height()-elm.height())/2);
}