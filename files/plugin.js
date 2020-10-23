$(document).ready(function() {
    const urlParams = new URLSearchParams(window.location.search);
    const showLogin = urlParams.get('showLogin');

    if(!showLogin) {
        var frm = $('#login-form');
        var ssoUrl = $("meta[name='ssoUrl']").attr('content');
        $('<a id="sign_with_google" class="btn btn-primary btn-block bigger-110" href="'+ssoUrl+'">Mit GSuite anmelden</a>').insertAfter(frm);
        frm.remove();
    }
});