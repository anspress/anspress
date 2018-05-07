function apCpatchaLoaded() {
  jQuery('.load-recaptcha').each(function(){
    var id = jQuery(this).attr('id');
    jQuery(this).removeClass('load-recaptcha');

    AnsPress.captcha[id] = grecaptcha.render(id, {
      'sitekey': jQuery(this).data('sitekey')
    });

    jQuery('body').on('submit', jQuery(this).closest('form'), function(){
      grecaptcha.reset(AnsPress.captcha[id]);
    });
  });
}
jQuery(document).ready(function(){
  window.Anspress = Anspress || {};
  window.Anspress.captcha = {}
});
