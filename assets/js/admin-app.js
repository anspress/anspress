Vue.filter('replaceAmp', function (value) {
  return value.replace(/&amp;/g, '&');
});

(function($){
  $(document).ready(function(){
    if($('#answers-list').length>0){
      new Vue({
        el: '#answers-list',
        data: {
          items: []
        },
        mounted: function () {
          var self = this;
          $.ajax({
            url: ajaxurl,
            method: 'GET',
            data: {
              action: 'ap_ajax',
              ap_ajax_action: 'get_all_answers',
              question_id: $(self.$el).data('questionid')
            },
            success: function (data) {
              data = apParseAjaxResponse( data );
              self.items = data.data;
            },
            error: function (error) {
              alert(JSON.stringify(error));
            }
          });
        },
        methods: {
          statusCase: function(val){
            return val.toLowerCase();
          }
        },
        beforeUpdate: function() {
          var self = this;
          self.items.forEach(function(val, k){
            self.items[k].edit_link = self.$options.filters.replaceAmp(val.edit_link);
            self.items[k].trash_link = self.$options.filters.replaceAmp(val.trash_link);
          });
        }
      });
    }
  });
})(jQuery);