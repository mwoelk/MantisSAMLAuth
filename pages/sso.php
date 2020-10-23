<?php
require_once( 'core.php' );
require_once dirname(__DIR__).'/saml/saml.php';

$reauthenticate = gpc_get_bool( 'reauthenticate', false );
$returnUrl = gpc_get_string( 'return', config_get( 'default_home_page' ) );
$returnUrl = string_url( string_sanitize_url( $returnUrl ) );

if ($reauthenticate || !session_get('samlUserdata', null)) {
    $samlAuth->login(config_get_global( 'path' ) . $returnUrl);

    $ssoAuthUrl = $samlAuth->login($returnUrl, array(), false, false, true);
    session_set('AuthNRequestID', $samlAuth->getLastRequestID());

    print_header_redirect( 'https://accounts.google.com/AccountChooser?continue=' . $ssoAuthUrl, true, false, true );
} else {
    $email = session_get('samlNameId', null);
    $user_id = user_get_id_by_email( $email );

    # User does not exist
    if( !$user_id ) {
        echo "<p>1 Email address not registered. Please register new account first. <br/> <a href='/login_page.php'>Login</a>";
        return false;
    }

    # check for disabled account
    if( !user_is_enabled( $user_id ) ) {
        echo "<p>2 Email address not registered. Please register new account first. <br/> <a href='/login_page.php'>Login</a>";
        return false;
    }

    # max. failed login attempts achieved...
    if( !user_is_login_request_allowed( $user_id ) ) {
        echo "<p>3 Email address not registered. Please register new account first. <br/> <a href='/login_page.php'>Login</a>";
        return false;
    }

    # check for anonymous login
    if( user_is_anonymous( $user_id ) ) {
        echo "<p>4 Email address not registered. Please register new account first. <br/> <a href='/login_page.php'>Login</a>";
        return false;
    }

    # perform user login
    user_increment_login_count( $user_id );

    user_reset_failed_login_count_to_zero( $user_id );
    user_reset_lost_password_in_progress_count_to_zero( $user_id );

    # set the cookies
    auth_set_cookies( $user_id, false );
    auth_set_tokens( $user_id );

    print_header_redirect( $returnUrl, true );
}