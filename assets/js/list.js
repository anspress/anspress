(function($) {
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
    //tagName: 'li',
    id: function(){
      return this.model.id;
    },
    nameAttr: function(){
      if(this.multiple) return ''+this.model.get('key')+'[]';
      return this.model.get('key');
    },
    isActive: function(){
      if(this.model.get('active'))
        return this.model.get('active');

      if(this.active)
        return this.active;

      var get_value = AnsPress.getUrlParam(this.model.get('key'));
      if(!_.isEmpty(get_value)){
        var value = this.model.get('value');
        if(!_.isArray(get_value) && get_value === value)
          return true;
        if(_.contains(get_value, value)){
          this.active = true;
          return true;
        }
      }

      this.active = false;
      return false;
    },
    className: function(){
      return this.isActive() ? 'active' : '';
    },
    inputType: function(){
      return this.multiple ? 'checkbox' : 'radio';
    },
    initialize: function(options){
      this.model = options.model;
      this.multiple = options.multiple;
      this.listenTo(this.model, 'remove', this.removed);
    },
    template: '<label><input type="{{inputType}}" name="{{name}}" value="{{value}}"<# if(active){ #> checked="checked"<# } #>/><i class="apicon-check"></i>{{label}}</label>',
    events: {
      'change input': 'clickFilter'
    },
    render: function(){
      var t = _.template(this.template);
      var json = this.model.toJSON();
      json.name = this.nameAttr();
      json.active = this.isActive();
      json.inputType = this.inputType();
      this.removeHiddenField();
      this.$el.html(t(json));
      return this;
    },
    removeHiddenField: function(){
      $('input[name="'+this.nameAttr()+'"][value="'+this.model.get('value')+'"]').remove();
    },
    clickFilter: function(e){
      e.preventDefault();
      $(e.target).closest('form').submit();
    },
    removed: function(){
      this.remove();
    }
  });

  AnsPress.views.Filters = Backbone.View.extend({
    className: 'ap-dropdown-menu',
    searchTemplate: '<div class="ap-filter-search"><input type="text" search-filter placeholder="'+aplang.search+'" /></div>',
    template: '<button class="ap-droptogg apicon-x"></button><filter-items></filter-items>',
    initialize: function(options){
      this.model = options.model;
      this.multiple = options.multiple;
      this.filter = options.filter;
      this.nonce = options.nonce;
      this.listenTo(this.model, 'add', this.added);
    },
    events: {
      'keypress [search-filter]': 'searchInput'
    },
    renderItem: function(filter){
      var view = new AnsPress.views.Filter({model: filter, multiple: this.multiple});
      this.$el.find('filter-items').append(view.render().$el);
    },
    render: function(){
      var self = this;
      if(this.multiple)
        this.$el.append(this.searchTemplate);

      this.$el.append(this.template);
      this.model.each(function(filter){
        self.renderItem(filter);
      });
      return this;
    },
    search: function(q, e){
      var self = this;

      var args = { __nonce: this.nonce, ap_ajax_action: 'load_filter_'+this.filter, search: q, filter: this.filter };

      AnsPress.showLoading(e);
			AnsPress.ajax({
				data: args,
				success: function(data){
          AnsPress.hideLoading(e);
          if(data.success){
            self.nonce = data.nonce;
            while (model = self.model.first()) {
              model.destroy();
            }
            self.model.add(data.items);
          }
				}
			});
    },
    searchInput: function(e){
      var self = this;
      clearTimeout(this.searchTO);
      this.searchTO = setTimeout(function(){
        self.search($(e.target).val(), e.target);
      }, 600);
    },
    added: function(model){
      this.renderItem(model);
    }
  });

  AnsPress.views.List = Backbone.View.extend({
    el: '#ap-filters',
    initialize: function(){

    },
    events: {
      'click [ap-filter]:not(.loaded)': 'loadFilter',
      'click #ap-filter-reset': 'resetFilter'
    },
    loadFilter: function(e){
      e.preventDefault();
			var self = this;
			AnsPress.showLoading(e.currentTarget);
      var q = $.parseJSON($(e.currentTarget).attr('apquery'));
			q.ap_ajax_action = 'load_filter_'+q.filter;

			AnsPress.ajax({
				data: q,
				success: function(data){
					AnsPress.hideLoading(e.currentTarget);
          $(e.currentTarget).addClass('loaded');
					var filters = new AnsPress.collections.Filters(data.items);
          var view = new AnsPress.views.Filters({model: filters, multiple: data.multiple, filter: q.filter, nonce: data.nonce});
          $(e.currentTarget).after(view.render().$el);
				}
			});
    },
    resetFilter: function(e){
      $('#ap-filters input[type="hidden"]').remove();
      $('#ap-filters input[type="checkbox"]').prop('checked', false);
    }
  });

  $(document).ready(function(){
    new AnsPress.views.List();
  });

})(jQuery);