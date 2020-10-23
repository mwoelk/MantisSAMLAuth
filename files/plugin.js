$(document).ready(function() {
    //allow admin to set password directly
    //include input for new password
    var frm = $('#login-form');

    var ssoUrl = $("meta[name='ssoUrl']").attr('content');

    $('<a id="sign_with_google" class="btn btn-primary btn-sm bigger-110" href="'+ssoUrl+'">Mit GSuite anmelden</a>').appendTo(frm);
});