jQuery('body').on('submitQuestion', function(e, responce){
	if(responce['action'] == 'validation_falied'){
		self.clearError('#ask_question_form');
		self.addMessage(responce['message'], 'error');
		self.appendFormError('#ask_question_form', responce['error']);
		if(typeof responce['error']['recaptcha_response_field'] !== 'undefined'){
			var errorString = "&error=" + encodeURIComponent(responce['error']['recaptcha_response_field']);
			Recaptcha.reload();
			console.log(errorString);
		}
	}
});