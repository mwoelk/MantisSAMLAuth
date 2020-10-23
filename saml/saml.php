<?php
require_once dirname(__DIR__).'/vendor/php-saml/_toolkit_loader.php';
require_once dirname(__DIR__).'/saml/settings.php';

$samlAuth = new OneLogin_Saml2_Auth($settingsInfo);
$samlSettings = new OneLogin_Saml2_Settings($settingsInfo);