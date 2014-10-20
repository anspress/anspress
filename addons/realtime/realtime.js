/* on start */
jQuery(document).ready(function() {
     
    /* create document */
    ApRealTime.realtime = new ApRealTime.realtime();
    /* need to call init manually with jQuery */
    ApRealTime.realtime.initialize();
 
});
 
/* namespace */
window.ApRealTime = {};
ApRealTime.realtime = function() {};

ApRealTime.realtime.prototype = {
	//initialization
     initialize: function(){
		if(typeof qid !== 'undefined')
          this.initSevr(); 
     },
	 
	 initSevr: function(){
		var self = this;
		var sse = new EventSource(realtime_process+'?page='+ap_page+'&qid='+qid+'&event=question_'+qid);
		
		sse.addEventListener('question_'+qid,function(e){	
			var data = e.data;
			var obj = JSON.parse(data);
			if(typeof obj !== 'undefined'){
				load_time = obj['max_time'];
				jQuery.each(obj, function(time, update){
					if(update['action'] == 'new_answer')
						self.new_answer(update);
					else if(update['action'] == 'upvote' || update['action'] == 'downvote' || update['action'] == 'voteup_undo' || update['action'] == 'votedown_undo')
						self.vote(update);
					else if(update['action'] == 'comment')
						self.comment(update);
				});
			}
			console.log(obj);
		},false);
	 },
	 new_answer: function(obj){
		if(obj['action'] == 'new_answer'){
				if(!obj['can_answer'])
					jQuery('#answer-form-c').hide();
					
				/* Update answer count */
				
				jQuery('[data-view="ap-answer-count"]').text(obj['count']);
				jQuery('[data-view="ap-answer-count-label"]').text(obj['count_label']);
				
				APjs.site.addMessage(obj['message'], 'success');
				
				if(jQuery('#answers').length === 0){
					jQuery('#question').after(jQuery(obj['html']));
					jQuery(obj['div_id']).hide();
				}else
					jQuery('#answers').append(jQuery(obj['html']).hide());			
				
				jQuery(obj['div_id']).slideDown(500);
				
				/* if(typeof responce['redirect_to'] !== 'undefined')
					window.location.replace(responce['redirect_to']); */

			}
	 },
	 vote: function(obj){
		jQuery('[data-id="'+obj['post_id']+'"] > [data-view="ap-net-vote"]').text(obj['net_vote']);
	 },
	 comment: function(obj){
		if(jQuery('#li-comment-'+obj['comment_id']).length ==0){
			APjs.site.addMessage(obj['message'], 'success');
		
			jQuery('#comments-'+obj['post_id']+' ul.commentlist').append(jQuery(obj['html']).hide().slideDown(300));
		}
	 },
	 
}

