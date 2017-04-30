/**
 * AnsPress admin app.
 */
'use strict';

(function($) {
  AnsPress.views.Answer = Backbone.Model.extend({
    defaults: {
      ID: '',
      content: '',
      deleteNonce: '',
      comments: '',
      activity : '',
      author: '',
      editLink: '',
      trashLink: '',
      status: '',
      selected: '',
      avatar: ''
    }
  });

  AnsPress.collections.Answers = Backbone.Collection.extend({
    url: ajaxurl+'?action=ap_ajax&ap_ajax_action=get_all_answers&question_id='+currentQuestionID,
    model: AnsPress.views.Answer
  });

  AnsPress.views.Answer = Backbone.View.extend({
    className: 'ap-ansm clearfix',
    id: function(){
      return this.model.get('ID');
    },
    initialize: function(options){
      if(options.model)
        this.model = options.model;
    },
    template: function(){
      return $('#ap-answer-template').html()
    },
    render: function(){
      if(this.model){
        var t = _.template(this.template());
        this.$el.html(t(this.model.toJSON()));
      }
      return this;
    }
  });

  AnsPress.views.Answers = Backbone.View.extend({
    initialize: function(options){
      this.model = options.model;
      this.model.on('add', this.answerFetched, this);
    },
    renderItem: function(ans){
      var view = new AnsPress.views.Answer({model: ans});
      this.$el.append(view.render().$el);
    },
    render: function(){
      var self = this;
      if(this.model){
        this.model.each(function(ans){
          self.renderItem(ans);
        });
      }

      return this;
    },
    answerFetched: function(answer){
      this.renderItem(answer);
    }
  });

  if( currentQuestionID ) {
    var answers = new AnsPress.collections.Answers();
    var answersView = new AnsPress.views.Answers({model: answers, el: '#answers-list'});
    answersView.render();
    answers.fetch();
  }

})(jQuery);