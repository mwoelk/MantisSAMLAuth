<?php
require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/saml/settings.php';

$samlAuth = new OneLogin\Saml2\Auth($settingsInfo);
$samlSettings = new OneLogin\Saml2\Settings($settingsInfo);