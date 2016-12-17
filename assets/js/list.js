(function($) {
	AnsPress.loadTemplate('list');

	AnsPress.models.Filter = Backbone.Model.extend({
		defaults: {
			active: false,
      label: '',
      value: ''
		}
	});

  AnsPress.collections.Filters = Backbone.Collection.extend({
    model: AnsPress.models.Filter
  });

  AnsPress.activeListFilters = $('#ap_current_filters').length > 0 ? JSON.parse($('#ap_current_filters').html()) : {};
  AnsPress.views.Filter = Backbone.View.extend({
    tagName: 'li',
    className: 'filter-item',
    nameAttr: function(){
      if(this.multiple) return 'filters['+this.model.get('key')+'][]';
      return 'filters['+this.model.get('key')+']';
    },
    isActive: function(){

      if(!_.isEmpty(AnsPress.activeListFilters)){
        var key = this.model.get('key');
        var value = this.model.get('value').toString();
        if(AnsPress.activeListFilters[key] && ((!this.multiple && AnsPress.activeListFilters[key] == value) || (this.multiple && _.contains(AnsPress.activeListFilters[key], value)))){
          return true;
        }
      }
      return false;
    },
    className: function(){
      return this.isActive() ? 'active' : '';
    },
    template: '<label><input type="checkbox" name="{{name}}" value="{{value}}"<# if(active){ #> checked="checked"<# } #>/>{{label}}</label>',
    initialize: function(options){
      this.model = options.model;
      this.multiple = options.multiple;
    },
    events: {
      'change input': 'clickFilter'
    },
    render: function(){
      var t = _.template(this.template);
      var json = this.model.toJSON();
      json.name = this.nameAttr();
      json.active = this.isActive();
      this.$el.html(t(json));
      return this;
    },
    clickFilter: function(e){
      e.preventDefault();
      $(e.target).closest('form').submit();
    }
  });

  AnsPress.views.Filters = Backbone.View.extend({
    tagName: 'ul',
    className: 'ap-dropdown-menu',
    initialize: function(options){
      this.model = options.model;
      this.multiple = options.multiple;
    },
    renderItem: function(filter){
      var view = new AnsPress.views.Filter({model: filter, multiple: this.multiple});
      this.$el.append(view.render().$el);
    },
    render: function(){
      var self = this;
      this.model.each(function(filter){
        self.renderItem(filter);
      });
      return this;
    }
  });

  AnsPress.views.List = Backbone.View.extend({
    el: '#ap-lists',
    initialize: function(){

    },
    events: {
      'click [ap-filter]:not(.loaded)': 'loadFilter'
    },
    loadFilter: function(e){
      e.preventDefault();
			var self = this;
			AnsPress.showLoading(e.currentTarget);
      var q = $.parseJSON($(e.currentTarget).attr('ap-query'));
			q.ap_ajax_action = 'load_filter_'+q.filter;

			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.currentTarget);
          $(e.currentTarget).addClass('loaded');
					var filters = new AnsPress.collections.Filters(data.items);
          var view = new AnsPress.views.Filters({model: filters, multiple: data.multiple});
          $(e.currentTarget).after(view.render().$el);
				}
			});
    }
  });

  new AnsPress.views.List();

})(jQuery);