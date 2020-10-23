<?php
require_once( 'core.php' );
require_once dirname(__DIR__).'/saml/saml.php';

$requestID = session_get('AuthNRequestID', null);

$samlAuth->processResponse($requestID);
session_delete('AuthNRequestID');

$errors = $samlAuth->getErrors();

if (!empty($errors)) {
    echo '<p>' .  $samlAuth->getLastErrorReason() . '</p>';
    exit();
}

if (!$samlAuth->isAuthenticated()) {
    echo "<p>Not authenticated</p>";
    exit();
}

session_set('samlUserdata', $samlAuth->getAttributes());
session_set('samlNameId', $samlAuth->getNameId());
session_set('samlNameIdFormat', $samlAuth->getNameIdFormat());
session_set('samlNameIdNameQualifier', $samlAuth->getNameIdNameQualifier());
session_set('samlNameIdSPNameQualifier', $samlAuth->getNameIdSPNameQualifier());
session_set('samlSessionIndex', $samlAuth->getSessionIndex());

if(!session_get('samlUserdata', null)) {
    echo "<p>No userdata available</p>";
    exit();
}

$email = $samlAuth->getNameId();

$user_id = user_get_id_by_email( $userData->email );

# User does not exist
if( !$user_id ) {
    echo "<p>Email address not registered. Please register new account first. <br/> <a href='/login_page.php'>Login</a>";
    return false;
}

# check for disabled account
if( !user_is_enabled( $user_id ) ) {
    echo "<p>Email address not registered. Please register new account first. <br/> <a href='/login_page.php'>Login</a>";
    return false;
}

# max. failed login attempts achieved...
if( !user_is_login_request_allowed( $user_id ) ) {
    echo "<p>Email address not registered. Please register new account first. <br/> <a href='/login_page.php'>Login</a>";
    return false;
}

# check for anonymous login
if( user_is_anonymous( $user_id ) ) {
    echo "<p>Email address not registered. Please register new account first. <br/> <a href='/login_page.php'>Login</a>";
    return false;
}

# perform user login
user_increment_login_count( $user_id );

user_reset_failed_login_count_to_zero( $user_id );
user_reset_lost_password_in_progress_count_to_zero( $user_id );

# set the cookies
auth_set_cookies( $user_id, false );
auth_set_tokens( $user_id );


if (isset($_POST['RelayState']) && OneLogin\Saml2\Utils::getSelfURL() != $_POST['RelayState']) {
    # Redirect to relay
    $samlAuth->redirectTo($_POST['RelayState']);
} else {
    # Otherwise redirect to homepage
    print_header_redirect( config_get( 'default_home_page' ), true );
}