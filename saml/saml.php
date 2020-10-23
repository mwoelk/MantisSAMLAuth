<?php
require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/saml/settings.php';

\OneLogin\Saml2\Utils::setProxyVars(true);

$samlAuth = new OneLogin\Saml2\Auth($settingsInfo);
$samlSettings = new OneLogin\Saml2\Settings($settingsInfo);
