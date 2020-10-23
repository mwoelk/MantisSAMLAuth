<?php
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

require_once( 'core.php' );
require_once dirname(__DIR__).'/saml/saml.php';
require_api( 'authentication_api.php' );
require_api( 'user_api.php' );

$f_reauthenticate = gpc_get_bool( 'reauthenticate', false );
$f_return = gpc_get_string( 'return', config_get( 'default_home_page' ) );

$t_return = string_url( string_sanitize_url( $f_return ) );


$returnUrl = config_get( 'default_home_page' );
# Redirect to original page user wanted to access before authentication
if( !is_blank( $t_return ) ) {
	$returnUrl = 'login_cookie_test.php?return=' . $t_return;
}

if (gpc_get_bool('acs', false)) {
    $requestID = session_get('AuthNRequestID', null);

    $samlAuth->processResponse($requestID);

    $errors = $samlAuth->getErrors();
   

    if (!empty($errors)) {
        echo '<p>',implode(', ', $errors),'</p>';
        echo '<p>' .  $samlAuth->getLastErrorReason() . '</p>';
        exit();
    }

    if (!$samlAuth->isAuthenticated()) {
        echo "<p>Not authenticated</p>";
        exit();
    }

    print_r( $samlAuth->getAttributes());
    die;

    session_set('samlUserdata', $samlAuth->getAttributes());
    session_set('samlNameId', $samlAuth->getNameId());
    session_set('samlNameIdFormat', $samlAuth->getNameIdFormat());
    session_set('samlNameIdNameQualifier', $samlAuth->getNameIdNameQualifier());
    session_set('samlNameIdSPNameQualifier', $samlAuth->getNameIdSPNameQualifier());
    session_set('samlSessionIndex', $samlAuth->getSessionIndex());
    session_delete('AuthNRequestID');
    if (isset($_POST['RelayState']) && OneLogin\Saml2\Utils::getSelfURL() != $_POST['RelayState']) {
        $samlAuth->redirectTo($_POST['RelayState']);
    }
}

if (!session_get('samlUserdata', null)) {
    die ("SESSION UNSET");
    $samlAuth->login(config_get_global( 'path' ) . $returnUrl);
} 

$email = session_get('samlNameId', null);

echo $email;
die;

$t_user_id = is_blank( $email ) ? false : user_get_id_by_email( $email );

if( $t_user_id == false ) {
	$t_query_args = array(
		'error' => 1,
		'username' => $email,
	);

	if( !is_blank( 'return' ) ) {
		$t_query_args['return'] = $t_return;
	}

	if( $f_reauthenticate ) {
		$t_query_args['reauthenticate'] = 1;
	}

	$t_query_text = http_build_query( $t_query_args, '', '&' );

	$t_uri = auth_login_page( $t_query_text );

	print_header_redirect( $t_uri );
}

# Let user into MantisBT
auth_login_user( $t_user_id );

# Redirect to original page user wanted to access before authentication
if( !is_blank( $t_return ) ) {
	print_header_redirect( 'login_cookie_test.php?return=' . $t_return );
}

# If no return page, redirect to default page
print_header_redirect( config_get( 'default_home_page' ) );
