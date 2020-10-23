<?php
# Copyright (c) MantisBT Team - mantisbt-dev@lists.sourceforge.net
# Licensed under the MIT license

require_once( 'core.php' );
require_once dirname(__DIR__).'/saml/saml.php';
require_api( 'authentication_api.php' );

# User is already logged out from Mantis
$idpData = $samlSettings->getIdPData();
if (isset($idpData['singleLogoutService']) && isset($idpData['singleLogoutService']['url'])) {
    $sloUrl = $idpData['singleLogoutService']['url'];
} else {
    print_header_redirect( auth_login_page(), true, false );
}

if (isset($_SESSION['IdPSessionIndex']) && !empty($_SESSION['IdPSessionIndex'])) {
    $logoutRequest = new OneLogin_Saml2_LogoutRequest($samlSettings, null, $_SESSION['IdPSessionIndex']);
} else {
    $logoutRequest = new OneLogin_Saml2_LogoutRequest($samlSettings);
}

$samlRequest = $logoutRequest->getRequest();

$parameters = array('SAMLRequest' => $samlRequest);

$url = OneLogin_Saml2_Utils::redirect($sloUrl, $parameters, true);

print_header_redirect( $url, true, false );
