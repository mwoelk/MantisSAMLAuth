<?php
require_once dirname(__DIR__).'/vendor/php-saml/_toolkit_loader.php';
require_once dirname(__DIR__).'/saml/settings.php';

$samlAuth = new OneLogin\Saml2\Auth($settingsInfo);
$samlSettings = new OneLogin\Saml2\Settings($settingsInfo);