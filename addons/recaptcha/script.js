function apCpatchaLoaded() {
  window.ansPressCaptchas = {};

  jQuery('.load-recaptcha').each(function(){
    var id = jQuery(this).attr('id');
    jQuery(this).removeClass('load-recaptcha');

    ansPressCaptchas[id] = grecaptcha.render(id, {
      'sitekey': jQuery(this).data('sitekey')
    });

    jQuery('body').on('submit', jQuery(this).closest('form'), function(){
      grecaptcha.reset(ansPressCaptchas[id]);
    });
  });
}
