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


if (!isset($_SESSION['samlUserdata'])) {
    $samlAuth->login(config_get_global( 'path' ) . $returnUrl);
} else if (isset($_GET['acs'])) {
    if (isset($_SESSION) && isset($_SESSION['AuthNRequestID'])) {
        $requestID = $_SESSION['AuthNRequestID'];
    } else {
        $requestID = null;
        echo "ID was null";
        die;
    }

    $samlAuth->processResponse($requestID);

    $errors = $samlAuth->getErrors();

    if (!empty($errors)) {
        echo '<p>',implode(', ', $errors),'</p>';
        exit();
    }

    if (!$samlAuth->isAuthenticated()) {
        echo "<p>Not authenticated</p>";
        exit();
    }

    print_r( $samlAuth->getAttributes());
    die;

    $_SESSION['samlUserdata'] = $samlAuth->getAttributes();
    $_SESSION['samlNameId'] = $samlAuth->getNameId();
    $_SESSION['samlNameIdFormat'] = $samlAuth->getNameIdFormat();
    $_SESSION['samlNameIdNameQualifier'] = $samlAuth->getNameIdNameQualifier();
    $_SESSION['samlNameIdSPNameQualifier'] = $samlAuth->getNameIdSPNameQualifier();
    $_SESSION['samlSessionIndex'] = $samlAuth->getSessionIndex();
    unset($_SESSION['AuthNRequestID']);
    if (isset($_POST['RelayState']) && OneLogin\Saml2\Utils::getSelfURL() != $_POST['RelayState']) {
        $samlAuth->redirectTo($_POST['RelayState']);
    }
} else if (isset($_GET['sls'])) {
    if (isset($_SESSION) && isset($_SESSION['LogoutRequestID'])) {
        $requestID = $_SESSION['LogoutRequestID'];
    } else {
        $requestID = null;
    }

    $samlAuth->processSLO(false, $requestID);
    $errors = $samlAuth->getErrors();
    if (empty($errors)) {
		print_header_redirect( auth_logout_page() );
    } else {
        echo '<p>', implode(', ', $errors), '</p>';
    }
}

$email = $_SESSION['samlNameId'];

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
