jQuery(function(){jQuery.fn.apAjaxQueryString=function(){var a=jQuery(this).data("query").split("::"),e={action:"ap_ajax"};e.ap_ajax_action=a[0],e.__nonce=a[1],e.args={};var i=0;return jQuery.each(a,function(t){0!=t&&1!=t&&(e.args[i]=a[t],i++)}),e},APjs.admin=new APjs.admin,APjs.admin.initialize()}),window.APjs={},APjs.admin=function(){},function(e){APjs.admin.prototype={initialize:function(){this.renameTaxo(),this.editPoints(),this.savePoints(),this.deleteFlag(),this.ajaxBtn()},renameTaxo:function(){jQuery(".ap-rename-taxo").on("click",function(t){return t.preventDefault(),jQuery.ajax({url:ajaxurl,data:{action:"ap_taxo_rename"},context:this,success:function(t){jQuery(this).closest(".error").remove(),location.reload()}}),!1})},editPoints:function(){jQuery(".wp-admin").on("click",'[data-action="ap-edit-reputation"]',function(t){t.preventDefault(),t=jQuery(this).attr("href"),jQuery.ajax({type:"POST",url:ajaxurl,data:{action:"ap_edit_reputation",id:t},context:this,dataType:"json",success:function(t){t.status&&(jQuery("#ap-reputation-edit").remove(),jQuery("#anspress-reputation-table").hide(),jQuery("#anspress-reputation-table").after(t.html))}})})},savePoints:function(){jQuery(".wp-admin").on("submit",'[data-action="ap-save-reputation"]',function(t){return t.preventDefault(),jQuery(".button-primary",this).attr("disabled","disabled"),jQuery(this).attr("href"),jQuery.ajax({type:"POST",url:ajaxurl,cache:!1,data:jQuery(this).serialize({checkboxesAsBools:!0}),context:this,dataType:"json",success:function(t){t.status&&jQuery(".wrap").empty().html(t.html)}}),!1})},deleteFlag:function(){jQuery('[data-action="ap-delete-flag"]').on("click",function(t){t.preventDefault(),jQuery.ajax({type:"POST",url:ajaxurl,data:jQuery(this).attr("href"),context:this,success:function(t){jQuery(this).closest(".flag-item").remove()}})})},ajaxBtn:function(){e(".ap-ajax-btn").on("click",function(t){t.preventDefault(),t=e(this).apAjaxQueryString(),e.ajax({url:ajaxurl,data:t,context:this,type:"POST",success:function(t){var a;void 0!==e(this).data("cb")&&(a=e(this).data("cb"),"function"==typeof APjs.admin[a]&&APjs.admin[a](t,this))}})})},replaceText:function(t,a){e(a).closest("li").find("strong").text(t)}},e(document).ready(function(){e("#select-question-for-answer").on("keyup",function(){""!=jQuery.trim(jQuery(this).val())&&jQuery.ajax({type:"POST",url:ajaxurl,data:{action:"ap_ajax",ap_ajax_action:"suggest_similar_questions",value:jQuery(this).val(),is_admin:!0},success:function(t){var a=jQuery(t).filter("#ap-response").html();void 0!==a&&2<a.length&&(t=JSON.parse(a)),void 0!==t.html&&jQuery("#similar_suggestions").html(t.html)},context:this})}),e('[data-action="ap_media_uplaod"]').on("click",function(t){t.preventDefault(),$btn=jQuery(this);var i=wp.media({title:jQuery(this).data("title"),multiple:!1}).open().on("select",function(t){var a=(e=i.state().get("selection").first()).toJSON().url,e=e.toJSON().id;jQuery($btn.data("urlc")).val(a),jQuery($btn.data("idc")).val(e),jQuery($btn.data("urlc")).prev().is("img")?jQuery($btn.data("urlc")).prev().attr("src",a):jQuery($btn.data("urlc")).before('<img id="ap_category_media_preview" src="'+a+'" />')})}),e('[data-action="ap_media_remove"]').on("click",function(t){t.preventDefault(),e('input[data-action="ap_media_value"]').val(""),e('img[data-action="ap_media_value"]').remove()}),e(".checkall").on("click",function(){e(this).closest(".ap-tools-ck").find('input[type="checkbox"]:not(.checkall)').prop("checked",e(this).prop("checked"))}),e("#"+e("#ap-tools-selectroles").val()).slideDown(),e("#ap-tools-selectroles").on("change",function(){var t="#"+e(this).val();e(".ap-tools-roleitem").hide(),e(t).fadeIn(300)})})}(jQuery);
//# sourceMappingURL=ap-admin.js.map
